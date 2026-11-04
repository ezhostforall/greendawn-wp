# Agency framework baseline audit — 2 November 2026

Scope: M0 and the first v0.4.0 foundation PRs. This is a source audit, not approval to deploy or promote a release. Decisions below are proposed for review through PRs.

## Verified baseline

- Default branch `main`: `81924c7cb445e51df46e38c466964c3726c0a0e6`.
- Source package, theme constant/header and both plugin constants/headers: **0.3.0**. README previously said 0.2.0; this documentation PR corrects that drift.
- GitHub release **v0.3.0** is published, non-draft and stable. Assets: theme, Greendawn Core and EZ Host Updater ZIPs, `updates.json`, `checksums.sha256`.
- GitHub's latest main CI and manual promotion runs passed. A hosted staging smoke run also passed. These are historical workflow results, not new staging verification.
- The brief reports all three native WordPress updates installed successfully. Installed staging versions, current candidate pins, backups, editor behaviour and rollback were not independently inspected in this audit.
- No open PRs or equivalent roadmap issues existed when checked. Issue #7 is closed; do not revive Codespaces WordPress as a daily development requirement.
- Working source was retrieved through the GitHub connector because a direct private clone had no credentials. Repository credentials were not copied into this environment.

## Current implementation and gaps

| Area | Source evidence | Gap / next action |
|---|---|---|
| Theme | PHP hierarchy, template parts, core menus/logo, escaped output | Basic navigation only; no nested disclosure, resize lifecycle, CTA/utility slots or complete footer system |
| Editing | `theme.json`, three core-block patterns, SCF helpers | No systematic editor/frontend parity checks or section-level editorial contracts |
| Tokens | Palette/system font/layout in JSON; duplicated CSS literals | Make JSON authoritative; preserve CSS aliases to avoid breaking consumers |
| Assets | Vite manifest loader; main/editor/optional React entries; dynamic GSAP | Keep existing pipeline; complex client motion stays opt-in |
| Initialisation | Identifier rewrites, plugin renames, validation, dry-run | Starter repository URLs can become invented client URLs; hosted staging survives; updater metadata is rewritten; preview omits renames/README; validation can leave partial output |
| Site plugin | Disabled-by-default module registry; diagnostics REST capability check; environment safeguards | Extend only for evidenced client content models/integrations |
| Updater | Separate, unrenamed plugin; server-side allowlist and token; identity/checksum validation; candidate gate | All package versions still coupled by package/set-version/validator scripts and manifest schema |
| Releases | Tag creates prerelease; manual promotion; generated ZIPs/checksums | Preserve behaviour; do not bump/tag 0.4.0 until all acceptance gates are met |
| Tests | PHP helpers/updater rules; Node initialisation/config; browser/axe/React smoke; optional visual tests | Two-client build/package coverage, token parity, nested menu/no-JS/resize, editor and migration tests needed |
| Configuration | Placeholder-only example, ignored real configs, staging-only candidates | Client copies need explicit sources/staging rather than inherited agency URLs; document independent host setup |

## Architecture decisions

### ADR-001 — Design-token authority

**Proposed:** `theme/theme.json` owns palette, font families/sizes, spacing presets, content widths and custom radius/state tokens; WordPress emits their CSS variables in frontend and editor. Existing semantic CSS names alias those variables. Breakpoint conditions remain CSS literals because custom properties cannot be used in media-query conditions.

Alternative: maintain two token sets manually (current drift risk), or introduce a token compiler (unnecessary additional toolchain). Keep the existing visual defaults while adding configurable roles. Do not mistake a source-level alias check for verified editor parity.

### ADR-002 — Client identity and configuration

**Proposed:** one-shot initialisation plans and validates all edits and renames before writing. Add explicit optional repository and HTTPS staging arguments. Without a repository, the generated example has an empty updater allowlist. Never invent a repository from the client slug or inherit the agency staging host. Preserve the updater directory and bytes. Author metadata applies only to the client theme/site plugin.

