# Greendawn homepage pilot

The Greendawn Homepage template renders the Hero, Trust, Problem, System and Process sections from the Astro reference. The branded header is shared across the site, with a transparent overlay on this template and a navy background above inner-page content. The layout and proof stage order live in PHP. Greendawn Core owns the copy and image defaults and registers the editable homepage fields when Secure Custom Fields is active.

## Set up the staging page

1. Install the candidate theme and Greendawn Core together. Activate Secure Custom Fields for the editing fields.
2. Create a Page, select **Greendawn Homepage** as its template and preview it before assigning it as the static homepage. Empty copy fields show the Greendawn defaults. The hero defaults to the Media Library attachment at `2026/11/greendawn-engineers-electrical-distribution-1600.webp` relative to the site’s uploads folder. If the file was uploaded under a different path, select it in **Hero photograph**. Set its alt text in the Media Library.
3. In **Appearance → Customise → Header**, set **Discuss your site** and the current live enquiry destination. The header and hero use this setting together. Use **Utility bar text** to edit the message and **Reset utility bar text** to restore the original wording, then publish the change. The Explore our projects link defaults to the existing public Greendawn gallery. Use the Projects destination field to point it at the migrated project content when ready.
4. Assign the Primary menu with About Us, Our Projects, the three Our Solutions children and Aftercare. Point the entries at their existing live destinations until those pages move. Assign the telephone and customer support links to the Utility menu. The supplied raster wordmark is the fallback when no custom logo is configured.
5. Verify the Page preview and then assign it under **Settings → Reading** when ready. Compare it with the Astro reference at 390px and 1440px, exercise the menu by keyboard and confirm the enquiry and gallery destinations.

The homepage editor groups fields under Hero, Trust, Problem, System and Process. Edit the four shared stage labels in Hero and their full descriptions in Process. The two System guides default to the existing public guide pages and can be edited when those routes move.

The homepage uses the starter’s native menu disclosure. The coordination model, system arc and process timeline render in the initial HTML. The remaining sections, motion and callback drawer follow separately.

The hero WebPs, wordmark and client marks come from Astro revision `891ed840c7369cb50df3e33232e81de49e2699d6`. The asset provenance and publication notes are maintained with the [Astro image records](https://github.com/ezhostforall/GreenDawn/blob/891ed840c7369cb50df3e33232e81de49e2699d6/docs/image-sources.md).

The [pinned Astro source snapshot](./astro-reference/) provides the homepage components and style tokens used to check layout and typography.
