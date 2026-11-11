import { expect, test } from '@playwright/test';
import { mockComplianz } from './helpers/complianz';
import AxeBuilder from '@axe-core/playwright';

const route = '/business-ev-charging-installations/';
const names = ['Johnsons Cars', 'Elite Hotels', 'Salvation Army'];
const serviceTitles = ['Site Assessment & Consultation', 'Professional Installation', 'Ongoing Support & Maintenance'];
const serviceCopy = [
	'We’ll assess your site’s layout, power capacity, and goals to recommend the right charging solution for your business — no obligation.',
	'Our certified installers handle everything — groundworks, hardware, software setup, and final testing — all with minimal disruption to your business.',
	'We offer optional maintenance packages, remote diagnostics, and on-site callouts to keep your chargers working smoothly.',
];
const quotes = [
	'"Working with Greendawn has taken the headache out of our EV charging rollout, they have demonstrated a deep understanding of my business in a sector that is complex and always changing. I know I can rely on Greendawn to provide me with the best solution in the most cost-effective way"',
	'"Greendawn have successfully installed EV charging stations at all our hotels in the south of England. They provide excellent service & support and have proved to be a great partner."',
	'"Greendawn stood out with their sustainability and values aligning with ours, as well as the flexable tariff setting and nationwide coverage. It has been a smooth process from start to finnish with Greendawn; we look forward to working with them on many more projects."',
];

