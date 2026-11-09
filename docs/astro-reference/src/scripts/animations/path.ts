import { gsap } from "../motion/runtime";

function preparePath(selector: string): SVGPathElement | null {
  const path = document.querySelector<SVGPathElement>(selector);
  if (!path) return null;

  const length = path.getTotalLength();
  gsap.set(path, {
    strokeDasharray: length,
    strokeDashoffset: length,
  });

  return path;
}

export function animatePath(selector: string, scrollTrigger: ScrollTrigger.Vars): void {
  const path = preparePath(selector);
  if (!path) return;

  gsap.to(path, {
    strokeDashoffset: 0,
    ease: "none",
    scrollTrigger,
  });
}

export function animatePathEntrance(selector: string, vars: gsap.TweenVars = {}): gsap.core.Tween | undefined {
  const path = preparePath(selector);
  if (!path) return undefined;

  return gsap.to(path, {
    strokeDashoffset: 0,
    duration: 1.2,
    ease: "power2.out",
    ...vars,
  });
}
