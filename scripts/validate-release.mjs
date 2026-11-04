#!/usr/bin/env node
/** Verify that release metadata, ZIP assets and SHA256 checksums match. */
import { createHash } from 'node:crypto';
import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
const root = join(import.meta.dirname, '..');
const pkg = JSON.parse(readFileSync(join(root, 'package.json'), 'utf8'));
const manifest = JSON.parse(readFileSync(join(root, 'build/updates.json'), 'utf8'));
if (manifest.schema !== 1 || manifest.version !== pkg.version) {
  throw new Error('Invalid update manifest version/schema');
}
const lines = readFileSync(join(root, 'build/checksums.sha256'), 'utf8').trim().split('\n');
const checksums = new Map();
for (const line of lines) {
  const match = line.match(/^([a-f0-9]{64}) {2}([a-z0-9.-]+\.zip)$/);
  if (!match || checksums.has(match[2])) throw new Error('Invalid/duplicate checksum: ' + line);
  checksums.set(match[2], match[1]);
}
const expected = new Set();
for (const [id, data] of Object.entries(manifest.components)) {
  if (!/^(theme\/[a-z0-9-]+|plugin\/[a-z0-9-]+\/[a-z0-9-]+\.php)$/.test(id)) throw new Error('Invalid component ID ' + id);
  if (!data.requires_php || !data.requires_wp) throw new Error('Missing compatibility data for ' + id);
  const [, slug] = id.split('/');
  const asset = slug + '-' + pkg.version + '.zip';
  if (data.asset !== asset) throw new Error('Unexpected asset for ' + id);
  if (expected.has(asset)) throw new Error('Duplicate asset ' + asset);
  expected.add(asset);
  const path = join(root, 'build', asset);
  if (!existsSync(path)) throw new Error('Missing ZIP: ' + asset);
  const digest = createHash('sha256').update(readFileSync(path)).digest('hex');
  if (checksums.get(asset) !== digest) throw new Error('Checksum mismatch: ' + asset);
}
if (expected.size !== checksums.size) throw new Error('Unreferenced checksum entries');
console.log('Verified', expected.size, 'package checksums and component metadata.');
