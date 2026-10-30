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
	const previousBox = await section.locator('[data-slide-prev]').boundingBox();
	const nextBox = await section.locator('[data-slide-next]').boundingBox();

	expect(sectionBox).not.toBeNull();
	expect(previousBox).not.toBeNull();
	expect(nextBox).not.toBeNull();

	const previousDistance = previousBox.x - sectionBox.x;
	const nextDistance = sectionBox.x + sectionBox.width - (nextBox.x + nextBox.width);

	expect(previousDistance).toBeLessThan(90);
	expect(nextDistance).toBeLessThan(90);
});
