import { gsap } from "../motion/runtime";
import { animatePath } from "./path";

export function animateSystem(): void {
  animatePath("[data-system-path]", {
    trigger: "[data-system]",
    start: "top 65%",
    end: "55% 55%",
    scrub: 1,
  });

  gsap.from("[data-system-card]", {
    y: 58,
    duration: 0.72,
    stagger: 0.1,
    ease: "power4.out",
    scrollTrigger: {
      trigger: ".system__cards",
      start: "top 82%",
    },
  });
}

export function animateDesktopSystem(): void {
  gsap.to(".system__cards", {
    yPercent: -6,
    ease: "none",
    scrollTrigger: {
      trigger: "[data-system]",
      start: "top 70%",
      end: "bottom top",
      scrub: 1,
    },
  });
}
