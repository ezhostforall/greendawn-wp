import { getCF7Attribution } from './attribution';

export function initCF7Attribution(): void {
	// Capture the entry page even when this page contains no contact form.
	getCF7Attribution();
	const fill = (form: HTMLFormElement): void => {
		if (!form.matches('.wpcf7 form')) return;
		for (const [name, value] of Object.entries(getCF7Attribution())) {
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
		for (const name of Object.keys(getCF7Attribution())) {
			const input = event.target.querySelector<HTMLInputElement>(`input[type="hidden"][name="${name}"]`);
			if (input) event.formData.set(name, input.value);
		}
	});
	// CF7 resets inputs after success. Refill after the native reset has completed.
	document.addEventListener('reset', event => { if (event.target instanceof HTMLFormElement) { const form = event.target; queueMicrotask(() => fill(form)); } }, true);
	window.addEventListener('pageshow', fillAll);
}
