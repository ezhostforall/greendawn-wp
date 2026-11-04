# Client customisation

1. Run the init command (see README) once per project.
2. Branding: edit `theme/theme.json` (palette, typography, spacing, layout and custom values). `theme/src/styles/tokens.css` contains aliases, not a second set of values. See [design tokens](design-tokens.md).
3. Remove what you do not need: example patterns (`theme/patterns`), `components.css`, React (`src/react`, `inc/react.php`, template part, entry in `vite.config.ts` and `wp_starter_entries()`), GSAP module, `consent.css`.

## Initialise an independent client project

PHP CLI is required to validate generated PHP before writing. Initialise a clean source copy once per project. Create the client repository and configure its own staging host. Preview the exact writes and renames first:

```bash
node scripts/init-project.mjs --name "Example Client" --slug example-client \
  --repository example-agency/example-client \
  --staging-url https://staging.example.com/ \
  --author "Example Agency" --author-uri https://agency.example.com \
  --dry-run
```

Run the same command without `--dry-run` after inspecting the plan. `--prefix`, `--text-domain`, `--hostname` and `--theme-uri` remain available. The theme stays in `theme/`; its installed ZIP directory uses the project slug. The client plugin and MU file are renamed. The updater keeps its own identity and source bytes, including author metadata.

The repository argument explicitly configures the theme, client plugin and bundled updater example sources. It does not create a GitHub repository, configure hosting, install packages on WordPress or verify repository access. Without it, the generated configuration has an **empty allowlist**. Without a staging URL, documentation uses a clearly invalid placeholder; it never inherits the starter's hosted staging address. The marker `project.identity.json` records the initial identity and starter version; retain it in client Git history.

Run only on a clean source copy. Private `wp-config*.php` and `.env` files (except examples), symlinks outside ignored dependencies, unsafe replacement values and rename collisions are refused before writing. Display names/author names containing quotes, backslashes or source-control characters must use a simple safe initialisation name; set the final display label in the appropriate metadata afterwards. A second initialisation is refused even if someone restores the old theme name. Validation catches PHP/JSON errors before writes, but filesystem failures during writes still require restoring the clean Git checkout; this is not a transactional filesystem tool.

```bash
pnpm install --frozen-lockfile
composer install --no-interaction
pnpm check
pnpm package
node scripts/validate-release.mjs
```

Real host configurations stay outside Git. Copy only reviewed environment/update settings into an existing WordPress configuration. Keep production sources stable-only. A staging `candidate_tag` pins an exact release and must be changed explicitly for the next candidate. Never copy database credentials or salts between installations.

Template-created projects do not inherit later starter fixes automatically. Record reviewed upstream patches in client PRs, adapt identifiers and validate against each client's own staging and release lifecycle. Updater source is shared, but the current packages remain versioned in lockstep within each project until a separately reviewed migration changes that contract.

## Bespoke templates
- Add `page-{slug}.php`, `single-{post_type}.php`, `archive-{post_type}.php`, or a template with a `/* Template Name: ... */` header. Use `get_header()`/`get_footer()`, put markup in `template-parts/`, and call `wp_starter_enqueue_entry()` for page-specific assets.
- Keep one `<h1>` and the existing landmarks.

## Site-specific plugin
Agency Core owns the generic Services post type and its fields. Enable **Services** in **Settings → Agency Core** if the project uses the generic model. The theme renders its detail route and Services overview. See [Services](services.md) for the editing and routing contract.

Place client-specific models, fields, integrations and business logic in the client site plugin or the project's Agency Core fork. If a project replaces the Services model, replace the module through `agency_core_available_modules` or disable it before registering a replacement. Keep a single owner of the `service` post type and its routes.

A site plugin can append a module while preserving any earlier module selections. In an initialised project, use its renamed hook prefix.

```php
<?php
/**
 * Plugin Name: Example Client Site
 */
defined( 'ABSPATH' ) || exit;

add_filter(
	'agency_core_enabled_modules',
	static function ( array $modules ): array {
		$modules[] = 'diagnostics';
		return array_values( array_unique( $modules ) );
	}
);
```

Packaging picks up every directory in `plugins/` that contains `<dir>/<dir>.php` with a matching version.

## Secure patterns
- Forms/AJAX: nonce (`wp_create_nonce` / `check_ajax_referer`), capability check, sanitise input, escape output.
- REST: always set `permission_callback` (see `Diagnostics`).
- Outbound webhooks: check `Agency_Core\Plugin::outbound_allowed()` so development and staging never call production services.

## Preview and release

Use a separate persistent WordPress staging site for client-specific templates, SCF fields, menus and update checks. Build and install the ZIPs; do not require a WordPress installation in Codespaces. Client repositories retain their **own** source allowlists and releases. See [Staging](staging.md) and [Dashboard updates](dashboard-updates.md). Never point client staging at production databases.
