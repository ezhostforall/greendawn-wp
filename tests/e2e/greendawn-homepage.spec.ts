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
			await expect(main.getByRole('img', { name: 'Johnsons Cars', exact: true })).toBeVisible();
			await expect(main.getByRole('img', { name: 'The Salvation Army', exact: true })).toBeVisible();
			await expect(main.locator('.gd-hero__proof strong')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(main.getByRole('heading', { level: 2 })).toHaveText([
				'One charging project. Coordinated from the start.',
				'The charger is only one part of the system.',
				'A clear route from requirement to operation.',
				'A dealership programme coordinated site by site.',
				'Replace assumptions with a buildable plan.',
				'Charging built around your operation.',
				'EV infrastructure needs to keep working.',
				'Different locations. A coordinated programme.',
				'Make better-informed charging decisions.',
				'Tell us what your site needs.',
			]);
			await expect(main.locator('#problem li')).toHaveCount(6);
			await expect(main.locator('#system .gd-system__cards h3')).toHaveText([
				'Power & infrastructure', 'Charging equipment', 'Software & operation',
			]);
			await expect(main.locator('#process h3')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			const aftercare = main.locator('#aftercare');
			expect(await aftercare.locator('.gd-eyebrow').evaluate((eyebrow) => Number.parseFloat(getComputedStyle(eyebrow).marginBottom))).toBeGreaterThan(0);
			await expect(aftercare.getByRole('heading', { level: 3 })).toHaveText([
				'Monitoring & reporting', 'Inspection & testing', 'Maintenance & fault support', 'Driver & user support',
			]);
			await expect(aftercare.getByRole('link', { name: 'Explore aftercare' })).toHaveAttribute('href', `${baseURL}/aftercare/`);
			await expect(aftercare.getByRole('link', { name: 'Contact customer support' })).toHaveAttribute('href', `${baseURL}/customer-care/`);
			await expect(aftercare.locator('.gd-aftercare__quote')).toHaveText('Support that continues beyond installation.');
			await expect(aftercare.locator('.gd-aftercare__quote')).toHaveCSS('font-style', 'italic');
			await expect(aftercare.locator('.gd-aftercare__quote')).toHaveCSS('font-family', /Bricolage Grotesque/);
			const proof = main.locator('#supporting-proof');
			await expect(proof.getByRole('article')).toHaveCount(1);
			await expect(proof.getByRole('heading', { level: 3 })).toHaveText('The Salvation Army');
			await expect(proof.locator('.gd-proof-card__body strong')).toHaveText('50 sites · 71 chargepoints');
			await expect(proof.getByRole('link', { name: 'Explore our project experience' })).toHaveAttribute('href', `${baseURL}/gallery/`);
			expect(await proof.locator('.gd-eyebrow').evaluate((eyebrow) => Number.parseFloat(getComputedStyle(eyebrow).marginBottom))).toBeGreaterThan(0);
			expect(await aftercare.locator('.gd-aftercare__quote').evaluate((quote) => Number.parseFloat(getComputedStyle(quote).marginTop))).toBeGreaterThan(0);
			await expect(main.locator('#who-we-help h3')).toHaveText([
				'Fleet & depot charging', 'Workplace charging', 'Public & destination charging',
				'Multi-site programmes', 'Dealerships & motor retail',
			]);
			const destinations = await main.locator('#who-we-help .gd-solution-row').evaluateAll((links) => links.map((link) => (link as HTMLAnchorElement).href));
			expect(destinations).toEqual([
				`${baseURL}/fleet-charging-solutions/`, `${baseURL}/workplace-charging/`, `${baseURL}/public-charging/`,
				`${baseURL}/business-ev-charging-installations/`, `${baseURL}/guides/ev-charging-for-car-dealership/`,
			]);
			for (const href of await main.locator('a[href]:not([href^="tel:"])').evaluateAll((links) => links.map((link) => (link as HTMLAnchorElement).href))) {
				expect(new URL(href).origin).toBe(new URL(baseURL!).origin);
			}
			const projectImage = main.locator('#projects figure img');
			await expect(projectImage).toHaveAttribute('loading', 'lazy');
			await expect(projectImage).toHaveAttribute('src', /johnsons-dealership-ev-charging-/);
			await expect(projectImage).toHaveAttribute('width', /\d+/);
			await expect(projectImage).toHaveAttribute('height', /\d+/);
			await projectImage.scrollIntoViewIfNeeded();
			await expect.poll(() => projectImage.evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
			await projectImage.evaluate((image: HTMLImageElement) => image.decode());
			await expect(main.locator('#projects figure')).toHaveCSS('position', width > 976 ? 'sticky' : 'relative');
			await expect(main.locator('#projects .gd-case-study__facts strong')).toHaveText(['136', '454', '11']);
			const chargepoints = main.locator('#projects .gd-case-study__facts span').nth(1);
			expect(await chargepoints.evaluate((label) => {
				const range = document.createRange();
				range.selectNodeContents(label);
				return range.getClientRects().length;
			})).toBe(1);
			const surveyCard = main.locator('#surveys .gd-surveys__card');
			await expect(surveyCard.locator('p')).toHaveText([
				'Site surveys from',
				'The scope and final price depend on the site and requirement. Both are confirmed before booking, including any further assessments or third-party costs that may apply.',
			]);
			const surveyLogo = surveyCard.getByRole('img', { name: 'Greendawn', exact: true });
			await expect(surveyLogo).toHaveAttribute('src', /greendawn-wordmark-480\.png$/);
			await surveyLogo.scrollIntoViewIfNeeded();
			await expect.poll(() => surveyLogo.evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
			await expect(surveyCard).not.toContainText(/£\d|\+ VAT/);
			await expect(main.locator('#surveys li p')).toHaveText([
				'Charging demand and available site capacity',
				'Charger locations, cable routes and access constraints',
				'Electrical, civil and network dependencies',
				'The information needed for the design and proposal',
			]);
			await expect(main.locator('#projects').getByRole('link', { name: 'Explore our project experience' })).toHaveAttribute('href', `${baseURL}/gallery/`);
			await expect(main.getByRole('link', { name: 'Discuss a site survey' })).toHaveAttribute('href', /\/about\/$/);
			await expect(main.locator('#surveys').getByRole('link', { name: 'What a technical survey should establish' })).toHaveAttribute('href', /\/guides\/greendawn-ev-charging-survey-pre-installation-guide\/$/);
			await expect(main.getByRole('link', { name: 'Understand AC and DC charging' })).toHaveAttribute('href', /\/guides\/ac-vs-dc-ev-charging\/$/);
			await expect(main.getByRole('link', { name: 'Explore site power planning' })).toHaveAttribute('href', /\/guides\/can-my-building-handle-ev-charging\/$/);
			await expect(main.locator('.gd-hero').getByRole('link', { name: 'Discuss your site' })).toHaveAttribute('href', /\/about\/$/);
			await expect(main.getByRole('link', { name: 'Explore our projects' })).toHaveAttribute('href', `${baseURL}/gallery/`);
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
			await testInfo.attach(`greendawn-project-${width}`, { body: await main.locator('#projects').screenshot(), contentType: 'image/png' });
			await testInfo.attach(`greendawn-surveys-${width}`, { body: await main.locator('#surveys').screenshot(), contentType: 'image/png' });
			await testInfo.attach(`greendawn-aftercare-${width}`, { body: await aftercare.screenshot({ animations: 'disabled' }), contentType: 'image/png' });
			const proofImage = proof.getByRole('img', { name: 'Commercial EV chargers installed for The Salvation Army', exact: true });
			await expect(proofImage).toHaveAttribute('loading', 'lazy');
			await expect(proofImage).toHaveAttribute('srcset', /666w/);
			await expect(proofImage).toHaveAttribute('width', /\d+/);
			await expect(proofImage).toHaveAttribute('height', /\d+/);
			await proofImage.scrollIntoViewIfNeeded();
			await proofImage.evaluate((image: HTMLImageElement) => image.decode());
			await testInfo.attach(`greendawn-proof-${width}`, { body: await proof.screenshot({ animations: 'disabled' }), contentType: 'image/png' });
			const aftercareLink = aftercare.getByRole('link', { name: 'Explore aftercare' });
			await aftercareLink.focus();
			await aftercareLink.press('Tab');
			const supportLink = aftercare.getByRole('link', { name: 'Contact customer support' });
			await expect(supportLink).toBeFocused();
			await expect(supportLink).toHaveCSS('outline-color', 'rgb(233, 242, 39)');
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
	const utility = page.getByRole('navigation', { name: 'Utility' }).getByRole('link', { name: 'Support' });
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
	const heroCta = main.locator('.gd-hero').getByRole('link', { name: 'Discuss your site' });
	await heroCta.hover();
	await expect(heroCta).toHaveCSS('background-color', 'rgb(250, 249, 246)');
	await expect(heroCta).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -3)');

	const projects = main.getByRole('link', { name: 'Explore our projects' });
	await projects.hover();
	await expect(projects).toHaveCSS('color', 'rgb(233, 242, 39)');
	await expect(projects).toHaveCSS('gap', '21.6px');
	const projectLink = main.locator('#projects').getByRole('link', { name: 'Explore our project experience' });
	await expect(projectLink).toHaveCSS('color', 'rgb(11, 29, 42)');
	await projectLink.scrollIntoViewIfNeeded();
	// A programmatic jump starts the scroll parallax after Playwright's first stability check.
	let previousTop = Number.NaN;
	let stableSamples = 0;
	await expect.poll(async () => {
		const top = (await projectLink.boundingBox())!.y;
		stableSamples = Math.abs(top - previousTop) < 0.05 ? stableSamples + 1 : 0;
		previousTop = top;
		return stableSamples;
	}, { intervals: [100, 150, 200] }).toBeGreaterThanOrEqual(2);
	await projectLink.hover();
	await expect(projectLink).toHaveCSS('color', 'rgb(58, 34, 216)');
	const surveyCta = main.getByRole('link', { name: 'Discuss a site survey' });
	await expect(surveyCta).toHaveCSS('background-color', 'rgb(11, 29, 42)');
	await surveyCta.hover();
	await expect(surveyCta).toHaveCSS('background-color', 'rgb(58, 34, 216)');
	await expect(surveyCta).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -3)');
	const aftercareCta = main.getByRole('link', { name: 'Explore aftercare' });
	await expect(aftercareCta).toHaveCSS('background-color', 'rgb(233, 242, 39)');
	await aftercareCta.hover();
	await expect(aftercareCta).toHaveCSS('background-color', 'rgb(250, 249, 246)');
	await expect(aftercareCta).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -3)');
	const proof = main.locator('#supporting-proof');
	await proof.getByRole('link', { name: 'Explore our project experience' }).hover();
	await expect(proof.getByRole('link')).toHaveCSS('color', 'rgb(58, 34, 216)');
	await proof.locator('.gd-proof-card').hover();
	await expect(proof.locator('img')).toHaveCSS('transform', 'matrix(1.055, 0, 0, 1.055, 0, 0)');
	const closing = main.locator('#final-enquiry');
	const closingCta = closing.getByRole('link', { name: 'Discuss your site' });
	await expect(closingCta).toHaveCSS('background-color', 'rgb(11, 29, 42)');
	await closingCta.hover();
	await expect(closingCta).toHaveCSS('background-color', 'rgb(58, 34, 216)');
	await expect(closingCta).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, -3)');
	await closingCta.focus();
	await page.keyboard.press('Tab');
	const phone = closing.getByRole('link', { name: '0345 512 0044' });
	await expect(phone).toBeFocused();
	await expect(phone).toHaveCSS('outline-color', 'rgb(11, 29, 42)');
	await page.emulateMedia({ reducedMotion: 'reduce' });
	expect(await closingCta.evaluate((element) => Number.parseFloat(getComputedStyle(element).transitionDuration))).toBeLessThanOrEqual(0.001);

});


