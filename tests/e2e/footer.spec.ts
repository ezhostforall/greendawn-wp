import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

test('the reference footer renders sitewide without JavaScript at mobile, tablet and desktop widths', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 390, 800, 1440]) {
			await page.setViewportSize({ width, height: 900 });
			await page.goto('/about/');
			await page.evaluate(() => document.fonts.ready);
			const footer = page.getByRole('contentinfo');
			await expect(footer).toHaveCSS('background-color', 'rgb(7, 23, 33)');
			await expect(footer.locator('.gd-footer__lead p')).toHaveText('EV charging. Handled.');
			await expect(footer.locator('.gd-footer__lead p')).toHaveCSS('font-style', 'italic');
			await expect(footer.getByRole('navigation')).toHaveCount(5);
			await expect(footer.getByRole('navigation', { name: 'Company', exact: true }).getByRole('link')).toHaveText(['About Us', 'Our Projects']);
			await expect(footer.getByRole('navigation', { name: 'Our Solutions', exact: true }).getByRole('link')).toHaveText(['Workplace Charging', 'Fleet Charging', 'Public Charging']);
			await expect(footer.getByRole('navigation', { name: 'Support', exact: true }).getByRole('link')).toHaveText(['Aftercare', 'Customer support', 'Insights']);
			await expect(footer.getByRole('link', { name: '0345 512 0044' })).toHaveAttribute('href', 'tel:03455120044');
			await expect(footer.getByRole('link', { name: 'connect@greendawn.co.uk' })).toHaveAttribute('href', 'mailto:connect@greendawn.co.uk');
			await expect(footer.locator('.gd-footer__contact p')).toContainText('Office 2.026, Innovation Centre');
			await expect(footer.locator('.gd-footer__base > span')).toContainText('Greendawn Power Limited');
			await expect(footer.locator('.gd-footer__base > span')).toContainText('Company 12758202');
			await expect(footer.locator('.gd-footer__base > span')).toContainText('VAT GB359248367');
			await expect(footer.getByRole('link', { name: 'Terms', exact: true })).toHaveAttribute('href', `${baseURL}/terms-and-conditions/`);
			for (const href of await footer.locator('a[href]').evaluateAll((links) => links.map((link) => (link as HTMLAnchorElement).href))) {
				if (/^(tel:|mailto:)/.test(href)) continue;
				if (href === 'https://www.linkedin.com/company/greendawnpower/') continue;
				expect(new URL(href).origin).toBe(new URL(baseURL!).origin);
			}
			if (width === 800) {
				expect(await footer.locator('.gd-footer__social a').evaluate((link) => {
					const range = document.createRange();
					range.selectNodeContents(link.firstChild!);
					return range.getClientRects().length;
				})).toBe(1);
			}
			const columns = await footer.locator('.gd-footer__grid').evaluate((element) => getComputedStyle(element).gridTemplateColumns.split(' ').length);
			expect(columns).toBe(width <= 480 ? 1 : width <= 976 ? 2 : 4);
			const logo = footer.locator('.gd-footer__brand img');
			await expect(logo).toHaveAttribute('loading', 'lazy');
			await expect(logo).toHaveAttribute('fetchpriority', 'low');
			await expect(logo).toHaveAttribute('width', /\d+/);
			await logo.scrollIntoViewIfNeeded();
			await logo.evaluate((image: HTMLImageElement) => image.decode());
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
			await testInfo.attach(`greendawn-footer-${width}`, { body: await footer.screenshot(), contentType: 'image/png' });
			await footer.locator('.gd-footer__brand').press('Enter');
			await expect(page).toHaveURL(`${baseURL}/`);
		}
		await page.setViewportSize({ width: 1440, height: 900 });
		for (const path of ['/greendawn-home/', '/contact/', '/services/', '/blog/', '/policy-example/', '/this-page-does-not-exist/']) {
			await page.goto(path);
			const footer = page.getByRole('contentinfo');
			await expect(footer).toHaveCount(1);
			await expect(footer).toHaveCSS('background-color', 'rgb(7, 23, 33)');
			await expect(footer.getByRole('link', { name: '0345 512 0044' })).toHaveAttribute('href', 'tel:03455120044');
			if (path === '/greendawn-home/') {
				await expect(footer.getByRole('link', { name: 'Discuss your site' })).toHaveAttribute('href', `${baseURL}/about/`);
				await testInfo.attach('greendawn-footer-home-1440', { body: await footer.screenshot(), contentType: 'image/png' });
			} else {
				await expect(page.locator('link[id^="greendawn-greendawn-homepage-"]')).toHaveCount(0);
			}
		}
	} finally {
		await context.close();
	}
});

