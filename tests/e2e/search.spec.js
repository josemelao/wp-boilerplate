const { test, expect } = require('@playwright/test');

test('@search busca somente artigos e mostra estado vazio', async ({ page }) => {
	await page.goto('/?s=decis%C3%B5es');

	expect(await page.locator('.post-card').count()).toBeGreaterThan(0);
	await expect(page.locator('h1')).toContainText('Resultados');

	await page.goto('/?s=Sample');

	expect(await page.locator('.post-card').count()).toBe(0);
	await expect(page.locator('.content-archive__empty')).toContainText('Nenhum conteúdo encontrado');
});

test('@search campo do footer envia a busca', async ({ page }) => {
	await page.goto('/');

	const footer = page.locator('.site-footer');
	await expect(footer.locator('.search-form')).toBeVisible();

	await footer.locator('.search-field').fill('decisões');
	await footer.locator('.search-field').press('Enter');

	await expect(page).toHaveURL(/[?&]s=/);
	expect(await page.locator('.post-card').count()).toBeGreaterThan(0);
});
