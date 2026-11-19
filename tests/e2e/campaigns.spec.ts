import { expect, test, type Page } from '@playwright/test';
import { mockComplianz } from './helpers/complianz';

const landing = '/workplace-charging-grant/';
const dates = {
	live: '2027-01-15T12:00:00Z',
	late: '2027-02-01T00:00:00Z',
	closed: '2027-03-31T23:00:00Z',
};

async function phase(page: Page, baseURL: string | undefined, value: keyof typeof dates = 'live') {
	await page.context().addCookies([{ name: 'gd_test_campaign', value, url: baseURL! }]);
	await page.clock.install({ time: new Date(dates[value]) });
}

async function fillContact(drawer: ReturnType<Page['getByRole']>, email: string) {
	await drawer.getByRole('textbox', { name: 'First name', exact: true }).fill('Campaign visitor');
	await drawer.getByRole('textbox', { name: 'Telephone number' }).fill('0121 555 0100');
	await drawer.getByRole('textbox', { name: 'Company name' }).fill('Campaign Test Company');
	await drawer.getByRole('textbox', { name: 'Email', exact: true }).fill(email);
	await drawer.getByRole('radio', { name: 'Tomorrow', exact: true }).check();
}

test('the featured notice links to the root landing page from public templates', async ({ page, baseURL }) => {
	await phase(page, baseURL);
	await page.setViewportSize({ width: 390, height: 850 });
	for (const path of ['/', '/about/', '/callback-inner/', '/campaigns/another-campaign/', landing]) {
		await page.goto(path);
		const bar = page.getByRole('complementary', { name: 'Featured campaign' });
		await expect(bar).toBeVisible();
		await expect(bar.getByRole('link', { name: 'Plan My Project' })).toHaveAttribute('href', `${baseURL}${landing}`);
		await expect(page.locator('[data-gd-campaign-popup]')).toHaveCount(1);
	}
	await expect(page.getByRole('heading', { level: 1 })).toHaveText('Workplace charging, planned well.');
	await expect(page.getByText('Scheduled scheme end')).toBeVisible();
	await expect(page.getByText('31 March 2027', { exact: true })).toBeVisible();
});

test('the pop-up opens once on the scroll trigger or the six-second timer', async ({ page, baseURL }) => {
	await phase(page, baseURL);
	await page.goto(landing);
	const dialog = page.getByRole('dialog', { name: 'Two dates to plan around.' });
	await expect(dialog).toBeHidden();
	await page.evaluate(() => window.scrollTo({ top: 260, behavior: 'instant' }));
	await expect(dialog).toBeVisible();
	await page.keyboard.press('Escape');
	await expect(dialog).toBeHidden();
	await page.goto('/about/');
	await page.clock.fastForward(6500);
	await expect(dialog).toBeHidden();

	await page.evaluate(() => sessionStorage.clear());
	await page.reload();
	await page.clock.fastForward(6100);
	await expect(dialog).toBeVisible();
});

test('the pop-up waits for the menu and drawer, then hands off to a grant enquiry', async ({ page, baseURL }) => {
	await phase(page, baseURL);
	await page.setViewportSize({ width: 390, height: 850 });
	await page.goto('/callback-inner/');
	const menu = page.locator('[data-nav-toggle]');
	const popup = page.getByRole('dialog', { name: 'Two dates to plan around.' });
	await menu.click();
	await page.clock.fastForward(6100);
	await expect(popup).toBeHidden();
	await menu.click();
	await expect(popup).toBeVisible();
	await popup.getByRole('link', { name: 'Plan my grant project' }).click();
	const drawer = page.getByRole('dialog', { name: 'Request a callback' });
	await expect(drawer.getByRole('heading', { name: 'How should we reach you?' })).toBeVisible();
	await expect(drawer.locator('.lead-capture__campaign')).toBeVisible();
	await expect(drawer.getByRole('spinbutton', { name: /Estimated number of chargers/ })).toBeVisible();
	await expect(drawer.getByRole('textbox', { name: /Anything else we should know/ })).toBeVisible();
	await page.keyboard.press('Escape');
	await expect(drawer).toBeHidden();

	await page.evaluate(() => sessionStorage.clear());
	await page.reload();
	await page.locator('.lead-capture__launcher').click();
	await expect(drawer.locator('.lead-capture__campaign')).toBeVisible();
	await page.clock.fastForward(6100);
	await expect(popup).toBeHidden();
	await page.keyboard.press('Escape');
	await expect(popup).toBeVisible();
});

