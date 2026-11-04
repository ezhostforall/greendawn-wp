import { expect, test, type Locator } from '@playwright/test';

async function sample(canvas: Locator) {
	return canvas.evaluate((element) => {
		const style = getComputedStyle(element);
		return Object.fromEntries(['--color-text', '--color-accent', '--font-body', '--space-m', '--radius', '--line-height-body'].map((name) => [name, style.getPropertyValue(name).trim()]));
	});
}

test('WordPress exposes the shared token values on the frontend', async ({ page }, testInfo) => {
	await page.goto('/');
	const values = await sample(page.locator('body'));
	expect(values['--color-text']).toBe('#1a1a1a');
	expect(values['--color-accent']).toBe('#262626');
	expect(values['--font-body']).toContain('Archivo');
	expect(values['--space-m']).toBe('1rem');
	expect(values['--radius']).toBe('0.375rem');
	expect(values['--line-height-body']).toBe('1.6');
	await testInfo.attach('frontend-token-page', { body: await page.screenshot(), contentType: 'image/png' });
});

test('two different brand overrides flow through aliases without changing component CSS', async ({ page }) => {
	await page.goto('/about/');
	for (const brand of [
		{ accent: '#236345', text: '#101b14', space: '1.25rem', font: 'Georgia, serif' },
		{ accent: '#6b27a0', text: '#201129', space: '1.5rem', font: 'Arial, sans-serif' },
	]) {
		await page.locator('body').evaluate((body, tokens) => {
			body.style.setProperty('--wp--preset--color--accent', tokens.accent);
			body.style.setProperty('--wp--preset--color--text', tokens.text);
			body.style.setProperty('--wp--preset--spacing--m', tokens.space);
			body.style.setProperty('--wp--custom--brand--body', tokens.font);
		}, brand);
		const values = await sample(page.locator('body'));
		expect(values['--color-accent']).toBe(brand.accent);
		expect(values['--color-text']).toBe(brand.text);
		expect(values['--space-m']).toBe(brand.space);
		expect(values['--font-body']).toBe(brand.font);
		await expect(page.locator('.site-header')).toHaveCSS('padding-inline-start', `${Number.parseFloat(brand.space) * 16}px`);
	}
});

test('linked post titles use the heading colour independently of accent', async ({ page }) => {
	await page.goto('/blog/');
	const title = page.locator('.card__title > a').first();
	await expect(title).toHaveCSS('color', 'rgb(26, 26, 26)');

	await page.locator('body').evaluate((body) => {
		body.style.setProperty('--wp--preset--color--accent', '#236345');
		body.style.setProperty('--wp--preset--color--heading', '#542991');
	});

	await expect(title).toHaveCSS('color', 'rgb(84, 41, 145)');
	expect((await sample(page.locator('body')))['--color-accent']).toBe('#236345');
});

test('link text and keyboard focus use separate brand roles from accent', async ({ page }) => {
	await page.goto('/about/');
	await page.locator('main').evaluate((main) => {
		const link = document.createElement('a');
		link.href = '#content';
		link.textContent = 'Brand token test link';
		main.prepend(link);
	});
	const link = page.getByRole('link', { name: 'Brand token test link' });
	await expect(link).toHaveCSS('color', 'rgb(54, 54, 54)');

	await page.locator('body').evaluate((body) => {
		for (const scope of [document.documentElement, body]) {
			scope.style.setProperty('--wp--preset--color--accent', '#236345');
			scope.style.setProperty('--wp--preset--color--link', '#542991');
			scope.style.setProperty('--wp--preset--color--focus', '#9a3412');
		}
	});

	await link.focus();
	await expect(link).toHaveCSS('color', 'rgb(84, 41, 145)');
	await expect(link).toHaveCSS('outline-color', 'rgb(154, 52, 18)');
	expect((await sample(page.locator('body')))['--color-accent']).toBe('#236345');
});

test('retired block font presets follow the current brand fonts', async ({ page }) => {
	await page.goto('/');
	const cases = [
		{ slug: 'system', family: 'Archivo' },
		{ slug: 'humanist', family: 'Archivo' },
		{ slug: 'serif', family: 'Bricolage Grotesque' },
	];

	await page.locator('main').evaluate((main, presets) => {
		for (const { slug } of presets) {
			const classBlock = document.createElement('p');
			classBlock.className = `has-${slug}-font-family`;
			classBlock.id = `retired-font-${slug}`;
			const inlineBlock = document.createElement('p');
			inlineBlock.id = `retired-inline-${slug}`;
			inlineBlock.style.fontFamily = `var(--wp--preset--font-family--${slug})`;
			main.append(classBlock, inlineBlock);
		}
	}, cases);

	for (const { slug, family } of cases) {
		await expect(page.locator(`#retired-font-${slug}`)).toHaveCSS('font-family', new RegExp(family));
		await expect(page.locator(`#retired-inline-${slug}`)).toHaveCSS('font-family', new RegExp(family));
	}
});

test('authenticated disposable block editor shares frontend tokens', async ({ page, baseURL }, testInfo) => {
	// Test credentials belong only to tests/integration/setup.sh, never hosted staging.
	test.skip(baseURL !== 'http://localhost:8080', 'Editor login is restricted to the disposable CI fixture.');
	await page.goto('/about/');
	const frontend = await sample(page.locator('body'));
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto('/wp-admin/post-new.php');
	await expect.poll(async () => {
		for (const frame of page.frames()) {
			if (await frame.locator('.editor-styles-wrapper').count()) return true;
		}
		return false;
	}, { timeout: 30000 }).toBe(true);
	const frame = page.frames().find((candidate) => candidate.name() === 'editor-canvas') ?? page.mainFrame();
	const canvas = frame.locator('.editor-styles-wrapper').first();
	await expect.poll(() => sample(canvas)).toEqual(frontend);
	const welcome = page.getByRole('dialog');
	if (await welcome.count()) {
		await welcome.getByRole('button', { name: 'Close', exact: true }).click();
		await expect(welcome).toBeHidden();
	}
	await testInfo.attach('editor-token-canvas', { body: await page.screenshot(), contentType: 'image/png' });
});
