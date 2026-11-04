import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Locator, type Page } from '@playwright/test';

async function bounds(locator: Locator) {
	const box = await locator.boundingBox();
	if (!box) throw new Error('Layout element has no rendered bounds');
	return box;
}

async function noOverflow(page: Page) {
	expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBe(true);
}

async function columns(cards: Locator) {
	const boxes = await Promise.all((await cards.all()).map(bounds));
	const first = boxes[0];
	if (!first) throw new Error('No grid items');
	return boxes.filter((box) => Math.abs(box.y - first.y) < 2).length;
}

async function typography(content: Locator) {
	const result: Record<string, string[]> = {};
	for (const selector of ['.container--narrow h2', '.container--narrow h3', '.container--narrow h4', '.type-display', '.type-lead', '.type-small']) {
		result[selector] = await content.locator(selector).first().evaluate((element) => {
			const style = getComputedStyle(element);
			return [style.fontFamily, style.fontSize, style.lineHeight, style.fontWeight, style.color];
		});
	}
	return result;
}

test('narrow, wide and full sections use their intended widths and compositions', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 1440, height: 1000 });
	await page.goto('/layout-primitives/');
	const narrow = await bounds(page.locator('#layout-narrow'));
	const wide = await bounds(page.locator('#layout-wide'));
	const full = await bounds(page.locator('#layout-full'));
	expect(narrow.width).toBe(672);
	expect(wide.width).toBe(1152);
	expect(full.x).toBe(0);
	expect(full.width).toBe(1440);
	expect(await columns(page.locator('#layout-wide .card'))).toBe(3);
	expect(await columns(page.locator('#layout-row > p'))).toBe(3);
	const media = page.locator('#layout-media');
	expect(Math.abs((await bounds(media.locator('figure'))).y - (await bounds(media.locator('.layout-stack'))).y)).toBeLessThan(100);
	expect((await bounds(media.locator('figure'))).x).toBeLessThan((await bounds(media.locator('.layout-stack'))).x);
	await expect(media.locator('img')).toHaveJSProperty('complete', true);
	expect(await media.locator('img').evaluate((image: HTMLImageElement) => image.naturalWidth)).toBeGreaterThan(0);
	await noOverflow(page);
	await testInfo.attach('layout-desktop', { body: await page.screenshot(), contentType: 'image/png' });
	await testInfo.attach('layout-grid', { body: await page.locator('#layout-wide').screenshot(), contentType: 'image/png' });
	await testInfo.attach('layout-media', { body: await media.screenshot(), contentType: 'image/png' });
	const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
});

for (const width of [320, 640]) {
	test(`long content and compositions reflow at ${width}px`, async ({ page }, testInfo) => {
		await page.setViewportSize({ width, height: 800 });
		await page.goto('/layout-primitives/');
		await noOverflow(page);
		expect(await columns(page.locator('#layout-wide .card'))).toBe(width === 320 ? 1 : 2);
		const picture = await bounds(page.locator('#layout-media figure'));
		const copy = await bounds(page.locator('#layout-media .layout-stack'));
		expect(copy.y).toBeGreaterThanOrEqual(picture.y + picture.height);
		for (const selector of ['#layout-narrow', '#layout-wide', '#layout-row', '#layout-media', '.wp-block-table']) {
			const box = await bounds(page.locator(selector));
			expect(box.x).toBeGreaterThanOrEqual(0);
			expect(box.x + box.width).toBeLessThanOrEqual(width + 1);
		}
		const pre = page.locator('.wp-block-preformatted');
		await expect(pre).toHaveCSS('overflow-x', 'auto');
		await expect(pre).toContainText('a'.repeat(200));
		const overflows = await pre.evaluate((element) => element.scrollWidth > element.clientWidth);
		if (overflows) {
			await pre.evaluate((element) => { element.scrollLeft = element.scrollWidth; });
			expect(await pre.evaluate((element) => element.scrollLeft)).toBeGreaterThan(0);
		} else {
			// Core's pre-wrap is also valid: no text may be clipped vertically.
			expect(await pre.evaluate((element) => element.scrollHeight <= element.clientHeight + 1)).toBe(true);
			await expect(pre).toHaveCSS('white-space', /pre-wrap|break-spaces/);
		}
		await page.locator('html').evaluate((element) => { element.setAttribute('dir', 'rtl'); });
		await noOverflow(page);
		await testInfo.attach(`layout-${width}-rtl`, { body: await page.locator('#layout-media').screenshot(), contentType: 'image/png' });
	});
}

test('fluid type grows within its bounds and survives text enlargement', async ({ page }) => {
	await page.setViewportSize({ width: 320, height: 800 });
	await page.goto('/layout-primitives/');
	const smallTitle = Number.parseFloat(await page.locator('h1').evaluate((el) => getComputedStyle(el).fontSize));
	const smallHeading = Number.parseFloat(await page.locator('#layout-narrow h2').evaluate((el) => getComputedStyle(el).fontSize));
	await page.setViewportSize({ width: 1440, height: 1000 });
	const largeTitle = Number.parseFloat(await page.locator('h1').evaluate((el) => getComputedStyle(el).fontSize));
	const largeHeading = Number.parseFloat(await page.locator('#layout-narrow h2').evaluate((el) => getComputedStyle(el).fontSize));
	expect(smallTitle).toBeGreaterThanOrEqual(36);
	expect(largeTitle).toBeLessThanOrEqual(72);
	expect(largeTitle).toBeGreaterThan(smallTitle + 8);
	expect(smallHeading).toBeGreaterThanOrEqual(24);
	expect(largeHeading).toBeLessThanOrEqual(44);
	expect(largeHeading).toBeGreaterThan(smallHeading + 4);
	// A 640px CSS viewport represents 1280px desktop layout at 200% browser zoom.
	await page.setViewportSize({ width: 640, height: 800 });
	await page.locator('html').evaluate((element) => { element.style.fontSize = '200%'; });
	await noOverflow(page);
	expect(await columns(page.locator('#layout-wide .card'))).toBe(1);
	await expect(page.locator('#layout-narrow h6')).toBeVisible();
	await expect(page.locator('#layout-media .layout-stack')).toContainText('No fixed heights');
});

