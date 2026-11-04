# Layout and typography primitives

`theme.json` owns colours, widths, spacing, type sizes, heading line height and composition minimums. `tokens.css` aliases those values; `primitives.css` is shared by the frontend and editor. No layout JavaScript or new dependency is required.

## Widths and compositions

| Class or alignment | Behaviour |
|---|---|
| `.container` / `.container--wide` | Centred, wide container with a medium spacing gutter |
| `.container--narrow` | Container bounded by the reading width |
| `.container--full` | Container bounded only by its parent, retaining its gutter |
| Core `alignwide` | Direct article section bounded by wide width and page gutters |
| Core `alignfull` | Direct article section spans the main viewport width |
| `.section` | Large spacing above and below a section |
| `.layout-grid` | Equal tracks, automatically fitting the grid minimum or available width |
| `.layout-row` | A wrapping horizontal row with medium gaps |
| `.layout-stack` | A vertical stack with medium gaps and child margins reset |
| `.layout-media` | Two flexible media/text columns that wrap when both cannot fit |
| `.prose` | Reading rhythm and safe wrapping for long text |

In PHP, compose these classes on ordinary semantic elements. In a saved core Group block, choose its default/flow layout and add the composition classes under Advanced → Additional CSS class(es). Core Row/Grid controls remain available as their own native layouts; avoid asking a custom class and an incompatible core layout to control the same element. Inside article content, a direct `.container--wide` or `.container--full` also selects that section width. A full section can contain a wide or narrow container to keep its text readable.

Full width is relative to the parent: a nested full container does not escape a narrow parent. The main article can span the viewport, while headings, breadcrumbs, archive/search lists, pagination and comments retain constrained widths. There are no negative-margin or `100vw` breakouts, and page overflow is not hidden to conceal geometry problems. Grid minimums shrink to the available width; children can shrink, long addresses/headings wrap, and preformatted content/wide tables can scroll locally.

## Type roles

| Role | Preset | Default bounds | Default semantic element |
|---|---|---|---|
| Display | `display` | 2–4rem, fluid | h1 |
| Heading | `heading` | 1.5–2.5rem, fluid | h2 |
| Subheading | `subheading` | 1.25–1.75rem, fluid | h3 |
| Minor heading | `minor-heading` | 1.125–1.25rem, fluid | h4 |
| Lead | `lead` | 1.125–1.5rem, fluid | Supporting introduction |
| Body | `body` | 1rem | Body text and h5 |
| Small | `small` | 0.875rem | Supporting text and h6 |

Heading defaults live in `theme.json`'s element styles and use the shared heading line height. WordPress emits the fluid values for both frontend and editor. `.type-display`, `.type-heading`, `.type-subheading`, `.type-minor-heading`, `.type-lead`, `.type-body` and `.type-small` let a presentation role differ from the element's semantic level. Preserve the appropriate heading hierarchy; a visual role does not change the document outline. Explicit block font-size settings retain normal precedence.

## Brand demonstrations and verification

Disposable CI pages `/layout-brand-a/` and `/layout-brand-b/` render exactly the same saved blocks using different server-side theme.json token values:

| Example | Character | Reading/wide widths | Medium/large spacing |
|---|---|---|---|
| A | Green, warm background, Georgia | 38/68rem | 1.25/2.5rem |
| B | Violet, pale background, Arial | 46/76rem | 1.5/3rem |

Both use the same compiled CSS and markup, with no brand selectors in production CSS. The brand switch plugin and illustration exist only in the disposable fixture; they are not release assets, a production brand switcher, or client-specific starter defaults. Real client projects change their own theme.json values after initialisation.

Browser coverage verifies actual section widths, full-width edges, grid/media arrangements, 320px and 640px reflow, RTL, long text and preformatted wrapping/local scrolling, fluid type bounds, two token sets, axe checks, valid saved blocks and editor typography/composition parity. Text enlargement uses a 200% root font size at a 640px CSS viewport, alongside the 320px reflow check. This is an automated reflow/text-scaling stress test; actual browser zoom remains a hosted acceptance check. CI retains screenshots of both brands, compositions and editor content.

No installed release is changed by this feature PR. A new versioned candidate and hosted checks are required before deployment; stable v0.4.1 assets remain unchanged.
