import { expect, test } from '@playwright/test';

test.use({ javaScriptEnabled: false });

test('published service cards lead to responsive detail pages and back to Services', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 320, height: 850 });
	await page.goto('/services/');
	await expect(page.getByRole('heading', { level: 1 })).toHaveText('Services');
	await expect(page.locator('.feature-hero').getByRole('navigation', { name: 'Breadcrumb' })).toBeVisible();
	const cards = page.locator('.service-card');
	await expect(cards.getByRole('heading', { level: 2 })).toHaveText(['Hosting', 'Web Design']);
	const cardImage = cards.getByRole('img', { name: 'Service fixture image' });
	await expect(cardImage).toHaveAttribute('srcset', /300w.*768w|768w.*300w/);
	await expect(cardImage).toHaveAttribute('sizes', /50vw/);
	await expect(cardImage).toHaveCSS('aspect-ratio', '16 / 9');
	await cardImage.scrollIntoViewIfNeeded();
	await expect.poll(() => cardImage.evaluate((image: HTMLImageElement) => image.currentSrc)).toMatch(/-300x167\.jpg$/);

	await page.setViewportSize({ width: 1280, height: 850 });
	await cardImage.scrollIntoViewIfNeeded();
	await expect.poll(() => cardImage.evaluate((image: HTMLImageElement) => image.currentSrc)).toMatch(/-768x429\.jpg$/);

	await cards.getByRole('link', { name: 'Web Design' }).click();
	await expect(page).toHaveURL(/\/services\/web-design\/$/);
	const heading = page.getByRole('heading', { level: 1 });
	await expect(heading).toHaveText('Web Design');
	const heroImage = page.locator('.feature-hero').getByRole('img', { name: 'Service fixture image' });
	await expect(heroImage).toHaveAttribute('loading', 'eager');
	await expect(heroImage).toHaveAttribute('srcset', /1024w/);
	await expect(heroImage).toHaveAttribute('sizes', /44rem/);
	await expect(heroImage).toHaveCSS('aspect-ratio', '4 / 3');

	for (const width of [320, 1280]) {
		await page.setViewportSize({ width, height: 850 });
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
		const title = await heading.boundingBox();
		const image = await heroImage.boundingBox();
		if (!title || !image) throw new Error('The service hero title and image must be visible.');
		if (width === 320) {
			expect(image.y).toBeGreaterThan(title.y + title.height);
		} else {
			expect(image.x).toBeGreaterThanOrEqual(title.x + title.width);
		}
		await testInfo.attach(`service-detail-${width}`, { body: await page.screenshot(), contentType: 'image/png' });
	}

	const breadcrumb = page.locator('.feature-hero').getByRole('navigation', { name: 'Breadcrumb' });
	await expect(breadcrumb.locator('[aria-current="page"]')).toHaveText('Web Design');
	await breadcrumb.getByRole('link', { name: 'Services', exact: true }).click();
	await expect(page).toHaveURL(/\/services\/$/);
	await cards.getByRole('link', { name: 'Hosting' }).click();
	await expect(heading).toHaveText('Hosting');
	await expect(page.locator('.feature-hero__media, .feature-cta, .service-detail__content')).toHaveCount(0);
});
