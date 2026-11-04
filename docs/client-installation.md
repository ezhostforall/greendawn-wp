# Client installation and configuration ownership

This checklist covers the v0.4.0 foundation's setup contract. It does not provision hosting or attest that a real installation has passed acceptance. Assign named people to the roles below for each client.

| Role | Owns | Where it belongs |
|---|---|---|
| Client implementation maintainer | Slug, text domain, PHP prefix, brand, source, starter origin and reviewed upstream patches | Client repository and `project.identity.json` |
| Hosting administrator | Database, salts, HTTPS, environment, cookies/proxy configuration, backups, filesystem permissions, server-side tokens and source allowlist | Private host configuration; never committed |
| Repository maintainer | Repository access, CI, release assets, staging workflow variable and optional HTTP authentication secrets | GitHub repository/environments |
| Release reviewer/operator | Candidate acceptance, recorded evidence, rollback rehearsal and manual promotion | Release checklist and GitHub workflow |

One person may hold several roles. Record the actual owner and staging URL in client-specific operational documentation. Avoid creating a shared starter setting that points multiple clients at one database or update source.

## 1. Initialise the source

Create an independent client repository, then run the initialiser's dry-run with the explicit repository and client staging origin; inspect the plan before applying it. See [client customisation](client-customisation.md). Keep the tracked identity marker and originating starter version. Install dependencies and validate/build/packages before installing anything on WordPress.

Without `--repository`, the generated example has an empty updater allowlist. With it, the theme, client site plugin and bundled updater explicitly use that client repository. The updater keeps its installed identity. The lockstep release contract still applies; do not repoint it to a hypothetical central repository.

The shared example intentionally describes the starter's own staging installation. Client initialisation replaces its source/staging references. Read the generated example for the specific client; never copy the uninitialised starter's active source mappings into a client installation.

## 2. Preserve the host's working configuration

The example is reference material. On an existing installation, copy only the reviewed environment/updater settings into its private `wp-config.php` before WordPress loads. Retain its database connection, eight unique salts, table prefix, site URLs and required host-specific settings. Do not replace a working configuration wholesale.

Use separate staging and production databases, salts and credentials. Configure HTTPS on the host and verify the site's actual login, canonical URLs and cookie behaviour. Preserve any required trusted reverse-proxy setup; this template does not guess cookie domains, force URL constants or override proxy headers. Enable direct filesystem access only when appropriate for the hosting permissions.

Real `wp-config*.php` variants, including nested host backups, are ignored by Git; the root `wp-config.example.php` is tracked. Ignoring files does not remove secrets that were already committed. Inspect staged changes before each commit. Keep local `.env` values outside tracked source as well.

## 3. Select the environment and approved sources

Use exactly one `WP_ENVIRONMENT_TYPE` definition:

| Environment | Value | Updates and integration behaviour |
|---|---|---|
| Local asset development | `local` or `development` | Vite dev-server file may be honoured; no production integration credentials |
| Persistent client staging | `staging` | Explicit candidate pins permitted; outbound integrations must use test/sandbox destinations |
| Client production | `production` | Approved stable releases only; omit every candidate pin |

On staging, protect access, discourage indexing, use representative test data and disable live payments/deliveries/lead destinations until their sandbox routes are verified. The source plugin/MU safeguards must be reviewed for each integration; setting an environment label is not proof that all third-party plugins are safe.

The production updater excerpt below is illustrative. Replace every component ID and repository with the installed client values and add only the sources required. Keep the host's existing database/bootstrap sections:

```php
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'EZHOST_UPDATE_SOURCES', array(
    'themes' => array(
        'example-client' => array(
            'repo' => 'example-agency/example-client',
            'token_env' => 'EZHOST_GITHUB_TOKEN',
        ),
    ),
    'plugins' => array(
        'example-client-core/example-client-core.php' => array(
            'repo' => 'example-agency/example-client',
            'token_env' => 'EZHOST_GITHUB_TOKEN',
        ),
        'ezhost-updater/ezhost-updater.php' => array(
            'repo' => 'example-agency/example-client',
            'token_env' => 'EZHOST_GITHUB_TOKEN',
        ),
    ),
) );
```

For private repositories, supply the token through secure server environment storage or a private PHP constant with the configured name. Use repository-limited Contents: Read-only access. `token_env` holds the variable/constant name, not the token. Public sources can omit it. Never put credentials in browser bundles, GitHub URLs, normal WordPress options or committed examples. The hosting administrator owns token rotation and access checks; generated source does not provision credentials.

Check installed theme directory, plugin main-file IDs and `Update URI` headers against every allowlist entry. The updater does not infer a repository from a brand name and should not intercept unconfigured components. Managed automatic updates remain disabled.

## 4. Test the exact candidate

1. Take a recoverable backup of the database, uploads and installed packages. Retain the previous approved ZIPs and note each installed version.
2. Publish the reviewed candidate through the existing release workflow. Start native update testing with an older supported installed version.
3. Only on staging, set an exact `candidate_tag`, such as `v0.4.0`, on each intended source entry after that prerelease exists. A pin selects that tag; it never follows later candidates automatically. Update an old `v0.3.0` pin explicitly when testing the next version.
4. Use WordPress's native update screen. Allow for the updater's 15-minute successful-metadata cache and WordPress's update cache; refresh normal update checks. Do not disable checksum verification to work around a stale result.
5. Verify all three installed identities/versions, frontend, saved block editor content, no-JavaScript content, optional plugins and relevant integrations. Check logs without publishing tokens or personal data. Rehearse restoration of the previous packages.

A candidate pin on production is rejected for that component rather than falling back to stable. A wrong pin can therefore suppress updates. Keep production configuration free of pins, including after testing.

## 5. Record acceptance and promote

Record the tested tag/commit, CI run, installed versions, reviewer, date, checks/evidence, unresolved limits and rollback result. Automated CI is not approval of hosted content, claims, forms, integrations, accessibility or performance.

After explicit release approval, use **Promote tested release** with that exact tag and the staging-verification acknowledgement. It validates and promotes the same existing assets. Production then discovers the approved stable release through a manual update check. Review production source mappings, remove any candidate pins and roll out client by client with backups.

If an update fails, preserve the previous installation, inspect the WordPress error and restore known-good packages/source settings as needed. Reinstalling an older ZIP is a manual rollback; WordPress does not offer a downgrade automatically. File rollback does not reverse database changes.

## Hosted smoke workflow

The repository maintainer sets the client repository's `staging` environment variable `WP_STAGING_URL` to its full HTTPS origin. Put optional HTTP Basic Authentication credentials in that environment's secrets. The read-only smoke workflow checks public landmarks and the login form; it does not authenticate to WordPress Admin, install a package or change a database. Do not reuse the starter's host or CI's disposable administrator credentials for client staging.

See [staging](staging.md), [dashboard updates](dashboard-updates.md) and [updater lifecycle](updater-lifecycle.md) for operational detail.
