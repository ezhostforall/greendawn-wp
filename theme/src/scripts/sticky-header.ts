/** Match the Astro compact header; the utility row remains in document flow. */
export function initStickyHeader(): void {
	const header = document.querySelector<HTMLElement>('.site-header--sticky');
	const slot = header?.closest<HTMLElement>('.gd-header-slot');
	if (!header || !slot || header.dataset.stickyReady) return;
	header.dataset.stickyReady = 'true';
	const controller = new AbortController();
	const { signal } = controller;

	const update = (): void => {
		const scrolled = window.scrollY > 72;
		if (scrolled === header.classList.contains('is-scrolled')) return;
		if (scrolled) {
			// Freeze its occupied space before taking the main row out of flow.
			slot.style.height = `${slot.getBoundingClientRect().height}px`;
			header.classList.add('is-scrolled');
		} else {
			header.classList.remove('is-scrolled');
			slot.style.removeProperty('height');
		}
	};

	update();
	window.addEventListener('scroll', update, { passive: true, signal });
	window.addEventListener('pageshow', update, { signal });
	window.addEventListener('pagehide', (event) => {
		if (!event.persisted) controller.abort();
	}, { signal });
}
