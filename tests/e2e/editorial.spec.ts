import { expect, test, type Locator } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const typography = (locator: Locator) => locator.evaluate(element => {
	const style = getComputedStyle(element);
	return [style.fontFamily, style.fontSize, style.fontWeight, style.lineHeight, style.color];
});

test('blog cards, pagination and article headers work at narrow and wide widths', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 320, height: 850 });
	await page.goto('/blog/');
	const hero = page.locator('.editorial-hero');
	const indexHero = page.locator('.blog-index .gd-page-hero');
	await expect(indexHero.getByRole('heading', { level: 1 })).toHaveText('Our Content');
	await expect(indexHero.getByRole('navigation', { name: 'Breadcrumb' })).toBeVisible();
	const card = page.locator('.post-card').filter({ hasText: 'Editorial sample' });
	await expect(card.locator('img')).toHaveAttribute('srcset', /300w/);
	await expect(card.locator('figure')).toHaveCSS('aspect-ratio', '16 / 10');
	await expect(card.locator('.entry-summary')).toHaveText('A complete editorial excerpt with a final sentence for the archive.');
	await expect(card.getByRole('link', { name: 'Read more: Editorial sample' })).toHaveAttribute('href', /\/editorial-sample\/$/);
	await expect(page.locator('.post-grid')).toHaveCSS('grid-template-columns', /^\d+(\.\d+)?px$/);
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(320);
	await testInfo.attach('blog-index-320', { body: await page.screenshot(), contentType: 'image/png' });

	await page.locator('.pagination').getByRole('link', { name: /next/i }).click();
	await expect(page).toHaveURL(/\/blog\/page\/2\/$/);
	await expect(page.locator('.pagination [aria-current="page"]')).toHaveText('2');
	await page.goBack();
	await card.getByRole('link', { name: 'Editorial sample', exact: true }).click();
	await expect(hero.getByRole('heading', { level: 1 })).toHaveText('Editorial sample');
	await expect(hero.locator('time')).toHaveAttribute('datetime', /^\d{4}-\d{2}-\d{2}/);
	await expect(hero.locator('.entry-thumbnail img')).toHaveAttribute('loading', 'eager');
	await expect(hero.locator('.entry-thumbnail img')).toHaveAttribute('fetchpriority', 'high');
	await expect(hero.getByText(/^By /)).toBeVisible();
	await expect(page.locator('.entry-footer')).toContainText('Installation notes');
	await expect(hero.getByRole('navigation', { name: 'Breadcrumb' }).getByRole('link', { name: 'Blog' })).toHaveAttribute('href', /\/blog\/$/);
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(320);

	await page.setViewportSize({ width: 1280, height: 850 });
	for (const selector of ['.entry-header', '.entry-thumbnail']) {
		const bounds = await hero.locator(selector).boundingBox();
		expect(bounds?.width).toBeCloseTo(1232, 0);
		expect(bounds?.x).toBeCloseTo(24, 0);
	}
	const postBody = await page.locator('.entry-content > p').boundingBox();
	expect(postBody?.width).toBeCloseTo(672, 0);
	await page.evaluate(async () => { await document.fonts.ready; await document.querySelector<HTMLImageElement>('.entry-thumbnail img')?.decode(); });
	await testInfo.attach('blog-post-1280', { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
	await page.goto('/policy-example/');
	await expect(hero.getByRole('heading', { level: 1 })).toHaveText('Policy example');
	await expect(hero.locator('[aria-current="page"]')).toHaveText('Policy example');
	await expect(page.locator('.entry-content')).toContainText('Policy body fixture.');
	await expect(hero.locator('time, .entry-thumbnail')).toHaveCount(0);
	const titleLeft = await hero.locator('h1').evaluate(element => element.getBoundingClientRect().left);
	const bodyLeft = await page.locator('.entry-content > p').evaluate(element => element.getBoundingClientRect().left);
	expect(titleLeft).toBeCloseTo(bodyLeft, 0);
	expect(titleLeft).toBeCloseTo(80, 0);
	const bodyWidth = await page.locator('.entry-content > p').evaluate(element => element.getBoundingClientRect().width);
	expect(bodyWidth).toBeCloseTo(1120, 0);
	await page.setViewportSize({ width: 320, height: 850 });
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(320);
});

