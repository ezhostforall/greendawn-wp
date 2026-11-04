import { expect, test } from '@playwright/test';

test('the branded homepage renders its hero, proof and client marks without JavaScript', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 390, 1440]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/greendawn-home/');
			const main = page.getByRole('main');
			await expect(main.getByRole('heading', { level: 1 })).toHaveText('EV charging. Handled.');
			const image = main.getByRole('img', { name: /Greendawn engineers/ });
			await expect(image).toHaveAttribute('fetchpriority', 'high');
			await expect(image).toHaveAttribute('srcset', /480w.*800w.*1200w.*1600w/);
			await expect(main.getByRole('img', { name: 'Johnsons Cars' })).toBeVisible();
			await expect(main.getByRole('img', { name: 'The Salvation Army' })).toBeVisible();
			await expect(main.locator('.gd-hero__proof strong')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(main.getByRole('link', { name: 'Discuss your site' })).toHaveAttribute('href', /\/about\/$/);
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			await testInfo.attach(`greendawn-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}

		await page.setViewportSize({ width: 1024, height: 700 });
		const nav = page.getByRole('navigation', { name: 'Primary', exact: true });
		await expect(nav).toBeHidden();
		await page.locator('[data-nav-toggle]').press('Enter');
		await expect(nav.getByRole('link', { name: 'About' })).toBeVisible();
		await expect(nav.getByRole('link', { name: 'Discuss your site' })).toBeVisible();
	} finally {
		await context.close();
	}
});

test('the branded menu closes with focus return and homepage CSS stays page-specific', async ({ page }) => {
	await page.setViewportSize({ width: 1024, height: 700 });
	await page.goto('/greendawn-home/');
	const toggle = page.locator('[data-nav-toggle]');
	const nav = page.getByRole('navigation', { name: 'Primary', exact: true });
	await toggle.press('Enter');
	await nav.getByRole('link', { name: 'About' }).press('Escape');
	await expect(toggle).toBeFocused();
	await expect(nav).toBeHidden();
	await expect(page.locator('link[id^="greendawn-greendawn-homepage-"]')).toHaveCount(1);

	await page.goto('/about/');
	await expect(page.locator('link[id^="greendawn-greendawn-homepage-"]')).toHaveCount(0);
	await expect(page.locator('body')).not.toHaveClass(/greendawn-home/);
});
