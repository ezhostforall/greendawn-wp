# Greendawn homepage pilot

The Greendawn Homepage template renders the Hero, Trust, Problem, System, Process, Featured Project, Technical Site Surveys, Services, Aftercare, Supporting Proof, Insights and Final Enquiry sections from the Astro reference. The branded header is shared across the site, with a transparent overlay on this template and a navy background above inner-page content. The layout and proof stage order live in PHP. Greendawn Core owns the copy and image defaults and registers the editable homepage fields when Secure Custom Fields is active.

## Set up the staging page

1. Install the candidate theme and Greendawn Core together. Activate Secure Custom Fields for the editing fields.
2. Create a Page, select **Greendawn Homepage** as its template and preview it before assigning it as the static homepage. Empty copy fields show the Greendawn defaults. The hero defaults to the Media Library attachment at `2026/11/greendawn-engineers-electrical-distribution-1600.webp` relative to the site’s uploads folder. If the file was uploaded under a different path, select it in **Hero photograph**. Set its alt text in the Media Library.
3. In **Appearance → Customise → Header**, set **Discuss your site** and the current live enquiry destination. The header, hero, survey enquiry button and final enquiry button use this destination together. Use **Utility bar text** to edit the message and **Reset utility bar text** to restore the original wording, then publish the change. The Explore our projects link defaults to the gallery on the current WordPress site. Use the Projects destination field to choose another internal destination when needed.
4. Assign the Primary menu with About Us, Our Projects, the three Our Solutions children and Aftercare. Point the entries at their existing live destinations until those pages move. Assign the telephone and customer support links to the Utility menu. The supplied raster wordmark is the fallback when no custom logo is configured.
5. Verify the Page preview and then assign it under **Settings → Reading** when ready. Compare it with the Astro reference at 390px and 1440px, exercise the menu by keyboard and confirm the enquiry and gallery destinations.

The homepage editor groups fields under Hero, Trust, Problem, System, Process, Featured project, Surveys, Services, Aftercare, Supporting proof, Insights and Final enquiry. Edit the four shared stage labels in Hero and their full descriptions in Process. The System and survey guides default to internal routes on the current WordPress site. Imported homepage URLs saved with `greendawn.co.uk` or `www.greendawn.co.uk` are resolved on the current site, retaining their paths, query strings and fragments. Other explicitly saved external destinations remain unchanged.

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

