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

## About template implementation

The new **Greendawn About Us** template and shared presentation primitives are available in the next page-template change. See [setup and acceptance checks](greendawn-about.md). The content foundation and Neel-owned charger/team intake are complete as reported; the six audience charger URLs were checked publicly and Neel confirmed the saved specification values. Assign the new template to the existing About Us record after staging installation, then review its team/form/image/article selections.

## Charger detail implementation

About/form/tracking staging acceptance was confirmed by Neel. The next change adds the automatic native charger detail template and connects Request Quote to the existing drawer with server-resolved product identity. See [charger detail setup and acceptance](greendawn-charger-detail.md). Audience templates remain the next implementation stage; the Enquire/support templates remain later work.


## Workplace Charging implementation

Neel confirmed charger-detail staging acceptance. The fixed **Greendawn Workplace Charging** Page template is the next audience-page implementation, reusing the hero, CTA, process and page-selected form primitives and extracting a shared article collection. It retains all existing Workplace copy, its own process, six native charger destinations, two process photographs, the inline form and ten articles. Benefits and chargers remain visible without flip cards/autoplay. See [Workplace setup and acceptance](greendawn-workplace.md).

After merging/installing the candidate theme and Core, assign the template to the existing `/workplace-charging/` Page and review its media, ordered native relationships and CF7 selection. Actual attachment matching, visible form feedback and intended-recipient delivery remain staging checks. Fleet Charging and Public Charging follow this audience-page implementation; only Public Charging replaces its source process with the approved homepage process. Enquire/support, Aftercare and the remaining internal templates follow the approved page plan.


## Fleet Charging implementation

Neel confirmed Workplace staging acceptance. The next audience-page template is **Greendawn Fleet Charging** for the existing `/fleet-charging-solutions/` Page. It retains the full Fleet copy, four benefits, six native charger destinations, aftercare CTA, Fleet-specific process/two photographs and all ten articles. The charger collection is shared with Workplace while all content/relationship ownership stays page-specific. The approved source/design has no inline Fleet body form; the local Enquire CTA and completed sitewide quote behaviour remain the enquiry routes. See [Fleet setup and acceptance](greendawn-fleet.md).

After merging/installing the theme and Core, assign the template and reconcile native media/relationships on staging. Public Charging follows Fleet acceptance, using only the approved canonical homepage process and removing its old repeated process/photo groups. Existing About, Workplace and Fleet process copy remains independent.

## Public Charging implementation

Neel confirmed Fleet staging acceptance. The next audience template is **Greendawn Public Charging** for the existing `/public-charging/` Page. It preserves the source benefits, complete AC/DC guidance, six charger destinations, both existing CTAs and ten articles. Both old process groups and their four repeated photo positions are replaced by one approved homepage-language process. No inline body form or process-photo fields are added. See [Public Charging setup and acceptance](greendawn-public-charging.md).

Approved process defaults now have one Core definition with an adapter for the existing homepage field keys. Existing saved homepage content and its loading/reset behaviour stay intact. Public saved values remain Page-owned; blank Public values stay blank and restoration is an explicit content edit, not a cross-page overwrite. About, Workplace and Fleet process wording is unaffected. After installing theme/Core, assign the template to the existing Page and reconcile its hero, native selections, two CTA journeys and single process on staging. Aftercare and the enquiry/support/remaining internal templates follow audience-page acceptance in the approved plan.

## Aftercare implementation

Neel confirmed Public Charging staging acceptance. **Greendawn Aftercare** is the next fixed template for the existing `/aftercare/` Page, reusing the shared split hero, token-based shell, validated CF7 content renderer and article collection. It adds reusable restricted media/copy, full-content offering cards and native FAQ disclosures. All source paragraphs, offering lists, three complete FAQ answers and ten article destinations are retained. The per-page CF7 selector remains independent; no selection omits the optional panel. This retains the form placement capability pending any later content decision and changes no support/sales recipient.

Core owns content/defaults, including SCF FAQ repeater storage that renders without SCF. Saved clears remain clear and the imported body backup stays untouched. After installing theme/Core, select the template, reconcile its hero/introduction photographs and article records, select the intended form and verify feedback/delivery on staging. See [Aftercare setup and acceptance](greendawn-aftercare.md). Enquire, Business EV Charging Installations, Customer Care, Gallery and Blog remain the next internal templates.

## Enquire implementation

Neel confirmed Aftercare staging acceptance. **Greendawn Enquire** is the next fixed template for the existing `/enquire/` Page: a compact text-only hero, independently selected detailed sales form beside the complete six-step source process, then grouped shared contact methods. Its existing six-stage wording remains independent of the canonical four-stage homepage/Public process. Editors receive content-only fields; shared phone/email/address stay in the existing Footer Customiser settings.

The cleaned detailed CF7 definition preserves source field names, adds no form dependency and is supplied for manual configuration/assignment. Product quote context remains visible and gains server-filled native hidden fields, including without JavaScript. Submitted product identity is resolved from public records only for explicitly configured Enquire forms; no recipient or unrelated form is rewritten. See [Enquire setup and acceptance](greendawn-enquire.md). After installing theme/Core, assign the template, select/configure the sales form, and verify real delivery, attribution, product context and shared contacts. Business EV Charging Installations, Customer Care, Gallery and Blog remain next.

## Business EV Charging Installations implementation

Neel confirmed Enquire staging acceptance. **Greendawn Business EV Charging Installations** is the next fixed template for the existing `/business-ev-charging-installations/` Page. It retains the approved compact hero, three complete service summaries beside an independently selected detailed sales form, then all three expanded source testimonials. The old carousel is replaced by visible native cards; the Johnsons Cars video is activated explicitly, with a normal external fallback link and no third-party video request before activation.

Core owns content/defaults and native media selections; editors receive no design controls. The existing detailed Enquire form may be selected explicitly without changing its definition, mail settings or other Page assignment. See [Business setup and acceptance](greendawn-business-installations.md). After installing theme/Core, assign the template, reconcile the project photograph/client logos, select the reviewed form and test feedback/delivery/video playback. Customer Care, Gallery and Blog remain next.
