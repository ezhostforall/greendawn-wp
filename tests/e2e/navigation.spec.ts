import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

const primary = (page: Page) => page.getByRole('navigation', { name: 'Primary', exact: true });
const services = (page: Page) => page.getByRole('button', { name: 'Toggle submenu for Services', exact: true });
const advisory = (page: Page) => page.getByRole('button', { name: 'Toggle submenu for Advisory', exact: true });
const menuToggle = (page: Page) => page.locator('[data-nav-toggle]');

async function expectNoOverflow(page: Page) {
	expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
}

test('desktop hides the mobile button and supports nested keyboard disclosures', async ({ page }, testInfo) => {
	await page.goto('/about/');
	await expect(menuToggle(page)).toBeHidden();
	// Reproduce the old cascade bug even when the generic button rule is later.
	await page.addStyleTag({ content: '.button { display: inline-block; }' });
	await expect(menuToggle(page)).toBeHidden();
	await expect(primary(page).getByRole('link', { name: 'About', exact: true })).toHaveAttribute('aria-current', 'page');
	await services(page).focus();
	await page.keyboard.press('Enter');
	await expect(services(page)).toHaveAttribute('aria-expanded', 'true');
	await page.keyboard.press('Tab');
	await expect(primary(page).getByRole('link', { name: 'Advisory', exact: true })).toBeFocused();
	await page.keyboard.press('Tab');
	await expect(advisory(page)).toBeFocused();
	await page.keyboard.press('Space');
	const detail = primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' });
	await page.keyboard.press('Tab');
	await expect(detail).toBeFocused();
	await page.keyboard.press('Escape');
	await expect(advisory(page)).toBeFocused();
	await expect(detail).toBeHidden();
	await page.keyboard.press('Escape');
	await expect(services(page)).toBeFocused();
	await expect(advisory(page)).toBeHidden();
	await services(page).click();
	await page.getByRole('button', { name: 'Toggle submenu for Resources' }).click();
	await expect(services(page)).toHaveAttribute('aria-expanded', 'false');
	await expect(primary(page).getByRole('link', { name: 'Contact', exact: true })).toBeVisible();
	await page.locator('h1').click();
	await expect(primary(page).getByRole('link', { name: 'Contact', exact: true })).toBeHidden();
	await services(page).click();
	await advisory(page).click();
	await expectNoOverflow(page);
	const results = await new AxeBuilder({ page }).include('.site-header').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await testInfo.attach('desktop-header', { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
});

for (const width of [320, 768, 960]) {
	test(`inline navigation works at ${width}px without trapping focus or locking scroll`, async ({ page }, testInfo) => {
		await page.setViewportSize({ width, height: 720 });
		await page.goto('/about/');
		await expect(primary(page)).toBeHidden();
		await menuToggle(page).click();
		await expect(primary(page)).toBeVisible();
		await services(page).click();
		await advisory(page).click();
		await expectNoOverflow(page);
		expect(await page.locator('body').evaluate((body) => getComputedStyle(body).overflow)).not.toBe('hidden');
		await testInfo.attach(`header-${width}`, { body: await page.locator('.site-header').screenshot(), contentType: 'image/png' });
		await primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' }).focus();
		await page.keyboard.press('Escape');
		await expect(advisory(page)).toBeFocused();
		await page.keyboard.press('Escape');
		await expect(services(page)).toBeFocused();
		await page.keyboard.press('Escape');
		await expect(menuToggle(page)).toBeFocused();
		await expect(primary(page)).toBeHidden();
		await menuToggle(page).click();
		// Tab from the final visible menu control reaches content, closing the menu.
		await page.getByRole('button', { name: 'Toggle submenu for Resources' }).focus();
		await page.keyboard.press('Tab');
		await expect(primary(page)).toBeHidden();
		expect(await page.evaluate(() => !document.querySelector('.site-header')?.contains(document.activeElement))).toBe(true);
		await menuToggle(page).click();
		await page.keyboard.press('Shift+Tab');
		await expect(page.locator('.site-header .custom-logo-link')).toBeFocused();
		await expect(primary(page)).toBeHidden();
		await menuToggle(page).click();
		await primary(page).getByRole('link', { name: 'About', exact: true }).click();
		await expect(primary(page)).toBeHidden();
	});
}

test('the mobile homepage starts with stable geometry while navigation and fonts load', async ({ page }) => {
	await page.setViewportSize({ width: 412, height: 823 });
	let releaseAssets!: () => void;
	const assetsReady = new Promise<void>((resolve) => { releaseAssets = resolve; });
	await page.route(/\/greendawn\/(?:dist\/assets\/main-[^/]+\.js|assets\/fonts\/[^/]+\.woff2)$/, async (route) => {
		await assetsReady;
		await route.continue();
	});

	const main = page.locator('main');
	const hero = main.locator('.feature-hero__media img');
	let initialTop: number;
	try {
		await page.goto('/business-home/', { waitUntil: 'commit' });
		await expect(hero).toHaveAttribute('fetchpriority', 'high');
		await expect(hero).toHaveAttribute('loading', 'eager');
		await expect(menuToggle(page)).toBeVisible();
		await expect(primary(page)).toBeHidden();
		initialTop = await main.evaluate((element) => element.getBoundingClientRect().top);
	} finally {
		releaseAssets();
	}
	await page.waitForLoadState('load');
	await page.evaluate(() => document.fonts.ready);
	expect(await main.evaluate((element) => element.getBoundingClientRect().top)).toBe(initialTop);
	await expectNoOverflow(page);
});

test('touch disclosures preserve the parent link destination', async ({ browser, baseURL }) => {
	const context = await browser.newContext({ baseURL, hasTouch: true, isMobile: true, viewport: { width: 375, height: 667 } });
	const page = await context.newPage();
	await page.goto('/about/');
	await menuToggle(page).tap();
	await services(page).tap();
	await advisory(page).tap();
	await expect(primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' })).toBeVisible();
	await primary(page).getByRole('link', { name: 'Services', exact: true }).tap();
	await expect(page).toHaveURL(/\/about\/#services$/);
	await expect(primary(page)).toBeHidden();
	await context.close();
});

test('resize resets disclosures and keeps focus out of hidden content', async ({ page }) => {
	await page.setViewportSize({ width: 320, height: 720 });
	await page.goto('/about/');
	await menuToggle(page).click();
	await services(page).click();
	await advisory(page).click();
	await primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' }).focus();
	await page.setViewportSize({ width: 1280, height: 800 });
	await expect(menuToggle(page)).toBeHidden();
	await expect(primary(page).getByRole('link', { name: 'About', exact: true })).toBeFocused();
	await expect(services(page)).toHaveAttribute('aria-expanded', 'false');
	await page.setViewportSize({ width: 320, height: 720 });
	await expect(primary(page)).toBeHidden();
	await expect(menuToggle(page)).toBeFocused();
	await menuToggle(page).click();
	await expect(primary(page)).toBeVisible();
});

test('optional utility, CTA and sticky header work in short viewports and RTL', async ({ page }, testInfo) => {
	await page.goto('/about/');
	await expect(page.locator('.header-cta')).toHaveCount(0);
	await expect(page.locator('.utility-nav')).toHaveCount(0);
	await expect(page.locator('.site-header')).not.toHaveClass(/site-header--sticky/);
	await page.goto('/header-options/');
	await expect(page.getByRole('navigation', { name: 'Utility', exact: true }).getByRole('link', { name: 'Support' })).toBeVisible();
	await expect(page.getByText('Utility child omitted')).toHaveCount(0);
	await expect(page.locator('.header-cta')).toHaveAttribute('href', /\/about\/$/);
	await page.evaluate(() => window.scrollTo(0, 700));
	await expect(page.locator('.site-header')).toHaveCSS('position', 'sticky');
	expect((await page.locator('.site-header').boundingBox())?.y).toBe(0);
	// Exercise both fixed admin-bar offsets without authenticating to any host.
	await page.evaluate(() => document.body.classList.add('admin-bar'));
	await expect(page.locator('.site-header')).toHaveCSS('top', '32px');
	await page.setViewportSize({ width: 768, height: 720 });
	await expect(page.locator('.site-header')).toHaveCSS('top', '46px');
	await page.setViewportSize({ width: 320, height: 720 });
	await expect(page.locator('.site-header')).toHaveCSS('top', '0px');
	await page.setViewportSize({ width: 320, height: 360 });
	await page.evaluate(() => { window.scrollTo(0, 0); document.documentElement.dir = 'rtl'; });
	await expect(page.locator('.site-header')).toHaveCSS('position', 'relative');
	await menuToggle(page).click();
	await services(page).click();
	await advisory(page).click();
	await expectNoOverflow(page);
	await primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' }).scrollIntoViewIfNeeded();
	await expect(primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' })).toBeInViewport();
	const enquiry = primary(page).getByRole('link', { name: 'Talk to us' });
	await enquiry.scrollIntoViewIfNeeded();
	await expect(enquiry).toBeInViewport();
	await testInfo.attach('short-rtl-header', { body: await page.locator('.site-header').screenshot(), contentType: 'image/png' });
});

test('no assigned menu does not create empty navigation controls', async ({ page }) => {
	await page.goto('/header-empty/');
	await expect(menuToggle(page)).toHaveCount(0);
	await expect(page.locator('.primary-nav, .utility-nav, .header-cta')).toHaveCount(0);
	await expect(page.locator('.site-header .custom-logo-link')).toBeVisible();
});

for (const width of [320, 1280]) {
	test(`all nested links remain available without JavaScript at ${width}px`, async ({ browser, baseURL }) => {
		const context = await browser.newContext({ baseURL, javaScriptEnabled: false, viewport: { width, height: 720 } });
		const page = await context.newPage();
		await page.goto('/about/');
		if (width === 320) {
			await expect(primary(page)).toBeHidden();
			await menuToggle(page).press('Enter');
		} else {
			await expect(menuToggle(page)).toBeHidden();
		}
		await expect(primary(page).getByRole('link', { name: 'Service detail with a longer descriptive label' })).toBeVisible();
		await expect(primary(page).getByRole('link', { name: 'Contact', exact: true })).toBeVisible();
		await expectNoOverflow(page);
		await primary(page).getByRole('link', { name: 'Services', exact: true }).click();
		await expect(page).toHaveURL(/\/about\/#services$/);
		await context.close();
	});
}
