import { getTrackingConsent, pushTrackingEvent, type ConsentSnapshot } from './tracking-consent';

const inlineLocations = ['contact', 'about', 'workplace', 'enquire', 'business_installations', 'customer_care'] as const;
type FormLocation = typeof inlineLocations[number] | 'enquiry_drawer';
type LeadType = 'sales_enquiry' | 'charger_quote' | 'support';
export const drawerEntryPoints = ['floating-launcher', 'header', 'hero', 'services', 'pricing', 'final-cta', 'footer-cta', 'charger-quote'] as const;
export type DrawerEntryPoint = typeof drawerEntryPoints[number];

export function reportConfirmedForm(
	location: FormLocation,
	submitted: ConsentSnapshot,
	type: LeadType,
	entry?: DrawerEntryPoint,
	slug?: string,
): void {
	pushTrackingEvent({
		event: type === 'support' ? 'support_request' : 'generate_lead',
		form_location: location,
		lead_type: type,
		cta_location: entry ?? null,
		charger_slug: type === 'charger_quote' && slug && /^[a-z0-9-]{1,200}$/.test(slug) ? slug : null,
	}, type === 'support' ? { ...submitted, marketing: null } : submitted);
}

export function initCF7Tracking(): void {
	// CF7 carries the same detail object from beforesubmit to mailsent. A second
	// click that CF7 refuses cannot replace the original submission's consent.
	const submissions = new WeakMap<object, {
		consent: ConsentSnapshot;
		location: typeof inlineLocations[number];
		slug?: string;
	}>();

	document.addEventListener('wpcf7beforesubmit', event => {
		if (!(event.target instanceof Element) || !(event instanceof CustomEvent)
			|| !event.detail || typeof event.detail !== 'object' || event.target.closest('[data-lead-transport]')) return;
		const placement = event.target.closest('.wpcf7')?.closest<HTMLElement>('[data-greendawn-form-location]');
		const location = inlineLocations.find(value => value === placement?.dataset.greendawnFormLocation);
		if (!location || !placement?.dataset.greendawnFormId
			|| placement.dataset.greendawnFormId !== String(event.detail.contactFormId)
			|| submissions.has(event.detail)) return;
		submissions.set(event.detail, {
			consent: getTrackingConsent(), location, slug: placement.dataset.greendawnChargerSlug,
		});
	});

	document.addEventListener('wpcf7mailsent', event => {
		if (!(event instanceof CustomEvent) || !event.detail || typeof event.detail !== 'object') return;
		const submission = submissions.get(event.detail);
		if (!submission) return;
		submissions.delete(event.detail);
		const type = submission.location === 'customer_care' ? 'support'
			: submission.slug ? 'charger_quote' : 'sales_enquiry';
		reportConfirmedForm(submission.location, submission.consent, type, undefined, submission.slug);
	});

	document.addEventListener('wpcf7submit', event => {
		if (event instanceof CustomEvent && event.detail && typeof event.detail === 'object') submissions.delete(event.detail);
	});
}
