import type {
  LeadAftercareIssue,
  LeadCallbackPreference,
  LeadIntent,
  LeadPowerReason,
  LeadProjectStage,
  LeadSiteType,
} from "../types/lead";

export interface LeadChoice<T extends string> {
  value: T;
  label: string;
}

export interface LeadConditionalQuestion<T extends string> {
  legend: string;
  name: "siteType" | "aftercareIssue" | "powerReason";
  choices: ReadonlyArray<LeadChoice<T>>;
}

export const leadIntentChoices: ReadonlyArray<LeadChoice<LeadIntent>> = [
  { value: "ev-charging", label: "EV charging" },
  { value: "power", label: "Power / grid capacity" },
  { value: "solar-battery", label: "Solar & battery" },
  { value: "aftercare", label: "Existing chargers / aftercare" },
  { value: "unsure", label: "Not sure yet" },
];

export const leadProjectStageChoices: ReadonlyArray<LeadChoice<LeadProjectStage>> = [
  { value: "exploring", label: "Just exploring" },
  { value: "planning", label: "Planning a project" },
  { value: "quote", label: "Need a quote" },
  { value: "existing", label: "Already have infrastructure" },
  { value: "problem", label: "Something isn’t working" },
];

export const leadCallbackChoices: ReadonlyArray<LeadChoice<LeadCallbackPreference>> = [
  { value: "asap", label: "As soon as possible" },
  { value: "today", label: "Today" },
  { value: "tomorrow", label: "Tomorrow" },
  { value: "scheduled", label: "Suggest a time" },
];

export const leadConditionalQuestions: {
  readonly "ev-charging": LeadConditionalQuestion<LeadSiteType>;
  readonly aftercare: LeadConditionalQuestion<LeadAftercareIssue>;
  readonly power: LeadConditionalQuestion<LeadPowerReason>;
} = {
  "ev-charging": {
    legend: "What best describes the site?",
    name: "siteType",
    choices: [
      { value: "workplace", label: "Workplace" },
      { value: "fleet-depot", label: "Fleet / depot" },
      { value: "public-destination", label: "Public / destination" },
      { value: "motor-dealership", label: "Motor dealership" },
      { value: "other", label: "Other" },
      { value: "unsure", label: "Not sure" },
    ],
  },
  aftercare: {
    legend: "What are you experiencing?",
    name: "aftercareIssue",
    choices: [
      { value: "offline", label: "Chargers offline" },
      { value: "slow-charging", label: "Charging too slowly" },
      { value: "payment-software", label: "Payment / software issue" },
      { value: "maintenance", label: "Maintenance" },
      { value: "other", label: "Something else" },
      { value: "unsure", label: "Not sure" },
    ],
  },
  power: {
    legend: "What’s prompted the enquiry?",
    name: "powerReason",
    choices: [
      { value: "capacity", label: "Not enough capacity" },
      { value: "grid-upgrade", label: "Grid upgrade" },
      { value: "expansion", label: "Future expansion" },
      { value: "ev-demand", label: "EV charging demand" },
      { value: "other", label: "Something else" },
      { value: "unsure", label: "Not sure" },
    ],
  },
};
