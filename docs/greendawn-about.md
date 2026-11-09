# Greendawn About Us

## Install and assign

1. Install the candidate theme **and Greendawn Core** on staging. Keep SCF active for the editing interface.
2. Open the existing **About Us** Page and assign **Greendawn About Us**. Keep its `/about-us/` slug; do not create a duplicate page. The original generic **About** template remains available for existing uses.
3. Save and reopen the page. Its Hero, Story, Process, Call to action, Team, Form and Articles tabs expose content fields only. Section order, layout, colours and typography stay in code. Gutenberg/classic body-layout editing is disabled for this template.
4. Review the two photographs. The hero initially uses the Page’s featured image, or the existing uploaded engineers reference when no featured image is assigned. The story tries the original uploaded `2025/05/Blogs-1.avif`; select/upload an appropriate attachment if that reference is not present. No remote default photograph is fetched as a replacement. Set meaningful alt text in the Media Library.
5. Review **Team members — drag to reorder**. On a page with no saved selection, this contains published Team Members in native Order/title order. Save the chosen list to curate it explicitly. Reorder it here to control this page’s presentation. Draft, private, password-protected and wrong-type records are excluded when rendering, including stale/imported IDs.
6. Select a published CF7 form under **Form**. This is page-specific and does not inherit the Contact template’s Customiser selection. Configure the actual form fields, recipient, spam handling and response messages in CF7. No form or mail settings are created/changed by this template.
7. Check the ten source articles under **Articles**. Initial selection resolves the original About article slugs to published WordPress posts, in their source order. It does not invent new posts or switch to the homepage’s three curated articles. Missing/unpublished articles are omitted; select the correct imported records when necessary.

The captured About copy is provided as Core field defaults and rendered on the server. There is no client-side replacement copy, external portrait embed or page-content duplication. Existing imported `post_content` remains stored as an intake backup, but the structured template does not append it.

Unset fields use their defined defaults. A saved blank stays blank; clearing a saved image/relationship removes it rather than silently restoring a default. Required headings prevent empty titles through the editing form. Source About process wording is retained; only Public Charging has approval to replace its legacy process with the homepage’s canonical wording.

## Composition and reuse

The template uses the approved navy **image-split hero**, shared shell and typography, Story, four-step Method, lime CTA band, native Team grid, CF7 form panel and ten-article track. Its breadcrumbs share the hero surface. The header, footer and React enquiry drawer stay sitewide.

Shared parts under `theme/template-parts/components/` take explicit content arguments: page hero, process, CTA band, team card, form panel and article card. Component instance IDs scope headings and control targets. The homepage process and curated article cards now call the same renderers through adapters, preserving their original field keys, IDs, markup classes, image sizes and motion hooks. Neutral token/shell, process and card rules are extracted from the homepage styles; the other homepage compositions and GSAP entry remain homepage-only.

About’s small page entry adds optional article-track buttons. Native scrolling and every article link still work without JavaScript. The Team grid changes from three columns to two to one. Long prose stays within the shared shell on a narrower reading measure. Forms have visible validation/response styling.

## Staging acceptance

- One H1; breadcrumbs on the navy hero; no default/imported duplicate hero or Divi layout appended.
- Correct source copy/defaults, selected images and eager/high-priority hero media; below-fold images lazy-loaded with responsive sizes.
- Five intended team records, correct portraits/alt text, complete biographies, order and LinkedIn destinations; no public member profile pages.
- Correct page-selected CF7 form; blank validation and visible success/failure feedback. Test delivery on staging with the intended recipient separately; this PR does not change mail routing.
- All source article relationships and internal URLs, optional next/previous controls, keyboard access and reduced-motion behaviour.
- Check 320/375px, tablet and desktop for overflow and spacing. Check the homepage’s process animation, edited defaults, insight accessible names, header/footer and enquiry drawer after installing both packages.

Automated CI fixtures cover SCF field defaults/explicit blanks, import-backup preservation, editor scope, form selection and relationship restrictions. Browser tests cover rendering without SCF/JavaScript, responsive layout, unique IDs, ordering/visibility, empty media, invalid form IDs, optional controls and accessibility. These fixtures are restricted to the disposable local WordPress environment and never touch persistent staging content.

## Native CF7 enquiry markup and attribution

Paste `docs/cf7-project-enquiry-form.html` into the chosen form's **Form** tab. It preserves `fullname`, `project-name`, `tel-758`, `your-email`, `company`, `Projecttype` and `your-message`, along with the three existing tracking names. The native `.gd-contact-fields` grid uses two columns where space permits and one on mobile. The theme disables CF7 auto-paragraph formatting only for forms containing this grid; other forms and HTML-email formatting keep their existing behaviour.

The small theme script records the first landing page and incoming referrer for the current tab in `sessionStorage`, including an entry page with no form. It fills only existing CF7 hidden inputs: `page-url` is the page being submitted, `first_landing` is the first page seen in that tab and `first_referrer` is its original incoming referrer. Later page views do not overwrite first-touch values. URLs retain origin/path only: query strings and fragments are discarded. Referrers may be absent or limited by the browser's referrer policy; direct entries remain blank. Storage denial falls back to the current page/referrer and never blocks the form. No cookies, analytics requests or third-party scripts are added.

Values refresh before serialisation and following resets/back-forward navigation. These are browser-supplied enquiry context, not trusted evidence or an access-control mechanism. Without JavaScript, the enquiry form still renders/submits and tracking fields remain blank.

Include the following in the selected form's **Mail → Message body** if the recipient needs this context:

```text
Submitted from: [page-url]
First landing page: [first_landing]
First referrer: [first_referrer]
```

This does not change recipients or automatically rewrite existing live form definitions or email bodies.
