import type { LeadSubmission } from "../../types/lead";

/**
 * Development implementation.
 *
 * GitHub Pages does not provide server-side execution. Replace this function
 * with POST /api/lead.php when the production site is deployed to Greendawn's
 * PHP hosting. Do not expose a Zapier webhook or integration credential here.
 */
export async function submitLead(lead: LeadSubmission): Promise<void> {
  console.groupCollapsed("Greendawn — Mock Lead Submission");
  console.log("[Greendawn lead payload]", lead);
  console.groupEnd();

  await new Promise<void>((resolve) => {
    window.setTimeout(resolve, 400);
  });
}