test('two server-side brand token sets reuse the same markup and compiled CSS', async ({ page }, testInfo) => {
	const stylesheets: string[] = [];
	for (const brand of [
		{ slug: 'layout-brand-a', font: 'Georgia', accent: 'rgb(23, 92, 74)', narrow: 608, wide: 1088, space: '20px' },
		{ slug: 'layout-brand-b', font: 'Arial', accent: 'rgb(91, 33, 182)', narrow: 736, wide: 1216, space: '24px' },
	]) {
		await page.setViewportSize({ width: 1440, height: 1000 });
		await page.goto(`/${brand.slug}/`);
		stylesheets.push(await page.locator('link[href*="/themes/greendawn/dist/assets/main-"]').getAttribute('href') ?? '');
		await expect(page.locator('body')).toHaveCSS('font-family', new RegExp(brand.font));
		await expect(page.locator('#layout-full')).toHaveCSS('background-color', brand.accent);
		await expect(page.locator('#layout-row')).toHaveCSS('gap', brand.space);
		expect((await bounds(page.locator('#layout-narrow'))).width).toBe(brand.narrow);
		expect((await bounds(page.locator('#layout-wide'))).width).toBe(brand.wide);
		await noOverflow(page);
		await testInfo.attach(`${brand.slug}-desktop`, { body: await page.screenshot(), contentType: 'image/png' });
		await page.setViewportSize({ width: 320, height: 800 });
		await noOverflow(page);
		expect(await columns(page.locator('#layout-wide .card'))).toBe(1);
		const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
		expect(results.violations).toEqual([]);
	}
	expect(stylesheets[0]).toBeTruthy();
	expect(stylesheets[1]).toBe(stylesheets[0]);
});

test('saved branded primitives keep their type and compositions inside the editor', async ({ page, context, baseURL }, testInfo) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Editor credentials belong only to the disposable fixture.');
	await page.goto('/layout-brand-a/');
	const postId = (await page.locator('body').getAttribute('class'))?.match(/\bpage-id-(\d+)\b/)?.[1];
	expect(postId).toBeTruthy();
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto(`/wp-admin/post.php?post=${postId}&action=edit`);
	await expect.poll(() => Promise.resolve(page.frames().some((frame) => frame.name() === 'editor-canvas')), { timeout: 30000 }).toBe(true);
	const welcome = page.getByRole('dialog');
	if (await welcome.count()) {
		await welcome.getByRole('button', { name: 'Close', exact: true }).click();
		await expect(welcome).toBeHidden();
	}
	const frame = page.frames().find((candidate) => candidate.name() === 'editor-canvas')!;
	const canvas = frame.locator('.editor-styles-wrapper');
	await expect(canvas.locator('.layout-grid .card')).toHaveCount(3);
	const viewport = await frame.evaluate(() => ({ width: innerWidth, height: innerHeight }));
	const frontend = await context.newPage();
	await frontend.setViewportSize(viewport);
	await frontend.goto('/layout-brand-a/');
	const expected = await typography(frontend.locator('.entry-content'));
	await expect.poll(() => typography(canvas)).toEqual(expected);
	expect(await columns(canvas.locator('.layout-grid .card'))).toBe(await columns(frontend.locator('.layout-grid .card')));
	expect(await columns(canvas.locator('.layout-row p'))).toBe(await columns(frontend.locator('.layout-row p')));
	for (const selector of ['.layout-grid', '.layout-row', '.layout-media', '.container--narrow']) {
		const padding = await frontend.locator(selector).evaluate((element) => getComputedStyle(element).paddingBlockStart);
		await expect(canvas.locator(selector)).toHaveCSS('padding-block-start', padding);
	}
	const editorPicture = await bounds(canvas.locator('.layout-media .wp-block-image'));
	const editorCopy = await bounds(canvas.locator('.layout-media .layout-stack'));
	expect(editorCopy.x).toBeGreaterThan(editorPicture.x);
	const invalid = await page.evaluate(() => {
		type Block = { name: string; isValid?: boolean; innerBlocks?: Block[] };
		const wp = (window as unknown as { wp: { data: { select: (name: string) => { getBlocks: () => Block[] } } } }).wp;
		const collect = (blocks: Block[]): string[] => blocks.flatMap((block) => [...(block.isValid === false ? [block.name] : []), ...collect(block.innerBlocks ?? [])]);
		return collect(wp.data.select('core/block-editor').getBlocks());
	});
	expect(invalid).toEqual([]);
	await testInfo.attach('layout-editor-grid', { body: await canvas.locator('.layout-grid').screenshot(), contentType: 'image/png' });
	await testInfo.attach('layout-editor-type', { body: await canvas.locator('.container--narrow').screenshot(), contentType: 'image/png' });
	await frontend.close();
});