test('native editorial categories retain filtering, descriptions and navigation without JavaScript', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		await page.setViewportSize({ width: 1440, height: 950 });
		await page.goto('/blog/');
		const categories = page.getByRole('navigation', { name: 'Article categories' });
		const guides = categories.getByRole('link', { name: 'Guides', exact: true });
		const editorials = categories.getByRole('link', { name: 'Editorials', exact: true });
		await expect(guides).toHaveAttribute('href', /\/category\/guides\/$/);
		await expect(editorials).toHaveAttribute('href', /\/category\/editorial\/$/);
		await expect(page.locator('.post-grid')).toHaveCSS('grid-template-columns', /^(?:[\d.]+px ){2}[\d.]+px$/);
		await page.evaluate(async () => {
			await document.fonts.ready;
			await Promise.all([...document.querySelectorAll<HTMLImageElement>('.gd-editorial-card img')].map(img => img.decode()));
		});
		await testInfo.attach('blog-index-1440', { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		await page.setViewportSize({ width: 834, height: 950 });
		await expect(page.locator('.post-grid')).toHaveCSS('grid-template-columns', /^[\d.]+px [\d.]+px$/);
		await guides.click();
		await expect(page.locator('main h1')).toHaveText('Guides');
		await expect(guides).toHaveAttribute('aria-current', 'page');
		await expect(page.locator('.gd-editorial-description')).toHaveText('Installation guides and practical advice.');
		await expect(page.locator('.gd-editorial-card h2')).toHaveText(['Editorial sample']);
		await expect(page.locator('.gd-editorial-card')).not.toContainText('Hello starter');
		await expect(categories.getByRole('link', { name: 'All articles' })).toHaveAttribute('href', /\/blog\/$/);
		await editorials.click();
		await expect(page.locator('main h1')).toHaveText('Editorials');
		await expect(page.locator('.gd-editorial-card h2')).toHaveText(['Hello starter']);
		await page.locator('.gd-editorial-card h2 a').click();
		await expect(page.locator('.editorial-hero h1')).toHaveText('Hello starter');
		await expect(page.locator('.editorial-hero .entry-thumbnail')).toHaveCount(0);
		await page.goto('/editorial-protected/');
		await expect(page.locator('.post-password-form')).toBeVisible();
		await expect(page.locator('.editorial-hero .entry-thumbnail')).toHaveCount(0);
		await expect(page.locator('main')).not.toContainText('Protected editorial fixture body.');
	} finally { await context.close(); }
});

test('Blog and article pages pass accessibility checks at mobile width', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 375, height: 950 });
	for (const path of ['/blog/', '/editorial-sample/']) {
		await page.goto(path);
		const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
		expect(results.violations).toEqual([]);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(375);
	}
	await page.evaluate(async () => { await document.fonts.ready; await document.querySelector<HTMLImageElement>('.entry-thumbnail img')?.decode(); });
	await testInfo.attach('blog-post-375', { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
});

test('saved post headings and paragraphs keep their typography in Gutenberg', async ({ page, context, baseURL }) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Editor credentials belong only to the disposable fixture.');
	await page.goto('/editorial-sample/');
	const postId = (await page.locator('body').getAttribute('class'))?.match(/\bpostid-(\d+)\b/)?.[1];
	expect(postId).toBeTruthy();

	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto(`/wp-admin/post.php?post=${postId}&action=edit`);
	const editor = page.frameLocator('iframe[name="editor-canvas"]').locator('.editor-styles-wrapper');
	await expect(editor.getByText('Heading two', { exact: true })).toBeVisible();
	const viewport = await editor.evaluate(() => ({ width: innerWidth, height: innerHeight }));
	const frontend = await context.newPage();
	await frontend.setViewportSize(viewport);
	await frontend.goto('/editorial-sample/');
	for (const text of ['Heading two', 'Saved content for editor and frontend comparison.']) {
		const expected = await typography(frontend.locator('.entry-content').getByText(text, { exact: true }));
		await expect.poll(() => typography(editor.getByText(text, { exact: true }))).toEqual(expected);
	}
	await frontend.close();
});
