import { expect, test, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { mockComplianz } from './helpers/complianz';

async function direct(page: Page, path = '/callback-home/') {
	await page.goto(path);
	await page.locator('.lead-capture__launcher').click();
	const dialog = page.getByRole('dialog', { name: 'Request a callback' });
	await expect(dialog).toBeVisible();
	await dialog.getByRole('button', { name: 'Just request a callback' }).click();
	return dialog;
}

async function fillContact(page: Page, email = 'visitor@example.test') {
	const dialog = page.getByRole('dialog');
	await dialog.getByRole('textbox', { name: 'First name', exact: true }).fill('Test visitor');
	await dialog.getByRole('textbox', { name: 'Telephone number' }).fill('0121 555 0100');
	await dialog.getByRole('textbox', { name: 'Company name' }).fill('Callback Test Company');
	await dialog.getByRole('textbox', { name: 'Email', exact: true }).fill(email);
	await dialog.getByRole('radio', { name: 'Tomorrow', exact: true }).check();
}

test.beforeEach(async ({ request }) => { await request.post('/wp-json/greendawn-test/v1/callback/reset'); });

test('qualified React flow uses native CF7 delivery and emits no personal data in funnel events', async ({ page, request }, testInfo) => {
	await mockComplianz(page, { marketing: true });
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.addInitScript(() => {
		(window as unknown as { funnel: unknown[] }).funnel = [];
		document.addEventListener('greendawn:lead-funnel', (event) => (window as unknown as { funnel: unknown[] }).funnel.push((event as CustomEvent).detail));
	});
	const loaded: string[] = [];
	page.on('request', (request) => loaded.push(request.url()));
	await page.goto('/about/?utm_source=qa&utm_medium=cpc&utm_campaign=callback&utm_term=fleet&utm_content=ad-one', { referer: 'https://referrer.example/campaign/' });
	await page.goto('/callback-home/');
	expect(loaded.some((url) => /(?:mount-|jsx-runtime-).*\.js/.test(url))).toBe(false);
	await page.locator('.gd-hero__actions [data-lead-capture-open]').click();
	const dialog = page.getByRole('dialog');
	await dialog.getByRole('button', { name: 'EV charging', exact: true }).click();
	await dialog.getByRole('button', { name: 'Fleet / depot', exact: true }).click();
	await dialog.getByRole('button', { name: 'Planning a project', exact: true }).click();
	await dialog.getByRole('textbox', { name: /Town, city/ }).fill('Birmingham');
	await dialog.getByRole('button', { name: 'Continue', exact: true }).click();
	await fillContact(page);
	await dialog.locator('.lead-capture__body').evaluate((element) => { element.scrollTop = 0; });
	await testInfo.attach('callback-contact-1440', { body: await page.screenshot(), contentType: 'image/png' });
	await dialog.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(dialog.getByRole('heading', { name: 'Thank you. Your request has been sent.' })).toBeVisible();
	const mail = await (await request.get('/wp-json/greendawn-test/v1/callback')).json();
	expect(mail.to).toContain('admin@example.test');
	expect(mail.message).toContain('firstName: Test visitor');
	expect(mail.message).toContain('intent: ev-charging');
	expect(mail.message).toContain('siteType: fleet-depot');
	expect(mail.message).toContain('location: Birmingham');
	expect(mail.message).toContain('entryPoint: hero');
	expect(mail.message).toContain('utmSource: qa');
	expect(mail.message).toContain('utmMedium: cpc');
	expect(mail.message).toContain('utmCampaign: callback');
	expect(mail.message).toContain('utmTerm: fleet');
	expect(mail.message).toContain('utmContent: ad-one');
	expect(mail.message).toContain('page-url: http://localhost:8080/callback-home/');
	expect(mail.message).toContain('first_landing: http://localhost:8080/about/');
	expect(mail.message).toContain('first_referrer: https://referrer.example/');
	expect(mail.message).not.toContain('?utm_');
	const conversions = await page.evaluate(() => window.dataLayer?.filter(entry => entry.event === 'generate_lead'));
	expect(conversions).toEqual([{
		event: 'generate_lead', form_location: 'enquiry_drawer', lead_type: 'sales_enquiry',
		cta_location: 'hero', charger_slug: null, gd_event_statistics: false, gd_event_marketing: true,
	}]);
	expect(loaded.some((url) => /mount-.*\.js/.test(url))).toBe(true);
	const events = await page.evaluate(() => (window as unknown as { funnel: unknown[] }).funnel);
	expect(events).toEqual([
		expect.objectContaining({ event: 'lead_tool_opened', entryPoint: 'hero' }),
		expect.objectContaining({ event: 'lead_intent_selected', intent: 'ev-charging' }),
		expect.objectContaining({ event: 'lead_callback_started', projectStage: 'planning' }),
		expect.objectContaining({ event: 'lead_callback_submitted' }),
	]);
	expect(JSON.stringify(events)).not.toMatch(/Test visitor|0121|visitor@example|Birmingham|Callback Test|referrer\.example|ad-one|utm/ );
	await dialog.getByRole('button', { name: 'Start another request' }).click();
	await dialog.getByRole('button', { name: 'Just request a callback' }).click();
	await expect(dialog.getByRole('textbox', { name: 'First name', exact: true })).toHaveValue('');
});

test('direct route validates, retains answers on close and mail failure, then shows confirmed success', async ({ page }) => {
	const dialog = await direct(page, '/callback-inner/');
	await dialog.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(dialog.getByRole('alert')).toContainText('highlighted');
	await expect(dialog.locator('[aria-invalid="true"]')).toHaveCount(8);
	await expect(dialog.getByRole('textbox', { name: 'First name', exact: true })).toBeFocused();
	await fillContact(page, 'failure@example.test');
	await page.keyboard.press('Escape');
	await expect(page.locator('.lead-capture__launcher')).toBeFocused();
	await page.locator('.lead-capture__launcher').click();
	await expect(dialog.getByRole('textbox', { name: 'First name', exact: true })).toHaveValue('Test visitor');
	await dialog.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(dialog.getByRole('alert')).toContainText('could not be sent');
	await expect(dialog.getByRole('textbox', { name: 'Email', exact: true })).toHaveValue('failure@example.test');
	await dialog.getByRole('textbox', { name: 'Email', exact: true }).fill('retry@example.test');
	await dialog.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(dialog.getByRole('heading', { name: /Thank you/ })).toBeVisible();
});

test('back navigation, branching, short viewports and all CTA entry points remain usable', async ({ page }, testInfo) => {
	for (const width of [320, 390, 800, 1440]) {
		await page.setViewportSize({ width, height: width === 800 ? 400 : 850 });
		const dialog = await direct(page);
		await fillContact(page);
		await dialog.getByRole('radio', { name: 'Suggest a time' }).check();
		await dialog.locator('input[type="datetime-local"]').fill('2027-01-20T10:30');
		await expect(dialog.getByRole('button', { name: 'Request callback', exact: true })).toBeInViewport();
		expect(await dialog.evaluate((el) => el.scrollWidth <= el.clientWidth)).toBe(true);
		expect(await dialog.evaluate((el) => Math.round(el.getBoundingClientRect().right))).toBe(width);
		await testInfo.attach(`callback-contact-${width}`, { body: await page.screenshot(), contentType: 'image/png' });
		await page.keyboard.press('Escape');
	}
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/callback-home/');
	for (const entry of ['header', 'hero', 'pricing', 'final-cta', 'footer-cta']) {
		const trigger = page.locator(`a[data-lead-entry-point="${entry}"]:visible`).first();
		await trigger.click();
		await expect(page.getByRole('dialog')).toBeVisible();
		await page.keyboard.press('Escape');
		await expect(trigger).toBeFocused();
	}
	await page.setViewportSize({ width: 390, height: 850 });
	const mobileToggle = page.locator('[data-nav-toggle]');
	await mobileToggle.click();
	await page.locator('.primary-nav [data-lead-entry-point="header"]').click();
	await expect(page.getByRole('dialog')).toBeVisible();
	await expect(page.locator('.primary-nav')).toBeHidden();
	await page.keyboard.press('Escape');
	await expect(mobileToggle).toBeFocused();
	await expect(page.locator('html')).not.toHaveCSS('overflow-y', 'hidden');
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.locator('.lead-capture__launcher').click();
	const dialog = page.getByRole('dialog');
	await dialog.getByRole('button', { name: 'Just request a callback' }).click();
	await dialog.getByRole('button', { name: 'Back', exact: true }).click();
	await dialog.getByRole('button', { name: 'Power / grid capacity' }).click();
	await expect(dialog.getByText('What’s prompted the enquiry?')).toBeVisible();
	await expect(dialog.locator('.lead-capture__panel')).toHaveAttribute('style', /--lead-progress: 40/);
	await dialog.getByRole('button', { name: 'Back', exact: true }).click();
	await dialog.getByRole('button', { name: 'Existing chargers / aftercare' }).click();
	await expect(dialog.getByText('What are you experiencing?')).toBeVisible();
	await dialog.getByRole('button', { name: 'Maintenance', exact: true }).click();
	await dialog.getByRole('button', { name: 'Back', exact: true }).click();
	await expect(dialog.getByRole('button', { name: 'Maintenance', exact: true })).toHaveAttribute('aria-pressed', 'true');
});

test('no JavaScript or failed React loading leaves ordinary enquiry destinations', async ({ browser, baseURL, page }) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	try {
		const fallback = await context.newPage();
		await fallback.goto('/callback-home/');
		await expect(fallback.locator('.lead-capture__launcher')).toBeHidden();
		await fallback.locator('.gd-hero__actions [data-lead-capture-open]').click();
		await expect(fallback).toHaveURL(/\/about\/$/);
	} finally { await context.close(); }
	await page.route(/mount-.*\.js/, (route) => route.abort());
	await page.goto('/callback-home/');
	await page.locator('.gd-hero__actions [data-lead-capture-open]').click();
	await expect(page).toHaveURL(/\/about\/$/);
});

