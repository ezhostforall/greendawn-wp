# Testing

| Check | Command | Runs without WordPress? |
| --- | --- | --- |
| Composer manifest and lock consistency | `composer validate --no-check-publish` | Yes |
| PHP syntax, WPCS, PHPUnit | `composer run syntax && composer run lint && composer run test` | Yes |
| ESLint, TypeScript, JS unit tests | `pnpm lint && pnpm typecheck && pnpm test:node` | Yes |
| Vite build and manifest | `pnpm build` | Yes |
| Package ZIP integrity | `pnpm package && node scripts/validate-release.mjs` | Yes |
| Disposable Playwright integration | `pnpm test:e2e` | No; CI provisions fixture |
| Persistent hosted staging smoke | `BASE_URL=https://... pnpm test:staging` | No; external staging site |
| Visual regression (opt-in) | `pnpm test:visual` | No; consistent browser baseline required |

`pnpm check` performs the checks that do **not** need WordPress. **Do not install WordPress in Codespaces as part of normal development.**

CI validates Composer's manifest and lock consistency before installing PHP tooling. This repository is a development project, not a published Composer package, so validation uses `--no-check-publish`; a missing package name is not a release requirement. The inherited lock hash described a manifest with `name: ezhostforall/greendawn-wp`, although the tracked manifest had no name. The foundation fix refreshes that hash using Composer's own `Locker::getContentHash()` without changing any locked package or source reference. Future dependency changes must use Composer and retain a reviewed lockfile diff; do not suppress stale-lock warnings by ignoring validation.

## CI WordPress integration fixture

The GitHub Actions `e2e` job builds the theme, starts an isolated WordPress/MariaDB stack from `tests/integration/compose.yml`, seeds sample pages, runs browser/React/accessibility tests, and removes the test database and containers. No port-forwarded browser, administrator login or remote hosting is required. The tests assume that sample `/about/` and `/react-example/` content exists in this fixture.

Only to debug CI failures locally:

```bash
pnpm build
pnpm test:wp:setup
pnpm exec playwright install --with-deps chromium
pnpm test:e2e
pnpm test:wp:down
```

The fixture's `localhost:8080` exists only inside the test runner. It is **not** a persistent website. Vite's asset server (5173) does not serve WordPress HTML.

## Hosted staging smoke tests

Use **Actions → Staging smoke test** after deploying built ZIPs to your standalone WordPress staging site. Configure the `staging` environment variable `WP_STAGING_URL`. The separate Playwright project checks the homepage and WordPress login form without using administrator credentials or modifying data. Optional HTTP Basic authentication is supported through environment secrets. The CI fixture's hard-coded example pages are not required on staging.

**Manual staging acceptance remains mandatory** for native theme/plugin updates, visual design, editor functionality, integrations, email, forms and rollback. The smoke suite cannot prove those features are correct; see [Staging](staging.md).

## Accessibility and visual regression

Axe checks are useful but insufficient. Manually test keyboard navigation, dialogs, focus, contrast and assistive technology. Visual snapshots are sensitive to operating system and fonts; commit reviewed baseline snapshots before enforcing visual comparisons in CI.
