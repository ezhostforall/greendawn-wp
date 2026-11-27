# First Guides staging checkpoint: after PR3

Build the candidate from the merged PR3 commit using the repository's existing prerelease workflow. This PR itself creates no tag, GitHub release, installed update or staging record. Do not promote the candidate to stable at this checkpoint.

## Install and prepare

1. On staging32, record the current theme/Core versions and take a database/uploads backup. Retain the previous matching packages. Preserve staging access restrictions and noindex settings.
2. Install the **theme and Greendawn Core from the same candidate**. Core now contains the discovery query, so updating only the theme is insufficient. EZ Host Updater has no source change in this PR. Use the existing explicit staging candidate configuration, not a production update source.
3. Confirm supported SCF is active for editing (the integration baseline is 6.9.8). Check that Guides and Guide Topics appear, that both Page templates are selectable, and that assigning them shows the correct field groups and removes the body editor only on those records.
4. Inventory any existing `/guides/` Page and regional routes before assigning templates. Do not overwrite or delete existing content for this checkpoint. Use temporary QA Page slugs such as `/qa-pr3-guides/` and `/qa-pr3-midlands/` where appropriate. The final route/migration audit and importer remain later work.
5. Create disposable QA records through normal WordPress/SCF controls on this protected staging site: a hub Page; two Guide topics; three published Guides; and one regional Page. Use unique Guide slugs such as `qa-pr3-site-power` so existing `/guides/` child Pages retain their URLs. Published test records are needed for public results; drafts must remain excluded. Do not add navigation links yet.

## What to put in the QA records

| Record | Representative fields |
|---|---|
| First Guide | Title; assigned primary topic; intro; distinct direct answer; three headed sections; checklist; a captioned four-column table; image and native alt text; FAQ; source URL; two curated related Guides; closing CTA. Leave reviewed/checked dates blank initially. |
| Other Guides | Different topics, useful minimum content and one unusually long title. Leave one image blank. Include a unique search word only in a section, list, table or FAQ, rather than in its title. |
| Hub | Hero copy/image; ordered topics; featured first Guide; search label; no-results heading/copy; optional enquiry and trust copy; closing CTA. |
| Region | Local intro; three service summaries/icons; considerations; named places; FAQ; curated national Guides; closing CTA. Leave evidence blank unless a verified published source supports the complete extract. |

Use temporary QA copy, with no survey price, unsupported grant promise or invented commercial evidence. Do not set an editorial review date simply because the record was created. The full reviewed content will come from PR5's import pack.

## Checks to perform

- **Layout:** all three templates at 1920 and 1440px; tablet at 1024 and 768px; mobile at 393px and 320px. Compare the outer shell to existing pages. Check long headings, no-image layouts, balanced cards, reading measure, date omission and table scrolling. Look for sideways page overflow.
- **Hub:** find each unique SCF-only word, combine search/topic, verify counts and ordered topics, clear filters and use browser back/forward. Unknown/empty filters should show the configured empty state. Draft, private and password-protected Guides must not enter cards or counts. Pagination is automatic beyond 24 matches; there is no need to create 25 staging records merely to repeat the automated bound check.
- **Guide:** title and corporate author; all saved reading copy exactly once; unique contents destinations; keyboard-operable mobile contents and FAQs; table caption/headings; native alt text; sources without fabricated dates; related links in selected order. Preview a draft while authenticated and check a protected record without its password.
- **Region:** distinct local copy, valid icons, consideration headings, coverage and correct related links. Clear optional sections and confirm their shells disappear. Evidence must disappear when its required source/copy is absent.
- **Shared behaviour:** header, footer and existing pages remain usable. Open and close the existing drawer from the new CTAs, then check focus returns. Confirm genuine fallback URLs with JavaScript disabled. Test the existing featured bar on the three layouts, including the desktop sticky header and mobile reflow. The fuller campaign timing, intent and submission verification belongs to PR4.
- **Editing/fallback:** save, reload and preview the SCF data. Briefly deactivate SCF only if suitable on this staging copy, confirm saved public content remains readable, then reactivate it. Do not deactivate Core on a real review site just to test missing CPT registration; automated fixtures cover the safe adapter behaviour.

Purge SiteGround/page caches after installing the matched candidate and after changing test records. Record candidate tag, component versions, WordPress/SCF versions, tested URLs, screenshots and any failures. Do not send test submissions to production recipients or count them as production ad conversions.

## Pass and rollback

A pass approves the template/content-field checkpoint, not production publication or the final SEO/import work. Keep the twelve unresolved editorial clarifications deferred. PR4 and PR5 follow before the final candidate/content import review.

If the candidate fails, restore the prior matching code packages and the affected QA records from the checkpoint backup as needed. Remove only the temporary records you created; retain pre-existing/shared Media Library items and unrelated content. Do not delete staging content as part of this PR.
