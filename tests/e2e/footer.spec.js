const { test, expect } = require('@playwright/test');

test('@footer organiza informações e linka escritórios', async ({ page }) => {
	await page.goto('/');

	const footer = page.locator('.site-footer');

	await expect(footer.locator('.site-footer__top')).toBeVisible();
	await expect(footer.locator('.site-footer__main')).toBeVisible();
	await expect(footer.locator('.site-footer__bottom')).toBeVisible();
	await expect(footer.locator('.site-footer__search .search-form')).toBeVisible();

	const officeLink = footer.locator('.site-footer__offices a').filter({ hasText: 'São Paulo' });
	await expect(officeLink).toHaveAttribute('href', /escritorios\/sao-paulo/);

	await expect(footer.locator('.site-footer__contact')).toHaveAttribute('href', /contato/);
});
