# WP Starter

**A reusable, production-oriented WordPress theme and plugin development starter by EZ Host For All / Newify.**

WP Starter provides a client-agnostic foundation for building modern WordPress websites using conventional PHP templates, WordPress's native content management features, TypeScript, Vite and optional React and GSAP integrations.

The project separates **code development**, **automated testing**, **staging validation** and **production release management**.

Developers write and build code in GitHub Codespaces. A permanent WordPress staging installation provides real-world previews and update testing. GitHub Actions handles automated quality checks, disposable integration tests and package generation.

## Project links

| Resource | Location |
|---|---|
| GitHub repository | https://github.com/ezhostforall/wp-starter |
| Permanent staging website | https://wp-starter-theme.ezhostforall.com/ |
| Staging WordPress Admin | https://wp-starter-theme.ezhostforall.com/wp-admin/ |
| GitHub Actions | https://github.com/ezhostforall/wp-starter/actions |
| GitHub Releases | https://github.com/ezhostforall/wp-starter/releases |
| Detailed staging guide | [docs/staging.md](docs/staging.md) |
| Deployment guide | [docs/deployment.md](docs/deployment.md) |
| Update system documentation | [docs/dashboard-updates.md](docs/dashboard-updates.md) |
| Header and navigation | [docs/header-navigation.md](docs/header-navigation.md) |
| Layout and typography | [docs/layout-typography.md](docs/layout-typography.md) |

**Current source version:** `0.5.0`

**Release status:** v0.5.0 is prepared for staging validation of the merged header/navigation and layout/typography foundations. Approved stable remains v0.4.1. Publish only after review and green main CI, then follow the [v0.5.0 staging checklist](docs/releases/0.5.0.md). Existing stable assets remain unchanged.

**Licence:** GPL-2.0-or-later — see [LICENSE](LICENSE).

---

## 1. Objectives

This starter is designed to provide a consistent foundation across independently maintained client websites.

Its principal objectives are:

- **Reusability:** Start a new client project without rebuilding standard WordPress architecture.
- **Maintainability:** Use conventional PHP templates, modular components and a documented frontend pipeline.
- **Performance:** Server-rendered HTML, compiled assets and optional JavaScript features loaded only where needed.
- **Accessibility:** Semantic HTML, keyboard navigation, reduced-motion support and automated accessibility tests.
- **Security:** Escaped output, capability checks, secure release downloads and controlled deployment.
- **Developer experience:** A consistent Codespaces environment without requiring a local WordPress server.
- **Testing:** Automated source checks, integration tests and independent hosted staging validation.
- **Release management:** Versioned theme and plugin ZIPs, checksums, prerelease candidates and manual production approval.
- **Client independence:** Each client project can maintain its own branding, functionality, staging installation and release lifecycle.

The starter is deliberately not a complete site builder, hosting management platform or WordPress deployment service.

## 2. Technology stack

| Technology | Purpose |
|---|---|
| WordPress | CMS, content administration and PHP rendering |
| PHP | Templates, backend functionality and integration hooks |
| `theme.json` | WordPress design tokens and editor integration |
| TypeScript | Frontend application logic |
| Vite | Asset compilation, bundling and development tooling |
| CSS | Styling, components and responsive layouts |
| React | Optional interactive components |
| GSAP | Optional animation functionality |
| PNPM | JavaScript package management |
| Composer | PHP development dependencies |
| PHPUnit | PHP unit testing |
| PHP_CodeSniffer / WPCS | WordPress coding standards |
| ESLint | JavaScript and TypeScript linting |
| Playwright | Browser and integration testing |
| axe-core | Automated accessibility checks |
| GitHub Actions | Continuous integration, packages and release workflows |
| GitHub Releases | Versioned distribution of approved theme and plugin packages |

### Minimum requirements

- WordPress 6.6 or newer.
- PHP 8.1 or newer for the theme and included plugins.
- Node.js 22 or newer for development and building.
- PNPM using the version specified in `package.json`.
- Composer for PHP development tooling.
- Git and GitHub access.

CI currently tests PHP using version 8.3.

Node.js, PNPM, TypeScript and Vite are development dependencies. They are **not required on a production WordPress server** when installing compiled ZIP packages.

---

## 3. Development architecture

The development environment deliberately separates the source repository from the running WordPress installation.

