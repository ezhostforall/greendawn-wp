# Design-token contract

`theme/theme.json` is authoritative. WordPress emits its variables for the frontend and block editor; `theme/src/styles/tokens.css` maps stable semantic aliases to them. Do not copy palette, font, spacing or width literals into that CSS file. Existing aliases remain compatible.

| Role | Edit in theme.json | CSS consumption |
|---|---|---|
| Colours, including interactive/on-accent | `settings.color.palette` | `--color-*` → `--wp--preset--color--*` |
| Body and heading fonts | `settings.typography.fontFamilies` and `settings.custom.brand` | Body alias and WordPress heading styles use separate font roles |
| Small/body/lead/heading/display | `settings.typography.fontSizes` | `--type-*`; lead/heading/display are fluid |
| Spacing | `settings.spacing.spacingSizes` | `--space-xs/s/m/l/xl` |
| Narrow and wide content | `settings.layout` | `--content-width`, `--wide-width` → WordPress global sizes |
| Radius, shadow, focus, line height | `settings.custom` | `--radius`, `--shadow-card`, `--focus-*`, `--line-height-body` |

The aliases are declared on `body`, allowing WordPress's editor-style scoping to map them to its canvas. Preset/custom variables must come from WordPress, including saved user settings; compiled CSS deliberately supplies no duplicate fallback literals. A raw HTML preview must supply the WordPress variables to reproduce the design.

The default palette is monochrome, with an off-white page background, white surfaces and charcoal actions. The additional type/state/shadow roles are available to future components; this change does not implement the M2 typography or interaction system. Defining an accent hover role does not add hover behaviour to every button automatically.

Breakpoints stay in component CSS. Custom properties do not work as media-query conditions; document breakpoint choices beside the component and use the same query in any JavaScript controlling it. Do not introduce a second JSON breakpoint catalogue or token compiler.

## Brand and editorial changes

Administrators can open **Appearance → Customise → Brand** to preview changes to the shared colour roles and choose separate body and heading font presets. Use **Heading text** for H1–H6, including linked titles. **Link text** controls ordinary links, and **Keyboard focus** controls the theme’s focus outline. **Accent** controls accent-based components. These roles can be adjusted independently. The starter uses Greendawn’s three typefaces with a monochrome palette. Archivo is the body default, Bricolage Grotesque the heading default and JetBrains Mono the code and action font. The theme packages Latin variable WOFF2 files from Fontsource 5.2.8 with their SIL Open Font Licences. Saved Customiser choices for retired presets fall back to the new defaults. Saved System and Humanist block styles use the current body font. Saved Classic serif block styles use the current heading font. This applies to both saved classes and inline preset references. Client themes can supply their own defaults and font presets in `theme.json`.

Clear a colour or select **Theme default** for a font to reset that setting. **Reset brand styles** clears all Brand overrides in the preview. Publish to apply the changes. The reset is scoped to the Brand controls. WordPress global styles and explicit block styling have their own precedence.

On hosted staging, preview a contrasting colour pair and distinct body and heading fonts. Check both the front end and post editor after publishing, then verify individual and full reset. Use an Editor account to confirm the Brand controls are unavailable. Verify link, button and focus contrast with the chosen palette.

Change existing palette/font/spacing values without renaming their slugs unless every consumer and saved block reference is migrated. Additional brand roles can be added in a client theme. Client-specific names, colours and assets stay outside the starter. Test on-accent contrast independently; a palette change can invalidate previously accessible combinations.

Editor controls expose presets; custom settings provide CSS variables rather than automatic editor controls. Saved WordPress global styles can override theme defaults. Inspect those settings before assuming a CSS defect or resetting user choices.

## Verification and acceptance

Node checks reject literal aliases or unresolved variable targets. Disposable WordPress browser tests check the frontend and authenticated editor canvas, plus two runtime palette/font/spacing overrides. These are controlled regression checks, not complete client-brand acceptance.

Before a release, install the candidate on hosted staging and compare the frontend with real saved blocks in the editor: palette, font family, fluid sizes at 320px/desktop, spacing presets, narrow/wide layouts, card/button styles, focus and contrast. Check classic and iframe editor configurations where used. Capture reviewed screenshots and check for saved user overrides. Do not infer editor parity or WCAG compliance from a Vite build alone.

Official reference: [custom settings](https://developer.wordpress.org/themes/global-settings-and-styles/settings/custom/), [spacing presets](https://developer.wordpress.org/themes/global-settings-and-styles/settings/spacing/), [editor styles](https://developer.wordpress.org/themes/functionality/adding-custom-stylesheets/).
