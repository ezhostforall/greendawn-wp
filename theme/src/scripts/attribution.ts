const storageKey = 'greendawn:enquiry-visit:v2';
const campaigns = { utmSource: 'utm_source', utmMedium: 'utm_medium', utmCampaign: 'utm_campaign', utmTerm: 'utm_term', utmContent: 'utm_content' } as const;
type Campaign = Record<keyof typeof campaigns, string>;
type Visit = Campaign & { landing: string; referrer: string };
let visit: Visit | undefined;

// Attribution URLs contain origin/path only, never query strings or fragments.
function cleanURL(value: string): string {
	try {
		const url = new URL(value);
		return /^https?:$/.test(url.protocol) ? `${url.origin}${url.pathname}`.slice(0, 2048) : '';
	} catch { return ''; }
}

/** First-touch attribution shared by native forms and the lazy React drawer. */
export function getVisitAttribution(): Record<string, string> {
	if (!visit) {
		const params = new URLSearchParams(window.location.search);
		const initial = { landing: cleanURL(window.location.href), referrer: cleanURL(document.referrer) } as Visit;
		for (const [name, query] of Object.entries(campaigns)) initial[name as keyof Campaign] = params.get(query)?.slice(0, 120) ?? '';
		visit = initial;
		try {
			const stored: unknown = JSON.parse(window.sessionStorage.getItem(storageKey) ?? 'null');
			if (stored && typeof stored === 'object' && 'landing' in stored && 'referrer' in stored && typeof stored.landing === 'string' && typeof stored.referrer === 'string') {
				const landing = cleanURL(stored.landing);
				if (landing && new URL(landing).origin === window.location.origin) {
					visit = { ...initial, landing, referrer: cleanURL(stored.referrer) };
					for (const name of Object.keys(campaigns) as (keyof Campaign)[]) {
						const value = (stored as Record<string, unknown>)[name];
						visit[name] = typeof value === 'string' ? value.slice(0, 120) : '';
					}
				}
			}
			window.sessionStorage.setItem(storageKey, JSON.stringify(visit));
		} catch { /* Storage denial must never prevent enquiries; current-page values remain available. */ }
	}
	return { 'page-url': cleanURL(window.location.href), first_landing: visit.landing, first_referrer: visit.referrer, ...Object.fromEntries(Object.keys(campaigns).map(name => [name, visit?.[name as keyof Campaign] ?? ''])) };
}

/** Native contact forms retain their existing underscore-style UTM mail tags. */
export function getCF7Attribution(): Record<string, string> {
	const values = getVisitAttribution();
	return { ...values, ...Object.fromEntries(Object.entries(campaigns).map(([name, query]) => [query, values[name] ?? ''])) };
}
