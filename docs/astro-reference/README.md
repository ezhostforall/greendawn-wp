# Greendawn Astro design reference

Source revision [891ed840c7369cb50df3e33232e81de49e2699d6](https://github.com/ezhostforall/GreenDawn/tree/891ed840c7369cb50df3e33232e81de49e2699d6).

- [Homepage components](src/components/home) contain the section markup and component-level styles.
- [Homepage content and media definitions](src/content) supply the pinned copy, routes and focal positions.
- [Shared styles](src/styles) contain typography, spacing tokens and common layout primitives.
- [Header and utility bar](src/components/layout) record the layout components used in the homepage comparison.

The files are an unmodified documentation snapshot. Their Astro imports refer to the upstream project. Refresh this snapshot from one upstream revision when the design reference changes. The [published Astro homepage](https://ezhostforall.github.io/GreenDawn/) provides the rendered comparison.

The `media/` directory holds the approved Johnsons project photograph for upload to WordPress. It was copied from `public/images/optimized/johnsons-dealership-ev-charging-1200.webp` at the same revision. Its provenance is recorded in the upstream [image sources](https://github.com/ezhostforall/GreenDawn/blob/891ed840c7369cb50df3e33232e81de49e2699d6/docs/image-sources.md). It is not a runtime theme asset.

The five `commercial-ev-*` photographs under `media/` are the service previews referenced by the pinned `src/content/home.ts` and `src/content/media.ts`. They were copied byte-for-byte from the matching files under upstream `public/images/optimized/` and are provided for Media Library upload, rather than runtime theme fallbacks.

The `salvation-army-ev-charging-666.webp` photograph is the Supporting Proof project image from the same revision. It is provided for Media Library upload with the same upstream provenance records.

The Insights cards also use `commercial-ev-electrical-connection-1200.webp` and `commercial-ev-site-survey-1024.webp`, copied byte-for-byte from the same pinned upstream revision. Their fleet photograph is shared with the Services preview. All three are Media Library content, with provenance maintained in the same upstream image sources.
