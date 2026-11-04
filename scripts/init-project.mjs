#!/usr/bin/env node
/**
 * Turns this starter into a client project by rewriting every starter identifier.
 *
 *   node scripts/init-project.mjs --name "Example Client" --slug example-client \
 *     [--prefix example_client] [--text-domain example-client] [--hostname example-client.localhost] \
 *     [--author "Agency"] [--author-uri https://example.com] [--theme-uri https://example.com] \
 *     [--repository owner/repo] [--staging-url https://staging.example.com] \
 *     [--root <dir>] [--dry-run]
 *
 * The theme keeps its folder name (`theme/`); the optional plugin and MU plugin are
 * renamed to `<slug>-core`. Re-running is refused once the identifiers are gone.
 */
import { existsSync, readdirSync, readFileSync, renameSync, writeFileSync } from 'node:fs';
import { join, relative, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';
import { parseArgs } from 'node:util';

const STARTER = {
	name: 'WP Starter',
	slug: 'wp-starter',
	prefix: 'wp_starter',
	pascal: 'WP_Starter',
	hostname: 'wp-starter.localhost',
	pluginSlug: 'agency-core',
	pluginPrefix: 'agency_core',
	pluginPascal: 'Agency_Core',
	pluginName: 'Agency Core',
	author: 'EZ Host For All',
	authorUri: 'https://github.com/ezhostforall',
	themeUri: 'https://github.com/ezhostforall/wp-starter',
};

const SKIP_DIRS = new Set(['.git', 'node_modules', 'vendor', 'build', 'dist', 'playwright-report', 'test-results']);
const SKIP_FILES = new Set(['pnpm-lock.yaml', 'composer.lock', 'LICENSE', 'init-project.mjs', 'init-project.test.mjs']);
const MARKER = 'project.identity.json';

const pascal = (value) => value.split('_').map((part) => part.charAt(0).toUpperCase() + part.slice(1)).join('_');
const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/** Values are inserted in PHP, JSON and documentation; fail before mutation. */
export function validate(options) {
	const errors = [];
	if (!options.name?.trim()) errors.push('--name is required.');
	for (const key of ['name', 'author']) {
		if (options[key] && (/['"\\$*/`<>]/.test(options[key]) || [...options[key]].some((char) => char.charCodeAt(0) < 32 || char.charCodeAt(0) === 127))) errors.push(`--${key} contains characters unsafe for source replacement.`);
	}
	for (const key of ['slug', 'textDomain']) {
		if (!/^[a-z][a-z0-9]*(-[a-z0-9]+)*$/.test(options[key] ?? '')) errors.push(`--${key} must be lowercase letters, digits and hyphens.`);
	}
	if (!/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/.test(options.prefix ?? '')) errors.push('--prefix must be lowercase letters, digits and underscores.');
	if (['wp-starter', 'agency-core', 'ezhost-updater', 'theme'].includes(options.slug) || ['wp_starter', 'agency_core', 'ezhost_updater'].includes(options.prefix)) errors.push('Choose a new, non-reserved project identity.');
	if (!options.hostname || options.hostname.length > 253 || options.hostname.split('.').some((label) => !/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/.test(label))) errors.push('--hostname must be a valid hostname.');
	if (options.repository && (!/^[A-Za-z0-9][A-Za-z0-9-]*\/[A-Za-z0-9][A-Za-z0-9_.-]*$/.test(options.repository) || options.repository.toLowerCase() === 'ezhostforall/wp-starter')) errors.push('--repository must identify the client repository as owner/repo.');
	for (const key of ['authorUri', 'themeUri', 'stagingUrl']) {
		if (!options[key]) continue;
		try {
			const url = new URL(options[key]);
			if (!options[key].startsWith('https://') || url.protocol !== 'https:' || url.username || url.password || /[\s'"\\$*`<>]/.test(options[key])) throw new Error();
			if (key === 'stagingUrl' && (url.hostname === 'wp-starter-theme.ezhostforall.com' || url.search || url.hash || url.pathname !== '/')) throw new Error();
		} catch {
			errors.push(`--${key} must be an HTTPS URL without credentials or unsafe characters; staging must be a client origin.`);
		}
	}
	return errors;
}

function* walk(dir, root = dir) {
	for (const entry of readdirSync(dir, { withFileTypes: true })) {
		if (SKIP_DIRS.has(entry.name)) continue;
		const path = join(dir, entry.name);
		if (relative(root, path) === 'plugins/ezhost-updater') continue;
		if (entry.isSymbolicLink()) throw new Error(`Initialise a clean source copy without symlinks: ${relative(root, path)}`);
		if (entry.isDirectory()) yield* walk(path, root);
		else {
			if ((/^wp-config(?:[.-].*)?\.php$/.test(entry.name) && entry.name !== 'wp-config.example.php') || (/^\.env(?:\.|$)/.test(entry.name) && entry.name !== '.env.example')) throw new Error(`Remove private configuration before initialisation: ${relative(root, path)}`);
			if (!SKIP_FILES.has(entry.name)) yield path;
		}
	}
}

/** Builds the ordered list of [pattern, replacement] pairs. Order matters. */
export function buildReplacements(o) {
	const pluginSlug = `${o.slug}-core`;
	const pluginPrefix = `${o.prefix}_core`;
	return [
		[/https:\/\/github\.com\/ezhostforall\/wp-starter/g, o.repository ? `https://github.com/${o.repository}` : 'https://example.invalid/configure-client-repository'],
		[/ezhostforall\/wp-starter/g, o.repository ?? 'CONFIGURE_CLIENT_REPOSITORY'],
		[/https:\/\/wp-starter-theme\.ezhostforall\.com/g, o.stagingUrl?.replace(/\/$/, '') ?? 'https://example.invalid/configure-client-staging'],
		// Plugin identifiers (no overlap with theme identifiers).
		[/AGENCY_CORE/g, pluginPrefix.toUpperCase()],
		[/Agency_Core/g, pascal(pluginPrefix)],
		[/agency_core/g, pluginPrefix],
		[/agency-core/g, pluginSlug],
		[/Agency Core/g, `${o.name} Core`],
		// Hostname before slug, because it contains the slug.
		[new RegExp(escapeRegExp(STARTER.hostname), 'g'), o.hostname],
		// Text domain occurrences, so it may differ from the slug.
		[/'wp-starter'/g, `'${o.textDomain}'`],
		[/Text Domain: wp-starter/g, `Text Domain: ${o.textDomain}`],
		[/<element value="wp-starter"\/>/g, `<element value="${o.textDomain}"/>`],
		// Theme identifiers.
		[/WP_STARTER/g, o.prefix.toUpperCase()],
		[/WP_Starter/g, pascal(o.prefix)],
		[/wp_starter/g, o.prefix],
		[/wp-starter/g, o.slug],
		[/WP Starter/g, o.name],
	];
}

/** Build and validate the complete plan before writing any files. */
export function initProject(root, options, { dryRun = false } = {}) {
	const errors = validate(options);
	if (errors.length) throw new Error(errors.join('\n'));
	const style = join(root, 'theme/style.css');
	if (existsSync(join(root, MARKER)) || !existsSync(style) || !/^Theme Name: WP Starter\r?$/m.test(readFileSync(style, 'utf8'))) {
		throw new Error('This project has already been initialised or is not a clean starter.');
	}
	const pluginSlug = `${options.slug}-core`;
	const renames = [
		[`plugins/${STARTER.pluginSlug}/${STARTER.pluginSlug}.php`, `plugins/${STARTER.pluginSlug}/${pluginSlug}.php`],
		[`plugins/${STARTER.pluginSlug}`, `plugins/${pluginSlug}`],
		[`mu-plugins/${STARTER.pluginSlug}-env.php`, `mu-plugins/${pluginSlug}-env.php`],
	].filter(([from]) => existsSync(join(root, from)));
	for (const [, to] of renames) {
		if (existsSync(join(root, to))) throw new Error(`Rename destination already exists: ${to}`);
	}

	const replacements = buildReplacements(options);
	const combined = new RegExp(replacements.map(([pattern]) => `(?:${pattern.source})`).join('|'), 'g');
	const writes = new Map();
	for (const file of walk(root)) {
		const buffer = readFileSync(file);
		const original = buffer.toString('utf8');
		if (buffer.includes(0) || !Buffer.from(original).equals(buffer)) continue;
		const path = relative(root, file);
		let text = original;
		text = text.replace(combined, (match) => replacements.find(([pattern]) => new RegExp(`^(?:${pattern.source})$`).test(match))[1]);
		if (path === 'theme/style.css') {
			text = options.themeUri
				? text.replace(/^Theme URI:.*$/m, () => `Theme URI: ${options.themeUri}`)
				: text.replace(/^Theme URI:.*\r?\n/m, '');
		}
		if (path === 'theme/style.css' || path.startsWith(`plugins/${STARTER.pluginSlug}/`)) {
			if (options.author) text = text.replace(/^([ \t]*\*?[ \t]*Author:).*$/gm, (_, header) => `${header} ${options.author}`);
			if (options.authorUri) {
				if (/^[ \t]*\*?[ \t]*Author URI:/m.test(text)) text = text.replace(/^([ \t]*\*?[ \t]*Author URI:).*$/gm, (_, header) => `${header} ${options.authorUri}`);
				else text = text.replace(/^([ \t]*\*?[ \t]*)Author:.*$/m, (line, indent) => `${line}\n${indent}Author URI: ${options.authorUri}`);
			}
		}
		if (path === 'wp-config.example.php') {
			const entries = options.repository ? `array(
        'themes' => array(
            '${options.slug}' => array( 'repo' => '${options.repository}', 'token_env' => 'EZHOST_GITHUB_TOKEN' ),
        ),
        'plugins' => array(
            '${pluginSlug}/${pluginSlug}.php' => array( 'repo' => '${options.repository}', 'token_env' => 'EZHOST_GITHUB_TOKEN' ),
            'ezhost-updater/ezhost-updater.php' => array( 'repo' => '${options.repository}', 'token_env' => 'EZHOST_GITHUB_TOKEN' ),
        ),
    )` : "array( 'themes' => array(), 'plugins' => array() )";
			const pattern = /define\(\s*'EZHOST_UPDATE_SOURCES',[\s\S]*?\n\);/;
			if (!pattern.test(text)) throw new Error('Cannot locate the example update allowlist.');
			text = text.replace(pattern, () => `define(\n    'EZHOST_UPDATE_SOURCES',\n    ${entries}\n);`);
			text = text.replace(/\/\/ To test [\s\S]*?\/\/ Do NOT add candidate_tag to a production site's configuration\./, '// Updates require explicit client repository configuration.\n// Production uses stable releases. On staging only, add an exact candidate_tag\n// to the selected entries after publishing and verifying that candidate.');
		}
		if (path.endsWith('.php')) {
			const result = spawnSync('php', ['-l'], { input: text, encoding: 'utf8', timeout: 10000 });
			if (result.status !== 0) throw new Error(`Generated PHP validation failed for ${path}: ${result.error?.message ?? result.stderr ?? result.stdout}`);
		}
		if (['package.json', 'theme/theme.json', 'composer.json'].includes(path)) JSON.parse(text);
		if (text !== original) writes.set(path, text);
	}

	writes.set('README.md', `# ${options.name}\n\nIndependent WordPress theme (\`theme/\`) and site plugin (\`plugins/${pluginSlug}\`).\n\nRepository: ${options.repository ?? 'Configure a client repository before enabling updates.'}\n\nStaging: ${options.stagingUrl ?? 'Configure a separate client WordPress staging installation.'}\n\nSee [getting started](docs/getting-started.md), [client customisation](docs/client-customisation.md) and [deployment](docs/deployment.md). Upstream improvements must be ported through reviewed client PRs.\n`);
	writes.set(MARKER, JSON.stringify({ schema: 1, ...options, starterVersion: JSON.parse(readFileSync(join(root, 'package.json'), 'utf8')).version }, null, 2) + '\n');
	if (!dryRun) {
		for (const [path, text] of writes) writeFileSync(join(root, path), text);
		for (const [from, to] of renames) renameSync(join(root, from), join(root, to));
	}
	return { changed: [...writes.keys()], renames, pluginSlug };
}

function main() {
	const { values } = parseArgs({
		options: {
			name: { type: 'string' },
			slug: { type: 'string' },
			prefix: { type: 'string' },
			'text-domain': { type: 'string' },
			hostname: { type: 'string' },
			author: { type: 'string' },
			'author-uri': { type: 'string' },
			'theme-uri': { type: 'string' },
			repository: { type: 'string' },
			'staging-url': { type: 'string' },
			root: { type: 'string' },
			'dry-run': { type: 'boolean', default: false },
		},
	});

	const slug = values.slug;
	const options = {
		name: values.name,
		slug,
		prefix: values.prefix ?? slug?.replace(/-/g, '_'),
		textDomain: values['text-domain'] ?? slug,
		hostname: values.hostname ?? (slug ? `${slug}.localhost` : undefined),
		author: values.author,
		authorUri: values['author-uri'],
		themeUri: values['theme-uri'],
		repository: values.repository,
		stagingUrl: values['staging-url'],
	};

	const errors = validate(options);
	if (errors.length) {
		console.error(errors.join('\n'));
		process.exit(1);
	}

	const root = resolve(values.root ?? join(import.meta.dirname, '..'));
	try {
		const { changed, renames, pluginSlug } = initProject(root, options, { dryRun: values['dry-run'] });
		console.log(`${values['dry-run'] ? 'Would update' : 'Updated'} ${changed.length} files. Plugin renamed to ${pluginSlug}.`);
		for (const path of changed) console.log(`  write ${path}`);
		for (const [from, to] of renames) console.log(`  rename ${from} -> ${to}`);
		if (!values['dry-run']) console.log('Next: pnpm install && composer install, then commit.');
	} catch (error) {
		console.error(error.message);
		process.exit(1);
	}
}

if (process.argv[1] && resolve(process.argv[1]) === import.meta.filename) main();