```text
GitHub Repository
ezhostforall/wp-starter
        |
        v
GitHub Codespaces
- PHP development
- CSS / TypeScript
- React / GSAP
- Linting and unit tests
- Asset builds
        |
        v
GitHub Actions
- PHP checks
- Frontend checks
- Dependency audit
- Disposable WordPress integration tests
- Versioned ZIP packaging
        |
        v
Permanent WordPress Staging
wp-starter-theme.ezhostforall.com
- Real WordPress administration
- Theme previews
- Plugin functionality
- Editor and content testing
- Update and rollback testing
        |
        v
Candidate GitHub Release
- Versioned theme ZIP
- Versioned plugin ZIPs
- Update metadata
- SHA-256 checksums
        |
        v
Manual Staging Approval
        |
        v
Approved Stable GitHub Release
        |
        v
Client WordPress Websites
- Manual update installation
- Client-specific acceptance checks
```

### Environment responsibilities

| Environment | Responsibilities | Persistent WordPress? |
|---|---|---|
| Codespaces | Write, review, validate and package code | No |
| GitHub Actions CI | Automated integration tests using disposable WordPress | Temporary only |
| Permanent staging | Browser previews, Admin, content, plugins and release testing | Yes |
| Production client websites | Approved theme and plugin versions | Yes |

**Important:** Changes pushed to GitHub do not automatically modify the staging website. A built package must be installed or deployed to staging before changes become visible there.

No automatic production deployment is currently configured.

---

## 4. Repository structure

```text
wp-starter/
|
|-- theme/
|   |-- style.css
|   |-- theme.json
|   |-- functions.php
|   |-- front-page.php
|   |-- header.php
|   |-- footer.php
|   |-- page.php
|   |-- single.php
|   |-- archive.php
|   |-- search.php
|   |-- 404.php
|   |
|   |-- inc/
|   |   |-- setup.php
|   |   |-- assets.php
|   |   |-- vite.php
|   |   |-- react.php
|   |   |-- patterns.php
|   |   |-- security.php
|   |   |-- template-tags.php
|   |   |-- integrations/
|   |
|   |-- template-parts/
|   |-- patterns/
|   |-- acf-json/
|   |
|   |-- src/
|       |-- styles/
|       |-- scripts/
|       |-- react/
|       |-- assets/
|
|-- plugins/
|   |-- agency-core/
|   |-- ezhost-updater/
|
|-- mu-plugins/
|   |-- agency-core-env.php
|
|-- scripts/
|   |-- init-project.mjs
|   |-- set-version.mjs
|   |-- package.mjs
|   |-- validate-manifest.mjs
|   |-- validate-release.mjs
|
|-- tests/
|   |-- php/
|   |-- node/
|   |-- e2e/
|   |-- integration/
|
|-- .github/
|   |-- workflows/
|       |-- ci.yml
|       |-- release.yml
|       |-- staging-smoke.yml
|       |-- promote-release.yml
|
|-- docs/
|   |-- getting-started.md
|   |-- architecture.md
|   |-- staging.md
|   |-- testing.md
|   |-- deployment.md
|   |-- dashboard-updates.md
|   |-- plugin-integrations.md
|   |-- client-customisation.md
|
|-- package.json
|-- pnpm-lock.yaml
|-- composer.json
|-- composer.lock
|-- playwright.config.ts
|-- vite.config.ts
|-- tsconfig.json
|-- AGENTS.md
|-- README.md
```

The `theme/dist/` directory contains compiled frontend assets generated by Vite.

The `build/` directory contains installable ZIP packages and release metadata generated by the packaging script.

These generated directories do not need to be maintained manually.

---

## 5. Getting started with GitHub Codespaces

The recommended development environment is GitHub Codespaces using its default configuration.

This repository intentionally does not include a custom `.devcontainer` configuration.

### Step 1: Open the repository

Visit:

https://github.com/ezhostforall/wp-starter

Select **Code → Codespaces** and create or open a Codespace.

Alternatively, clone the repository into a suitable local development environment.

### Step 2: Install dependencies

From the repository root:

```bash
corepack enable
pnpm install --frozen-lockfile
composer install --no-interaction
```

### Step 3: Validate the project

```bash
pnpm check
```

This runs the configured frontend and PHP validation checks, including:

- ESLint.
- TypeScript validation.
- Node.js tests.
- Vite production build.
- WordPress coding standards.
- PHPUnit.

These checks do not require a running WordPress installation.

### Step 4: Build the installable packages

```bash
pnpm package
```

This generates theme and plugin packages in `build/`.

At the current source version, the expected packages include:

