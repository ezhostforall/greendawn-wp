# Greendawn homepage pilot

The Greendawn Homepage template renders the Hero, Trust, Problem, System, Process, Featured Project, Technical Site Surveys, Services, Aftercare and Supporting Proof sections from the Astro reference. The branded header is shared across the site, with a transparent overlay on this template and a navy background above inner-page content. The layout and proof stage order live in PHP. Greendawn Core owns the copy and image defaults and registers the editable homepage fields when Secure Custom Fields is active.

## Set up the staging page

1. Install the candidate theme and Greendawn Core together. Activate Secure Custom Fields for the editing fields.
2. Create a Page, select **Greendawn Homepage** as its template and preview it before assigning it as the static homepage. Empty copy fields show the Greendawn defaults. The hero defaults to the Media Library attachment at `2026/11/greendawn-engineers-electrical-distribution-1600.webp` relative to the site’s uploads folder. If the file was uploaded under a different path, select it in **Hero photograph**. Set its alt text in the Media Library.
3. In **Appearance → Customise → Header**, set **Discuss your site** and the current live enquiry destination. The header and hero use this setting together. Use **Utility bar text** to edit the message and **Reset utility bar text** to restore the original wording, then publish the change. The Explore our projects link defaults to the gallery on the current WordPress site. Use the Projects destination field to choose another internal destination when needed.
4. Assign the Primary menu with About Us, Our Projects, the three Our Solutions children and Aftercare. Point the entries at their existing live destinations until those pages move. Assign the telephone and customer support links to the Utility menu. The supplied raster wordmark is the fallback when no custom logo is configured.
5. Verify the Page preview and then assign it under **Settings → Reading** when ready. Compare it with the Astro reference at 390px and 1440px, exercise the menu by keyboard and confirm the enquiry and gallery destinations.

The homepage editor groups fields under Hero, Trust, Problem, System, Process, Featured project, Surveys, Services, Aftercare and Supporting proof. Edit the four shared stage labels in Hero and their full descriptions in Process. The System and survey guides default to internal routes on the current WordPress site. Imported homepage URLs saved with `greendawn.co.uk` or `www.greendawn.co.uk` are resolved on the current site, retaining their paths, query strings and fragments. Other explicitly saved external destinations remain unchanged.

Upload [the approved Johnsons project photograph](astro-reference/media/johnsons-dealership-ev-charging-1200.webp) to the Media Library and set its alt text to “Vehicle connected to EV charging equipment at a Johnsons Cars dealership”. Select it under **Featured project → Project photograph**. The default resolves the uploaded attachment at `2026/11/johnsons-dealership-ev-charging-1200.webp`; select it explicitly if WordPress uses a different month or filename. The theme renders the selected attachment with responsive sizes, dimensions and lazy loading. If no attachment resolves, the project story renders in one column without an empty image frame. No remote or bundled photograph is rendered as a fallback.

The project gallery link shares **Projects destination** with the hero. The survey enquiry button shares the Header enquiry destination and has its own editable label. Survey price, VAT wording, scope, four outcomes, guide destination and all project copy/figures are editable content fields; section layout and order remain in the theme.

The default programme figures (136 sites, 454 chargepoints, 11 automotive brands, reviewed September 2026) and survey price (from £200 + VAT) come from the pinned Astro source. Retain the review note and scope wording, and confirm those figures and the current price before production publication. They are not independently revalidated by this port.

Verify both sections at 320px, 390px and 1440px, including the saved project image/copy, price and guide URL, gallery and survey links, keyboard focus, sticky image behaviour and absence of horizontal scrolling. Default content and saved overrides must be present in the initial HTML with JavaScript disabled.

## Services section

The **Services** tab supplies the heading, introduction and five fixed audience rows. Each row has an editable title, description, link text, destination and photograph. Row order, typography and composition remain controlled by the theme. All five defaults point internally and were checked against the existing staging routes:

| Audience | Internal destination | Upload-ready photograph |
| --- | --- | --- |
| Fleet & depot | `/fleet-charging-solutions/` | [Fleet charging bays](astro-reference/media/commercial-ev-fleet-charging-bays-1200.webp) |
| Workplace | `/workplace-charging/` | [Workplace charger](astro-reference/media/commercial-ev-workplace-charging-1200.webp) |
| Public & destination | `/public-charging/` | [Destination hospitality](astro-reference/media/commercial-ev-destination-hospitality-1200.webp) |
| Multi-site | `/business-ev-charging-installations/` | [Multi-site installation](astro-reference/media/commercial-ev-multi-site-charging-768.webp) |
| Dealerships & motor retail | `/guides/ev-charging-for-car-dealership/` | [Dealership charger](astro-reference/media/commercial-ev-dealership-charging-1200.webp) |

