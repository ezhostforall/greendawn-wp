import '../../styles/home-mobile.css';

const home = document.querySelector<HTMLElement>('[data-gd-home-refresh]');
const phone = matchMedia('(max-width: 42rem)');

if (home) {
	const lifecycle = new AbortController();
	const options = { signal: lifecycle.signal };
	const originalSections = Array.from(home.children);
	const hero = home.querySelector<HTMLElement>('#top');
	const project = home.querySelector<HTMLElement>('#projects');
	const solutions = home.querySelector<HTMLElement>('#who-we-help');
	const launcher = document.querySelector<HTMLButtonElement>('.lead-capture__launcher');
	const tablist = home.querySelector<HTMLElement>('.gd-refresh-process-tabs');
	const list = home.querySelector<HTMLOListElement>('.gd-process__steps');
	const tabs = Array.from(tablist?.querySelectorAll<HTMLButtonElement>('[role="tab"]') ?? []);
	const panels = Array.from(list?.querySelectorAll<HTMLElement>(':scope > li') ?? []);
	const controls = home.querySelector<HTMLElement>('.gd-refresh-process-controls');
	const count = controls?.querySelector<HTMLElement>('[data-gd-stage-count]');
	const buttons = Array.from(controls?.querySelectorAll<HTMLButtonElement>('[data-gd-step]') ?? []);
	let active = 0;
	let gesture: { x: number; y: number } | null = null;
	let frame = 0;

	function updateLauncher(): void {
		if (!launcher) return;
		const viewport = window.visualViewport;
		const keyboard = viewport && viewport.scale === 1 && viewport.height + 150 < innerHeight
			&& document.activeElement?.matches('input, textarea, [contenteditable="true"]');
		launcher.toggleAttribute('data-gd-refresh-keyboard', Boolean(keyboard));
	}

	function queueLauncher(): void {
		cancelAnimationFrame(frame);
		frame = requestAnimationFrame(updateLauncher);
	}

	function selectStage(index: number, focus = false): void {
		active = index;
		tabs.forEach((tab, i) => {
			tab.setAttribute('aria-selected', String(i === active));
			tab.tabIndex = i === active ? 0 : -1;
			panels[i]!.hidden = i !== active;
		});
		if (count) count.textContent = (tabs[active]?.textContent?.trim() ?? '') + ' · ' + (active + 1) + '/' + tabs.length;
		buttons.forEach((button) => {
			button.setAttribute('aria-disabled', String(button.dataset.gdStep === '-1' ? active === 0 : active === tabs.length - 1));
		});
		if (focus) tabs[active]?.focus();
	}

	function step(delta: number): void {
		const next = active + delta;
		if (next >= 0 && next < tabs.length) selectStage(next);
	}

	function syncProcess(): void {
		if (!tablist || !list || !controls || tabs.length < 2 || panels.length !== tabs.length) return;
		const compact = phone.matches;
		const focused = document.activeElement;
		const focusInControls = tablist.contains(focused) || controls.contains(focused);
		tablist.hidden = !compact;
		controls.hidden = !compact;
		list.classList.toggle('gd-refresh-process-panels', compact);
		list.setAttribute('role', compact ? 'presentation' : 'list');
		panels.forEach((panel, i) => {
			panel.hidden = false;
			panel.removeAttribute('aria-labelledby');
			panel.removeAttribute('role');
			panel.removeAttribute('tabindex');
			if (compact) {
				panel.setAttribute('role', 'tabpanel');
				panel.setAttribute('aria-labelledby', tabs[i]!.id);
				panel.tabIndex = 0;
			}
		});
		if (compact) selectStage(active);
		else if (focusInControls) {
			const heading = home?.querySelector<HTMLElement>('#gd-process-title');
			heading?.setAttribute('tabindex', '-1');
			heading?.focus({ preventScroll: true });
		}
	}

	function syncLayout(): void {
		if (!home) return;
		const focused = document.activeElement;
		if (phone.matches && hero && solutions && project) {
			hero.after(solutions, project);
			const workplace = solutions.querySelector('.gd-solution-row--workplace')?.parentElement;
			const fleet = solutions.querySelector('.gd-solution-row--fleet')?.parentElement;
			if (workplace && fleet) fleet.before(workplace);
		} else {
			let next: Element | null = home.firstElementChild;
			for (const section of originalSections) {
				if (section !== next) home.insertBefore(section, next);
				next = section.nextElementSibling;
			}
			const workplace = solutions?.querySelector('.gd-solution-row--workplace')?.parentElement;
			const fleet = solutions?.querySelector('.gd-solution-row--fleet')?.parentElement;
			if (workplace && fleet && workplace.nextElementSibling === fleet) workplace.before(fleet);
		}
		if (focused instanceof HTMLElement && focused.isConnected && document.activeElement !== focused) focused.focus({ preventScroll: true });
		syncProcess();
		updateLauncher();
	}

	tabs.forEach((tab, index) => tab.addEventListener('click', () => selectStage(index), options));
	buttons.forEach((button) => button.addEventListener('click', () => step(Number(button.dataset.gdStep)), options));
	tablist?.addEventListener('keydown', (event) => {
		const keys = {
			ArrowLeft: (active + tabs.length - 1) % tabs.length,
			ArrowRight: (active + 1) % tabs.length,
			Home: 0,
			End: tabs.length - 1,
		};
		if (!Object.hasOwn(keys, event.key)) return;
		event.preventDefault();
		selectStage(keys[event.key as keyof typeof keys], true);
	}, options);
	list?.addEventListener('touchstart', (event) => {
		const touch = event.touches.length === 1 ? event.touches[0] : undefined;
		gesture = touch ? { x: touch.clientX, y: touch.clientY } : null;
	}, { ...options, passive: true });
	list?.addEventListener('touchend', (event) => {
		const start = gesture;
		gesture = null;
		const touch = event.changedTouches[0];
		if (!phone.matches || !start || !touch) return;
		const x = touch.clientX - start.x;
		const y = touch.clientY - start.y;
		if (Math.abs(x) > 45 && Math.abs(x) > Math.abs(y) * 1.35) step(x < 0 ? 1 : -1);
	}, { ...options, passive: true });
	list?.addEventListener('touchcancel', () => { gesture = null; }, options);

	document.querySelector<HTMLAnchorElement>('.gd-refresh-cookie-settings')?.addEventListener('click', (event) => {
		if (!phone.matches || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		const manage = document.querySelector<HTMLElement>('.cmplz-manage-consent');
		if (!manage || !document.querySelector('.cmplz-cookiebanner')) return;
		manage.click();
		// Keep the policy link usable if Complianz did not open a banner.
		const banner = document.querySelector<HTMLElement>('.cmplz-cookiebanner:not(.cmplz-dismissed)');
		if (banner?.getClientRects().length) event.preventDefault();
	}, options);

	phone.addEventListener('change', syncLayout, options);
	window.addEventListener('resize', queueLauncher, { ...options, passive: true });
	document.addEventListener('focusin', queueLauncher, options);
	document.addEventListener('focusout', queueLauncher, options);
	window.visualViewport?.addEventListener('resize', queueLauncher, options);
	window.addEventListener('pageshow', queueLauncher, options);
	window.addEventListener('pagehide', (event) => {
		if (!event.persisted) {
			cancelAnimationFrame(frame);
			lifecycle.abort();
		}
	}, options);
	syncLayout();
}
