const { test, expect } = require('@playwright/test');

test('@story single mostra crédito e outras histórias', async ({ page }) => {
	await page.goto('/historias/historia-04/');

	await expect(page.locator('.story-single__credit')).toHaveText('Imagem demonstrativa');
	await expect(page.locator('.share')).toBeVisible();

	const description = await page.locator('meta[name="description"]').getAttribute('content');
	expect(description).toContain('Seguimos construindo relações de longo prazo');

	const related = page.locator('.story-related');
	await expect(related.locator('.story-card')).toHaveCount(3);
	await expect(related.locator('a[href*="/historias/historia-04/"]')).toHaveCount(0);
	await expect(related.locator('.story-card__title').first()).not.toBeEmpty();
});

test('@story single esconde o crédito quando vazio', async ({ page }) => {
	await page.goto('/historias/historia-01/');

	await expect(page.locator('.story-single__credit')).toHaveCount(0);
	await expect(page.locator('.story-related .story-card')).toHaveCount(3);
});

test('@story single corpo alinha com a área do título', async ({ page }) => {
	await page.setViewportSize({ width: 1280, height: 900 });
	await page.goto('/historias/historia-04/');

	const boxes = await page.evaluate(() => {
		const rect = (selector) => {
			const element = document.querySelector(selector);
			return element ? element.getBoundingClientRect() : null;
		};

		return {
			title: rect('.story-single__hero-content h1'),
			content: rect('.story-single__content'),
		};
	});

	expect(boxes.title).not.toBeNull();
	expect(boxes.content).not.toBeNull();
	expect(Math.abs(boxes.content.left - boxes.title.left)).toBeLessThanOrEqual(1);
	expect(boxes.content.width).toBeGreaterThan(1000);
});
