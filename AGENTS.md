# Instructions for AI coding agents

## Principles
- Conventional PHP-rendered WordPress theme. No page may require JavaScript to render; React/GSAP are optional and lazy.
- Keep the starter client-agnostic: no client branding, content types or field groups. Client logic belongs in a site-specific plugin.
- Small, surgical changes. Do not add frameworks, CSS frameworks, paid plugins or speculative features.

## Identifiers (must stay initialisable)
`scripts/init-project.mjs` rewrites these tokens: `greendawn` (slug/text domain), `greendawn` (function prefix), `GREENDAWN` (constants), `Greendawn` (docblock package), `Greendawn` (name), `greendawn.localhost`, and the plugin equivalents `greendawn-core`, `greendawn_core`, `GREENDAWN_CORE`, `Greendawn_Core`, `Greendawn Core`.
- Prefix every global PHP function, constant, hook and shortcode with `greendawn_` / `greendawn_core_`.
- Text domain strings must be exactly `'greendawn'` (single quotes). Do not use it for other quoted values.
- Do not introduce a new project-specific identifier without teaching `init-project.mjs` and `tests/node/init-project.test.mjs` about it.
- Initialisation records `project.identity.json`, accepts explicit `--repository` and `--staging-url`, and must validate all edits/renames before writing. Dry-run reports the same plan without writing. Preserve `plugins/ezhost-updater/` bytes and author metadata. Run initialisation only on clean source copies without private configuration.

## PHP
- WordPress Coding Standards (`composer run lint`), PHP 8.1+ syntax, compatible with 8.3.
- Escape late (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`), sanitise input, verify nonces and check capabilities for privileged actions. REST routes need an explicit `permission_callback`.
- Theme = presentation; business logic goes in `plugins/`.
- The theme must work when Rank Math, SCF and Pressidium are absent. Do not output SEO metadata the theme does not own.

## Frontend
- TypeScript strict mode, ESLint clean. Sources in `theme/src`; add new Vite entries to both `vite.config.ts` and `greendawn_entries()`.
- Load page-specific code with `greendawn_enqueue_entry()` or dynamic `import()`; never add always-loaded dependencies casually.
- Respect `prefers-reduced-motion` and clean up listeners/animations.

## Before finishing
Run `pnpm check`. Run `pnpm test:e2e` when a WordPress environment is available. Never claim a check passed unless it was run.

## Developer environment
- Do not add a `.devcontainer/` directory or force the Codespaces shell to run as root. Default Codespaces is intentional.
- **Daily preview/testing uses a persistent WordPress staging site on external hosting, not a Codespaces WordPress installation.** The disposable `tests/integration/compose.yml` stack exists only for CI integration tests or optional CI debugging; never require it for ordinary PHP/TypeScript builds.
- Do not revive obsolete `pnpm wp:install`, `pnpm env:up`, or related container-specific scripts. See `docs/getting-started.md`.

## GitHub Releases updater
- `plugins/ezhost-updater/` is an independent, unrenamed package. Never embed GitHub tokens or production URLs in theme/client source.
- Updater accepts only allowlisted components configured in `EZHOST_UPDATE_SOURCES` and uses WP native `Update URI` filters.
- Keep `scripts/package.mjs`, `scripts/validate-release.mjs` and GitHub release assets consistent: `updates.json`, `checksums.sha256`, versioned ZIP files.
- No client theme may automatically inherit the starter repository as its update source. See `docs/dashboard-updates.md`.
- Automatic updates of managed components remain disabled; deployment is staging-first.
- A version tag creates a **prerelease** candidate. Only the manual promotion workflow can mark the same verified assets as stable/latest.
- `candidate_tag` update sources are valid only on WordPress staging environments; do not expose candidate releases to production client sites.
- Persistent staging host configuration, credentials and WordPress content live outside the repository. Never commit secrets, automate production deployments by default, or overwrite production data.
