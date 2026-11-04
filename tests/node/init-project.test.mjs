import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import { cpSync, existsSync, mkdtempSync, readFileSync, readdirSync, rmSync, mkdirSync, symlinkSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { after, before, describe, test } from 'node:test';
import { initProject, validate } from '../../scripts/init-project.mjs';

const repo = join(import.meta.dirname, '../..');
const skip = new Set(['node_modules', '.git', 'vendor', 'build', 'dist']);
describe('starter initialisation', { skip: existsSync(join(repo, 'project.identity.json')) }, () => {
let tmp;

before(() => {
	tmp = mkdtempSync(join(tmpdir(), 'init-'));
	cpSync(repo, tmp, { recursive: true, filter: (src) => !skip.has(src.split('/').pop()) });
	const result = spawnSync(
		process.execPath,
		['scripts/init-project.mjs', '--name', 'Example Client', '--slug', 'example-client', '--prefix', 'example_client', '--text-domain', 'ex-client', '--hostname', 'example.localhost'],
		{ cwd: tmp, encoding: 'utf8' }
	);
	assert.equal(result.status, 0, result.stderr);
});

after(() => rmSync(tmp, { recursive: true, force: true }));

function* files(dir) {
	for (const entry of readdirSync(dir, { withFileTypes: true })) {
		const path = join(dir, entry.name);
		if (entry.isDirectory()) yield* files(path);
		else yield path;
	}
}

test('rewrites WordPress identifiers', () => {
	const style = readFileSync(join(tmp, 'theme/style.css'), 'utf8');
	assert.match(style, /Theme Name: Example Client/);
	assert.match(style, /Text Domain: ex-client/);
	assert.doesNotMatch(style, /Theme URI/);

	const functions = readFileSync(join(tmp, 'theme/functions.php'), 'utf8');
	assert.match(functions, /EXAMPLE_CLIENT_VERSION/);
	assert.match(readFileSync(join(tmp, 'theme/inc/setup.php'), 'utf8'), /function example_client_setup/);
	assert.match(readFileSync(join(tmp, 'theme/header.php'), 'utf8'), /'ex-client'/);
	assert.match(readFileSync(join(tmp, 'package.json'), 'utf8'), /"name": "example-client"/);
	assert.match(readFileSync(join(tmp, 'theme/patterns/card.php'), 'utf8'), /Slug: example-client\/card/);
});

test('renames the plugin and MU plugin consistently', () => {
	assert.ok(existsSync(join(tmp, 'plugins/example-client-core/example-client-core.php')));
	assert.ok(existsSync(join(tmp, 'mu-plugins/example-client-core-env.php')));
	assert.ok(!existsSync(join(tmp, 'plugins/agency-core')));
	const plugin = readFileSync(join(tmp, 'plugins/example-client-core/includes/Plugin.php'), 'utf8');
	assert.match(plugin, /namespace Example_Client_Core;/);
	assert.match(plugin, /example_client_core_enabled_modules/);
});

test('leaves no starter identifiers behind', () => {
	const leftovers = [/wp-starter/i, /wp_starter/i, /agency[-_ ]core/i];
	const allowed = [/LICENSE$/, /pnpm-lock\.yaml$/, /composer\.lock$/, /init-project(\.test)?\.mjs$/];
	for (const file of files(tmp)) {
		if (allowed.some((pattern) => pattern.test(file))) continue;
		const text = readFileSync(file, 'utf8');
		for (const pattern of leftovers) assert.doesNotMatch(text, pattern, `${file} still matches ${pattern}`);
	}
});

test('generated PHP is syntactically valid', () => {
	for (const file of files(tmp)) {
		if (!file.endsWith('.php')) continue;
		const result = spawnSync('php', ['-l', file], { encoding: 'utf8' });
		assert.equal(result.status, 0, `${file}: ${result.stdout}${result.stderr}`);
	}
});

test('refuses to run twice', () => {
	const result = spawnSync(process.execPath, ['scripts/init-project.mjs', '--name', 'Other', '--slug', 'other'], { cwd: tmp, encoding: 'utf8' });
	assert.notEqual(result.status, 0);
});

test('validates input', () => {
	assert.deepEqual(validate({ name: 'A', slug: 'a-b', prefix: 'a_b', textDomain: 'a-b', hostname: 'a-b.localhost' }), []);
	assert.ok(validate({ name: 'A', slug: 'Bad Slug', prefix: 'a', textDomain: 'a', hostname: 'a.localhost' }).length > 0);
	assert.ok(validate({ name: 'A */ evil', slug: 'a', prefix: 'a', textDomain: 'a', hostname: 'a.localhost' }).length > 0);
});

const client = { name: 'Second Studio', slug: 'second-studio', prefix: 'second_studio', textDomain: 'second-studio', hostname: 'second.localhost' };
function copyStarter() {
	const dir = mkdtempSync(join(tmpdir(), 'init-plan-'));
	cpSync(repo, dir, { recursive: true, filter: (src) => !skip.has(src.split('/').pop()) });
	return dir;
}
function snapshot(dir) {
	return [...files(dir)].map((file) => [file.slice(dir.length), readFileSync(file).toString('base64')]);
}

test('dry-run reports every write and rename without touching source', () => {
	const dir = copyStarter();
	try {
		const original = snapshot(dir);
		const plan = initProject(dir, client, { dryRun: true });
		assert.deepEqual(snapshot(dir), original);
		assert.ok(plan.changed.includes('README.md'));
		assert.ok(plan.changed.includes('project.identity.json'));
		assert.equal(plan.renames.length, 3);
		assert.deepEqual(initProject(dir, client).changed, plan.changed);
		writeFileSync(join(dir, 'theme/style.css'), 'Theme Name: WP Starter\n');
		assert.throws(() => initProject(dir, client), /already been initialised/);
	} finally { rmSync(dir, { recursive: true, force: true }); }
});

test('preserves shared updater bytes and removes inherited sources', () => {
	for (const file of files(join(repo, 'plugins/ezhost-updater'))) {
		assert.deepEqual(readFileSync(file), readFileSync(file.replace(repo, tmp)));
	}
	const config = readFileSync(join(tmp, 'wp-config.example.php'), 'utf8');
	assert.match(config, /'themes' => array\(\), 'plugins' => array\(\)/);
	assert.doesNotMatch(config, /ezhostforall\/example-client|wp-starter-theme\.ezhostforall/);
	assert.ok(existsSync(join(tmp, 'project.identity.json')));
});

test('rejects collisions, live configuration, invalid PHP and invalid JSON before writing', () => {
	for (const prepare of [
		(dir) => mkdirSync(join(dir, 'plugins/second-studio-core')),
		(dir) => writeFileSync(join(dir, 'wp-config.php'), '<?php // private configuration'),
		(dir) => writeFileSync(join(dir, 'theme/page.php'), '<?php function ('),
		(dir) => writeFileSync(join(dir, 'theme/theme.json'), '{ broken'),
	]) {
		const dir = copyStarter();
		try {
			prepare(dir);
			const original = snapshot(dir);
			assert.throws(() => initProject(dir, client));
			assert.deepEqual(snapshot(dir), original);
		} finally { rmSync(dir, { recursive: true, force: true }); }
	}
});

test('validates direct API calls and unsafe/reserved inputs before writing', () => {
	for (const invalid of [
		{ name: 'Quoted "name"' }, { name: "O'Brien" }, { author: 'Broken */ header' },
		{ slug: 'wp-starter' }, { slug: 'agency-core' }, { prefix: 'wp_starter' }, { prefix: 'ezhost_updater' },
		{ hostname: 'bad..host' }, { repository: 'ezhostforall/wp-starter' },
		{ stagingUrl: 'https://wp-starter-theme.ezhostforall.com/' },
		{ stagingUrl: 'http://staging.example.com' }, { stagingUrl: 'https:staging.example.com' }, { authorUri: 'https://user:pass@example.com' },
	]) {
		assert.ok(validate({ ...client, ...invalid }).length, JSON.stringify(invalid));
		assert.throws(() => initProject(tmp, { ...client, ...invalid }));
	}
});

test('two independently configured client projects build and package with correct identities', () => {
	for (const slug of ['north2-studio', 'south-services']) {
		const dir = copyStarter();
		try {
			const repository = `example-agency/wp-starter-${slug}`;
			initProject(dir, { ...client, slug, prefix: slug.replaceAll('-', '_'), textDomain: `${slug}-text`, repository, stagingUrl: `https://${slug}.example.com/`, author: 'Client Agency', authorUri: 'https://agency.example.com' });
			const config = readFileSync(join(dir, 'wp-config.example.php'), 'utf8');
			assert.match(config, new RegExp(`'${slug}' => array`));
			assert.equal(config.split(`'repo' => '${repository}'`).length - 1, 3);
			assert.doesNotMatch(config, /ezhostforall\/wp-starter|wp-starter-theme\.ezhostforall/);
			assert.match(readFileSync(join(dir, `plugins/${slug}-core/${slug}-core.php`), 'utf8'), /Author URI: https:\/\/agency\.example\.com/);
			assert.match(readFileSync(join(dir, 'theme/style.css'), 'utf8'), /Author URI: https:\/\/agency\.example\.com/);
			assert.doesNotMatch(readFileSync(join(dir, 'plugins/ezhost-updater/ezhost-updater.php'), 'utf8'), /Client Agency/);
			for (const file of files(join(repo, 'plugins/ezhost-updater'))) assert.deepEqual(readFileSync(file), readFileSync(file.replace(repo, dir)));
			symlinkSync(join(repo, 'node_modules'), join(dir, 'node_modules'), 'dir');
			for (const args of [['scripts/set-version.mjs', '0.4.0'], ['scripts/package.mjs'], ['scripts/validate-release.mjs']]) {
				const result = spawnSync(process.execPath, args, { cwd: dir, encoding: 'utf8', timeout: 60000 });
				assert.equal(result.status, 0, `${slug}: ${result.stdout}${result.stderr}`);
			}
			const manifest = JSON.parse(readFileSync(join(dir, 'build/updates.json'), 'utf8'));
			assert.deepEqual(Object.keys(manifest.components).sort(), [`theme/${slug}`, `plugin/${slug}-core/${slug}-core.php`, 'plugin/ezhost-updater/ezhost-updater.php'].sort());
			assert.equal(manifest.version, '0.4.0');
			assert.ok(readFileSync(join(dir, 'theme/functions.php'), 'utf8').includes("VERSION', '0.4.0'"));
		} finally { rmSync(dir, { recursive: true, force: true }); }
	}
});
});
