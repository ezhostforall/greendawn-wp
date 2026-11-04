# Header and navigation foundation

Assign a core WordPress menu to **Primary menu** under Appearance → Menus. Parent items keep their normal links. JavaScript adds a separate, translated submenu disclosure button with `aria-expanded` and `aria-controls`; nested levels use the same controls. The current page retains WordPress's `aria-current` marker.

The inline mobile menu is used at widths up to 60rem. It opens in the document flow rather than a modal overlay: no focus trap or body scroll lock is applied. Tab/Shift+Tab follow the ordinary link/button order. Enter or Space activates disclosure buttons. Escape closes the innermost open submenu and returns focus to its button; a further Escape closes the mobile menu and returns focus to Menu. Outside clicks, leaving the navigation and following a mobile link close disclosures. Crossing the breakpoint clears open state and moves focus away from content that becomes hidden. Dropdowns also work with touch; there is no hover-only path or mandatory arrow-key menu model.

Without JavaScript, the Menu button stays hidden and every nested link remains visible in indented lists. Without an assigned primary menu, no primary navigation or Menu button is rendered. There is no automatic page-list fallback.

## Optional settings

- Assign **Utility menu** to show a wrapping utility bar above the primary row. Only its first level is rendered.
- Appearance → Customise → Header offers **Keep the header visible while scrolling**, **Call-to-action text** and **Call-to-action destination**. These default to off/empty. A CTA appears only when both its text and a safe destination are present. Replace a placeholder # destination with the Contact page URL or a tel: link.
- Sticky positioning accounts for the WordPress admin bar. On viewports no taller than 30rem the header scrolls normally; taller mobile viewports let an open sticky header scroll within the available height. It never locks the page.
- A custom logo continues to use core Site Identity; otherwise the linked site name is shown. Colours, spacing, borders and focus presentation use the shared theme.json aliases.

Customizer settings are theme presentation options, not client business logic. Initialisation rewrites their starter prefixes. No package versions, updater protocol or source configuration change in this feature PR; a future release must use a new candidate tag after review.

## Verification

The disposable WordPress seed creates a three-level primary menu, a second parent with a child, an optional utility/CTA/sticky scenario and a no-menu scenario. Scenario configuration is a test-only plugin copied into that disposable installation, guarded to the local fixture origin. It is not included in release ZIPs or installed on hosted staging.

Browser coverage checks desktop button visibility even after a later generic `.button` display rule, keyboard and touch controls, Escape/focus return, outside clicks, tabbing out, link destinations, current-page markers, 320px/tablet/breakpoint layouts, resize cleanup, short RTL viewports, optional defaults and no-JavaScript access. Screenshots are retained with CI evidence; axe covers the expanded header as well as the existing whole-page checks. Hosted menu configuration and visual acceptance remain separate release checks.
