# Internal-page content foundation

The first internal-page change belongs to Greendawn Core. Installing/updating and activating Core automatically registers **EV Chargers** and **Team Members**. No configuration filter, CPT UI or WPCode snippet is needed. SCF supplies the structured editing fields; neither CPT depends on SCF to register. Settings → Greendawn Core describes their status.

## Charger compatibility

- Storage key and detail paths: `evcharger`, `/evcharger/<slug>/`, independent of any blog permalink prefix. No charger archive is introduced.
- Taxonomies: `brands` (hierarchical) and `power_outputs` (non-hierarchical), following the supplied CPT UI screenshots. The old site also has a conflicting Power Output snippet: confirm term parent data during intake. Terms are editable/exportable but have no new public taxonomy archives on this fresh installation; review any legacy taxonomy destinations before launch.
- Both CPTs use native Page capabilities: Administrators and Editors can manage them; Authors, Contributors and Subscribers gain no additional access. No role capabilities are modified.
- Neither type exposes a REST collection or a Gutenberg/body-layout editor. Existing `post_content`, including imported Divi markup, remains stored. No save hook rewrites it.
- The original 12-field export is bundled byte-for-byte at `plugins/greendawn-core/fields/ev-charger-specification.json`, registered automatically on `acf/init`. Do not manually import a second editable copy of that group. Its original group/field keys, names, required flags, choices, types and defaults remain unchanged.
- The `brand` field is separate from the `brands` taxonomy, and `charging_power` is separate from `power_outputs`. No automatic mapping is applied.
- Preserve value metadata **and** ACF reference metadata (`brand` and `_brand`, for example). Connectivity remains an array; price, voltage and charging-power strings retain their source formats.
- The source schema contains a **250 kg default**. It is not a verified product specification. Registration never writes defaults into imported posts; compare raw metadata, rather than SCF's displayed defaults, when auditing missing values. All twelve source fields are required, so review gaps before saving an imported record through the admin form.
- `greendawn_charger_description` is a separate, plain-text structured description for reviewed content. It is initially empty. The imported body remains the intake backup until its text has been converted/mapped; this PR does not render or convert old Divi content.
- Rewrite rules refresh once per site after the new registration is installed, including an update to an already-active Core plugin. The refresh is soft and does not write server configuration. Subsequent requests do not flush them again.

## Team records

`team_member` provides native name/title, featured portrait and Order (`menu_order`) plus SCF role/organisation, biography, LinkedIn URL and optional connection-count label. It creates no WordPress login accounts. There are no public member singles, archive, navigation-menu entries or search results. A future About relationship selects published records explicitly; drafts/private records must not be displayed. No team records are seeded in this foundation.

## Staging installation and Neel-owned import

1. Merge the PR, prepare a staging candidate with the existing release workflow, then install the updated Core package on staging. Activate SCF if needed. Confirm both content menus and the specification/team fields appear before importing anything.
2. **Neel imports** the existing `evcharger` posts using the chosen WordPress import workflow, including taxonomy terms, metadata/reference keys and featured media. Export/import content only; do not copy the old CPT snippets, builder plugins or database over the fresh site. Existing Page/blog records should not be recreated.
3. Check record counts and slugs against the export; confirm all six audience-page charger destinations. Sample every specification, including decimals, voltage ranges, text prices and multi-value connectivity. Check raw missing values and `_field_name` references before saving through SCF.
4. Check `brands`/`power_outputs` assignments and any parent terms, attachment downloads, portrait/product alt text and featured-image associations. A WXR file can retain attachment references without successfully downloading media: check the actual Media Library and rendered images separately.
5. Check draft/private visibility and the Editor role. Confirm `/evcharger/<slug>/` URLs use the staging host and have no blog prefix. The dedicated charger detail design and quotation context come in a later template PR; generic native rendering in this foundation is not final acceptance.

On staging with SCF inactive, titles, thumbnails/order, taxonomy terms and existing metadata remain intact; the additional field panels are absent. React enquiry, forms and the homepage are unchanged.

## Next implementation stages

Extract shared shell/type/spacing, image-split page hero, process, cards, CTA and form primitives in small changes with homepage regression checks. The approved About hero uses the **same navy image-split family as Aftercare and the audience pages**; compact text-only heroes are reserved for functional/editorial routes such as Enquire, Customer Care and Blog. Build the fixed About template and its ordered team selection after the foundation.

Public Charging uses only the homepage's current Consult → Design → Install → Manage heading, introduction and complete step descriptions. Replace both old process/photo groups. Use that language for new process sections going forwards; preserve the other existing page-specific process copy until separately approved. Optional form-placement recommendations and conflicting source Gallery statistics remain review decisions.

## Verification

CI tests registration without SCF, imports a representative WXR charger, checks raw metadata and references, activates pinned SCF 6.9.8, checks field resolution/editing, capabilities and rewrite refresh idempotence, then deactivates SCF before the existing browser suite. These are disposable fixtures and never touch persistent staging or production. Final attachment/content reconciliation follows Neel's actual import.