```text
build/
|-- wp-starter-0.2.0.zip
|-- agency-core-0.2.0.zip
|-- ezhost-updater-0.2.0.zip
|-- updates.json
|-- checksums.sha256
```

The ZIPs include compiled assets and exclude development-only source directories and dependencies where appropriate.

**Do not upload the entire GitHub repository to WordPress.** Upload the installable ZIP packages.

### Step 5: Preview changes

Install the packages on the permanent staging website:

https://wp-starter-theme.ezhostforall.com/

The staging site runs WordPress independently of Codespaces.

You do not need to start MariaDB, Docker or a forwarded WordPress port when developing normally.

### About the Vite development server

The command:

```bash
pnpm dev
```

starts Vite, not WordPress.

Vite serves CSS and JavaScript development assets. It does not execute PHP templates or provide a standalone WordPress homepage.

The theme supports Vite development integration only in appropriately configured local/development WordPress environments.

For the permanent hosted staging website, the recommended approach is to compile assets and deploy the resulting theme ZIP rather than expose Codespaces' Vite server.

---

## 6. Permanent WordPress staging environment

### Staging URL

**Frontend**

https://wp-starter-theme.ezhostforall.com/

**WordPress administration**

https://wp-starter-theme.ezhostforall.com/wp-admin/

This is the designated permanent testing installation for the WP Starter repository.

It is separate from both GitHub Codespaces and the disposable WordPress installation used in automated CI.

### Required staging configuration

The staging installation should have:

- HTTPS enabled and a valid TLS certificate.
- A working WordPress installation.
- A separate database.
- A staging administrator account with a strong, unique password.
- PHP compatible with the theme and plugins.
- File permissions allowing theme and plugin installations.
- Backups covering the WordPress database, uploads, themes and plugins.
- Search engine indexing discouraged.
- Appropriate access restrictions.
- No production payment credentials or live third-party integrations unless deliberately configured for safe testing.

The staging website's WordPress administrator credentials are **not** the disposable CI credentials defined in `tests/integration/setup.sh`.

### Set the WordPress environment type

Add the following to the staging installation's `wp-config.php`, before WordPress loads:

```php
define( 'WP_ENVIRONMENT_TYPE', 'staging' );
```

This is important because the updater restricts candidate release access to sites that WordPress identifies as staging environments.

Confirm that `WP_ENVIRONMENT_TYPE` is not already defined elsewhere before adding it.

Production installations should use the corresponding `production` environment designation.

### Set WordPress URLs

In **Settings → General**, confirm that both the WordPress Address and Site Address use the correct HTTPS URL:

```text
https://wp-starter-theme.ezhostforall.com
```

Avoid mixed HTTP/HTTPS configurations.

### Protect staging from indexing and misuse

In WordPress Admin, use **Settings → Reading → Discourage search engines from indexing this site**.

Also implement access protection at the hosting or application level where appropriate.

The WordPress indexing preference and a `robots.txt` directive do **not** provide access security.

Do not make the staging site publicly accessible solely to enable GitHub Actions testing.

---

## 7. Initial installation on staging

After building the repository, install the generated packages.

### Install the theme

1. Log into https://wp-starter-theme.ezhostforall.com/wp-admin/.
2. Open **Appearance → Themes**.
3. Select **Add New → Upload Theme**.
4. Upload `wp-starter-0.2.0.zip`, or the corresponding package for the version being tested.
5. Install and activate the theme.

Verify that the homepage renders, stylesheets load and JavaScript assets are served successfully.

### Install Agency Core

Navigate to **Plugins → Add New → Upload Plugin**.

Upload the generated `agency-core` ZIP and activate it.

Agency Core provides an optional modular foundation for site functionality. Its modules are inactive unless explicitly enabled through the documented hooks.

### Install EZ Host Updater

Install `ezhost-updater-0.2.0.zip` in the same way.

The updater uses native WordPress update mechanisms to discover releases from explicitly configured GitHub repositories.

It does not operate against arbitrary repositories and does nothing without its source configuration.

### Configure representative test content

Populate staging with realistic, non-sensitive example content to verify:

- Homepage components.
- Pages and blog posts.
- Navigation menus.
- Featured images and responsive media.
- Gutenberg blocks and patterns.
- React components where used.
- Mobile navigation.
- Forms and relevant integrations.
- Theme editor styles.
- Accessibility and responsive layouts.

The persistent staging site will not automatically contain the example pages generated for the CI test fixture.

---

## 8. GitHub Actions and automated testing

The repository includes automated workflows under `.github/workflows/`.

