import { gsap, ScrollTrigger } from './runtime';
import { setupProblem } from './problem';
import { setupSolutions } from './solutions';

/** Astro scroll ranges and easing; root context restores the complete static layout. */
export function initHomeMotion(root: HTMLElement): () => void {
	const context = gsap.context(() => undefined, root);
	const media = gsap.matchMedia();
	const lifecycle = new AbortController();
	let active = true;
	let frame = 0;
	const refresh = (): void => {
		window.cancelAnimationFrame(frame);
		frame = window.requestAnimationFrame(() => { if (active) ScrollTrigger.refresh(); });
	};
	const dispose = (): void => {
		if (!active) return;
		active = false;
		lifecycle.abort();
		window.cancelAnimationFrame(frame);
		media.revert();
		context.revert();
		delete root.dataset.gdScrollReady;
	};

	try {
		context.add(() => {
			const drawPath = (selector: string, scrollTrigger: ScrollTrigger.Vars): void => {
				const path = root.querySelector<SVGPathElement>(selector);
				if (!path) return;
				const length = path.getTotalLength();
				gsap.fromTo(path, { strokeDasharray: length, strokeDashoffset: length }, { strokeDashoffset: 0, ease: 'none', scrollTrigger });
			};
			setupProblem(root, media);
			drawPath('.gd-system__arc path', { trigger: root.querySelector('#system'), start: 'top 65%', end: '55% 55%', scrub: 1 });
			gsap.from(root.querySelectorAll('.gd-system__card'), { y: 58, duration: 0.72, stagger: 0.1, ease: 'power4.out', clearProps: 'transform', scrollTrigger: { trigger: root.querySelector('.gd-system__cards'), start: 'top 82%' } });
			const process = root.querySelector('.gd-process__track');
			gsap.fromTo(root.querySelector('.gd-process__line span'), { scaleX: 0 }, { scaleX: 1, ease: 'none', scrollTrigger: { trigger: process, start: 'top 72%', end: 'bottom 62%', scrub: 1 } });
			gsap.from(root.querySelectorAll('.gd-process__steps > li'), { y: 24, stagger: 0.1, ease: 'none', scrollTrigger: { trigger: process, start: 'top 74%', end: 'bottom 68%', scrub: 1 } });
			setupSolutions(root, media);
			const caseImage = root.querySelector('.gd-case-study__media img');
			if (caseImage) gsap.fromTo(caseImage, { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: root.querySelector('#projects'), start: 'top bottom', end: 'bottom top', scrub: true } });
			drawPath('.gd-final-cta > svg path', { trigger: root.querySelector('#final-enquiry'), start: 'top 80%', end: 'bottom 55%', scrub: 1 });
			media.add('(min-width: 62rem) and (min-height: 44.01rem) and (pointer: fine)', () => {
				const heroImage = root.querySelector('.gd-hero__parallax');
				if (heroImage) gsap.to(heroImage, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: root.querySelector('.gd-hero'), start: 'top top', end: 'bottom top', scrub: true } });
				gsap.to(root.querySelector('.gd-system__cards'), { yPercent: -6, ease: 'none', scrollTrigger: { trigger: root.querySelector('#system'), start: 'top 70%', end: 'bottom top', scrub: 1 } });
				gsap.to(root.querySelector('.gd-case-study__story'), { yPercent: -3, ease: 'none', scrollTrigger: { trigger: root.querySelector('#projects'), start: 'top bottom', end: 'bottom top', scrub: 1 } });
			}, root);
		});
		root.dataset.gdScrollReady = 'true';
		root.addEventListener('load', refresh, { signal: lifecycle.signal, capture: true });
		window.addEventListener('pageshow', (event) => { if (event.persisted) refresh(); }, { signal: lifecycle.signal });
		void document.fonts.ready.then(() => { if (active) refresh(); });
		refresh();
		return dispose;
	} catch (error) {
		dispose();
		throw error;
	}
}
