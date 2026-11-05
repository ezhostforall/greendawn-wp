import { expect, test } from '@playwright/test';

test('the branded homepage renders its sections in order without JavaScript', async ({ browser, baseURL }, testInfo) => {
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
			await expect(image).toHaveAttribute('srcset', /1600w/);
			await expect(image).toHaveAttribute('src', /\/uploads\/2026\/11\/greendawn-engineers-electrical-distribution-/);
			await expect(main.getByRole('img', { name: 'Johnsons Cars' })).toBeVisible();
			await expect(main.getByRole('img', { name: 'The Salvation Army' })).toBeVisible();
			await expect(main.locator('.gd-hero__proof strong')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(main.getByRole('heading', { level: 2 })).toHaveText([
				'One charging project. Coordinated from the start.',
				'The charger is only one part of the system.',
				'A clear route from requirement to operation.',
			]);
			await expect(main.locator('#problem li')).toHaveCount(6);
			await expect(main.locator('#system .gd-system__cards h3')).toHaveText([
				'Power & infrastructure', 'Charging equipment', 'Software & operation',
			]);
			await expect(main.locator('#process h3')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(main.getByRole('link', { name: 'Understand AC and DC charging' })).toHaveAttribute('href', /\/guides\/ac-vs-dc-ev-charging\/$/);
			await expect(main.getByRole('link', { name: 'Explore site power planning' })).toHaveAttribute('href', /\/guides\/can-my-building-handle-ev-charging\/$/);
			await expect(main.getByRole('link', { name: 'Discuss your site' })).toHaveAttribute('href', /\/about\/$/);
			await expect(main.getByRole('link', { name: 'Explore our projects' })).toHaveAttribute('href', 'https://greendawn.co.uk/gallery/');
			const delivery = main.getByText('Commercial delivery', { exact: true });
			expect(await delivery.evaluate((element) => getComputedStyle(element, '::before').backgroundColor)).toBe('rgb(255, 94, 51)');
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
			const bodyFontSize = await page.locator('body').evaluate((element) => Number.parseFloat(getComputedStyle(element).fontSize));
			expect(bodyFontSize).toBeCloseTo(width === 390 ? 16.14 : width === 320 ? 16 : 18, 1);
			const eyebrow = main.locator('.gd-hero .gd-eyebrow');
			const eyebrowMetrics = await eyebrow.evaluate((element) => {
				const { fontSize, fontWeight, lineHeight } = getComputedStyle(element);
				return { fontWeight, lineHeightRatio: Number.parseFloat(lineHeight) / Number.parseFloat(fontSize) };
			});
			expect(eyebrowMetrics.fontWeight).toBe('400');
			expect(eyebrowMetrics.lineHeightRatio).toBeCloseTo(1.2, 2);
			await expect(main.locator('#process h2')).toHaveCSS('margin-block-end', '32px');
			await testInfo.attach(`greendawn-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}

		await page.setViewportSize({ width: 390, height: 844 });
		const nav = page.getByRole('navigation', { name: 'Primary', exact: true });
		await expect(nav).toBeHidden();
		await page.locator('[data-nav-toggle]').press('Enter');
		await expect(nav.getByRole('link', { name: 'About' })).toBeVisible();
		await expect(nav.getByRole('link', { name: 'Discuss your site' })).toBeVisible();
		await expect(nav.getByRole('link', { name: 'Support', exact: true })).toBeVisible();
	} finally {
		await context.close();
	}
});

test('the branded header is shared, with the overlay and hero CSS scoped to the homepage', async ({ page }) => {
	await page.setViewportSize({ width: 1024, height: 700 });
	await page.goto('/greendawn-home/');
	const toggle = page.locator('[data-nav-toggle]');
	const nav = page.getByRole('navigation', { name: 'Primary', exact: true });
	await toggle.press('Enter');
	await nav.getByRole('link', { name: 'About' }).press('Escape');
	await expect(toggle).toBeFocused();
	await expect(nav).toBeHidden();
	const header = page.getByRole('banner');
	await expect(header).toHaveCSS('position', 'absolute');
	await expect(header).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
	await expect(page.locator('link[id^="greendawn-greendawn-homepage-"]')).toHaveCount(1);

	await page.goto('/about/');
	await expect(page.locator('link[id^="greendawn-greendawn-homepage-"]')).toHaveCount(0);
	await expect(page.locator('body')).not.toHaveClass(/greendawn-home/);
	await expect(header.getByRole('img', { name: 'Greendawn' })).toBeVisible();
	await expect(header).toHaveCSS('position', 'relative');
	await expect(header).toHaveCSS('background-color', 'rgb(7, 23, 33)');
	const headerBox = (await header.boundingBox())!;
	const mainBox = (await page.getByRole('main').boundingBox())!;
	expect(mainBox.y).toBeGreaterThanOrEqual(headerBox.y + headerBox.height);
	await expect(nav).toBeHidden();
	await toggle.press('Enter');
	await expect(nav.getByRole('link', { name: 'About' })).toBeVisible();
});

test('the desktop header and hero use the Greendawn hover styles', async ({ page }) => {
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/greendawn-home/');
	const banner = page.getByRole('banner');
	const utility = banner.getByRole('navigation', { name: 'Utility' }).getByRole('link', { name: 'Support' });
	const utilitySize = await utility.evaluate((element) => Number.parseFloat(getComputedStyle(element).fontSize));
	expect(utilitySize).toBeCloseTo(15.32, 1);
	await utility.hover();
	await expect(utility).toHaveCSS('color', 'rgb(233, 242, 39)');

	const nav = banner.getByRole('navigation', { name: 'Primary', exact: true });
	const about = nav.getByRole('link', { name: 'About', exact: true });
	await about.hover();
	await expect.poll(() => about.evaluate((element) => getComputedStyle(element.parentElement!, '::after').transform)).toBe('matrix(1, 0, 0, 1, 0, 0)');

	await nav.getByRole('button', { name: 'Toggle submenu for Services' }).click();
	const advisory = nav.getByRole('link', { name: 'Advisory', exact: true });
	await advisory.hover();
	await expect(advisory).toHaveCSS('background-color', 'rgb(233, 242, 39)');

	const headerCta = banner.getByRole('link', { name: 'Discuss your site' });
	await headerCta.hover();
	await expect(headerCta).toHaveCSS('background-color', 'rgb(233, 242, 39)');
	await expect(headerCta).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -2)');

	const main = page.getByRole('main');
	const heroCta = main.getByRole('link', { name: 'Discuss your site' });
	await heroCta.hover();
	await expect(heroCta).toHaveCSS('background-color', 'rgb(250, 249, 246)');
	await expect(heroCta).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -3)');

	const projects = main.getByRole('link', { name: 'Explore our projects' });
	await projects.hover();
	await expect(projects).toHaveCSS('color', 'rgb(233, 242, 39)');
	await expect(projects).toHaveCSS('gap', '21.6px');
});


test('saved homepage values render in the initial response with one hero image', async ({ page }) => {
	const imageRequests: string[] = [];
	page.on('request', (request) => {
		if (request.resourceType() === 'image') imageRequests.push(request.url());
	});
	const response = await page.goto('/greendawn-home-edited/');
	const html = await response?.text();
	expect(html).toContain('Charging around your site.');
	expect(html).not.toContain('EV charging.');
	expect(html).not.toContain('greendawn-engineers-electrical-distribution-');
	const heroImage = page.locator('.gd-hero__media img');
	await expect(heroImage).toHaveCount(1);
	await expect(heroImage).toHaveAttribute('alt', 'Service fixture image');
	expect(imageRequests.filter((url) => url.includes('service-media'))).toHaveLength(1);
	expect(imageRequests.some((url) => url.includes('greendawn-engineers-electrical-distribution-'))).toBe(false);
	await expect(page.getByRole('heading', { level: 1 })).toHaveText('Charging around your site. Handled.');
	await expect(page.locator('.gd-hero__proof strong').first()).toHaveText('Assess');
	await expect(page.locator('#process h3').first()).toHaveText('Assess');
	await expect(page.locator('#process .gd-eyebrow')).toHaveText('Assess · Design · Install · Manage');
});

test('the utility bar text can be edited and reset in the Customiser', async ({ page, baseURL }) => {
	test.skip(baseURL !== 'http://localhost:8080', 'The login belongs to the disposable fixture only.');
	const defaultText = 'Commercial EV infrastructure across England, Scotland & Wales';
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto('/wp-admin/customize.php');
	await page.locator('#accordion-section-greendawn_header .accordion-section-title').click();
	const input = page.getByRole('textbox', { name: 'Utility bar text' });
	await expect(input).toHaveValue(defaultText);
	const preview = page.frameLocator('#customize-preview iframe').first().locator('.gd-utility p');
	await input.fill('Commercial charging for your fleet');
	await input.press('Tab');
	await expect(preview).toHaveText('Commercial charging for your fleet');
	await page.getByRole('button', { name: 'Reset utility bar text' }).click();
	await expect(input).toHaveValue(defaultText);
	await expect(preview).toHaveText(defaultText);
});
