# Site footer

Open **Appearance → Customise → Footer** to enter an optional business summary, contact details and a call to action. Supply both CTA fields to show the link. The footer uses the site name and the optional Site Identity logo.

Under **Menus**, assign Footer, Footer secondary, Footer social and Footer legal as needed. The existing Footer location is preserved. Use descriptive link text for social profiles. Each location renders one level and empty locations are omitted.

A site plugin can supply approved client disclosures via the `greendawn_footer_disclosure` filter. It receives an empty string by default and the theme filters the returned markup through `wp_kses_post()`.

On staging, check the default footer with no assignments and a fully configured example. Check long menu labels and contact details at 320 px, distinct navigation landmarks, keyboard focus and CTA contrast. Confirm that a partial CTA and unassigned menus are omitted.
