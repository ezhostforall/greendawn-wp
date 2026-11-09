import { animateFinalCta } from "./animations/final-cta";
import { animateHero } from "./animations/hero";
import { animateProblem } from "./animations/problem";
import { animateProcess } from "./animations/process";
import { animateDesktopProjectStory, animateProjects } from "./animations/projects";
import { animateReveals, revealWithoutMotion } from "./animations/reveals";
import { initialiseSolutionStory } from "./animations/solutions";
import { animateDesktopSystem, animateSystem } from "./animations/system";
import { createMotionRuntime, gsap } from "./motion/runtime";
import { initialiseNavigation } from "./navigation";
import { initialiseLeadCapture } from "./lead-capture/controller";

const cleanupNavigation = initialiseNavigation();
const cleanupLeadCapture = initialiseLeadCapture();
const motion = createMotionRuntime();

if (motion.reduceMotion) {
  revealWithoutMotion();
} else {
  motion.run((registerCleanup) => {
    gsap.ticker.lagSmoothing(500, 33);
    registerCleanup(animateHero());
    animateReveals();
    registerCleanup(animateProblem());
    animateSystem();
    animateProcess();
    registerCleanup(initialiseSolutionStory());
    animateProjects();
    animateFinalCta();

    const desktop = gsap.matchMedia();
    desktop.add("(min-width: 62rem) and (min-height: 44.01rem) and (pointer: fine)", () => {
      animateDesktopSystem();
      animateDesktopProjectStory();
    });
    registerCleanup(() => desktop.revert());
  });
}

motion.refreshWhenReady();

const teardown = (): void => {
  window.removeEventListener("pagehide", onPageHide);
  cleanupLeadCapture();
  cleanupNavigation();
  motion.destroy();
};

const onPageHide = (event: PageTransitionEvent): void => {
  if (!(event as PageTransitionEvent).persisted) teardown();
};

window.addEventListener("pagehide", onPageHide);
