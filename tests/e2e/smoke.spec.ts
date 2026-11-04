import { expect, test } from '@playwright/test';

test('front page renders landmarks and a single h1', async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('header.site-header')).toBeVisible();
	await expect(page.locator('main#content')).toBeVisible();
	await expect(page.locator('footer.site-footer')).toBeVisible();
	await expect(page.locator('h1')).toHaveCount(1);
});

test('skip link is first focusable element and targets main', async ({ page }) => {
	await page.goto('/');
	await page.keyboard.press('Tab');
	const skip = page.locator('.skip-link');
	await expect(skip).toBeFocused();
	await expect(skip).toHaveAttribute('href', '#content');
});

test('unknown URLs return a 404 template', async ({ page }) => {
	const response = await page.goto('/this-page-does-not-exist/');
	expect(response?.status()).toBe(404);
	await expect(page.getByRole('heading', { level: 1 })).toContainText('Page not found');
});

test('search works', async ({ page }) => {
	await page.goto('/?s=hello');
	await expect(page.getByRole('heading', { level: 1 })).toContainText('Search results');
});

test('theme emits exactly one canonical-capable head (no duplicate title tags)', async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('head title')).toHaveCount(1);
});
