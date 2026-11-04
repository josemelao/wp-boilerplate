const { test, expect } = require('@playwright/test');

test('@smoke home carrega sem erros de console', async ({ page }) => {
	const errors = [];

	page.on('pageerror', (error) => {
		errors.push(error.message);
	});
	page.on('console', (message) => {
		if ('error' === message.type()) {
			errors.push(message.text());
		}
	});

	await page.goto('/');

	await expect(page.locator('h1')).toHaveCount(1);
	await expect(page.locator('[data-component="hero"]')).toBeVisible();
	await expect(page.locator('[data-component="carousel"]')).toBeVisible();
	await expect(page.locator('[data-component="stories"]')).toBeVisible();
	await expect(page.locator('[data-component="testimonials"]')).toBeVisible();

	expect(errors, errors.join('\n')).toEqual([]);
});

test('@smoke links internos do menu não guardam o host local', async ({ page }) => {
	await page.goto('/');

	const hrefs = await page
		.locator('#menu-principal .menu-item-object-custom > a')
		.evaluateAll((links) => links.map((link) => link.getAttribute('href')));

	expect(hrefs.length).toBeGreaterThan(0);

	for (const href of hrefs) {
		expect(href.startsWith('/'), href).toBeTruthy();
	}
});

test('@smoke rotas principais respondem', async ({ request }) => {
	const routes = ['/', '/artigos/', '/socios/', '/historias/historia-01/'];

	for (const route of routes) {
		const response = await request.get(route);
		expect(response.status(), route).toBe(200);
	}

	const missing = await request.get('/rota-inexistente-e2e/');
	expect(missing.status()).toBe(404);
});
