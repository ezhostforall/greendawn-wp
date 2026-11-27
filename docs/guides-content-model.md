# Guides foundation (PR1)

Greendawn Core owns the Guides content model. This increment adds registration, the three SCF groups, validated writes, native reads, revisions and derived reading/search data. It creates **no content**, changes no existing post type, sends nothing off-site and does not add public templates, navigation, schema or an XML importer. Those remain subsequent implementation stages. Do not import/publish the campaign's guide dataset before the dedicated templates are installed.

## Registration and routes

- `gdguide`: public singles under `/guides/{slug}/`, no archive, Page capabilities, title/revisions/custom-field support, no body editor or REST endpoint.
- `gdguide_topic`: hierarchical editor taxonomy, Page editing capabilities, no public topic archives. Assign topics through the native taxonomy box; the SCF primary topic must be one of those assignments at publication.
- The ordinary Page owns `/guides/`. Published/private legacy child Pages retain their routes through a scoped request adapter. A Guide cannot publish over a published/private/scheduled child Page; a child Page cannot publish over a public/private/scheduled Guide. Draft conflicts must still be resolved in the later content-import preflight.
- Rewrites refresh once using the non-autoloaded `greendawn_core_guides_rewrite_version` marker. Registration does not allocate slugs, provision terms, convert Posts or move Pages.

The field groups for the future `templates/greendawn-guides.php` and `templates/greendawn-region.php` Page templates are installed in advance. PR1 does not register missing theme templates as selectable options. Existing Page editing screens are unchanged unless explicitly assigned one of these templates.

## Canonical SCF contract

`Guide_Fields::VERSION` is `1`. `Guide_Fields::fields($kind)` supplies the canonical definitions and `Guide_Fields::find($key)` resolves a stable field key. The definitions drive validation and the native reader; a future generator/import adapter must use this same contract rather than guess flattened metadata.

Top-level keys are `field_` followed by the complete field name. Nested keys append the full ancestor path, while nested names remain local to their repeater. For example:

```text
name: greendawn_guide_sections
key: field_greendawn_guide_sections
row child name: body
row child key: field_greendawn_guide_sections_body
nested table cell key: field_greendawn_guide_sections_table_rows_cells_text
```

There are no WYSIWYG fields, layout controls, per-guide personal authors or automatic reviewed dates.

| Kind / field prefix | Fields, using short names |
|---|---|
| Guide / `greendawn_guide_` | `intro`, `image`, `image_position`, `primary_topic`, `reviewed_on`, `answer`, `sections`, `faqs`, `sources`, `related`, `cta_heading`, `cta_copy`, `cta_label` |
| Hub / `greendawn_guides_hub_` | `heading`, `intro`, `image`, `topics`, `featured`, `search_label`, `empty_heading`, `empty_copy`, `trust_heading`, `trust_copy`, `enquiry_heading`, `enquiry_copy`, `cta_heading`, `cta_copy`, `cta_label` |
| Region / `greendawn_region_` | `name`, `heading`, `intro`, `image`, `services`, `considerations`, `places`, `faqs`, `proof_heading`, `proof_copy`, `proof_image`, `proof_url`, `guides`, `cta_heading`, `cta_copy`, `cta_label` |

### Repeater rows

- Guide `sections` (maximum 60): `heading`, `nav_label`, `body`, `list_style`, `list_items[text]`, `table_caption`, `columns[label]`, `table_rows[cells[text]]`, `image`, `caption`.
- `columns` and each row's `cells` have a maximum of four entries. A table needs a caption, nonempty column labels, rows and equal cell/column counts.
- Guide `sources`: `title`, `publisher`, `url`, `checked_on`.
- Guide/region `faqs`: `question`, `answer`.
- Region `services`: `title`, `copy`, `icon`.
- Region `considerations`: `heading`, `copy`.
- Region `places`: `name`.
- Unless specified otherwise, repeaters are bounded at 100 rows. Related Guide lists have a maximum of twelve selections. No new survey pricing or project proof is seeded.

