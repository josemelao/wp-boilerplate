const { test, expect } = require('@playwright/test');

test('@area single mostra serviços, setores, CTA e outras áreas', async ({ page }) => {
	await page.goto('/areas-de-atuacao/tributario/');

	await expect(page.locator('h1')).toHaveText('Tributário');
	await expect(page.locator('.area-services li')).toHaveCount(4);
	await expect(page.locator('.area-sectors li')).toHaveCount(3);

	const cta = page.locator('.area-cta a');
	await expect(cta).toHaveAttribute('href', /\/contato\/$/);
	await expect(cta).toContainText('Fale com a equipe');

	const related = page.locator('.area-related');
	await expect(related.locator('.area-card')).toHaveCount(3);
	await expect(related.locator('a[href*="/areas-de-atuacao/tributario/"]')).toHaveCount(0);
});

test('@area single esconde serviços e setores quando vazios', async ({ page }) => {
	await page.goto('/areas-de-atuacao/societario-e-ma/');

	await expect(page.locator('.area-services')).toHaveCount(0);
	await expect(page.locator('.area-sectors')).toHaveCount(0);
	await expect(page.locator('.area-cta')).toHaveCount(1);
	await expect(page.locator('.area-related .area-card')).toHaveCount(3);
});

test('@area single alinha foto e texto com a área do título', async ({ page }) => {
	await page.setViewportSize({ width: 1280, height: 900 });
	await page.goto('/areas-de-atuacao/tributario/');

	const boxes = await page.evaluate(() => {
		const rect = (selector) => {
			const element = document.querySelector(selector);
			return element ? element.getBoundingClientRect() : null;
		};

		return {
			title: rect('h1'),
			image: rect('.content-page__image'),
			content: rect('.area-single .entry-content'),
			services: rect('.area-services'),
		};
	});

	expect(boxes.title).not.toBeNull();
	expect(boxes.image).not.toBeNull();
	expect(boxes.content).not.toBeNull();
	expect(boxes.services).not.toBeNull();

	expect(Math.abs(boxes.image.left - boxes.title.left)).toBeLessThanOrEqual(1);
	expect(Math.abs(boxes.content.left - boxes.title.left)).toBeLessThanOrEqual(1);
	expect(Math.abs(boxes.services.left - boxes.title.left)).toBeLessThanOrEqual(1);
	expect(boxes.image.width).toBeGreaterThan(1000);
});
