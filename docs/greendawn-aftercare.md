# Greendawn Aftercare

Assign **Greendawn Aftercare** (`templates/greendawn-aftercare.php`) to the existing `/aftercare/` Page after installing the candidate **theme and Greendawn Core**. Existing imported `post_content` remains stored as a backup and is not appended to the structured layout. The template is password-aware and disables Gutenberg and the classic layout/body editor only for its own Page.

## Approved composition and content

The fixed composition follows the approved internal-page design:

1. Shared navy image-split hero: **EV Charging / Aftercare**, with the complete source introduction.
2. **What It Is and Why It Matters**: the full introduction, all four asset descriptions and the closing paragraph, beside the source photograph.
3. **Our Aftercare Offering**: four visible cards in a two-column grid, stacking on mobile. Backoffice Software, Inspection + Testing, Complete Care Plan, and Driver + User Support retain their complete paragraphs, emphasis, all sixteen bullet points and the final public-charging note.
4. **Frequently Asked Questions**: all three questions and complete answers, including their lists. Native `details`/`summary` disclosures work without JavaScript; the first is initially open. The page-selected form sits beside the FAQs on wide screens and below them on smaller screens.
5. **Our Content**: the same ten native source article destinations, in selected order, through the shared non-autoplay article component.

The source FAQ and regulatory/commercial statements are retained as supplied; this template does not revise those claims. No homepage process is inserted into Aftercare. The sitewide header, footer, React drawer, homepage Aftercare summary and existing audience copy remain independent. Legacy duplicated footer forms and unrelated source-navigation links are not imported.

## Editing and defaults

Core owns the `greendawn_aftercare_*` fields. Hero copy/media, introduction, four offering titles/full content, FAQ heading/repeater, optional form heading/selection and article heading/selection appear in section tabs. Editors change content, not the section order, surfaces, type scale, spacing or grid columns. Offering positions remain fixed; FAQ rows support content editing, adding/removing and ordering.

Introduction, offering content and FAQ answers use the basic SCF visual editor, with media uploads disabled. Output permits paragraphs, line breaks, emphasis, lists and safe text links only; styles, classes, headings, images, embeds, scripts and arbitrary layout markup are discarded. Source copy is supplied as unsaved defaults, not replaced on the client. Reading a default never writes metadata. Saved edits and saved empty values are preserved across updates. Clearing FAQ rows keeps them empty rather than recreating the three defaults. Restore approved copy explicitly in the fields if required.

Saved FAQ rows use SCF's native repeater metadata and continue to render with Core active when SCF is deactivated. SCF supplies the editing UI; the PHP rendering does not call `get_field()`. Without Core the theme still renders native saved scalar fields/title/media and omits unavailable collections safely.

## Local images and article relationships

Hero precedence while the dedicated field is unset: native featured image, then the local attachment matching `2026/11/commercial-ev-electrical-connection-1200.webp`. The introduction defaults to the imported local `2026/04/20260220_112809-1.avif`. Native `-scaled` variants are supported. Missing attachments produce no broken image, remote hotlink or substitute photograph. A saved cleared image stays absent, including when a featured image exists. Select the actual imported images manually where filenames/paths differ. The hero uses responsive WordPress markup with eager loading and high fetch priority; below-fold media is lazy-loaded.

The article relationship initially resolves the existing ten source post slugs in source order. It does not fabricate articles. Published, non-passworded native Posts only are rendered; invalid types, duplicates, private/draft/passworded records are excluded. Clearing the selection omits the collection. Links use native local permalinks.

## CF7 ownership and routing

Use **FAQs and enquiry → Contact Form 7 form** to select a published form on this Page. No form is seeded, copied, guessed, or inherited from the Contact template's Customiser selection. Configure its fields, recipient and messages in CF7; the existing cleaned project-enquiry definition may be reused if that is the intended routing. This PR does not create an Aftercare support destination or change an existing form's recipient. Customer Care remains separate future work.

Leaving the form unselected omits the form panel and lets the FAQ list use the single-column layout. Selecting an invalid/unpublished/non-CF7 record or deactivating CF7 shows the existing visible unavailable message and a local `/enquire/` link, rather than a broken shortcode. The shared form-content renderer retains the existing About/Workplace form behaviour. Existing attribution fields, first-touch/UTM handling and visible CF7 response styling apply to this form too.

## Staging acceptance

- Confirm the two images and all source content, including all four offering lists and three expanded FAQ answers.
- Review the hero/media and offering grid at desktop, tablet and 320/375px widths; check wrapping and page overflow.
- Edit and reorder an FAQ, save, reload and confirm it persists. Verify the imported body backup is untouched.
- Assign the intended CF7 form; check required-field feedback, visible success/failure messages, attribution and intended-recipient delivery. Delivery requires the actual staging mail configuration; CI intercepts successful test responses and sends no live email.
- Clear/unselect the form to check the FAQ-only layout. Verify the local fallback link if CF7 is unavailable.
- Review all ten article selections, keyboard disclosure/track controls, reduced motion, sitewide navigation and the enquiry drawer.
- Recheck About and Workplace forms after the shared form-content extraction, plus the completed homepage, Fleet and Public pages.

Disposable integration/browser fixtures do not modify persistent staging or production. Their introduction photograph and article media are genuine local test assets, not evidence that the actual imported attachments have been reconciled.
