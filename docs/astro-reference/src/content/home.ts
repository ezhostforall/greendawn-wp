import type {
  AftercareService,
  InsightSummary,
  NumberedContent,
  Solution,
  SupportingProof,
  SurveyTier,
  SystemLayer,
} from "../types/home";
import { homeMedia } from "./media";

export const systemLayers = [
  {
    number: "01",
    title: "Power & infrastructure",
    copy: "Assess charging demand, available power, cable routes and groundworks. Identify any connection work needed to support the design.",
    detail: "Capacity before equipment",
  },
  {
    number: "02",
    title: "Charging equipment",
    copy: "Select AC or DC equipment around the vehicles, parking time and energy required—not the headline power rating alone.",
    detail: "Chosen for the operation",
  },
  {
    number: "03",
    title: "Software & operation",
    copy: "Plan how people use the chargers, how tariffs are managed and what your team needs to monitor. Set out the software and support arrangements.",
    detail: "Access, payments and visibility",
  },
] satisfies readonly SystemLayer[];

export const processSteps = [
  {
    number: "01",
    title: "Consult",
    copy: "Tell us about your vehicles, users, sites and objectives. We establish the requirement and recommend the next step.",
  },
  {
    number: "02",
    title: "Design",
    copy: "Assess power, layout and delivery constraints. Where a technical survey is needed, agree its scope before developing the design and proposal.",
  },
  {
    number: "03",
    title: "Install",
    copy: "Coordinate the agreed electrical and civil works, equipment, testing and commissioning. Plan access and handover with your team.",
  },
  {
    number: "04",
    title: "Manage",
    copy: "Put the agreed software, monitoring, maintenance and fault-support arrangements in place, with clear ongoing responsibilities.",
  },
] satisfies readonly NumberedContent[];

export const audiences = [
  {
    number: "01",
    title: "Fleet & depot charging",
    copy: "Plan around daily energy demand, charging windows and when vehicles need to leave.",
    linkLabel: "Explore fleet charging",
    href: "/fleet-charging-solutions/",
    image: homeMedia.fleetChargingBays,
    alt: "Completed EV charging bays at a commercial site",
  },
  {
    number: "02",
    title: "Workplace charging",
    copy: "Match provision to staff, company vehicles and visitors, with capacity and access considered from the start.",
    linkLabel: "Explore workplace charging",
    href: "/workplace-charging/",
    image: homeMedia.workplaceCharging,
    alt: "Wall-mounted EV charger beside a parked vehicle",
  },
  {
    number: "03",
    title: "Public & destination charging",
    copy: "Plan charging around the visitor experience, parking time, payment and ongoing support.",
    linkLabel: "Explore public and destination charging",
    href: "/public-charging/",
    image: homeMedia.destinationHospitality,
    alt: "EV chargers installed outside a hotel",
  },
  {
    number: "04",
    title: "Multi-site programmes",
    copy: "Coordinate shared requirements while accounting for the different constraints at each location.",
    linkLabel: "Explore commercial project delivery",
    href: "/business-ev-charging-installations/",
    image: homeMedia.multiSiteCharging,
    alt: "Completed EV charging installation with multiple marked bays",
  },
  {
    number: "05",
    title: "Dealerships & motor retail",
    copy: "Plan for vehicle preparation, workshops, demonstrators, handovers and customer charging.",
    linkLabel: "Read the dealership planning guide",
    href: "/guides/ev-charging-for-car-dealership/",
    image: homeMedia.dealershipCharging,
    alt: "EV charging equipment installed at a Ford dealership",
  },
] satisfies readonly Solution[];

export const aftercareServices = [
  {
    number: "01",
    title: "Monitoring & reporting",
    copy: "Back-office configuration, charging data and reporting kept visible through one support route.",
  },
  {
    number: "02",
    title: "Inspection & testing",
    copy: "Planned inspection and electrical testing scoped to the estate and its operating environment.",
  },
  {
    number: "03",
    title: "Maintenance & fault support",
    copy: "Fault investigation, maintenance, repair and warranty coordination through Greendawn.",
  },
  {
    number: "04",
    title: "Driver & user support",
    copy: "Driver support options for fleet, public and customer-facing charging.",
  },
] satisfies readonly AftercareService[];

export const surveyTiers = [
  {
    id: "survey-1-2",
    name: "Essential site survey",
    price: 200,
    suitableFor: "1–2 charging sockets",
    scope: ["Proposed charger positions", "Electrical route and practical constraints"],
    deliverable: "A documented site assessment and scoped next step.",
    benefitLabel: "Included support",
    benefit: "One year of selected back-office and testing support",
  },
  {
    id: "survey-3-7",
    name: "Standard site survey",
    price: 500,
    suitableFor: "3–7 charging sockets",
    scope: ["Site capacity and electrical routes", "Infrastructure, civils and charger locations"],
    deliverable: "A documented technical scope suitable for formal quotation.",
    benefitLabel: "Installation credit",
    benefit: "Full £500 credit applied when the installation is ordered",
  },
  {
    id: "survey-8-14",
    name: "Extended site survey",
    price: 1000,
    suitableFor: "8–14 charging sockets",
    scope: ["Multi-unit capacity and phasing", "Infrastructure, civils and operational constraints"],
    deliverable: "A documented technical scope with phased installation considerations.",
    benefitLabel: "Installation credit",
    benefit: "£500 credit applied when the installation is ordered",
  },
  {
    id: "survey-15-plus",
    name: "Complex site survey",
    price: 1500,
    suitableFor: "15+ sockets or DC rapid charging",
    scope: ["Higher-demand electrical and operational requirements", "Infrastructure, phasing and network dependencies"],
    deliverable: "A documented technical scope for a complex or higher-demand project.",
    benefitLabel: "Installation credit",
    benefit: "£500 credit applied when the installation is ordered",
  },
] satisfies readonly SurveyTier[];

export const supportingProof = [
  {
    eyebrow: "Fleet programme experience",
    title: "The Salvation Army",
    result: "50 sites · 71 chargepoints",
    copy: "Fleet charging across a varied estate, with different site requirements brought together within one coordinated programme.",
    image: homeMedia.salvationArmy,
    alt: "Commercial EV chargers installed for The Salvation Army",
    kind: "project",
  },
  {
    eyebrow: "Great Britain coverage",
    title: "England, Scotland & Wales",
    result: "One managed delivery model",
    copy: "Greendawn combines its engineers with managed specialists where required, with maintenance following the same footprint.",
    kind: "coverage",
  },
  {
    eyebrow: "AC to DC infrastructure",
    title: "Built around the operation",
    result: "7–22 kW AC through to DC rapid charging",
    copy: "The specification follows dwell time, vehicles and available power—from workplace AC to higher-demand fleet infrastructure.",
    kind: "capability",
  },
] satisfies readonly SupportingProof[];

export const insights = [
  {
    label: "Power & capacity",
    title: "Understanding load management",
    href: "/guides/load-balancing-explained/",
    image: homeMedia.electricalConnection,
    alt: "New electrical connection for a commercial EV charging project",
  },
  {
    label: "Project planning",
    title: "What a technical survey should establish",
    href: "/guides/greendawn-ev-charging-survey-pre-installation-guide/",
    image: homeMedia.siteSurvey,
    alt: "Completed paving and ducting area for an EV charging installation",
  },
  {
    label: "Fleet planning",
    title: "Planning the transition to an electric fleet",
    href: "/guides/transitioning-to-electric-fleets/",
    image: homeMedia.fleetChargingBays,
    alt: "Completed EV charging bays at a commercial fleet site",
  },
] satisfies readonly InsightSummary[];
