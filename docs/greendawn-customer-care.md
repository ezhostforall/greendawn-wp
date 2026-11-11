# Greendawn Customer Care

Install the candidate **theme and Greendawn Core**, edit the existing `/customer-care/` Page and assign **Greendawn Customer Care** (`templates/greendawn-customer-care.php`). Keep the existing URL and imported Page record. No new Page, form, mailbox, CPT or deployment is provisioned by this implementation.

## Approved composition and content ownership

The approved hi-fi design and current source agree on a compact text-only **Customer / Support** hero, the complete introduction, four visible support commitments, then **Get In Touch** with **Contact Us On Whatsapp! / Open Whatsapp** beside **Call Back Request Form**. All source wording is retained. The legacy form’s H6 is corrected to H3 under the contact H2. The four commitments remain a semantic list, with the source emphasis retained; the visible numbers are decorative.

The source uses a Divi form, not CF7. Recreate its five required fields using the supplied CF7 definition below. No Divi, row/column add-on, third-party widget or WhatsApp SDK is required. The WhatsApp action is an ordinary link, with no external request until the visitor chooses to follow it.

Greendawn Core owns `greendawn_customer_care_*` fields under **Hero**, **Support points** and **Support contact**. The four point positions and page layout are fixed. Editors change words, the WhatsApp destination and a published CF7 form selection. They cannot change colours, typography, section order, columns or spacing. No image is required; a featured image remains stored but is not used in this text-only hero.

Initial values are server-resolved defaults and appear in SCF before saving; reading defaults does not write metadata. Saved edits and saved blanks are retained. Clearing both parts of a support point removes it, while a remaining title or continuation stays visible. Clearing a WhatsApp URL or its label removes the action. URLs must use HTTPS `wa.me` with an international phone number; stale invalid metadata cannot render an arbitrary destination. The initial source route is `https://wa.me/443455120044`.

The template remains readable without JavaScript or SCF. If Core is inactive, available saved scalar content still renders through native metadata and unvalidated WhatsApp links are omitted. Imported `post_content` remains in storage as a backup and is not appended to the fixed template. Gutenberg/classic body editing is disabled only for this template. The native password gate runs before rendering structured support content.

## Recreate and select the support form

1. In **Contact → Contact Forms**, create a dedicated **Customer Care** form.
2. Paste [cf7-customer-care-form.html](cf7-customer-care-form.html) into its **Form** tab. It uses the existing native field grid, visible labels and the original issue choices. All five fields are required: Name, Email Address, Phone Number, Issue Type, Issue Summary. The submit label remains **Submit**.
3. Configure **Mail**, **Mail (2)** if deliberately enabled, **Messages**, spam protection and **Additional Settings**. Review the intended support recipient independently of sales. Do not reuse the Enquire sales form simply because it appears in the dropdown.
4. Return to the Customer Care Page and select the published form under **Support contact → Dedicated support — Contact Form 7 form**. Save and verify delivery on staging.

The selector starts empty. It does not inherit Enquire, Business Installations, Aftercare, the legacy Contact Customiser selection or the React callback drawer. Selecting a form does not rewrite its definition, recipients or messages. Administrators receive a scoped setup warning when the selection is unavailable. Form administration remains in CF7.

Suggested Mail configuration, using your reviewed actual mailbox/domain:

| CF7 setting | Configuration |
|---|---|
| To | Actual support mailbox, confirmed by the site administrator |
| From | A verified sender on the site’s own domain, using the existing delivery integration |
| Subject | `Customer support: [issue-type]` |
| Additional headers | `Reply-To: [your-email]` |
| Message body | The tags below, plus any existing reviewed delivery information |

```text
Name: [your-name]
Email: [your-email]
Phone: [phone]
Issue type: [issue-type]

Issue summary:
[issue-summary]

Page: [page-url]
First landing: [first_landing]
First referrer: [first_referrer]
UTM source: [utm_source]
UTM medium: [utm_medium]
UTM campaign: [utm_campaign]
UTM term: [utm_term]
UTM content: [utm_content]
```

Existing shared attribution fills these hidden fields with JavaScript and restores them after a successful form reset. First-landing/referrer/page URLs retain origin/path only, dropping query strings and fragments; campaign values are collected separately. No client-collected attribution is promised without JavaScript. These values provide context and must not determine authorisation or recipient routing.

An unselected, wrong-type, unpublished or unavailable form shows **The support form is currently unavailable.** with the page’s valid WhatsApp fallback. It never silently swaps in a sales form or links the visitor to `/enquire/`. With no valid WhatsApp URL/label, the unavailable message remains visible without a dead link. The existing sitewide header, footer and drawer remain available, but this support section does not add a sales CTA or trigger that drawer.

## Shared components and regression boundary

The template reuses `page-hero`, a developer-selected support variant of `feature-grid`, and `form-content`. The support variant uses list items rather than introducing an empty section heading; existing audience benefit grids are unchanged. `form-content` gains explicit unavailable-message/link-label arguments while retaining its existing defaults for every other page. No homepage animation bundle, React composition or frontend dependency is added.

## Staging acceptance

- Assign the template, confirm the full introduction/four commitments and review the WhatsApp destination.
- Create/select the dedicated five-field CF7 support form and verify its support recipient, From/Reply-To and messages independently of sales.
- Check 320/375px mobile, 834px tablet and desktop: readable content, stacked contact section, visible labels, wrapped buttons and no horizontal overflow.
- Test keyboard navigation, required fields, invalid email, visible success/error feedback and a real intended-recipient delivery. Confirm issue type/summary and attribution in the delivered message.
- Open WhatsApp manually and confirm it reaches the intended account. Automated checks do not send WhatsApp messages or prove account availability.
- Save an edited point and cleared optional field; reload and confirm persistence. Check password-protected content remains gated and the imported body backup remains stored.
- Recheck Enquire/Business sales forms, the legacy Contact selection and the sitewide drawer.

Disposable CI creates only local test forms with distinct `support@example.test` and `sales@example.test` recipients. Browser success/error responses are intercepted and validation requests remain invalid; the suite sends no live email. Automated recipient-separation checks do not replace a real staging delivery test.
