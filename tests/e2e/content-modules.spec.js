const { test, expect } = require('@playwright/test');

test('@modules home mostra áreas de atuação', async ({ page }) => {
	await page.goto('/');

	const areas = page.locator('.areas');
	await expect(areas).toBeVisible();
	await expect(areas.locator('.area-card')).toHaveCount(3);
	await expect(areas).toContainText('Áreas de atuação');
});

test('@modules arquivos e páginas da F5.2 respondem', async ({ request }) => {
	const routes = [
		'/areas-de-atuacao/',
		'/areas-de-atuacao/contencioso-e-arbitragem/',
		'/escritorios/',
		'/escritorios/sao-paulo/',
		'/socios/ana-exemplo/',
		'/sobre/',
		'/contato/',
		'/reconhecimentos/',
		'/privacidade/',
	];

	for (const route of routes) {
		const response = await request.get(route);
		expect(response.status(), route).toBe(200);
	}
});

test('@modules áreas e escritórios respeitam a ordem do painel', async ({ page }) => {
	await page.goto('/areas-de-atuacao/');

	const areaTitles = await page.locator('.area-card__title').allTextContents();
	expect(areaTitles[0]).toContain('Contencioso');

	await page.goto('/escritorios/');

	const officeTitles = await page.locator('.office-card__title').allTextContents();
	expect(officeTitles[0]).toContain('São Paulo');

	await page.goto('/socios/');

	const socioTitles = await page.locator('.socio-card__name').allTextContents();
	expect(socioTitles[0]).toContain('Ana Exemplo');
});

test('@modules perfil de sócio tem contato e biografia', async ({ page }) => {
	await page.goto('/socios/ana-exemplo/');

	await expect(page.locator('h1')).toContainText('Ana Exemplo');
	await expect(page.locator('.socio-profile__contact')).toBeVisible();
	await expect(page.locator('.socio-profile__bio')).toBeVisible();
});
