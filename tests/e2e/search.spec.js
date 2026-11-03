const { test, expect } = require('@playwright/test');

test('@search busca global identifica a seção de cada resultado', async ({ page }) => {
	await page.goto('/?s=decis%C3%B5es');
	expect(await page.locator('.search-result').count()).toBeGreaterThan(0);
	await expect(page.locator('.search-result__type').first()).toHaveText('Artigo');

	await page.goto('/?s=Carla');
	await expect(page.locator('.search-result__type').first()).toHaveText('Sócio');
	await expect(page.locator('.search-result a[href*="/socios/carla-exemplo/"]')).toHaveCount(1);

	await page.goto('/?s=S%C3%A3o%20Paulo');
	await expect(page.locator('.search-result__type').first()).toHaveText('Escritório');
	await expect(page.locator('.search-result a[href*="/escritorios/sao-paulo/"]')).toHaveCount(1);

	await page.goto('/?s=Tribut%C3%A1rio');
	expect(await page.locator('.search-result__type', { hasText: 'Área de atuação' }).count()).toBeGreaterThan(0);

	await page.goto('/?s=Sobre');
	await expect(page.locator('.search-result a[href*="/sobre/"]')).toHaveCount(1);
	await expect(page.locator('.search-result__type', { hasText: 'Página' })).toHaveCount(1);

	await page.goto('/?s=O%20que%20vem%20pela%20frente');
	await expect(page.locator('.search-result__type').first()).toHaveText('História');
	await expect(page.locator('.search-result a[href*="/historias/historia-04/"]')).toHaveCount(1);
});

test('@search estado vazio com termo inexistente', async ({ page }) => {
	await page.goto('/?s=zzzzzz-nao-existe');

	await expect(page.locator('.search-result')).toHaveCount(0);
	await expect(page.locator('.content-archive__empty')).toContainText('Nenhum conteúdo encontrado');
});

test('@search campo do footer busca no site inteiro', async ({ page }) => {
	await page.goto('/');

	const footer = page.locator('.site-footer');
	const field = footer.locator('.search-field');

	await expect(field).toHaveAccessibleName('Buscar no site');
	await expect(field).toHaveAttribute('placeholder', 'Buscar no site');

	await field.fill('Carla');
	await field.press('Enter');

	await expect(page).toHaveURL(/[?&]s=/);
	expect(await page.locator('.search-result').count()).toBeGreaterThan(0);
	await expect(page.locator('.search-result__type').first()).toHaveText('Sócio');
});

test('@search resultados são uma lista editorial sem imagens', async ({ page }) => {
	await page.goto('/?s=Exemplo');

	await expect(page.locator('.search-results__list')).toHaveCount(1);
	await expect(page.locator('.search-result')).toHaveCount(4);
	await expect(page.locator('.search-result img')).toHaveCount(0);
});
