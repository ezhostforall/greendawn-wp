# Deployment

## Build
```bash
node scripts/set-version.mjs 1.0.0   # package.json, style.css, theme/plugin constants and headers
pnpm package                          # runs the build, validates the manifest, writes build/*.zip
```
`build/<slug>-<version>.zip` (theme) and one ZIP per plugin in `plugins/` are produced. Packaging fails if versions disagree or the manifest is invalid. ZIPs exclude `src/`, source maps, `node_modules`, `.env*`, logs and the dev-server marker; they contain no databases or credentials. The `mu-plugins/` folder is deliberately not packaged: copy it only where you want those safeguards. Production does not need Node.js.

For a hosted release, use **GitHub Actions → Prepare staging candidate** on `main` and enter the new version. The workflow packages the CI-verified source and publishes a **prerelease candidate** containing the ZIPs, `updates.json` and `checksums.sha256`. The existing tag-push path remains available for already versioned source. Install/test that exact candidate on persistent WordPress staging first. An explicit manual **Promote tested release** workflow validates the existing assets and changes the GitHub release to stable latest; client sites see only approved stable releases. See [Staging](staging.md) and [Dashboard updates](dashboard-updates.md).

## Options
- **Manual ZIP:** Appearance → Themes → Add New → Upload (and Plugins → Upload). Take a backup first.
- **SFTP/SSH:** upload/rsync the unzipped theme to `wp-content/themes/<slug>/`, e.g. `rsync -av --delete build/stage-extract/<slug>/ user@host:/path/wp-content/themes/<slug>/`. Test with `--dry-run` first.
- **Git-based:** build in CI and deploy the built artifact (or commit `dist/` to a deploy branch). Do not rely on building on the server.
- **Staging → production:** test a pinned candidate through WordPress's native updater on separately hosted staging, then promote the *identical* GitHub Release assets (no rebuild). Never overwrite the production database with staging data: production holds live content, orders, submissions and users. Move content changes deliberately.

Set `WP_ENVIRONMENT_TYPE` to `production`/`staging` in each environment's `wp-config.php`. Codespaces does not need WordPress. `tests/integration/compose.yml` is for disposable CI only; its credentials must never be deployed. Staging host provisioning and ZIP uploads are manual until a separate authorised deployment integration is configured.

## Rollback
Keep the previous ZIP for each release. To roll back: back up, upload/restore the previous theme and plugin ZIPs, clear caches, verify. Code releases do not change the database, so rolling code back does not touch content; if a plugin release added a migration, document its reverse before shipping.

## Checklist
Backup · version bumped · `pnpm check` green · candidate prerelease created · staging update and rollback verified · candidate promoted · cookie/consent and SEO reviewed · production manual update · smoke test · cache purge.
