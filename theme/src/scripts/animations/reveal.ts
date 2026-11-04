import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * Example GSAP module: fades elements marked with `data-animate="fade-up"` into view.
 *
 * - Loaded lazily from main.ts only when such elements exist.
 * - Disabled under `prefers-reduced-motion: reduce`.
 * - Uses gsap.matchMedia() so animations and ScrollTriggers are reverted automatically
 *   when the media query stops matching, and via the returned cleanup function.
 */
export function initReveal(): () => void {
	gsap.registerPlugin(ScrollTrigger);
	const mm = gsap.matchMedia();

	mm.add('(prefers-reduced-motion: no-preference)', () => {
		gsap.utils.toArray<HTMLElement>('[data-animate="fade-up"]').forEach((element) => {
			gsap.from(element, {
				autoAlpha: 0,
				y: 24,
				duration: 0.6,
				ease: 'power2.out',
				scrollTrigger: { trigger: element, start: 'top 85%', once: true },
			});
		});
	});

	return () => mm.revert();
}
