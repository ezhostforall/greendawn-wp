import { gsap } from "../motion/runtime";

export function animateReveals(): void {
  const revealItems = gsap.utils.toArray<HTMLElement>("[data-reveal]");

  revealItems.forEach((item) => {
    gsap.from(item, {
      y: 28,
      duration: 0.62,
      ease: "power3.out",
      scrollTrigger: {
        trigger: item,
        start: "top 87%",
        once: true,
      },
    });
  });
}

export function revealWithoutMotion(): void {
  document.querySelectorAll<HTMLElement>("[data-reveal]").forEach((item) => {
    gsap.set(item, { clearProps: "all" });
  });
}