### Continuous integration

Workflow: `.github/workflows/ci.yml`

Triggered by pull requests and pushes to `main`.

| Job | Purpose |
|---|---|
| PHP | Syntax, WordPress coding standards, PHPUnit |
| Frontend | ESLint, TypeScript, Node tests and Vite build |
| Playwright | Browser tests using a temporary WordPress installation |
| Dependency audit | JavaScript and PHP dependency checks |
| Release packages | Build ZIPs and validate release metadata |

The CI integration environment is created automatically for the Playwright job.

It uses the disposable WordPress and MariaDB configuration in `tests/integration/`.

The containers and their data are removed when the job finishes.

**Developers do not need this environment running in Codespaces.**

### Testing commands

| Command | Purpose |
|---|---|
| `pnpm lint` | Run ESLint |
| `pnpm typecheck` | Validate TypeScript |
| `pnpm test:node` | Run Node tests |
| `pnpm lint:php` | Run PHP coding standards |
| `pnpm test:php` | Run PHPUnit |
| `pnpm build` | Compile frontend assets |
| `pnpm check` | Run standard development checks |
| `pnpm package` | Produce ZIPs and release metadata |
| `pnpm test:e2e` | Run browser integration tests against the CI fixture |
| `pnpm test:staging` | Run read-only checks against hosted staging |
| `pnpm test:visual` | Run optional visual regression tests |

### Optional local reproduction of CI integration tests

Only when debugging an integration failure:

```bash
pnpm build
pnpm test:wp:setup
pnpm exec playwright install --with-deps chromium
pnpm test:e2e
pnpm test:wp:down
```

This starts and removes a disposable test WordPress installation.

**Warning:** `pnpm test:wp:down` removes the disposable stack's volumes and test data. It must never be used against the permanent staging installation.

---

## 9. Automated staging smoke tests

Workflow: `.github/workflows/staging-smoke.yml`

The staging workflow is run manually from GitHub Actions.

It performs read-only Playwright tests against the independently hosted staging website.

### Configure the GitHub environment

In the GitHub repository, navigate to:

**Settings → Environments**

Create or select the environment:

```text
staging
```

Add this environment variable:

```text
WP_STAGING_URL
```

Value:

```text
https://wp-starter-theme.ezhostforall.com/
```

The workflow requires HTTPS.

### Optional HTTP Basic Authentication

If the hosting provider protects staging with HTTP Basic Authentication, configure these GitHub environment secrets:

```text
STAGING_BASIC_AUTH_USERNAME
STAGING_BASIC_AUTH_PASSWORD
```

These are only for HTTP Basic Authentication, not the normal WordPress Admin login.

Do not store WordPress administrator passwords in source code or public repository variables.

### Run the staging tests

Navigate to:

**GitHub → Actions → Staging smoke test → Run workflow**

The current suite checks:

1. The staging homepage returns HTTP 200.
2. The expected starter theme landmarks render.
3. Navigation remains on the configured staging origin.
4. The WordPress login page loads.
5. The login form's expected fields are visible.

These tests do not install packages, modify website content or submit WordPress administrator credentials.

The starter theme must already be installed and active before the homepage test can pass.

### Run manually

From a machine able to reach staging:

```bash
BASE_URL=https://wp-starter-theme.ezhostforall.com/ pnpm test:staging
```

Install the required Playwright browser dependencies first if needed:

```bash
pnpm exec playwright install --with-deps chromium
```

Passing the smoke tests does not replace manual testing of the editor, native WordPress updater, integrations or design.

---

## 10. Theme architecture

The theme uses conventional PHP-rendered WordPress templates.

This ensures that primary page content does not depend on JavaScript execution.

### Main responsibilities

**PHP templates**

Responsible for document structure, semantic HTML, page rendering and WordPress content output.

**`theme.json`**

Responsible for native WordPress editor configuration and global design settings.

**CSS**

Responsible for visual styling, responsive layouts, utility styles, component styles and presentation tokens.

**TypeScript**

Responsible for interactive frontend behaviour.

**React**

Available for specific interactive components where it materially improves functionality.

**GSAP**

Available for controlled animation and scroll effects.

### Asset management

Vite compiles source assets into hashed production files.

The theme uses the generated manifest to enqueue the correct scripts and styles through WordPress APIs.

After modifying frontend assets:

```bash
pnpm build
```

When deploying staging packages:

```bash
pnpm package
```

Do not manually edit generated files inside `theme/dist/`.

### React components

