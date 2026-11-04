#!/usr/bin/env node
/** Fails if the Vite build output is missing, incomplete or references absent files. */
import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const dist = join(import.meta.dirname, '../theme/dist');
const required = ['src/scripts/main.ts', 'src/styles/editor.css', 'src/react/index.tsx', 'src/styles/greendawn-homepage.css'];

const fail = (message) => {
	console.error(`Manifest validation failed: ${message}`);
	process.exit(1);
};

const manifestPath = join(dist, 'manifest.json');
if (!existsSync(manifestPath)) fail('theme/dist/manifest.json not found. Run `pnpm build`.');

const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
for (const key of required) {
	if (!manifest[key]) fail(`entry "${key}" missing from manifest.`);
}
for (const [key, entry] of Object.entries(manifest)) {
	for (const file of [entry.file, ...(entry.css ?? [])]) {
		if (!existsSync(join(dist, file))) fail(`"${key}" references missing file ${file}.`);
	}
}
console.log(`Manifest OK (${Object.keys(manifest).length} entries).`);
