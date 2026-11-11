import { expect, test } from '@playwright/test';
import { mockComplianz } from './helpers/complianz';

test('Marketing permission controls stored attribution and clears native form fields on withdrawal', async ({ page }) => {
	await mockComplianz(page);
	await page.goto('/gd-about-default/?utm_source=test-campaign');
	const utm = page.locator('.gd-form-panel [name="utm_source"]');
	const stored = () => page.evaluate(() => sessionStorage.getItem('greendawn:enquiry-visit:v2'));
	await expect(utm).toHaveValue('');
	expect(await stored()).toBeNull();
	await page.evaluate(() => document.dispatchEvent(new CustomEvent('test:consent', { detail: { marketing: true } })));
	await expect(utm).toHaveValue('test-campaign');
	expect(await stored()).toContain('test-campaign');
	await page.evaluate(() => document.dispatchEvent(new CustomEvent('test:consent', { detail: { marketing: false } })));
	await expect(utm).toHaveValue('');
	expect(await stored()).toBeNull();
});

test('only confirmed inline placements report once, using consent at submission and confirmation', async ({ page }) => {
	await mockComplianz(page);
	await page.goto('/enquire/');
	const form = page.locator('.gd-enquire-form .wpcf7 form');
	const events = () => page.evaluate(() => window.dataLayer?.filter(entry => entry.event === 'generate_lead'));
	const start = () => form.evaluate(node => {
		const placement = node.closest<HTMLElement>('[data-greendawn-form-id]');
		const detail = { contactFormId: placement?.dataset.greendawnFormId };
		const wrapper = node.closest('.wpcf7');
		wrapper?.addEventListener('test:finish', event => {
			const name = (event as CustomEvent<string>).detail;
			for (let i = 0; i < 2; i++) wrapper.dispatchEvent(new CustomEvent(name, { bubbles: true, detail }));
			wrapper.dispatchEvent(new CustomEvent('wpcf7submit', { bubbles: true, detail }));
		}, { once: true });
		wrapper?.dispatchEvent(new CustomEvent('wpcf7beforesubmit', { bubbles: true, detail }));
	});
	const finish = (eventName: string) => form.evaluate((node, name) => {
		node.closest('.wpcf7')?.dispatchEvent(new CustomEvent('test:finish', { detail: name }));
	}, eventName);
	await start();
	await page.evaluate(() => document.dispatchEvent(new CustomEvent('test:consent', { detail: { statistics: true } })));
	await finish('wpcf7mailsent');
	expect(await events()).toEqual([]);
	await start();
	await finish('wpcf7mailfailed');
	expect(await events()).toEqual([]);
	await start();
	await finish('wpcf7mailsent');
	expect(await events()).toEqual([{
		event: 'generate_lead', form_location: 'enquire', lead_type: 'sales_enquiry',
		cta_location: null, charger_slug: null, gd_event_statistics: true, gd_event_marketing: false,
	}]);
	await start();
	await page.evaluate(() => document.dispatchEvent(new CustomEvent('test:consent', { detail: { statistics: false } })));
	await finish('wpcf7mailsent');
	expect(await events()).toHaveLength(1);
	await page.goto('/customer-care/');
	await page.evaluate(() => {
		document.dispatchEvent(new CustomEvent('test:consent', { detail: { statistics: true, marketing: true } }));
	});
	const support = page.locator('.gd-support-form .wpcf7 form');
	await support.evaluate(node => {
		const placement = node.closest<HTMLElement>('[data-greendawn-form-id]');
		const detail = { contactFormId: placement?.dataset.greendawnFormId };
		const wrapper = node.closest('.wpcf7');
		for (const name of ['wpcf7beforesubmit', 'wpcf7mailsent', 'wpcf7submit']) {
			wrapper?.dispatchEvent(new CustomEvent(name, { bubbles: true, detail }));
		}
	});
	expect(await page.evaluate(() => window.dataLayer?.filter(entry => entry.event === 'support_request'))).toEqual([{
		event: 'support_request', form_location: 'customer_care', lead_type: 'support',
		cta_location: null, charger_slug: null, gd_event_statistics: true, gd_event_marketing: false,
	}]);
	expect(await events()).toEqual([]);
});