React is optional and component-specific.

The starter includes an example counter to demonstrate the integration.

A page can remain fully server-rendered while mounting a React component into a specific container.

See `theme/src/react/` and `theme/inc/react.php`.

### Animation

GSAP is optional.

Animations should respect `prefers-reduced-motion`, avoid unnecessary frontend loading and clean up event listeners or animation contexts appropriately.

---

## 11. Plugins and integrations

### Agency Core

`plugins/agency-core/`

A modular plugin scaffold for agency-level functionality.

The plugin provides a registry of optional modules and a reference administrator-only diagnostics endpoint.

Site-specific modules must be explicitly enabled.

### EZ Host Updater

`plugins/ezhost-updater/`

Provides native WordPress update discovery and package installation from approved GitHub Releases.

It supports configured private repositories, compatibility checks, package checksums and controlled release selection.

### Optional ordinary WordPress plugins

The starter includes integration points for:

- Rank Math SEO.
- Secure Custom Fields.
- Pressidium Cookie Consent.

These are **not bundled** into the starter's deployment packages.

Install only the plugins required for the individual project.

See [Plugin integrations](docs/plugin-integrations.md) for implementation and configuration guidance.

### MU plugins

The `mu-plugins/` directory contains optional environment safeguards.

These are not automatically included in the generated plugin ZIPs.

Deploy them deliberately when the corresponding environment needs their behaviour.

---

## 12. Native WordPress updates through GitHub

The updater is designed to let WordPress detect and install approved theme and plugin releases from GitHub.

Updates are not distributed by copying the repository's source files directly to client installations.

The package must be built, versioned and published.

### Source configuration

Configure approved GitHub sources server-side in `wp-config.php`.

For the WP Starter staging installation, a suitable configuration is:

```php
define(
    'EZHOST_UPDATE_SOURCES',
    array(
        'themes' => array(
            'wp-starter' => array(
                'repo'      => 'ezhostforall/wp-starter',
                'token_env' => 'EZHOST_GITHUB_TOKEN',
            ),
        ),
        'plugins' => array(
            'agency-core/agency-core.php' => array(
                'repo'      => 'ezhostforall/wp-starter',
                'token_env' => 'EZHOST_GITHUB_TOKEN',
            ),
            'ezhost-updater/ezhost-updater.php' => array(
                'repo'      => 'ezhostforall/wp-starter',
                'token_env' => 'EZHOST_GITHUB_TOKEN',
            ),
        ),
    )
);
```

Place the configuration before WordPress loads, and avoid defining the same constant more than once.

This configuration uses the repository's actual component names.

### Private repository access

If the repository is private, the hosting environment must supply a GitHub token that can read its release metadata and assets.

The configuration above expects a server-side environment variable named:

```text
EZHOST_GITHUB_TOKEN
```

Use a fine-grained GitHub token with **Contents: Read-only** access limited to the required repository, or an appropriately scoped GitHub App credential.

Never commit the token, store it in ordinary WordPress options or expose it in frontend JavaScript.

The hosting configuration must make the token available to PHP.

If a repository is public, the `token_env` entry can be omitted.

### WordPress update identification

The theme and managed plugins use their WordPress `Update URI` headers to opt into this custom updater mechanism.

The `.invalid` host used in these identifiers is deliberate. It identifies the update provider inside WordPress; it is not a real external update server.

### Security checks

Before handing a package to the native WordPress upgrader, the updater verifies relevant release information, including:

- Approved repository configuration.
- Component identity.
- Version compatibility.
- PHP and WordPress requirements.
- Expected ZIP filenames.
- SHA-256 checksums.
- Package structure.
- Trusted download destinations.

Automatic updates for managed components are disabled.

Installation is initiated manually through WordPress.

---

## 13. Candidate releases and staging approval

The release process separates candidate testing from production approval.

### Stage A: Prepare a version

Update all relevant package versions with:

```bash
node scripts/set-version.mjs 0.4.0
```

The version above is an example of a future release. Do not reuse an existing version or tag.

Then run:

```bash
pnpm check
pnpm package
node scripts/validate-release.mjs
```

Commit the changes and create a matching tag:

```bash
git add .
git commit -m "Candidate release 0.4.0"
git tag v0.4.0
git push
git push origin v0.4.0
```

### Stage B: Publish the candidate

Workflow: `.github/workflows/release.yml`

A valid version tag triggers the workflow to:

