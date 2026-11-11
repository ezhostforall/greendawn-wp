import { expect, test } from '@playwright/test';
import { mockComplianz } from './helpers/complianz';
import AxeBuilder from '@axe-core/playwright';

const route = '/customer-care/';
const intro = 'At Greendawn, our commitment to you doesn’t stop once your chargers are installed. We’re here to make sure everything runs smoothly long after the first plug-in. We provide:';
const points = [
	'Fast response times for troubleshooting and repairs',
	'Remote diagnostics to solve issues quickly',
	'Clear communication so you always know what’s happening',
	'Ongoing care to keep your charging infrastructure working at its best',
];
const issueTypes = ['Issue Type', 'Charger Issue', 'Backoffice Help', 'Recommendation', 'Other Issue'];

test('Customer Care keeps complete source support content and its dedicated form without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	const requests: string[] = [];
	page.on('request', request => { if (/wa\.me|whatsapp|youtube|ytimg/.test(new URL(request.url()).hostname)) requests.push(request.url()); });
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto(route);
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('Customer');
			await expect(page.locator('main h1 em')).toHaveText('Support');
			await expect(page.locator('.gd-page-lead')).toHaveText(intro);
			await expect(page.locator('.gd-support-points li p')).toHaveText(points);
			await expect(page.locator('.gd-support-contact h2')).toHaveText('Get In Touch');
			await expect(page.locator('.gd-support-whatsapp h3')).toHaveText('Contact Us On Whatsapp!');
			await expect(page.locator('.gd-support-whatsapp a')).toHaveText('Open Whatsapp');
			await expect(page.locator('.gd-support-whatsapp a')).toHaveAttribute('href', 'https://wa.me/443455120044');
			await expect(page.locator('.gd-support-form h3')).toHaveText('Call Back Request Form');
			const form = page.locator('.gd-support-form form');
			await expect(form.locator('.gd-contact-fields label')).toHaveCount(5);
			await expect(form.locator('[aria-required="true"]')).toHaveCount(5);
			await expect(form.locator('[name="issue-type"] option')).toHaveText(issueTypes);
			await expect(form.locator('[name="issue-summary"]')).toHaveAttribute('rows', '5');
			await expect(form.getByRole('button', { name: 'Submit', exact: true })).toBeVisible();
			await expect(form.locator('.gd-contact-fields p, .gd-contact-fields br, [name="projecttype"], [name="chargerId"]')).toHaveCount(0);
			await expect(page.locator('.gd-support-contact [data-gd-enquiry-open], main .gd-cta-band, main .gd-page-insights, main .gd-process, main img, main iframe')).toHaveCount(0);
			await expect(page.locator('main')).not.toContainText('Do not append this legacy support layout');
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
			await testInfo.attach(`customer-care-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
		expect(requests).toEqual([]);
	} finally { await context.close(); }
});

test('Customer Care preserves edits and clears while unavailable forms keep support routing', async ({ page }) => {
	await page.goto('/gd-care-edited/');
	await expect(page.locator('main h1')).toHaveText('Support for our customers.');
	await expect(page.locator('main h1 em')).toHaveCount(0);
	await expect(page.locator('.gd-page-lead')).toHaveText('An edited support introduction.');
	await expect(page.locator('.gd-support-points li p').first()).toHaveText('Reviewed customer support Edited continuation <strong>rendered as text</strong>.');
	await expect(page.locator('.gd-support-points li p strong strong')).toHaveCount(0);
	await expect(page.locator('.gd-support-contact h2')).toHaveText('Contact support');
	await expect(page.locator('.gd-support-form h3')).toHaveText('Ask our support team');
	await expect(page.locator('.gd-support-whatsapp a')).toHaveText('Message support');
	await expect(page.locator('.gd-support-whatsapp a')).toHaveAttribute('href', 'https://wa.me/447700900123?text=Support');
	await expect(page.locator('.gd-support-form form')).toHaveCount(1);
	for (const path of ['/gd-care-unassigned/', '/gd-care-invalid-form/', '/gd-care-draft-form/', '/gd-care-no-cf7/']) {
		await page.goto(path);
		await expect(page.locator('.gd-support-form form')).toHaveCount(0);
		await expect(page.locator('.gd-support-form')).toContainText('The support form is currently unavailable.');
		await expect(page.locator('.gd-support-form a')).toHaveAttribute('href', 'https://wa.me/443455120044');
		await expect(page.locator('.gd-support-form a')).toHaveText('Open Whatsapp');
		await expect(page.locator('main a[href*="/enquire"], main [data-gd-enquiry-open]')).toHaveCount(0);
	}
	await page.goto('/gd-care-cleared/');
	await expect(page.locator('.gd-page-lead, .gd-support-points, .gd-support-contact h2, .gd-support-contact h3, .gd-support-contact a')).toHaveCount(0);
	await expect(page.locator('.gd-support-form')).toContainText('The support form is currently unavailable.');
	await page.goto('/gd-care-invalid-whatsapp/');
	await expect(page.locator('.gd-support-contact a')).toHaveCount(0);
	await expect(page.locator('main')).not.toContainText('untrusted.example');
	await page.goto('/gd-care-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-care-no-core');
	await expect(page.locator('.gd-support-points, .gd-support-contact a')).toHaveCount(0);
	await page.goto('/gd-care-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('main')).not.toContainText('Protected support content must stay private.');
	await expect(page.locator('.gd-support-contact, .gd-support-points')).toHaveCount(0);
	await page.goto('/enquire/');
	await expect(page.locator('.gd-enquire-form .gd-contact-fields label')).toHaveCount(8);
	await expect(page.locator('.gd-enquire-form [name="issue-type"]')).toHaveCount(0);
	await page.goto('/business-ev-charging-installations/');
	await expect(page.locator('.gd-business-form form .gd-contact-fields label')).toHaveCount(8);
	await expect(page.locator('.gd-business-form [name="issue-type"]')).toHaveCount(0);
});

test('Customer Care validates required support fields and remains accessible with reduced motion', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto(route);
	const scan = async (): Promise<void> => {
		const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
		expect(results.violations).toEqual([]);
	};
	await scan();
	const whatsapp = page.locator('.gd-support-whatsapp a');
	await whatsapp.focus();
	await expect(whatsapp).toBeFocused();
	const form = page.locator('.gd-support-form form');
	await form.getByRole('button', { name: 'Submit', exact: true }).click();
	await expect(form.locator('[aria-invalid="true"]')).toHaveCount(5);
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await scan();
	await form.getByRole('textbox', { name: 'Name (required)', exact: true }).fill('Support test');
	await form.getByRole('textbox', { name: 'Email Address (required)', exact: true }).fill('not-an-email');
	await form.getByRole('textbox', { name: 'Phone Number (required)', exact: true }).fill('0121 555 0100');
	await form.locator('[name="issue-type"]').selectOption('Backoffice Help');
	await form.getByRole('textbox', { name: 'Issue Summary (required)', exact: true }).fill('Test support issue.');
	await form.getByRole('button', { name: 'Submit', exact: true }).click();
	await expect(form.locator('[name="your-email"]')).toHaveAttribute('aria-invalid', 'true');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(375);
	await expect(page.locator('script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
});

test('Customer Care retains native success/error feedback and attribution without sending live mail', async ({ page, baseURL }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await mockComplianz(page, { marketing: true });
	await page.goto('/about/?utm_source=support&utm_medium=referral&utm_campaign=care', { referer: 'https://referrer.example/path/?private=discard' });
	await page.goto(route);
	const form = page.locator('.gd-support-form form');
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}${route}`);
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/about/`);
	await expect(form.locator('[name="first_referrer"]')).toHaveValue('https://referrer.example/path/');
	await expect(form.locator('[name="utm_campaign"]')).toHaveValue('care');
	await form.getByRole('textbox', { name: 'Name (required)', exact: true }).fill('Customer support test');
	await form.getByRole('textbox', { name: 'Email Address (required)', exact: true }).fill('support@example.test');
	await form.getByRole('textbox', { name: 'Phone Number (required)', exact: true }).fill('0121 555 0100');
	await form.locator('[name="issue-type"]').selectOption('Charger Issue');
	await form.getByRole('textbox', { name: 'Issue Summary (required)', exact: true }).fill('Support integration check only.');
	let status = 'mail_failed';
	await page.route(url => url.pathname.includes('/contact-forms/') && url.pathname.endsWith('/feedback'), async intercept => {
		const payload = intercept.request().postDataBuffer()?.toString() ?? '';
		for (const value of ['Customer support test', 'Charger Issue', 'Support integration check only.', `${baseURL}${route}`, `${baseURL}/about/`, 'support', 'referral', 'care']) expect(payload).toContain(value);
		expect(payload).not.toContain('private=discard');
		expect(payload).not.toContain('name="chargerId"');
		await intercept.fulfill({ json: { status, message: status === 'mail_sent' ? 'Your support request has been received.' : 'Support delivery failed. Please try again.', invalid_fields: [] } });
	});
	await form.getByRole('button', { name: 'Submit', exact: true }).click();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Support delivery failed. Please try again.');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	status = 'mail_sent';
	await form.getByRole('button', { name: 'Submit', exact: true }).click();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Your support request has been received.');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await expect(form.locator('[name="your-name"]')).toHaveValue('');
	await expect(form.locator('[name="issue-type"]')).toHaveValue('');
	await expect(form.locator('[name="utm_campaign"]')).toHaveValue('care');
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}${route}`);
});
