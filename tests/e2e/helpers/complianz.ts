import type { Page } from '@playwright/test';

export async function mockComplianz(page: Page, initial: { statistics?: boolean; marketing?: boolean } = {}): Promise<void> {
	await page.addInitScript((choices) => {
		window.wp_consent_type = 'optin';
		window.cmplz_get_cookie = category => choices[category] ? 'allow' : 'deny';
		window.cmplz_has_consent = category => choices[category] === true;
		document.addEventListener('test:consent', event => {
			Object.assign(choices, (event as CustomEvent).detail);
			document.dispatchEvent(new Event('cmplz_fire_categories'));
		});
	}, initial);
}
