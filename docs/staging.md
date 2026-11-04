# Persistent WordPress staging site

**Current persistent staging URL:** https://gd.ezhostforall.com/. **Initial candidate update walkthrough:** [v0.3.0 checklist](releases/0.3.0.md). The redacted [wp-config template](../wp-config.example.php) is reference material only; retain the actual server's database credentials, salts and table prefix.

**Next candidate preparation:** [v0.5.0 checklist](releases/0.5.0.md) covers the merged header/navigation and layout/typography foundations. v0.4.1 remains approved stable. This is a release plan, not evidence that v0.5.0 has been published or installed. Complete this staging batch before starting the footer foundation.

The everyday development environment is **Codespaces for code and packaging**, not a WordPress server. Maintain one **independent, persistent WordPress staging site** on your chosen hosting account for previews, WordPress Admin, real plugin/theme upgrades, and rollback rehearsals. GitHub Actions continues to run a disposable WordPress fixture for regression tests.

## Provision once

1. Create a dedicated WordPress installation on a staging subdomain. Use HTTPS, an isolated database and separate credentials from production. Never point it at a production database.
2. Set `WP_ENVIRONMENT_TYPE` to `'staging'` in `wp-config.php`. Protect access with hosting authentication or a suitable access policy, and enable WordPress's 'discourage search engines' setting. A robots directive alone does not secure staging.
3. Keep recent backups of database, uploads, current theme and plugins. Use test data and disable live payments, deliveries, outbound leads and production credentials.
4. Install the built theme ZIP and relevant plugin ZIPs from the **Packages** artifact of a passing GitHub Actions run (`pnpm package` creates the same local ZIPs under `build/`). Activate them manually on staging.
5. Install and configure the independent `ezhost-updater` plugin only if you want native WordPress update testing. Its repository allowlist and token are set in server-side `wp-config.php`; do not commit secrets.
6. Seed real preview content, navigation, media and representative custom fields. Do not assume that CI's disposable example pages exist here.

See [Deployment](deployment.md) and [Dashboard updates](dashboard-updates.md) for package layout and updater configuration.

## Daily development loop

1. Edit PHP, TypeScript, CSS or templates in a normal Codespace; run `pnpm check` and `pnpm package`.
2. Push the changes and review CI. Download the packaging artifact for that **specific commit**.
3. Upload the built theme/plugin ZIPs to staging, back up beforehand, then verify the design, responsive behaviour, admin screens and plugin functionality.
4. Run the optional **Staging smoke test** workflow from GitHub Actions (Actions → Staging smoke test → Run workflow). It runs only read-only Playwright checks and does **not** install anything or log in as an administrator.
5. Once approved, follow the release candidate and promotion procedure below.

Node/Vite HMR across a remote host is not required. Build ZIPs for reliable previews. WordPress runs only on staging hosting; CodeSpaces uses no WordPress/MySQL services.

## Configure the optional staging browser workflow

In GitHub repository **Settings → Environments**, configure environment `staging` and its **variable** `WP_STAGING_URL` with the full HTTPS origin, e.g. `https://staging.example.test/` (replace with your real resolvable staging domain). The workflow fails rather than guessing if the URL is missing.

If staging is protected with HTTP Basic Authentication, add **environment secrets** `STAGING_BASIC_AUTH_USERNAME` and `STAGING_BASIC_AUTH_PASSWORD`. Playwright uses these for HTTP access only; it does not submit WordPress Admin credentials. If staging is IP-restricted, permit the GitHub runner or run `BASE_URL=https://... pnpm test:staging` from an allowed network instead. Avoid making staging public merely to satisfy CI.

The smoke suite expects this starter's `header.site-header`, `main#content` and `footer.site-footer`, plus a standard `/wp-login.php` form. It is not a generic test suite for arbitrary WordPress themes; adapt these assertions if a client customises the markup.

## Accept a candidate on hosted staging

Record the candidate tag, source commit, installed theme and plugin versions, and the relevant CI run. Use populated content and record each result as passed, failed or pending with a link or screenshot when available.

- Check the homepage, Services overview and a populated Service, About, Contact, Blog, a representative post and a default Page. Check a category, search and a missing page.
- Check desktop and 320px plus a representative phone width. Check keyboard focus, skip and submenu behaviour, disclosures, horizontal overflow and responsive images. Record the LCP element and observed timing on the hosted site.
- Use an Editor account to create, preview and publish a Service on staging. Check media, FAQ order, structured page editing and the Blog editor. Confirm site-wide Brand settings are restricted to administrators.
- Check required-field feedback and confirm the mail destination is a test inbox before a synthetic successful submission. Record both the success message and receipt.
- Confirm native update discovery and installation for the candidate. Rehearse recovery with the previous known-good packages and reinstall the candidate before promotion.

The hosted smoke workflow covers availability. Use the acceptance record for these wider checks and track any gaps under [#23](https://github.com/ezhostforall/greendawn-wp/issues/23).

## Test a native WordPress update before releasing it to clients

1. Start with an **older known-good installed version** of the theme and each affected plugin on staging, and make a backup.
2. Bump the repository version, run checks and build packages. After review, push its `vX.Y.Z` tag. The release workflow publishes a **prerelease candidate**, deliberately excluded from the production updater's `/releases/latest` query.
3. On the staging site **only**, add `candidate_tag => 'vX.Y.Z'` to the appropriate theme/plugin entry in `EZHOST_UPDATE_SOURCES`. Keep the same allowlisted GitHub repository and server-side read-only token. See [Dashboard updates](dashboard-updates.md).
4. Use WordPress Admin → Dashboard → Updates to refresh update checks. The candidate version should be offered for the allowlisted components. Install it through the native WordPress updater, and check that the expected files and versions changed.
5. Test frontend, React/animations as applicable, admin/editor, theme settings, SEO, consent, forms, email and any relevant integrations. Confirm no PHP errors and rehearse restoration of the previous ZIP.
6. When satisfied, run **Actions → Promote tested release** with the same tag and check `staging_verified`. The workflow validates the **existing** candidate ZIP checksums and manifest, then converts it into the stable GitHub release marked latest. It does **not** rebuild or alter the tested artifacts.
7. Production sites without `candidate_tag` will now discover the approved stable release through normal manual WordPress update checks. Roll out client-by-client with appropriate backups and checks.

**Important:** The promotion workflow has a human attestation checkbox. You can additionally configure environment `production` protection and tag rules if available to your GitHub plan; do not assume required reviewers are available for a private repository. A repository administrator can still override the release process, so repository permissions and branch/tag protection matter.

## Boundaries

- This project does **not** provision a hosting account, copy WordPress databases, deploy to production, or configure secrets automatically.
- The disposable `tests/integration/` Compose installation is a CI fixture only. No Codespaces port forwarding or test administrator credentials are required in daily work.
- The updater accepts a pinned `candidate_tag` only when `wp_get_environment_type()` reports **staging**. Production sites that accidentally contain a candidate tag fail closed for that component instead of fetching a candidate or silently switching channels.
- Keep previous ZIPs and database backups. Code rollback does not undo database migrations.
