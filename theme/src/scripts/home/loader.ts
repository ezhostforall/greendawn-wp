/** Homepage-only enhancements; neither content nor native links depend on them. */
const home = document.querySelector<HTMLElement>('[data-gd-home-motion]');

if (home) {
	const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	const lifecycle = new AbortController();
	const seen = new WeakSet<Element>();
	let observer: IntersectionObserver | undefined;
	let motionCleanup: (() => void) | undefined;
	let loading: Promise<void> | undefined;
	let resizeFrame = 0;
	const reveals = [...home.querySelectorAll<HTMLElement>([
		'.gd-problem__intro', '.gd-system__header', '.gd-system__decisions > article',
		'.gd-process__intro', '.gd-surveys__intro', '.gd-surveys__card', '.gd-surveys__outcomes',
		'.gd-surveys__footer', '.gd-solutions__intro', '.gd-aftercare__copy', '.gd-aftercare__quote',
		'.gd-proof-grid__header', '.gd-insights__header', '.gd-final-cta__inner',
	].join(','))];

	function observeReveals(): void {
		observer?.disconnect();
		if (reduced.matches || !('IntersectionObserver' in window)) return;
		observer = new IntersectionObserver((entries) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) continue;
				seen.add(entry.target);
				if (!entry.target.contains(document.activeElement)) entry.target.classList.add('gd-reveal-enter');
				observer?.unobserve(entry.target);
			}
		}, { rootMargin: `0px 0px -${Math.round(window.innerHeight * 0.13)}px 0px` });
		reveals.forEach((element) => { if (!seen.has(element)) observer?.observe(element); });
	}

	function enable(): void {
		if (lifecycle.signal.aborted || reduced.matches) return;
		observeReveals();
		if (motionCleanup || loading) return;
		loading = import('./scroll-motion').then(({ initHomeMotion }) => {
			if (!lifecycle.signal.aborted && !reduced.matches) motionCleanup = initHomeMotion(home!);
		}).catch(() => {
			// Visible HTML and CSS previews remain usable when the enhancement fails.
		}).finally(() => { loading = undefined; });
	}

	function disable(): void {
		observer?.disconnect();
		motionCleanup?.();
		motionCleanup = undefined;
		reveals.forEach((element) => element.classList.remove('gd-reveal-enter'));
	}

	reduced.addEventListener('change', () => reduced.matches ? disable() : enable(), { signal: lifecycle.signal });
	home.addEventListener('animationend', (event) => {
		if (event.animationName === 'gd-section-in' && event.target instanceof HTMLElement) event.target.classList.remove('gd-reveal-enter');
	}, { signal: lifecycle.signal });
	window.addEventListener('resize', () => {
		window.cancelAnimationFrame(resizeFrame);
		resizeFrame = window.requestAnimationFrame(observeReveals);
	}, { signal: lifecycle.signal, passive: true });
	window.addEventListener('pagehide', (event) => {
		if (event.persisted) return;
		lifecycle.abort();
		window.cancelAnimationFrame(resizeFrame);
		disable();
	}, { signal: lifecycle.signal });
	enable();
}
