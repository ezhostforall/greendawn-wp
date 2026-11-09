import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

for (const path of ['/', '/about/', '/?s=hello', '/this-page-does-not-exist/']) {
	test(`no axe violations on ${path}`, async ({ page }) => {
		await page.goto(path);
		const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
		expect(results.violations).toEqual([]);
	});
}

test('insight card names contain their visible category and heading', async ({ page }) => {
	for (const width of [320, 1440]) {
		await page.setViewportSize({ width, height: 900 });
		for (const path of ['/greendawn-home/', '/greendawn-home-edited/', '/greendawn-home-no-insight-images/']) {
			await page.goto(path);
			const cards = page.locator('#insights .gd-insight-card');
			await expect(cards).toHaveCount(3);
			for (const card of await cards.all()) {
				const name = [await card.locator('p').innerText(), await card.locator('h3').innerText()].join(' ').replace(/\s+/g, ' ').trim();
				await expect(card).toHaveAccessibleName(new RegExp(`^${name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`, 'i'));
			}
			// Run the precise Lighthouse rule, which is not included in the WCAG-tagged scans above.
			const results = await new AxeBuilder({ page }).include('#insights').withRules(['label-content-name-mismatch']).analyze();
			expect(results.violations).toEqual([]);
			expect(results.passes.find((rule) => rule.id === 'label-content-name-mismatch')?.nodes).toHaveLength(3);
		}
	}
});