Upload these photographs and select them under their respective fields. Defaults resolve attachments with these filenames in the `2026/11/` uploads folder, including WordPress’s automatically scaled copy of the tall workplace photograph. If the upload path or filename differs, select the attachment explicitly. The preview images are decorative; their captions use the editable row titles.

The first available photograph is shown initially. Hovering or keyboard-focusing a row previews its selected photograph through CSS, with keyboard focus taking priority over hover. All rows remain normal links and work without JavaScript. With normal browser lazy loading, hidden previews are deferred until displayed. Browsers may eagerly fetch lazy images when JavaScript is disabled, under the [HTML lazy-loading privacy rules](https://html.spec.whatwg.org/multipage/urls-and-fetching.html#lazy-loading-attributes); content and previews still work. A row without a selected/resolvable photograph retains the initial preview; if no photographs resolve, the list uses the full width without an empty frame. GSAP scroll animation remains a later stage.

## Aftercare section

The **Aftercare** tab supplies the eyebrow, heading, two copy paragraphs, button label/destination, customer support prompt/link and closing statement. The four fixed support rows have editable headings: Monitoring & reporting, Inspection & testing, Maintenance & fault support, and Driver & user support. Their numbering and layout remain in the template.

The section follows the pinned Astro navy/lime styling and italic Bricolage Grotesque closing statement. It renders in the initial HTML without JavaScript and requires no new media uploads. The button defaults to the internal `/aftercare/` page and customer support defaults to `/customer-care/`; both routes were checked on staging. Existing Greendawn public-domain URLs saved in these fields resolve internally through the homepage field module.

After updating the theme and Greendawn Core together, review the new Aftercare tab and compare the section at 320px, 390px and 1440px. Check both destinations, keyboard focus on the dark background, button hover, the closing statement spacing, and saved/blank field behaviour. Empty text fields use the pinned defaults.

## Supporting Proof section

The **Supporting proof** tab supplies the section eyebrow/heading, gallery link label, photograph/caption, project name, result line and description. The pinned `SupportingProofSection.astro` renders one compact Salvation Army project card; the unused coverage and capability records from `home.ts` are not added as extra cards. Layout and card count remain controlled by the theme.

Upload [the Salvation Army photograph](astro-reference/media/salvation-army-ev-charging-666.webp), set its Media Library alt text to “Commercial EV chargers installed for The Salvation Army”, then select it under **Supporting project photograph**. The default resolves the attachment at `2026/11/salvation-army-ev-charging-666.webp`; select it explicitly if the upload filename or path differs. The selected image is server-rendered with responsive sizes, dimensions and lazy loading. When no photograph resolves, the card shows its copy across the full width without an empty frame.

The gallery link shares **Hero → Projects destination** with the hero and Johnsons case study, so it stays on the current WordPress site by default. Its label can be edited independently. The default `50 sites · 71 chargepoints` result is copied from the pinned Astro content; confirm the figures before production publication. This port does not independently revalidate the programme data.

After updating the theme and Greendawn Core together, check the card at 320px, 390px and 1440px, including the uploaded/selected photograph and alt text, caption, saved copy/results, internal gallery destination, image hover and no-JavaScript rendering.

The homepage uses the starter’s native menu disclosure. The coordination model, system arc and process timeline render in the initial HTML. The remaining sections, motion and callback drawer follow separately.

The hero WebPs, wordmark and client marks come from Astro revision `891ed840c7369cb50df3e33232e81de49e2699d6`. The asset provenance and publication notes are maintained with the [Astro image records](https://github.com/ezhostforall/GreenDawn/blob/891ed840c7369cb50df3e33232e81de49e2699d6/docs/image-sources.md).

The [pinned Astro source snapshot](./astro-reference/) provides the homepage components and style tokens used to check layout and typography.

## Remaining homepage sequence

After these ten sections, port Insights and the final enquiry section, in that order from the pinned Astro `src/pages/index.astro`. Complete the sitewide footer and enquiry interaction, then add the planned GSAP enhancement with reduced-motion and no-JavaScript fallbacks. Final acceptance compares typography, spacing, media, keyboard behaviour and performance across the complete page.