1. Verify that the Git tag matches the package version.
2. Build production assets.
3. Package the theme and plugins.
4. Generate update metadata.
5. Validate release checksums.
6. Publish a GitHub **prerelease**.

The candidate is not yet an approved production release.

### Stage C: Configure the candidate on staging

On the permanent staging WordPress installation, set:

```php
define( 'WP_ENVIRONMENT_TYPE', 'staging' );
```

For the component being tested, add a pinned candidate tag in `EZHOST_UPDATE_SOURCES`.

Example:

```php
'wp-starter' => array(
    'repo'          => 'ezhostforall/wp-starter',
    'token_env'     => 'EZHOST_GITHUB_TOKEN',
    'candidate_tag' => 'v0.4.0',
),
```

The same approach can be applied to individual plugin entries.

The updater only permits this candidate-tag behaviour when WordPress identifies the site as `staging`.

**Do not use `candidate_tag` on production websites.**

### Stage D: Test the update

For a meaningful update test, staging should initially have an older version of the component installed.

In WordPress Admin:

**Dashboard → Updates**

Refresh the update information if needed.

WordPress and the updater both cache update checks; successful release metadata is cached for 15 minutes by the updater.

Once the candidate appears:

1. Back up staging.
2. Record the installed component versions.
3. Install the candidate through WordPress.
4. Confirm the new versions and assets.
5. Test the frontend, responsive layouts and editor.
6. Verify relevant plugin functionality and integrations.
7. Check PHP errors, browser console output and broken requests.
8. Confirm that the previous package can be restored if required.

A successful ZIP upload alone does not establish that a release is ready for production.

### Stage E: Promote the tested release

Workflow: `.github/workflows/promote-release.yml`

Navigate to:

**GitHub → Actions → Promote tested release → Run workflow**

Enter the tested tag, for example:

```text
v0.4.0
```

Confirm the staging verification acknowledgement.

The workflow checks that the candidate remains a prerelease, downloads the already published assets, validates their metadata and checksums, then promotes the existing release to stable latest.

**The approved ZIP files are not rebuilt during promotion.**

This ensures that production receives the same package artifacts tested on staging.

### Stage F: Production availability

After promotion, properly configured client websites can discover the approved stable release through normal WordPress update checks.

Managed components still require manual installation.

Production rollout should be performed site by site, with backups and acceptance checks.

---

## 14. Generating a new client project

This repository is intended to act as a reusable template.

Each client's customised theme should normally be developed in its **own repository**.

Do not point independent client themes at the WP Starter repository as their long-term update source.

### Create a project

On GitHub, select **Use this template → Create a new repository** and copy the default branch. If the project needs a specific tested release, seed the new repository with that tag's source instead.

Open the new repository in Codespaces or clone it locally. With Node.js and PHP CLI available, preview the client setup:

```bash
node scripts/init-project.mjs \
  --name "Example Client" \
  --slug example-client \
  --repository example-agency/example-client \
  --staging-url https://staging.example.com/ \
  --dry-run
```

Replace the example values with the client project details. Review the file changes and renames, then rerun the command with `--dry-run` removed. The script sets client identifiers and update sources, renames the client plugin, and writes a project README and `project.identity.json`.

Run the initialiser once in a clean client checkout. If `--repository` is reported as unknown, the checkout contains an older starter snapshot. Check its source against the intended starter branch or tag before proceeding.

After initialisation, install dependencies and run the checks:

```bash
pnpm install --frozen-lockfile
composer install --no-interaction
pnpm check
```

