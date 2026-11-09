import { gsap } from "../motion/runtime";

export function animateProcess(): void {
  gsap.to("[data-process-line]", {
    width: "100%",
    ease: "none",
    scrollTrigger: {
      trigger: "[data-process]",
      start: "top 72%",
      end: "bottom 62%",
      scrub: 1,
    },
  });

  gsap.from("[data-process-step]", {
    y: 24,
    stagger: 0.1,
    ease: "none",
    scrollTrigger: {
      trigger: "[data-process]",
      start: "top 74%",
      end: "bottom 68%",
      scrub: 1,
    },
  });
}
