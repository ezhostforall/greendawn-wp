import { getTrackingConsent } from './tracking-consent';

const storageKey = 'greendawn:advertising-visit:v1';
const identifiers = [
	'gclid', 'gbraid', 'wbraid', 'gad_source', 'gad_campaignid', 'gclsrc', 'dclid',
	'msclkid', 'fbclid', 'ttclid', 'li_fat_id', 'twclid', 'sccid', 'epik', 'rdt_cid',
] as const;
const campaigns = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as const;
const empty = Object.fromEntries([
	...identifiers.map(name => [name, '']),
	...campaigns.map(name => [`ad_${name}`, '']),
]);

let snapshot: Record<string, string> | undefined;
let restored = false;

export function clearAdvertisingAttribution(): void {
	snapshot = undefined;
	restored = false;
	try { window.sessionStorage.removeItem(storageKey); } catch { /* Storage is optional. */ }
}

function identifier(value: unknown): string {
	return typeof value === 'string' && /^[A-Za-z0-9._~+/=-]{1,1024}$/.test(value) ? value : '';
}

function campaign(value: unknown): string {
	return typeof value === 'string' && value.length <= 240 && [...value].length <= 120 && !/\p{Cc}/u.test(value) ? value : '';
}

function singleIdentifier(values: string[]): string {
	const first = identifier(values[0]);
	return first && values.every(value => value === first) ? first : '';
}

function restore(): void {
	if (restored) return;
	restored = true;
	try {
		const stored: unknown = JSON.parse(window.sessionStorage.getItem(storageKey) ?? 'null');
		if (!stored || typeof stored !== 'object') return;
		const fields = stored as Record<string, unknown>;
		const recovered = Object.fromEntries(identifiers.map(name => [name, identifier(fields[name])]));
		if (!Object.values(recovered).some(Boolean)) return;
		for (const name of campaigns) recovered[`ad_${name}`] = campaign(fields[`ad_${name}`]);
		snapshot = recovered;
	} catch { /* Malformed or blocked storage does not affect the enquiry. */ }
}

/** Retain one consented, tagged landing per tab, separately from the original visit. */
export function getAdvertisingAttribution(support = false): Readonly<Record<string, string>> {
	if (support || getTrackingConsent().marketing === null) return empty;

	restore();
	if (snapshot) return snapshot;

	// An internal link carrying query parameters is not a new ad landing.
	if (document.referrer && new URL(document.referrer).origin === window.location.origin) return empty;
	const params = new URLSearchParams(window.location.search);
	const fields = Object.fromEntries(identifiers.map(name => [
		name, singleIdentifier(name === 'sccid' ? [...params.getAll('ScCid'), ...params.getAll(name)] : params.getAll(name)),
	]));
	if (!Object.values(fields).some(Boolean)) return empty;

	for (const name of campaigns) fields[`ad_${name}`] = campaign(params.get(name));
	snapshot = fields;
	try { window.sessionStorage.setItem(storageKey, JSON.stringify(fields)); } catch { /* Keep current-page values. */ }
	return snapshot;
}
