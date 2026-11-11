import { expect, test } from '@playwright/test';
import { mockComplianz } from './helpers/complianz';
import AxeBuilder from '@axe-core/playwright';

const questions = ['Do I need aftercare if my chargers are brand new?', 'Can I accept payments without a backoffice platform?', 'What happens if I don’t have an aftercare plan?'];
const offerings = ['Backoffice Software', 'Inspection + Testing', 'Complete Care Plan', 'Driver + User Support'];
const offeringPoints = [
	['Usage tracking and reporting', 'Access control for staff or public users', 'Payment handling and transaction records', 'Remote management and diagnostics'],
	['Electrical inspection and testing', 'Certification in line with regulations', 'Early identification of faults', 'Support for warranty compliance'],
	['Troubleshooting and technical support', 'System updates and maintenance', 'Warranty support and coordination', 'Prioritised response and reduced disruption'],
	['Helpline or app-based support', 'Assistance with charging and payment issues', 'Guidance for first-time or public users', 'Support aligned with public charging requirements'],
];
const faqPoints = [
	['Active backoffice software (if accepting payments)', 'Compliance oversight', 'Driver support (for public charging above 7kW)', 'Ongoing electrical inspection and testing'],
	['Handles transaction processing', 'Records payments', 'Enables reporting', 'Ensures regulatory compliance'],
	['Full call-out rates apply', 'Downtime may increase', 'Compliance risk increases', 'Warranty protection may be affected', 'Transaction capability could be disrupted'],
];

