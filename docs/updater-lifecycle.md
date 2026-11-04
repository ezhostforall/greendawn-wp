# Updater ownership and versioning assessment

Status: accepted through merged PR #27 for v0.4.0. Addresses WP-003. No migration or updater runtime change is included.

## Observed contract at v0.3.0

EZ Host Updater is already a separate installable plugin with a fixed directory, main file, namespace, text domain, Update URI host and server-side allowlist. It can update itself using the currently installed updater code. It is not embedded in the theme.

Release ownership is still coupled:

- `scripts/set-version.mjs` sets the package, theme and every bundled plugin to one version.
- `scripts/package.mjs` requires that equality and puts one root `version` in schema-1 `updates.json`.
- `scripts/validate-release.mjs` derives every expected asset name from that root version.
- `Updater::release()` requires manifest version to match the Git tag.
- `component_update()` advertises that release version for any mapped component.
- `pre_download()` derives `<component>-<release-version>.zip` and `archive_valid()` requires that version in the installed package header.
- Candidate tags accept exact `vMAJOR.MINOR.PATCH` tags on staging only; no moving prerelease channel or suffixed SemVer tag support exists in the installed updater.

Consequently, independently versioning the updater header without changing the whole contract would make packages fail validation or advertise the wrong version. Separate installability does not establish independent versioning.

## Options

| Option | Benefits | Compatibility / operating cost | Decision |
|---|---|---|---|
| Keep bundled lockstep releases | Existing updater, manifests, checksums, rollback and CI remain compatible | Repackages unchanged updater; independent client copies need explicit maintenance | **Use for v0.4.0** |
| Independent component versions in one repository | Less release duplication; one source tree | Requires schema evolution, dual readers, new version script/validator logic and old-updater bootstrap | Defer until operational demand justifies it |
| Dedicated updater repository with its own schema-1 releases | Independent source/release owner while retaining old schema for each updater release | Reconfigure each server's updater source and repository token permissions; retain install ID; coordinate client package ownership | Preferred first alternative if a later split is needed; prototype and approve separately |

## Decision and responsibilities

Retain the plugin's identity, current schema and per-project lockstep versioning for v0.4.0. Do not extract the updater for architectural neatness. Theme/client-plugin sources are client-owned; updater source remains agency-maintained, copied without renaming. Generated client examples may explicitly use the client repository for all bundled packages while this contract remains in force.

The agency release maintainer owns updater security fixes and a list of affected client installations. Client maintainers port reviewed source patches and release/test client-specific packages. One installation must have one authoritative allowlisted source per component. Never install a client updater package over a centrally maintained plugin by accident.

The GitHub source configuration lives in the host's private WordPress configuration, outside the database and source control. Repointing it is a host operation requiring correct read permissions for the new repository. Do not put live credentials in a migration PR.

## If a dedicated repository is later justified

This is a transition plan to validate, not authority to run it now.

1. Inventory installed updater versions, source repositories, host environments and component IDs. Establish the highest version among all client lockstep releases. The first central updater version must compare newer than all affected installed versions; choosing 0.4.0 blindly could strand a client already on 1.2.0.
2. Create a central release using the existing schema-1 metadata, exact plugin ID `plugin/ezhost-updater/ezhost-updater.php`, matching ZIP root/header and checksums. Do not rename the slug, main file, Update URI host, namespace or token interface.
3. Test the **old installed updater** downloading/installing that central package on disposable WordPress and dedicated staging with private-repository access. It must not need the new updater in order to fetch the new updater. No circular bootstrap dependency is acceptable.
4. Review new repository token access. Change only the updater entry in private `EZHOST_UPDATE_SOURCES`; client theme/site-plugin entries retain client repositories. Pin the central candidate only on staging. Confirm native discovery, download verification, installation and a second update check.
5. Change future client packaging to stop distributing a competing updater package only after the central source works. Exclude it from generic set-version operations and validate each release's actual component list. Old client assets remain available for recovery.
6. Promote only after explicit approval through the existing stable gate. Update production source configuration deliberately and perform manual native updates; no candidate pin or automatic update is introduced.
7. Roll back by restoring the known-good updater ZIP and its previous source entry from backup. WordPress does not advertise version downgrades automatically. Clear only the relevant WordPress/update metadata caches through an authorised maintenance process; do not alter content databases as part of the migration.

For independently versioned components inside one manifest, first ship an old-compatible lockstep updater that understands both schemas. Keep old schema-1 feeds available until every supported installation can read the new schema; define mixed-version comparison, asset naming and downgrade policy explicitly. This is a larger change than using separate schema-1 updater releases.

## Required migration compatibility matrix

| Scenario | Evidence required before migration |
|---|---|
| Old v0.3.0 updater → central schema-1 package | Native update offered, correct installed plugin ID, successful checksum/header validation and installation |
| Client with newer lockstep version → central package | Version comparison offers intended upgrade; no assumed numeric reset |
| Unconfigured or wrong component | No interception; theme and client plugin sources unaffected |
| Stable-only production | Prerelease/candidate source rejected; managed automatic updates remain disabled |
| Staging exact candidate → stable | Pin selects only its tag; removal selects approved latest stable; stale tag behaviour documented |
| Private release and redirect | Correct token scope; signed redirect receives no GitHub credentials |
| Corrupt checksum, ZIP root, header, traversal or requirements | Download/install rejected without overwriting installed component |
| Source/token/network error | Installed site remains usable; diagnostic output contains no token or personal data |
| Interrupted update / repeat check | Recovery and subsequent update check succeed without a circular dependency |
| Rollback | Previous ZIP/source restores functionality; no database/content migration required |
| Mixed schema (only if chosen) | Old and new readers tested against both supported feeds and intentional rejection paths |

Current PHPUnit coverage tests source gating, redirects, checksum uniqueness, compatibility and unconfigured download handling. It does **not** yet prove real archive extraction, private download redirects, full self-update, mixed-schema migration or rollback on WordPress. Those are future migration gates, not completed tests.

## Release and approval boundary

Keep the existing tag → prerelease → hosted staging verification → manual stable promotion sequence. An exact staging candidate pin must be changed for each next candidate. Production uses approved stable releases only. No live configuration, release asset, installed plugin or database is changed by this assessment.
