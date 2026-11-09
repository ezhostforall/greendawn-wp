import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('About renders complete structured content without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/gd-about-default/');
			const main = page.locator('main');
			await expect(main.locator('h1')).toHaveCount(1);
			await expect(main.locator('h1 span')).toHaveText('EV Charging');
			await expect(main.locator('h1 em')).toHaveText('Done Properly');
			await expect(main).not.toContainText('Do not append this legacy layout');
			const image = page.locator('.gd-page-hero__media img');
			await expect(image).toHaveAttribute('loading', 'eager');
			await expect(image).toHaveAttribute('fetchpriority', 'high');
			await expect(image).toHaveAttribute('srcset', /1024w/);
			await expect(page.locator('#about-process h3')).toHaveText(['Consult', 'Design', 'Install', 'Manage']);
			await expect(page.locator('.gd-team-card h3')).toHaveText(['Member 1', 'Member 2', 'Member 3', 'Member 4', 'Member 5']);
			await expect(page.locator('.gd-team-card img')).toHaveCount(5);
			await expect(page.locator('.gd-team-card img').first()).toHaveAttribute('loading', 'lazy');
			await expect(page.locator('.gd-team-card').first().locator('img')).toHaveAttribute('alt', 'Member 1');
			await expect(page.locator('.gd-team-card').first()).toContainText('Second sentence retained in full.');
			await expect(page.locator('.gd-article-track .gd-insight-card')).toHaveCount(10);
			await expect(page.getByRole('button', { name: 'Next articles' })).toBeHidden();
			await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/`);
			await expect(page.getByRole('button', { name: 'Send fixture enquiry' })).toBeVisible();
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			await testInfo.attach(`about-${width}`, { body: await page.screenshot(), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('About selections retain their order, omit private records and honour saved blanks', async ({ page, baseURL }) => {
	await page.goto('/gd-about-edited/');
	await expect(page.locator('main h1')).toHaveText('Charging shaped around your organisation.');
	await expect(page.locator('main h1 em, .gd-page-lead')).toHaveCount(0);
	await expect(page.locator('#about-process h3').first()).toHaveText('Assess');
	await expect(page.locator('.gd-team-card h3')).toHaveText(['Member 2', 'Member 1']);
	await expect(page.locator('.gd-article-track h3').first()).toHaveText('Guide fixture 10');
	await expect(page.locator('.gd-cta-band a')).toHaveAttribute('href', `${baseURL}/enquire/?from=about#project`);
	const next = page.getByRole('button', { name: 'Next articles' });
	await next.click();
	await expect.poll(() => page.locator('.gd-article-track').evaluate(element => element.scrollLeft)).toBeGreaterThan(0);
	await page.goto('/gd-about-empty/');
	await expect(page.locator('.gd-page-hero__media, .gd-team-card, .gd-page-insights')).toHaveCount(0);
	await expect(page.getByText('The enquiry form is currently unavailable.', { exact: true })).toBeVisible();
	await page.goto('/gd-about-invalid-form/');
	await expect(page.locator('.gd-form-panel form')).toHaveCount(0);
	await expect(page.getByText('The enquiry form is currently unavailable.', { exact: true })).toBeVisible();
	await page.goto('/gd-about-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-about-no-core');
	await expect(page.locator('main h2')).toHaveCount(0);
});

test('About has accessible contrast and form feedback without loading homepage motion', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.goto('/gd-about-default/');
	await expect(page.locator('script[src*="greendawn-home-motion"], script[src*="ScrollTrigger"]')).toHaveCount(0);
	await page.getByRole('button', { name: 'Send fixture enquiry' }).click();
	await expect(page.locator('.gd-form-panel [aria-invalid="true"]').first()).toBeVisible();
	await expect(page.locator('.gd-form-panel .wpcf7-response-output')).toBeVisible();
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
});
