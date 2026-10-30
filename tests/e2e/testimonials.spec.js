const { test, expect } = require('@playwright/test');

test('@testimonials seção aberta e setas nas laterais', async ({ page }) => {
	await page.goto('/');

	const section = page.locator('.testimonials');
	await section.scrollIntoViewIfNeeded();

	const inner = section.locator('.testimonials__inner');
	const styles = await inner.evaluate((element) => {
		const computed = getComputedStyle(element);

		return {
			border: computed.borderTopWidth,
			background: computed.backgroundColor,
			paddingInline: computed.paddingInlineStart,
		};
	});

	expect(styles.border).toBe('0px');
	expect(styles.background).toBe('rgba(0, 0, 0, 0)');
	expect(parseFloat(styles.paddingInline)).toBeLessThan(2);

	const sectionBox = await section.boundingBox();
	const contentBox = await section.locator('.testimonials__content').boundingBox();
	const previousBox = await section.locator('[data-slide-prev]').boundingBox();
	const nextBox = await section.locator('[data-slide-next]').boundingBox();

	expect(sectionBox).not.toBeNull();
	expect(contentBox).not.toBeNull();
	expect(previousBox).not.toBeNull();
	expect(nextBox).not.toBeNull();

	const previousDistance = previousBox.x - contentBox.x;
	const nextDistance = contentBox.x + contentBox.width - (nextBox.x + nextBox.width);

	expect(Math.abs(previousDistance)).toBeLessThan(2);
	expect(Math.abs(nextDistance)).toBeLessThan(2);
});

test('@testimonials setas não cobrem a citação em 320 px', async ({ page }) => {
	await page.setViewportSize({ width: 320, height: 800 });
	await page.goto('/');
	await page.locator('.testimonials').scrollIntoViewIfNeeded();

	const boxes = await page.evaluate(() => {
		const rect = (selector) => {
			const element = document.querySelector(selector);
			return element ? element.getBoundingClientRect() : null;
		};

		return {
			quote: rect('.testimonials__item.is-active blockquote'),
			previous: rect('[data-slide-prev]'),
			next: rect('[data-slide-next]'),
		};
	});

	expect(boxes.quote).not.toBeNull();
	expect(boxes.previous).not.toBeNull();
	expect(boxes.next).not.toBeNull();
	expect(boxes.previous.right).toBeLessThanOrEqual(boxes.quote.left - 8);
	expect(boxes.next.left).toBeGreaterThanOrEqual(boxes.quote.right + 8);
});

test('@testimonials troca com slide direcional', async ({ page }) => {
	await page.goto('/');
	await page.locator('.testimonials').scrollIntoViewIfNeeded();

	const track = page.locator('[data-testimonials-track]');
	const translateX = () => track.evaluate((element) => new DOMMatrixReadOnly(getComputedStyle(element).transform).e);

	expect(Math.abs(await translateX())).toBeLessThan(2);

	await page.locator('.testimonials [data-slide-next]').click();
	await page.waitForTimeout(550);
	expect(await translateX()).toBeLessThan(-100);

	await page.locator('.testimonials [data-slide-prev]').click();
	await page.waitForTimeout(550);
	expect(Math.abs(await translateX())).toBeLessThan(2);
});

test('@testimonials autoplay retoma depois de clicar na seta', async ({ page }) => {
	await page.goto('/');
	await page.locator('.testimonials').scrollIntoViewIfNeeded();
	await page.mouse.move(0, 0);

	await page.locator('.testimonials [data-slide-next]').click();
	const first = await page.locator('.testimonials__item.is-active .testimonials__headline').textContent();

	await page.waitForTimeout(9000);
	const second = await page.locator('.testimonials__item.is-active .testimonials__headline').textContent();

	expect(second).not.toBe(first);
});
