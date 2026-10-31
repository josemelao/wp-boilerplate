const { test, expect } = require('@playwright/test');

test('@office single mostra horário, contato, CTA e outro escritório', async ({ page }) => {
	await page.goto('/escritorios/sao-paulo/');

	await expect(page.locator('h1')).toHaveText('São Paulo');
	await expect(page.locator('.content-page__image')).toHaveCount(1);
	await expect(page.locator('.office-hours li')).toHaveCount(2);

	const contact = page.locator('.office-single__contact');
	await expect(contact.locator('address')).toContainText('São Paulo');
	await expect(contact.locator('a[href^="tel:"]')).toHaveCount(1);
	await expect(contact.locator('a[href^="mailto:"]')).toHaveCount(1);
	await expect(contact.locator('a[href*="maps.google.com"]')).toHaveCount(1);

	const cta = page.locator('.office-cta a');
	await expect(cta).toHaveAttribute('href', /\/contato\/$/);
	await expect(cta).toContainText('Fale conosco');

	const related = page.locator('.office-related');
	await expect(related.locator('.office-card')).toHaveCount(1);
	await expect(related.locator('a[href*="/escritorios/sao-paulo/"]')).toHaveCount(0);
});

test('@office single esconde o horário quando vazio', async ({ page }) => {
	await page.goto('/escritorios/rio-de-janeiro/');

	await expect(page.locator('.office-hours')).toHaveCount(0);
	await expect(page.locator('.office-cta')).toHaveCount(1);
	await expect(page.locator('.office-related .office-card')).toHaveCount(1);
});

test('@office single alinha foto e conteúdo com a área do título', async ({ page }) => {
	await page.setViewportSize({ width: 1280, height: 900 });
	await page.goto('/escritorios/sao-paulo/');

	const boxes = await page.evaluate(() => {
		const rect = (selector) => {
			const element = document.querySelector(selector);
			return element ? element.getBoundingClientRect() : null;
		};

		return {
			title: rect('h1'),
			image: rect('.content-page__image'),
			layout: rect('.office-single__main'),
		};
	});

	expect(boxes.title).not.toBeNull();
	expect(boxes.image).not.toBeNull();
	expect(boxes.layout).not.toBeNull();

	expect(Math.abs(boxes.image.left - boxes.title.left)).toBeLessThanOrEqual(1);
	expect(Math.abs(boxes.layout.left - boxes.title.left)).toBeLessThanOrEqual(1);
	expect(boxes.image.width).toBeGreaterThan(1000);
});
