import { gsap, ScrollTrigger } from "../motion/runtime";

export function initialiseSolutionStory(): () => void {
  const rows = gsap.utils.toArray<HTMLElement>("[data-solution-row]");
  const images = gsap.utils.toArray<HTMLElement>("[data-solution-image]");
  if (!rows.length || !images.length) return () => undefined;
  const media = gsap.matchMedia();
  media.add("(min-width: 62rem)", () => {
    let activeIndex = 0;
    let requestVersion = 0;
    let disposed = false;
    gsap.set(images, { visibility: "hidden", scale: 1 });
    gsap.set(images[0], { visibility: "visible" });
    const activate = (index: number): void => {
      const version = ++requestVersion;
      if (!images[index] || index === activeIndex) return;
      const showImage = (): void => {
        if (disposed || version !== requestVersion) return;
        gsap.killTweensOf(images);
        rows.forEach((row, rowIndex) => row.classList.toggle("is-active", rowIndex === index));
        images.forEach((image, imageIndex) => {
          if (imageIndex !== index) gsap.set(image, { visibility: "hidden", scale: 1.025 });
        });
        gsap.set(images[index], { visibility: "visible", scale: 1.035 });
        gsap.to(images[index], { scale: 1, duration: 0.48, ease: "power2.out", overwrite: true });
        activeIndex = index;
      };
      const image = images[index].querySelector<HTMLImageElement>("img");
      if (!image || (image.complete && image.naturalWidth > 0)) showImage();
      else {
        image.loading = "eager";
        // Keep the current photograph visible until its replacement is ready.
        void image.decode().then(showImage).catch(() => undefined);
      }
    };
    const cleanups: Array<() => void> = [];
    rows.forEach((row, index) => {
      const trigger = ScrollTrigger.create({ trigger: row, start: "top 58%", end: "bottom 43%", onEnter: () => activate(index), onEnterBack: () => activate(index) });
      const onActivate = (): void => activate(index);
      row.addEventListener("pointerenter", onActivate);
      row.addEventListener("focus", onActivate);
      cleanups.push(() => {
        trigger.kill();
        row.removeEventListener("pointerenter", onActivate);
        row.removeEventListener("focus", onActivate);
      });
    });
    return () => {
      disposed = true;
      requestVersion++;
      cleanups.forEach((cleanup) => cleanup());
      gsap.killTweensOf(images);
      rows.forEach((row, index) => row.classList.toggle("is-active", index === 0));
      gsap.set(images, { clearProps: "all" });
    };
  });
  return () => media.revert();
}
