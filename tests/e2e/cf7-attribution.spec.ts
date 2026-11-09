import { expect, test } from '@playwright/test';

test('native CF7 grid retains first-touch context through navigation and serialisation', async ({ page, baseURL }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.goto('/about/?private=discard#discard', { referer: 'https://referrer.example/campaign/?token=discard#discard' });
	await page.goto('/gd-about-default/?private=discard#discard');
	const form = page.locator('.gd-form-panel form');
	const fields = form.locator('.gd-contact-fields');
	await expect(fields).toBeVisible();
	await expect(fields.locator('p, br')).toHaveCount(0);
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}/gd-about-default/`);
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/about/`);
	await expect(form.locator('[name="first_referrer"]')).toHaveValue('https://referrer.example/campaign/');
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(375);
	await form.getByRole('textbox', { name: 'Full Name (required)', exact: true }).fill('Form test');
	await form.getByRole('textbox', { name: 'Project Name (required)', exact: true }).fill('Office installation');
	await form.getByRole('textbox', { name: 'Phone Number (required)', exact: true }).fill('0121 555 0100');
	await form.getByRole('textbox', { name: 'Email Address (required)', exact: true }).fill('form@example.test');
	await form.getByRole('combobox', { name: 'Project Type (required)', exact: true }).selectOption('Workplace');
	await page.evaluate(() => window.history.replaceState({}, '', '/gd-about-default/?another=discard#discard'));
	await form.locator('[name="page-url"]').evaluate((input: HTMLInputElement) => { input.value = 'stale'; });
	// Intercept delivery: inspect CF7's actual payload without sending another test email.
	const submitted = page.waitForRequest(request => request.method() === 'POST' && request.url().includes('/contact-forms/') && request.url().includes('/feedback'));
	await page.route('**/contact-forms/*/feedback', route => route.fulfill({ json: { status: 'mail_failed', message: 'Test delivery intercepted.', invalid_fields: [] } }));
	await form.getByRole('button', { name: 'Send enquiry' }).click();
	const payload = (await submitted).postDataBuffer()?.toString() ?? '';
	expect(payload).toContain(`${baseURL}/gd-about-default/`);
	expect(payload).toContain(`${baseURL}/about/`);
	expect(payload).toContain('https://referrer.example/campaign/');
	expect(payload).not.toMatch(/private=|token=|another=|#discard|stale/);
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await form.evaluate((element: HTMLFormElement) => element.reset());
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/about/`);
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}/gd-about-default/`);
	await page.reload();
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/about/`);
});

test('CF7 remains usable when session storage is denied or JavaScript is disabled', async ({ browser, baseURL }) => {
	const context = await browser.newContext({ baseURL });
	await context.addInitScript(() => { Object.defineProperty(window, 'sessionStorage', { get() { throw new DOMException('Storage denied', 'SecurityError'); } }); });
	const page = await context.newPage();
	await page.goto('/gd-about-default/');
	const form = page.locator('.gd-form-panel form');
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}/gd-about-default/`);
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/gd-about-default/`);
	await form.getByRole('button', { name: 'Send enquiry' }).click();
	await expect(form.locator('[aria-invalid="true"]').first()).toBeVisible();
	await context.close();
	const noJS = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const fallback = await noJS.newPage();
	await fallback.goto('/gd-about-default/');
	await expect(fallback.locator('.gd-contact-fields label')).toHaveCount(7);
	await expect(fallback.locator('.gd-form-panel [name="page-url"]')).toHaveValue('');
	await expect(fallback.getByRole('button', { name: 'Send enquiry' })).toBeVisible();
	await noJS.close();
});
