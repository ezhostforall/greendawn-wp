import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const chargerSlugs = ['hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw'];
const articleSlugs = ['ozev-ev-charger-grants-from-april-2026', 'ev-charging-for-car-dealership', 'ev-charger-earthing', 'greendawn-ev-charging-survey-pre-installation-guide', 'transitioning-to-electric-fleets', 'ev-charging-cable-theft', 'zaptec-status-codes', 'project-ev-fault-codes', 'is-ozev-funding-enough', 'ev-installation-day-and-beyond'];
const benefits = [
	'Earn from every charge. Public charging stations provide ongoing income while offsetting installation costs.',
	'Drivers are more likely to stop, spend, and return when you offer convenient EV charging at your site.',
	'With EV adoption accelerating, public charging positions your business as forward-thinking and ready to meet customer demand.',
	'Demonstrate commitment to green transport, enhancing your reputation and compliance with environmental targets.',
];
const steps = [
	'Tell us about your vehicles, users, sites and objectives. We establish the requirement and recommend the next step.',
	'Assess power, layout and delivery constraints. Where a technical survey is needed, agree its scope before developing the design and proposal.',
	'Coordinate the agreed electrical and civil works, equipment, testing and commissioning. Plan access and handover with your team.',
	'Put the agreed software, monitoring, maintenance and fault-support arrangements in place, with clear ongoing responsibilities.',
];
const guidance = [
	'22 kW AC Chargers: Perfect for destinations like retail parks, gyms, or hotels where dwell time is longer. Affordable, reliable, and ideal for consistent revenue from longer stays.',
	'Rapid DC Chargers: Essential for roadside, service stations, and high-traffic sites where drivers expect a fast turnaround. Higher initial investment, but greater earning potential from quick throughput.',
];
const processTitle = 'A clear route from requirement to operation.';
const processIntro = 'You do not need a finished specification to start. Each stage establishes the decisions and information needed for the next.';

