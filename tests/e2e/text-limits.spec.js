const { test, expect } = require('@playwright/test');

const lineClamp = (locator) => locator.evaluate((element) => getComputedStyle(element).webkitLineClamp);

test('@design textos longos são limitados visualmente', async ({ page }) => {
	await page.goto('/');

	expect(await lineClamp(page.locator('.hero__text').first())).toBe('3');
	expect(await lineClamp(page.locator('.stories__summary').first())).toBe('2');
	expect(await lineClamp(page.locator('.post-card__excerpt').first())).toBe('3');
	expect(await lineClamp(page.locator('.area-card__excerpt').first())).toBe('3');
	expect(await lineClamp(page.locator('.area-card__title').first())).toBe('3');

	await page.goto('/historias/historia-04/');

	expect(await lineClamp(page.locator('.story-single__lead').first())).toBe('4');
	expect(await lineClamp(page.locator('.story-card__summary').first())).toBe('2');

	await page.goto('/socios/carla-exemplo/');

	expect(await lineClamp(page.locator('.socio-profile__lead').first())).toBe('4');

	await page.goto('/areas-de-atuacao/contencioso-e-arbitragem/');

	expect(await lineClamp(page.locator('.content-archive__description').first())).toBe('4');

	await page.goto('/?s=Exemplo');

	expect(await lineClamp(page.locator('.search-result__excerpt').first())).toBe('3');
	expect(await lineClamp(page.locator('.search-result__title').first())).toBe('3');
});
