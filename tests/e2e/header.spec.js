const { test, expect } = require('@playwright/test');

async function scrollTo(page, y) {
	await page.evaluate((top) => window.scrollTo(0, top), y);
}

test('header fixo acompanha a rolagem da página', async ({ page }) => {
	await page.goto('/');

	const header = page.locator('.site-header');
	const before = await header.boundingBox();
	expect(Math.round(before.y)).toBe(0);

	await scrollTo(page, 1200);
	await expect.poll(async () => Math.round((await header.boundingBox()).y)).toBe(0);
	await expect(header).toBeVisible();
});

test('header marca o estado de rolagem apenas fora do topo', async ({ page }) => {
	await page.goto('/');

	const header = page.locator('.site-header');
	await expect(header).not.toHaveClass(/\bis-stuck\b/);

	await scrollTo(page, 400);
	await expect(header).toHaveClass(/\bis-stuck\b/);

	await scrollTo(page, 0);
	await expect(header).not.toHaveClass(/\bis-stuck\b/);
});

test('skip link aparece acima do header fixo com a página rolada', async ({ page }) => {
	await page.goto('/');
	await scrollTo(page, 900);

	await page.keyboard.press('Tab');

	const skip = page.locator('.skip-link');
	await expect(skip).toBeFocused();
	await expect(skip).toBeVisible();
	await expect.poll(async () => Math.round((await skip.boundingBox()).y)).toBe(0);

	const position = await skip.evaluate((element) => {
		const rect = element.getBoundingClientRect();
		const top = document.elementFromPoint(rect.left + rect.width / 2, rect.top + rect.height / 2);

		return {
			covered: !(top === element || element.contains(top)),
			top: Math.round(rect.top),
			bottom: Math.round(rect.bottom),
			viewport: window.innerHeight,
		};
	});

	expect(position.covered).toBe(false);
	expect(position.top).toBeGreaterThanOrEqual(0);
	expect(position.bottom).toBeLessThanOrEqual(position.viewport);
});

test('pular para o conteúdo não esconde o topo sob o header fixo', async ({ page }) => {
	await page.goto('/');

	await page.keyboard.press('Tab');
	await expect(page.locator('.skip-link')).toBeFocused();

	await page.keyboard.press('Enter');
	await expect(page).toHaveURL(/#conteudo$/);

	const gap = await page.evaluate(() => {
		const header = document.querySelector('.site-header').getBoundingClientRect();
		const main = document.querySelector('main').getBoundingClientRect();
		return Math.round(main.top - header.bottom);
	});

	expect(gap).toBeGreaterThanOrEqual(0);
});

test('menu mobile aberto cabe na tela com o header fixo', async ({ page }) => {
	await page.setViewportSize({ width: 360, height: 640 });
	await page.goto('/');

	await scrollTo(page, 1200);

	const headerTop = await page.evaluate(() =>
		Math.round(document.querySelector('.site-header').getBoundingClientRect().top),
	);
	expect(headerTop).toBe(0);

	const toggle = page.locator('[data-component="menu-toggle"]');
	await toggle.click();

	const nav = page.locator('.site-nav');
	await expect(nav).toBeVisible();

	const geometry = await page.evaluate(() => {
		const navElement = document.querySelector('.site-nav');
		const navBox = navElement.getBoundingClientRect();
		const headerBox = document.querySelector('.site-header').getBoundingClientRect();

		return {
			headerTop: Math.round(headerBox.top),
			navBottom: Math.round(navBox.bottom),
			viewport: window.innerHeight,
		};
	});

	expect(geometry.headerTop).toBe(0);
	expect(geometry.navBottom).toBeLessThanOrEqual(geometry.viewport);
});
