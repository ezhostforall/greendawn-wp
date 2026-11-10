import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const chargerSlugs = ['hellonext-h1-22', 'project-ev-ac-pro-earth-dual-7-22kw', 'simpson-partners-7-22kw', 'sync-ev-22kw', 'sync-ev-22kw-2', 'easee-charger-22kw'];
const benefits = [
	'Charging on-site is up to 4–5x cheaper than relying on public chargers, giving you predictable, controlled costs.',
	'Staff have consistent access to charging without delays, queues, or fluctuating rates.',
	'Set charging policies, balance usage between staff and fleet vehicles, and optimise your site’s power capacity.',
	'Reduces your business’s carbon footprint and demonstrates commitment to green initiatives.',
];
const steps = [
	'Every project begins with a consultation and then an on-site survey. We assess your power supply, layout, and access requirements to ensure accurate scoping.',
	'Based on the survey, we deliver a clear proposal including costs, charger recommendations, grant eligibility, and timeline. Once approved, we confirm project details and move to delivery.',
	'Our experienced engineers carry out the installation with minimal disruption, ensuring compliance, safety, and a high standard of workmanship throughout.',
	'After installation, we continue to support you with maintenance, software updates, and fault response, ensuring your charging infrastructure remains reliable and easy to manage.',
];

test('Workplace preserves its complete source content and local destinations without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/workplace-charging/');
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('EV Charging For');
			await expect(page.locator('main h1 em')).toHaveText('Your Workplace');
			await expect(page.locator('main')).not.toContainText('Do not append this legacy layout');
			await expect(page.locator('.gd-page-lead')).toHaveText('Take Control of Workplace Charging: Smarter, Accountable, and Always Available for Your Staff');
			const image = page.locator('.gd-page-hero__media img');
			await expect(image).toHaveAttribute('loading', 'eager');
			await expect(image).toHaveAttribute('fetchpriority', 'high');
			await expect(image).toHaveAttribute('srcset', /[0-9]+w/);
			await expect(page.locator('.gd-feature-grid h3')).toHaveText(['Cost Savings', 'Convenience & Reliability', 'Energy Management', 'Sustainability']);
			await expect(page.locator('.gd-feature-grid p')).toHaveText(benefits);
			for (const paragraph of await page.locator('.gd-feature-grid p').all()) await expect(paragraph).toBeVisible();
			await expect(page.locator('.gd-charger-card h3')).toHaveText(chargerSlugs.map((_, i) => `Charger fixture ${i + 1}`));
			for (const [index, slug] of chargerSlugs.entries()) {
				const card = page.locator('.gd-charger-card').nth(index);
				await expect(card.locator('.gd-charger-card__product')).toHaveAttribute('href', `${baseURL}/evcharger/${slug}/`);
				await expect(card.getByRole('link', { name: `Request Quote — Charger fixture ${index + 1}`, exact: true })).toHaveAttribute('href', `${baseURL}/evcharger/${slug}/`);
				await expect(card.locator('img')).toHaveAttribute('loading', 'lazy');
				expect(await card.locator('img').evaluate(el => getComputedStyle(el).objectFit)).toBe('contain');
			}
			await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/`);
			await expect(page.locator('#workplace-process h3')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(page.locator('#workplace-process .gd-process__steps p')).toHaveText(steps);
			await expect(page.locator('.gd-process-media img')).toHaveCount(2);
			await expect(page.locator('.gd-process-media img').first()).toHaveAttribute('loading', 'lazy');
			await expect(page.locator('.gd-form-section h2')).toHaveCount(0);
			await expect(page.getByRole('region', { name: 'Project enquiry', exact: true })).toBeVisible();
			await expect(page.locator('.gd-form-panel form')).toHaveCount(1);
			await expect(page.getByRole('button', { name: 'Send enquiry' })).toBeVisible();
			await expect(page.locator('.gd-article-track .gd-insight-card')).toHaveCount(10);
			await expect(page.locator('.gd-article-track h3')).toHaveText(Array.from({ length: 10 }, (_, i) => `Guide fixture ${i + 1}`));
			await expect(page.getByRole('button', { name: 'Next articles' })).toBeHidden();
			for (const href of await page.locator('main a[href]').evaluateAll(links => links.map(a => (a as HTMLAnchorElement).href))) expect(href).toMatch(new RegExp(`^${baseURL}/`));
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			// Load native lazy media before capturing the complete layout.
			for (const media of await page.locator('main img').all()) await media.scrollIntoViewIfNeeded();
			await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
			await testInfo.attach(`workplace-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('Workplace honours edited content, explicit blanks and safe ordered native selections', async ({ page, baseURL }) => {
	await page.goto('/gd-workplace-edited/');
	await expect(page.locator('main h1')).toHaveText('Charging for our team.');
	await expect(page.locator('main h1 em, .gd-page-lead')).toHaveCount(0);
	await expect(page.locator('.gd-feature-grid p').first()).toHaveText('Reviewed workplace cost description.');
	await expect(page.locator('#workplace-process h3').first()).toHaveText('Assess');
	await expect(page.locator('.gd-charger-card h3')).toHaveText(['Charger fixture 3', 'Charger fixture 1']);
	await expect(page.locator('.gd-article-track h3')).toHaveText(['Guide fixture 10', 'Guide fixture 1']);
	await expect(page.locator('.gd-form-section h2')).toHaveText('Discuss your workplace');
	await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/?from=workplace#project`);
	await page.goto('/gd-workplace-empty/');
	await expect(page.locator('.gd-page-hero__media, .gd-charger-card, .gd-process-media, .gd-page-insights')).toHaveCount(0);
	await expect(page.getByText('The enquiry form is currently unavailable.', { exact: true })).toBeVisible();
	await page.goto('/gd-workplace-invalid-form/');
	await expect(page.locator('.gd-form-panel form')).toHaveCount(0);
	await expect(page.getByText('The enquiry form is currently unavailable.', { exact: true })).toBeVisible();
	await page.goto('/gd-workplace-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-workplace-no-core');
	await expect(page.locator('main h2')).toHaveCount(0);
});

test('Workplace form feedback, article controls and charger-to-quote journey remain accessible', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/workplace-charging/');
	await expect(page.locator('script[src*="greendawn-home-motion"], script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
	await page.getByRole('button', { name: 'Next articles' }).click();
	await expect.poll(() => page.locator('.gd-article-track').evaluate(el => el.scrollLeft)).toBeGreaterThan(0);
	await page.getByRole('button', { name: 'Send enquiry' }).click();
	await expect(page.locator('.gd-form-panel [aria-invalid="true"]').first()).toBeVisible();
	await expect(page.locator('.gd-form-panel .wpcf7-response-output')).toBeVisible();
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await page.getByRole('link', { name: 'Request Quote — Charger fixture 1', exact: true }).click();
	await expect(page).toHaveURL(/\/evcharger\/hellonext-h1-22\/$/);
	await page.getByRole('link', { name: 'Request Quote', exact: true }).first().click();
	await expect(page.getByRole('dialog').locator('.lead-capture__product-context')).toContainText('Charger fixture 1');
	await expect(page.getByRole('dialog').getByRole('heading', { name: 'How should we reach you?' })).toBeVisible();
});
