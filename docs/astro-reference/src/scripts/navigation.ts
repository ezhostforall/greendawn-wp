const MOBILE_BREAKPOINT = "(max-width: 75rem)";

export function initialiseNavigation(): () => void {
  const header = document.querySelector<HTMLElement>("[data-header]");
  const menuButton = document.querySelector<HTMLButtonElement>(".menu-toggle");
  const mobileNav = document.querySelector<HTMLElement>(".mobile-nav");
  if (!menuButton || !mobileNav) return () => undefined;

  const mediaQuery = window.matchMedia(MOBILE_BREAKPOINT);
  const menuLinks = Array.from(mobileNav.querySelectorAll<HTMLAnchorElement>("a"));
  const disclosures = Array.from(document.querySelectorAll<HTMLDetailsElement>("[data-nav-disclosure]"));
  const menuLabel = menuButton.querySelector<HTMLElement>(".sr-only");
  let menuOpen = false;

  const closeDisclosures = (except?: HTMLDetailsElement): void => {
    disclosures.forEach((disclosure) => {
      if (disclosure !== except) disclosure.open = false;
    });
  };

  const focusableElements = (): HTMLElement[] => [
    menuButton,
    ...Array.from(mobileNav.querySelectorAll<HTMLElement>(
      'a[href], button:not([disabled]), summary, input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
    )),
  ].filter((element) => !element.closest("[inert]") && element.getClientRects().length > 0);

  const setMenu = (open: boolean, restoreFocus = false): void => {
    menuOpen = open;
    menuButton.setAttribute("aria-expanded", String(open));
    mobileNav.setAttribute("aria-hidden", String(!open));
    mobileNav.toggleAttribute("inert", !open);
    mobileNav.classList.toggle("is-open", open);
    header?.classList.toggle("menu-active", open);
    document.body.classList.toggle("menu-open", open);
    if (!open) closeDisclosures();
    if (menuLabel) menuLabel.textContent = open ? "Close navigation" : "Open navigation";
    if (open) window.requestAnimationFrame(() => menuLinks[0]?.focus());
    else if (restoreFocus) menuButton.focus();
  };

  const onMenuButtonClick = (): void => setMenu(!menuOpen);
  const onMenuLinkClick = (): void => setMenu(false);
  const onDisclosureToggle = (event: Event): void => {
    const disclosure = event.currentTarget as HTMLDetailsElement;
    if (disclosure.open) closeDisclosures(disclosure);
  };
  const onDocumentPointerDown = (event: PointerEvent): void => {
    if (!(event.target instanceof Node)) return;
    const containingDisclosure = disclosures.find((disclosure) => disclosure.contains(event.target as Node));
    if (!containingDisclosure) closeDisclosures();
  };
  const onKeyDown = (event: KeyboardEvent): void => {
    if (event.key === "Escape") {
      const openDisclosure = disclosures.find((disclosure) => disclosure.open && disclosure.contains(document.activeElement));
      if (openDisclosure) {
        event.preventDefault();
        openDisclosure.open = false;
        openDisclosure.querySelector<HTMLElement>("summary")?.focus();
        return;
      }
      if (menuOpen) {
        event.preventDefault();
        setMenu(false, true);
      }
      return;
    }
    if (!menuOpen) return;
    if (event.key !== "Tab") return;
    const focusable = focusableElements();
    const first = focusable[0];
    const last = focusable.at(-1);
    if (!first || !last) return;
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  };
  const onBreakpointChange = ({ matches }: MediaQueryListEvent): void => {
    if (!matches && menuOpen) setMenu(false);
    closeDisclosures();
  };
  const onPageShow = (): void => {
    if (menuOpen) setMenu(false);
    closeDisclosures();
  };
  const updateHeader = (): void => {
    header?.classList.toggle("is-scrolled", window.scrollY > 72);
  };

  menuButton.addEventListener("click", onMenuButtonClick);
  menuLinks.forEach((link) => link.addEventListener("click", onMenuLinkClick));
  disclosures.forEach((disclosure) => disclosure.addEventListener("toggle", onDisclosureToggle));
  document.addEventListener("pointerdown", onDocumentPointerDown);
  window.addEventListener("keydown", onKeyDown);
  mediaQuery.addEventListener("change", onBreakpointChange);
  window.addEventListener("pageshow", onPageShow);
  window.addEventListener("scroll", updateHeader, { passive: true });
  updateHeader();

  return () => {
    setMenu(false);
    menuButton.removeEventListener("click", onMenuButtonClick);
    menuLinks.forEach((link) => link.removeEventListener("click", onMenuLinkClick));
    disclosures.forEach((disclosure) => disclosure.removeEventListener("toggle", onDisclosureToggle));
    document.removeEventListener("pointerdown", onDocumentPointerDown);
    window.removeEventListener("keydown", onKeyDown);
    mediaQuery.removeEventListener("change", onBreakpointChange);
    window.removeEventListener("pageshow", onPageShow);
    window.removeEventListener("scroll", updateHeader);
  };
}