test('saved homepage values render in the initial response with one hero image', async ({ page, baseURL }) => {
	const imageRequests: string[] = [];
	page.on('request', (request) => {
		if (request.resourceType() === 'image') imageRequests.push(request.url());
	});
	const response = await page.goto('/greendawn-home-edited/');
	const html = await response?.text();
	expect(html).toContain('Charging around your site.');
	expect(html).not.toContain('<span>EV charging.</span>');
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
	expect(html).toContain('A programme shaped around your estate.');
	expect(html).not.toContain('A dealership programme coordinated site by site.');
	expect(html).not.toContain('johnsons-dealership-ev-charging-');
	await expect(page.locator('#projects figure img')).toHaveAttribute('alt', 'Service fixture image');
	await expect(page.locator('#projects .gd-case-study__facts strong').first()).toHaveText('150');
	await expect(page.locator('#surveys').getByRole('img', { name: 'Greendawn', exact: true })).toBeVisible();
	await expect(page.locator('#surveys')).not.toContainText(/£250|\+ VAT/);
	await expect(page.locator('#surveys li p').first()).toHaveText('Assess the electrical capacity at your site');
	await expect(page.locator('#surveys').getByRole('link', { name: 'What a technical survey should establish' })).toHaveAttribute('href', 'https://example.com/survey-guide/');
	const services = page.locator('#who-we-help');
	await expect(services.getByRole('heading', { name: 'Charging for your fleet' })).toBeVisible();
	await expect(services.locator('.gd-solution-row--fleet p')).toHaveText('A charging plan for your vehicles and operating hours.');
	await expect(services.locator('.gd-solution-row--fleet')).toHaveAttribute('href', `${baseURL}/fleet-charging-solutions/?source=home#details`);
	await expect(services.locator('.gd-solution-row--fleet .gd-solution-row__destination')).toHaveText('See our fleet service');
	await expect(services.locator('.gd-solution-image--fleet img')).toHaveAttribute('src', /service-media/);
	await expect(services.locator('.gd-solution-image--fleet figcaption')).toHaveText('01Charging for your fleet');
	expect(html).toContain('Charging for your fleet');
	await expect(page.getByRole('link', { name: 'Explore our projects' })).toHaveAttribute('href', `${baseURL}/gallery/?view=estate#projects`);
	await expect(page.getByRole('link', { name: 'Understand AC and DC charging' })).toHaveAttribute('href', `${baseURL}/guides/ac-vs-dc-ev-charging/`);
	const aftercare = page.locator('#aftercare');
	await expect(aftercare.getByRole('heading', { level: 2 })).toHaveText('Support around your charging operation.');
	await expect(aftercare.locator('.gd-aftercare__intro')).toHaveText('Installation is the start of the charging operation, not the end of the relationship.');
	await expect(aftercare.getByRole('heading', { level: 3 }).first()).toHaveText('Charger monitoring and reporting');
	await expect(aftercare.locator('.gd-aftercare__quote')).toHaveText('Clear support for every stage of operation.');
	await expect(aftercare.getByRole('link', { name: 'Review our aftercare' })).toHaveAttribute('href', `${baseURL}/aftercare/?view=plans#scope`);
	await expect(aftercare.getByRole('link', { name: 'Speak to customer care' })).toHaveAttribute('href', `${baseURL}/customer-care/?from=home#help`);
	expect(html).toContain('Support around your charging operation.');
	expect(html).not.toContain('EV infrastructure needs to keep working.');
	const proof = page.locator('#supporting-proof');
	await expect(proof.getByRole('heading', { level: 2 })).toHaveText('Coordinated charging across your sites.');
	await expect(proof.getByRole('heading', { level: 3 })).toHaveText('Fleet charging programme');
	await expect(proof.locator('.gd-proof-card__body strong')).toHaveText('52 sites · 74 chargepoints');
	await expect(proof.locator('figcaption')).toHaveText('Fleet programme delivered together');
	await expect(proof.locator('.gd-proof-card__body p')).toHaveText('A coordinated programme for a varied fleet estate.');
	await expect(proof.getByRole('img')).toHaveAttribute('src', /service-media/);
	await expect(proof.getByRole('link', { name: 'See our fleet projects' })).toHaveAttribute('href', `${baseURL}/gallery/?view=estate#projects`);
	expect(html).toContain('Coordinated charging across your sites.');
	expect(html).not.toContain('salvation-army-ev-charging-');
	const insights = page.locator('#insights');
	await expect(insights.getByRole('heading', { level: 2 })).toHaveText('Guidance for your charging project.');
	await expect(insights.getByRole('link', { name: 'Read our charging guides' })).toHaveAttribute('href', `${baseURL}/blog/?from=home#guides`);
	const powerGuide = insights.getByRole('link', { name: 'Plan your site power' });
	await expect(powerGuide).toHaveAttribute('href', `${baseURL}/guides/load-balancing-explained/?from=home#capacity`);
	await expect(powerGuide.locator('p')).toHaveText('Power & capacity');
	await expect(powerGuide.getByRole('img')).toHaveAttribute('src', /service-media/);
	await expect(insights.getByRole('link', { name: 'What a technical survey should establish' })).toHaveAttribute('href', 'https://example.com/technical-guide/');
	await expect(insights.locator('.gd-insight-card--survey p')).toHaveText('Survey planning');
	await expect(insights.getByRole('link', { name: 'Charging around your fleet' })).toHaveAttribute('href', `${baseURL}/guides/transitioning-to-electric-fleets/`);
	expect(html).toContain('Guidance for your charging project.');
	expect(html).not.toContain('Make better-informed charging decisions.');
	expect(html).not.toContain('commercial-ev-electrical-connection-');
	const closing = page.locator('#final-enquiry');
	await expect(closing.locator('.gd-eyebrow')).toHaveText('Your next charging project');
	await expect(closing.getByRole('heading', { level: 2 })).toHaveText('Start your charging project.');
	await expect(closing.locator('.gd-final-cta__inner > p').nth(1)).toHaveText('Tell us about your vehicles, site and operating hours.');
	await expect(closing.locator('.gd-final-cta__inner > p').nth(2)).toHaveText('We’ll help identify the next step, including whether a technical survey is appropriate.');
	await expect(closing.getByRole('link', { name: 'Plan your project' })).toHaveAttribute('href', `${baseURL}/contact/?from=home#enquiry`);
	await expect(page.locator('.gd-hero').getByRole('link', { name: 'Discuss your site' })).toHaveAttribute('href', `${baseURL}/contact/?from=home#enquiry`);
	await expect(closing.getByRole('link', { name: '+44 345 512 0044', exact: true })).toHaveAttribute('href', 'tel:+443455120044');
	await expect(closing.locator('.gd-final-cta__actions > p')).toContainText('Call our team');
	expect(html).toContain('Start your charging project.');
	expect(html).not.toContain('Tell us what your site needs.');

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

test('service photographs follow hover and keyboard focus without JavaScript', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();

	try {
		await page.setViewportSize({ width: 1440, height: 900 });
		await page.goto('/greendawn-home/');
		const services = page.locator('#who-we-help');
		const media = services.locator('.gd-solutions__media');
		const fleet = services.locator('.gd-solution-row--fleet');
		const fleetImage = services.locator('.gd-solution-image--fleet img');
		await fleetImage.scrollIntoViewIfNeeded();
		await expect.poll(() => fleetImage.evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
		await fleetImage.evaluate((image: HTMLImageElement) => image.decode());
		await expect(fleetImage).toHaveAttribute('loading', 'lazy');
		await expect(fleetImage).toHaveAttribute('alt', '');
		await expect(fleetImage).toHaveAttribute('srcset', /1200w/);

		const workplace = services.locator('.gd-solution-row--workplace');
		await workplace.hover();
		await expect(workplace).toHaveCSS('color', 'rgb(58, 34, 216)');
		const workplaceImage = services.locator('.gd-solution-image--workplace img');
		await expect(workplaceImage).toBeVisible();
		await expect.poll(() => workplaceImage.evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
		await workplaceImage.evaluate((image: HTMLImageElement) => image.decode());
		await expect(workplaceImage).toHaveAttribute('src', /workplace-charging-1200/);
		await expect(workplaceImage).toHaveAttribute('width', /\d+/);
		await expect(workplaceImage).toHaveAttribute('height', /\d+/);

		await fleet.focus();
		await fleet.press('Tab');
		await expect(workplace).toBeFocused();
		await services.locator('.gd-solution-row--public').hover();
		const box = (await media.boundingBox())!;
		const topPreview = await page.evaluate(({ x, y }) => document.elementFromPoint(x, y)?.closest('figure')?.className, { x: box.x + box.width / 2, y: box.y + box.height / 2 });
		expect(topPreview).toContain('gd-solution-image--workplace');
		const captionBox = (await services.locator('.gd-solution-image--workplace figcaption').boundingBox())!;
		const topCaption = await page.evaluate(({ x, y }) => document.elementFromPoint(x, y)?.closest('figure')?.querySelector('figcaption')?.textContent, { x: captionBox.x + captionBox.width / 2, y: captionBox.y + captionBox.height / 2 });
		expect(topCaption).toBe('02Workplace charging');
		await testInfo.attach('greendawn-services-keyboard-preview', { body: await services.screenshot(), contentType: 'image/png' });

		for (const key of ['public', 'multi_site', 'dealership']) {
			await page.keyboard.press('Tab');
			await expect(services.locator(`.gd-solution-row--${key}`)).toBeFocused();
			const image = services.locator(`.gd-solution-image--${key} img`);
			await expect(image).toBeVisible();
			await expect.poll(() => image.evaluate((element: HTMLImageElement) => element.naturalWidth)).toBeGreaterThan(0);
			await image.evaluate((element: HTMLImageElement) => element.decode());
		}
		await page.locator('.gd-hero h1').click();
		await page.mouse.move(0, 0);
		await testInfo.attach('greendawn-services-1440', { body: await services.screenshot(), contentType: 'image/png' });

		await page.setViewportSize({ width: 320, height: 900 });
		await page.goto('/greendawn-home/');
		await fleetImage.scrollIntoViewIfNeeded();
		await fleetImage.evaluate((image: HTMLImageElement) => image.decode());
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(320);
		await expect(services.locator('.gd-solution-row')).toHaveCount(5);
		expect(await services.locator('.gd-solution-image--fleet figcaption span').evaluate((label) => {
			const range = document.createRange();
			range.selectNodeContents(label);
			return range.getClientRects().length;
		})).toBe(1);
		await testInfo.attach('greendawn-services-320', { body: await services.screenshot(), contentType: 'image/png' });
	} finally {
		await context.close();
	}
});


test('service previews defer unused image requests with browser lazy loading enabled', async ({ page }) => {
	// This checks the static CSS preview contract; scroll selection is covered separately.
	await page.emulateMedia({ reducedMotion: 'reduce' });
	const serviceImages: string[] = [];
	page.on('request', (request) => {
		if (request.resourceType() === 'image' && /commercial-ev-(fleet|workplace|destination|multi-site|dealership)/.test(request.url())) {
			serviceImages.push(request.url());
		}
	});
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/greendawn-home/');
	const services = page.locator('#who-we-help');
	const fleetImage = services.locator('.gd-solution-image--fleet img');
	await fleetImage.scrollIntoViewIfNeeded();
	await expect.poll(() => fleetImage.evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
	await fleetImage.evaluate((image: HTMLImageElement) => image.decode());
	expect(serviceImages.some((url) => url.includes('fleet-charging-bays'))).toBe(true);
	expect(serviceImages.some((url) => /workplace|destination|multi-site|dealership/.test(url))).toBe(false);
	await services.locator('.gd-solution-row--workplace').hover();
	const workplaceImage = services.locator('.gd-solution-image--workplace img');
	await expect.poll(() => workplaceImage.evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
	expect(serviceImages.some((url) => url.includes('workplace'))).toBe(true);
	expect(serviceImages.some((url) => /destination|multi-site|dealership/.test(url))).toBe(false);
});


test('supporting proof retains its content and full-width card when the photograph is unavailable', async ({ page, baseURL }) => {
	for (const width of [320, 1440]) {
		await page.setViewportSize({ width, height: 900 });
		await page.goto('/greendawn-home-no-proof-image/');
		const proof = page.locator('#supporting-proof');
		await expect(proof.locator('figure')).toHaveCount(0);
		await expect(proof.getByRole('heading', { level: 3 })).toHaveText('The Salvation Army');
		await expect(proof.locator('.gd-proof-card__body strong')).toHaveText('50 sites · 71 chargepoints');
		await expect(proof.getByRole('link')).toHaveAttribute('href', `${baseURL}/gallery/`);
		const card = (await proof.locator('.gd-proof-card').boundingBox())!;
		const body = (await proof.locator('.gd-proof-card__body').boundingBox())!;
		expect(body.width).toBeGreaterThan(card.width - 4);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
	}
});


test('insights match the curated guide layout on mobile, tablet and desktop without JavaScript', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 390, 800, 1440]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/greendawn-home/');
			const insights = page.locator('#insights');
			const cards = insights.locator('.gd-insight-card');
			await expect(cards).toHaveCount(3);
			await expect(insights.getByRole('heading', { level: 3 })).toHaveText([
				'Understanding load management', 'What a technical survey should establish', 'Planning the transition to an electric fleet',
			]);
			await expect(insights.getByRole('link', { name: 'Browse commercial charging guidance' })).toHaveAttribute('href', `${baseURL}/blog/`);
			expect(await cards.evaluateAll((links) => links.map((link) => (link as HTMLAnchorElement).href))).toEqual([
				`${baseURL}/guides/load-balancing-explained/`, `${baseURL}/guides/greendawn-ev-charging-survey-pre-installation-guide/`, `${baseURL}/guides/transitioning-to-electric-fleets/`,
			]);
			await expect(cards.locator(':scope > p')).toHaveText(['Power & capacity', 'Project planning', 'Fleet planning']);
			const images = cards.getByRole('img');
			await expect(images).toHaveCount(3);
			for (const image of await images.all()) {
				await expect(image).toHaveAttribute('loading', 'lazy');
				await expect(image).toHaveAttribute('srcset', /\d+w/);
				await expect(image).toHaveAttribute('width', /\d+/);
				await expect(image).toHaveAttribute('height', /\d+/);
				await image.scrollIntoViewIfNeeded();
				await image.evaluate((element: HTMLImageElement) => element.decode());
			}
			await expect(images.first()).toHaveCSS('object-position', width <= 672 ? '62% 50%' : '66% 50%');
			await expect(insights.locator('.gd-eyebrow')).toHaveCSS('margin-bottom', '32px');
			const boxes = await cards.evaluateAll((elements) => elements.map((element) => {
				const { x, y, width } = element.getBoundingClientRect();
				return { x, y, width };
			}));
			if (width <= 672) {
				expect(boxes[0]!.x).toBeCloseTo(boxes[1]!.x, 0);
				expect(boxes[1]!.y).toBeGreaterThan(boxes[0]!.y);
				expect(boxes[2]!.y).toBeGreaterThan(boxes[1]!.y);
			} else if (width <= 976) {
				expect(boxes[0]!.y).toBeCloseTo(boxes[1]!.y, 0);
				expect(boxes[2]!.y).toBeGreaterThan(boxes[1]!.y);
				expect(boxes[2]!.width).toBeGreaterThan(boxes[0]!.width * 2);
				await expect(images.last()).toHaveAttribute('sizes', /61rem\) calc\(100vw/);
			} else {
				expect(boxes[0]!.y).toBeCloseTo(boxes[2]!.y, 0);
				expect(boxes[0]!.x).toBeLessThan(boxes[1]!.x);
				expect(boxes[1]!.x).toBeLessThan(boxes[2]!.x);
			}
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
			await testInfo.attach(`greendawn-insights-${width}`, { body: await insights.screenshot({ animations: 'disabled' }), contentType: 'image/png' });
		}
	} finally {
		await context.close();
	}
});

test('insight cards support hover, keyboard focus and reduced motion', async ({ page }) => {
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/greendawn-home/');
	const insights = page.locator('#insights');
	const power = insights.getByRole('link', { name: 'Understanding load management' });
	await power.hover();
	await expect(power.locator('h3')).toHaveCSS('color', 'rgb(58, 34, 216)');
	await expect(power.locator('img')).toHaveCSS('transform', 'matrix(1.055, 0, 0, 1.055, 0, 0)');
	await expect(power.locator('figure > span')).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, 0)');
	await insights.getByRole('link', { name: 'Browse commercial charging guidance' }).focus();
	await page.keyboard.press('Tab');
	await expect(power).toBeFocused();
	await expect(power).toHaveCSS('outline-color', 'rgb(58, 34, 216)');
	await page.keyboard.press('Tab');
	const survey = insights.getByRole('link', { name: 'What a technical survey should establish' });
	await expect(survey).toBeFocused();
	await expect(survey.locator('figure > span')).toHaveCSS('transform', 'matrix(1, 0, 0, 1, 0, 0)');
	await page.emulateMedia({ reducedMotion: 'reduce' });
	const duration = await survey.locator('img').evaluate((element) => Number.parseFloat(getComputedStyle(element).transitionDuration));
	expect(duration).toBeLessThanOrEqual(0.001);
});