`image_position` permits `centre`, `upper`, `lower`, `left`, `right`; `list_style` permits `plain`, `numbered`, `checklist`; region service `icon` permits `bolt`, `site`, `fleet`, `property`. Dates are optional, valid past/present `Ymd` values. Images store local attachment IDs. Relationships store published Guide IDs. Hub topic selections are ordered term IDs and do not assign taxonomy terms to the Page.

## Content service

```php
use Greendawn_Core\Modules\Guide_Content;
use Greendawn_Core\Modules\Guides;

// Internal reader: normalised fields under their short names, including blanks.
$fields = Guide_Content::read( $post_id );

// Public Guide adapter: empty for drafts/private/password-protected records.
$public = Guide_Content::public_data( $post_id );

// Authorised partial update. Omitted fields are preserved; [] and '' clear fields.
$result = Guide_Content::write( $post_id, array( 'intro' => 'Updated copy.' ) );

// Call once after a later import/native metadata batch has completely finished.
Guides::commit( $post_id );
```

`write()` requires permission to edit that record and SCF. It rejects unknown top-level fields and validates the complete proposed record before writing any supplied field. It uses SCF's stable field keys. Ordinary draft saves can be incomplete; publication requires a title, introduction, direct answer, useful headed sections, an assigned primary topic and complete optional content rows. Hub and regional field requirements are enforced by the shared validation service/SCF flow. Raw native Guide publication without complete content is held as a draft with an administrator notice. Callers of the WordPress post API must check the resulting status.

Programmatic imports should create/match drafts, resolve references, populate fields, commit and only then publish. `write()` is not the complete future import adapter: it does not resolve portable references, assign topics, migrate existing records or implement repeat-import conflict handling.

Native readers do not call SCF and preserve saved blanks without supplying marketing defaults. The public adapter adds `id`, `title`, `url`, `author`, `reading_minutes` and `schema_version`, filters unavailable related Guides and fixes `author` to **Greendawn Power Limited**. It returns data, not escaped HTML. Theme callers must escape for each output context. Other internal readers must not be used to expose non-public records through new endpoints.

## Derived data and revisions

- `_greendawn_guide_search_text`: private, generated plain text from the title, reading copy, section lists/tables/captions, FAQs and assigned topic names.
- `_greendawn_guide_reading_minutes`: estimated from the reading copy at 200 words/minute; zero when there is no reading copy. The native `post_content` remains empty.
- `_greendawn_guide_schema_version`: the derived-data contract version, not an editorial review date.

This PR generates the projection but adds no search SQL or public discovery endpoint. The later hub query must independently restrict results/counts to published, non-password-protected records. A private database projection of a draft is not public search output.

Native metadata writes and topic changes are queued; complete SCF/native saves, `import_end` and shutdown flush the queue. Topic renames and explicit term removals also invalidate the projection. `Guides::commit()` is the explicit batch completion hook for future imports.

Native revision snapshots use `_greendawn_guides_snapshot` on the **revision ID**, containing only allowlisted SCF metadata/reference keys, their presence/empty values, assigned topics, template and schema version. Derived values and unrelated metadata are excluded. Metadata-only edits create revisions; unchanged commits do not. The revision UI includes a structured diff. Restoration runs after SCF's hooks, removes stale flattened row keys, restores selected values/topics and rebuilds derived data, including when SCF is inactive. Restoration of incomplete Guide content cannot leave it published. SCF editing/import APIs remain unavailable without the editor plugin. Autosaves are not treated as saved SCF content revisions.

## Verification

Run `pnpm check` and the required release/package validation. `tests/integration/setup.sh` now runs `guides-foundation.php` once with SCF 6.9.8 and once with SCF skipped. The test file is guarded to WP-CLI, `local` environment and the disposable `http://localhost:8080` site; it must never be used to seed a hosted website.

The integration fixture covers real SCF post-form saves, quotes/backslashes, nested table/list storage, metadata-only revisions and restore, removal of stale rows, deliberate clears, source/date/image/reference validation, required publication fields, corporate identity, access restrictions, hub/region field reads, legacy route ownership, blog-prefix independence, topic rename invalidation and native rendering data with SCF inactive. It removes its temporary records after the native phase. Existing content migrations and live route allocation remain a separate staging/import audit.
