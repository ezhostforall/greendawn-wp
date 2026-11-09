import type {
  LeadAftercareIssue,
  LeadCallbackPreference,
  LeadEntryPoint,
  LeadIntent,
  LeadPowerReason,
  LeadProjectStage,
  LeadSiteType,
  LeadSource,
  LeadSubmission,
} from "../../types/lead";
import { emitLeadFunnelEvent } from "./events";
import { submitLead } from "./submit";

type LeadStep = "intent" | "context" | "stage" | "location" | "contact" | "success";
type ConditionalName = "siteType" | "aftercareIssue" | "powerReason";

const CONDITIONAL_FIELDS: Partial<Record<LeadIntent, ConditionalName>> = {
  "ev-charging": "siteType",
  aftercare: "aftercareIssue",
  power: "powerReason",
};

const PHONE_PATTERN = /^[+\d][\d\s()./-]*(?:\s?(?:x|ext\.?)\s?\d{1,6})?$/i;

function isLeadIntent(value: string): value is LeadIntent {
  return ["ev-charging", "power", "solar-battery", "aftercare", "unsure"].includes(value);
}

function isLeadEntryPoint(value: string): value is LeadEntryPoint {
  return ["floating-launcher", "header", "hero", "services", "pricing", "final-cta", "footer-cta"].includes(value);
}

function isLeadSource(value: string): value is LeadSource {
  return ["homepage", "ev-charging", "power", "aftercare"].includes(value);
}

function optionalValue(form: HTMLFormElement, name: string): string | undefined {
  const input = form.elements.namedItem(name);
  if (!(input instanceof HTMLInputElement || input instanceof HTMLTextAreaElement)) return undefined;
  const value = input.value.trim();
  return value || undefined;
}

function selectedValue<T extends string>(form: HTMLFormElement, name: string): T | undefined {
  return form.querySelector<HTMLInputElement>(`input[name="${name}"]:checked`)?.value as T | undefined;
}

function localDateTimeMinimum(): string {
  const now = new Date(Date.now() + 15 * 60 * 1000);
  const offset = now.getTimezoneOffset() * 60_000;
  return new Date(now.getTime() - offset).toISOString().slice(0, 16);
}

