# WordPress dashboard updates — GitHub Releases

The optional **EZ Host Updater** plugin provides WordPress-native theme/plugin updates from explicitly approved GitHub Releases. It uses the core `Update URI` hooks and `upgrader_pre_download`; no external update service or paid plugin is required.

The **starter repository is not the update source for a client's customised theme**. Each generated client project publishes and checks its **own** repository and release artifacts.

## Install the updater

Install `ezhost-updater-<version>.zip` once using WordPress Admin → Plugins → Add New → Upload Plugin (or WP-CLI). It is a normal, separately activatable plugin. It does **nothing** without configuration.

No release is published by a pull request. The **Prepare staging candidate** workflow publishes a GitHub prerelease for staging. Promotion to stable requires a separate manual workflow after testing the candidate on persistent staging.

## Configure approved sources

Put the following example in `wp-config.php` **before** WordPress loads. Adjust every slug, file path, and repository for your actual installed theme/plugins:

```php
define( 'EZHOST_UPDATE_SOURCES', array(
    'themes' => array(
        'example-client' => array(
            'repo'      => 'ezhostforall/example-client-theme',
            'token_env' => 'EZHOST_GITHUB_TOKEN',
        ),
    ),
    'plugins' => array(
        'example-client-core/example-client-core.php' => array(
            'repo'      => 'ezhostforall/example-client-theme',
            'token_env' => 'EZHOST_GITHUB_TOKEN',
        ),
        'ezhost-updater/ezhost-updater.php' => array(
            'repo'      => 'ezhostforall/example-client-theme',
            'token_env' => 'EZHOST_GITHUB_TOKEN',
        ),
    ),
) );
```

### Test a specific candidate on staging only

When testing a new version through WordPress Admin, add a pinned tag to the relevant entry in `EZHOST_UPDATE_SOURCES` **on the staging server**:

```php
// Staging-only example within the 'themes' array:
'example-client' => array(
    'repo'          => 'ezhostforall/example-client-theme',
    'token_env'     => 'EZHOST_GITHUB_TOKEN',
    'candidate_tag' => 'v0.3.0',
),
```

Configure `define( 'WP_ENVIRONMENT_TYPE', 'staging' );` on that WordPress installation. The updater requests `/releases/tags/v0.3.0`, accepts its prerelease metadata and verifies its ZIPs with the same checksum/identity rules as stable updates. It will only offer an update if the candidate is newer than the installed component.

**Production sites must omit `candidate_tag`.** If a site is not identified by WordPress as `staging`, a configured candidate tag is rejected for that component rather than falling back to the stable channel. The default configuration still requests `/releases/latest` and excludes prereleases.

Set the secret on the server, for example as an environment variable named `EZHOST_GITHUB_TOKEN`, or in a protected `wp-config.php` constant. No token is needed for public repositories: omit `token_env`. **Never commit** private tokens to the repository or store them in a regular WordPress option.

For private repositories, use a fine-grained token with repository **Contents: Read-only** for the specific release repository, or a suitable GitHub App token. This implementation reads tokens per request; if using short-lived GitHub App credentials, your server must refresh them externally.

The update plugin checks only components that appear in this allowlist and have an `Update URI: https://updates.ezhostforall.invalid/<slug>` header. The `.invalid` hostname is an intentional local identifier for WordPress's update hooks, **not** an internet update server.

The `wp-starter` theme and current `agency-core` plugin already declare these headers. `scripts/init-project.mjs` rewrites the theme and client plugin slugs when creating a new client repository. The `ezhost-updater` plugin is **never renamed** by that script.

## Candidate release, staging verification and promotion

1. Confirm CI passed on the current `main` commit.
2. Open **GitHub Actions → Prepare staging candidate → Run workflow**. Select `main` and enter a new version such as `0.5.1`, without the `v`.
3. Wait for the run summary to confirm the published tag, for example `v0.5.1`.

The workflow versions all components together, packages and validates them, and publishes a **GitHub prerelease**. A version-only commit is attached to the release tag, so no version-bump PR is needed. Use a version newer than the source and previously tagged versions. A retry can reuse the tag if it points to the same candidate commit. Existing releases and assets are never overwritten.

Each prerelease contains:
- One ZIP per component, named `<slug>-<version>.zip`.
- `updates.json` (component ID, version, compatibility requirements).
- `checksums.sha256` (hashes of the exact ZIPs).

The production updater's latest-release endpoint ignores prereleases. See GitHub's documented latest-release behaviour. There is no automatic promotion on a tag push.

**On persistent staging:** install an older supported component version, take a backup, set a pinned `candidate_tag` as shown above and use WordPress Admin → Dashboard → Updates to install the candidate. Test frontend, editor, plugins, PHP logs, relevant integrations and restoration of the previous ZIP. The updater's successful release metadata cache lives for 15 minutes; WordPress also caches its update results.

**After manual approval:** open **GitHub Actions → Promote tested release**, enter the tag and acknowledge the `staging_verified` checkbox. The workflow checks the release remains a prerelease, downloads the *existing* assets, validates hashes and metadata, then converts that same release to stable latest. It does **not** create a new ZIP. Only then can production sites using the default update channel discover the release.

The updater uses GitHub API read-only credentials, never sends those credentials to signed redirects, checks SHA-256, package structure, identity and compatibility, and hands the package to WordPress's native upgrader. **Automatic updates are disabled for managed components**; operators install approved versions manually.

The `production` GitHub environment can be configured with protection rules when available, but required-reviewer support for private repositories varies by plan. The workflow has manual attestation; it is not proof of an independent security approval. Protect repositories, tags and GitHub account access.

See [Staging](staging.md) for the full hosted staging and rollback checklist.

Use the [client installation checklist](client-installation.md) to assign environment ownership, review allowlists and move an approved candidate to stable-only production configuration. The bundled updater still shares the project's release version; a dedicated updater repository has not been introduced. See [updater lifecycle](updater-lifecycle.md).

## Troubleshooting

- **No update appears:** confirm the updater is active, the component has the correct Update URI header and allowlist key, and the version/compatibility match. Stable production ignores prereleases; staging requires the exact `candidate_tag`, an active prerelease and `WP_ENVIRONMENT_TYPE=staging`. WordPress caches update checks, and this plugin caches successful GitHub metadata for 15 minutes.
- **Private GitHub repository inaccessible:** confirm the token is supplied server-side with Contents: Read access to the right repository. No token or private URL appears on the frontend.
- **Download fails:** WordPress will report an error and leave the previous component installed. Verify server HTTPS connectivity to `api.github.com` and GitHub's signed release-asset host, package checksum and ZIP structure.
- **A package fails checksum/identity:** DO NOT bypass the check. Inspect the GitHub release artifacts and regenerate a clean release with an incremented version.
- **Rollback:** restore a verified previous ZIP through WordPress, SSH/SFTP or hosting backups. A rollback of files alone will not reverse database migrations; design any migrations separately.

## Security boundaries

Only configured repository names (owner/name), local component IDs and exact release assets are accepted. Never use branch archives, arbitrary `browser_download_url` values or assets supplied by request query parameters. Authorization headers are used only against the GitHub API; redirected signed asset requests have no credentials.

Do not treat the updater as proof of legal consent compliance, database backup, release approval or environment-wide automatic rollback.
