import { expect, test, type Page } from '@playwright/test';

const ready = (page: Page) => expect(page.locator('[data-gd-home-motion]')).toHaveAttribute('data-gd-scroll-ready', 'true');

async function position(page: Page, selector: string) {
	return page.locator(selector).evaluate((element) => {
		const box = element.getBoundingClientRect();
		return { top: box.top + window.scrollY, height: box.height, viewport: window.innerHeight };
	});
}

async function scroll(page: Page, top: number) {
	await page.evaluate((y) => window.scrollTo({ top: y, behavior: 'instant' }), top);
}

async function transformY(page: Page, selector: string) {
	return page.locator(selector).evaluate((element) => new DOMMatrixReadOnly(getComputedStyle(element).transform).m42);
}

async function measureLayoutShift(page: Page) {
	await page.addInitScript(() => {
		const state = window as unknown as { motionCls: number };
		state.motionCls = 0;
		new PerformanceObserver((list) => {
			for (const entry of list.getEntries()) {
				const shift = entry as PerformanceEntry & { hadRecentInput: boolean; value: number };
				if (!shift.hadRecentInput) state.motionCls += shift.value;
			}
		}).observe({ type: 'layout-shift', buffered: true });
	});
}

test('CSS hero entrances finish and scroll arcs/process progress follow the page', async ({ page }, testInfo) => {
	await measureLayoutShift(page);
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/greendawn-home/');
	await ready(page);
	const heroArc = page.locator('.gd-hero__arc path');
	await expect.poll(() => heroArc.evaluate((path) => Number.parseFloat(getComputedStyle(path).strokeDashoffset))).toBe(0);
	await expect.poll(() => transformY(page, '.gd-hero-line--service > span')).toBe(0);
	expect(await page.evaluate(() => (window as unknown as { motionCls: number }).motionCls)).toBeLessThanOrEqual(0.1);
	await expect(page.locator('.gd-hero__media img')).toHaveAttribute('fetchpriority', 'high');
	await testInfo.attach('motion-hero-1440', { body: await page.screenshot(), contentType: 'image/png' });
	const system = await position(page, '#system');
	await scroll(page, system.top - system.viewport * 0.75);
	const systemArc = page.locator('.gd-system__arc path');
	await expect.poll(() => systemArc.evaluate((path) => Number.parseFloat(getComputedStyle(path).strokeDashoffset))).toBeGreaterThan(100);
	await scroll(page, system.top + system.height * 0.65 - system.viewport * 0.45);
	await expect.poll(() => systemArc.evaluate((path) => Number.parseFloat(getComputedStyle(path).strokeDashoffset))).toBeLessThan(1);
	const process = await position(page, '.gd-process__track');
	await scroll(page, process.top + process.height - process.viewport * 0.6);
	await expect.poll(() => page.locator('.gd-process__line span').evaluate((element) => new DOMMatrixReadOnly(getComputedStyle(element).transform).a)).toBeCloseTo(1, 2);
	await expect.poll(() => transformY(page, '.gd-process__steps > li:last-child')).toBeCloseTo(0, 1);
	await testInfo.attach('motion-process-1440', { body: await page.locator('#process').screenshot(), contentType: 'image/png' });
	const final = await position(page, '#final-enquiry');
	await scroll(page, final.top + final.height - final.viewport * 0.5);
	await expect.poll(() => page.locator('.gd-final-cta > svg path').evaluate((path) => Number.parseFloat(getComputedStyle(path).strokeDashoffset))).toBeLessThan(1);
});

test('reduced motion avoids GSAP downloads and live preference changes restore static content', async ({ page }, testInfo) => {
	const scripts: string[] = [];
	page.on('request', (request) => { if (request.resourceType() === 'script') scripts.push(request.url()); });
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto('/greendawn-home/');
	await expect(page.locator('[data-gd-home-motion]')).not.toHaveAttribute('data-gd-scroll-ready');
	await expect(page.locator('.gd-hero-line--service > span')).toHaveCSS('animation-name', 'none');
	expect(scripts.some((url) => /(?:scroll-motion|ScrollTrigger)-/.test(url))).toBe(false);
	await page.emulateMedia({ reducedMotion: 'no-preference' });
	await ready(page);
	await expect(page.locator('.gd-solutions__layout')).toHaveClass(/gd-solutions--motion/);
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await expect(page.locator('[data-gd-home-motion]')).not.toHaveAttribute('data-gd-scroll-ready');
	await expect(page.locator('.gd-solutions__layout')).not.toHaveClass(/gd-solutions--motion/);
	for (const selector of ['.gd-problem__fragments > li:first-child', '.gd-system__cards', '.gd-system__card:first-child', '.gd-process__steps > li:first-child', '.gd-case-study__story', '.gd-hero__parallax']) {
		await expect(page.locator(selector)).toHaveCSS('transform', 'none');
	}
	await expect(page.locator('.gd-system__arc path')).toHaveCSS('stroke-dashoffset', '0px');
	await expect(page.locator('.gd-final-cta > svg path')).toHaveCSS('stroke-dashoffset', '0px');
	await testInfo.attach('motion-reduced-1440', { body: await page.screenshot(), contentType: 'image/png' });
	await page.locator('.gd-solution-row--workplace').focus();
	await page.emulateMedia({ reducedMotion: 'no-preference' });
	await ready(page);
	await expect(page.locator('.gd-solutions--motion')).toHaveCount(1);
	await expect(page.locator('.gd-solution-image--workplace')).toBeVisible();
});

