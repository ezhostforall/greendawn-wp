# Plugin integrations

Contact Form 7 is required for contact forms. Rank Math, Secure Custom Fields and Pressidium Cookie Consent remain optional. Install plugins through WordPress Admin or WP-CLI on the client site.

## Contact Form 7 (required)

Install and activate **Contact Form 7** from **Plugins → Add Plugin**, or run `wp plugin install contact-form-7 --activate` from the WordPress installation directory. Use a plugin release compatible with the site's WordPress and PHP versions.

The theme shows administrators a setup notice when the Contact Form 7 shortcode is unavailable. The notice links to WordPress's plugin search and installed plugins, and clears once the shortcode is available. The theme still renders during setup or if Contact Form 7 is deactivated.

On staging, verify that the notice is visible to an administrator with Contact Form 7 inactive, disappears after activation and is unavailable to an Editor.

## Rank Math SEO (free)
- The theme declares `title-tag` support and outputs no meta description, canonical, Open Graph, schema or sitemap, so Rank Math is the only source (no duplicates).
- Breadcrumbs: `get_template_part( 'template-parts/breadcrumbs' )` (already in page, single, archive, search and index templates). With Rank Math active, enable *Rank Math → General Settings → Breadcrumbs* and the template part calls `rank_math_the_breadcrumbs()` (accessible `<nav>` with structured data). Without it, a minimal semantic fallback renders with no structured data.
- Verify: view source for one `<title>`, one canonical, one set of Open Graph tags; check `/sitemap_index.xml`; validate structured data with Google's Rich Results Test.
- Do not add a second SEO system to the theme; extend Rank Math through its filters.

## Secure Custom Fields (free)
- Local JSON: the theme loads/saves field groups from `theme/acf-json` (filter `greendawn_scf_json_path`). Commit the JSON.
- **Recommended:** keep a client's content model in a site-specific plugin, point the filter at that plugin's `acf-json` directory, and register post types and options pages there so content survives a theme change.
- Helpers: `greendawn_get_field( 'name', $post_id, 'default' )` returns the raw value or a default (and works when SCF is inactive); `greendawn_the_field( 'name', 'text|html|url|attr' )` echoes escaped scalar values. Always escape raw values from `greendawn_get_field()` yourself.
- Options pages: register with `acf_add_options_page()` in the site plugin (guarded by `function_exists`), read with `greendawn_get_field( 'name', 'option' )`.
- Do not commit client field groups to the starter.

## Pressidium Cookie Consent (free)
- The theme adds no consent logic. Style the banner in `theme/src/styles/consent.css` (or delete it).
- Banner & preferences: configure in the plugin settings; preferences can be reopened from a link/button the plugin provides—add it to the footer template if the project needs it.
- Google Consent Mode v2 / GTM: set default consent states to denied *before* GTM loads and update them on consent, using the plugin's built-in Consent Mode support; load GTM only via the plugin/GTM so tags respect consent.
- Script blocking: mark non-essential scripts as the plugin documents (blocked until consent). Scripts enqueued by this theme are first-party and functional only.
- Withdrawal: test that revoking consent stops tags and clears cookies.
- A banner alone does not make a site compliant; legal review is the project's responsibility.

### Consent testing checklist
1. Fresh browser profile: no non-essential cookies or third-party requests before interaction (DevTools → Network/Application).
2. Reject all: still none; site functions.
3. Accept: tags/cookies appear; GTM Preview shows consent update (`ad_storage`, `analytics_storage`, `ad_user_data`, `ad_personalization`).
4. Change preferences / withdraw: tags stop, cookies removed, state persists on reload.
5. Keyboard and screen-reader operability of banner and preferences.