test('insight guide links remain available when their photographs are missing', async ({ page }, testInfo) => {
	for (const width of [320, 1440]) {
		await page.setViewportSize({ width, height: 900 });
		await page.goto('/greendawn-home-no-insight-images/');
		const insights = page.locator('#insights');
		await expect(insights.locator('figure')).toHaveCount(0);
		await expect(insights.locator('.gd-insight-card')).toHaveCount(3);
		await expect(insights.getByRole('link', { name: 'Understanding load management' })).toHaveAttribute('href', /\/guides\/load-balancing-explained\/$/);
		await expect(insights.getByRole('heading', { level: 3 })).toHaveCount(3);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
		await testInfo.attach(`greendawn-insights-no-media-${width}`, { body: await insights.screenshot(), contentType: 'image/png' });
	}
});


test('the final enquiry panel matches the reference layout and works without JavaScript', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 390, 800, 1440]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/greendawn-home/');
			await page.evaluate(() => document.fonts.ready);
			const closing = page.locator('#final-enquiry');
			const title = closing.getByRole('heading', { name: 'Tell us what your site needs.' });
			await expect(closing).toHaveCSS('background-color', 'rgb(233, 242, 39)');
			await expect(closing.locator('.gd-eyebrow')).toHaveText('Start with the requirement');
			await expect(closing.locator('.gd-eyebrow')).toHaveCSS('margin-bottom', '32px');
			const titleMetrics = await title.evaluate((element) => {
				const style = getComputedStyle(element);
				return { size: Number.parseFloat(style.fontSize), lineHeight: Number.parseFloat(style.lineHeight) };
			});
			expect(titleMetrics.size).toBeCloseTo(Math.min(144, Math.max(64, 48 + width * 0.055)), 1);
			expect(titleMetrics.lineHeight / titleMetrics.size).toBeCloseTo(0.84, 2);
			await expect(closing.locator('.gd-final-cta__inner > p').nth(1)).toHaveText('A rough outline is enough to start. Tell us who needs charging, what you want it to achieve and what you know about the site.');
			await expect(closing.locator('.gd-final-cta__inner > p').nth(2)).toHaveText('We’ll help identify the next step, including whether a technical survey is appropriate.');
			await expect(closing.getByRole('link', { name: '0345 512 0044' })).toHaveAttribute('href', 'tel:03455120044');
			await expect(closing.locator('.gd-final-cta__actions')).toHaveCSS('flex-direction', width <= 672 ? 'column' : 'row');
			await expect(closing.locator(':scope > svg')).toHaveAttribute('aria-hidden', 'true');
			const cta = closing.getByRole('link', { name: 'Discuss your site' });
			await expect(cta).toHaveAttribute('href', `${baseURL}/about/`);
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
			await testInfo.attach(`greendawn-final-enquiry-${width}`, { body: await closing.screenshot({ animations: 'disabled' }), contentType: 'image/png' });
			await cta.press('Enter');
			await expect(page).toHaveURL(`${baseURL}/about/`);
		}
	} finally {
		await context.close();
	}
});

test('unconfigured enquiry destinations and unusable phone numbers do not create broken links', async ({ page }) => {
	await page.goto('/greendawn-home-no-proof-image/');
	const closing = page.locator('#final-enquiry');
	await expect(closing.getByRole('heading', { name: 'Tell us what your site needs.' })).toBeVisible();
	await expect(closing.getByRole('link')).toHaveCount(0);
	await page.goto('/greendawn-home-no-insight-images/');
	await expect(closing.getByRole('link', { name: 'Discuss your site' })).toHaveCount(0);
	await expect(closing.getByRole('link', { name: '0345 512 0044' })).toHaveAttribute('href', 'tel:03455120044');
});
