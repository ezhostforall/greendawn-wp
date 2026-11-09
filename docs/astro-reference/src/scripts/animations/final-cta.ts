import { animatePath } from "./path";

export function animateFinalCta(): void {
  animatePath("[data-final-arc]", {
    trigger: "[data-final-cta]",
    start: "top 80%",
    end: "bottom 55%",
    scrub: 1,
  });
}
