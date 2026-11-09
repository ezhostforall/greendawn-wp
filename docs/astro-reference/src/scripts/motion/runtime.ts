import gsap from "gsap";
import ScrollTrigger from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export { gsap, ScrollTrigger };

export type MotionCleanup = () => void;
export type MotionSetup = (registerCleanup: (cleanup: MotionCleanup) => void) => void;

export interface MotionRuntime {
  readonly reduceMotion: boolean;
  run: (setup: MotionSetup) => void;
  refresh: () => void;
  refreshWhenReady: () => void;
  destroy: () => void;
}

export function createMotionRuntime(root: HTMLElement = document.body): MotionRuntime {
  const cleanups: MotionCleanup[] = [];
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let context: gsap.Context | undefined;
  let resizeFrame = 0;
  let active = true;

  const registerCleanup = (cleanup: MotionCleanup): void => {
    cleanups.push(cleanup);
  };

  const refresh = (): void => {
    if (active) ScrollTrigger.refresh();
  };

  const onResize = (): void => {
    window.cancelAnimationFrame(resizeFrame);
    resizeFrame = window.requestAnimationFrame(refresh);
  };

  const onPageShow = (event: PageTransitionEvent): void => {
    if (event.persisted) window.requestAnimationFrame(refresh);
  };

  window.addEventListener("resize", onResize, { passive: true });
  window.addEventListener("pageshow", onPageShow);

  return {
    reduceMotion,
    run(setup) {
      if (!active || reduceMotion || context) return;
      context = gsap.context(() => setup(registerCleanup), root);
    },
    refresh,
    refreshWhenReady() {
      const criticalImages = Array.from(document.querySelectorAll<HTMLImageElement>('img[loading="eager"]'));
      const imageReady = criticalImages.map((image) => image.complete
        ? Promise.resolve()
        : new Promise<void>((resolve) => {
            image.addEventListener("load", () => resolve(), { once: true });
            image.addEventListener("error", () => resolve(), { once: true });
          }));

      Promise.all([document.fonts.ready, ...imageReady]).then(refresh);
    },
    destroy() {
      if (!active) return;
      active = false;
      window.cancelAnimationFrame(resizeFrame);
      window.removeEventListener("resize", onResize);
      window.removeEventListener("pageshow", onPageShow);
      cleanups.splice(0).forEach((cleanup) => cleanup());
      context?.revert();
      context = undefined;
    },
  };
}