test('Business keeps source services, detailed sales form and all full testimonials without JavaScript or SCF', async ({ browser, baseURL }, testInfo) => {
	const context = await browser.newContext({ baseURL, javaScriptEnabled: false });
	const page = await context.newPage();
	const thirdParty: string[] = [];
	page.on('request', request => { if (/youtube|ytimg|googlevideo/.test(new URL(request.url()).hostname)) thirdParty.push(request.url()); });
	try {
		for (const width of [320, 834, 1440]) {
			await page.setViewportSize({ width, height: 950 });
			await page.goto(route);
			await expect(page.locator('main h1')).toHaveCount(1);
			await expect(page.locator('main h1 span')).toHaveText('EV Charger Installation for Businesses');
			await expect(page.locator('main h1 em')).toHaveText('That Think Ahead');
			await expect(page.locator('.gd-page-lead')).toHaveText('We work with fleets, offices, retail parks, and commercial landlords to survey, plan, and install EV infrastructure.');
			await expect(page.locator('.gd-page-hero__media, main iframe, .gd-process, .gd-page-insights')).toHaveCount(0);
			await expect(page.locator('main')).not.toContainText('Do not append this legacy layout');
			await expect(page.locator('.gd-business-help h2')).toHaveText('How We Can Help You:');
			await expect(page.locator('.gd-business-help > p')).toHaveText('Whether you’re looking to power a single site or roll out EV charging across your portfolio, we make the process simple, strategic, and fully compliant');
			await expect(page.locator('.gd-service-list h3')).toHaveText(serviceTitles);
			await expect(page.locator('.gd-service-list p')).toHaveText(serviceCopy);
			await expect(page.locator('.gd-business-form h2')).toHaveText('Speak To Our Team- For Expert Advice');
			const form = page.locator('.gd-business-form form');
			await expect(form).toHaveCount(1);
			await expect(form.locator('.gd-contact-fields label')).toHaveCount(8);
			await expect(form.locator('[aria-required="true"]')).toHaveCount(4);
			await expect(form.locator('.gd-contact-fields p, .gd-contact-fields br')).toHaveCount(0);
			await expect(form.getByRole('button', { name: 'Start Your Journey' })).toBeVisible();
			await expect(page.locator('.gd-testimonials h2')).toHaveText('Who we work with…');
			await expect(page.locator('.gd-testimonials-intro')).toHaveText('Typically, our clients have large installations on single sites, or have multiple sites with changing requirements. For example, we work extensively in the hotel and leisure space, and have major relationships with some of the UKs largest car dealership networks.');
			await expect(page.locator('.gd-testimonial-card h3')).toHaveText(names);
			await expect(page.locator('.gd-testimonial-card blockquote')).toHaveText(quotes);
			for (const quote of await page.locator('.gd-testimonial-card blockquote').all()) {
				await expect(quote).toBeVisible();
				expect(await quote.evaluate(el => el.scrollHeight <= el.clientHeight + 1)).toBe(true);
			}
			await expect(page.getByRole('link', { name: 'Watch Johnsons Cars video testimonial' })).toHaveAttribute('href', 'https://youtu.be/t96HrzEREM0');
			await expect(page.locator('.gd-testimonial-card h3 a')).toHaveAttribute('href', 'https://youtu.be/t96HrzEREM0');
			await expect(page.locator('.gd-testimonial-attribution')).toHaveCount(0);
			for (const image of await page.locator('main img').all()) {
				await expect(image).toHaveAttribute('loading', 'lazy');
				await expect(image).toHaveAttribute('src', new RegExp(`^${baseURL}/`));
				await image.scrollIntoViewIfNeeded();
				await image.evaluate(async (el: HTMLImageElement) => { await el.decode(); });
			}
			expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
			expect(await page.locator('[id]').evaluateAll(elements => elements.map(e => e.id).filter((id, index, ids) => ids.indexOf(id) !== index))).toEqual([]);
			await page.evaluate(async () => { await document.fonts.ready; window.scrollTo(0, 0); });
			await testInfo.attach(`business-${width}`, { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
		}
		expect(thirdParty).toEqual([]);
	} finally { await context.close(); }
});

test('Business honours saved content/clears, safe media and explicit unavailable/protected states', async ({ page, baseURL }) => {
	await page.goto('/gd-business-edited/');
	await expect(page.locator('main h1')).toHaveText('Charging for our business.');
	await expect(page.locator('main h1 em, .gd-page-lead')).toHaveCount(0);
	await expect(page.locator('.gd-service-list p').nth(1)).toHaveText('Reviewed installation description.');
	await expect(page.locator('.gd-testimonial-card blockquote').first()).toHaveText('Edited quote <strong>retained as text</strong>.');
	await expect(page.locator('.gd-testimonial-card blockquote strong, main iframe')).toHaveCount(0);
	await expect(page.locator('.gd-testimonial-attribution')).toHaveText('Reviewed client attribution');
	await expect(page.locator('[data-gd-video]')).toHaveCount(0);
	for (const path of ['/gd-business-unassigned/', '/gd-business-invalid-form/', '/gd-business-no-cf7/']) {
		await page.goto(path);
		await expect(page.locator('.gd-business-form form')).toHaveCount(0);
		await expect(page.locator('.gd-business-form')).toContainText('The enquiry form is currently unavailable.');
		await expect(page.locator('.gd-business-form a')).toHaveAttribute('href', `${baseURL}/enquire/`);
	}
	await page.goto('/gd-business-empty-media/');
	await expect(page.locator('.gd-testimonial-media, main iframe, [data-gd-video]')).toHaveCount(0);
	await expect(page.locator('.gd-testimonial-card blockquote')).toHaveText(quotes);
	await page.goto('/gd-business-invalid-video/');
	await expect(page.locator('[data-gd-video], main iframe, .gd-testimonial-card h3 a')).toHaveCount(0);
	await expect(page.locator('.gd-testimonial-card blockquote')).toHaveText(quotes);
	await page.goto('/gd-business-no-core/');
	await expect(page.locator('main h1')).toHaveText('gd-business-no-core');
	await expect(page.locator('main h2, .gd-service-list, .gd-testimonial-card')).toHaveCount(0);
	await page.goto('/gd-business-protected/');
	await expect(page.locator('main .post-password-form')).toBeVisible();
	await expect(page.locator('main')).not.toContainText('Protected Business content must stay private.');
	await expect(page.locator('.gd-business-form, .gd-service-list, .gd-testimonial-card')).toHaveCount(0);
});

test('Business video loads only on keyboard activation and keeps the layout stable', async ({ page }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	const requests: string[] = [];
	page.on('request', request => { if (/youtube|ytimg|googlevideo/.test(new URL(request.url()).hostname)) requests.push(request.url()); });
	await page.route('https://www.youtube-nocookie.com/**', route => route.fulfill({ contentType: 'text/html', body: '<!doctype html><title>Video integration fixture</title>' }));
	await page.goto(route);
	await expect(page.locator('main iframe')).toHaveCount(0);
	expect(requests).toEqual([]);
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	const media = page.locator('[data-gd-video-media]').first();
	const before = await media.evaluate(el => el.getBoundingClientRect().height);
	const link = page.getByRole('link', { name: 'Watch Johnsons Cars video testimonial' });
	await link.focus();
	await page.keyboard.press('Enter');
	const frame = page.getByTitle('Watch Johnsons Cars video testimonial', { exact: true });
	await expect(frame).toHaveAttribute('src', 'https://www.youtube-nocookie.com/embed/t96HrzEREM0?rel=0&autoplay=1');
	await expect(frame).toBeFocused();
	expect(Math.abs(await media.evaluate(el => el.getBoundingClientRect().height) - before)).toBeLessThan(1);
	await expect(page.locator('.gd-testimonial-card blockquote')).toHaveText(quotes);
	await expect(page.locator('.gd-testimonial-card h3 a')).toHaveAttribute('href', 'https://youtu.be/t96HrzEREM0');
	await expect(page.locator('script[src*="ScrollTrigger"], script[src*="jsx-runtime"]')).toHaveCount(0);
});

test('Business sales form reuses native feedback and first-touch attribution without changing Enquire', async ({ page, baseURL }) => {
	await page.setViewportSize({ width: 375, height: 900 });
	await mockComplianz(page, { marketing: true });
	await page.goto('/about/?utm_source=search&utm_medium=cpc&utm_campaign=business', { referer: 'https://referrer.example/landing/?private=discard' });
	await page.goto(route);
	const form = page.locator('.gd-business-form form');
	await expect(form.locator('[name="page-url"]')).toHaveValue(`${baseURL}${route}`);
	await expect(form.locator('[name="first_landing"]')).toHaveValue(`${baseURL}/about/`);
	await expect(form.locator('[name="utm_campaign"]')).toHaveValue('business');
	await form.getByRole('button', { name: 'Start Your Journey' }).click();
	await expect(form.locator('[aria-invalid="true"]').first()).toBeVisible();
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	const results = await new AxeBuilder({ page }).include('main').withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
	expect(results.violations).toEqual([]);
	await form.getByRole('textbox', { name: 'Full Name (required)', exact: true }).fill('Business form test');
	await form.getByRole('textbox', { name: 'Phone Number (required)', exact: true }).fill('0121 555 0100');
	await form.getByRole('textbox', { name: 'Email Address (required)', exact: true }).fill('business@example.test');
	let status = 'mail_failed';
	await page.route(url => url.pathname.includes('/contact-forms/') && url.pathname.endsWith('/feedback'), async intercept => {
		const payload = intercept.request().postDataBuffer()?.toString() ?? '';
		for (const value of ['Business form test', `${baseURL}${route}`, `${baseURL}/about/`, 'search', 'cpc', 'business']) expect(payload).toContain(value);
		expect(payload).not.toContain('private=discard');
		await intercept.fulfill({ json: { status, message: status === 'mail_sent' ? 'Business enquiry received.' : 'Delivery failed. Please try again.', invalid_fields: [] } });
	});
	await form.getByRole('button', { name: 'Start Your Journey' }).click();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Delivery failed. Please try again.');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	status = 'mail_sent';
	await form.getByRole('button', { name: 'Start Your Journey' }).click();
	await expect(form.locator('.wpcf7-response-output')).toHaveText('Business enquiry received.');
	await expect(form.locator('.wpcf7-response-output')).toBeVisible();
	await expect(form.locator('[name="utm_campaign"]')).toHaveValue('business');
	await page.goto('/enquire/');
	await expect(page.locator('.gd-enquire-form .gd-contact-fields label')).toHaveCount(8);
	await expect(page.locator('.gd-enquiry-process h2')).toHaveCount(6);
});
