#!/usr/bin/env node
/**
 * Builds deployable ZIPs: one for the theme and one per plugin in plugins/.
 * Output: build/<slug>-<version>.zip. Requires the `zip` command.
 */
import { spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { cpSync, existsSync, mkdirSync, readdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { basename, join } from 'node:path';

const root = join(import.meta.dirname, '..');
const run = (command, args, cwd = root) => {
	const result = spawnSync(command, args, { cwd, stdio: 'inherit' });
	if (result.status !== 0) {
		console.error(`Command failed: ${command} ${args.join(' ')}`);
		process.exit(result.status ?? 1);
	}
};
const header = (file, label) => readFileSync(join(root, file), 'utf8').match(new RegExp(`^[ *]*${label}:\\s*(.+)$`, 'm'))?.[1]?.trim();

const pkg = JSON.parse(readFileSync(join(root, 'package.json'), 'utf8'));
const themeVersion = header('theme/style.css', 'Version');
if (themeVersion !== pkg.version) {
	console.error(`Version mismatch: package.json ${pkg.version} vs theme/style.css ${themeVersion}. Run scripts/set-version.mjs.`);
	process.exit(1);
}

run('pnpm', ['build']);

const out = join(root, 'build');
rmSync(out, { recursive: true, force: true });
mkdirSync(out, { recursive: true });

const excluded = ['src', 'node_modules', '.vite-hot', '.DS_Store', '.gitkeep'];
const filter = (source) => !excluded.includes(basename(source)) && !/\.(map|log)$/.test(source) && !/^\.env/.test(basename(source));

const targets = [{
  slug: pkg.name,
  dir: 'theme',
  version: themeVersion,
  type: 'theme',
  requires_wp: header('theme/style.css', 'Requires at least'),
  requires_php: header('theme/style.css', 'Requires PHP'),
}];
for (const entry of readdirSync(join(root, 'plugins'), { withFileTypes: true })) {
	const main = `plugins/${entry.name}/${entry.name}.php`;
	if (entry.isDirectory() && existsSync(join(root, main))) {
		const version = header(main, 'Version');
		if (version !== pkg.version) {
			console.error(`Version mismatch: plugin ${entry.name} ${version} vs package.json ${pkg.version}.`);
			process.exit(1);
		}
		targets.push({
      slug: entry.name,
      dir: `plugins/${entry.name}`,
      version,
      type: 'plugin',
      requires_wp: header(main, 'Requires at least'),
      requires_php: header(main, 'Requires PHP'),
    });
	}
}

const manifest = { schema: 1, version: pkg.version, components: {} };
const checksums = [];

for (const { slug, dir, version, type, requires_wp, requires_php } of targets) {
  if (!requires_wp || !requires_php) {
    throw new Error(`Missing PHP or WordPress requirement header in ${dir}`);
  }
	const stage = join(out, 'stage');
	const staged = join(stage, slug);
	cpSync(join(root, dir), staged, { recursive: true, filter });
	const zip = join(out, `${slug}-${version}.zip`);
	run('zip', ['-rq', zip, slug], stage);
	rmSync(stage, { recursive: true, force: true });
	const asset = `${slug}-${version}.zip`;
  const key = type === 'theme' ? `theme/${slug}` : `plugin/${slug}/${slug}.php`;
  manifest.components[key] = { asset, requires_wp, requires_php };
  const hash = createHash('sha256').update(readFileSync(zip)).digest('hex');
  checksums.push(`${hash}  ${asset}`);
  console.log(`Created ${zip}`);
}
writeFileSync(join(out, 'updates.json'), JSON.stringify(manifest, null, 2) + '\n');
writeFileSync(join(out, 'checksums.sha256'), checksums.join('\n') + '\n');
console.log('Created update manifest and SHA-256 checksums.');
