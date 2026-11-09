import { expect, test } from '@playwright/test';

test('only the main header sticks and compacts like Astro while the utility row scrolls away', async ({ page }, testInfo) => {
	for (const path of ['/greendawn-home-sticky/', '/header-options/']) {
		for (const width of [390, 768, 1440]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto(path);
			await page.evaluate(() => document.fonts.ready);
			const header = page.getByRole('banner');
			const utility = page.locator('.gd-utility-bar');
			await expect(header).not.toHaveClass(/is-scrolled/);
			expect((await header.boundingBox())!.y).toBeCloseTo((await utility.boundingBox())!.height, 1);
			const expanded = (await header.boundingBox())!.height;
			expect(expanded).toBeCloseTo(width <= 672 ? 75.2 : 88, 1);
			const mainTop = await page.getByRole('main').evaluate((element) => element.getBoundingClientRect().top + window.scrollY);
			if (path === '/greendawn-home-sticky/') {
				expect(mainTop).toBe(0);
				await expect(header).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
			}
			await page.evaluate(() => window.scrollTo({ top: 70, behavior: 'instant' }));
			await expect(header).not.toHaveClass(/is-scrolled/);
			await page.evaluate(() => window.scrollTo({ top: 800, behavior: 'instant' }));
			await expect(header).toHaveCSS('position', 'fixed');
			await expect(header).toHaveCSS('background-color', 'rgba(7, 23, 33, 0.94)');
			await expect.poll(async () => (await header.boundingBox())!.height).toBeCloseTo(75.2, 1);
			expect((await header.boundingBox())!.y).toBe(0);
			expect((await utility.boundingBox())!.y + (await utility.boundingBox())!.height).toBeLessThan(0);
			expect(await header.locator('.gd-utility').count()).toBe(0);
			expect(await page.getByRole('main').evaluate((element) => element.getBoundingClientRect().top + window.scrollY)).toBeCloseTo(mainTop, 1);
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
			await testInfo.attach(`header-compact-${path.includes('home') ? 'home' : 'inner'}-${width}`, { body: await header.screenshot({ animations: 'disabled' }), contentType: 'image/png' });
			if (path === '/greendawn-home-sticky/') {
				await page.evaluate(() => window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'instant' }));
				expect((await header.boundingBox())!.y).toBe(0);
			}
			await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
			await expect(header).not.toHaveClass(/is-scrolled/);
			await expect.poll(async () => (await header.boundingBox())!.height).toBeCloseTo(expanded, 1);
		}
	}
	await page.goto('/greendawn-home/');
	await page.evaluate(() => window.scrollTo({ top: 800, behavior: 'instant' }));
	await expect(page.getByRole('banner')).not.toHaveClass(/site-header--sticky|is-scrolled/);
	expect((await page.getByRole('banner').boundingBox())!.y).toBeLessThan(0);
});

test('the utility divider spans the viewport while its content remains constrained, including RTL', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const path of ['/greendawn-home/', '/about/']) {
			for (const width of [320, 768, 1440, 1920]) {
				await page.setViewportSize({ width, height: 900 });
				await page.goto(path);
				const row = page.locator('.gd-utility-bar');
				const box = (await row.boundingBox())!;
				const header = (await page.getByRole('banner').boundingBox())!;
				expect(box.x).toBeCloseTo(header.x, 1);
				expect(box.width).toBeCloseTo(header.width, 1);
				const divider = await row.evaluate((element) => {
					const style = getComputedStyle(element, '::after');
					return { left: style.left, right: style.right, width: Number.parseFloat(style.width), border: style.borderBottomWidth };
				});
				expect(divider.left).toBe('0px');
				expect(divider.right).toBe('0px');
				expect(divider.width).toBeCloseTo(box.width, 1);
				expect(divider.border).toBe('1px');
				const content = (await row.locator('.gd-utility').boundingBox())!;
				expect(content.x).toBeGreaterThan(box.x);
				expect(content.width).toBeLessThan(box.width);
				expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
				if (width === 1920) await testInfo.attach(`header-divider-${path.includes('home') ? 'home' : 'inner'}-1920`, { body: await page.screenshot(), contentType: 'image/png' });
			}
		}
		await page.evaluate(() => { document.documentElement.dir = 'rtl'; });
		expect((await page.locator('.gd-utility-bar').boundingBox())!.x).toBe(0);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(1920);
	} finally {
		await context.close();
	}
});

test('the compact main header clears the WordPress admin toolbar and keeps mobile navigation usable', async ({ page, baseURL }) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Login belongs to the disposable fixture only.');
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	for (const width of [1440, 768, 390]) {
		await page.setViewportSize({ width, height: 900 });
		await page.goto('/greendawn-home-sticky/');
		const header = page.getByRole('banner');
		const toolbar = page.locator('#wpadminbar');
		await expect(toolbar).toBeVisible();
		expect((await header.boundingBox())!.y).toBeGreaterThanOrEqual((await toolbar.boundingBox())!.height);
		await page.evaluate(() => window.scrollTo({ top: 73, behavior: 'instant' }));
		await expect(header).toHaveClass(/is-scrolled/);
		expect(await header.evaluate((element) => {
			const box = element.getBoundingClientRect();
			return document.elementFromPoint(box.x + box.width / 2, box.y + 8)?.closest('.site-header') === element;
		})).toBe(true);
		await page.evaluate(() => window.scrollTo({ top: 800, behavior: 'instant' }));
		expect((await header.boundingBox())!.y).toBe(width > 782 ? 32 : width > 600 ? 46 : 0);
		if (width === 390) {
			const toggle = header.locator('[data-nav-toggle]');
			await toggle.click();
			const nav = header.getByRole('navigation', { name: 'Primary', exact: true });
			const support = nav.getByRole('link', { name: 'Support', exact: true });
			await support.scrollIntoViewIfNeeded();
			await expect(support).toBeInViewport();
			await support.press('Escape');
			await expect(toggle).toBeFocused();
			await expect(nav).toBeHidden();
			const skip = page.getByRole('link', { name: 'Skip to content', exact: true });
			await skip.focus();
			await expect(skip).toBeInViewport();
			expect(await skip.evaluate((element) => {
				const box = element.getBoundingClientRect();
				return document.elementFromPoint(box.x + box.width / 2, box.y + box.height / 2)?.closest('.skip-link') === element;
			})).toBe(true);
		}
	}
});

test('the Customiser checkbox enables and disables the compact homepage header in preview', async ({ page, baseURL }) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Login belongs to the disposable fixture only.');
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto(`/wp-admin/customize.php?url=${encodeURIComponent(`${baseURL}/greendawn-home/`)}`);
	await page.locator('#accordion-section-greendawn_header .accordion-section-title').click();
	const setting = page.getByRole('checkbox', { name: 'Keep the header visible while scrolling', exact: true });
	await expect(setting).not.toBeChecked();
	await setting.check();
	const preview = page.frameLocator('#customize-preview iframe').first();
	const header = preview.getByRole('banner');
	await expect(header).toHaveClass(/site-header--sticky/);
	await preview.locator('body').evaluate(() => window.scrollTo({ top: 800, behavior: 'instant' }));
	await expect(header).toHaveCSS('position', 'fixed');
	expect(await preview.locator('.gd-utility-bar').evaluate((element) => element.getBoundingClientRect().bottom)).toBeLessThan(0);
	await setting.uncheck();
	await expect(header).not.toHaveClass(/site-header--sticky/);
	await expect(header).toHaveCSS('position', 'absolute');
});