test('Public preserves source content and all local destinations without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/public-charging/');
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('Public EV');
			await expect(page.locator('main h1 em')).toHaveText('Charging Solutions');
			await expect(page.locator('main')).not.toContainText('Do not append this legacy layout');
			await expect(page.locator('.gd-page-lead')).toHaveText('Optimise Public Charging: Reliable, Revenue-Ready, and Easy to Manage');
			const image = page.locator('.gd-page-hero__media img');
			await expect(image).toHaveAttribute('loading', 'eager');
			await expect(image).toHaveAttribute('fetchpriority', 'high');
			await expect(image).toHaveAttribute('srcset', /[0-9]+w/);
			await expect(page.locator('.gd-feature-grid h3')).toHaveText(['New Revenue Streams', 'Customer Attraction & Loyalty', 'Futureproof Your Business', 'Sustainability & Brand Value']);
			await expect(page.locator('.gd-feature-grid p')).toHaveText(benefits);
			for (const paragraph of await page.locator('.gd-feature-grid p').all()) await expect(paragraph).toBeVisible();
			await expect(page.locator('.gd-recommended-chargers h2')).toHaveText('The Right Chargers for Public Sites');
			await expect(page.locator('.gd-chargers-intro').first()).toHaveText('For public use, we recommend a combination of 22 kW fast AC chargers and rapid DC chargers:');
			await expect(page.locator('.gd-charger-guidance li')).toHaveText(guidance);
			await expect(page.locator('.gd-chargers-intro').last()).toHaveText('This mixed approach ensures you can serve both long-stay visitors and quick-stop drivers, maximising usage and ROI.');
			await expect(page.locator('.gd-charger-card h3')).toHaveText(chargerSlugs.map((_, i) => `Charger fixture ${i + 1}`));
			for (const [index, slug] of chargerSlugs.entries()) {
				const card = page.locator('.gd-charger-card').nth(index);
				await expect(card.locator('.gd-charger-card__product')).toHaveAttribute('href', `${baseURL}/evcharger/${slug}/`);
				await expect(card.getByRole('link', { name: `Request Quote — Charger fixture ${index + 1}`, exact: true })).toHaveAttribute('href', `${baseURL}/evcharger/${slug}/`);
				await expect(card.locator('img')).toHaveAttribute('loading', 'lazy');
				expect(await card.locator('img').evaluate(el => getComputedStyle(el).objectFit)).toBe('contain');
			}
			await expect(page.locator('.gd-cta-band h2')).toHaveText(['Not sure what the right public charging setup looks like?', 'Ready to Power Public EV Charging?']);
			await expect(page.locator('.gd-cta-band p')).toHaveText([
				'Every site is different. Power capacity, user demand, and revenue potential all play a part. We’ll help you understand what works before anything is installed.',
				'Turn EV charging into a new revenue stream while growing your customer base and futureproofing your business.',
			]);
			for (const link of await page.locator('.gd-cta-band a').all()) await expect(link).toHaveAttribute('href', `${baseURL}/enquire/`);
			await expect(page.locator('.gd-process')).toHaveCount(1);
			await expect(page.locator('#public-process h2')).toHaveText(processTitle);
			await expect(page.locator('#public-process .gd-process__intro > p')).toHaveText(processIntro);
			await expect(page.locator('#public-process h3')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(page.locator('#public-process .gd-process__steps p')).toHaveText(steps);
			await expect(page.locator('.gd-process-media')).toHaveCount(0);
			await expect(page.locator('main')).not.toContainText('We begin with a consultation and site survey, carried out on-site.');
			await expect(page.locator('main .wpcf7, .gd-form-section')).toHaveCount(0);
			await expect(page.locator('.gd-article-track .gd-insight-card')).toHaveCount(10);
			await expect(page.locator('.gd-article-track h3')).toHaveText(Array.from({ length: 10 }, (_, i) => `Guide fixture ${i + 1}`));
			for (const [index, slug] of articleSlugs.entries()) await expect(page.locator('.gd-insight-card').nth(index)).toHaveAttribute('href', new RegExp(`/${slug}/$`));
			await expect(page.getByRole('button', { name: 'Next articles' })).toBeHidden();
			for (const href of await page.locator('main a[href]').evaluateAll(links => links.map(a => (a as HTMLAnchorElement).href))) expect(href).toMatch(new RegExp(`^${baseURL}/`));
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			for (const media of await page.locator('main img').all()) {
				await media.scrollIntoViewIfNeeded();
				await media.evaluate(async (el: HTMLImageElement) => { await el.decode(); });
			}
			// Check lazy media in its visible viewport as well as the full-page composition.
			// Chromium may omit cached off-screen lazy images from a full-page capture.
			for (const [label, selector] of [['charger', '.gd-charger-card'], ['article', '.gd-insight-card']] as const) {
				const card = page.locator(selector).first();
				await card.scrollIntoViewIfNeeded();
				expect(await card.locator('img').evaluate((el: HTMLImageElement) => el.complete && el.naturalWidth > 0)).toBe(true);
				await card.evaluate(() => new Promise<void>(resolve => requestAnimationFrame(() => requestAnimationFrame(() => resolve()))));
				await testInfo.attach(`public-${label}-${width}`, { body: await card.screenshot(), contentType: 'image/png' });
			}
			await page.evaluate(async () => {
				await document.fonts.ready;
				document.querySelector('.gd-article-track')?.scrollTo({ left: 0, behavior: 'instant' });
				window.scrollTo(0, 0);
			});
			await testInfo.attach(`public-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('Public retains edited content, filters stale selections and honours cleared media and protected pages', async ({ page, baseURL }) => {
	await page.goto('/gd-public-edited/');
	await expect(page.locator('main h1')).toHaveText('Public charging for our visitors.');
	await expect(page.locator('main h1 em, .gd-page-lead')).toHaveCount(0);
	await expect(page.locator('.gd-charger-guidance li').first()).toContainText('Reviewed AC guidance for this site.');
	await expect(page.locator('#public-process h3').nth(1)).toHaveText('Assess the site');
	await expect(page.locator('#public-process .gd-process__steps p').first()).toHaveText('');
	await expect(page.locator('#public-process .gd-process__intro > p')).toHaveCount(0);
	await expect(page.locator('.gd-charger-card h3')).toHaveText(['Charger fixture 4', 'Charger fixture 1']);
	await expect(page.locator('.gd-article-track h3')).toHaveText(['Guide fixture 10', 'Guide fixture 1']);
	await expect(page.locator('.gd-cta-band a').first()).toHaveAttribute('href', `${baseURL}/enquire/?from=public#project`);
	await expect(page.locator('.gd-cta-band a').last()).toHaveAttribute('href', `${baseURL}/enquire/?from=public-final#consultation`);
	await page.goto('/gd-public-empty/');
	await expect(page.locator('.gd-page-hero__media, .gd-charger-card, .gd-process-media, .gd-page-insights')).toHaveCount(0);
	await expect(page.locator('.gd-feature-grid article')).toHaveCount(4);
	await expect(page.locator('.gd-charger-guidance li')).toHaveText(guidance);
	await expect(page.locator('#public-process h2')).toHaveText(processTitle);
	await expect(page.locator('main .wpcf7, .gd-form-section')).toHaveCount(0);
	await page.goto('/gd-public-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-public-no-core');
	await expect(page.locator('main h2')).toHaveCount(0);
	await page.goto('/gd-public-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('main')).not.toContainText('Protected Public content must stay private.');
	await expect(page.locator('.gd-feature-grid, .gd-charger-card, .gd-process, .gd-page-insights')).toHaveCount(0);
});

test('Public article controls, local enquiry CTA and charger quote journey work with reduced motion', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/public-charging/');
	await expect(page.locator('script[src*="greendawn-home-motion"], script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
	await page.getByRole('button', { name: 'Next articles' }).click();
	await expect.poll(() => page.locator('.gd-article-track').evaluate(el => el.scrollLeft)).toBeGreaterThan(0);
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await page.getByRole('link', { name: 'Book a Free Consultation', exact: true }).click();
	await expect(page).toHaveURL(/\/enquire\/$/);
	await expect(page.locator('.charger-enquiry-context')).toHaveCount(0);
	await page.goto('/public-charging/');
	await page.getByRole('link', { name: 'Request Quote — Charger fixture 1', exact: true }).click();
	await expect(page).toHaveURL(/\/evcharger\/hellonext-h1-22\/$/);
	await page.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
	await expect(page.getByRole('dialog').locator('.lead-capture__product-context')).toContainText('Charger fixture 1');
	await expect(page.getByRole('dialog').getByRole('heading', { name: 'How should we reach you?' })).toBeVisible();
});


test('canonical defaults preserve the homepage process adapter and saved legacy content', async ({ page }) => {
	await page.goto('/gd-process-saved-home/');
	await expect(page.locator('#process h2')).toHaveText('Our saved homepage route.');
	await expect(page.locator('#process h3').first()).toHaveText('Saved consultation');
	await expect(page.locator('#process .gd-process__steps p').first()).toHaveText('Existing homepage consultation copy.');
	await page.goto('/public-charging/');
	await expect(page.locator('#public-process h2')).toHaveText(processTitle);
	await expect(page.locator('#public-process .gd-process__steps p')).toHaveText(steps);
});