test('UK date boundaries switch the CTA, then remove notices while the URL stays live', async ({ page, baseURL }) => {
	await phase(page, baseURL, 'late');
	await page.goto(landing);
	await expect(page.locator('.gd-campaign-hero').getByRole('link', { name: 'Check installation availability' })).toBeVisible();
	await expect(page.getByRole('complementary', { name: 'Featured campaign' }).getByText('Ask what can still be scheduled.')).toBeVisible();

	await page.clock.setSystemTime(new Date(dates.closed));
	await page.evaluate(() => window.dispatchEvent(new PageTransitionEvent('pageshow')));
	await expect(page.getByRole('complementary', { name: 'Featured campaign' })).toBeHidden();
	await expect(page.locator('.gd-campaign-metrics')).toBeHidden();
	await expect(page.locator('.gd-campaign-checklist')).toBeHidden();
	await expect(page.getByRole('heading', { name: 'Get your voucher.' })).toBeHidden();
	await expect(page.getByRole('heading', { name: 'Agree your plan.' })).toBeVisible();
	await expect(page.getByText('Can we apply if we lease our premises?', { exact: true })).toBeHidden();
	await expect(page.locator('.gd-campaign-hero').getByRole('link', { name: 'Discuss my workplace project' })).toBeVisible();

	await page.context().addCookies([{ name: 'gd_test_campaign', value: 'closed', url: baseURL! }]);
	await page.reload();
	await expect(page.locator('[data-gd-campaign-popup]')).toHaveCount(0);
	await expect(page.getByRole('heading', { name: 'Planning your workplace charging?' })).toBeVisible();
	await expect(page).toHaveURL(`${baseURL}${landing}`);
});

test('a grant callback keeps the campaign reference and consented attribution in its email', async ({ page, baseURL, request }) => {
	await request.post('/wp-json/greendawn-test/v1/callback/reset');
	await phase(page, baseURL);
	await mockComplianz(page, { marketing: true });
	await page.goto(`${landing}?gclid=QA-Campaign&msclkid=QA-Microsoft&utm_campaign=wcs`);
	await page.locator('.gd-campaign-hero').getByRole('link', { name: 'Plan my grant project' }).click();
	const drawer = page.getByRole('dialog', { name: 'Request a callback' });
	await fillContact(drawer, 'campaign@example.test');
	await drawer.getByRole('spinbutton', { name: /Estimated number of chargers/ }).fill('4');
	await drawer.getByRole('textbox', { name: /Anything else we should know/ }).fill('Two sites, with a phased installation.');
	await drawer.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(drawer.getByRole('heading', { name: /Thank you/ })).toBeVisible();
	await expect(drawer.locator('.lead-capture__campaign')).toHaveCount(0);
	const mail = await (await request.get('/wp-json/greendawn-test/v1/callback')).json();
	expect(mail.message).toContain('Campaign: Workplace Charging Scheme');
	expect(mail.message).toContain('Estimated number of chargers: 4');
	expect(mail.message).toContain('Two sites, with a phased installation.');
	expect(mail.message).toContain('gclid: QA-Campaign');
	expect(mail.message).toContain('msclkid: QA-Microsoft');
	const conversions = await page.evaluate(() => window.dataLayer?.filter(entry => entry.event === 'generate_lead'));
	expect(conversions).toEqual([expect.objectContaining({ cta_location: 'campaign-landing', gd_event_marketing: true })]);
});


test('grant delivery can retry without marketing permission or optional project details', async ({ page, baseURL, request }) => {
	await request.post('/wp-json/greendawn-test/v1/callback/reset');
	await phase(page, baseURL);
	await mockComplianz(page, { statistics: true, marketing: false });
	await page.goto(`${landing}?gclid=QA-Denied&msclkid=QA-Denied-MS`);
	await page.locator('.gd-campaign-hero').getByRole('link', { name: 'Plan my grant project' }).click();
	const drawer = page.getByRole('dialog', { name: 'Request a callback' });
	await fillContact(drawer, 'failure@example.test');
	await drawer.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(drawer.getByRole('alert')).toContainText('could not be sent');
	await expect(drawer.locator('.lead-capture__campaign')).toBeVisible();
	await expect(drawer.getByRole('textbox', { name: 'First name', exact: true })).toHaveValue('Campaign visitor');
	expect(await page.evaluate(() => window.dataLayer?.filter(entry => entry.event === 'generate_lead') ?? [])).toHaveLength(0);
	await drawer.getByRole('textbox', { name: 'Email', exact: true }).fill('campaign-retry@example.test');
	await drawer.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(drawer.getByRole('heading', { name: /Thank you/ })).toBeVisible();
	const mail = await (await request.get('/wp-json/greendawn-test/v1/callback')).json();
	expect(mail.message).toContain('Campaign: Workplace Charging Scheme');
	expect(mail.message).not.toMatch(/Advertising attribution|Estimated number of chargers|QA-Denied/);
});

