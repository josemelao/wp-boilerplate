const { test, expect } = require('@playwright/test');

test('@design home alterna superfícies e separa as seções', async ({ page }) => {
	await page.goto('/');

	const surfaces = await page.evaluate(() => {
		const read = (selector) => {
			const element = document.querySelector(selector);

			if (!element) {
				return null;
			}

			const computed = getComputedStyle(element);

			return {
				background: computed.backgroundColor,
				borderTop: computed.borderTopWidth,
				borderBottom: computed.borderBottomWidth,
			};
		};

		return {
			featured: read('.featured'),
			areas: read('.areas'),
			testimonials: read('.testimonials'),
			panel: read('.testimonials__panel'),
			insights: read('.insights'),
		};
	});

	expect(surfaces.featured.background).toBe('rgb(255, 255, 255)');
	expect(surfaces.areas.background).toBe('rgb(243, 241, 236)');
	expect(surfaces.areas.borderTop).toBe('1px');
	expect(surfaces.areas.borderBottom).toBe('1px');
	expect(surfaces.testimonials.background).toBe('rgb(22, 50, 74)');
	expect(surfaces.panel.background).toBe('rgb(22, 50, 74)');
	expect(surfaces.insights.borderTop).toBe('1px');
});

test('@design depoimentos continuam girando com o mouse sobre a seção', async ({ page }) => {
	await page.goto('/');

	const section = page.locator('.testimonials');
	await section.scrollIntoViewIfNeeded();
	const box = await section.boundingBox();

	await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);

	const before = await page.locator('.testimonials__item.is-active .testimonials__headline').textContent();
	await page.waitForTimeout(9000);
	const after = await page.locator('.testimonials__item.is-active .testimonials__headline').textContent();

	expect(after).not.toBe(before);
});

test('@design depoimentos têm painel escuro e controles nomeados', async ({ page }) => {
	await page.goto('/');

	const panel = page.locator('.testimonials__panel');
	await expect(panel).toHaveCount(1);

	const colors = await panel.evaluate((element) => {
		const quote = element.querySelector('.testimonials__headline');

		return {
			panel: getComputedStyle(element).color,
			quote: quote ? getComputedStyle(quote).color : '',
		};
	});

	expect(colors.panel).toBe('rgb(255, 255, 255)');
	expect(colors.quote).toBe('rgb(255, 255, 255)');

	const toggle = panel.locator('[data-rotation-toggle]');
	await expect(toggle).toHaveAttribute('aria-label', 'Pausar apresentação');
	await expect(panel.locator('.testimonials__toggle-icon--pause')).toBeVisible();
	await expect(panel.locator('.testimonials__toggle-icon--play')).toBeHidden();

	await toggle.click();

	await expect(toggle).toHaveAttribute('aria-label', 'Retomar apresentação');
	await expect(panel.locator('.testimonials__toggle-icon--play')).toBeVisible();
	await expect(panel.locator('.testimonials__toggle-icon--pause')).toBeHidden();
});
