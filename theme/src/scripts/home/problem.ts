import { gsap } from './runtime';

/** The pinned Astro assembly sequence, using the WordPress section's actual nodes. */
export function setupProblem(root: HTMLElement, media: gsap.MatchMedia): void {
	const model = root.querySelector<HTMLElement>('.gd-problem__model');
	const fragments = [...root.querySelectorAll<HTMLElement>('.gd-problem__fragments > li')];
	const connector = model?.querySelector<HTMLElement>('.gd-problem__connector > span');
	const answer = model?.querySelector<HTMLElement>('.gd-problem__answer');
	if (!model || !fragments.length || !connector || !answer) return;
	const wide = [[-56, -34, -2.4], [56, -34, 2.2], [-72, 0, -1.8], [72, 0, 1.8], [-48, 34, -2.1], [48, 34, 2.4]];
	const narrow = [[-24, -20, -1.6], [24, -20, 1.6], [-30, 0, -1.2], [30, 0, 1.2], [-22, 20, -1.4], [22, 20, 1.4]];
	media.add({ compact: '(max-width: 22.5rem)', narrow: '(max-width: 42rem)', wide: '(min-width: 42.01rem)' }, (context) => {
		const small = Boolean(context.conditions?.narrow);
		const compact = Boolean(context.conditions?.compact);
		const offsets = small ? narrow : wide;
		const timeline = gsap.timeline({ defaults: { ease: 'power3.out' }, scrollTrigger: { trigger: model, start: 'top 78%', once: true } });
		timeline.from(fragments, {
			x: (index) => compact ? 0 : offsets[index]?.[0] ?? 0,
			y: (index) => offsets[index]?.[1] ?? 0,
			rotation: (index) => offsets[index]?.[2] ?? 0,
			scale: 0.94, duration: 0.72, stagger: { each: 0.08, from: 'edges' }, clearProps: 'transform',
		}).from(connector, {
			scaleX: small ? 1 : 0, scaleY: small ? 0 : 1,
			transformOrigin: small ? 'top center' : 'left center', duration: 0.34, ease: 'power2.out', clearProps: 'transform',
		}, '-=0.06').from(answer, {
			clipPath: small ? 'inset(0 0 100% 0 round 1.35rem)' : 'inset(0 100% 0 0 round 1.35rem)',
			scale: 0.985, duration: 0.55, clearProps: 'clipPath,transform',
		}, '-=0.04').from(answer.children, { y: 14, duration: 0.38, stagger: 0.06, clearProps: 'transform' }, '-=0.26');
	}, root);
}
