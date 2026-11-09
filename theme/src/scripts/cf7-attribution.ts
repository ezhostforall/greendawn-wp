const storageKey = 'greendawn:enquiry-visit:v1';
type Visit = { landing: string; referrer: string };

// Attribution URLs contain origin/path only, never query strings or fragments.
function cleanURL(value: string): string {
	try {
		const url = new URL(value);
		return /^https?:$/.test(url.protocol) ? `${url.origin}${url.pathname}`.slice(0, 2048) : '';
	} catch { return ''; }
}

export function initCF7Attribution(): void {
	let visit: Visit = { landing: cleanURL(window.location.href), referrer: cleanURL(document.referrer) };
	try {
		const stored: unknown = JSON.parse(window.sessionStorage.getItem(storageKey) ?? 'null');
		if (stored && typeof stored === 'object' && 'landing' in stored && 'referrer' in stored && typeof stored.landing === 'string' && typeof stored.referrer === 'string') {
			const landing = cleanURL(stored.landing);
			if (landing && new URL(landing).origin === window.location.origin) visit = { landing, referrer: cleanURL(stored.referrer) };
		}
		window.sessionStorage.setItem(storageKey, JSON.stringify(visit));
	} catch { /* Storage denial must never prevent form use; this page's values remain available. */ }

	const fill = (form: HTMLFormElement): void => {
		if (!form.matches('.wpcf7 form')) return;
		for (const [name, value] of Object.entries({ 'page-url': cleanURL(window.location.href), first_referrer: visit.referrer, first_landing: visit.landing })) {
			for (const input of form.querySelectorAll<HTMLInputElement>(`input[type="hidden"][name="${name}"]`)) input.value = value;
		}
	};
	const fillAll = (): void => document.querySelectorAll<HTMLFormElement>('.wpcf7 form').forEach(fill);
	fillAll();
	// Capture before CF7 serialises fields; formdata also covers newly inserted forms.
	document.addEventListener('submit', event => { if (event.target instanceof HTMLFormElement) fill(event.target); }, true);
	document.addEventListener('formdata', event => {
		if (!(event.target instanceof HTMLFormElement) || !(event instanceof FormDataEvent)) return;
		fill(event.target);
		if (!event.target.matches('.wpcf7 form')) return;
		for (const name of ['page-url', 'first_referrer', 'first_landing']) {
			const input = event.target.querySelector<HTMLInputElement>(`input[type="hidden"][name="${name}"]`);
			if (input) event.formData.set(name, input.value);
		}
	});
	// CF7 resets inputs after success. Refill after the native reset has completed.
	document.addEventListener('reset', event => { if (event.target instanceof HTMLFormElement) { const form = event.target; queueMicrotask(() => fill(form)); } }, true);
	window.addEventListener('pageshow', fillAll);
}
