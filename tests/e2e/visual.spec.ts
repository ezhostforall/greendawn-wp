import { expect, test } from '@playwright/test';

// Opt-in: generate baselines with `pnpm test:visual --update-snapshots`, commit them for the project,
// then compare on later runs. Baselines are environment specific, so generate them in CI/Codespaces.
test('front page visual baseline', async ({ page }) => {
	await page.goto('/');
	await expect(page).toHaveScreenshot('front-page.png', { fullPage: true, maxDiffPixelRatio: 0.01 });
});