test('direct HTTP requests cannot bypass required fields, controlled choices, spam or rate limits', async ({ page, request }) => {
	await page.goto('/callback-inner/');
	const native = page.locator('[data-lead-transport] form');
	const fields = await native.locator('input[type="hidden"]').evaluateAll((inputs) => Object.fromEntries(inputs.map((input) => [(input as HTMLInputElement).name, (input as HTMLInputElement).value])));
	const config = JSON.parse(await page.locator('[data-lead-capture]').getAttribute('data-config') ?? '{}');
	const endpoint = `/wp-json/contact-form-7/v1/contact-forms/${config.formId}/feedback`;
	const good = { ...fields, firstName: 'HTTP test', company: 'Test Company', phone: '0121 555 0100', email: 'http@example.test', callbackPreference: 'today', source: 'website', entryPoint: 'header' };
	const invalid = await (await request.post(endpoint, { multipart: { ...good, phone: '123', intent: 'unapproved', company: '' } })).json();
	expect(invalid.status).toBe('validation_failed');
	expect(invalid.invalid_fields.map((field: { field: string }) => field.field)).toEqual(expect.arrayContaining(['phone', 'intent', 'company']));
	expect(await (await request.get('/wp-json/greendawn-test/v1/callback')).json()).toEqual([]);
	const spam = await (await request.post(endpoint, { multipart: { ...good, website: 'https://spam.example' } })).json();
	expect(spam.status).toBe('spam');
	for (let i = 0; i < 5; i++) {
		const accepted = await (await request.post(endpoint, { multipart: good })).json();
		expect(accepted.status).toBe('mail_sent');
	}
	const throttled = await (await request.post(endpoint, { multipart: good })).json();
	expect(throttled.status).toBe('spam');
});