The first available photograph is shown initially. Hovering or keyboard-focusing a row previews its selected photograph through CSS, with keyboard focus taking priority over hover. All rows remain normal links and work without JavaScript. With normal browser lazy loading, hidden previews are deferred until displayed. Browsers may eagerly fetch lazy images when JavaScript is disabled, under the [HTML lazy-loading privacy rules](https://html.spec.whatwg.org/multipage/urls-and-fetching.html#lazy-loading-attributes); content and previews still work. A row without a selected/resolvable photograph retains the initial preview; if no photographs resolve, the list uses the full width without an empty frame. The optional scroll controller is described under Homepage motion below.

## Aftercare section

The **Aftercare** tab supplies the eyebrow, heading, two copy paragraphs, button label/destination, customer support prompt/link and closing statement. The four fixed support rows have editable headings: Monitoring & reporting, Inspection & testing, Maintenance & fault support, and Driver & user support. Their numbering and layout remain in the template.

The section follows the pinned Astro navy/lime styling and italic Bricolage Grotesque closing statement. It renders in the initial HTML without JavaScript and requires no new media uploads. The button defaults to the internal `/aftercare/` page and customer support defaults to `/customer-care/`; both routes were checked on staging. Existing Greendawn public-domain URLs saved in these fields resolve internally through the homepage field module.

After updating the theme and Greendawn Core together, review the new Aftercare tab and compare the section at 320px, 390px and 1440px. Check both destinations, keyboard focus on the dark background, button hover, the closing statement spacing, and saved/blank field behaviour. Empty text fields use the pinned defaults.

## Supporting Proof section

The **Supporting proof** tab supplies the section eyebrow/heading, gallery link label, photograph/caption, project name, result line and description. The pinned `SupportingProofSection.astro` renders one compact Salvation Army project card; the unused coverage and capability records from `home.ts` are not added as extra cards. Layout and card count remain controlled by the theme.

Upload [the Salvation Army photograph](astro-reference/media/salvation-army-ev-charging-666.webp), set its Media Library alt text to “Commercial EV chargers installed for The Salvation Army”, then select it under **Supporting project photograph**. The default resolves the attachment at `2026/11/salvation-army-ev-charging-666.webp`; select it explicitly if the upload filename or path differs. The selected image is server-rendered with responsive sizes, dimensions and lazy loading. When no photograph resolves, the card shows its copy across the full width without an empty frame.

The gallery link shares **Hero → Projects destination** with the hero and Johnsons case study, so it stays on the current WordPress site by default. Its label can be edited independently. The default `50 sites · 71 chargepoints` result is copied from the pinned Astro content; confirm the figures before production publication. This port does not independently revalidate the programme data.

After updating the theme and Greendawn Core together, check the card at 320px, 390px and 1440px, including the uploaded/selected photograph and alt text, caption, saved copy/results, internal gallery destination, image hover and no-JavaScript rendering.

## Insights section

The **Insights** tab supplies the section eyebrow/heading and guidance index link, followed by three curated cards for power, survey and fleet guidance. Each card has an editable label, title, photograph and destination. Card count, order, typography and responsive layout remain controlled by the theme; this is not an automatic feed of the latest posts.

Upload/select the reference photographs under `docs/astro-reference/media/`:

| Card | Photograph | Suggested Media Library alt text |
| --- | --- | --- |
| Power guide | `commercial-ev-electrical-connection-1200.webp` | New electrical connection for a commercial EV charging project |
| Survey guide | `commercial-ev-site-survey-1024.webp` | Completed paving and ducting area for an EV charging installation |
| Fleet guide | `commercial-ev-fleet-charging-bays-1200.webp` (already used by Services) | Completed EV charging bays at a commercial fleet site |

Defaults resolve the uploaded attachments under `2026/11/`; select them explicitly when filenames or upload paths differ. Images use responsive WordPress markup, native dimensions and lazy loading. A missing photograph leaves the card title, label and link intact without an empty image frame. Saved media renders directly in the initial HTML, without a default/replacement image swap.

The index defaults to `/blog/`. Card destinations default to `/guides/load-balancing-explained/`, `/guides/greendawn-ev-charging-survey-pre-installation-guide/` and `/guides/transitioning-to-electric-fleets/` on the current WordPress site. Create those guide pages or point the fields at the appropriate internal content before publishing. Existing imported Greendawn-host URLs follow the current environment; deliberately saved third-party destinations remain external.

After updating the theme and Greendawn Core together, check 320px/390px mobile, 800px tablet and 1440px desktop. The fleet card spans both columns on tablet. Check image crops, index-link wrapping, hover, keyboard focus, saved/blank field defaults and no-JavaScript rendering.

## Final enquiry section

The **Final enquiry** tab supplies the eyebrow, large closing heading, two paragraphs, button label, telephone prompt and optional page-specific display number. Copy defaults come from the pinned `FinalCtaSection.astro`; the phone defaults to the sitewide Footer telephone, whose initial `0345 512 0044` value comes from the same revision’s `src/config/site.ts`. Layout, lime background, typography and decorative arc remain controlled by the theme.

The enquiry button shares **Appearance → Customise → Header → Call-to-action destination** with the header, hero and survey button. Set it to the current WordPress enquiry/contact page; this section navigates normally and works without JavaScript. A blank or `#` destination hides the button. The callback/enquiry drawer enhances this link when enabled; see setup below. The arc follows the optional homepage motion layer described below.

The telephone link is derived from the display number’s digits and optional leading `+`. Use a normal local number or an international number such as `+44 345 512 0044`; set the contact page consistently. A value without a usable telephone number omits the telephone prompt/link rather than producing an empty `tel:` link. Empty content fields restore their pinned defaults, as in the other homepage sections.

After updating the theme and Greendawn Core together, review the panel at 320px, 390px, 800px and 1440px. Check the shared enquiry destination, telephone display/call link, saved copy and blank-field defaults, mobile action stacking, button hover, keyboard focus and rendering without JavaScript. No further photographs need uploading for this section.

## Shared header behaviour

**Appearance → Customise → Header → Keep the header visible while scrolling** enables the Astro compact main header. Above 72px of page scrolling, only the main navigation row becomes fixed and reduces from 5.5rem to 4.7rem on desktop. Mobile already uses the 4.7rem row. The utility row scrolls away separately, with a divider spanning the full header width and its content aligned to the page shell.

The homepage keeps its initial transparent overlay; the compact header uses the reference dark translucent background, blur and shadow. Inner pages reserve the original main-row space to avoid shifting content when the header becomes fixed. WordPress admin-bar offsets, keyboard navigation, bounded mobile menus and reduced motion are retained. Without JavaScript, the header and native menu remain usable and scroll normally with the page.

The homepage uses the starter’s native menu disclosure. The coordination model, system arc and process timeline render in the initial HTML. All twelve static homepage sections are now ported. The Astro sitewide footer is shared across every template; see [footer setup](footer.md) for its Customiser content, menus and legal links. The optional callback drawer is described below; the motion layer is described below.

The hero WebPs, wordmark and client marks come from Astro revision `891ed840c7369cb50df3e33232e81de49e2699d6`. The asset provenance and publication notes are maintained with the [Astro image records](https://github.com/ezhostforall/GreenDawn/blob/891ed840c7369cb50df3e33232e81de49e2699d6/docs/image-sources.md).

The [pinned Astro source snapshot](./astro-reference/) provides the homepage components and style tokens used to check layout and typography.

## Remaining homepage sequence

The twelve static sections from the pinned Astro `src/pages/index.astro` and the shared sitewide footer are now ported. The optional React enquiry interaction is now implemented. The CSS/GSAP motion layer is now implemented, with reduced-motion and no-JavaScript fallbacks. Final acceptance compares typography, spacing, media, keyboard behaviour and performance across the complete page.

## React callback drawer

The enquiry enhancement now follows the pinned Astro flow. Desktop opens a right-side drawer; up to 42rem it becomes a bottom sheet. Header (including mobile), hero, survey, final enquiry and footer calls to action open the same form. A floating launcher is available sitewide. Existing anchor destinations remain the no-JavaScript and failed-module fallback; keep the Header enquiry destination pointing at a real internal contact page.

### Staging setup

1. Update **both the theme and Greendawn Core**, with Contact Form 7 active.
2. Under **Settings → Greendawn Core → Callback enquiry drawer**, choose **Create callback form**. This creates a separate, idempotent Contact Form 7 form; it does not overwrite the existing contact form or enable the drawer automatically.
3. Follow **Review callback email recipient and delivery settings**. The generated form initially uses the WordPress administration email. Check its **Mail** recipient, domain-valid sender and Reply-To, SMTP delivery and spam integrations before enabling it. Retain the generated form-tag names and validation contract: React is the presentation layer; these fields are the native delivery bridge.
4. Check the site's privacy-policy page and the sitewide Footer telephone. These supply the drawer links. Configure WordPress's privacy-policy page where available; otherwise the internal `/privacy-policy/` route is used.
5. Enable **the sitewide callback drawer**, save and purge the page cache. Disabling it restores ordinary enquiry links. If Core/CF7 is unavailable, the page and anchors still render.
6. Test a qualified enquiry and the direct callback route, verify the actual mailbox, and confirm the success message. Browser/CI checks use intercepted test mail; they do not prove delivery on the staging host.

The visitor selects an interest, at most one contextual answer, project stage and optional location. Choice buttons advance immediately; Back and closing/reopening retain answers in memory. “Just request a callback” skips qualification. First name, company, telephone, email and callback preference are required. Suggested times and notes are optional; a suggested time is a preference in UK local time, not a confirmed booking.

The UI submits the **actual native CF7 form**, retaining its normal submission events, integration fields, validation, spam verdict and mail handling. It does not post directly to a webhook or introduce a second mail endpoint. The success screen appears only after CF7 reports `mail_sent`; this means its mail transport accepted the message, not a guarantee of inbox receipt. Rejection, spam and mail failure retain the visitor's answers. A delivery timeout explicitly says that delivery could not be confirmed; it does not encourage an immediate duplicate submission.

Greendawn Core validates required contact details, telephone format, controlled choice values, field bounds and suggested date/time format independently of React. Its additional honeypot and hashed-IP limit (five otherwise valid attempts within a rolling ten-minute interval) supplement CF7 protections. No raw IP addresses or enquiry values are stored by the Core module; existing CF7 integrations such as Flamingo, SMTP logging or third-party spam services may have their own storage and privacy responsibilities. Protect the public form at the hosting layer too where appropriate; the transient throttle is intentionally modest and is not an atomic distributed rate limiter.

The email includes supplied qualification, callback details, a page path and optional UTM values. It excludes page query strings and fragments. The `greendawn:lead-funnel` event exposes only controlled source, entry point, intent and project stage; no contact details, free text or URLs are sent to that event. No analytics vendor, CRM, Zapier or Trello integration is added in this stage. No visitor information is written to local/session storage.

The small launcher/CSS entry is enabled only when the managed form is configured. React and the application chunk are dynamically imported on the first open. The modal uses native dialog focus containment/Escape, restores opener focus, reserves an independently scrolling body for short/mobile viewports and respects reduced motion. No animation framework is required for the drawer.

Verify at 320px, 390px, tablet and desktop widths, including a short viewport and mobile menu entry; test keyboard-only use, validation announcements, retained inputs after failure, Back navigation, required details, suggested time, privacy/telephone links, all CTA entry points and no-JavaScript fallbacks. CI installs pinned Contact Form 7 6.2.4.1 in disposable WordPress and intercepts all outgoing test email. It also exercises direct HTTP validation, spam, throttling and accepted delivery, rather than mocking the CF7 REST response in the browser.

## Homepage motion

The motion layer follows the pinned Astro animation modules under `docs/astro-reference/src/scripts/animations/`. Ordinary hover/menu/form transitions remain CSS. Hero text/items/proof, the initial photograph settle and hero arc drawing use CSS with the reference durations and stagger offsets. Supported browsers use sampled versions of the reference GSAP easing curves, with cubic-bezier fallbacks. The hero remains a high-priority image and never fades from zero opacity.

Simple section entrances use CSS transform animations, triggered once by a small Intersection Observer at the reference 87% viewport threshold. Their content is always visible; no stylesheet initially hides headings, copy or links. Completing an entrance removes its animation class, avoiding repeated movement when focus enters/leaves a section.

A homepage-only loader dynamically imports GSAP/ScrollTrigger for:

- the six-card problem assembly, connector and answer reveal;
- the system arc and staggered system cards;
- the delivery-process progress line and steps;
- service-photo selection as rows pass the viewport thresholds;
- the featured-project photograph and closing arc;
- additional hero, system and project-story parallax on fine-pointer desktops at least 62rem wide and over 44rem tall.

Scroll ranges, one-second smoothing and coordinated timings come from the pinned source. No new animation dependency is added. The existing generic `data-animate` example remains independently optional; the new homepage loader is not enqueued on ordinary pages.

The Services controller matches photographs to stable audience keys, not array positions, so an omitted photograph does not shift the other mappings. Keyboard focus takes priority over pointer hover, followed by the current scroll selection. Incoming photographs are requested only when selected and decoded before replacing the visible image. Failed/missing photographs retain the current preview. Rapid scroll changes are batched into one frame and superseded image loads cannot overwrite a newer selection. Native CSS previews remain available without the controller.

An initial reduced-motion preference avoids downloading the GSAP scroll bundle. Changing that preference while the page is open disconnects reveals and restores the original transforms, SVG strokes, service classes and image previews; re-enabling motion creates one fresh controller. Resizing updates the reference breakpoints. Font/image readiness and back-forward-cache restoration refresh scroll measurements. Normal page departure removes listeners, observers and animation contexts. An unavailable GSAP chunk leaves visible content and native links/previews usable. CSS hero entrances can run without JavaScript; reduced motion disables them from the first paint.

### Staging acceptance

Update the **theme**, purge the page cache and compare the complete homepage with Astro at 320px, 390px, tablet and desktop widths, including a short landscape window. No Core update, field migration or additional media upload is required for this stage.

Check the hero entrance and arc, problem assembly, system arc/cards, process line, service-photo changes, project crop/parallax and closing arc. Exercise forward/reverse/fast scrolling, keyboard focus while hovering another service, slow/failed images, live resizing, reduced motion, back/forward navigation and the enquiry drawer. Confirm content remains readable with JavaScript or the scroll bundle disabled. Retest Lighthouse on the deployed staging host; automated browser coverage checks cold-page layout shift and preserves the hero's priority hint, but is not a substitute for the hosted performance audit.

## Shared approved process defaults

The approved process language is defined once in Core’s `Greendawn_Process` and adapted to the existing homepage keys. Saved homepage values, stage captions, motion hooks and existing blank/default behaviour are unchanged. New Public Charging process fields use the same initial wording but keep their own saved Page content; this is not a global synchronisation or reset action. See [Public process ownership and restoration](greendawn-public-charging.md#approved-process-ownership-and-restoration).
