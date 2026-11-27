import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('public reading templates without JavaScript', () => {
	test.use({ javaScriptEnabled: false });
	for (const width of [1920, 1440, 1024, 768, 393, 320]) {
		test(`hub, Guide and region reflow at ${width}px`, async ({ page }, testInfo) => {
			test.setTimeout(60_000);
			await page.setViewportSize({ width, height: 950 });
			for (const [path, selector, title] of [
				['/guides/', '.greendawn-guides', 'EV charging guides for businesses'],
				['/guides/template-guide-1/', '.greendawn-guide', 'Template site power guide'],
				['/template-region/', '.greendawn-region', 'Charging that fits your Midlands site'],
			] as const) {
				const response = await page.goto(path);
				expect(response?.status()).toBe(200);
				const root = page.locator(`main ${selector}`);
				await expect(root.locator('h1')).toHaveText(title);
				await expect(root.locator('.gd-page-hero__media img')).toBeVisible();
				const ids = await page.locator('main [id]').evaluateAll(elements => elements.map(element => element.id));
				expect(new Set(ids).size).toBe(ids.length);
				expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
				if (selector === '.greendawn-guide') {
					await expect(root.locator('.gd-editorial-meta__author')).toHaveText('Greendawn Power Limited');
					await expect(root.locator('.gd-editorial-meta time, .gd-source-list time')).toHaveCount(0);
					await expect(root.locator('.gd-reading-section')).toHaveCount(3);
					await expect(root.locator('.gd-reading-table th')).toHaveCount(4);
					await expect(root.locator('.gd-page-insights .gd-guide-card h3')).toHaveText(['Template charging guide 03', 'Template charging guide 02', 'Template charging guide 09']);
					await expect(root.getByRole('link', { name: 'Browse all guides', exact: true })).toHaveAttribute('href', /\/guides\/$/);
					if (width <= 976) {
						const contents = root.locator('.gd-reading-contents__mobile');
						await contents.locator('summary').focus(); await page.keyboard.press('Enter');
						await contents.getByRole('link', { name: 'Existing supply', exact: true }).click();
						await expect(page).toHaveURL(/#guide-assess-the-existing-supply$/);
					} else { await expect(root.locator('.gd-reading-contents__desktop')).toBeVisible(); }
					const faq = root.locator('.gd-faq-item').first();
					await faq.locator('summary').click(); await expect(faq).not.toHaveAttribute('open');
					await faq.locator('summary').click(); await expect(faq.locator('.gd-faq-answer')).toBeVisible();
				}
				if (selector === '.greendawn-region') {
					await expect(root.locator('.gd-feature-grid--icons article')).toHaveCount(3);
					await expect(root.locator('.gd-region-considerations .gd-reading-section h3')).toHaveCount(3);
					await expect(root.locator('.gd-coverage-list li')).toHaveCount(4);
				}
				await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
				await testInfo.attach(`${selector.slice(1)}-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
			}
		});
	}

	test('searches SCF text and combines native GET filters', async ({ page }) => {
		await page.goto('/guides/');
		await page.getByRole('navigation', { name: 'Guide topics' }).getByRole('link', { name: 'Template power (8)', exact: true }).click();
		await expect(page.locator('.gd-guides-results .gd-guide-card')).toHaveCount(8);
		await expect(page.locator('.gd-guides-feature')).toHaveCount(0);
		await page.getByRole('searchbox', { name: 'Search guides', exact: true }).fill('switchboardseeker');
		await page.getByRole('search', { name: 'Guides', exact: true }).getByRole('button', { name: 'Search', exact: true }).click();
		await expect(page).toHaveURL(/guide_topic=template-power/);
		await expect(page.locator('.gd-guides-results .gd-guide-card h3')).toHaveText('Template site power guide');
		await page.getByRole('navigation', { name: 'Guide topics' }).getByRole('link', { name: 'Template operation (4)', exact: true }).click();
		await expect(page.locator('.gd-guides-empty h3')).toHaveText('No matching guides');
		await expect(page.getByRole('searchbox', { name: 'Search guides', exact: true })).toHaveValue('switchboardseeker');
		await page.getByRole('link', { name: 'Clear filters', exact: true }).click();
		await expect(page).toHaveURL(/\/guides\/$/);
		await expect(page.locator('.gd-guides-feature')).toBeVisible();
		await page.getByRole('searchbox', { name: 'Search guides', exact: true }).fill('load & supply');
		await page.getByRole('search', { name: 'Guides', exact: true }).getByRole('button', { name: 'Search', exact: true }).click();
		await expect(page.locator('.gd-guides-results .gd-guide-card')).toHaveCount(11);
		const href = await page.getByRole('navigation', { name: 'Guide topics' }).getByRole('link', { name: 'Template power (8)', exact: true }).getAttribute('href');
		expect(new URL(href ?? '').searchParams.get('guide_search')).toBe('load & supply');
		await page.goto('/guides/?guide_topic=template-hidden');
		await expect(page.locator('.gd-guides-results .gd-guide-card')).toHaveCount(0);
		await expect(page.locator('.gd-guide-topics')).not.toContainText('Template hidden topic');
		await page.goto('/guides/?guide_search[]=malformed&guide_topic[]=malformed&guide_page[]=malformed');
		await expect(page.locator('main h1')).toHaveText('EV charging guides for businesses');
	});

	test('optional content disappears and native password forms gate structured data', async ({ page }) => {
		await page.goto('/template-region-minimal/');
		await expect(page.locator('main .gd-page-hero--text')).toBeVisible();
		await expect(page.locator('main img, main .gd-feature-grid, main .gd-coverage-list, main .gd-story--dark, main .gd-faq-section, main .gd-page-insights, main .gd-cta-band')).toHaveCount(0);
		await expect(page.locator('.gd-region-considerations')).toBeVisible();
		await page.goto('/template-hub-minimal/');
		await expect(page.locator('main .gd-page-hero--text')).toBeVisible();
		await expect(page.locator('.gd-guides-feature, main .gd-story, main .gd-cta-band')).toHaveCount(0);
		for (const path of ['/template-hub-protected/', '/guides/template-guide-15/']) {
			await page.goto(path);
			await expect(page.locator('main .post-password-form')).toBeVisible();
			await expect(page.locator('main .gd-guide-filters, main .gd-reading-section, main .gd-editorial-meta, main .gd-page-hero__media')).toHaveCount(0);
			await expect(page.locator('main')).not.toContainText('Plan charging around the vehicles');
		}
		await page.goto('/guides/template-guide-13/'); await expect(page.locator('.greendawn-guide')).toHaveCount(0);
	});
});

test('all three templates reuse the existing drawer launcher', async ({ page }) => {
	for (const path of ['/guides/', '/guides/template-guide-1/', '/template-region/']) {
		await page.goto(path);
		const trigger = page.locator('main .gd-cta-band [data-lead-capture-open]');
		await trigger.click();
		const drawer = page.locator('dialog.lead-capture__dialog');
		await expect(drawer).toBeVisible(); await page.keyboard.press('Escape');
		await expect(drawer).toBeHidden(); await expect(trigger).toBeFocused();
	}
});

test('hub, Guide and region pass mobile and desktop accessibility checks', async ({ page }) => {
	test.setTimeout(60_000);
	for (const width of [393, 1440]) {
		await page.setViewportSize({ width, height: 950 });
		for (const path of ['/guides/', '/guides/template-guide-1/', '/template-region/']) {
			await page.goto(path);
			const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
			expect(results.violations).toEqual([]);
		}
	}
});