Record initialisation in a tracked project identity file, supplementing the theme-name guard. Reject invalid/reserved identifiers, unsafe replacement values, target collisions and live configuration files before mutation. Dry-run lists every planned write/rename and does not create a marker. Generated projects need two-brand compile/package validation.

Alternative: create a universal runtime framework or parent/child theme updater now. Rejected: each client is an independent project; this work does not establish an automatic upstream inheritance mechanism.

### ADR-003 — Updater lifecycle

**Proposed for v0.4.0:** retain current separately packaged plugin and lockstep release format. The existing updater version is also the release-manifest version, ZIP version and expected archive header version. A standalone repository or independent version cannot be introduced just by changing the plugin header.

Assess independent-version alternatives separately before any migration. Stable/candidate routing, installed IDs, private token handling, checksum validation and manual approval remain intact. Client projects may bundle the same updater source, but must port reviewed fixes explicitly; source identity is shared, release lifecycles are not.

### ADR-004 — PHP and Gutenberg composition

**Proposed:** PHP templates and escaped template parts own structural presentation; core-block patterns own ordinary editable compositions. Structured fields/CPTs/taxonomies belong in the client site plugin where content requires them. Decide each bespoke section individually; avoid encoding an entire site in hardcoded PHP or creating a new block framework before concrete need.

Use attachment IDs and WordPress image functions for content media. Interactive components progressively enhance readable HTML. Optional plugins must remain optional.

### ADR-005 — Porting shared improvements

**Proposed:** template copies are independent. Record originating starter tag/commit, component versions and reviewed upstream patches in each client repository. Port shared fixes via small client PRs, adapt identifiers, run client tests and stage client-specific releases. Updater fixes receive a coordinated compatibility review across installations.

Alternative: automatic bulk syncing, shared runtime dependency or Git submodule. Deferred until the maintenance cost and compatibility requirements justify it. Never replace customised client files automatically.

## Greendawn reference mapping

Read-only reference: `ezhostforall/GreenDawn` at `891ed840c7369cb50df3e33232e81de49e2699d6`. Inspected page composition, tokens, config/content, motion runtime, submission mock, types and behavioural/visual tests. This is a migration map, not a completed visual or commercial-content approval.

| Astro source | WordPress destination / ownership |
|---|---|
| `UtilityBar`, `SiteHeader`, navigation config | Shared accessible menu/utility primitives; client menu content and brand |
| `HeroSection`, `TrustSection` | Client PHP/field composition using shared hero, responsive image, stats/logo primitives |
| `ProblemSection`, `FragmentedModel`, `SystemSection` | Client-specific presentation and opt-in motion modules |
| `ProcessSection`, `ProcessStep` | Shared ordered-step layout; client fields/copy |
| `FeaturedProjectSection`, `SupportingProofSection` | Client project queries/fields; shared cards/media; claims require approval |
| `SurveySection` and survey tier components | Client scope/pricing content; do not automatically expose unused tier data |
| `SolutionsSection`, row/image components | Client solutions/audiences; shared media/text/card layouts |
| `AftercareSection` | Client scope/terms; reusable layout only |
| `InsightsSection`, `InsightCard` | Core posts and client queries; shared cards |
| `FinalCtaSection`, `SiteFooter` | Shared CTA/footer slots; client content, legal links and routes |
| `LeadCapture`, lead types/controller/events | Client payload/UI; authenticated operational integrations remain outside the theme |
| `lead-capture/submit.ts` | Development mock logs personal lead payloads; replace with secure server submission before launch; no production console logging |
| `motion/runtime.ts`, animation modules | Preserve context/cleanup, image/font readiness, history and resize behaviour; retain reduced motion and page-specific loading |
| Tokens, brand images, responsive media map | Client `theme.json`, exact official assets and WordPress responsive attachment images |
| `home.spec.ts`, visual snapshots | Port navigation/lead/no-JS/overflow/history assertions and seven viewport references; review new WordPress baselines |

The brand source uses navy/slate/cream/lime/violet/coral with Bricolage Grotesque, Archivo and JetBrains Mono. Keep those definitions in the client repository. Source case-study numbers are evidence of existing content, not independent evidence of the underlying claims.