test('motion stays contained at narrow, tablet, desktop and short viewport sizes', async ({ page }, testInfo) => {
	await measureLayoutShift(page);
	for (const [width, height] of [[320, 850], [390, 850], [800, 900], [1440, 900], [1280, 500]]) {
		await page.setViewportSize({ width: width!, height: height! });
		await page.goto('/greendawn-home/');
		await ready(page);
		const problem = await position(page, '.gd-problem__model');
		await scroll(page, problem.top - problem.viewport * 0.7);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(await page.evaluate(() => document.documentElement.clientWidth));
		await expect.poll(() => page.locator('.gd-problem__fragments > li:first-child').evaluate((element) => element.style.transform)).toBe('');
		await expect(page.locator('.gd-problem__answer')).toHaveCSS('clip-path', 'none');
		const cls = await page.evaluate(() => (window as unknown as { motionCls: number }).motionCls);
		expect(cls).toBeLessThanOrEqual(0.1);
		await testInfo.attach(`motion-layout-shift-${width}-${height}`, { body: JSON.stringify({ width, height, cls }), contentType: 'application/json' });
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(await page.evaluate(() => document.documentElement.clientWidth));
		if (width! < 992 || height! < 705) await expect(page.locator('.gd-hero__parallax')).toHaveCSS('transform', 'none');
		await testInfo.attach(`motion-problem-${width}-${height}`, { body: await page.locator('#problem').screenshot(), contentType: 'image/png' });
	}
});

test('desktop service selection follows scrolling and keeps focus priority during delayed image loading', async ({ page }, testInfo) => {
	await page.setViewportSize({ width: 1440, height: 900 });
	let release: () => void = () => undefined;
	const pending = new Promise<void>((resolve) => { release = resolve; });
	let requested = false;
	await page.route(/\/uploads\/.*commercial-ev-workplace-charging.*\.webp/, async (route) => { requested = true; await pending; await route.continue(); });
	try {
		await page.goto('/greendawn-home/');
		await ready(page);
		const fleet = await position(page, '.gd-solution-row--fleet');
		await scroll(page, fleet.top - fleet.viewport * 0.58 + 8);
		const fleetImage = page.locator('.gd-solution-image--fleet');
		await expect(fleetImage).toBeVisible();
		const workplace = page.locator('.gd-solution-row--workplace');
		await workplace.focus();
		await expect.poll(() => requested).toBe(true);
		await expect(fleetImage).toBeVisible();
		await expect(page.locator('.gd-solution-image--workplace')).toBeHidden();
		const publicRow = page.locator('.gd-solution-row--public');
		await publicRow.focus();
		await expect(page.locator('.gd-solution-image--public')).toBeVisible();
		release();
		await expect.poll(() => page.locator('.gd-solution-image--workplace img').evaluate((image: HTMLImageElement) => image.complete)).toBe(true);
		await workplace.hover();
		await expect(publicRow).toBeFocused();
		await expect(page.locator('.gd-solution-image--public')).toBeVisible();
		await expect(page.locator('.gd-solution-image--workplace')).toBeHidden();
		await testInfo.attach('motion-services-keyboard-1440', { body: await page.locator('#who-we-help').screenshot(), contentType: 'image/png' });
		await page.locator('.gd-hero__project').focus();
		await page.mouse.move(0, 0);
		const dealership = await position(page, '.gd-solution-row--dealership');
		await scroll(page, dealership.top - dealership.viewport * 0.58 + 8);
		await expect(page.locator('.gd-solution-image--dealership')).toBeVisible();
	} finally { release(); }
});

test('a missing GSAP chunk preserves visible content and native service previews', async ({ page }) => {
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.route(/scroll-motion-.*\.js/, (route) => route.abort());
	await page.goto('/greendawn-home/');
	await expect(page.locator('[data-gd-home-motion]')).not.toHaveAttribute('data-gd-scroll-ready');
	await expect(page.locator('#problem h2')).toBeVisible();
	await expect(page.locator('.gd-system__card')).toHaveCount(3);
	await expect(page.locator('.gd-process__steps > li')).toHaveCount(4);
	await page.locator('.gd-solution-row--workplace').focus();
	await expect(page.locator('.gd-solution-image--workplace')).toBeVisible();
	await expect(page.locator('.gd-solutions__layout')).not.toHaveClass(/gd-solutions--motion/);
});

test('the homepage motion bundle is absent from ordinary pages', async ({ page }) => {
	const requests: string[] = [];
	page.on('request', (request) => requests.push(request.url()));
	await page.goto('/about/');
	await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
	expect(requests.some((url) => /(?:greendawn-home-motion|scroll-motion|ScrollTrigger)-/.test(url))).toBe(false);
});
