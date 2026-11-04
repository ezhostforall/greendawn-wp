#!/usr/bin/env node
/** Sets one version across package.json, theme/style.css, theme constant and plugin headers. */
import { readdirSync, readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const version = process.argv[2];
if (!version || !/^\d+\.\d+\.\d+(-[0-9A-Za-z.-]+)?$/.test(version)) {
	console.error('Usage: node scripts/set-version.mjs <semver>   e.g. 1.2.0');
	process.exit(1);
}

const root = join(import.meta.dirname, '..');
const edit = (file, pattern, replacement) => {
	const path = join(root, file);
	const before = readFileSync(path, 'utf8');
	const after = before.replace(pattern, replacement);
	if (before === after && !pattern.test(before)) throw new Error(`Pattern not found in ${file}`);
	writeFileSync(path, after);
};

edit('package.json', /("version":\s*")[^"]+/, `$1${version}`);
edit('theme/style.css', /^(Version:).*$/m, `$1 ${version}`);
edit('theme/functions.php', /(define\( '[A-Z0-9_]+_VERSION', ')[^']*/, `$1${version}`);

for (const dir of readdirSync(join(root, 'plugins'), { withFileTypes: true })) {
	const main = `plugins/${dir.name}/${dir.name}.php`;
	if (dir.isDirectory() && existsSync(join(root, main))) {
		edit(main, /^( \* Version:).*$/m, `$1 ${version}`);
		edit(main, /(define\( '[A-Z0-9_]+_VERSION', ')[^']*/, `$1${version}`);
	}
}
console.log(`Version set to ${version}`);
