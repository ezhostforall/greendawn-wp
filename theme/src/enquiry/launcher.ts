import './lead-capture.css';

export interface LeadConfig {
	formId: number;
	choices: Record<string, Record<string, string>>;
	privacyUrl: string;
	phone: string;
	phoneUrl: string;
	source: string;
}

const component = document.querySelector<HTMLElement>('[data-lead-capture]');
const transport = component?.querySelector<HTMLFormElement>('[data-lead-transport] form');
const mount = component?.querySelector<HTMLElement>('[data-lead-react]');

if (component && transport && mount && typeof HTMLDialogElement !== 'undefined') {
	try {
		const config = JSON.parse(component.dataset.config ?? '{}') as LeadConfig;
		if (!Number.isInteger(config.formId) || !config.choices?.intent) throw new Error('Invalid callback configuration');
		let application: Awaited<ReturnType<typeof initialise>> | undefined;
		let loading: Promise<Awaited<ReturnType<typeof initialise>>> | undefined;
		const lifecycle = new AbortController();
		async function initialise() {
			const { mountLeadCapture } = await import('./mount');
			return mountLeadCapture(mount!, transport!, config);
		}
		const ready = (): boolean => 'wpcf7' in transport;
		const launcher = component.querySelector<HTMLButtonElement>('.lead-capture__launcher');
		const reveal = (): void => { if (launcher && ready()) launcher.hidden = false; };
		if (document.readyState === 'complete') reveal();
		else window.addEventListener('load', reveal, { once: true, signal: lifecycle.signal });
		document.addEventListener('click', (event) => {
			const trigger = event.target instanceof Element ? event.target.closest<HTMLElement>('[data-lead-capture-open]') : null;
			if (!trigger || !ready() || event.defaultPrevented || (event instanceof MouseEvent && (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey))) return;
			event.preventDefault();
			trigger.setAttribute('aria-busy', 'true');
			loading ??= initialise();
			void loading.then((app) => {
				application = app;
				if (lifecycle.signal.aborted || !trigger.isConnected) return;
				const mobile = trigger.closest('.primary-nav');
				const toggle = document.querySelector<HTMLElement>('[data-nav-toggle]');
				if (mobile && toggle?.getAttribute('aria-expanded') === 'true') toggle.click();
				app.open(mobile && toggle ? toggle : trigger, trigger.dataset.leadEntryPoint ?? 'floating-launcher');
			}).catch(() => {
				loading = undefined;
				if (trigger instanceof HTMLAnchorElement) window.location.assign(trigger.href);
				else {
					const fallback = document.querySelector<HTMLAnchorElement>('.header-cta');
					if (fallback) window.location.assign(fallback.href);
				}
			}).finally(() => trigger.removeAttribute('aria-busy'));
		}, { signal: lifecycle.signal });
		window.addEventListener('pagehide', (event) => {
			application?.close();
			if (!event.persisted) {
				lifecycle.abort();
				application?.destroy();
			}
		}, { signal: lifecycle.signal });
	} catch {
		// Ordinary enquiry anchors remain usable if configuration cannot be read.
	}
}