test('the mobile drawer has no axe violations and restores the mobile menu trigger', async ({ page }) => {
	await page.setViewportSize({ width: 390, height: 850 });
	await page.goto('/callback-home/');
	const toggle = page.locator('[data-nav-toggle]');
	await toggle.click();
	await page.locator('.gd-mobile-enquiry').click();
	const dialog = page.getByRole('dialog');
	await expect(dialog).toBeVisible();
	await dialog.getByRole('button', { name: 'Just request a callback' }).click();
	const results = await new AxeBuilder({ page }).include('.lead-capture__dialog').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await page.keyboard.press('Escape');
	await expect(toggle).toBeFocused();
	await expect(page.locator('.primary-nav')).toBeHidden();
});

test('the administrator can disable and re-enable the drawer without changing the contact form', async ({ page, baseURL, browser }) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Disposable administrator only.');
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto('/wp-admin/options-general.php?page=greendawn-core');
	await expect(page.getByRole('link', { name: 'Review callback email recipient and delivery settings' })).toHaveAttribute('href', /page=wpcf7&post=\d+&action=edit/);
	const enabled = page.getByRole('checkbox', { name: 'Enable the sitewide callback drawer' });
	await expect(enabled).toBeChecked();
	const visitor = await browser.newContext({ baseURL });
	try {
		const front = await visitor.newPage();
		await front.goto('/callback-inner/');
		const oldFields = await front.locator('[data-lead-transport] input[type="hidden"]').evaluateAll((inputs) => Object.fromEntries(inputs.map((input) => [(input as HTMLInputElement).name, (input as HTMLInputElement).value])));
		const config = JSON.parse(await front.locator('[data-lead-capture]').getAttribute('data-config') ?? '{}');
		await enabled.uncheck();
		await page.getByRole('button', { name: 'Save callback settings' }).click();
		await expect(page.getByRole('checkbox', { name: 'Enable the sitewide callback drawer' })).not.toBeChecked();
		await front.goto('/callback-inner/');
		await expect(front.locator('[data-lead-capture]')).toHaveCount(0);
		await expect(front.locator('.header-cta')).toHaveAttribute('href', `${baseURL}/about/`);
		const disabled = await visitor.request.post(`/wp-json/contact-form-7/v1/contact-forms/${config.formId}/feedback`, { multipart: { ...oldFields, firstName: 'Disabled test', company: 'Test Company', phone: '0121 555 0100', email: 'disabled@example.test', callbackPreference: 'today' } });
		expect((await disabled.json()).status).toBe('spam');
	} finally {
		await page.getByRole('checkbox', { name: 'Enable the sitewide callback drawer' }).check();
		await page.getByRole('button', { name: 'Save callback settings' }).click();
		await visitor.close();
	}
	await expect(page.getByRole('checkbox', { name: 'Enable the sitewide callback drawer' })).toBeChecked();
});

