import { expect, test } from '@playwright/test';

// Read-only checks for a separately managed WordPress staging site.
// The site must have this starter theme installed; no fixture content is created.
test.beforeAll(() => {
	const url = process.env.BASE_URL;
	if (!url || !url.startsWith('https://')) {
		throw new Error('Set BASE_URL to the HTTPS URL of the dedicated WordPress staging website.');
	}
});

test('staging home renders the starter theme on the staging origin', async ({ page }) => {
	const response = await page.goto('/');
	expect(response?.status()).toBe(200);
	await expect(page.locator('header.site-header')).toBeVisible();
	await expect(page.locator('main#content')).toBeVisible();
	await expect(page.locator('footer.site-footer')).toBeVisible();
	expect(new URL(page.url()).origin).toBe(new URL(process.env.BASE_URL!).origin);
});

test('WordPress staging login screen loads without submitting credentials', async ({ page }) => {
	const response = await page.goto('/wp-login.php');
	expect(response?.status()).toBe(200);
	await expect(page.locator('input[name="log"]')).toBeVisible();
	await expect(page.locator('input[name="pwd"]')).toBeVisible();
	expect(new URL(page.url()).origin).toBe(new URL(process.env.BASE_URL!).origin);
});
