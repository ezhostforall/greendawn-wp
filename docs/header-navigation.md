# Header and navigation foundation

Assign a core WordPress menu to **Primary menu** under Appearance → Menus. Give a parent a custom URL of `#` to make its label and chevron one submenu button. Blank parent URLs work the same way. A parent with a destination, including a section link such as `#services`, keeps its link and gains a separate chevron button. Both buttons expose `aria-expanded` and `aria-controls`. Nested items follow the same rules, and links to the current page retain WordPress's `aria-current` marker.

At widths up to 75rem, the enhanced mobile menu fills the viewport and locks background scrolling. Tab and Shift+Tab follow the ordinary link and button order. Enter or Space activates disclosure buttons. Escape closes the innermost open submenu and returns focus to its button. A further Escape closes the mobile menu and returns focus to Menu. Outside clicks, leaving the navigation and following a mobile link close disclosures. Crossing the breakpoint clears open state and moves focus away from content that becomes hidden.

Without JavaScript, the native mobile Menu disclosure shows all nested links in an inline panel. On desktop the links are visible in indented lists. The Primary menu is shown only when one is assigned.

## Optional settings

- Assign **Utility menu** to show a wrapping utility bar above the primary row. Only its first level is rendered.
- Appearance → Customise → Header offers **Keep the header visible while scrolling**, **Call-to-action text** and **Call-to-action destination**. These default to off/empty. A CTA appears only when both its text and a safe destination are present. Replace a placeholder # destination with the Contact page URL or a tel: link.
- Sticky positioning accounts for the WordPress admin bar. The open mobile menu keeps the regular or compact header row height while the links scroll below it.
- A custom logo continues to use core Site Identity; otherwise the linked site name is shown. Colours, spacing, borders and focus presentation use the shared theme.json aliases.

Customizer settings are theme presentation options, not client business logic. Initialisation rewrites their starter prefixes. No package versions, updater protocol or source configuration change in this feature PR; a future release must use a new candidate tag after review.

## Verification

The disposable WordPress seed creates a three-level primary menu, a second parent with a child, an optional utility/CTA/sticky scenario and a no-menu scenario. Scenario configuration is a test-only plugin copied into that disposable installation, guarded to the local fixture origin. It is not included in release ZIPs or installed on hosted staging.

Browser coverage checks desktop button visibility even after a later generic `.button` display rule, keyboard and touch controls, Escape/focus return, outside clicks, tabbing out, link destinations, current-page markers, 320px/tablet/breakpoint layouts, resize cleanup, short RTL viewports, optional defaults and no-JavaScript access. Screenshots are retained with CI evidence; axe covers the expanded header as well as the existing whole-page checks. Hosted menu configuration and visual acceptance remain separate release checks.
