import { gsap, ScrollTrigger } from './runtime';

/** Scroll selection yields to keyboard focus, then pointer hover, without blank image swaps. */
export function setupSolutions(root: HTMLElement, media: gsap.MatchMedia): void {
	const layout = root.querySelector<HTMLElement>('.gd-solutions__layout');
	if (!layout) return;
	const rows = [...layout.querySelectorAll<HTMLElement>('[data-gd-solution-row]')];
	const pictures = new Map([...layout.querySelectorAll<HTMLElement>('[data-gd-solution-image]')].map((figure) => [figure.dataset.gdSolutionImage!, figure]));
	const initial = layout.querySelector<HTMLElement>('.gd-solution-image--default')?.dataset.gdSolutionImage;
	if (!initial || !rows.length || !pictures.size) return;

	media.add('(min-width: 62rem)', () => {
		const events = new AbortController();
		let disposed = false;
		let version = 0;
		let active = initial;
		let scroll = initial;
		let hovered = rows.find((row) => row.matches(':hover'))?.dataset.gdSolutionRow;
		let focused = rows.find((row) => row.contains(document.activeElement))?.dataset.gdSolutionRow;
		let frame = 0;
		const transforms = new Map([...pictures.values()].map((figure) => [figure, figure.style.transform]));
		layout.classList.add('gd-solutions--motion');
		pictures.get(initial)?.classList.add('is-active');
		rows.forEach((row) => row.classList.toggle('is-active', row.dataset.gdSolutionRow === initial));

		const activate = (): void => {
			const requested = focused ?? hovered ?? scroll;
			const currentVersion = ++version;
			const figure = pictures.get(requested);
			if (!figure || requested === active) return;
			const show = (): void => {
				if (disposed || currentVersion !== version) return;
				gsap.killTweensOf([...pictures.values()]);
				pictures.forEach((picture, key) => picture.classList.toggle('is-active', key === requested));
				rows.forEach((row) => row.classList.toggle('is-active', row.dataset.gdSolutionRow === requested));
				gsap.fromTo(figure, { scale: 1.035 }, { scale: 1, duration: 0.48, ease: 'power2.out', overwrite: true });
				active = requested;
			};
			const image = figure.querySelector<HTMLImageElement>('img');
			if (!image || (image.complete && image.naturalWidth > 0)) show();
			else {
				image.loading = 'eager';
				void image.decode().then(show).catch(() => undefined);
			}
		};
		const scrollActivate = (key: string): void => {
			scroll = key;
			window.cancelAnimationFrame(frame);
			frame = window.requestAnimationFrame(activate);
		};
		rows.forEach((row) => {
			const key = row.dataset.gdSolutionRow!;
			ScrollTrigger.create({ trigger: row, start: 'top 58%', end: 'bottom 43%', onEnter: () => scrollActivate(key), onEnterBack: () => scrollActivate(key) });
			row.addEventListener('pointerenter', () => { hovered = key; activate(); }, { signal: events.signal });
			row.addEventListener('pointerleave', () => { hovered = undefined; activate(); }, { signal: events.signal });
			row.addEventListener('focusin', () => { focused = key; activate(); }, { signal: events.signal });
			row.addEventListener('focusout', (event) => {
				if (event.relatedTarget instanceof Node && row.contains(event.relatedTarget)) return;
				focused = undefined; activate();
			}, { signal: events.signal });
		});
		activate();
		return () => {
			disposed = true; version++;
			events.abort();
			window.cancelAnimationFrame(frame);
			gsap.killTweensOf([...pictures.values()]);
			layout.classList.remove('gd-solutions--motion');
			pictures.forEach((figure) => figure.classList.remove('is-active'));
			pictures.forEach((figure) => { figure.style.transform = transforms.get(figure) ?? ''; });
			rows.forEach((row) => row.classList.remove('is-active'));
		};
	}, root);
}