test('aftercare hides the campaign notice and uses its own charger count label', async ({ page, baseURL, request }) => {
	await request.post('/wp-json/greendawn-test/v1/callback/reset');
	await phase(page, baseURL);
	await mockComplianz(page, { marketing: true });
	await page.goto('/callback-inner/?gclid=QA-Aftercare');
	await page.locator('.lead-capture__launcher').click();
	const drawer = page.getByRole('dialog', { name: 'Request a callback' });
	await expect(drawer.locator('.lead-capture__campaign')).toBeVisible();
	await drawer.getByRole('button', { name: 'Existing chargers / aftercare' }).click();
	await expect(drawer.locator('.lead-capture__campaign')).toHaveCount(0);
	await drawer.getByRole('button', { name: 'Maintenance' }).click();
	await drawer.getByRole('button', { name: 'Something isn’t working' }).click();
	await drawer.getByRole('button', { name: 'Skip for now' }).click();
	const count = drawer.getByRole('spinbutton', { name: /Number of chargers affected/ });
	await count.fill('2');
	await fillContact(drawer, 'aftercare-campaign@example.test');
	await drawer.getByRole('button', { name: 'Request callback', exact: true }).click();
	await expect(drawer.getByRole('heading', { name: /Thank you/ })).toBeVisible();
	const mail = await (await request.get('/wp-json/greendawn-test/v1/callback')).json();
	expect(mail.message).toContain('Number of chargers affected: 2');
	expect(mail.message).not.toMatch(/Campaign: Workplace Charging Scheme|Advertising attribution|QA-Aftercare/);
});

test('the expanded campaign follows the shared container at desktop, tablet and phone widths', async ({ page, baseURL }) => {
	await phase(page, baseURL);
	for (const width of [1920, 1440, 1024, 768, 393, 320]) {
		await page.setViewportSize({ width, height: 1000 });
		await page.goto(landing);
		await expect(page.getByRole('heading', { name: 'Could your workplace qualify?' })).toBeVisible();
		await expect(page.locator('.gd-campaign-metrics__grid > div')).toHaveCount(3);
		await expect(page.locator('.gd-campaign-checklist li')).toHaveCount(4);
		await expect(page.locator('.gd-campaign-steps > li')).toHaveCount(3);
		await expect(page.locator('.gd-campaign-faq details')).toHaveCount(4);
		const alignment = await page.evaluate(() => {
			const hero = document.querySelector('.gd-campaign-hero .gd-campaign-shell')!.getBoundingClientRect();
			return {
				overflow: document.documentElement.scrollWidth > innerWidth,
				sections: Array.from(document.querySelectorAll<HTMLElement>('.gd-campaign-landing .gd-campaign-shell')).filter(el => el.getClientRects().length).map(el => {
					const box = el.getBoundingClientRect();
					return Math.abs(box.left - hero.left) < 1 && Math.abs(box.width - hero.width) < 1;
				}),
			};
		});
		expect(alignment.overflow, `overflow at ${width}px`).toBe(false);
		expect(alignment.sections.every(Boolean), `section alignment at ${width}px`).toBe(true);
	}
});

test('new eligibility and closing CTAs use the existing grant drawer', async ({ page, baseURL }) => {
	await phase(page, baseURL);
	await page.goto(landing);
	const drawer = page.getByRole('dialog', { name: 'Request a callback' });
	for (const selector of ['.gd-campaign-panel', '.gd-campaign-closing']) {
		const cta = page.locator(selector).getByRole('link').first();
		await expect(cta).toHaveAttribute('data-lead-entry-point', 'campaign-landing');
		await expect(cta).toHaveAttribute('data-lead-campaign', /^\d+$/);
		await cta.click();
		await expect(drawer.getByRole('heading', { name: 'How should we reach you?' })).toBeVisible();
		await expect(drawer.getByRole('spinbutton', { name: /Estimated number of chargers/ })).toBeVisible();
		await page.keyboard.press('Escape');
		const popup = page.locator('[data-gd-campaign-popup]');
		if (await popup.isVisible()) await page.keyboard.press('Escape');
	}
});

test('campaign sections and FAQ answers are available without JavaScript', async ({ browser, baseURL }) => {
	const context = await browser.newContext({ javaScriptEnabled: false });
	await context.addCookies([{ name: 'gd_test_campaign', value: 'live', url: baseURL! }]);
	const page = await context.newPage();
	await page.goto(`${baseURL}${landing}`);
	await expect(page.getByRole('heading', { name: 'Could your workplace qualify?' })).toBeVisible();
	await expect(page.getByRole('heading', { name: 'From first plans to charging at work.' })).toBeVisible();
	const question = page.getByText('Can we apply if we lease our premises?', { exact: true });
	await question.click();
	await expect(page.getByText('Yes, provided the site qualifies and you have the necessary landlord and landowner permission.')).toBeVisible();
	await expect(page.locator('.gd-campaign-panel a.gd-campaign-button').first()).toHaveAttribute('href', /\/enquire\/$/);
	await context.close();
});

test('a second campaign omits empty optional sections', async ({ page, baseURL }) => {
	await phase(page, baseURL);
	await page.goto('/campaigns/another-campaign/');
	await expect(page.getByRole('heading', { level: 1 })).toHaveText('Another campaign');
	await expect(page.locator('.gd-campaign-metrics, .gd-campaign-process, .gd-campaign-faq, .gd-campaign-closing')).toHaveCount(0);
	await expect(page.locator('.gd-campaign-landing')).not.toContainText('£500');
});
