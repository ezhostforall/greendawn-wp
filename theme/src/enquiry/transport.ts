export interface Feedback {
	status: string;
	message?: string;
	invalid_fields?: { field: string; message: string }[];
}

/** Submit the actual CF7 form so its integrations, tokens and spam checks still run. */
export function submitCallback(form: HTMLFormElement, values: Record<string, string>): Promise<Feedback> {
	for (const [name, value] of Object.entries(values)) {
		const field = form.elements.namedItem(name);
		if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement) field.value = value;
	}
	return new Promise((resolve, reject) => {
		const container = form.closest('.wpcf7');
		const finish = (event: Event): void => {
			const detail = (event as CustomEvent<{ apiResponse?: Feedback }>).detail;
			if (!detail?.apiResponse) return;
			cleanup();
			resolve(detail.apiResponse);
		};
		const cleanup = (): void => {
			window.clearTimeout(timer);
			container?.removeEventListener('wpcf7submit', finish);
		};
		const timer = window.setTimeout(() => {
			cleanup();
			reject(new Error('We could not confirm delivery. Your request may have reached us. Please call us before sending it again.'));
		}, 40_000);
		container?.addEventListener('wpcf7submit', finish);
		try { form.requestSubmit(); } catch { cleanup(); reject(new Error('The request could not be sent. Please try again or call us.')); }
	});
}
