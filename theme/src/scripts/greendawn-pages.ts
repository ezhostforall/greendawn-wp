import '../styles/greendawn-pages.css';

// Static article links remain accessible through native scrolling without JavaScript.
for (const root of document.querySelectorAll<HTMLElement>('[data-gd-articles]')) {
	const track = root.querySelector<HTMLElement>('.gd-article-track');
	if (!track) continue;
	const buttons = [...root.querySelectorAll<HTMLButtonElement>('[data-gd-track-direction]')];
	const controller = new AbortController();
	const update = (): void => {
		for (const button of buttons) {
			button.disabled = button.dataset.gdTrackDirection === '-1' ? track.scrollLeft <= 1 : track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
		}
	};
	for (const button of buttons) {
		button.hidden = false;
		button.addEventListener('click', () => track.scrollBy({ left: Number(button.dataset.gdTrackDirection) * track.clientWidth * 0.9, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' }), { signal: controller.signal });
	}
	track.addEventListener('scroll', update, { passive: true, signal: controller.signal });
	const observer = new ResizeObserver(update);
	observer.observe(track);
	update();
	// Retain controls when the browser stores this page in its back/forward cache.
	window.addEventListener('pagehide', event => {
		if (!event.persisted) { controller.abort(); observer.disconnect(); }
	});
}

// Local poster/fallback links work without JavaScript; third-party video loads only on activation.
for (const link of document.querySelectorAll<HTMLAnchorElement>('a[data-gd-video]')) {
	link.addEventListener('click', event => {
		if (event.button !== 0 || event.metaKey || event.ctrlKey || event.altKey || event.shiftKey) return;
		const media = link.closest<HTMLElement>('[data-gd-video-media]');
		if (!media) return;
		let source: URL;
		try { source = new URL(link.dataset.gdVideo ?? ''); } catch { return; }
		if (source.origin !== 'https://www.youtube-nocookie.com' || source.username || source.password || !/^\/embed\/[A-Za-z0-9_-]{11}$/.test(source.pathname)) return;
		event.preventDefault();
		source.search = '?rel=0&autoplay=1';
		source.hash = '';
		const frame = document.createElement('iframe');
		frame.src = source.href;
		frame.title = link.textContent?.trim() || 'Video testimonial';
		frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
		frame.allowFullscreen = true;
		frame.referrerPolicy = 'strict-origin-when-cross-origin';
		media.replaceChildren(frame);
		frame.focus();
	});
}
