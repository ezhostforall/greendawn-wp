# Shared reading components (PR2)

This increment supplies the reusable presentation layer for the approved Guides hub, Guide and regional layouts. The public templates and discovery queries belong to PR3; Rank Math and intent preselection belong to PR4; content import remains PR5. Nothing provisions public content or changes an existing Page template.

## Assets and dependencies

The new `greendawn-reading` CSS entry imports the existing page styles and adds only component/variant selectors. It is registered in both Vite and `greendawn_entries()`, but is **not enqueued globally**. Consumers should select it through `greendawn_page_entries` before `wp_head`; PR3 will wire the actual templates. The components add no JavaScript dependency. GET controls, anchors, FAQ disclosures, images and links work without JavaScript.

Render inside the existing `.greendawn-page` and `.gd-shell` system. Use `.gd-reading-layout` for the contents/body split and `.gd-reading-body` for the reading column. The reading layout collapses at the theme's 61rem breakpoint. `--gd-reading-offset` can be adjusted by the owning template; its default accounts for the existing collapsed header and native admin bar. Tables scroll inside their own labelled, keyboard-focusable region.

Core and SCF remain optional at theme runtime. `greendawn_guide_view($id)` delegates to Core's public adapter when available, otherwise returns an empty array. `greendawn_article_card_data(WP_Post $post)` resolves either a published, non-password-protected native Post or a public Guide. The Guide adapter supplies its SCF introduction/image, topic and computed reading estimate, without reading native post content or personal author profiles.

## Extensions: existing defaults retained

| Partial | Optional arguments |
|---|---|
| `page-hero.php` | `variant: guide`, `kicker`, `metadata`, `image_position`, `cta` |
| `article-card.php` | `variant: guide` or `guide-featured`, `topic`, `summary`, `reading_minutes`, `image_position`, `show_image` |
| `articles.php` | Published Post/Guide records in `posts`; `layout: grid` for a fixed responsive collection. Default remains the existing scroll track. |
| `cta-band.php` | `action: enquiry`, existing `entry_point`, optional `intent`. Plain links remain plain unless opted in. |
| `media-copy.php` | `tone: dark`, `kicker`, `caption`, `link: {url, label}` for a published evidence link |
| `feature-grid.php` | `variant: icons`; each item uses an allowlisted `icon`. Existing numbered/support variants remain available. |

Guide cards normally omit imagery to keep the index compact; featured cards show the image when available. `show_image` explicitly controls this on either variant. All variants retain a useful no-image layout. Focal positions are `centre`, `upper`, `lower`, `left`, `right` and become fixed classes, not editor CSS.

`greendawn_component_cta()` generates a real fallback URL and escaped, allowlisted drawer attributes for an explicit `enquiry` action. Use current tracking entry points such as `hero`, `services` or `final-cta`. The existing launcher consumes the open/entry-point attributes now. **`data-lead-intent` is a markup contract for PR4; this PR does not change drawer intent preselection, transport, consent or attribution.** No duplicate form is created.

## Seven new partials

| Partial | Input and behaviour |
|---|---|
| `guide-filters.php` | `url`, `query`, active topic slug, `topics[{slug,name,count?}]`, optional labels. Native GET uses `guide_search` and `guide_topic`; topic links retain search text. Counts are supplied by the future public query, never inferred from private records here. |
| `editorial-meta.php` | Explicit `author`, optional actual `reviewed_on` (`Ymd`), computed `reading_minutes`. Empty/invalid/future dates are omitted. Guide callers pass Core's corporate identity. |
| `reading-contents.php` | Prepared `sections`, optional `label` and instance `prefix`. The same records generate desktop links and a mobile native disclosure. No second editorial contents list. |
| `direct-answer.php` | `answer`, optional UI `label`/`instance_id`. Empty answers leave no visible shell. |
| `reading-sections.php` | Prepared sections. Fixed heading levels, semantic prose, numbered/plain/checklist lists, accessible tables and local attachment images/captions. Invalid table dimensions are omitted rather than producing misleading columns. |
| `source-list.php` | `items[{title,url,publisher?,checked_on?}]`, optional heading/instance ID. Invalid URLs and incomplete source identities are omitted; no implicit checked date. |
| `coverage-list.php` | `items[{name}]` (or strings), optional accessible label. Empty/duplicate names are omitted. No addresses, maps or local-office claims are generated. |

Prepare section records **once** and pass the same result to both reading partials:

```php
$guide = greendawn_guide_view( get_the_ID() );
$sections = greendawn_reading_sections( $guide['sections'] ?? array(), 'guide' );
get_template_part( 'template-parts/components/reading-contents', null, array( 'sections' => $sections ) );
get_template_part( 'template-parts/components/reading-sections', null, array( 'sections' => $sections ) );
```

Use a distinct instance prefix if several reading compositions appear on one page. The helper normalises repeaters and generates deterministic IDs, reserving heading/table-caption IDs as well as section anchors. Duplicate headings cannot create duplicate DOM IDs. Explicit content arguments remain escaped at output; restricted prose uses the existing `greendawn_page_prose()` allowlist and never executes blocks or shortcodes.

Icons are fixed decorative SVG paths through `greendawn_component_icon()` (`bolt`, `site`, `fleet`, `property`, `pin`, `check`); unknown names render nothing. They do not replace visible text labels.

## Verification and scope boundary

The disposable `reading-components.php` integration fixture verifies real Core/SCF data, public visibility, deterministic anchors, empty states, malformed tables, unsafe URLs/prose, date handling, plain/enquiry CTA contracts and legacy defaults. It runs with SCF active, SCF skipped and Core skipped. Test records and plugin templates are guarded to the local CI WordPress and are never packaged for hosted staging.

The browser fixture composes the library without creating the actual public templates. It covers 1440/1024/834/393/320px reflow, native no-JavaScript navigation/disclosures, table keyboard scrolling, accessible controls, the existing drawer and focus return. Existing page/editorial/browser tests continue covering the legacy component consumers. Both candidate creation and persistent staging review remain scheduled after PR3.
