import { gsap } from "../motion/runtime";

type FragmentOffset = readonly [x: number, y: number, rotation: number];

const wideOffsets: FragmentOffset[] = [
  [-56, -34, -2.4],
  [56, -34, 2.2],
  [-72, 0, -1.8],
  [72, 0, 1.8],
  [-48, 34, -2.1],
  [48, 34, 2.4],
];

const narrowOffsets: FragmentOffset[] = [
  [-24, -20, -1.6],
  [24, -20, 1.6],
  [-30, 0, -1.2],
  [30, 0, 1.2],
  [-22, 20, -1.4],
  [22, 20, 1.4],
];

export function animateProblem(): () => void {
  const model = document.querySelector<HTMLElement>("[data-problem-model]");
  const fragments = gsap.utils.toArray<HTMLElement>("[data-fragment]");
  const connector = document.querySelector<HTMLElement>("[data-problem-connector]");
  const answer = document.querySelector<HTMLElement>("[data-answer]");

  if (!model || fragments.length === 0 || !connector || !answer) return () => undefined;

  const media = gsap.matchMedia();
  media.add(
    {
      compact: "(max-width: 22.5rem)",
      narrow: "(max-width: 42rem)",
      wide: "(min-width: 42.01rem)",
    },
    (context) => {
      const compact = Boolean(context.conditions?.compact);
      const narrow = Boolean(context.conditions?.narrow);
      const offsets = narrow ? narrowOffsets : wideOffsets;
      const timeline = gsap.timeline({
        defaults: { ease: "power3.out" },
        scrollTrigger: {
          trigger: model,
          start: "top 78%",
          once: true,
        },
      });

      timeline
        .from(fragments, {
          x: (index) => compact ? 0 : (offsets[index]?.[0] ?? 0),
          y: (index) => offsets[index]?.[1] ?? 0,
          rotation: (index) => offsets[index]?.[2] ?? 0,
          scale: 0.94,
          duration: 0.72,
          stagger: { each: 0.08, from: "edges" },
          clearProps: "transform",
        })
        .from(
          connector,
          {
            scaleX: narrow ? 1 : 0,
            scaleY: narrow ? 0 : 1,
            transformOrigin: narrow ? "top center" : "left center",
            duration: 0.34,
            ease: "power2.out",
            clearProps: "transform",
          },
          "-=0.06",
        )
        .from(
          answer,
          {
            clipPath: narrow
              ? "inset(0 0 100% 0 round 1.35rem)"
              : "inset(0 100% 0 0 round 1.35rem)",
            scale: 0.985,
            duration: 0.55,
            clearProps: "clipPath,transform",
          },
          "-=0.04",
        )
        .from(
          answer.children,
          {
            y: 14,
            duration: 0.38,
            stagger: 0.06,
            clearProps: "transform",
          },
          "-=0.26",
        );

      return () => timeline.kill();
    },
  );

  return () => media.revert();
}