export function initialiseLeadCapture(root: ParentNode = document): () => void {
  const component = root.querySelector<HTMLElement>("[data-lead-capture]");
  if (!component) return () => undefined;

  const dialog = component.querySelector<HTMLDialogElement>("[data-lead-dialog]");
  const form = component.querySelector<HTMLFormElement>("[data-lead-form]");
  const panel = component.querySelector<HTMLElement>("[data-lead-panel]");
  const progressLabel = component.querySelector<HTMLElement>("[data-lead-progress-label]");
  const backButton = component.querySelector<HTMLButtonElement>("[data-lead-back]");
  const liveRegion = component.querySelector<HTMLElement>("[data-lead-live]");
  const globalError = component.querySelector<HTMLElement>("[data-lead-submit-error]");
  const submitButton = component.querySelector<HTMLButtonElement>("[data-lead-submit]");
  const contactActions = component.querySelector<HTMLElement>("[data-lead-contact-actions]");
  const scrollBody = component.querySelector<HTMLElement>(".lead-capture__body");
  const scheduleField = component.querySelector<HTMLElement>("[data-lead-schedule]");
  const scheduleInput = component.querySelector<HTMLInputElement>('input[name="preferredCallbackTime"]');
  const triggers = Array.from(root.querySelectorAll<HTMLElement>("[data-lead-capture-open]"));
  const closeButtons = Array.from(component.querySelectorAll<HTMLButtonElement>("[data-lead-close]"));
  const nextButtons = Array.from(component.querySelectorAll<HTMLButtonElement>("[data-lead-next]"));
  const directButton = component.querySelector<HTMLButtonElement>("[data-lead-direct]");
  const resetButton = component.querySelector<HTMLButtonElement>("[data-lead-reset]");
  const stepElements = Array.from(component.querySelectorAll<HTMLElement>("[data-lead-step]"));
  const conditionalGroups = Array.from(component.querySelectorAll<HTMLElement>("[data-lead-context]"));

  if (!dialog || !form || !panel || !progressLabel || !backButton || !liveRegion || !submitButton) {
    return () => undefined;
  }

  const autoAdvanceChoices = Array.from(form.querySelectorAll<HTMLInputElement>([
    'input[type="radio"][name="intent"]',
    'input[type="radio"][name="siteType"]',
    'input[type="radio"][name="aftercareIssue"]',
    'input[type="radio"][name="powerReason"]',
    'input[type="radio"][name="projectStage"]',
  ].join(",")));

  const sourceValue = component.dataset.source ?? "homepage";
  const source: LeadSource = isLeadSource(sourceValue) ? sourceValue : "homepage";
  const initialIntentValue = component.dataset.initialIntent ?? "";
  const initialIntent = isLeadIntent(initialIntentValue) ? initialIntentValue : undefined;
  let currentStep: LeadStep = initialIntent && CONDITIONAL_FIELDS[initialIntent] ? "context" : initialIntent ? "stage" : "intent";
  let history: LeadStep[] = [];
  let directFlow = false;
  let submitting = false;
  let submitted = false;
  let callbackStarted = false;
  let opener: HTMLElement | null = null;
  let entryPoint: LeadEntryPoint = "floating-launcher";

  const currentIntent = (): LeadIntent | undefined => selectedValue<LeadIntent>(form, "intent") ?? initialIntent;
  const currentStage = (): LeadProjectStage | undefined => selectedValue<LeadProjectStage>(form, "projectStage");
  const eventContext = () => ({
    source,
    entryPoint,
    intent: currentIntent(),
    projectStage: currentStage(),
  });

  const setLiveMessage = (message: string): void => {
    liveRegion.textContent = message;
  };

  const clearError = (name: string): void => {
    const error = component.querySelector<HTMLElement>(`[data-lead-error-for="${name}"]`);
    error?.setAttribute("hidden", "");
    component.querySelectorAll<HTMLElement>(`[name="${name}"]`).forEach((control) => {
      control.removeAttribute("aria-invalid");
    });
  };

  const showError = (name: string, message: string, focus = true): void => {
    const error = component.querySelector<HTMLElement>(`[data-lead-error-for="${name}"]`);
    if (error) {
      error.textContent = message;
      error.removeAttribute("hidden");
    }
    const controls = Array.from(component.querySelectorAll<HTMLElement>(`[name="${name}"]`));
    controls.forEach((control) => control.setAttribute("aria-invalid", "true"));
    if (focus) {
      controls[0]?.focus();
      setLiveMessage(message);
    }
  };

  const flow = (): LeadStep[] => {
    if (directFlow) return ["contact"];
    const steps: LeadStep[] = initialIntent ? [] : ["intent"];
    if (currentIntent() && CONDITIONAL_FIELDS[currentIntent()!]) steps.push("context");
    steps.push("stage", "location", "contact");
    return steps;
  };

  const updateProgress = (): void => {
    if (currentStep === "success") {
      progressLabel.textContent = "Complete";
      panel.style.setProperty("--lead-progress", "100");
      return;
    }
    const steps = flow();
    const position = Math.max(0, steps.indexOf(currentStep));
    const stageNames: Record<LeadStep, string> = {
      intent: "Your requirement",
      context: "Site details",
      stage: "Project stage",
      location: "Site location",
      contact: "Callback details",
      success: "Complete",
    };
    progressLabel.textContent = stageNames[currentStep];
    panel.style.setProperty("--lead-progress", String(((position + 1) / steps.length) * 100));
  };

  const updateConditionalGroup = (): void => {
    const fieldName = currentIntent() ? CONDITIONAL_FIELDS[currentIntent()!] : undefined;
    conditionalGroups.forEach((group) => {
      group.hidden = group.dataset.leadContext !== fieldName;
    });
  };

  const showStep = (step: LeadStep, announce = true): void => {
    currentStep = step;
    updateConditionalGroup();
    stepElements.forEach((element) => {
      element.hidden = element.dataset.leadStep !== step;
    });
    if (contactActions) contactActions.hidden = step !== "contact";
    if (scrollBody) scrollBody.scrollTop = 0;
    backButton.hidden = step === "success" || history.length === 0;
    updateProgress();
    const active = stepElements.find((element) => element.dataset.leadStep === step);
    const heading = step === "context"
      ? active?.querySelector<HTMLElement>("[data-lead-context]:not([hidden]) legend")
      : active?.querySelector<HTMLElement>("h2, legend");
    window.requestAnimationFrame(() => heading?.focus({ preventScroll: true }));
    if (announce && heading?.textContent) setLiveMessage(heading.textContent.trim());
  };

  const navigateTo = (step: LeadStep): void => {
    history.push(currentStep);
    showStep(step);
  };

  const validateChoice = (name: string, message: string): boolean => {
    clearError(name);
    if (selectedValue(form, name)) return true;
    showError(name, message);
    return false;
  };

  const validateCurrentStep = (): boolean => {
    if (currentStep === "intent") return validateChoice("intent", "Choose an area, or use the direct callback option.");
    if (currentStep === "stage") return validateChoice("projectStage", "Choose the option that best describes the project stage.");
    if (currentStep === "context") {
      const fieldName = currentIntent() ? CONDITIONAL_FIELDS[currentIntent()!] : undefined;
      return fieldName ? validateChoice(fieldName, "Choose an option to continue.") : true;
    }
    return true;
  };

  const startCallback = (): void => {
    if (callbackStarted) return;
    callbackStarted = true;
    emitLeadFunnelEvent("lead_callback_started", eventContext());
  };

  const advance = (): void => {
    if (!validateCurrentStep()) return;
    if (currentStep === "intent") {
      emitLeadFunnelEvent("lead_intent_selected", eventContext());
      navigateTo(CONDITIONAL_FIELDS[currentIntent()!] ? "context" : "stage");
    } else if (currentStep === "context") {
      navigateTo("stage");
    } else if (currentStep === "stage") {
      navigateTo("location");
    } else if (currentStep === "location") {
      startCallback();
      navigateTo("contact");
    }
  };

  const setScheduledVisibility = (): void => {
    const scheduled = selectedValue<LeadCallbackPreference>(form, "callbackPreference") === "scheduled";
    if (scheduleField) scheduleField.hidden = !scheduled;
    if (scheduleInput) {
      scheduleInput.disabled = !scheduled;
      scheduleInput.min = localDateTimeMinimum();
    }
  };

  const validateContact = (): boolean => {
    let valid = true;
    const name = form.elements.namedItem("name");
    const phone = form.elements.namedItem("phone");
    const company = form.elements.namedItem("company");
    const email = form.elements.namedItem("email");

    clearError("name");
    if (!(name instanceof HTMLInputElement) || !name.value.trim()) {
      showError("name", "Enter your first name.", valid);
      valid = false;
    }

    clearError("phone");
    if (phone instanceof HTMLInputElement) {
      const digits = phone.value.replace(/\D/g, "");
      if (!PHONE_PATTERN.test(phone.value.trim()) || digits.length < 7 || digits.length > 18) {
        showError("phone", "Enter a telephone number we can use for the callback.", valid);
        valid = false;
      }
    } else {
      valid = false;
    }

    clearError("company");
    if (!(company instanceof HTMLInputElement) || !company.value.trim()) {
      showError("company", "Enter your company name.", valid);
      valid = false;
    }

    clearError("email");
    if (!(email instanceof HTMLInputElement) || !email.value.trim()) {
      showError("email", "Enter your email address.", valid);
      valid = false;
    } else if (!email.validity.valid) {
      showError("email", "Enter a valid email address.", valid);
      valid = false;
    }

    clearError("callbackPreference");
    if (!selectedValue(form, "callbackPreference")) {
      showError("callbackPreference", "Choose when you would prefer Greendawn to call.", valid);
      valid = false;
    }

    return valid;
  };

  const buildSubmission = (): LeadSubmission => {
    const params = new URLSearchParams(window.location.search);
    const lead: LeadSubmission = {
      name: optionalValue(form, "name") ?? "",
      company: optionalValue(form, "company") ?? "",
      phone: optionalValue(form, "phone") ?? "",
      email: optionalValue(form, "email") ?? "",
      callbackPreference: selectedValue<LeadCallbackPreference>(form, "callbackPreference")!,
      source,
      entryPoint,
      pageUrl: window.location.href,
      submittedAt: new Date().toISOString(),
    };

    const optional: Partial<LeadSubmission> = {
      intent: currentIntent(),
      projectStage: currentStage(),
      siteType: selectedValue<LeadSiteType>(form, "siteType"),
      aftercareIssue: selectedValue<LeadAftercareIssue>(form, "aftercareIssue"),
      powerReason: selectedValue<LeadPowerReason>(form, "powerReason"),
      location: optionalValue(form, "location"),
      preferredCallbackTime: optionalValue(form, "preferredCallbackTime"),
      notes: optionalValue(form, "notes"),
      utmSource: params.get("utm_source")?.trim() || undefined,
      utmMedium: params.get("utm_medium")?.trim() || undefined,
      utmCampaign: params.get("utm_campaign")?.trim() || undefined,
    };

    for (const [key, value] of Object.entries(optional)) {
      if (value !== undefined) Object.assign(lead, { [key]: value });
    }
    return lead;
  };

  const submit = async (): Promise<void> => {
    if (submitting || !validateContact()) return;
    submitting = true;
    submitButton.disabled = true;
    submitButton.setAttribute("aria-busy", "true");
    submitButton.textContent = "Preparing request…";
    globalError?.setAttribute("hidden", "");

    try {
      const lead = buildSubmission();
      await submitLead(lead);
      submitted = true;
      emitLeadFunnelEvent("lead_callback_submitted", eventContext());
      showStep("success");
    } catch {
      if (globalError) {
        globalError.textContent = "The request could not be prepared. Please try again or call Greendawn directly.";
        globalError.removeAttribute("hidden");
        globalError.focus();
      }
    } finally {
      submitting = false;
      submitButton.disabled = false;
      submitButton.removeAttribute("aria-busy");
      submitButton.textContent = "Request callback";
    }
  };

  const reset = (): void => {
    form.reset();
    history = [];
    directFlow = false;
    submitting = false;
    submitted = false;
    callbackStarted = false;
    component.querySelectorAll<HTMLElement>("[data-lead-error-for]").forEach((error) => error.setAttribute("hidden", ""));
    component.querySelectorAll<HTMLElement>("[aria-invalid]").forEach((control) => control.removeAttribute("aria-invalid"));
    globalError?.setAttribute("hidden", "");
    const details = component.querySelector<HTMLDetailsElement>("[data-lead-details]");
    if (details) details.open = false;
    if (initialIntent) {
      const input = form.querySelector<HTMLInputElement>(`input[name="intent"][value="${initialIntent}"]`);
      if (input) input.checked = true;
    }
    setScheduledVisibility();
    showStep(initialIntent && CONDITIONAL_FIELDS[initialIntent] ? "context" : initialIntent ? "stage" : "intent", false);
  };

  const close = (): void => {
    if (dialog.open && typeof dialog.close === "function") dialog.close();
    else dialog.removeAttribute("open");
  };

  const onDialogClose = (): void => {
    document.body.classList.remove("lead-capture-open");
    opener?.focus();
  };

  const open = (trigger: HTMLElement): void => {
    if (submitted) reset();
    opener = trigger.closest(".mobile-nav")
      ? root.querySelector<HTMLElement>(".menu-toggle") ?? trigger
      : trigger;
    const requestedEntryPoint = trigger.dataset.leadEntryPoint ?? "floating-launcher";
    entryPoint = isLeadEntryPoint(requestedEntryPoint) ? requestedEntryPoint : "floating-launcher";
    if (typeof dialog.showModal === "function") dialog.showModal();
    else dialog.setAttribute("open", "");
    document.body.classList.add("lead-capture-open");
    emitLeadFunnelEvent("lead_tool_opened", eventContext());
    showStep(currentStep, false);
  };

  const onTriggerClick = (event: Event): void => {
    event.preventDefault();
    open(event.currentTarget as HTMLElement);
  };
  const onCancel = (event: Event): void => {
    event.preventDefault();
    close();
  };
  const onBack = (): void => {
    const previous = history.pop();
    if (!previous) return;
    if (previous === "intent") directFlow = false;
    showStep(previous);
  };
  const onDirect = (): void => {
    directFlow = true;
    startCallback();
    navigateTo("contact");
  };
  const onFormSubmit = (event: SubmitEvent): void => {
    event.preventDefault();
    if (currentStep === "contact") void submit();
    else advance();
  };
  const onFieldInput = (event: Event): void => {
    const control = event.target;
    if (control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement) clearError(control.name);
  };
  const onAutoAdvanceChoice = (event: Event): void => {
    const input = event.currentTarget;
    if (!(input instanceof HTMLInputElement) || !input.checked) return;

    const expectedName = currentStep === "intent"
      ? "intent"
      : currentStep === "stage"
        ? "projectStage"
        : currentStep === "context" && currentIntent()
          ? CONDITIONAL_FIELDS[currentIntent()!]
          : undefined;

    if (input.name !== expectedName) return;
    clearError(input.name);
    advance();
  };
  const onPreferenceChange = (): void => {
    clearError("callbackPreference");
    setScheduledVisibility();
  };
  const onPageShow = (event: PageTransitionEvent): void => {
    if (event.persisted && dialog.open) close();
  };

  triggers.forEach((trigger) => {
    trigger.setAttribute("aria-haspopup", "dialog");
    trigger.setAttribute("aria-controls", dialog.id);
    trigger.addEventListener("click", onTriggerClick);
  });
  closeButtons.forEach((button) => button.addEventListener("click", close));
  nextButtons.forEach((button) => button.addEventListener("click", advance));
  directButton?.addEventListener("click", onDirect);
  resetButton?.addEventListener("click", reset);
  backButton.addEventListener("click", onBack);
  dialog.addEventListener("cancel", onCancel);
  dialog.addEventListener("close", onDialogClose);
  form.addEventListener("submit", onFormSubmit);
  form.addEventListener("input", onFieldInput);
  autoAdvanceChoices.forEach((input) => input.addEventListener("click", onAutoAdvanceChoice));
  form.querySelectorAll<HTMLInputElement>('input[name="callbackPreference"]').forEach((input) => {
    input.addEventListener("change", onPreferenceChange);
  });
  window.addEventListener("pageshow", onPageShow);

  reset();

  return () => {
    triggers.forEach((trigger) => trigger.removeEventListener("click", onTriggerClick));
    closeButtons.forEach((button) => button.removeEventListener("click", close));
    nextButtons.forEach((button) => button.removeEventListener("click", advance));
    directButton?.removeEventListener("click", onDirect);
    resetButton?.removeEventListener("click", reset);
    backButton.removeEventListener("click", onBack);
    dialog.removeEventListener("cancel", onCancel);
    dialog.removeEventListener("close", onDialogClose);
    form.removeEventListener("submit", onFormSubmit);
    form.removeEventListener("input", onFieldInput);
    autoAdvanceChoices.forEach((input) => input.removeEventListener("click", onAutoAdvanceChoice));
    form.querySelectorAll<HTMLInputElement>('input[name="callbackPreference"]').forEach((input) => {
      input.removeEventListener("change", onPreferenceChange);
    });
    window.removeEventListener("pageshow", onPageShow);
    document.body.classList.remove("lead-capture-open");
    if (dialog.open) dialog.close();
  };
}
