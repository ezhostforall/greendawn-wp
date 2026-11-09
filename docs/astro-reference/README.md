# Greendawn Astro design reference

Source revision [891ed840c7369cb50df3e33232e81de49e2699d6](https://github.com/ezhostforall/GreenDawn/tree/891ed840c7369cb50df3e33232e81de49e2699d6).

- [Homepage components](src/components/home) contain the section markup and component-level styles.
- [Homepage content and media definitions](src/content) supply the pinned copy, routes and focal positions.
- [Shared styles](src/styles) contain typography, spacing tokens and common layout primitives.
- [Header, utility bar and sitewide footer](src/components/layout) record the shared layout components.
- [Animation modules](src/scripts/animations), [motion runtime](src/scripts/motion/runtime.ts) and [homepage composition](src/scripts/home.ts) record timings, scroll ranges and responsive conditions.
- [Navigation script](src/scripts/navigation.ts) records the 72px compact-header threshold and scroll-state behaviour.
- [Lead capture component](src/components/lead), [choices](src/content/lead-capture.ts), [types](src/types/lead.ts), [controller/transport](src/scripts/lead-capture) and [handoff notes](docs/lead-capture-stage-03.md) record the callback reference. The upstream submission remains a demonstration; the WordPress implementation uses native CF7 delivery.
- [Site and navigation configuration](src/config) records the pinned footer contact/company details and menu labels/routes.

The files are an unmodified documentation snapshot. Their Astro imports refer to the upstream project. Refresh this snapshot from one upstream revision when the design reference changes. The [published Astro homepage](https://ezhostforall.github.io/GreenDawn/) provides the rendered comparison.

The `media/` directory holds the approved Johnsons project photograph for upload to WordPress. It was copied from `public/images/optimized/johnsons-dealership-ev-charging-1200.webp` at the same revision. Its provenance is recorded in the upstream [image sources](https://github.com/ezhostforall/GreenDawn/blob/891ed840c7369cb50df3e33232e81de49e2699d6/docs/image-sources.md). It is not a runtime theme asset.

The five `commercial-ev-*` photographs under `media/` are the service previews referenced by the pinned `src/content/home.ts` and `src/content/media.ts`. They were copied byte-for-byte from the matching files under upstream `public/images/optimized/` and are provided for Media Library upload, rather than runtime theme fallbacks.

The `salvation-army-ev-charging-666.webp` photograph is the Supporting Proof project image from the same revision. It is provided for Media Library upload with the same upstream provenance records.

The Insights cards also use `commercial-ev-electrical-connection-1200.webp` and `commercial-ev-site-survey-1024.webp`, copied byte-for-byte from the same pinned upstream revision. Their fleet photograph is shared with the Services preview. All three are Media Library content, with provenance maintained in the same upstream image sources.
