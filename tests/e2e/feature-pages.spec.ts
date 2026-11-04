import { expect, test } from '@playwright/test';

for (const template of ['about', 'contact']) {
	test(`${template} uses the shared hero and preserves its content fallback`, async ({ page }, testInfo) => {
		await page.setViewportSize({ width: 320, height: 850 });
		await page.goto(`/${template}-template/`);
		const hero = page.locator('.feature-hero');
		const heading = hero.getByRole('heading', { level: 1 });
		const image = hero.getByRole('img', { name: 'Service fixture image' });
		await expect(heading).toBeVisible();
		await expect(image).toHaveAttribute('loading', 'eager');
		await expect(image).toHaveAttribute('srcset', /1024w/);
		await expect(image).toHaveCSS('aspect-ratio', '4 / 3');
		await expect(page.locator('main')).toHaveCSS('padding-block-start', '0px');
		await expect(page.getByText(template === 'about' ? 'About story fallback.' : 'The enquiry form is currently unavailable.', { exact: true })).toBeVisible();

		for (const width of [320, 1280]) {
			await page.setViewportSize({ width, height: 850 });
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			const titleBox = await heading.boundingBox();
			const imageBox = await image.boundingBox();
			if (!titleBox || !imageBox) throw new Error('Expected a visible title and hero image.');
			if (width === 320) {
				expect(imageBox.y).toBeGreaterThan(titleBox.y + titleBox.height);
			} else {
				expect(imageBox.x).toBeGreaterThanOrEqual(titleBox.x + titleBox.width);
			}
			await testInfo.attach(`${template}-${width}`, { body: await page.screenshot(), contentType: 'image/png' });
		}
	});
}
