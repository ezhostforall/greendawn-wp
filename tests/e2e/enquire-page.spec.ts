import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const titles = ['Consultation', 'Hardware Quote', 'Site Survey', 'Full Quote', 'Acceptance & Installation', 'Aftercare'];
const descriptions = [
	'We start with a free consultation to understand your goals and recommend suitable chargers, grants, and aftercare options.',
	'An upfront quote covering hardware costs, giving full visibility before moving forward.',
	'A Greendawn electrical engineer will assess your site to review power, cable routes, and access, to ensure your project can be scoped and priced accurately.',
	'A final proposal of the installation, as well as any safeguarding requirements of your current electrical supply, will be provided. The quote will include electrical materials, labour, hardware, and any necessary groundwork and commissioning. Everything required for a complete installation quote.',
	'Once approved, we schedule installation and complete all works safely and to specification.',
	'Ongoing support including maintenance, updates, and troubleshooting to keep your system running smoothly.',
];
const choices = ['Workplace', 'Fleet', 'Public parking', 'Showroom', 'Gym', 'Public sector', 'Other'];

test('Enquire renders the complete dedicated form, six-step process and contacts without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/enquire/');
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('Let’s start talking');
			await expect(page.locator('main h1 em')).toHaveText('about your project.');
			await expect(page.locator('.gd-page-hero__media, main img, .gd-page-insights, .gd-process')).toHaveCount(0);
			await expect(page.locator('main')).not.toContainText('Do not append this legacy layout');
			await expect(page.locator('.gd-enquiry-process h2')).toHaveText(titles);
			await expect(page.locator('.gd-enquiry-process p')).toHaveText(descriptions);
			for (const paragraph of await page.locator('.gd-enquiry-process p').all()) await expect(paragraph).toBeVisible();
			const form = page.locator('.gd-enquire-form form');
			await expect(form).toHaveCount(1);
			await expect(form.locator('.gd-contact-fields label')).toHaveCount(8);
			await expect(form.locator('.gd-contact-fields p, .gd-contact-fields br')).toHaveCount(0);
			await expect(form.locator('[aria-required="true"]')).toHaveCount(4);
			await expect(form.locator('select option')).toHaveText(choices);
			await expect(form.locator('[name="numbercharger"]')).toHaveAttribute('min', '1');
			await expect(form.locator('[name="postcode"]')).toHaveAttribute('autocomplete', 'postal-code');
			await expect(form.getByRole('button', { name: 'Start Your Journey' })).toBeVisible();
			await expect(form.locator('[name="page-url"]')).toHaveValue('');
			await expect(form.locator('[name="chargerId"]')).toHaveValue('');
			await expect(page.locator('#enquire-contact h2')).toHaveText('Have a Question?');
			await expect(page.locator('.gd-contact-intro')).toHaveText('A member of our friendly and knowledgable team will be happy to help with any enquiries you have, EV related or otherwise!');
			await expect(page.locator('.gd-contact-grid h3')).toHaveText(['Address', 'Call Us', 'Email']);
			await expect(page.locator('.gd-contact-grid')).toContainText('Office 2.026, Innovation Centre');
			await expect(page.locator('.gd-contact-grid a').first()).toHaveAttribute('href', 'tel:03455120044');
			await expect(page.locator('.gd-contact-grid a').last()).toHaveAttribute('href', 'mailto:connect@greendawn.co.uk');
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
			await testInfo.attach(`enquire-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('Enquire preserves independent edits, unavailable form/contact states and password protection', async ({ page }) => {
	await page.goto('/gd-enquire-edited/');
	await expect(page.locator('main h1')).toHaveText('Talk to our project team.');
	await expect(page.locator('main h1 em')).toHaveCount(0);
	await expect(page.locator('.gd-page-lead')).toHaveText('An edited introduction.');
	await expect(page.locator('.gd-enquire-form h2')).toHaveText('Discuss your project');
	await expect(page.locator('.gd-enquiry-process h2').first()).toHaveText('Discuss the requirement');
	await expect(page.locator('.gd-enquiry-process li').nth(3).locator('p')).toHaveCount(0);
	for (const path of ['/gd-enquire-unassigned/', '/gd-enquire-invalid-form/', '/gd-enquire-no-cf7/']) {
		await page.goto(path);
		await expect(page.locator('.gd-enquire-form form')).toHaveCount(0);
		await expect(page.locator('.gd-enquire-form')).toContainText('The enquiry form is currently unavailable.');
		await expect(page.locator('.gd-enquire-form a')).toHaveAttribute('href', '#enquire-contact');
		await page.locator('.gd-enquire-form a').click();
		await expect(page).toHaveURL(/#enquire-contact$/);
		await expect(page.locator('.gd-contact-grid a').first()).toBeVisible();
	}
	await page.goto('/gd-enquire-shared-contact/');
	await expect(page.locator('.gd-contact-grid a').first()).toHaveAttribute('href', 'tel:03001234567');
	await expect(page.locator('.gd-contact-grid a').last()).toHaveAttribute('href', 'mailto:changed@example.test');
	await expect(page.locator('.gd-footer__phone')).toHaveText('0300 123 4567');
	await expect(page.locator('.gd-contact-grid')).toContainText('Shared Office');
	await page.goto('/gd-enquire-contacts-cleared/');
	await expect(page.locator('#enquire-contact, .gd-enquire-form a')).toHaveCount(0);
	await page.goto('/gd-enquire-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-enquire-no-core');
	await expect(page.locator('.gd-enquiry-process')).toHaveCount(0);
	await page.goto('/gd-enquire-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('main')).not.toContainText('Protected Enquire content must stay private.');
	await expect(page.locator('.gd-enquire-form, .gd-enquiry-process, .gd-contact-grid')).toHaveCount(0);
	await page.setViewportSize({ width: 320, height: 950 });
	await page.goto('/gd-enquire-long-contact/');
	await expect(page.locator('.gd-contact-grid a, .gd-contact-grid script')).toHaveCount(0);
	await expect(page.locator('.gd-contact-grid')).toContainText('Ask the team');
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(320);
});

test('Enquire retains visible and submitted charger identity through the native no-JavaScript quote journey', async ({ browser, baseURL }) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		await page.goto('/evcharger/hellonext-h1-22/');
		await page.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
		await expect(page).toHaveURL(`${baseURL}/enquire/?charger=hellonext-h1-22`);
		await expect(page.locator('.charger-enquiry-context')).toHaveCount(1);
		await expect(page.locator('.charger-enquiry-context')).toContainText('Charger fixture 1');
		await expect(page.locator('.charger-enquiry-context a')).toHaveAttribute('href', `${baseURL}/evcharger/hellonext-h1-22/`);
		const form = page.locator('.gd-enquire-form form');
		await expect(form.locator('[name="chargerId"]')).toHaveValue(/^[1-9][0-9]*$/);
		await expect(form.locator('[name="chargerTitle"]')).toHaveValue('Charger fixture 1');
		await expect(form.locator('[name="chargerURL"]')).toHaveValue(`${baseURL}/evcharger/hellonext-h1-22/`);
		for (const slug of ['not-a-published-charger', 'private-enquiry-product', 'protected-enquiry-product']) {
			await page.goto(`/enquire/?charger=${slug}`);
			await expect(page.locator('.charger-enquiry-context')).toHaveCount(0);
			for (const name of ['chargerId', 'chargerTitle', 'chargerURL']) await expect(form.locator(`[name="${name}"]`)).toHaveValue('');
		}
	} finally { await context.close(); }
});

test('Enquire shows validation and delivery responses, retains attribution and leaves the drawer independent', async ({ page, baseURL }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/enquire/?charger=hellonext-h1-22&utm_source=search&utm_medium=cpc&utm_campaign=project&utm_term=fleet&utm_content=ad-one&private=discard', { referer: 'https://referrer.example/landing/?private=discard' });
	const form = page.locator('.gd-enquire-form form');
	await form.getByRole('button', { name: 'Start Your Journey' }).click();
	await expect(form.locator('[aria-invalid="true"]').first()).toBeVisible();
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}/enquire/`);
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/enquire/`);
	await expect(form.locator('[name="first_referrer"]')).toHaveValue(/^https:\/\/referrer\.example\/(?:landing\/)?$/);
	await form.getByRole('textbox', { name: 'Full Name (required)', exact: true }).fill('Sales form test');
	await form.getByRole('textbox', { name: 'Phone Number (required)', exact: true }).fill('0121 555 0100');
	await form.getByRole('textbox', { name: 'Email Address (required)', exact: true }).fill('sales@example.test');
	await form.getByRole('combobox', { name: 'Project Type (required)', exact: true }).selectOption('Fleet');
	let status = 'mail_failed';
	await page.route(url => url.pathname.includes('/contact-forms/') && url.pathname.endsWith('/feedback'), async route => {
		const payload = route.request().postDataBuffer()?.toString() ?? '';
		for (const value of ['Sales form test', 'Charger fixture 1', `${baseURL}/evcharger/hellonext-h1-22/`, 'search', 'cpc', 'project', 'fleet', 'ad-one']) expect(payload).toContain(value);
		expect(payload).not.toContain('private=discard');
		await route.fulfill({ json: { status, message: status === 'mail_sent' ? 'Project enquiry received.' : 'Delivery failed. Please try again.', invalid_fields: [] } });
	});
	await form.getByRole('button', { name: 'Start Your Journey' }).click();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Delivery failed. Please try again.');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	status = 'mail_sent';
	await form.getByRole('button', { name: 'Start Your Journey' }).click();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Project enquiry received.');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await expect(form.locator('[name="utm_campaign"]')).toHaveValue('project');
	await expect(page.locator('script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
});
