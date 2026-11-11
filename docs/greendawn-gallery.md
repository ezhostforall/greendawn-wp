# Greendawn Gallery

Install the candidate theme and Greendawn Core. Edit the existing `/gallery/` Page and select **Greendawn Gallery**. The fixed template shows the approved Our Projects hero, five case studies, the enquiry CTA and the photo gallery. The CTA goes to the current site's `/enquire/` route.

The **Projects** tab holds an ordered list of stories. Each story has a title, fact chips (one per line), formatted copy, optional status text and ordered Media Library photographs. The first selected photograph is the lead image. The **Photo gallery** tab controls the separate photo grid. Drag rows or photos to change their order. Edit photograph descriptions and captions in the Media Library.

The initial content uses all five source stories, including Honda's “Coming Soon…” status. Review the source figures with Greendawn before changing them. Johnsons has 250+ sockets and 50+ sites in the fact chips, with 281 chargers and 60 sites in the story. Salvation Army has 50 sites in the fact chips and more than 45 locations in the story. The Gallery Page owns these values independently of the homepage and the legacy Projects and My Case Studies records.

## Match the photographs

The [ordered source inventory](../plugins/greendawn-core/includes/gallery-defaults.php) lists 19 story photo placements and 39 gallery placements across 49 upload paths. Defaults match Media Library attachments by relative upload path, including the WordPress `-scaled` filename variant. Review each lead photo, thumbnail group and gallery position on staging. If an attachment is missing or has been imported with a different name, upload or select the corresponding original and place it in the source order. The same attachment can appear in its story and the main grid.

Missing photographs leave the associated story readable. The final grid appears once photographs are selected. Defaults appear before saving. A saved empty selection stays empty, so complete the photo selections before saving if the import is still in progress. The template reads saved fields with SCF inactive and retains the imported Page body in storage.

Each image links to its full image file. With JavaScript enabled, the link opens a native viewer with captions, Previous and Next controls, arrow keys, Escape and focus return. Full images load on activation. The gallery uses responsive, lazy-loaded thumbnails and the Media Library's alt text and captions.

## Staging checks

- Confirm five stories, their fact chips and Honda's status.
- Reconcile the 58 photo placements against the inventory, review crops and check descriptions and captions.
- Test the image links, viewer controls and the local enquiry route at narrow and wide widths.
- Save one content edit and reorder a photo. Reload the Page to verify the saved order.

Disposable WordPress tests use three local project photographs to verify path matching, scaling, order and the viewer. The complete staging Media Library still needs to be checked against the inventory.