test('charger quotes open the existing drawer and deliver canonical product context', async ({ page, request }) => {
	await mockComplianz(page, { marketing: true });
	await page.goto('/evcharger/hellonext-h1-22/?utm_source=product-qa&utm_campaign=quote');
	await page.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
	const dialog = page.getByRole('dialog');
	await expect(dialog.getByRole('heading', { name: 'How should we reach you?' })).toBeVisible();
	await expect(dialog.locator('.lead-capture__product-context')).toContainText('Charger fixture 1');
	await fillContact(page, 'charger@example.test');
	await dialog.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(dialog.getByRole('heading', { name: 'Thank you. Your request has been sent.' })).toBeVisible();
	const mail = await (await request.get('/wp-json/greendawn-test/v1/callback')).json();
	expect(mail.message).toContain('chargerTitle: Charger fixture 1');
	expect(mail.message).toContain('chargerURL: http://localhost:8080/evcharger/hellonext-h1-22/');
	expect(mail.message).toContain('entryPoint: charger-quote');
	expect(mail.message).toContain('intent: ev-charging');
	expect(mail.message).toContain('utmSource: product-qa');
	await dialog.getByRole('button', { name: 'Close', exact: true }).click();
	await page.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
	await dialog.getByRole('button', { name: 'Remove charger selection' }).click();
	await expect(dialog.locator('.lead-capture__product-context')).toHaveCount(0);
});

test('quote requests reject wrong product IDs and replace spoofed titles and URLs', async ({ page, request }) => {
	await page.goto('/evcharger/hellonext-h1-22/');
	const native = page.locator('[data-lead-transport] form');
	const fields = await native.locator('input[type="hidden"]').evaluateAll(inputs => Object.fromEntries(inputs.map(input => [(input as HTMLInputElement).name, (input as HTMLInputElement).value])));
	const config = JSON.parse(await page.locator('[data-lead-capture]').getAttribute('data-config') ?? '{}');
	const endpoint = `/wp-json/contact-form-7/v1/contact-forms/${config.formId}/feedback`;
	const good = { ...fields, firstName: 'Quote HTTP test', company: 'Quote Company', phone: '0121 555 0100', email: 'quote-http@example.test', callbackPreference: 'today', source: 'website', entryPoint: 'charger-quote' };
	const invalid = await (await request.post(endpoint, { multipart: { ...good, chargerId: String(config.formId), chargerTitle: 'Invented title', chargerURL: 'https://unrelated.example/' } })).json();
	expect(invalid.status).toBe('validation_failed');
	expect(invalid.invalid_fields.map((field: { field: string }) => field.field)).toContain('chargerId');
	const accepted = await (await request.post(endpoint, { multipart: { ...good, chargerId: String(config.charger.id), chargerTitle: 'Invented title', chargerURL: 'https://unrelated.example/' } })).json();
	expect(accepted.status).toBe('mail_sent');
	const mail = await (await request.get('/wp-json/greendawn-test/v1/callback')).json();
	expect(mail.message).toContain('chargerTitle: Charger fixture 1');
	expect(mail.message).toContain('chargerURL: http://localhost:8080/evcharger/hellonext-h1-22/');
	expect(mail.message).not.toMatch(/Invented title|unrelated\.example/);
});
