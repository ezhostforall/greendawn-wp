type Category = 'statistics' | 'marketing';
export type ConsentSnapshot = Record<Category, number | null>;
type DataLayer = Record<string, unknown>[];
type UetConsent = { ad_storage: 'granted' | 'denied' };
type UetConsentArgument = 'consent' | 'default' | 'update' | UetConsent;

declare global {
	interface Window {
		cmplz_get_cookie?: (category: Category) => string;
		cmplz_has_consent?: (category: Category) => boolean;
		wp_consent_type?: string;
		dataLayer?: DataLayer;
		uetq?: { push: (command: 'consent', mode: 'default' | 'update', state: UetConsent) => unknown };
		greendawn_tracking_ready?: (revision: number) => void;
	}
}

let consent: ConsentSnapshot = { statistics: null, marketing: null };
let revision = 0;
let published = -1;
let ready = -1;
let started = false;
let loaded = false;
let scheduled = false;
let resolved = false;

const config = document.querySelector<HTMLMetaElement>('meta[name="greendawn-tracking"]');
const container = config?.dataset.host === location.hostname ? config.content : '';
const microsoft = Boolean(container && config?.dataset.production === '1');
let pending: { event: Record<string, unknown>; consent: ConsentSnapshot }[] = [];

function dataLayer(): DataLayer {
	return window.dataLayer ??= [];
}

function granted(category: Category): boolean {
	try {
		// Complianz can return an implicit grant in some modes. Require a saved, explicit UK opt-in.
		return window.wp_consent_type === 'optin' && window.cmplz_get_cookie?.(category) === 'allow'
			&& window.cmplz_has_consent?.(category) === true;
	} catch {
		return false;
	}
}

export function getTrackingConsent(): ConsentSnapshot {
	return consent;
}

function deliver(event: Record<string, unknown>, submitted: ConsentSnapshot): void {
	const statistics = consent.statistics !== null && consent.statistics === submitted.statistics;
	const marketing = consent.marketing !== null && consent.marketing === submitted.marketing;
	if (statistics || marketing) {
		try {
			dataLayer().push({ ...event, gd_event_statistics: statistics, gd_event_marketing: marketing });
		} catch {
			// A measurement error must never turn confirmed form delivery into a failure.
		}
	}
}

export function pushTrackingEvent(event: Record<string, unknown>, submitted: ConsentSnapshot): void {
	if (submitted.statistics === null && submitted.marketing === null) return;
	if (container && ready !== revision) {
		// Keep early confirmed enquiries in memory while GTM initialises. Never replay a denied category.
		if (pending.length < 10) pending.push({ event, consent: submitted });
		return;
	}
	deliver(event, submitted);
}

function publishConsent(): void {
	if (published === revision || (container && !loaded)) return;
	published = revision;
	if (microsoft) {
		window.uetq ??= [] as UetConsentArgument[];
		window.uetq.push('consent', 'update', { ad_storage: consent.marketing === null ? 'denied' : 'granted' });
	}
	dataLayer().push({
		event: 'greendawn_consent_state',
		gd_consent_revision: revision,
		gd_statistics: consent.statistics !== null,
		gd_marketing: consent.marketing !== null,
	});
}

function startContainer(): void {
	if (!container || started) return;
	started = true;
	if (microsoft) {
		window.uetq ??= [] as UetConsentArgument[];
		window.uetq.push('consent', 'default', { ad_storage: 'denied' });
	}
	const script = document.createElement('script');
	script.async = true;
	script.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(container);
	script.addEventListener('load', () => {
		loaded = true;
		sync();
	}, { once: true });
	dataLayer().push({ 'gtm.start': Date.now(), event: 'gtm.js' });
	document.head.append(script);
}

function sync(): void {
	const statistics = granted('statistics');
	const marketing = granted('marketing');
	const available = typeof window.cmplz_get_cookie === 'function' && typeof window.cmplz_has_consent === 'function'
		&& typeof window.wp_consent_type === 'string';
	if ((!resolved && available) || statistics !== (consent.statistics !== null) || marketing !== (consent.marketing !== null)) {
		resolved ||= available;
		revision++;
		consent = {
			statistics: statistics ? consent.statistics ?? revision : null,
			marketing: marketing ? consent.marketing ?? revision : null,
		};
		pending = pending.filter(({ consent: snapshot }) =>
			(consent.statistics !== null && consent.statistics === snapshot.statistics)
			|| (consent.marketing !== null && consent.marketing === snapshot.marketing));
		// Update form fields and attribution before notifying the container.
		document.dispatchEvent(new Event('greendawn_consent_changed'));
	}
	if (statistics || marketing) startContainer();
	publishConsent();
}

function scheduleSync(): void {
	if (scheduled) return;
	scheduled = true;
	queueMicrotask(() => {
		scheduled = false;
		sync();
	});
}

export function initTrackingConsent(): void {
	window.greendawn_tracking_ready = (appliedRevision) => {
		if (appliedRevision !== revision) return;
		ready = appliedRevision;
		for (const entry of pending.splice(0)) deliver(entry.event, entry.consent);
	};
	for (const event of ['cmplz_fire_categories', 'cmplz_status_change', 'cmplz_cookie_warning_loaded', 'cmplz_revoke', 'visibilitychange']) {
		document.addEventListener(event, scheduleSync);
	}
	window.addEventListener('pageshow', scheduleSync);
	sync();
}
