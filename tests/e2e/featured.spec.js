const { test, expect } = require('@playwright/test');

/**
 * Arrasta o viewport do carrossel com o mouse.
 *
 * @param {import('@playwright/test').Page} page Página.
 * @param {import('@playwright/test').Locator} viewport Viewport.
 * @param {number} distance Distância horizontal (negativa = esquerda).
 * @param {Object} [options] Opções.
 * @param {number} [options.steps] Passos do mouse.
 * @param {number} [options.delay] Espera entre os movimentos.
 */
async function dragViewport(page, viewport, distance, options = {}) {
	const steps = options.steps || 4;
	const delay = options.delay || 0;

	await viewport.scrollIntoViewIfNeeded();

	const box = await viewport.boundingBox();

	if (!box) {
		throw new Error('Viewport do carrossel sem bounding box.');
	}

	const startX = box.x + box.width / 2;
	const y = box.y + box.height / 2;

	await page.mouse.move(startX, y);
	await page.mouse.down();
	await page.mouse.move(startX + distance, y, { steps });

	if (delay > 0) {
		await page.waitForTimeout(delay);
	}

	await page.mouse.up();
}

test.beforeEach(async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('[data-carousel-viewport]')).toBeVisible();
});

test('@featured começa centralizado no primeiro card', async ({ page }) => {
	await expect(page.locator('[data-carousel-viewport]')).toHaveAttribute('data-centered-index', '0');
});

test('@featured flick curto e rápido avança exatamente um card', async ({ page }) => {
	const viewport = page.locator('[data-carousel-viewport]');

	await dragViewport(page, viewport, -120, { steps: 1 });
	await page.waitForTimeout(700);

	await expect(viewport).toHaveAttribute('data-centered-index', '1');
	await expect(viewport).not.toHaveClass(/is-dragging/);
	await expect(viewport).not.toHaveClass(/is-settling/);
});

test('@featured arrasto curto e lento volta para o card atual', async ({ page }) => {
	const viewport = page.locator('[data-carousel-viewport]');

	await viewport.scrollIntoViewIfNeeded();

	const box = await viewport.boundingBox();
	const startX = box.x + box.width / 2;
	const y = box.y + box.height / 2;

	await page.mouse.move(startX, y);
	await page.mouse.down();
	await page.mouse.move(startX - 40, y);
	await page.waitForTimeout(200);
	await page.mouse.move(startX - 80, y);
	await page.waitForTimeout(200);
	await page.mouse.up();
	await page.waitForTimeout(700);

	await expect(viewport).toHaveAttribute('data-centered-index', '0');
});

test('@featured setas e teclado navegam entre os cards', async ({ page }) => {
	const viewport = page.locator('[data-carousel-viewport]');

	await page.locator('[data-carousel-next]').click();
	await page.waitForTimeout(500);
	await expect(viewport).toHaveAttribute('data-centered-index', '1');

	await viewport.focus();
	await page.keyboard.press('ArrowLeft');
	await page.waitForTimeout(500);
	await expect(viewport).toHaveAttribute('data-centered-index', '0');
});

test('@featured settle anima em frames contínuos', async ({ page }) => {
	await page.evaluate(() => {
		const element = document.querySelector('[data-carousel-viewport]');
		const start = performance.now();

		window.__scrollSamples = [];

		function frame(now) {
			window.__scrollSamples.push({
				time: now - start,
				left: element.scrollLeft,
			});

			if (now - start < 900) {
				requestAnimationFrame(frame);
			}
		}

		requestAnimationFrame(frame);
	});

	await page.locator('[data-carousel-next]').click();
	await page.waitForTimeout(900);

	const samples = await page.evaluate(() => window.__scrollSamples);

	expect(samples.length).toBeGreaterThan(3);
	expect(samples[samples.length - 1].left).toBeGreaterThan(samples[0].left);

	for (let index = 1; index < samples.length; index += 1) {
		expect(samples[index].left + 1).toBeGreaterThanOrEqual(samples[index - 1].left);
	}

	expect(new Set(samples.map((sample) => Math.round(sample.left))).size).toBeGreaterThan(3);
});

test('@featured clique depois do arrasto não navega', async ({ page }) => {
	const viewport = page.locator('[data-carousel-viewport]');
	const url = page.url();

	await dragViewport(page, viewport, -160, { steps: 4 });
	await page.waitForTimeout(700);

	expect(page.url()).toBe(url);
});

test('@featured respeita prefers-reduced-motion', async ({ page }) => {
	await page.emulateMedia({ reducedMotion: 'reduce' });
	await page.goto('/');

	const viewport = page.locator('[data-carousel-viewport]');

	await dragViewport(page, viewport, -120, { steps: 1 });
	await page.waitForTimeout(100);

	await expect(viewport).toHaveAttribute('data-centered-index', '1');
	await expect(viewport).not.toHaveClass(/is-settling/);
});
