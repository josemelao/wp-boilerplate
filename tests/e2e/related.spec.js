const { test, expect } = require('@playwright/test');

test('@related área lista os sócios da área', async ({ page }) => {
	await page.goto('/areas-de-atuacao/tributario/');

	const team = page.locator('.area-team');
	await expect(team.locator('.socio-card')).toHaveCount(1);
	await expect(team.locator('.socio-card__name')).toContainText('Carla Exemplo');
	await expect(team.locator('a[href*="/socios/carla-exemplo/"]')).toHaveCount(1);
});

test('@related artigo lista artigos da mesma categoria', async ({ page }) => {
	await page.goto('/artigo-01/');

	const related = page.locator('.related-posts');
	expect(await related.locator('.post-card').count()).toBeGreaterThan(0);
	await expect(related.locator('a[href*="/artigo-03/"]')).toHaveCount(1);
	await expect(related.locator('a[href*="/artigo-01/"]')).toHaveCount(0);
});

test('@related artigo sem relacionados esconde a seção', async ({ page }) => {
	await page.goto('/artigo-02/');

	await expect(page.locator('.related-posts')).toHaveCount(0);
});
