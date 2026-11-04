# Service detail pages

Agency Core ships the opt-in `services` module. It owns the Service post type and field schema. The theme renders Service entries with `single-service.php`. Client projects can adapt the module in their own Agency Core fork or replace it via `agency_core_available_modules`.

## Enable on staging

1. Update the theme and Agency Core through **Dashboard → Updates**.
2. Open **Settings → Agency Core**, tick **Enable Services** and save. The setting refreshes the rewrite rules. If the site uses Plain permalinks, choose a pretty permalink structure to use the path below.
3. Follow the Secure Custom Fields link on that screen if SCF still needs to be installed or activated.

Service entries use `/services/service-name/`, independent of the blog permalink prefix. The post type has no archive, so an ordinary Page can own `/services/`. Existing sites can still use the `agency_core_enabled_modules` filter to manage modules in code.

When Services is enabled without SCF, the title, excerpt and featured image remain available through WordPress.

## Services overview

Create a Page titled **Services** with the slug `services`. Assign the **Services Overview** template, then save and reopen it to edit the **Introduction** field. The grid lists published Service entries alphabetically and links each card to the detail page. Cards use the Service title, excerpt and featured image.

When the introduction is empty or SCF is inactive, any existing Page body is displayed. Clear or migrate that body before assigning the template if it contains an older services grid. On staging, check that Web Design appears, its card opens the correct URL, and drafts are excluded. Also check the empty grid and a service without an excerpt or featured image.

## Editing contract

| Content | Source |
|---|---|
| Title, card summary and hero introduction, image | WordPress title, excerpt and featured image |
| Description | `service_description`, basic rich text |
| Benefits | `service_benefits` rows with `benefit` |
| FAQs | `service_faqs` rows with `question` and `answer` |
| Call to action | `service_cta_label` and `service_cta_url` |

Editors manage the fields and row order. The PHP template controls the section order and design. The Service editor uses title, excerpt, featured image and the field panel. Posts retain their existing editor. If SCF is inactive, native Service content still renders and the structured sections are omitted.

## Shared presentation and images

The homepage and Services use the same hero treatment and service cards. The detail page uses the Service title as its H1 and its excerpt as the introduction. A configured call to action appears in the hero and repeats after the detail sections if any are present. The fallback breadcrumb links to the published root Services Page when it uses the Services Overview template. Rank Math retains control of its own breadcrumb trail.

Choose a featured image with the subject near the centre. The detail hero displays it in a centred 4:3 crop, and cards display a centred 16:9 crop. Use Media Library alt text to describe an informative image. Leave it empty if the image is decorative. Check both crops when choosing the image.

The split hero requests eager loading. WordPress supplies responsive sources from the attachment metadata and determines loading priority. Service cards use proportional attachment variants so WordPress can build a responsive source list from the original image ratio. CSS sets the card crop. Cards provide a sizes hint for the one- or two-column grid and leave lazy loading to WordPress, since the first card can be visible on initial load. Existing uploads with the standard WordPress image variants can use this layout without regenerating the card crop.

## Staging acceptance

- As an Editor, create a Service with an excerpt, featured image, description, two FAQs, benefits and a CTA. Preview it, then publish it and open the expected detail URL. Confirm drafts are unavailable to logged-out visitors.
- Reopen it, reorder the FAQs and verify that the public page follows the saved order. Test FAQ expansion with the keyboard and verify the CTA goes to the configured destination.
- Verify a minimal service without optional sections, and a populated service with long copy and a narrow viewport. Confirm that a Page at `/services/` can coexist with detail URLs and that posts still open in Gutenberg.
- Inspect the hero and cards at narrow and wide widths. Check the rendered sizes and image requests, and confirm below-fold images are lazy-loaded where appropriate. Record the LCP element on hosted staging.
