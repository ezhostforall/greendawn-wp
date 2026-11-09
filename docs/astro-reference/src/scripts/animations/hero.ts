import { gsap } from "../motion/runtime";
import { animatePathEntrance } from "./path";

export function animateHero(): () => void {
  const timeline = gsap.timeline({ defaults: { ease: "power3.out" } });
  const arcTween = animatePathEntrance("[data-arc-path]", {
    delay: 0.12,
    duration: 1.25,
  });

  timeline
    .from("[data-hero-line]", {
      yPercent: 24,
      duration: 0.58,
      stagger: 0.08,
    })
    .from(
      "[data-hero-item]",
      {
        y: 18,
        duration: 0.48,
        stagger: 0.1,
      },
      "-=0.32",
    )
    .from(
      ".hero__proof > div",
      {
        y: 14,
        duration: 0.42,
        stagger: 0.08,
      },
      "-=0.28",
    );

  gsap.fromTo(
    "[data-hero-image] img",
    { scale: 1.045, opacity: 0.76 },
    { scale: 1, opacity: 1, duration: 0.72, ease: "power3.out" },
  );

  const media = gsap.matchMedia();
  media.add("(min-width: 62rem) and (min-height: 44.01rem) and (pointer: fine)", () => {
    gsap.to("[data-hero-image] img", {
      yPercent: 5,
      ease: "none",
      scrollTrigger: {
        trigger: "[data-hero]",
        start: "top top",
        end: "bottom top",
        scrub: true,
      },
    });
  });

  return () => {
    arcTween?.kill();
    timeline.kill();
    media.revert();
  };
}
