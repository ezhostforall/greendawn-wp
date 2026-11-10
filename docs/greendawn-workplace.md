# Workplace Charging template

Assign **Greendawn Workplace Charging** to the existing `/workplace-charging/` Page after installing the updated theme and Greendawn Core. Do not create a duplicate Page, import its builder layout, or change its slug. SCF provides the content fields; the server-rendered page also works with SCF inactive.

The approved layout uses the shared navy image-split hero, four always-visible benefits, a six-card native charger grid, lime CTA, Workplace’s own four-step process and two photographs, the retained inline form, and all ten source article destinations. There are no flip cards or autoplay carousels. Public Charging’s separately approved process replacement does **not** apply here. Header/footer remain sitewide. About now shares the related-articles component; its content and identifiers remain unchanged.

## Content and media

SCF’s Hero, Benefits, Chargers, Call to action, Process, Form and Articles tabs edit fixed content slots. Editors cannot add, remove or rearrange design sections. Gutenberg and the body editor are disabled on this template only. Any imported body remains stored as a backup and is not appended to the page.

Unset copy fields display the existing source wording from Core. Defaults do not write page metadata. Saved blank copy or cleared media/relationships stay empty. Each Page’s selections are independent.

The hero initially uses the Page’s featured image, otherwise the locally uploaded `2026/11/commercial-ev-workplace-charging-1200.webp`. Process photographs resolve the locally uploaded `2025/05/Blogs-1.avif` and `2024/11/Electricians-at-fuse-board.png` (including WordPress `-scaled` variants). If those originals have not been uploaded/imported, choose the matching photographs in the Media Library. No public-site image request, client-side replacement or placeholder image is used. Missing media is omitted. Check alt text on each attachment. The hero is eager/high priority; below-fold images are responsive and lazy-loaded. Product photographs are contained rather than cropped.

The six default charger selections match the source order:

1. `hellonext-h1-22`
2. `project-ev-ac-pro-earth-dual-7-22kw`
3. `simpson-partners-7-22kw`
4. `sync-ev-22kw`
5. `sync-ev-22kw-2`
6. `easee-charger-22kw`

Selections resolve existing published, password-free native records only. The card uses that record’s current title, featured image and permalink. Its Request Quote link first opens the charger detail page, whose existing quote drawer carries the canonical product identity. No product copy/specifications are duplicated into Page fields. Drag selected chargers/articles to reorder; saved empty selections remove those collections. Articles initially select the same ten source slugs documented for About, on this site’s host. The article track can be scrolled without JavaScript; optional previous/next controls respect reduced motion.

## Contact Form 7

Select the reviewed form in this Page’s **Form → Contact Form 7 form** field. This is independent of About, the old Contact template’s Customiser selection and the React callback drawer. No form/recipient is selected automatically. Configure the recipient, mail tags and visible feedback in CF7. The native markup and attribution fields are documented in [the enquiry form guide](greendawn-about.md#native-cf7-enquiry-markup-and-attribution).

The source has no form heading, so this field defaults to empty. The form retains an accessible section label and a centred full-width panel. An optional heading uses the shared two-column composition. A missing/invalid form displays an explicit unavailable message and local Enquire link; it never executes an unrelated shortcode or exposes draft forms.

## Staging acceptance

1. Assign the template to the existing Page, save and reopen. Check the fixed editing tabs, prefilled copy, media and all six chargers/ten articles. Confirm the original process copy and imported body backup remain intact.
2. Select the reviewed CF7 form and confirm its configured recipient and mail tags. Check invalid and successful submissions, visible feedback and first-touch/UTM attribution. No test emails or live content are created by this implementation.
3. Review at 320px, tablet and desktop: shared header/footer, navy split hero, all benefits visible, charger grid, two process photographs and inline form; no horizontal page overflow. Check product images against the actual imported attachments.
4. Follow each charger to its native detail and Request Quote context; follow CTA/articles on the staging host. Test keyboard focus, article scrolling, reduced motion and an intentionally empty optional field.

CI uses disposable local WordPress/SCF/CF7 data. It checks editor scope, defaults before saving, explicit blanks, independent form selection, filtered/reordered relationships and import preservation. Browser checks cover no JavaScript/SCF, three viewport widths, content/destinations, form validation, accessibility and the charger-to-quote journey. Screenshot fixture photographs/titles are test data; actual imported media and live mail delivery remain staging acceptance.
