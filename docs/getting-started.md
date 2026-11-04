# Getting started

## Use the default Codespaces editor

This repository intentionally has no `.devcontainer/` folder. Codespaces is a code-editing and build workspace, **not a WordPress web server**. You do not need Docker, a WordPress installation, forwarded port 8080 or MariaDB for ordinary development.

```bash
corepack enable
pnpm install --frozen-lockfile
composer install --no-interaction
pnpm check
pnpm package
```

The output packages appear in `build/`. This produces theme/plugin ZIPs and verifies source without starting WordPress. `pnpm dev` starts Vite for JavaScript/CSS assets, **not** a standalone PHP WordPress website. Its root route may return a 404; that does not indicate a broken theme.

## Set up a separate persistent WordPress staging site

Provision a standard WordPress website on hosting you control. Enable HTTPS, keep it separate from production, set `WP_ENVIRONMENT_TYPE` to `staging`, secure access and take a backup. Install the ZIPs from `build/` or the CI packaging artifact, then preview the frontend and work in WordPress Admin there.

Use the staged candidate release workflow to verify native dashboard updates, rather than troubleshooting WordPress login or reverse-proxy behaviour inside Codespaces. See [Staging](staging.md), [Deployment](deployment.md) and [Dashboard updates](dashboard-updates.md).

## Automated tests

`pnpm check` covers lint, types, build, PHP and Node tests without WordPress. On pull requests, GitHub Actions independently spins up a **temporary WordPress/MariaDB instance** from `tests/integration/compose.yml`, seeds example content, runs Playwright browser regression checks and tears down the containers.

To reproduce that CI test fixture only when debugging an integration-test failure:

```bash
pnpm build
pnpm test:wp:setup
pnpm exec playwright install --with-deps chromium
pnpm test:e2e
pnpm test:wp:down
```

The fixture is bound to localhost port 8080 and uses disposable credentials. Running `pnpm test:wp:down` **deletes its test data**. It is not the staging website and should not be used for client previews. GitHub Actions already performs this sequence, so most developers never need to run it.

After provisioning staging, run the manual GitHub Actions **Staging smoke test** workflow or from an authorised network:

```bash
BASE_URL=https://your-real-staging-site.example pnpm test:staging
```

The staging smoke suite is read-only and requires a reachable site using this starter's markup. It does not deploy packages or authenticate to WordPress Admin. Configure the GitHub environment variable `WP_STAGING_URL` and optional HTTP Basic auth environment secrets as described in [Staging](staging.md).

## Next steps

Use the [client customisation guide](client-customisation.md) to build a client-specific theme and plugin, and follow the [staging-first release process](staging.md) before distributing updates.

Before provisioning a client site, work through the [client installation checklist](client-installation.md). A generated example does not configure hosting, credentials, GitHub environment secrets or production approval.
