const { test, expect } = require('@playwright/test');

test('@media home usa WebP e preload do LCP', async ({ page }) => {
	await page.goto('/');

	const preload = page.locator('link[rel="preload"][as="image"]').first();
	await expect(preload).toHaveCount(1);

	const preloadHref = await preload.getAttribute('href');
	expect(preloadHref).toMatch(/\.webp(\?|$)/);

	const hero = page.locator('.hero__image').first();
	const heroSrc = await hero.getAttribute('src');
	expect(heroSrc).toMatch(/\.webp(\?|$)/);

	const story = page.locator('.stories__image').first();
	const storySrc = await story.getAttribute('src');
	expect(storySrc).toMatch(/\.webp(\?|$)/);
});
