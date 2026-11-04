import { expect, test, type Locator } from '@playwright/test';

const blocks = {
	quote: { selector: '.wp-block-quote:not(.is-style-plain)', properties: ['borderInlineStartWidth', 'borderInlineStartStyle', 'borderInlineStartColor', 'paddingInlineStart', 'fontFamily', 'fontSize', 'color'] },
	plainQuote: { selector: '.wp-block-quote.is-style-plain', properties: ['borderInlineStartWidth', 'paddingInlineStart'] },
	code: { selector: '.wp-block-code', properties: ['borderTopWidth', 'borderTopStyle', 'borderTopColor', 'borderRadius', 'paddingTop', 'paddingLeft', 'fontFamily', 'fontSize', 'lineHeight'] },
	pullquote: { selector: '.wp-block-pullquote', properties: ['borderTopWidth', 'borderBottomWidth', 'borderInlineStartWidth', 'borderTopStyle', 'borderTopColor'] },
	citation: { selector: '.wp-block-pullquote cite', properties: ['fontStyle', 'fontSize', 'textTransform', 'fontFamily', 'color'] },
};

async function presentation(content: Locator) {
	const result = {} as Record<keyof typeof blocks, Record<string, string>>;
	for (const [name, { selector, properties }] of Object.entries(blocks)) {
		const block = content.locator(selector).first();
		await expect(block).toBeAttached();
		result[name as keyof typeof blocks] = await block.evaluate((element, names) => {
			const style = getComputedStyle(element);
			return Object.fromEntries(names.map((name) => [name, style[name as keyof CSSStyleDeclaration] as string]));
		}, properties);
	}
	return result;
}

async function expectContentAlignment(content: Locator) {
	const heading = await content.locator('h2').first().boundingBox();
	expect(heading).toBeTruthy();
	for (const selector of ['.wp-block-quote:not(.is-style-plain)', '.wp-block-code', '.wp-block-pullquote']) {
		const block = await content.locator(selector).first().boundingBox();
		expect(block).toBeTruthy();
		if (!heading || !block) throw new Error('Saved content bounds unavailable');
		expect(Math.abs(block.x - heading.x), `${selector} left alignment`).toBeLessThanOrEqual(1);
		expect(Math.abs(block.width - heading.width), `${selector} content width`).toBeLessThanOrEqual(1);
	}
}

test('saved core blocks share actual presentation in frontend and editor', async ({ page, baseURL }, testInfo) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Authenticated checks only use the disposable integration fixture.');
	await page.goto('/editor-parity/');
	const content = page.locator('.entry-content');
	await expect(content.locator('h2')).toHaveText('Heading two');
	await expect(content.locator('ul li')).toHaveCount(2);
	const frontend = await presentation(content);
	await expectContentAlignment(content);
	expect(frontend.quote.borderInlineStartWidth).toBe('4px');
	expect(frontend.plainQuote.borderInlineStartWidth).toBe('0px');
	expect(frontend.code.borderTopWidth).toBe('1px');
	expect(frontend.pullquote.borderTopWidth).toBe('4px');
	expect(frontend.pullquote.borderBottomWidth).toBe('4px');
	expect(frontend.citation.fontStyle).toBe('normal');
	expect(frontend.citation.textTransform).toBe('uppercase');
	await expect(content.locator('.wp-block-code')).toContainText('<p>Code sample</p>');
	const postId = (await page.locator('body').getAttribute('class'))?.match(/\bpage-id-(\d+)\b/)?.[1];
	expect(postId).toBeTruthy();
	await testInfo.attach('saved-blocks-frontend', { body: await content.screenshot(), contentType: 'image/png' });
	await testInfo.attach('pullquote-frontend', { body: await content.locator('.wp-block-pullquote').screenshot(), contentType: 'image/png' });

	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto(`/wp-admin/post.php?post=${postId}&action=edit`);
	await expect.poll(async () => {
		for (const frame of page.frames()) if (await frame.locator('.editor-styles-wrapper').count()) return true;
		return false;
	}, { timeout: 30000 }).toBe(true);
	const welcome = page.getByRole('dialog');
	if (await welcome.count()) {
		await welcome.getByRole('button', { name: 'Close', exact: true }).click();
		await expect(welcome).toBeHidden();
	}
	const frame = page.frames().find((candidate) => candidate.name() === 'editor-canvas') ?? page.mainFrame();
	const canvas = frame.locator('.editor-styles-wrapper').first();
	await expect.poll(() => presentation(canvas)).toEqual(frontend);
	await expectContentAlignment(canvas);
	const invalid = await page.evaluate(() => {
		type Block = { name: string; isValid?: boolean; innerBlocks?: Block[] };
		const wp = (window as unknown as { wp: { data: { select: (name: string) => { getBlocks: () => Block[] } } } }).wp;
		const collect = (items: Block[]): string[] => items.flatMap((item) => [...(item.isValid === false ? [item.name] : []), ...collect(item.innerBlocks ?? [])]);
		return collect(wp.data.select('core/block-editor').getBlocks());
	});
	expect(invalid).toEqual([]);
	await testInfo.attach('saved-blocks-editor', { body: await canvas.screenshot(), contentType: 'image/png' });
	await testInfo.attach('pullquote-editor', { body: await canvas.locator('.wp-block-pullquote').screenshot(), contentType: 'image/png' });
});

test('saved block defaults reflow at 320px and preserve logical quote borders', async ({ page }) => {
	await page.setViewportSize({ width: 320, height: 800 });
	await page.goto('/editor-parity/');
	const quote = page.locator('.entry-content .wp-block-quote:not(.is-style-plain)');
	await expect(quote).toHaveCSS('border-left-width', '4px');
	await page.locator('.entry-content details summary').click();
	await expect(page.getByText('Details body.', { exact: true })).toBeVisible();
	await page.locator('html').evaluate((element) => { element.setAttribute('dir', 'rtl'); });
	await expect(quote).toHaveCSS('border-right-width', '4px');
	await expect(quote).toHaveCSS('border-left-width', '0px');
	expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBe(true);
});
