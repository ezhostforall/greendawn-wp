import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const titles = ['Johnsons Cars Ltd.', 'Midlands Industrial Association Limited', 'Elite Hotels Group', 'Salvation Army', 'Honda Showrooms'];

test('Gallery keeps source stories and ordered native photo links without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/gallery/');
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1')).toHaveText('Our Projects');
			await expect(page.locator('.gd-case-study h2')).toHaveText(titles);
			const stories = page.locator('.gd-case-study');
			await expect(stories.nth(0).locator('.gd-case-study__facts li')).toHaveText(['250+ Sockets', '50+ Sites', 'Car Dealership']);
			await expect(stories.nth(0)).toContainText('281 chargers across all 60 locations');
			await expect(stories.nth(1)).toContainText('Monta integration, commissioning, and ongoing aftercare support.');
			await expect(stories.nth(2)).toContainText('reduce noise and maintain the hotel’s high standards of service');
			await expect(stories.nth(3)).toContainText('50 Sites');
			await expect(stories.nth(3)).toContainText('more than 45 locations nationwide');
			await expect(stories.nth(4)).toContainText('Coming Soon…');
			await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/`);
			await expect(page.locator('.gd-gallery-photos h2')).toHaveText('Our Gallery');
			const links = page.locator('.gd-gallery-photos a[data-gd-photo]');
			await expect(links).toHaveCount(3);
			await expect(links.nth(0)).toHaveAttribute('href', /Salvation-Army-Charging-Array\.png$/);
			await expect(links.nth(1)).toHaveAttribute('href', /Johnsons-Cars-Ford-CTEK-Charger-scaled\.png$/);
			await expect(links.nth(2)).toHaveAttribute('href', /Johnsons-Cars-Project-EV-Charger-Mazda-showroom\.png$/);
			await expect(stories.nth(0).locator('a[data-gd-photo]')).toHaveCount(2);
			await expect(stories.nth(1).locator('a[data-gd-photo]')).toHaveCount(0);
			await expect(page.locator('main')).not.toContainText('Legacy Gallery backup');
			await expect(page.locator('main form:not([method="dialog"]), main iframe')).toHaveCount(0);
			await expect(page.locator('dialog img')).not.toHaveAttribute('src', /./);
			for (const image of await page.locator('[data-gd-photo] img').all()) {
				await expect(image).toHaveAttribute('loading', 'lazy');
				await expect(image).toHaveAttribute('width', /[1-9]\d*/);
				await image.scrollIntoViewIfNeeded();
				await image.evaluate(async (el: HTMLImageElement) => { await el.decode(); });
			}
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
			await testInfo.attach(`gallery-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('Gallery viewer supports keyboard navigation, captions and focus return', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 375, height: 850 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/gallery/');
	const first = page.locator('.gd-case-study').first().locator('a[data-gd-photo]').first();
	await first.focus();
	await page.keyboard.press('Enter');
	const viewer = page.getByRole('dialog', { name: 'Project photograph viewer' });
	await expect(viewer).toBeVisible();
	await expect(viewer.getByRole('button', { name: 'Close' })).toBeFocused();
	await expect(viewer.getByRole('status')).toHaveText('1 / 2');
	await expect(viewer.getByRole('img')).toHaveAttribute('alt', 'Johnsons dealership charging');
	await page.keyboard.press('ArrowRight');
	await expect(viewer.getByRole('status')).toHaveText('2 / 2');
	await expect(viewer.locator('figcaption')).toHaveText('Ford dealership charging caption.');
	await expect(viewer.getByRole('link', { name: 'Open image file' })).toHaveAttribute('href', /Johnsons-Cars-Ford-CTEK-Charger-scaled\.png$/);
	await viewer.getByRole('img').evaluate(async (el: HTMLImageElement) => { await el.decode(); });
	const results = await new AxeBuilder({ page }).include('[data-gd-photo-dialog]').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await testInfo.attach('gallery-viewer-375', { body: await page.screenshot(), contentType: 'image/png' });
	await viewer.getByRole('button', { name: 'Previous' }).click();
	await expect(viewer.getByRole('status')).toHaveText('1 / 2');
	await page.keyboard.press('Escape');
	await expect(viewer).toBeHidden();
	await expect(first).toBeFocused();
	const last = page.locator('.gd-gallery-photos a[data-gd-photo]').last();
	await last.click();
	await expect(viewer.getByRole('status')).toHaveText('3 / 3');
	await viewer.getByRole('button', { name: 'Next' }).click();
	await expect(viewer.getByRole('status')).toHaveText('1 / 3');
	await viewer.getByRole('button', { name: 'Close' }).click();
	await expect(last).toBeFocused();
});

test('Gallery renders saved ordering, clears and the native password gate', async ({ page }) => {
	await page.goto('/gd-gallery-edited/');
	await expect(page.locator('main h1')).toHaveText('Selected projects');
	await expect(page.locator('main h1 em')).toHaveCount(0);
	await expect(page.locator('.gd-case-study h2')).toHaveText('Edited project');
	await expect(page.locator('.gd-case-study__facts li')).toHaveText(['Edited fact', 'Second fact']);
	await expect(page.locator('.gd-case-study__copy strong')).toHaveText('project copy.');
	await expect(page.locator('.gd-case-study__copy [style]')).toHaveCount(0);
	await expect(page.locator('.gd-case-study a[data-gd-photo]').first()).toHaveAttribute('href', /Johnsons-Cars-Ford-CTEK-Charger-scaled\.png$/);
	await expect(page.locator('.gd-gallery-photos a[data-gd-photo]')).toHaveCount(2);
	await expect(page.locator('.gd-gallery-photos a[data-gd-photo]').first()).toHaveAttribute('href', /Johnsons-Cars-Project-EV-Charger-Mazda-showroom\.png$/);
	await page.goto('/gd-gallery-empty/');
	await expect(page.locator('main h1')).toHaveText('Our Projects');
	await expect(page.locator('.gd-case-study, .gd-gallery-photos, main dialog, main .gd-cta-band')).toHaveCount(0);
	await page.goto('/gd-gallery-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('.gd-case-study, .gd-gallery-photos, main dialog, main .gd-cta-band')).toHaveCount(0);
});
