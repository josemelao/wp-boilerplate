const { test, expect } = require('@playwright/test');

test('@socio single mostra cargo, áreas, formação, idiomas e OAB', async ({ page }) => {
	await page.goto('/socios/ana-exemplo/');

	await expect(page.locator('h1')).toHaveText('Ana Exemplo');
	await expect(page.locator('.socio-profile__role')).toHaveText('Sócia');

	const areaLinks = page.locator('.socio-profile__areas a');
	await expect(areaLinks).toHaveCount(1);
	await expect(areaLinks.first()).toHaveAttribute('href', /\/areas-de-atuacao\/contencioso-e-arbitragem\/$/);

	await expect(page.locator('.socio-profile__education li')).toHaveCount(2);
	await expect(page.locator('.socio-profile__languages li')).toHaveCount(3);
	await expect(page.locator('.socio-profile__oab')).toContainText('OAB/SP');
	await expect(page.locator('.socio-profile__bio')).toBeVisible();
	await expect(page.locator('.socio-profile__back')).toHaveAttribute('href', /\/socios\/$/);
});

test('@socio single esconde detalhes vazios', async ({ page }) => {
	await page.goto('/socios/diego-exemplo/');

	await expect(page.locator('h1')).toHaveText('Diego Exemplo');
	await expect(page.locator('.socio-profile__areas')).toHaveCount(1);
	await expect(page.locator('.socio-profile__detail')).toHaveCount(0);
	await expect(page.locator('.socio-profile__bio')).toBeVisible();
	await expect(page.locator('.socio-profile__contact')).toBeVisible();
});
