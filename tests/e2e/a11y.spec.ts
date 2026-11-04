import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

for (const path of ['/', '/about/', '/?s=hello', '/this-page-does-not-exist/']) {
	test(`no axe violations on ${path}`, async ({ page }) => {
		await page.goto(path);
		const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
		expect(results.violations).toEqual([]);
	});
}
