# Greendawn sitewide footer

The footer is shared by the homepage, inner pages, services, editorial templates, search and 404 pages. Its layout matches the pinned Astro `SiteFooter.astro`: wordmark/tagline, Company, Our Solutions and Support link columns, contact details and the legal/social row. It renders in PHP without JavaScript or SCF. Greendawn Core owns the default copy and links; the theme owns presentation.

## Content and defaults

Open **Appearance → Customise → Footer**. The controls are prefilled with the pinned Greendawn content. Edit the tagline, column labels, telephone, email, address, legal name, company number and VAT number. Clearing a field hides that optional detail. **Reset footer content** restores the supplied defaults in the preview; publish when ready. The reset leaves menu assignments unchanged.

The existing `summary`, `contact`, `cta_label` and `cta_url` settings are preserved. Summary is presented as the tagline, contact as the multiline address/contact block, and a complete optional CTA appears in the contact column. A partial CTA or `#` destination is omitted. The wordmark follows **Site Identity → Logo**, with the same bundled default as the header.

The homepage Final enquiry telephone now defaults to this sitewide Footer telephone. A number already saved in the homepage SCF field remains an explicit override; clear that homepage field to follow the global number. Contact-page fields and manually authored menu telephone links remain separate content and should be kept consistent.

The legal name, registration/VAT numbers, address and contact details come from the pinned Astro site configuration. Review them before production publication. Optional approved disclosures supplied through `greendawn_footer_disclosure` are retained and escaped with `wp_kses_post()`.

## Native menus and internal links

Assign menus under **Appearance → Menus → Manage Locations**:

| Location | Footer area |
| --- | --- |
| Footer company menu (`footer`, preserved) | Company |
| Footer solutions menu (`footer_secondary`, preserved) | Our Solutions |
| Footer support menu (`footer_support`, new) | Support |
| Footer legal links (`footer_legal`, preserved) | Legal row |
| Footer social links (`footer_social`, preserved) | Social row |

Assigned menus replace their column’s default links, render one level and preserve native WordPress URL/target behaviour. An assigned empty menu is omitted. Without an assigned menu, Greendawn Core supplies the reference links on the current WordPress site. Existing imported `greendawn.co.uk` and `www.greendawn.co.uk` links retain their paths/query strings/fragments on the current environment. Explicit third-party links remain external; LinkedIn is external by design.

The default Support enquiry link shares **Header → Call-to-action destination** and is omitted when that destination is blank or `#`. If you assign your own Support menu, its enquiry link is managed as a native menu item.

Check all internal destinations before publication, especially `/about-us/`, `/gallery/`, the service pages and legal pages. The default Terms link is the internal `/terms-and-conditions/` page, replacing the Astro-only PDF asset route. Create that page or assign a Legal menu pointing to the approved PDF uploaded to WordPress. The other legal defaults are `/privacy-policy/` and `/modern-slavery-statements/`.

## Staging review

Update the theme and Greendawn Core together. Check the default footer before assigning menus, then review the Customiser values and any existing saved content. Confirm logo, telephone/email links, multiline address, company/VAT details, legal destinations and social profile.

Compare 320px/390px mobile, 800px tablet and 1440px desktop on the homepage and inner pages. Check long content, native menu overrides, keyboard focus, hover, reduced motion, clear/reset behaviour, and that inner pages do not load the homepage stylesheet. No new photographs or documents are bundled as runtime footer assets.
