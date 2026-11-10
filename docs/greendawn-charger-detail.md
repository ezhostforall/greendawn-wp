# Greendawn EV charger detail

Install the candidate **theme and Greendawn Core** together. WordPress selects `single-evcharger.php` automatically for existing `/evcharger/<slug>/` records; no Page/template assignment, replacement CPT, permalink change or new archive is needed. This change does not import, seed or rewrite persistent staging content.

## Content and media

The fixed product layout uses one H1, breadcrumbs on the navy hero, brand/power summary, a contained product image, description, a semantic specification grid and Request Quote actions. It removes blog date/post navigation from products. Hero images use native responsive attachment markup, stable dimensions, eager loading and high fetch priority. Product photographs use `object-fit: contain`, so tall charger silhouettes are not cropped.

The existing title, featured image and twelve specification fields remain the content sources. The original bundled ACF JSON and its keys/defaults are unchanged. Display reads **raw saved metadata**, including checkbox arrays; it never renders SCF defaults as product facts. Missing/blank rows are omitted. A saved zero weight remains `0 kg`; numeric weight gains `kg` and numerical dimensions gain `mm` only when units are absent. Charging power, voltage ranges, connector values and text prices retain their saved text. No price, currency or grant calculations are introduced. Brand/power fields are not silently replaced with similarly named taxonomy terms.

When the Description metadata has not been saved, native imported text prefills the structured Description field and renders as plain paragraphs. No content is written by that fallback. Explicitly saved copy overrides the import; a saved blank stays blank. The original `post_content` remains stored. Builder/shortcode bodies are not executed or converted automatically: map their approved text into Description explicitly. The adapter does not run legacy `the_content` filters or load Divi/snippet plugins.

## Quote journey

Request Quote opens the existing lazy React drawer directly on its contact step, with EV charging intent and a visible selected-charger summary. The selection can be removed. The managed CF7 transport/email gains `chargerId`, `chargerTitle` and `chargerURL` at runtime, preserving saved recipients, custom copy and enablement. Server validation resolves the ID to a published, non-passworded `evcharger`; canonical titles/permalinks replace client-supplied labels/URLs. Draft, private, passworded, missing and wrong-type records cannot be submitted as quote selections. Existing first-touch/UTM attribution and normal drawer flows remain available; product details are not added to analytics events.

The ordinary anchor destination is the local `/enquire/?charger=<slug>` route. Without JavaScript, with the drawer disabled or if React fails, navigation retains the selection and the existing Enquire page displays its verified product context. The dedicated Enquire template/per-page inline form belongs to the later enquiry/support stage; this PR does not create or reconfigure that live Page/form. Its drawer-enabled quote route is usable immediately. Non-product header/footer/launcher actions remain general enquiries and do not silently select a charger.

## Staging acceptance

1. Confirm all six existing audience destinations: Hellonext H1 22, Project EV AC Pro Earth Dual, Simpson & Partners, both SyncEV entries and Easee. Check other imported charger singles too.
2. Compare descriptions/specifications with the imported records, including decimal/zero weight, ranges, text prices and connectivity arrays. Review any missing Description/image fields; do not fill them using guessed product facts.
3. Check 320/375px, tablet and desktop: one H1, no horizontal overflow, readable specification labels and fully visible product images. Review image alt text and attachment health.
4. Open Request Quote, confirm the product summary, complete a test enquiry and confirm delivery with canonical product identity plus first-touch/UTM information. Remove the selection and check an ordinary enquiry. Check the disabled/no-JavaScript fallback URL and visible Enquire selection.
5. Check the existing homepage/header/footer, About/form tracking and ordinary drawer journey after installing both packages. Source copy/specification claims are retained, not independently verified manufacturer data.

CI uses disposable fixtures only: all six public paths, raw data/default omission, native-description prefill and saved blanks, no builder execution, product visibility restrictions, responsive/Axe checks, no-JavaScript fallback, real CF7 quote delivery and canonical identity despite spoofed title/URL input. The fixture uses the existing test attachment to verify responsive containment; it does not certify the actual imported product photographs. Persistent staging acceptance remains separate.
