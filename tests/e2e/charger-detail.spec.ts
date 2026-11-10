import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const slugs = ['hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw'];

test('all six charger destinations use product details with saved specification values', async ({ page }, testInfo) => {
	for (const [index, slug] of slugs.entries()) {
		await page.goto(`/evcharger/${slug}/`);
		await expect(page.locator('main h1')).toHaveText(`Charger fixture ${index + 1}`);
		await expect(page.locator('.gd-charger-spec-grid > div')).toHaveCount(12);
		await expect(page.locator('[data-spec="weight"] dd')).toHaveText('0 kg');
		await expect(page.locator('[data-spec="dimensions"] dd')).toHaveText('465 x 300 x 170 mm');
		await expect(page.locator('[data-spec="connectivity"] dd')).toHaveText('WiFi, RFID');
		await expect(page.locator('[data-spec="price"] dd')).toHaveText('1,250.00 + installation');
		await expect(page.locator('[data-spec="supply_voltage"] dd')).toHaveText('150 - 750V DC x2');
		await expect(page.locator('main time, main .post-navigation')).toHaveCount(0);
		await expect(page.locator('.gd-charger-description')).toContainText(index === 5 ? 'Edited structured product description.' : 'A second paragraph retained in full.');
	}
	await page.goto('/evcharger/hellonext-h1-22/');
	for (const width of [320, 834, 1440]) {
		await page.setViewportSize({ width, height: 950 });
		const image = page.locator('.gd-charger-media img');
		await expect(image).toHaveAttribute('loading', 'eager');
		await expect(image).toHaveAttribute('fetchpriority', 'high');
		await expect(image).toHaveAttribute('alt', 'Charger fixture 1');
		expect(await image.evaluate(el => getComputedStyle(el).objectFit)).toBe('contain');
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
		await testInfo.attach(`charger-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
	}
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
});

test('empty and builder records stay safe and no-JavaScript quote links retain the selection', async ({ page, browser, baseURL }) => {
	await page.goto('/evcharger/charger-empty/');
	await expect(page.locator('.gd-charger-media, .gd-charger-description, .gd-charger-specs')).toHaveCount(0);
	await expect(page.locator('main')).not.toContainText('250 kg');
	await page.goto('/evcharger/charger-builder/');
	await expect(page.locator('.gd-charger-description')).toHaveCount(0);
	await expect(page.locator('main')).not.toContainText('[et_pb_');
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	try {
		const native = await context.newPage();
		await native.goto('/evcharger/hellonext-h1-22/');
		await native.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
		await expect(native).toHaveURL(`${baseURL}/enquire/?charger=hellonext-h1-22`);
		await expect(native.locator('.charger-enquiry-context')).toContainText('Charger fixture 1');
		await native.goto('/enquire/?charger=not-a-published-charger');
		await expect(native.locator('.charger-enquiry-context')).toHaveCount(0);
	} finally { await context.close(); }
});
