import assert from 'node:assert/strict';
import { existsSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test } from 'node:test';

const root = join(import.meta.dirname, '../..');
const config = readFileSync(join(root, 'wp-config.example.php'), 'utf8');
const ignored = readFileSync(join(root, '.gitignore'), 'utf8');

test('wp-config example contains placeholders instead of database and authentication secrets', () => {
  const names = ['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST',
    'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
    'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];
  for (const name of names) {
    assert.ok(config.includes("'"+name+"',") || config.includes("'"+name+"', "));
    assert.ok(config.includes("'CHANGE_ME_"+(name === 'DB_NAME'?'DATABASE_NAME':name === 'DB_USER'?'DATABASE_USER':name === 'DB_PASSWORD'?'DATABASE_PASSWORD':name === 'DB_HOST'?'DATABASE_HOST':name)+"'"));
  }
  assert.ok(config.includes("// define( 'EZHOST_GITHUB_TOKEN', 'CHANGE_ME_READ_ONLY_GITHUB_TOKEN' );"));
  assert.match(ignored, /^wp-config\.php$/m);
  assert.match(ignored, /^\*\*\/wp-config\.php$/m);
});

test('evaluated configuration defaults to staging and stable-only explicit sources', () => {
  const dir = mkdtempSync(join(tmpdir(), 'config-template-'));
  try {
    // Bootstrap only this stub: no WordPress, database or network is started.
    writeFileSync(join(dir, 'wp-settings.php'), `<?php echo json_encode(array(
      'environment' => WP_ENVIRONMENT_TYPE,
      'file_edit_disabled' => DISALLOW_FILE_EDIT,
      'debug_display' => WP_DEBUG_DISPLAY,
      'token_constant_defined' => defined('EZHOST_GITHUB_TOKEN'),
      'sources' => EZHOST_UPDATE_SOURCES
    ), JSON_THROW_ON_ERROR);`);
    const result = spawnSync('php', ['-r', 'define("ABSPATH", $argv[1] . "/"); require $argv[2];', dir, join(root, 'wp-config.example.php')], { encoding: 'utf8' });
    assert.equal(result.status, 0, result.stderr);
    const settings = JSON.parse(result.stdout);
    assert.equal(settings.environment, 'staging');
    assert.equal(settings.file_edit_disabled, true);
    assert.equal(settings.debug_display, false);
    assert.equal(settings.token_constant_defined, false);
    const identityPath = join(root, 'project.identity.json');
    const identity = existsSync(identityPath) ? JSON.parse(readFileSync(identityPath, 'utf8')) : null;
    if (identity && !identity.repository) {
      assert.deepEqual(settings.sources, { themes: [], plugins: [] });
    } else {
      const slug = JSON.parse(readFileSync(join(root, 'package.json'), 'utf8')).name;
      const plugin = identity ? `${identity.slug}-core` : 'agency-core';
      assert.deepEqual(Object.keys(settings.sources.themes), [slug]);
      assert.deepEqual(Object.keys(settings.sources.plugins).sort(), [`${plugin}/${plugin}.php`, 'ezhost-updater/ezhost-updater.php'].sort());
      for (const source of [...Object.values(settings.sources.themes), ...Object.values(settings.sources.plugins)]) {
        assert.deepEqual(source, { repo: identity?.repository ?? 'ezhostforall/wp-starter', token_env: 'EZHOST_GITHUB_TOKEN' });
        assert.ok(!Object.hasOwn(source, 'candidate_tag'));
      }
    }
  } finally { rmSync(dir, { recursive: true, force: true }); }
});

test('Git ignores private config variants and tracks the root example', () => {
  const dir = mkdtempSync(join(tmpdir(), 'config-ignore-'));
  try {
    writeFileSync(join(dir, '.gitignore'), ignored);
    const init = spawnSync('git', ['init', '-q'], { cwd: dir, encoding: 'utf8' });
    assert.equal(init.status, 0, init.stderr);
    const privatePaths = ['wp-config.php', 'wp-config.production.local.php', 'host/wp-config.php', 'host/wp-config.backup.php', '.env', '.env.production'];
    const paths = [...privatePaths, 'wp-config.example.php', 'package.json'];
    const result = spawnSync('git', ['-c', 'core.excludesFile=/dev/null', 'check-ignore', '--no-index', '--stdin'], { cwd: dir, input: paths.join('\n') + '\n', encoding: 'utf8' });
    assert.equal(result.status, 0, result.stderr);
    assert.deepEqual(result.stdout.trim().split('\n').sort(), privatePaths.sort());
  } finally { rmSync(dir, { recursive: true, force: true }); }
});