See [client customisation](docs/client-customisation.md#initialise-an-independent-client-project) for optional flags and staging setup.

### Client-specific customisation

Typical project changes include:

- Branding and design tokens.
- Typography.
- Colours.
- Header and footer layouts.
- Homepage components.
- Custom page templates.
- Secure Custom Fields.
- Custom post types and taxonomies.
- Forms and integrations.
- Client-specific SEO configuration.
- Reusable content patterns.

Business-specific functionality should normally live in the client's site plugin rather than the presentation theme.

See [Client customisation](docs/client-customisation.md).

### Client release independence

Each generated client project should have its own:

- GitHub repository.
- Version history.
- Staging environment.
- Update source configuration.
- Release approval process.
- Backup and rollback procedures.

Shared agency components can be maintained separately, provided their update sources and compatibility requirements are explicitly managed.

---

## 15. Daily development workflow

The recommended sequence is:

### 1. Develop

Create a branch for the change.

Edit PHP templates, CSS, TypeScript or plugin code in Codespaces.

### 2. Validate

Run:

```bash
pnpm check
```

Resolve relevant lint, typing, test and build failures.

### 3. Package

Run:

```bash
pnpm package
```

Check that the required ZIPs and metadata are generated.

### 4. Review

Push the branch and open a pull request.

Review the implementation and automated GitHub Actions results.

Do not merge failing changes into `main`.

### 5. Preview on staging

Install the compiled packages on:

https://wp-starter-theme.ezhostforall.com/

Preview the change in the actual WordPress environment.

For routine development, this can be a manual deployment from the passing CI package artifact.

### 6. Verify

Run the staging smoke workflow where appropriate.

Perform manual testing of functionality that automated tests do not cover.

### 7. Prepare a release

Once changes are ready, create a new version tag and let GitHub Actions generate a candidate release.

### 8. Validate and promote

Test the candidate through native WordPress update installation.

Promote only after staging acceptance.

### 9. Deploy to client websites

Install approved releases manually and verify each client's website.

**GitHub commits, merges and tags do not automatically deploy changes to the hosted staging website or production clients.**

---

## 16. Deployment and rollback

### Supported deployment methods

Packages can be installed through:

- WordPress Admin ZIP uploads.
- WP-CLI, where available and appropriately authorised.
- SFTP or SSH deployment of built artifacts.
- Native WordPress updates through EZ Host Updater.

Automated SSH/SFTP deployment to the permanent staging host is not currently configured.

### Backups

Before significant theme or plugin updates, retain:

- Database backup.
- Relevant uploaded media.
- Current theme ZIP.
- Current plugin ZIPs.
- Any configuration required to restore integrations.

### Rollback

If an update introduces a regression:

1. Stop further rollout.
2. Identify the last known-good version.
3. Restore the previous verified ZIP or use the hosting provider's backup and deployment tools.
4. Clear applicable caches.
5. Verify frontend and administrator functionality.
6. Document the cause before releasing a replacement.

Rolling back code does not necessarily reverse database migrations or data changes.

Plan such migrations separately and ensure they have an appropriate recovery procedure.

---

## 17. Security and quality principles

### PHP and WordPress

- Follow WordPress Coding Standards.
- Sanitize untrusted input.
- Escape output in the correct context.
- Apply nonce verification to relevant privileged operations.
- Check user capabilities.
- Use explicit REST permission callbacks.
- Avoid placing client business logic inside presentation templates.

### Frontend

- Render core page content server-side.
- Avoid unnecessary JavaScript.
- Respect reduced-motion preferences.
- Provide accessible names and keyboard interactions.
- Avoid duplicate SEO metadata.
- Use optimized assets and responsive images.

### Dependencies

- Maintain PHP and JavaScript dependencies.
- Review dependency audit findings.
- Avoid unnecessary third-party packages.
- Build and verify artifacts in CI.

### Secrets

Never commit:

- WordPress passwords.
- GitHub access tokens.
- Database credentials.
- API keys.
- SMTP passwords.
- Production environment files.

Use appropriately scoped server-side secrets and GitHub environment secrets.

### Release management

- Do not distribute untested candidate builds to production.
- Keep production auto-updates disabled for managed components.
- Protect release repositories and version tags.
- Validate package checksums and component identity.
- Keep a documented rollback option.

---

## 18. Troubleshooting

### A change is visible in GitHub but not on staging

GitHub source changes are not automatically installed on WordPress.

Confirm that the matching build ZIP has been installed on staging.

Also verify that asset caches have been cleared and the active theme is correct.

### Vite shows a 404 page

Vite is an asset development server, not WordPress.

For a normal WordPress preview, open the permanent staging website.

### The staging smoke workflow cannot start

Confirm that the GitHub environment `staging` exists and contains:

```text
WP_STAGING_URL=https://wp-starter-theme.ezhostforall.com/
```

Check DNS, HTTPS, hosting access restrictions and any required HTTP Basic Authentication secrets.

### The staging homepage fails its browser test

Confirm that WP Starter is installed and activated.

The current staging tests expect the starter's header, main and footer landmarks.

A different or heavily customised theme may require different assertions.

### The updater doesn't show a release

Check:

- The updater plugin is active.
- The correct component is configured in `EZHOST_UPDATE_SOURCES`.
- The installed component has the expected `Update URI`.
- The GitHub repository token has appropriate read access.
- The release contains the expected ZIP and metadata files.
- The version is newer than the installed version.
- WordPress and PHP compatibility requirements are satisfied.
- The release channel matches the environment.

For candidate releases, confirm `WP_ENVIRONMENT_TYPE=staging` and that `candidate_tag` matches a published prerelease.

For production, use approved stable releases without `candidate_tag`.

### A candidate cannot be promoted

Confirm that:

- The tag has the expected version format.
- The tagged commit contains the matching package version.
- The GitHub release exists and remains a prerelease.
- The required ZIPs, `updates.json` and `checksums.sha256` exist.
- The artifacts pass validation.
- The workflow has the necessary GitHub permissions.
- The staging verification acknowledgement has been provided.

### WordPress cannot install a ZIP

Check available disk space, permissions, hosting restrictions, archive structure and WordPress compatibility.

Use the generated theme or plugin ZIP, not a GitHub source archive.

### WordPress Admin is slow

Investigate WordPress, PHP, database performance and outbound HTTP requests on the actual staging host.

Do not assume the performance characteristics of the disposable GitHub Actions fixture apply to the hosted staging installation.

---

## 19. Current implementation and outstanding setup

### Implemented in the repository

- Conventional WordPress starter theme.
- Native `theme.json` configuration.
- Vite and TypeScript build pipeline.
- Optional React and GSAP examples.
- PHP and Node testing.
- Automated Playwright integration suite.
- Disposable CI WordPress fixture.
- Theme and plugin ZIP packaging.
- Release metadata and checksums.
- Native GitHub release updater.
- Staging-only candidate release selection.
- Manual stable-release promotion workflow.
- Read-only hosted staging smoke workflow.
- Client project initialisation.
- Development and deployment documentation.

### Staging configuration to complete or verify

The permanent staging location has been provisioned at:

https://wp-starter-theme.ezhostforall.com/

Before treating it as fully operational, verify:

- [ ] WordPress is installed and accessible over HTTPS.
- [ ] WordPress Admin access is working.
- [ ] `WP_ENVIRONMENT_TYPE` is set to `staging`.
- [ ] Database and hosting backups are configured.
- [ ] Appropriate staging access restrictions are in place.
- [ ] Search indexing is discouraged.
- [ ] WP Starter theme ZIP is installed and activated.
- [ ] Agency Core is installed.
- [ ] EZ Host Updater is installed.
- [ ] GitHub update source configuration is completed.
- [ ] A suitable read-only GitHub repository token is configured server-side if needed.
- [ ] The GitHub `staging` environment contains `WP_STAGING_URL`.
- [ ] The staging smoke workflow passes.
- [ ] Representative test content is present.
- [ ] A real candidate update has been successfully tested.
- [ ] Rollback has been verified.
- [ ] The manual release-promotion process has been exercised.

Provisioning the staging URL does not, by itself, confirm completion of these checks.

### Planned improvements

Potential future work includes:

- Secure automated delivery of approved build artifacts to staging.
- More comprehensive authenticated WordPress Admin testing.
- Automated staging health and deployment checks.
- Expanded visual regression coverage.
- Additional plugin compatibility tests.
- More extensive release and rollback verification.
- Shared agency component version-management improvements.

These are not represented as completed functionality.

---

## 20. Further documentation

See the [framework baseline audit](docs/framework-audit.md) for the v0.4.0 backlog, proposed decisions, evidence and remaining acceptance gates.

| Document | Purpose |
|---|---|
| [Getting started](docs/getting-started.md) | Codespaces setup and initial development |
| [Architecture](docs/architecture.md) | Theme structure and technical design |
| [Staging](docs/staging.md) | Persistent WordPress staging and acceptance testing |
| [Testing](docs/testing.md) | Automated checks and test environments |
| [Deployment](docs/deployment.md) | Package installation, releases and rollback |
| [Dashboard updates](docs/dashboard-updates.md) | GitHub Release updater and approval workflow |
| [Plugin integrations](docs/plugin-integrations.md) | Rank Math, SCF and consent integrations |
| [Client customisation](docs/client-customisation.md) | Creating client-specific projects |
| [Coding agent conventions](AGENTS.md) | Coding requirements and project conventions |

---

## Maintained by

**EZ Host For All / Newify**

Repository: https://github.com/ezhostforall/wp-starter

Development and release workflow: GitHub Codespaces, GitHub Actions and GitHub Releases.

Permanent staging: https://wp-starter-theme.ezhostforall.com/

Licence: GPL-2.0-or-later.

WP Starter is intended as an actively maintained development foundation, not a finished client website or a substitute for project-specific testing, security review, accessibility assessment or deployment planning.
