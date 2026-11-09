import type { ResponsiveImageAsset } from "../types/media";

const asset = (name: string, widths: readonly number[], width: number, height: number, objectPosition = "50% 50%", mobileObjectPosition = objectPosition): ResponsiveImageAsset => ({
  sources: widths.map((sourceWidth) => ({
    src: `/images/optimized/${name}-${sourceWidth}.webp`,
    width: sourceWidth,
  })),
  width,
  height,
  objectPosition,
  mobileObjectPosition,
});

// Purpose-led references to the curated Greendawn homepage photography. Source
// and publication records are maintained in docs/image-sources.md.
export const homeMedia = {
  heroEngineers: asset("greendawn-engineers-electrical-distribution", [480, 800, 1200, 1600], 1600, 1200, "61% 50%", "67% 50%"),
  electricalConnection: asset("commercial-ev-electrical-connection", [480, 800, 1200], 1200, 900, "66% 50%", "62% 50%"),
  processInstallation: asset("commercial-ev-installation-work", [480, 768], 768, 1024, "50% 57%", "50% 54%"),
  fleetChargingBays: asset("commercial-ev-fleet-charging-bays", [480, 800, 1200], 1200, 900, "50% 56%"),
  workplaceCharging: asset("commercial-ev-workplace-charging", [480, 800, 1200], 1200, 2797, "50% 52%", "50% 52%"),
  dealershipCharging: asset("commercial-ev-dealership-charging", [480, 768, 1200], 1200, 1600, "50% 48%", "50% 44%"),
  destinationHospitality: asset("commercial-ev-destination-hospitality", [480, 800, 1200], 1200, 900, "50% 52%"),
  multiSiteCharging: asset("commercial-ev-multi-site-charging", [480, 768], 768, 1024, "50% 57%", "50% 54%"),
  featuredDealership: asset("johnsons-dealership-ev-charging", [480, 800, 1200], 1200, 960, "50% 50%", "48% 45%"),
  siteSurvey: asset("commercial-ev-site-survey", [480, 768, 1024], 1024, 768, "50% 57%"),
  salvationArmy: asset("salvation-army-ev-charging", [480, 666], 666, 466, "50% 54%"),
} satisfies Record<string, ResponsiveImageAsset>;