test('saved footer content and native menus replace defaults with escaped initial HTML', async ({ page, baseURL }) => {
	const response = await page.goto('/footer-options/');
	const html = await response!.text();
	const footer = page.getByRole('contentinfo');
	await expect(footer.locator('.gd-footer__lead p')).toHaveText('Commercial charging, coordinated.');
	await expect(footer.getByRole('navigation', { name: 'About the company' }).getByRole('link')).toHaveText(['Company overview', 'Our people']);
	await expect(footer.getByRole('link', { name: 'Company overview' })).toHaveAttribute('href', `${baseURL}/about/?from=footer#team`);
	await expect(footer.getByRole('link', { name: 'Charge your workplace' })).toHaveAttribute('href', `${baseURL}/services/`);
	await expect(footer.getByRole('link', { name: 'Ask our team' })).toHaveAttribute('href', `${baseURL}/contact/?source=footer#enquiry`);
	await expect(footer.getByRole('link', { name: '+44 345 512 0044' })).toHaveAttribute('href', 'tel:+443455120044');
	await expect(footer.locator('.gd-footer__contact p')).toContainText('Fixture House <em>registered office</em>');
	await expect(footer.locator('.gd-footer__contact em')).toHaveCount(0);
	await expect(footer.getByRole('link', { name: 'team@example.test' })).toHaveAttribute('href', 'mailto:team@example.test');
	await expect(footer.getByRole('link', { name: 'Company network' })).toHaveAttribute('href', 'https://www.linkedin.com/company/greendawnpower/');
	await expect(footer.getByRole('link', { name: 'Policy details' })).toHaveAttribute('href', `${baseURL}/policy-example/`);
	await expect(footer.getByRole('link', { name: 'Nested footer item omitted' })).toHaveCount(0);
	await expect(footer.locator('.gd-footer__base > span')).toContainText('Fixture Charging Limited');
	await expect(footer.locator('.gd-footer__base > span')).not.toContainText('VAT');
	expect(html).toContain('Commercial charging, coordinated.');
	expect(await footer.innerHTML()).not.toContain('Greendawn Power Limited');
	const ids = await page.locator('[id]').evaluateAll((elements) => elements.map((element) => element.id));
	expect(new Set(ids).size).toBe(ids.length);
	const results = await new AxeBuilder({ page }).include('.gd-footer').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
});

test('long or cleared footer content stays contained and native menus survive missing brand defaults', async ({ page }, testInfo) => {
	for (const width of [320, 800, 1440]) {
		await page.setViewportSize({ width, height: 900 });
		await page.goto('/footer-long/');
		const footer = page.getByRole('contentinfo');
		await expect(footer.getByRole('link', { name: 'Ask our team' })).toHaveCount(0);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
		if (width === 320) await testInfo.attach('greendawn-footer-long-320', { body: await footer.screenshot(), contentType: 'image/png' });
	}
	await page.goto('/footer-empty/');
	const footer = page.getByRole('contentinfo');
	await expect(footer.getByRole('navigation')).toHaveCount(0);
	await expect(footer.getByRole('link')).toHaveCount(1);
	await expect(footer.locator('.gd-footer__contact')).toHaveCount(0);
	await page.goto('/footer-no-core/');
	await expect(footer.getByRole('link', { name: 'Company overview' })).toBeVisible();
	await expect(footer.locator('.gd-footer__base > span')).toContainText('Greendawn Test');
	await expect(footer.locator('.gd-footer__base > span')).not.toContainText('Company 12758202');
});

