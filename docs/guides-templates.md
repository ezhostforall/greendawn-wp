# Guides templates and discovery (PR3)

This increment connects PR1's content model and PR2's component library to three public layouts. It provisions no website content, changes no existing Page template assignment and imports no XML. Existing Posts, service templates and campaign routing retain their owners.

| Content | Template | Assignment |
|---|---|---|
| Guides hub | `theme/templates/greendawn-guides.php` | Assign **Greendawn Guides** to the intended Page. That Page owns its permalink, normally `/guides/`. |
| Guide | `theme/single-gdguide.php` | Selected automatically for the Guides CPT. Existing `/guides/{slug}/` routing and collision guards remain in Core. |
| Regional service | `theme/templates/greendawn-region.php` | Assign **Greendawn Regional Service** to the intended Page; no regional routes are created automatically. |

Both Page templates expose their existing SCF groups and hide the ordinary body editor through PR1's scoped editor controls. All substantive copy, media, evidence and relationships use the canonical fields in `guides-content-model.md`. Shared interface labels such as Search, Related guides and Places we serve are translatable theme strings. No review dates, project claims, local offices or survey prices are invented to fill a layout.

The reading CSS bundle is enqueued only for these three layouts. The templates share the existing header/footer, shell, image markup, FAQ disclosures, drawer transport and campaign hooks. No new JavaScript entry, form, consent pipeline or SEO output is introduced.

## Discovery contract

Core's `Guide_Discovery::search($search, $topic, $page)` returns a separate `WP_Query`, restricted to published, non-password-protected Guides. Search is a literal, case/collation-dependent substring of the existing private SCF text projection, capped at 200 characters. It includes the projected title, introduction, answer, section/list/table text, FAQ copy and assigned topic names. SQL wildcard characters are escaped by `WP_Meta_Query`. This does not replace WordPress blog search or add a public API.

Search and an exact topic slug combine. Unknown topic slugs return no results. Topic child records are not implicitly included. Results use title then ID order, at most 24 per page; native pagination appears when needed. Featured content is shown separately only on the unfiltered first page; the same Guide remains in the complete result collection. No result is silently excluded because it is featured.

The normal GET parameters are `guide_search`, `guide_topic` and `guide_page`. Search retains the selected topic; choosing a topic retains the search; either starts at page one. Clear filters returns to the owning Page permalink. Ampersands and other punctuation survive topic links. Empty state copy comes from the hub's fields. Malformed array parameters are ignored safely.

`Guide_Discovery::topics($ordered_ids)` aggregates counts against public Guide records rather than exposing native taxonomy counts, which may include password-protected records. Empty topics disappear. Editor-selected public topics appear first in their chosen order, followed by other populated topics alphabetically. Counts describe the public topic collection, independently of the current search.

## Reading and regional behaviour

Guide metadata uses Core's corporate author, actual optional review date and computed reading estimate. One prepared section list supplies desktop sticky contents and the mobile native disclosure. The main reading column has a comfortable measure inside the site shell; wide tables scroll locally. FAQs and source metadata remain native HTML. Curated related Guides retain saved order, omit unavailable records and do not invent a fallback selection.

Regional service summaries use the fixed icon variant. Local considerations use level-three headings beneath their section heading (`reading-sections.php` accepts only the optional fixed `heading_level: 3`; its default remains level two). Places, FAQs and related Guides disappear when empty. Project evidence requires a heading, copy and its published source URL; the image is optional.

Native password forms gate structured copy, imagery and discovery on protected Pages and Guides. Protected Guides never appear on public cards/counts, even for a browser with their password cookie. A permitted direct password view or authenticated Guide preview can read its own fields after the relevant WordPress access check. Missing SCF leaves saved native fields readable; missing Core returns empty adapters without fatal errors (Core still owns CPT registration/routes).

Sticky contents account for the collapsed header and native admin bar. The campaign notice bar remains in normal flow; the existing header alone becomes fixed on scroll. No duplicate campaign positioning or timer is added here.

## Verification and remaining increments

`tests/integration/guides-templates.php` is guarded to local WP-CLI and `http://localhost:8080`. It checks real structured fields, SCF-only searches, public counts, unknown topics, combined filters, literal SQL punctuation, bounded pagination and missing-plugin adapters. Temporary pagination records are removed; the remaining disposable records feed the real public templates in Playwright, without a template override. CI runs its browser suite with SCF disabled.

Browser coverage includes 1920/1440/1024/768/393/320px reflow, no-JavaScript search/navigation/disclosures, empty sections, password gates, related order, drawer focus return and mobile/desktop Axe checks. The existing component suite remains in place. The initializer also keeps the single template filename aligned when renaming the short Guide CPT token.

PR4 remains responsible for Rank Math metadata/schema/analysis, breadcrumb context, relevant legacy article selectors, drawer intent preselection and fuller campaign/conversion regression checks. PR5 supplies the scoped importer and pilot/full content files. There is no `data-lead-intent` preselection claim in PR3, and no second SEO graph.

After this PR merges, the first staging candidate should contain matching theme and Core assets. See `staging-guides-pr3.md` for the exact first-checkpoint procedure. Full content, schema and repeat-import approval remains the later checkpoint after PR5.
