import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const chargerSlugs = ['hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw'];
const articleSlugs = ['ozev-ev-charger-grants-from-april-2026', 'ev-charging-for-car-dealership', 'ev-charger-earthing', 'greendawn-ev-charging-survey-pre-installation-guide', 'transitioning-to-electric-fleets', 'ev-charging-cable-theft', 'zaptec-status-codes', 'project-ev-fault-codes', 'is-ozev-funding-enough', 'ev-installation-day-and-beyond'];
const benefits = [
	'On-site charging is up to 4–5× cheaper than public networks, and with EVs already cheaper to run than petrol or diesel, the savings multiply across your fleet.',
	'Keep vehicles charged and ready when you need them—no wasted driver time at public stations or dealing with unpredictable availability.',
	'Schedule charging around fleet usage, balance loads across multiple chargers, and optimise site power to prevent downtime.',
	'Meet net zero targets, reduce emissions, and futureproof your fleet operations while enhancing your corporate image.',
];
const steps = [
	'We start by understanding your operational needs. An on-site survey is carried out to assess available power, site layout, and fleet usage patterns, ensuring accurate planning from the outset.',
	'You’ll receive a tailored proposal covering costs, optimal charger selection, grant guidance, and project timelines. Once approved, we finalise the scope and prepare for installation.',
	'Our engineers install your infrastructure efficiently, with minimal disruption to operations, while ensuring full compliance and adherence to specifications.',
	'Post-installation, we provide ongoing support, including system monitoring, maintenance, and fault response, keeping your fleet charging consistently and efficiently.',
];

test('Fleet preserves source content and all local destinations without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/fleet-charging-solutions/');
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('EV Charging For');
			await expect(page.locator('main h1 em')).toHaveText('Your Fleets');
			await expect(page.locator('main')).not.toContainText('Do not append this legacy layout');
			await expect(page.locator('.gd-page-lead')).toHaveText('Track and Manage Your Fleet Charging: Smarter, Cost-Effective, and Always Ready');
			const image = page.locator('.gd-page-hero__media img');
			await expect(image).toHaveAttribute('loading', 'eager');
			await expect(image).toHaveAttribute('fetchpriority', 'high');
			await expect(image).toHaveAttribute('srcset', /[0-9]+w/);
			await expect(page.locator('.gd-feature-grid h3')).toHaveText(['Lower Operating Costs', 'Operational Efficiency', 'Energy & Load Management', 'Sustainability & Compliance']);
			await expect(page.locator('.gd-feature-grid p')).toHaveText(benefits);
			for (const paragraph of await page.locator('.gd-feature-grid p').all()) await expect(paragraph).toBeVisible();
			await expect(page.locator('.gd-recommended-chargers h2')).toHaveText('The Right Chargers for Fleets');
			await expect(page.locator('.gd-chargers-intro')).toHaveText('For fleets, we typically recommend 22 kW AC units or mid-range DC chargers. With vehicles parked for predictable dwell times between jobs or overnight, 22 kW charging strikes the balance between speed and efficiency. For busier fleets or sites with higher turnover, mid-range DC chargers add faster turnaround without the heavy infrastructure costs of ultra-rapid charging.');
			await expect(page.locator('.gd-charger-card h3')).toHaveText(chargerSlugs.map((_, i) => `Charger fixture ${i + 1}`));
			for (const [index, slug] of chargerSlugs.entries()) {
				const card = page.locator('.gd-charger-card').nth(index);
				await expect(card.locator('.gd-charger-card__product')).toHaveAttribute('href', `${baseURL}/evcharger/${slug}/`);
				await expect(card.getByRole('link', { name: `Request Quote — Charger fixture ${index + 1}`, exact: true })).toHaveAttribute('href', `${baseURL}/evcharger/${slug}/`);
				await expect(card.locator('img')).toHaveAttribute('loading', 'lazy');
				expect(await card.locator('img').evaluate(el => getComputedStyle(el).objectFit)).toBe('contain');
			}
			await expect(page.locator('.gd-cta-band h2')).toHaveText('Aftercare & Fleet Charging');
			await expect(page.locator('.gd-cta-band p')).toHaveText('Fleet operators, stay in control of your EV charging costs and uptime, without sacrificing sustainability. Let’s build a smarter charging solution tailored to your fleet. We’ll help you work out what makes sense before anything is installed.');
			await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/`);
			await expect(page.locator('#fleet-process h3')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(page.locator('#fleet-process .gd-process__steps p')).toHaveText(steps);
			await expect(page.locator('.gd-process-media img')).toHaveCount(2);
			await expect(page.locator('.gd-process-media img').first()).toHaveAttribute('loading', 'lazy');
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
			await page.evaluate(async () => {
				await document.fonts.ready;
				document.querySelector('.gd-article-track')?.scrollTo({ left: 0, behavior: 'instant' });
				window.scrollTo(0, 0);
			});
			await testInfo.attach(`fleet-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('Fleet retains edited content, filters stale selections and honours cleared media and protected pages', async ({ page, baseURL }) => {
	await page.goto('/gd-fleet-edited/');
	await expect(page.locator('main h1')).toHaveText('Charging that fits our fleet.');
	await expect(page.locator('main h1 em, .gd-page-lead')).toHaveCount(0);
	await expect(page.locator('.gd-feature-grid p').first()).toHaveText('Reviewed fleet operating costs.');
	await expect(page.locator('#fleet-process h3').first()).toHaveText('Assess our fleet');
	await expect(page.locator('.gd-charger-card h3')).toHaveText(['Charger fixture 6', 'Charger fixture 1']);
	await expect(page.locator('.gd-article-track h3')).toHaveText(['Guide fixture 10', 'Guide fixture 1']);
	await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/?from=fleet#project`);
	await page.goto('/gd-fleet-empty/');
	await expect(page.locator('.gd-page-hero__media, .gd-recommended-chargers, .gd-process-media, .gd-page-insights')).toHaveCount(0);
	await expect(page.locator('.gd-feature-grid article')).toHaveCount(4);
	await expect(page.locator('main .wpcf7, .gd-form-section')).toHaveCount(0);
	await page.goto('/gd-fleet-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-fleet-no-core');
	await expect(page.locator('main h2')).toHaveCount(0);
	await page.goto('/gd-fleet-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('main')).not.toContainText('Protected Fleet content must stay private.');
	await expect(page.locator('.gd-feature-grid, .gd-charger-card, .gd-process, .gd-page-insights')).toHaveCount(0);
});

test('Fleet article controls, local enquiry CTA and charger quote journey work with reduced motion', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/fleet-charging-solutions/');
	await expect(page.locator('script[src*="greendawn-home-motion"], script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
	await page.getByRole('button', { name: 'Next articles' }).click();
	await expect.poll(() => page.locator('.gd-article-track').evaluate(el => el.scrollLeft)).toBeGreaterThan(0);
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await page.locator('.gd-cta-band').getByRole('link', { name: 'Start Your Journey' }).click();
	await expect(page).toHaveURL(/\/enquire\/$/);
	await expect(page.locator('.charger-enquiry-context')).toHaveCount(0);
	await page.goto('/fleet-charging-solutions/');
	await page.getByRole('link', { name: 'Request Quote — Charger fixture 1', exact: true }).click();
	await expect(page).toHaveURL(/\/evcharger\/hellonext-h1-22\/$/);
	await page.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
	await expect(page.getByRole('dialog').locator('.lead-capture__product-context')).toContainText('Charger fixture 1');
	await expect(page.getByRole('dialog').getByRole('heading', { name: 'How should we reach you?' })).toBeVisible();
});