test('Aftercare retains complete source offerings, FAQs and local articles without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto('/aftercare/');
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('EV Charging');
			await expect(page.locator('main h1 em')).toHaveText('Aftercare');
			await expect(page.locator('.gd-page-lead')).toHaveText('Take Control of your EV Charging: Smarter, Accountable, and Always Available');
			await expect(page.locator('main')).not.toContainText('Do not append this legacy layout');
			await expect(page.locator('.gd-page-hero__media img')).toHaveAttribute('loading', 'eager');
			await expect(page.locator('.gd-page-hero__media img')).toHaveAttribute('fetchpriority', 'high');
			await expect(page.locator('.gd-page-hero__media img')).toHaveAttribute('srcset', /[0-9]+w/);
			await expect(page.locator('.gd-story h2')).toHaveText('What It Is and Why It Matters');
			await expect(page.locator('.gd-reading-copy > p').first()).toHaveText('EV charging aftercare is the ongoing support, monitoring, maintenance and compliance management that ensures your chargers continue to operate safely, reliably and in line with current regulations.');
			await expect(page.locator('.gd-reading-copy li')).toHaveText(['A live electrical asset', 'A network-connected device', 'A payment terminal (in many cases)', 'A regulated piece of infrastructure']);
			await expect(page.locator('.gd-reading-copy > p').last()).toHaveText('Without structured aftercare, chargers can quickly become unreliable, non-compliant or financially ineffective.');
			await expect(page.locator('.gd-offering-card h3')).toHaveText(offerings);
			for (const [index, points] of offeringPoints.entries()) {
				const card = page.locator('.gd-offering-card').nth(index);
				await expect(card.locator('li')).toHaveText(points);
				for (const paragraph of await card.locator('p, li').all()) await expect(paragraph).toBeVisible();
			}
			await expect(page.locator('.gd-offering-card').first()).toContainText('Without this, chargers operate with limited visibility and control.');
			await expect(page.locator('.gd-offering-card').nth(1)).toContainText('Routine testing helps identify issues early and supports ongoing performance.');
			await expect(page.locator('.gd-offering-card').nth(2)).toContainText('A structured support plan ensures issues are resolved efficiently, helping to minimise downtime.');
			await expect(page.locator('.gd-offering-card').last()).toContainText('Essential for public charging environments. (Above 7kw)');
			await expect(page.locator('.gd-faq-item summary')).toHaveText(questions);
			await expect(page.locator('.gd-faq-item').first()).toHaveAttribute('open', '');
			for (const [index, points] of faqPoints.entries()) {
				const disclosure = page.locator('.gd-faq-item').nth(index);
				if (index > 0) await disclosure.locator('summary').click();
				await expect(disclosure.locator('li')).toHaveText(points);
				await expect(disclosure.locator('.gd-faq-answer')).toBeVisible();
			}
			await expect(page.locator('.gd-faq-item').first()).toContainText('Installation alone does not cover long-term operation or regulatory responsibility.');
			await expect(page.locator('.gd-faq-item').nth(1)).toContainText('Backoffice software is essential for accepting transactions');
			await expect(page.locator('.gd-faq-item').last()).toContainText('Reactive support is typically more expensive and less predictable than managed aftercare.');
			await expect(page.locator('.gd-faq-form form')).toHaveCount(1);
			await expect(page.locator('.gd-contact-fields label')).toHaveCount(7);
			await expect(page.locator('.gd-contact-fields p, .gd-contact-fields br')).toHaveCount(0);
			await expect(page.locator('main [name="page-url"]')).toHaveValue('');
			await expect(page.locator('.gd-article-track h3')).toHaveText(Array.from({ length: 10 }, (_, i) => `Guide fixture ${i + 1}`));
			for (const href of await page.locator('main a[href]').evaluateAll(links => links.map(a => (a as HTMLAnchorElement).href))) expect(href).toMatch(new RegExp(`^${baseURL}/`));
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			for (const media of await page.locator('main img').all()) {
				await media.scrollIntoViewIfNeeded();
				await media.evaluate(async (el: HTMLImageElement) => { await el.decode(); });
			}
			await page.evaluate(async () => { await document.fonts.ready; document.querySelector('.gd-article-track')?.scrollTo({ left: 0, behavior: 'instant' }); window.scrollTo(0, 0); });
			await testInfo.attach(`aftercare-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
	} finally { await context.close(); }
});

test('Aftercare preserves saved/reordered rich content, clears and safe unavailable/protected states', async ({ page, baseURL }) => {
	await page.goto('/gd-aftercare-edited/');
	await expect(page.locator('main h1')).toHaveText('Care for our charging network.');
	await expect(page.locator('main h1 em, .gd-page-lead')).toHaveCount(0);
	await expect(page.locator('.gd-reading-copy strong')).toHaveText('care content');
	await expect(page.locator('.gd-reading-copy [onclick], .gd-reading-copy [style], .gd-reading-copy .bad, .gd-reading-copy iframe, .gd-reading-copy a[href^="javascript:"]')).toHaveCount(0);
	await expect(page.locator('.gd-faq-item summary')).toHaveText(['Our last question first?', questions[0] ?? '']);
	await expect(page.locator('.gd-faq-answer strong').first()).toHaveText('emphasis');
	await expect(page.locator('.gd-faq-item').first().locator('li')).toHaveText(['First saved point', 'Second saved point']);
	await expect(page.locator('.gd-article-track h3')).toHaveText(['Guide fixture 10', 'Guide fixture 1']);
	await expect(page.locator('.gd-faq-form')).toHaveCount(0);
	await expect(page.locator('.gd-faq-layout--form')).toHaveCount(0);
	await page.goto('/gd-aftercare-empty/');
	await expect(page.locator('.gd-page-hero__media, .gd-story img, .gd-faq-section, .gd-page-insights')).toHaveCount(0);
	await expect(page.locator('.gd-offering-card')).toHaveCount(4);
	for (const path of ['/gd-aftercare-invalid-form/', '/gd-aftercare-no-cf7/']) {
		await page.goto(path);
		await expect(page.locator('.gd-form-panel form')).toHaveCount(0);
		await expect(page.getByText('The enquiry form is currently unavailable.', { exact: true })).toBeVisible();
		await expect(page.locator('.gd-form-panel a')).toHaveAttribute('href', `${baseURL}/enquire/`);
	}
	await page.goto('/gd-aftercare-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-aftercare-no-core');
	await expect(page.locator('main h2')).toHaveCount(0);
	await page.goto('/gd-aftercare-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('main')).not.toContainText('Protected Aftercare content must stay private.');
	await expect(page.locator('.gd-offering-card, .gd-faq-item, .gd-page-insights')).toHaveCount(0);
	await page.goto('/gd-aftercare-blank-answer/');
	await expect(page.locator('.gd-faq-item summary')).toHaveText('An intentionally blank answer?');
	await expect(page.locator('.gd-faq-answer')).toHaveText('');
});

test('Aftercare disclosures, form validation and article controls remain keyboard accessible with reduced motion', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/aftercare/');
	await expect(page.locator('script[src*="greendawn-home-motion"], script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
	const summary = page.locator('.gd-faq-item').nth(1).locator('summary');
	await summary.focus();
	await page.keyboard.press('Enter');
	await expect(page.locator('.gd-faq-item').nth(1).locator('.gd-faq-answer')).toBeVisible();
	await page.keyboard.press('Space');
	await expect(page.locator('.gd-faq-item').nth(1).locator('.gd-faq-answer')).toBeHidden();
	await page.getByRole('button', { name: 'Next articles' }).click();
	await expect.poll(() => page.locator('.gd-article-track').evaluate(el => el.scrollLeft)).toBeGreaterThan(0);
	await page.getByRole('button', { name: 'Send enquiry' }).click();
	await expect(page.locator('.gd-form-panel [aria-invalid="true"]').first()).toBeVisible();
	await expect(page.locator('.gd-form-panel .wpcf7-response-output')).toBeVisible();
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
});

test('Aftercare serialises existing attribution and shows success/delivery failure without sending email', async ({ page, baseURL }) => {
	await mockComplianz(page, { marketing: true });
	await page.goto('/aftercare/?utm_source=search&utm_medium=cpc&utm_campaign=care', { referer: 'https://referrer.example/landing/?private=discard' });
	const form = page.locator('.gd-faq-form form');
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}/aftercare/`);
	await expect(form.locator('[name="utm_campaign"]')).toHaveValue('care');
	await form.getByRole('textbox', { name: 'Full Name (required)', exact: true }).fill('Aftercare test');
	await form.getByRole('textbox', { name: 'Project Name (required)', exact: true }).fill('Care plan');
	await form.getByRole('textbox', { name: 'Phone Number (required)', exact: true }).fill('0121 555 0100');
	await form.getByRole('textbox', { name: 'Email Address (required)', exact: true }).fill('aftercare@example.test');
	await form.getByRole('combobox', { name: 'Project Type (required)', exact: true }).selectOption('Workplace');
	let status = 'mail_failed';
	await page.route(url => url.pathname.includes('/contact-forms/') && url.pathname.endsWith('/feedback'), async route => {
		const payload = route.request().postDataBuffer()?.toString() ?? '';
		expect(payload).toContain(`${baseURL}/aftercare/`);
		expect(payload).toContain('care');
		expect(payload).not.toContain('private=discard');
		await route.fulfill({ json: { status, message: status === 'mail_sent' ? 'Aftercare enquiry received.' : 'Delivery failed. Please try again.', invalid_fields: [] } });
	});
	await form.getByRole('button', { name: 'Send enquiry' }).click();
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Delivery failed. Please try again.');
	status = 'mail_sent';
	await form.getByRole('button', { name: 'Send enquiry' }).click();
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Aftercare enquiry received.');
});
