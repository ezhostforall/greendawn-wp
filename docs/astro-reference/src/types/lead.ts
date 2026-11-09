export type LeadSource = "homepage" | "ev-charging" | "power" | "aftercare";

export type LeadEntryPoint =
  | "floating-launcher"
  | "header"
  | "hero"
  | "services"
  | "pricing"
  | "final-cta"
  | "footer-cta";

export type LeadIntent =
  | "ev-charging"
  | "power"
  | "solar-battery"
  | "aftercare"
  | "unsure";

export type LeadProjectStage =
  | "exploring"
  | "planning"
  | "quote"
  | "existing"
  | "problem";

export type LeadCallbackPreference =
  | "asap"
  | "today"
  | "tomorrow"
  | "scheduled";

export type LeadSiteType =
  | "workplace"
  | "fleet-depot"
  | "public-destination"
  | "motor-dealership"
  | "other"
  | "unsure";

export type LeadAftercareIssue =
  | "offline"
  | "slow-charging"
  | "payment-software"
  | "maintenance"
  | "other"
  | "unsure";

export type LeadPowerReason =
  | "capacity"
  | "grid-upgrade"
  | "expansion"
  | "ev-demand"
  | "other"
  | "unsure";

export interface LeadSubmission {
  name: string;
  company: string;
  phone: string;
  email: string;
  callbackPreference: LeadCallbackPreference;
  source: LeadSource;
  entryPoint: LeadEntryPoint;
  pageUrl: string;
  submittedAt: string;
  intent?: LeadIntent;
  projectStage?: LeadProjectStage;
  siteType?: LeadSiteType;
  aftercareIssue?: LeadAftercareIssue;
  powerReason?: LeadPowerReason;
  location?: string;
  preferredCallbackTime?: string;
  notes?: string;
  utmSource?: string;
  utmMedium?: string;
  utmCampaign?: string;
}

export type LeadFunnelEventName =
  | "lead_tool_opened"
  | "lead_intent_selected"
  | "lead_callback_started"
  | "lead_callback_submitted";

export interface LeadFunnelEventDetail {
  event: LeadFunnelEventName;
  source: LeadSource;
  entryPoint: LeadEntryPoint;
  intent?: LeadIntent;
  projectStage?: LeadProjectStage;
}
