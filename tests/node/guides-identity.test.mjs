import assert from 'node:assert/strict';
import { test } from 'node:test';
import { buildReplacements } from '../../scripts/init-project.mjs';

test('new Guide identifiers remain initialisable and fit WordPress limits', () => {
	for (const prefix of ['example', 'very_long_agency_prefix']) {
		const replacements = buildReplacements({ prefix, slug: 'example', name: 'Example', textDomain: 'example', hostname: 'example.localhost' });
		const replace = (text) => replacements.reduce((value, [pattern, replacement]) => value.replace(pattern, replacement), text);
		const type = replace('gdguide');
		const taxonomy = replace('gdguide_topic');
		assert.match(type, /^[a-z_]+_guide$/);
		assert.match(taxonomy, /^[a-z_]+_guide_topic$/);
		assert.ok(type.length <= 20);
		assert.ok(taxonomy.length <= 32);
		assert.notEqual(type, 'gdguide');
		assert.notEqual(taxonomy, 'gdguide_topic');
	}
});