## Homecare requirements check

The supplied brief is the requirements source; this audit did not independently inspect the live Homecare site or private CRM. Its different visual identity and journeys validate prioritising navigation, flexible layouts, service cards, footer disclosure slots, editable patterns and enquiry contracts.

Use ordinary pages/taxonomies where adequate before creating service/location CPTs. Separate new-customer, existing-customer and commercial enquiries. Do not put CRM, engineer reports, payments or an AI system in the theme. Legal/product approval remains a publication gate; do not infer compliant wording from old website content.

## Backlog and proposed PR sequence

Shared issues carry priority, milestone, dependencies and acceptance evidence in GitHub:

| Issues | Scope | Sequence |
|---|---|---|
| #10 (WP-001) | Audit and five decisions | Documentation PR first |
| #11 (WP-002) | Client initialisation | Separate implementation PR |
| #12 (WP-003) | Updater assessment | Separate decision-only PR; no migration |
| #13 (WP-004) | Token authority | Separate implementation PR |
| #14 (WP-005) | Secure setup conventions | Review after initialisation/assessment |
| #15–#20 (WP-006–011) | Navigation, primitives, footer, components, media, patterns | M2 after foundation acceptance |
| #21–#22 (WP-012–013) | Client plugin, forms, analytics/SEO contracts | M6 informed by client pilots |
| #23 (WP-014) | Expanded browser/accessibility/responsive coverage | Across M2–M8 |

