const { test, expect } = require('@playwright/test');

/**
 * Lê o scaleX aplicado na barra de progresso.
 *
 * @param {import('@playwright/test').Page} page Página.
 * @return {Promise<number>}
 */
async function progressScale(page) {
	return page.locator('[data-rotation-progress]').evaluate((element) => {
		const transform = getComputedStyle(element).transform;

		if (!transform || transform === 'none') {
			return 0;
		}

		return new DOMMatrixReadOnly(transform).a;
	});
}

test('@hero barra de progresso preenche em 6 segundos', async ({ page }) => {
	await page.goto('/');

	const track = page.locator('.hero__progress');
	const bar = page.locator('[data-rotation-progress]');

	await expect(track).toHaveCount(1);
	await expect(track).toHaveAttribute('aria-hidden', 'true');
	await expect(bar).toHaveCount(1);

	const styles = await track.evaluate((element) => {
		const computed = getComputedStyle(element);
		const fill = getComputedStyle(element.querySelector('[data-rotation-progress]'));

		return {
			height: computed.height,
			color: fill.backgroundColor,
			duration: fill.animationDuration,
		};
	});

	expect(styles.height).toBe('2px');
	expect(styles.color).toBe('rgb(165, 28, 48)');
	expect(styles.duration).toBe('6s');

	await page.waitForTimeout(900);

	const started = await progressScale(page);
	expect(started).toBeGreaterThan(0.05);
	expect(started).toBeLessThan(0.5);
});

test('@hero barra reinicia ao trocar de slide', async ({ page }) => {
	await page.goto('/');

	await page.waitForTimeout(900);
	expect(await progressScale(page)).toBeGreaterThan(0.05);

	await page.locator('.hero__bullet').nth(1).click();
	await page.waitForTimeout(80);

	expect(await progressScale(page)).toBeLessThan(0.1);

	// Sai do foco do bullet para o autoplay (e a barra) voltarem a rodar.
	await page.locator('.site-header').click({ position: { x: 5, y: 5 } });
	await page.waitForTimeout(700);
	expect(await progressScale(page)).toBeGreaterThan(0.05);
});

test('@hero botão alterna pause e play e congela a barra', async ({ page }) => {
	await page.goto('/');

	const toggle = page.locator('.hero [data-rotation-toggle]');
	const pauseIcon = page.locator('.hero__toggle-icon--pause');
	const playIcon = page.locator('.hero__toggle-icon--play');

	await expect(toggle).toHaveAttribute('aria-pressed', 'false');
	await expect(toggle).toHaveAttribute('aria-label', 'Pausar apresentação');
	await expect(pauseIcon).toBeVisible();
	await expect(playIcon).toBeHidden();

	await page.waitForTimeout(700);

	await toggle.click();
	await expect(toggle).toHaveAttribute('aria-pressed', 'true');
	await expect(toggle).toHaveAttribute('aria-label', 'Retomar apresentação');
	await expect(playIcon).toBeVisible();
	await expect(pauseIcon).toBeHidden();

	const paused = await progressScale(page);
	await page.waitForTimeout(700);
	expect(await progressScale(page)).toBeCloseTo(paused, 2);

	await toggle.click();
	await expect(toggle).toHaveAttribute('aria-pressed', 'false');
	await expect(pauseIcon).toBeVisible();
	await expect(playIcon).toBeHidden();

	await page.waitForTimeout(600);
	expect(await progressScale(page)).toBeGreaterThan(paused);
});
