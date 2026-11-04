import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { join } from 'node:path';
import { test } from 'node:test';

const root = join(import.meta.dirname, '../..');
const theme = JSON.parse(readFileSync(join(root, 'theme/theme.json'), 'utf8'));
const aliases = readFileSync(join(root, 'theme/src/styles/tokens.css'), 'utf8');

test('semantic aliases reference WordPress tokens that the theme actually defines', () => {
	const defined = new Set(['--wp--style--global--content-size', '--wp--style--global--wide-size']);
	for (const [section, collection, stem] of [
		['color', 'palette', 'color'], ['typography', 'fontFamilies', 'font-family'],
		['typography', 'fontSizes', 'font-size'], ['spacing', 'spacingSizes', 'spacing'],
	]) {
		for (const preset of theme.settings[section][collection]) defined.add(`--wp--preset--${stem}--${preset.slug}`);
	}
	const custom = (object, path = '--wp--custom') => {
		for (const [key, value] of Object.entries(object)) {
			const name = `${path}--${key.replace(/[A-Z]/g, (char) => `-${char.toLowerCase()}`)}`;
			if (typeof value === 'object') custom(value, name);
			else defined.add(name);
		}
	};
	custom(theme.settings.custom);
	const declarations = [...aliases.matchAll(/(--[a-z-]+):\s*([^;]+);/g)];
	assert.ok(declarations.length > 15);
	for (const [, alias, value] of declarations) {
		const target = value.match(/^var\((--wp--[a-z-]+)\)$/)?.[1];
		assert.ok(target && defined.has(target), `${alias} must reference an existing WordPress variable, without a duplicate literal fallback`);
	}
});
