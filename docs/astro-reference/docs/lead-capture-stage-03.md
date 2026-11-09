# Lead capture Stage 3

## Status

Stage 3 adds the front-end callback experience to the verified Phase 5/6 homepage baseline. It does not add a production endpoint, Zapier webhook, Trello integration or analytics vendor.

The implementation is intentionally transport-agnostic and keeps the current static GitHub Pages deployment safe:

```text
LeadCapture.astro
       ↓
controller.ts
       ↓
submitLead(LeadSubmission)
       ↓
console log + 400ms mock delay
```

The later WordPress handoff should change only `submitLead()` to call a same-origin PHP endpoint. Server-side validation, normalisation, rate limiting, spam protection, logging and Zapier credentials belong behind that endpoint.

## User experience

One component is opened from four controlled entry-point categories. The header category has separate desktop and mobile triggers, so the page renders five triggers in total:

| Entry point | Controlled value | No-JavaScript behaviour |
| --- | --- | --- |
| Floating launcher | `floating-launcher` | Hidden |
| Header CTA | `header` | Links to the live enquiry page; the no-JavaScript mobile navigation includes the same fallback |
| Hero CTA | `hero` | Links to the live enquiry page |
| Final CTA | `final-cta` | Links to the live enquiry page |

Desktop uses a right-side drawer. Viewports up to 42rem use a bottom sheet. The tool never opens automatically.

The qualified flow is:

1. Area of interest.
2. At most one conditional context question.
3. Project stage.
4. Optional location.
5. Required name, company, telephone, email and callback preference, with optional project details.

Choice-based qualification steps advance as soon as an answer is activated. The copy makes that behaviour explicit, and the back control appears after the first step. “Just request a callback” bypasses all qualification and opens the callback fields directly. Back navigation and closing preserve answers until a successful mock submission is reset or reopened.

## Component API

```astro
<LeadCapture source="homepage" />
<LeadCapture source="ev-charging" initialIntent="ev-charging" />
```

`source` and `initialIntent` are controlled unions from `src/types/lead.ts`. There is one component instance per page and any trigger with `data-lead-capture-open` can open it. Every trigger must also declare a valid `data-lead-entry-point`.

## Ownership

| Concern | Owner |
| --- | --- |
| Markup and component CSS | `src/components/lead/LeadCapture.astro` |
| Controlled labels and choices | `src/content/lead-capture.ts` |
| Payload and event types | `src/types/lead.ts` |
| State, validation, focus and metadata | `src/scripts/lead-capture/controller.ts` |
| Mock/production transport boundary | `src/scripts/lead-capture/submit.ts` |
| PII-safe analytics boundary | `src/scripts/lead-capture/events.ts` |
| Lifecycle composition | `src/scripts/home.ts` |

No lead-capture CSS is added to the global stylesheets. `global.css` continues to import only tokens, foundations and shared primitives.

## Payload contract

`LeadSubmission` has five visitor-required fields. Requiring a company helps qualify the callback as a commercial enquiry, while requiring email gives Greendawn a second response channel:

- `name`;
- `company`;
- `phone`;
- `email`;
- `callbackPreference`.

It also carries controlled qualification values where supplied, plus `source`, `entryPoint`, `pageUrl`, `submittedAt` and the `utm_source`, `utm_medium` and `utm_campaign` query values. Optional blank values are omitted from the final object.

Client validation is usability support, not a security boundary. The PHP endpoint must repeat validation independently and must not trust controlled unions merely because the browser emits them.

## Privacy and consent

The callback request does not use a mandatory consent checkbox. Greendawn needs an appropriate documented lawful basis for processing the submitted details and must provide the required privacy information, but consent is not automatically required for data used only to respond to a request initiated by the visitor. The form therefore gives a concise purpose statement and links to the privacy policy.

If the details are later used for an additional purpose such as email, text or telephone marketing, that purpose must be assessed separately. Where consent is required, it must use a separate, optional, unticked and specific opt-in rather than being bundled into the callback request.

## Analytics boundary

The controller dispatches `greendawn:lead-funnel` with one of:

- `lead_tool_opened`;
- `lead_intent_selected`;
- `lead_callback_started`;
- `lead_callback_submitted`.

Event detail is restricted to `source`, `entryPoint`, `intent` and `projectStage`. Name, telephone, email, company and free text must never be added. The analytics phase should subscribe to this event and map controlled values to the approved GA4/GTM schema.

## Accessibility and resilience

- Native `<dialog>` supplies modal semantics and Escape handling.
- Focus moves to each active step and returns to the originating trigger on close.
- Radio choices use fieldsets and legends.
- Errors are linked to controls, shown together and announced; focus moves to the first invalid control.
- Controls meet the approximate 44px touch-target requirement.
- The panel scrolls independently so the software keyboard does not trap the final controls off-screen.
- Motion is disabled under `prefers-reduced-motion: reduce`.
- Hero and final CTA anchors preserve live enquiry-page fallbacks without JavaScript.
- The floating enhancement is hidden while the root document remains in its `no-js` state.
- A persisted `pageshow` closes any restored modal and the homepage lifecycle tears down listeners outside the back-forward cache.

## Testing

Static validation checks the single dialog, five rendered triggers, missing form action, mock submission boundary, absence of live network/webhook code, no-JavaScript rule and PII-safe event boundary.

Playwright coverage in `tests/home.spec.ts` verifies:

- the complete contextual path and constructed mock submission;
- automatic choice progression, visible back navigation and retained answers;
- no POST request;
- expected funnel-event order and absence of PII;
- the direct callback route;
- all required inline errors;
- Escape and focus return;
- narrow bottom-sheet containment;
- no-JavaScript CTA fallback behaviour.

The persistent launcher is an intentional visual change and will appear in all seven full-page screenshots. Run Playwright and inspect the diffs locally before accepting new baselines:

```bash
pnpm test
pnpm test:visual
pnpm test:visual:update # only after approving the intentional change
```

## Bundle impact

The same local toolchain was used to compare the untouched Phase 5/6 archive with this Stage 3 build.

| Asset | Phase 5/6 | Stage 3 | Change |
| --- | ---: | ---: | ---: |
| Compiled CSS, raw | 49,417 bytes | 59,036 bytes | +9,619 bytes |
| Compiled CSS, gzip | 9,608 bytes | 11,478 bytes | +1,870 bytes |
| Homepage JavaScript, raw | 121,616 bytes | 131,067 bytes | +9,451 bytes |
| Homepage JavaScript, gzip | 46,588 bytes | 49,687 bytes | +3,099 bytes |

No new dependency, stylesheet request, module-script request, image or font request was introduced. The increase is the self-contained form UI and controller.

## Production handoff checklist

Before replacing the mock:

1. Implement same-origin `POST /api/lead.php` on the WordPress host.
2. Validate and normalise all fields server-side.
3. Add rate limiting, spam controls, size limits and safe operational logging.
4. Keep Zapier credentials server-side and forward only the accepted payload.
5. Define failure responses without leaking internal details.
6. Update `submitLead()` only; do not duplicate endpoint knowledge in the controller or component.
7. Replace the development success copy with approved production confirmation copy.
8. Add endpoint integration tests and confirm privacy/retention responsibilities.
9. Complete the separate analytics phase using the existing PII-safe event boundary.
