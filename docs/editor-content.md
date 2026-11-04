# Saved block presentation

The v0.4.0 hosted editor test exposed a gap beyond design-token equality. Saved headings/lists/content appeared, but quote and code borders and pullquote borders/citation styling differed from the block editor. The public page contained WordPress's structural block CSS, so this was not evidence that all Gutenberg assets were absent.

`theme/src/styles/content.css` provides small, shared defaults for these existing core blocks and is imported by both frontend and editor entries. Palette, spacing, radius and type values continue to use the theme.json aliases. Core block loading is unchanged; the fix neither copies WordPress CSS files nor disables on-demand asset loading.

- Standard quotes have a logical leading border; the plain variation remains borderless.
- Code retains literal escaped text and gets the same border, padding and monospace defaults in both contexts.
- Pullquotes share their top/bottom rules and citation treatment; legacy solid-colour variations keep their own border policy.
- Author-supplied inline block settings retain normal CSS precedence; no important overrides are added.

The disposable CI fixture now stores a representative page containing headings, lists, quote/plain quote, code, details and pullquote blocks. Browser tests open that same saved content on the frontend and in an authenticated editor, compare actual computed presentation, reject invalid blocks and retain screenshots. They also exercise 320px reflow and a logical border in RTL. Authenticated tests are restricted to the disposable localhost fixture.

This closes the specific presentation gap. It does not establish parity for every core/plugin block or complete the M2 typography/component system. Hosted save/revert, responsive/editor review and rollback remain separate acceptance steps. The published v0.4.0 prerelease is not overwritten; corrections use a new v0.4.1 candidate after review.
