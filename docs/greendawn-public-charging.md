# Public Charging template

Install the candidate **theme and Greendawn Core** together, then assign **Greendawn Public Charging** to the existing `/public-charging/` Page. Keep its slug, relationships and imported body backup. No new Page, CPT, charger import or permalink change is needed.

## Fixed layout and source content

The page reuses the audience hero, four visible benefits, native charger collection, lime CTA, shared process and article track. It retains the full Public hero/benefits copy, AC/DC introduction and both guidance list items, all six charger destinations, setup CTA, final **Ready to Power Public EV Charging?** CTA and all ten article destinations. The AC/DC advice remains visible even if no charger records are selected. The selected product catalogue is retained as supplied; the template does not invent DC products or new cost, revenue or grant claims.

As approved, **both legacy process sections and all four repeated process-photo positions are replaced by one process** using the complete homepage language: heading, introduction and Consult → Design → Install → Manage descriptions. There are no Public process-photo fields. Other pages keep their own process wording. The old body, including its duplicate process blocks, remains stored as the import backup and is never appended or executed by the new template.

The source inventory/design has no inline Public page-body form. Start Your Journey and Book a Free Consultation remain local Enquire links. No duplicated legacy footer form, new CF7 selector, email recipient or delivery behaviour is added. The completed sitewide header/footer and lazy product-aware quote drawer remain in use.

SCF supplies fixed Hero, Benefits, Chargers, Call to action, Approved process, Final call to action and Articles tabs. Editors change content and order selected records; they cannot choose layouts, section order, spacing or columns. Gutenberg/the classic body editor are disabled on this template only. The page renders with SCF inactive. Password-protected Pages keep the native WordPress password gate before any structured copy is rendered.

## Approved process ownership and restoration

`Greendawn_Process::defaults()` is the single approved initial language definition. Public’s process fields take their defaults from it. `homepage_defaults()` maps that same definition to the **existing** homepage `process_title`, `process_intro`, `<stage>_label` and `<stage>_description` keys. Existing homepage content, field keys, stage captions, motion hooks and default-loading behaviour are preserved; no saved metadata is migrated or rewritten.

This centralises **defaults**, not saved page content. Each Page owns its saved edits. Editing Public does not rewrite the homepage or another Public Page; editing the homepage does not silently replace Public’s saved copy. About, Workplace and Fleet retain their separate existing process definitions.

Public defaults apply only to unset metadata. A saved blank remains blank. Clearing a field is **not** a reset-to-default action: paste the approved wording back into that field to restore it. The approved initial copy is the heading “A clear route from requirement to operation.” and the complete introduction/descriptions in the version-controlled definition. Existing homepage blank/default behaviour is unchanged. Brand-style resets do not alter page copy. No new global process settings or automatic synchronisation job is introduced.

## Native media and selections

A saved dedicated hero selection takes precedence. If that field has never been saved, it uses the Page’s featured image, falling back to the local attachment `2026/11/commercial-ev-destination-hospitality-1200.webp` (including a WordPress `-scaled` variant). Upload/select the approved photograph if absent. A saved cleared selection omits the image, even when a featured image exists. No old-domain remote image or replacement script is loaded. Check alt text and attachment health. Hero media is responsive, eager and high priority; product/article images are lazy-loaded.

The default charger order resolves existing published, password-free native records:

1. `hellonext-h1-22`
2. `project-ev-ac-pro-earth-dual-7-22kw`
3. `simpson-partners-7-22kw`
4. `sync-ev-22kw`
5. `sync-ev-22kw-2`
6. `easee-charger-22kw`

Cards use the record’s actual title, contained featured image and local detail URL. Request Quote opens the detail first, then the existing quote drawer carries the verified product identity. No specifications or product copy are duplicated into Page fields. Drag selected chargers/articles to reorder. Unpublished, passworded, wrong-type and duplicate records are excluded. Cleared charger selections remove cards while keeping the Public AC/DC guidance; cleared articles remove the collection. Articles keep all ten source slugs, native permalinks and a non-autoplay track with no-JavaScript scrolling.

## Staging acceptance

1. Assign the template, save/reopen and review all fixed content tabs. Check the four benefits, complete AC/DC list, both CTAs and native relationships against the existing content.
2. Check that there is **one** approved process, with its complete introduction/four descriptions and **no** legacy process-photo strips. Confirm the imported body remains stored and is not duplicated in the front end.
3. Review hero/product/article attachments, local URLs and record order at 320px, tablet and desktop. Check one H1, readable guidance, 1/2/3-column charger cards and no outer-page overflow.
4. Follow both Enquire CTAs and a product’s quote journey. Recheck the completed homepage’s saved process copy/motion, plus Workplace’s form and Fleet’s process/collection.

CI uses disposable WordPress/SCF fixtures to check canonical defaults, legacy homepage key compatibility/saved copy, field/editor scope, explicit blanks, native media, ordered/filtered relationships and import preservation. Browser checks cover full Public copy/destinations, exactly one process/no photo strips, both CTAs, no JavaScript/SCF, empty/protected states, responsive screenshots, reduced motion, Axe and product quotation context. Fixture titles/product photographs do not certify actual imported media; those remain staging acceptance. No persistent staging content, form or recipient is changed by these tests.
