const { test, expect } = require('@playwright/test');

test('@pages sobre mostra time, escritórios e CTA', async ({ page }) => {
	await page.goto('/sobre/');

	await expect(page.locator('h1')).toHaveText('Sobre');
	await expect(page.locator('.about-team .socio-card')).toHaveCount(4);
	await expect(page.locator('.about-offices .office-card')).toHaveCount(2);

	const cta = page.locator('.about-cta a');
	await expect(cta).toHaveAttribute('href', /\/contato\/$/);
	await expect(cta).toContainText('Fale conosco');
});

test('@pages contato mostra escritórios e canais, sem formulário', async ({ page }) => {
	await page.goto('/contato/');

	await expect(page.locator('h1')).toHaveText('Contato');
	await expect(page.locator('.contact-offices .office-contact-card')).toHaveCount(2);
	await expect(page.locator('.contact-offices a[href^="mailto:"]')).toHaveCount(2);
	await expect(page.locator('.contact-offices a[href^="tel:"]')).toHaveCount(2);
	await expect(page.locator('.contact-page form')).toHaveCount(0);
});

test('@pages conteúdo alinha com o título', async ({ page }) => {
	await page.setViewportSize({ width: 1280, height: 900 });

	for ( const route of [ '/sobre/', '/contato/' ] ) {
		await page.goto( route );

		const boxes = await page.evaluate(() => {
			const rect = (selector) => {
				const element = document.querySelector(selector);
				return element ? element.getBoundingClientRect() : null;
			};

			return {
				title: rect('h1'),
				content: rect('.content-page__content'),
			};
		});

		expect(boxes.title, route).not.toBeNull();
		expect(boxes.content, route).not.toBeNull();
		expect(Math.abs(boxes.content.left - boxes.title.left), route).toBeLessThanOrEqual(1);
		expect(boxes.content.width, route).toBeGreaterThan(800);
	}
});
