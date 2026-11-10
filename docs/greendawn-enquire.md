# Greendawn Enquire

After installing the candidate **theme and Greendawn Core**, assign **Greendawn Enquire** (`templates/greendawn-enquire.php`) to the existing `/enquire/` Page. Use **Project enquiry → Project enquiry — Contact Form 7 form** to select the published detailed sales form. Existing imported body content is retained in storage and is not appended. Gutenberg/classic body-layout editing is hidden for this template only; ordinary Pages and Posts are unchanged.

## Approved composition and source content

- Compact navy text-only hero: **Let’s start talking / about your project.** No hero image is introduced, including when the Page has a featured image.
- Detailed project form first, beside the full six-step source process on desktop and above it on tablet/mobile. An optional form heading starts empty, matching the source.
- Six page-owned steps: **Consultation**, **Hardware Quote**, **Site Survey**, **Full Quote**, **Acceptance & Installation**, **Aftercare**. Every source paragraph, including the complete Full Quote explanation, remains available and visible. This is the existing Enquire process, independent of the four-step homepage/Public process.
- **Have a Question?** and the complete source introduction, followed by grouped address, telephone and email methods. Contact labels and introduction are page content; actual contact values are shared with the footer/drawer.

No related-article collection, process photographs, extra sales form or duplicate legacy footer forms are added. Header/footer/drawer behaviour remains sitewide. New contact-panel and vertical process-list renderers accept explicit data and fixed presentation.

## Editing and shared contacts

Core owns the `greendawn_enquire_*` fields in Hero, Project enquiry, Six-step process and Contact panel tabs. Editors change copy and the selected form, without controls for colours, spacing, columns or section order. Process positions remain fixed. Initial source wording is an unsaved default; reading it writes no metadata. Saved edits and saved blanks are retained across updates and continue to render with SCF inactive. Restoring approved wording is an explicit content edit, not an automatic overwrite.

Edit shared **telephone, email and address** under **Appearance → Customise → Footer**. The Enquire contact panel reads these same native settings at render time, including intentionally cleared values. No duplicate page-level contact-value fields are created. The shared address retains the existing site's multiline formatting. Telephone/email links are built from validated values and escaped on output; invalid telephone text remains plain text and invalid email addresses create no mail link. Long content wraps within the shell.

## Form assignment and setup

Create/configure the detailed form in CF7 and select it on the Page. No form, recipient, mail template or drawer option is provisioned or rewritten by this template. It does not inherit the legacy Contact template's Customiser form setting. A missing/unselected/invalid form or inactive CF7 produces a visible unavailable message and a link to the contact panel on this page, avoiding a link back to the same Enquire route. Administrators see a setup warning on the Page edit screen when its form is unavailable.

[Clean detailed form markup](cf7-detailed-enquiry-form.html) retains the source field names and content:

| Source field name | Purpose | Required |
|---|---|---|
| `fullname` | Full Name | Yes |
| `phone` | Phone Number | Yes |
| `your-email` | Email Address | Yes |
| `postcode` | Postcode | No |
| `numbercharger` | Number of Chargers | No |
| `projecttype` | Workplace, Fleet, Public parking, Showroom, Gym, Public sector, Other | Yes |
| `company` | Company Name | No |
| `your-message` | Tell us about your project | No |

The cleaned markup uses the existing `gd-contact-fields` grid, native CF7 tags, autocomplete and no third-party row/column shortcodes or paragraph wrappers. Optional charger count is a positive whole number. The source Project Type initially selects Workplace; no choices are renamed. The submit label remains **Start Your Journey**. This is a pasteable definition and a disposable test fixture, not an automatic replacement of existing forms. Review existing Mail/Mail (2), Messages, Additional Settings, spam integration and any consent requirements before use.

Use `Reply-To: [your-email]` in the reviewed CF7 mail configuration and include the required field values in its body. Configure the actual recipient and sender in CF7/site mail settings. Neither is a theme setting.

## Attribution and charger context

The definition includes the existing native attribution tags `page-url`, `first_landing`, `first_referrer`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`. Existing first-touch/session logic supplies these when JavaScript is available, including after a form reset. URLs retain origin/path only; query strings/fragments are excluded. With JavaScript disabled, the form and product selection remain usable but these client-collected attribution tags remain empty.

For product quote journeys the definition also includes `chargerId`, `chargerTitle`, `chargerURL`. The existing `/enquire/?charger=<slug>` route resolves a published, non-passworded EV Charger. Its name and local link appear once above the form. Explicit hidden product tags are filled on the server, so product context also survives without JavaScript.

On submission, Core checks the current CF7 form's saved association with a public Enquire-template Page. It does not trust a client-supplied container ID, product title or URL. A valid product ID is re-resolved through the existing public-product adapter; malformed, wrong-type, private, deleted or passworded products yield empty product context. Other fields and unrelated forms are untouched. The callback drawer retains its own existing managed-form policy and configuration. Forms without the optional product tags do not acquire new fields automatically.

Include these optional tags in the reviewed Mail body if attribution/product details are needed:

```text
Page: [page-url]
First landing: [first_landing]
First referrer: [first_referrer]
UTM source: [utm_source]
UTM medium: [utm_medium]
UTM campaign: [utm_campaign]
UTM term: [utm_term]
UTM content: [utm_content]
Charger ID: [chargerId]
Charger: [chargerTitle]
Charger URL: [chargerURL]
```

## Staging acceptance

1. Assign the template on the existing Page and select the intended detailed sales form. Reconcile its eight fields, choices, placeholders and reviewed mail settings.
2. Confirm all six complete process descriptions and shared contact details, including phone/email links, at desktop/tablet and 320/375px widths.
3. Verify required-field feedback, visible success/delivery-error messages, keyboard operation, attribution and actual intended-recipient delivery. CI uses intercepted success/failure responses and sends no live email.
4. Follow a charger's native quote link with JavaScript disabled. Confirm one visible product context and the correct product details in a real configured email. Repeat an ordinary enquiry with no product selected.
5. Check the React drawer remains independent. Recheck About/Workplace/Aftercare forms, header/footer, and audience CTAs reaching this Page.
6. Save a process edit/blank and reload; confirm it persists and the imported body remains stored. Check the visible fallback if no form is selected or CF7 is unavailable.

Disposable WordPress/SCF/browser fixtures do not alter persistent staging or production. Actual form delivery and mailbox routing remain staging checks.