test('footer links match the reference hover styles and have visible keyboard focus', async ({ page }) => {
	await page.goto('/about/');
	const footer = page.getByRole('contentinfo');
	const about = footer.getByRole('link', { name: 'About Us', exact: true });
	await expect(about).toHaveCSS('color', 'rgb(171, 181, 189)');
	await about.hover();
	await expect(about).toHaveCSS('color', 'rgb(250, 249, 246)');
	await about.focus();
	await page.keyboard.press('Tab');
	const projects = footer.getByRole('link', { name: 'Our Projects', exact: true });
	await expect(projects).toBeFocused();
	await expect(projects).toHaveCSS('outline-color', 'rgb(233, 242, 39)');
	const privacy = footer.getByRole('link', { name: 'Privacy', exact: true });
	await privacy.hover();
	await expect(privacy).toHaveCSS('color', 'rgb(233, 242, 39)');
	await page.emulateMedia({ reducedMotion: 'reduce' });
	expect(await privacy.evaluate((element) => Number.parseFloat(getComputedStyle(element).transitionDuration))).toBeLessThanOrEqual(0.001);
});

test('the footer Customiser is prefilled, previews changes and resets to supplied defaults', async ({ page, baseURL }) => {
	test.skip(baseURL !== 'http://localhost:8080', 'Login belongs to the disposable fixture only.');
	await page.goto('/wp-login.php');
	await page.locator('#user_login').fill('admin');
	await page.locator('#user_pass').fill('local-test-only');
	await page.locator('#wp-submit').click();
	await page.waitForURL('**/wp-admin/**');
	await page.goto(`/wp-admin/customize.php?url=${encodeURIComponent(`${baseURL}/greendawn-home/`)}`);
	await page.locator('#accordion-section-greendawn_footer .accordion-section-title').click();
	const tagline = page.getByRole('textbox', { name: 'Footer tagline', exact: true });
	const phone = page.getByRole('textbox', { name: 'Telephone number', exact: true });
	await expect(tagline).toHaveValue('EV charging. Handled.');
	await expect(phone).toHaveValue('0345 512 0044');
	await tagline.fill('Charging for your operation.');
	await phone.fill('0300 123 4567');
	await phone.press('Tab');
	const preview = page.frameLocator('#customize-preview iframe').first();
	await expect(preview.locator('.gd-footer__lead p')).toHaveText('Charging for your operation.');
	await expect(preview.locator('.gd-footer__phone')).toHaveAttribute('href', 'tel:03001234567');
	await expect(preview.locator('#final-enquiry a[href^="tel:"]')).toHaveAttribute('href', 'tel:03001234567');
	await page.getByRole('button', { name: 'Reset footer content' }).click();
	await expect(tagline).toHaveValue('EV charging. Handled.');
	await expect(phone).toHaveValue('0345 512 0044');
	await expect(preview.locator('.gd-footer__phone')).toHaveAttribute('href', 'tel:03455120044');
});

test('the final enquiry follows the global footer phone unless a homepage override is saved', async ({ page }) => {
	await page.goto('/greendawn-home-global-phone/');
	await expect(page.locator('.gd-footer__phone')).toHaveAttribute('href', 'tel:03001234567');
	await expect(page.locator('#final-enquiry').getByRole('link', { name: '0300 123 4567' })).toHaveAttribute('href', 'tel:03001234567');
	await page.goto('/greendawn-home-edited/');
	await expect(page.locator('.gd-footer__phone')).toHaveAttribute('href', 'tel:03455120044');
	await expect(page.locator('#final-enquiry').getByRole('link', { name: '+44 345 512 0044' })).toHaveAttribute('href', 'tel:+443455120044');
});
