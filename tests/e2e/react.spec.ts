import { expect, test } from '@playwright/test';

// Requires example content (`pnpm wp:content`) and a production build (`pnpm build`).
test('React example mounts on its page and works', async ({ page }) => {
	const errors: string[] = [];
	page.on('pageerror', (error) => errors.push(error.message));

	await page.goto('/react-example/');
	const root = page.locator('[data-react-root="example"]');
	await expect(root.getByText('Clicks:')).toBeVisible();
	await root.getByRole('button', { name: 'Increment' }).click();
	await expect(root.locator('strong')).toHaveText('1');
	expect(errors).toEqual([]);
});

test('pages without React do not request the React bundle', async ({ page }) => {
	const requests: string[] = [];
	page.on('request', (request) => requests.push(request.url()));

	await page.goto('/about/');
	await page.waitForLoadState('networkidle');
	expect(requests.filter((url) => /react-example|reveal/.test(url))).toEqual([]);
});
