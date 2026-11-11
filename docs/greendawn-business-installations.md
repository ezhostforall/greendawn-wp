# Greendawn Business EV Charging Installations

Install the candidate **theme and Greendawn Core**, then assign **Greendawn Business EV Charging Installations** (`templates/greendawn-business-installations.php`) to the existing `/business-ev-charging-installations/` Page. Select its reviewed CF7 form under **Sales enquiry**. Existing imported `post_content` remains stored as a backup and is not appended to the fixed layout. Gutenberg/classic body editing is disabled only for this template.

## Approved composition and source content

The approved internal-page mock-up and captured source inventory define this composition. The supplied 10 November page capture also confirms the service copy and detailed form structure.

1. Compact text-only navy hero: **EV Charger Installation for Businesses / That Think Ahead**, with the full introduction. The legacy hero illustration is not part of the approved compact hero. A native featured image is retained in storage but is not rendered here.
2. **How We Can Help You:** and the full introduction, followed by three visible service summaries: **Site Assessment & Consultation**, **Professional Installation**, **Ongoing Support & Maintenance**. The detailed sales form sits beside these on desktop and follows them on smaller screens, retaining the approved reading order.
3. **Who we work with…** and its full introduction, followed by three full testimonial panels: **Johnsons Cars**, **Elite Hotels**, **Salvation Army**. Every quote is retained, including its supplied spelling. The original carousel’s hidden panels are included; nothing is line-clamped or rotated automatically. No individual attribution is invented.

The Johnsons Cars video retains source ID `t96HrzEREM0`. All three panels support separately edited local media, quote, organisation, optional attribution and optional YouTube link. There is no new testimonial CPT, slider, extra process section, article track or duplicate legacy footer form. Header/footer/drawer remain sitewide.

## Content editing and defaults

Core owns the `greendawn_business_*` fields in Hero, How we help, Sales enquiry and Testimonials tabs. There are three fixed service positions and three fixed testimonial positions. Editors change content and native image selections; they cannot select layouts, section order, colours, spacing or columns.

Initial copy/media references are unsaved defaults, resolved on the server without writing metadata. Saved edits and saved blanks are preserved. Clearing a video removes its action; clearing a poster removes that image without silently restoring it. Quotes remain readable when images/videos are missing. Clear both a testimonial’s organisation and quote to omit that panel. Restore approved content explicitly if required. Rendering works with SCF inactive while Core supplies native metadata/defaults; without Core, available saved scalar fields/media still render safely and unvalidated video destinations are omitted.

All copy is plain text, escaped before paragraph formatting. Arbitrary quote HTML, layout code, iframe markup and shortcode fields are not exposed.

## Local media and video behaviour

Unset image fields resolve local Media Library attachments matching these imported paths, including native `-scaled` variants:

| Position | Initial local attachment reference | Presentation |
|---|---|---|
| Johnsons Cars | `2026/11/johnsons-dealership-ev-charging-1200.webp` | Project photograph/video poster; cover fit |
| Elite Hotels | `2024/11/elite-hotels.png` | Logo; contained with padding |
| Salvation Army | `2024/11/SA.png` | Logo; contained with padding |

If an import/upload uses another path, select the actual local attachment explicitly in the field. Missing logos are omitted; the template does not hotlink them or fetch YouTube thumbnails. Native media uses responsive WordPress markup and lazy loading. An absent poster still leaves the video action available. Image fitting is fixed by the approved panel design, not offered as an editor layout control.

A YouTube URL is content, not arbitrary embed HTML. Core accepts supported HTTPS `youtu.be`, YouTube watch/embed/shorts and YouTube privacy-enhanced embed URLs, validates the eleven-character video ID and generates fixed canonical watch/playback destinations. Other hosts, schemes, credentials, explicit ports and malformed IDs are rejected in SCF and produce no frontend video action if stale metadata bypasses validation.

The native **Watch Johnsons Cars video testimonial** link works without JavaScript. With JavaScript, normal activation replaces the local poster with a titled `youtube-nocookie.com` iframe and moves focus to it. The player’s dimensions remain stable. No third-party video resource is requested before activation; playback starts only after the visitor chooses the action. The organisation heading also retains a normal YouTube fallback link after the player opens. No new modal, autoplay carousel, GSAP composition or frontend dependency is introduced.

Real player availability and any site-level consent/CSP integration remain staging checks. CI intercepts the playback request to test activation, focus, destination and dimensions without downloading a live third-party player.

## Form assignment and routing

Select a published detailed sales form explicitly on this Page. It may be the same reviewed form as Enquire; the selection is stored independently and does not rewrite either Page’s other fields. The existing [detailed CF7 definition](cf7-detailed-enquiry-form.html) preserves the eight source fields, choices and **Start Your Journey** submit label. Configure the recipient, sender, Mail/Mail (2), messages and additional settings in CF7. No form or recipient is provisioned, guessed, inherited or changed automatically.

Missing/unselected/invalid forms or inactive CF7 show the existing visible unavailable message and local `/enquire/` link. Administrators receive a scoped edit-screen setup warning. The Contact template’s Customiser selection and the React drawer remain independent.

Existing page/first-landing/referrer/UTM collection and visible CF7 response styling apply. Add the required attribution tags to the reviewed mail body, following [Enquire setup](greendawn-enquire.md). With JavaScript disabled the form is still present, but client-collected attribution remains empty. This landing page introduces no product-selection route or automatic charger selection.

## Staging acceptance

- Assign the template, confirm the full hero/service/testimonial copy, and select the intended detailed sales form.
- Reconcile the native project photograph and both client logos. Check the local images actually load; missing imported references are not evidence of successful media migration.
- Check the desktop two-column enquiry section and three testimonial cards, then tablet/mobile stacking and wrapping at 834/375/320px. Read every quote to its end.
- Activate the Johnsons video with mouse/keyboard, confirm actual playback, and check the ordinary fallback link without JavaScript.
- Check required-field feedback, visible success/delivery-error messages, attribution and intended-recipient delivery. CI sends no live test email.
- Save an edited quote/attribution and a cleared image/video; reload and confirm persistence without changing the imported body.
- Recheck the Enquire form, audience pages, About/Aftercare forms, sitewide navigation and callback drawer.

Disposable SCF/browser fixtures never modify persistent staging or production. Their source copy and native media checks do not replace reconciliation of the actual staging attachments or a real delivery test.
