/** Enhance the native mobile disclosure and add nested submenu controls. */
export function initNavigation(): void {
	const toggle = document.querySelector<HTMLElement>('[data-nav-toggle]');
	const disclosure = toggle?.closest('details');
	const menu = document.getElementById(toggle?.getAttribute('aria-controls') ?? '');
	if (!toggle || !disclosure || !menu || menu.classList.contains('is-enhanced')) return;

	const smallScreen = window.matchMedia('(max-width: 60rem)');
	const controller = new AbortController();
	const { signal } = controller;
	const submenus = new Map<HTMLButtonElement, HTMLUListElement>();

	const closeSubmenu = (button: HTMLButtonElement): void => {
		const submenu = submenus.get(button);
		if (!submenu) return;
		button.setAttribute('aria-expanded', 'false');
		submenu.hidden = true;
		for (const child of submenu.querySelectorAll<HTMLButtonElement>('.submenu-toggle')) {
			child.setAttribute('aria-expanded', 'false');
			const descendant = submenus.get(child);
			if (descendant) descendant.hidden = true;
		}
	};
	const closeAll = (): void => submenus.forEach((_, button) => closeSubmenu(button));
	const closeMenu = (): void => {
		disclosure.open = false;
		closeAll();
	};

	menu.querySelectorAll<HTMLUListElement>('.sub-menu').forEach((submenu, index) => {
		const link = submenu.previousElementSibling;
		if (!(link instanceof HTMLAnchorElement)) return;
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'submenu-toggle';
		button.setAttribute('aria-label', (toggle.dataset.submenuLabel ?? '%s').replace('%s', link.textContent?.trim() ?? ''));
		submenu.id = `primary-submenu-${index + 1}`;
		button.setAttribute('aria-controls', submenu.id);
		link.after(button);
		submenus.set(button, submenu);
		closeSubmenu(button);
		button.addEventListener('click', () => {
			const open = button.getAttribute('aria-expanded') !== 'true';
			if (open) {
				// Close siblings, retaining ancestor disclosures for deeper levels.
				for (const [other] of submenus) {
					if (other !== button && other.parentElement?.parentElement === button.parentElement?.parentElement) closeSubmenu(other);
				}
			}
			button.setAttribute('aria-expanded', String(open));
			if (open) submenu.hidden = false;
			else closeSubmenu(button);
		}, { signal });
	});

	const reset = (): void => {
		const active = document.activeElement;
		const focusWillHide = active instanceof HTMLElement && (
			(smallScreen.matches && menu.contains(active)) ||
			(!smallScreen.matches && active === toggle) ||
			(menu.contains(active) && active.closest('.sub-menu') !== null)
		);
		closeMenu();
		if (focusWillHide) {
			if (smallScreen.matches) toggle.focus();
			else menu.querySelector<HTMLAnchorElement>('a')?.focus();
		}
	};
	menu.classList.add('is-enhanced');
	disclosure.addEventListener('toggle', () => {
		if (disclosure.open) return;
		if (smallScreen.matches && menu.contains(document.activeElement)) toggle.focus();
		closeAll();
	}, { signal });
	smallScreen.addEventListener('change', reset, { signal });

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape' || !(event.target instanceof HTMLElement)) return;
		const expanded = event.target.closest('li')?.querySelector<HTMLButtonElement>(':scope > .submenu-toggle[aria-expanded="true"]');
		const submenu = event.target.closest<HTMLUListElement>('.sub-menu');
		const button = expanded ?? (submenu ? [...submenus].find(([, list]) => list === submenu)?.[0] : null);
		if (button) {
			closeSubmenu(button);
			button.focus();
			event.preventDefault();
		} else if (menu.contains(event.target) || event.target === toggle) {
			if (smallScreen.matches && disclosure.open) {
				closeMenu();
				toggle.focus();
			}
		}
	}, { signal });
	document.addEventListener('click', (event) => {
		if (!(event.target instanceof Node) || menu.contains(event.target) || toggle.contains(event.target)) return;
		const active = document.activeElement;
		if (smallScreen.matches && menu.contains(active)) toggle.focus();
		else if (active instanceof HTMLElement && menu.contains(active) && active.closest('.sub-menu')) {
			const ancestor = active.closest('.primary-nav > ul > li');
			ancestor?.querySelector<HTMLButtonElement>(':scope > .submenu-toggle')?.focus();
		}
		closeMenu();
	}, { signal });
	document.addEventListener('focusin', (event) => {
		if (event.target instanceof Node && !menu.contains(event.target) && event.target !== toggle) closeMenu();
	}, { signal });
	menu.addEventListener('click', (event) => {
		if (event.target instanceof Element && event.target.closest('a') && smallScreen.matches) {
			closeMenu();
			toggle.focus();
		}
	}, { signal });
	window.addEventListener('pagehide', (event) => {
		// Keep handlers on a bfcache entry; browsers resume that same document.
		if (!event.persisted) controller.abort();
	}, { signal });
	window.addEventListener('pageshow', (event) => {
		if (event.persisted) reset();
	}, { signal });
}
