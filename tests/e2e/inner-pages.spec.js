const { test, expect } = require('@playwright/test');

test('@inner artigo individual tem compartilhamento acessível', async ({ page, context }) => {
	await page.goto('/artigo-01/');

	const share = page.locator('.share');
	await expect(share).toBeVisible();
	await expect(share.locator('a')).toHaveCount(5);

	const hrefs = await share.locator('a').evaluateAll((links) => links.map((link) => link.getAttribute('href') || ''));
	const copy = share.locator('[data-share-copy]');

	expect(hrefs.some((href) => href.includes('wa.me'))).toBeTruthy();
	expect(hrefs.some((href) => href.includes('twitter.com/intent/tweet'))).toBeTruthy();
	expect(hrefs.some((href) => href.includes('linkedin.com/sharing'))).toBeTruthy();
	expect(hrefs.some((href) => href.includes('facebook.com/sharer'))).toBeTruthy();
	expect(hrefs.some((href) => href.startsWith('mailto:'))).toBeTruthy();
	expect(hrefs.some((href) => href.includes('artigo-01'))).toBeTruthy();

	await expect(copy).toHaveCount(1);
	await expect(copy).toHaveAttribute('data-url', /artigo-01/);
	await expect(copy).toHaveAttribute('aria-label', /copiar/i);

	await context.grantPermissions(['clipboard-read', 'clipboard-write']);
	await copy.click();
	await expect(share.locator('[data-share-status]')).toHaveText('Link copiado.');
});

test('@inner artigo mantém coluna de leitura confortável', async ({ page }) => {
	await page.goto('/artigo-01/');

	const content = page.locator('.entry-content');
	const box = await content.boundingBox();

	expect(box).not.toBeNull();
	expect(box.width).toBeLessThanOrEqual(800);
});
