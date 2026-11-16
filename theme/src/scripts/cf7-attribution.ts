import { clearAdvertisingAttribution, getAdvertisingAttribution } from './advertising-attribution';
import { clearVisitAttribution, getCF7Attribution } from './attribution';
import { getTrackingConsent } from './tracking-consent';

function fieldsFor(form: HTMLFormElement): Record<string, string> {
	const existing = getCF7Attribution();
	const placement = form.closest<HTMLElement>('[data-greendawn-form-location]');
	// The drawer fills its own native transport, including its support-intent policy.
	if (form.closest('[data-lead-transport]')) {
		return getTrackingConsent().marketing === null ? { ...existing, ...getAdvertisingAttribution() } : existing;
	}
	const location = placement?.dataset.greendawnFormLocation;
	if (!location || !['contact', 'about', 'workplace', 'enquire', 'business_installations'].includes(location)) return existing;
	return { ...existing, ...getAdvertisingAttribution() };
}

export function initCF7Attribution(): void {
	const fill = (form: HTMLFormElement): void => {
		if (!form.matches('.wpcf7 form')) return;
		for (const [name, value] of Object.entries(fieldsFor(form))) {
			for (const input of form.querySelectorAll<HTMLInputElement>(`input[type="hidden"][name="${name}"]`)) input.value = value;
		}
	};
	const fillAll = (): void => document.querySelectorAll<HTMLFormElement>('.wpcf7 form').forEach(fill);
	const refresh = (): void => {
		if (getTrackingConsent().marketing === null) {
			clearVisitAttribution();
			clearAdvertisingAttribution();
		}
		// Capture a permitted entry even on pages without a contact form.
		getCF7Attribution();
		getAdvertisingAttribution();
		fillAll();
	};
	document.addEventListener('greendawn_consent_changed', refresh);
	getCF7Attribution();
	getAdvertisingAttribution();
	fillAll();
	// Capture before CF7 serialises fields; formdata also covers newly inserted forms.
	document.addEventListener('submit', event => { if (event.target instanceof HTMLFormElement) fill(event.target); }, true);
	document.addEventListener('formdata', event => {
		if (!(event.target instanceof HTMLFormElement) || !(event instanceof FormDataEvent)) return;
		fill(event.target);
		if (!event.target.matches('.wpcf7 form')) return;
		for (const name of Object.keys(fieldsFor(event.target))) {
			const input = event.target.querySelector<HTMLInputElement>(`input[type="hidden"][name="${name}"]`);
			if (input) event.formData.set(name, input.value);
		}
	});
	// CF7 resets inputs after success. Refill after the native reset has completed.
	document.addEventListener('reset', event => { if (event.target instanceof HTMLFormElement) { const form = event.target; queueMicrotask(() => fill(form)); } }, true);
	window.addEventListener('pageshow', fillAll);
}