Greendawn source-mapping work is tracked in its existing repository as [GD-001](https://github.com/ezhostforall/GreenDawn/issues/1). GD-002–006 belong in its future WordPress repository, not the shared starter. Homecare has no confirmed implementation repository in the brief; defer issue creation there until its repository is identified/created at M3.

Deferred client backlog: HC-001 information architecture; HC-002 independent project; HC-003 homepage/services; HC-004 existing-customer journey; HC-005 service/location models; HC-006 CRM enquiry contract; HC-007 controlled publication checklist. GD and HC dependencies and acceptance remain as specified in the project brief; do not treat this deferred list as implementation completion.

## Dependencies and risk register

| Risk / dependency | Impact | Treatment / owner |
|---|---|---|
| Initialiser rewrites shared identity or sources | Incorrect client updates / links | WP-002; implementation maintainer; explicit sources and two-project regression tests |
| Client release versions diverge from bundled updater | Package/manifest rejection | WP-003; release maintainer; retain lockstep until tested migration |
| Token styles differ in editor / frontend | Misleading editing experience | WP-004/WP-011; frontend maintainer; real WordPress parity checks |
| Independent projects do not inherit fixes | Security/maintenance drift | ADR-005; agency maintainer; tracked client PRs |
| Premature abstraction delays client work | Delivery cost | Build shared features required by both pilots; postpone speculative frameworks |
| Astro-to-WordPress visual or lifecycle regression | Broken layout / interactions | GD-001; client frontend owner; preserve source/tests and compare viewports |
| Mock lead handling reaches production | Lost leads / exposed personal data | GD-006; client integration owner; secure backend and failure tests before launch |
| Homecare wording and integrations unapproved | Incorrect public claims / routing | Client business/compliance owners to confirm; separate approval gate |
| Missing host/repo credentials or setup | Acceptance/release blocked | Host/release owner; use secure environment configuration, never commit secrets |
| Automated tests mistaken for complete assurance | Accessibility/performance defects | Manual editor, keyboard, reflow, assistive technology, hosted performance and rollback checks |

## Versioning and release acceptance

Keep 0.3.0 source versions while preparing independently reviewable foundation PRs. After accepted changes and all required checks, use the existing version script for a 0.4.0 candidate; do not manufacture a stable release now. Candidate tags are exact pins, not moving channels; existing `v0.3.0` staging pins must be changed deliberately when testing the next candidate. Production stays stable-only.

M1 requires two independently initialised projects to build/package with correct identities and sources, code checks, release validation, and hosted WordPress acceptance. A passing source build is not evidence of successful update installation, editor parity, accessibility compliance, measured performance or rollback.

## Validation at audit baseline

- Frozen-lockfile JavaScript install, ESLint and TypeScript passed locally.
- Vite build, theme/plugin packaging and release checksum/component validation passed locally (three packages).
- Initial `pnpm check` stopped on the Node test's generated-PHP syntax check because PHP was absent in this runner; it did not establish a PHP code failure. PHP/Composer provisioning and CI verification are recorded in the implementation PRs.
- No WordPress database, live configuration, production site or Astro source was modified.

References: [baseline CI](https://github.com/ezhostforall/greendawn-wp/actions/runs/40768246539), [stable promotion](https://github.com/ezhostforall/greendawn-wp/actions/runs/40768516912), [historical hosted smoke](https://github.com/ezhostforall/greendawn-wp/actions/runs/40767894006).

## First execution cycle — preparation record

The following were prepared as separate PRs and have subsequently been merged; the original verification record is retained below:

- [Audit and decisions, PR #24](https://github.com/ezhostforall/greendawn-wp/pull/24).
- [Initialisation hardening, PR #25](https://github.com/ezhostforall/greendawn-wp/pull/25): 13 Node tests including two independently built/packaged client copies; all source checks passed. [CI passed](https://github.com/ezhostforall/greendawn-wp/actions/runs/41521037642).
- [Token authority, PR #26](https://github.com/ezhostforall/greendawn-wp/pull/26): 9 Node tests, local source/package checks passed; initial CI passed 14 browser tests including authenticated disposable editor parity and two brand overrides. Browser evidence is retained by the updated workflow; inspect the PR's latest run before merging.
- [Updater lifecycle assessment, PR #27](https://github.com/ezhostforall/greendawn-wp/pull/27): separate decision-only proposal, no migration.

After PHP/Composer were provisioned, baseline and implementation `pnpm check`, `composer run syntax`, `pnpm package` and `node scripts/validate-release.mjs` passed locally. PHPUnit: 11 tests, 31 assertions. Frozen JavaScript lockfile install succeeded. CI also verifies locked production dependencies. Composer installation still reports a pre-existing stale lockfile hash; [the tooling backlog](https://github.com/ezhostforall/greendawn-wp/issues) tracks reconciliation separately from runtime changes.

Remaining: review/accept foundation decisions and PRs, resolve configuration/tooling follow-ons, then prepare a 0.4.0 candidate and perform hosted editor/native update/rollback acceptance. Responsive navigation and the reusable UI milestone start after that foundation acceptance. Homecare repository identification and client issue creation remain M3 work; no client repository, production site or WordPress database was changed.

## Integration and v0.4.0 preparation

PRs #24, #25, #26 and #27 were reviewed/merged by the maintainer. The integrated default branch at `5b54ba2d8221e20e42e85e0fa4fb0fc8dcb40f26` passed [all CI jobs](https://github.com/ezhostforall/greendawn-wp/actions/runs/41524574506); local checks passed 14 Node tests, 11 PHPUnit tests/31 assertions, builds, packaging and checksums. Issues #10–#13 are complete; hosted release acceptance is a separate gate.

The remaining foundation fixes are [Composer lock consistency, PR #29](https://github.com/ezhostforall/greendawn-wp/pull/29) and [client configuration conventions, PR #30](https://github.com/ezhostforall/greendawn-wp/pull/30). Merge/review both before tagging the 0.4.0 candidate. Configuration checks now evaluate the PHP example through a disposable bootstrap and exercise Git ignore behaviour; no real installation configuration is executed or edited.

The release-preparation change sets source/package headers to 0.4.0 and supplies [the candidate acceptance checklist](releases/0.4.0.md). Publishing the candidate, native hosted updates, saved block/editor checks and rollback are not complete merely because these source changes pass. The existing 0.3.0 release stays stable/latest until explicit promotion of the same tested candidate assets.

The responsive menu issue, full UI/editor composition system and client implementations remain in M2 and later work; this release does not claim v1.0.0 or client launch readiness.
