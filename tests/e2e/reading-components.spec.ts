import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('reading components reflow, preserve content and work without JavaScript', async ({ browser, baseURL }, testInfo) => {
	test.setTimeout(90_000);
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [1440, 1024, 834, 393, 320]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/reading-components/');
			const main = page.locator('main');
			await expect(main.locator('h1')).toHaveText('Shared component review');
			await expect(main.locator('.gd-editorial-meta__author')).toHaveText('Greendawn Power Limited');
			await expect(main.locator('.gd-editorial-meta time')).toHaveAttribute('datetime', '2024-01-01');
			await expect(main.locator('.gd-page-hero__media img')).toHaveCSS('object-position', '50% 20%');
			await expect(main.locator('.gd-guide-card')).toHaveCount(4);
			if (width === 1440) {
				expect((await main.locator('.gd-guide-card--featured').boundingBox())?.height).toBeLessThan(600);
			}
			await expect(main).not.toContainText('Hidden component draft');
			await expect(main).not.toContainText('Hidden component password');
			await expect(main.locator('.gd-reading-section')).toHaveCount(3);
			await expect(main.locator('.gd-reading-table caption')).toHaveText('Site planning comparison');
			await expect(main.locator('.gd-reading-table th[scope="col"]')).toHaveCount(4);
			await expect(main.locator('.gd-reading-list--checklist li')).toHaveCount(2);
			await expect(main.locator('.gd-feature-grid--icons .gd-component-icon')).toHaveCount(3);
			await expect(main.locator('.gd-coverage-list li')).toHaveText(['Birmingham', 'Shropshire']);
			await expect(main.locator('.gd-story--dark .gd-page-text-link')).toHaveAttribute('href', `${baseURL}/gallery/`);
			const ids = await main.locator('[id]').evaluateAll(elements => elements.map(element => element.id));
			expect(new Set(ids).size).toBe(ids.length);
			for (const href of await main.locator('.gd-reading-contents a').evaluateAll(elements => elements.map(el => el.getAttribute('href')))) {
				expect(ids).toContain(href?.slice(1));
			}
			if (width <= 976) {
				await expect(main.locator('.gd-reading-contents__desktop')).toBeHidden();
				const mobile = main.locator('.gd-reading-contents__mobile');
				await mobile.locator('summary').focus();
				await page.keyboard.press('Enter');
				await expect(mobile).toHaveAttribute('open', '');
				await mobile.getByRole('link', { name: 'The next stage' }).click();
				await expect(page).toHaveURL(/#guide-supply-assessment-2$/);
			} else {
				await expect(main.locator('.gd-reading-contents__desktop')).toBeVisible();
				await expect(main.locator('.gd-reading-contents__mobile')).toBeHidden();
			}
			if (width === 393) {
				const table = main.locator('.gd-reading-table-scroll');
				expect(await table.evaluate(el => el.scrollWidth > el.clientWidth)).toBe(true);
				await table.focus();
				await page.keyboard.press('ArrowRight');
				await expect.poll(() => table.evaluate(el => el.scrollLeft)).toBeGreaterThan(0);
			}
			const faq = main.locator('.gd-faq-item').first();
			await faq.locator('summary').click();
			await expect(faq).not.toHaveAttribute('open');
			await faq.locator('summary').click();
			await expect(faq.locator('.gd-faq-answer')).toBeVisible();
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
			await testInfo.attach(`reading-components-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
		await page.goto('/reading-components/');
		const search = page.getByRole('search', { name: 'Guides', exact: true });
		const planningLink = page.getByRole('navigation', { name: 'Guide topics' }).getByRole('link', { name: 'Planning' });
		expect(new URL((await planningLink.getAttribute('href')) ?? '').searchParams.get('guide_search')).toBe('load & supply');
		await expect(search).toHaveAttribute('method', 'get');
		await search.getByRole('searchbox', { name: 'Search guides' }).fill('power and load');
		await search.getByRole('button', { name: 'Search', exact: true }).click();
		const url = new URL(page.url());
		expect(url.searchParams.get('guide_search')).toBe('power and load');
		expect(url.searchParams.get('guide_topic')).toBe('power');
		await expect(page.getByRole('navigation', { name: 'Guide topics' }).getByRole('link', { name: 'Planning' })).toHaveAttribute('href', /guide_topic=planning/);
		await expect(page.getByRole('link', { name: 'Clear filters', exact: true })).toHaveAttribute('href', `${baseURL}/reading-components/`);
		await expect(page.getByRole('link', { name: 'Discuss this project', exact: true })).toHaveAttribute('href', `${baseURL}/contact/`);
	} finally { await context.close(); }
});

test('empty variants and existing component defaults remain usable', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 393, height: 900 });
	await page.goto('/reading-components-empty/');
	await expect(page.locator('main .gd-page-hero--text')).toBeVisible();
	await expect(page.locator('main img, main .gd-editorial-meta, .gd-direct-answer, .gd-source-section, .gd-coverage-list, .gd-reading-section, .gd-reading-contents')).toHaveCount(0);
	await expect(page.locator('main')).toContainText('Empty optional components are omitted.');
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(393);
	await testInfo.attach('reading-empty-393', { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
	await page.goto('/reading-components-legacy/');
	await expect(page.locator('main .gd-page-hero__kicker, main .gd-editorial-meta, main [data-lead-capture-open], main .gd-story--dark, main .gd-guide-card')).toHaveCount(0);
	await expect(page.locator('main .gd-feature-number')).toHaveText('01');
	await expect(page.locator('main .gd-article-track .gd-insight-card')).toHaveCount(1);
	await expect(page.locator('main .gd-article-track')).toContainText('Component legacy article');
});

test('shared enquiry action opens the existing drawer and returns focus', async ({ page, baseURL }) => {
	await page.goto('/reading-components/');
	const trigger = page.getByRole('link', { name: 'Discuss this project', exact: true });
	await expect(trigger).toHaveAttribute('data-lead-entry-point', 'hero');
	await expect(trigger).toHaveAttribute('data-lead-intent', 'ev-charging');
	await trigger.click();
	const drawer = page.locator('dialog.lead-capture__dialog');
	await expect(drawer).toBeVisible();
	await page.keyboard.press('Escape');
	await expect(drawer).toBeHidden();
	await expect(trigger).toBeFocused();
	await expect(page).toHaveURL(`${baseURL}/reading-components/`);
});

test('reading components pass accessibility checks at mobile and desktop widths', async ({ page }) => {
	for (const width of [393, 1440]) {
		await page.setViewportSize({ width, height: 950 });
		await page.goto('/reading-components/');
		const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
		expect(results.violations).toEqual([]);
	}
});
