# Architecture

## Layout
- `theme/` – presentation only. `functions.php` loads files in `inc/` (setup, Vite manifest, asset enqueueing, template tags, patterns, security, integrations, React helper).
- `plugins/greendawn-core/` – optional, independently activatable plugin with a module registry. Nothing runs until a site enables modules via `greendawn_core_enabled_modules`.
- `mu-plugins/` – a single small file, justified because the safeguards must be on even if a plugin is deactivated. Rank Math, SCF and Pressidium are normal plugins and never live here.
- `scripts/`, `tests/`, `.github/` – tooling (no `.devcontainer/` override).

## Decision: Barebones vs a lightweight equivalent
[Barebones](https://github.com/benchmarkstudios/barebones) (Benchmark Studios, GPL-2.0-or-later) is a lightweight, conventional starter theme with Sass and block support. We evaluated its approach and chose a **lightweight equivalent** instead, because this starter needs a Vite/TypeScript/React pipeline, `theme.json`, default Codespaces support, a disposable integration-test stack and a rename script, and adapting an existing Sass/Webpack-oriented structure would add more change than starting small. No Barebones code is copied; its conventions (conventional templates, `inc/` organisation) informed the layout. If code is ever borrowed, retain its licence and attribution.

## Asset pipeline
- Vite builds three entries (see `vite.config.ts`): `main` (CSS + small TS, every page), `editor` (editor styles) and `react-example` (optional).
- Production: `theme/dist/manifest.json` is read by `inc/vite.php`; `inc/assets.php` enqueues hashed files with WordPress APIs and marks scripts `type="module"`. Without a build, the theme still renders (unstyled).
- Development: `pnpm dev` writes `theme/.vite-hot`; when `WP_ENVIRONMENT_TYPE` is `local`/`development`, the theme loads `@vite/client` and sources from the dev server for HMR.
- Page-specific assets: call `greendawn_enqueue_entry( 'name' )` from a template/template part, or return names from the `greendawn_page_entries` filter. Register the entry in `vite.config.ts` and `greendawn_entries()`.
- Code splitting: `main.ts` dynamically imports GSAP only when `[data-animate]` exists.
- Fonts: place WOFF2 files in `theme/src/assets/fonts/`, declare `@font-face` in a CSS file under `src/styles/` with `font-display: swap`, and reference the file by relative URL so Vite hashes it. Images referenced from CSS are processed the same way. Content images use WordPress responsive images (`srcset`/`sizes`, lazy loading).

## React (optional)
`template-parts/react-example.php` renders a mount element with JSON in `data-config` (`wp_json_encode` + `esc_attr`) and enqueues the `react-example` entry. `react/index.tsx` validates the JSON, mounts with an error boundary, and unmounts on `pagehide`. Component CSS is imported next to the component. Remove `src/react`, `inc/react.php`, the template part, the Vite entry and the React dependencies if a project does not need it.

## GSAP (optional)
`src/scripts/animations/reveal.ts` shows the pattern: `gsap.matchMedia()` with `prefers-reduced-motion: no-preference`, ScrollTrigger registered only inside the module, cleanup via `revert()`. Add `data-animate="fade-up"` to elements to use it. Add new modules beside it and dynamic-import them from `main.ts` behind a selector check.

## SEO, accessibility, performance
Semantic landmarks, skip link, labelled navigation, `aria-expanded` menu toggle, visible focus styles, reduced-motion CSS, `theme.json`-driven editor styling, one `<h1>` per page. SEO output (meta, canonical, Open Graph, sitemaps, schema) is delegated to Rank Math when used; the theme adds none. Extension points: `greendawn_entries`, `greendawn_page_entries`, `greendawn_scf_json_path`. Search/AI crawlers see fully server-rendered HTML. No ranking or Core Web Vitals outcomes are promised; measure each project.

## Security
Escaping on output, no direct input handling in the theme, `ABSPATH` guards, generator tag removed, dev server file ignored outside local environments, REST example with capability check, local-only credentials in `tests/integration/`, `pnpm audit` / `composer audit` in CI.

## Developer environment

The starter deliberately uses the default GitHub Codespaces environment: no custom `.devcontainer` and no forced container user. WordPress is not automatically provisioned when a Codespace starts. The Docker Compose stack under `tests/integration/` is disposable and intended for CI/browser integration tests only. Development **targets a persistent, separately hosted WordPress staging installation** for previews, WordPress Admin and update validation. CI continues to use the disposable container for regression checks; there is no need for WordPress/MariaDB in Codespaces. Version tags create candidate prereleases that must be verified on staging before manual promotion.
