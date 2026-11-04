const { test, expect } = require('@playwright/test');

/**
 * Extrai os canais RGB de uma cor computada.
 *
 * @param {string} color Cor no formato rgb()/rgba().
 * @return {{r: number, g: number, b: number}}
 */
function rgb(color) {
	const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);

	if (!match) {
		throw new Error(`Cor inesperada: ${color}`);
	}

	return {
		r: Number(match[1]),
		g: Number(match[2]),
		b: Number(match[3]),
	};
}

/**
 * Vermelho do título ativo.
 *
 * @param {string} color Cor computada.
 * @return {boolean}
 */
function isRed(color) {
	const value = rgb(color);

	return value.r > 150 && value.g < 80 && value.b < 80;
}

/**
 * Branco ou quase branco.
 *
 * @param {string} color Cor computada.
 * @return {boolean}
 */
function isNearWhite(color) {
	const value = rgb(color);

	return value.r > 235 && value.g > 235 && value.b > 235;
}

/**
 * Instala um sampler de rAF e aciona uma seta das histórias.
 *
 * @param {import('@playwright/test').Page} page Página.
 * @param {'down'|'up'} direction Direção.
 * @return {Promise<{samples: Array<Object>, outgoingId: string}>}
 */
async function collectRotationSamples(page, direction) {
	const active = page.locator('.stories__entry.is-active');
	const outgoingId = await active.getAttribute('data-story-id');

	await page.evaluate((id) => {
		const outgoing = document.querySelector(`.stories__entry[data-story-id="${id}"]`);
		const start = performance.now();

		window.__storySamples = [];

		function frame(now) {
			const current = document.querySelector('.stories__entry.is-active');

			if (!current) {
				return;
			}

			const currentSummary = current.querySelector('[data-story-summary]');
			const outgoingSummary = outgoing.querySelector('[data-story-summary]');

			window.__storySamples.push({
				time: now - start,
				activeId: current.getAttribute('data-story-id'),
				incomingColor: getComputedStyle(current).color,
				incomingOpacity: Number(getComputedStyle(currentSummary).opacity),
				outgoingColor: getComputedStyle(outgoing).color,
				outgoingOpacity: Number(getComputedStyle(outgoingSummary).opacity),
			});

			if (now - start < 800) {
				requestAnimationFrame(frame);
			}
		}

		requestAnimationFrame(frame);
	}, outgoingId);

	await page.locator(`[data-story-${direction}]`).click();
	await page.waitForTimeout(900);

	const samples = await page.evaluate(() => window.__storySamples);

	return {
		samples: samples.filter((sample) => sample.activeId !== outgoingId),
		outgoingId,
	};
}

/**
 * Verifica a troca de conteúdo entre o cartão que entra e o que sai.
 *
 * @param {Array<Object>} samples Amostras pós-clique.
 */
function expectCrossfade(samples) {
	expect(samples.length).toBeGreaterThan(3);

	const first = samples[0];
	const last = samples[samples.length - 1];

	expect(isRed(first.incomingColor), `título que entra não deveria começar vermelho: ${first.incomingColor}`).toBeFalsy();
	expect(first.incomingOpacity).toBeLessThan(0.95);

	expect(isRed(first.outgoingColor), `título que sai deveria começar vermelho: ${first.outgoingColor}`).toBeTruthy();
	expect(first.outgoingOpacity).toBeGreaterThan(0.05);

	const middles = samples.filter(
		(sample) => !isRed(sample.incomingColor) && !isNearWhite(sample.incomingColor),
	);
	expect(middles.length, 'a cor do título que entra deveria passar por um meio-termo').toBeGreaterThan(0);

	expect(isRed(last.incomingColor), `título que entrou deveria terminar vermelho: ${last.incomingColor}`).toBeTruthy();
	expect(last.incomingOpacity).toBeGreaterThan(0.95);
	expect(isNearWhite(last.outgoingColor), `título que saiu deveria terminar branco: ${last.outgoingColor}`).toBeTruthy();
	expect(last.outgoingOpacity).toBeLessThan(0.05);

	const early = samples.find((sample) => sample.time >= 220);

	if (early) {
		expect(early.outgoingOpacity, 'o texto do cartão que sai não deveria ficar sobre o que entra').toBeLessThan(0.1);
	}
}

test('@stories seta para baixo faz fade simétrico de entrada e saída', async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('[data-component="stories"]')).toBeVisible();

	await expect(page.locator('.stories__entry')).toHaveCount(4);
	await expect(page.locator('.stories__entry.is-visible')).toHaveCount(4);
	await expect(page.locator('.stories__entry.is-active')).toHaveCount(1);

	const { samples, outgoingId } = await collectRotationSamples(page, 'down');

	expectCrossfade(samples);

	const outgoingSummary = page.locator(`.stories__entry[data-story-id="${outgoingId}"] .stories__summary`);
	await expect(outgoingSummary).not.toHaveAttribute('hidden');
	await expect(outgoingSummary).toHaveAttribute('aria-hidden', 'true');
});

test('@stories seta para cima faz o mesmo fade e retorna ao item anterior', async ({ page }) => {
	await page.goto('/');

	const activeTitle = page.locator('.stories__entry.is-active .stories__title:visible');
	const initialText = (await activeTitle.textContent()).trim();

	await page.locator('[data-story-down]').click();
	await page.waitForTimeout(800);
	await expect(activeTitle).not.toHaveText(initialText);

	const { samples, outgoingId } = await collectRotationSamples(page, 'up');

	expectCrossfade(samples);
	await expect(activeTitle).toHaveText(initialText);

	const outgoingSummary = page.locator(`.stories__entry[data-story-id="${outgoingId}"] .stories__summary`);
	await expect(outgoingSummary).not.toHaveAttribute('hidden');
	await expect(outgoingSummary).toHaveAttribute('aria-hidden', 'true');
});

test('@stories itens inativos não são clicáveis', async ({ page }) => {
	await page.goto('/');

	const activeTitle = page.locator('.stories__entry.is-active .stories__title:visible');
	const initialText = (await activeTitle.textContent()).trim();

	await expect(activeTitle).toHaveAttribute('href', /.+/);
	await expect(page.locator('[data-story-select]')).toHaveCount(0);

	const inactiveTitle = page.locator('.stories__entry:not(.is-active).is-visible .stories__title:visible').first();

	await expect(inactiveTitle).toBeVisible();
	await inactiveTitle.click();
	await page.waitForTimeout(800);

	await expect(activeTitle).toHaveText(initialText);
});

test('@stories setas são maiores e estão alinhadas horizontalmente', async ({ page }) => {
	await page.goto('/');

	const down = page.locator('[data-story-down]');
	const up = page.locator('[data-story-up]');

	await down.scrollIntoViewIfNeeded();

	const downBox = await down.boundingBox();
	const upBox = await up.boundingBox();
	const downIcon = down.locator('svg');
	const upIcon = up.locator('svg');

	await expect(downIcon).toHaveCount(1);
	await expect(upIcon).toHaveCount(1);

	const downIconBox = await downIcon.boundingBox();
	const upIconBox = await upIcon.boundingBox();

	expect(downBox).not.toBeNull();
	expect(upBox).not.toBeNull();
	expect(downBox.width).toBeGreaterThanOrEqual(44);
	expect(upBox.width).toBeGreaterThanOrEqual(44);
	expect(downBox.height).toBeGreaterThanOrEqual(36);
	expect(upBox.height).toBeGreaterThanOrEqual(36);
	expect(Math.abs(downBox.y - upBox.y)).toBeLessThan(2);
	expect(downIconBox).not.toBeNull();
	expect(upIconBox).not.toBeNull();
	expect(downIconBox.width).toBeGreaterThanOrEqual(24);
	expect(upIconBox.width).toBeGreaterThanOrEqual(24);
	expect(Math.abs((downIconBox.y + downIconBox.height / 2) - (upIconBox.y + upIconBox.height / 2))).toBeLessThan(1);
});

test('@stories item que sobe mantém a posição horizontal', async ({ page }) => {
	await page.goto('/');
	await page.locator('[data-component="stories"]').scrollIntoViewIfNeeded();

	await page.evaluate(() => {
		window.__leftSamples = [];
		const start = performance.now();

		function frame(now) {
			const active = document.querySelector('.stories__entry.is-active');
			const title = active ? active.querySelector('.stories__title') : null;

			if (title) {
				window.__leftSamples.push(title.getBoundingClientRect().left);
			}

			if (now - start < 800) {
				requestAnimationFrame(frame);
			}
		}

		requestAnimationFrame(frame);
	});

	await page.locator('[data-story-up]').click();
	await page.waitForTimeout(900);

	const samples = await page.evaluate(() => window.__leftSamples);

	expect(samples.length).toBeGreaterThan(3);

	const spread = Math.max(...samples) - Math.min(...samples);
	expect(spread).toBeLessThanOrEqual(1);
});

test('@stories fundo acompanha o fade do texto', async ({ page }) => {
	await page.goto('/');

	const backdrop = page.locator('.stories__backdrop').first();
	const duration = await backdrop.evaluate((element) => getComputedStyle(element).transitionDuration);

	expect(duration).toContain('0.32s');
});

test('@stories todo fundo tem imagem', async ({ page }) => {
	await page.goto('/');

	const missing = await page.locator('.stories__backdrop').evaluateAll((elements) =>
		elements.filter((element) => !element.querySelector('img')).length,
	);

	expect(missing).toBe(0);
});

test('@stories fundo começa a trocar no clique e texto mantém o tempo', async ({ page }) => {
	await page.goto('/');
	await page.locator('[data-component="stories"]').scrollIntoViewIfNeeded();

	const before = await page.locator('.stories__entry.is-active').getAttribute('data-story-id');
	await page.locator('[data-story-up]').click();
	await page.waitForTimeout(60);

	const state = await page.evaluate(() => {
		const entry = document.querySelector('.stories__entry.is-active');
		const backdrop = document.querySelector('.stories__backdrop.is-active');
		const entryStyle = getComputedStyle(document.querySelector('.stories__entry'));
		const summaryStyle = getComputedStyle(document.querySelector('.stories__summary'));
		const backdropStyle = getComputedStyle(document.querySelector('.stories__backdrop'));

		return {
			entryId: entry ? entry.getAttribute('data-story-id') : '',
			backdropId: backdrop ? backdrop.getAttribute('data-story-backdrop') : '',
			entryDuration: entryStyle.transitionDuration,
			summaryDuration: summaryStyle.transitionDuration,
			backdropDuration: backdropStyle.transitionDuration,
		};
	});

	expect(state.backdropId).not.toBe(before);
	expect(state.backdropId).toBe(state.entryId);
	expect(state.entryDuration).toContain('0.32s');
	expect(state.summaryDuration).toBe('0.32s');
	expect(state.backdropDuration).toBe('0.32s');
});

test('@stories resumo da home vem do campo Resumo', async ({ page }) => {
	await page.goto('/');

	const active = page.locator('.stories__entry.is-active');
	await expect(active.locator('[data-story-summary]')).toContainText('A história começa com uma equipe pequena');
});

/**
 * Lê a posição vertical de um item da lista em % da altura da seção.
 *
 * @param {import('@playwright/test').Page} page Página.
 * @param {string} selector Seletor do item.
 * @return {Promise<number>} Topo relativo à seção, em percentual.
 */
async function storyOffset(page, selector) {
	return page.evaluate((target) => {
		const element = document.querySelector(target);
		const section = document.querySelector('.stories');

		if (!element || !section) {
			return NaN;
		}

		const box = section.getBoundingClientRect();

		return ((element.getBoundingClientRect().top - box.top) / box.height) * 100;
	}, selector);
}

test('@stories para baixo o item do topo entra rolando', async ({ page }) => {
	await page.goto('/');
	await page.locator('[data-component="stories"]').scrollIntoViewIfNeeded();
	await page.waitForTimeout(300);

	await page.locator('[data-story-down]').click();
	await page.waitForTimeout(50);

	const early = await storyOffset(page, '[data-story-ghost]');
	expect(early, 'o item que entra deveria começar acima da seção').toBeLessThan(-2);

	await page.waitForTimeout(350);

	const later = await storyOffset(page, '[data-story-ghost]');
	expect(later, 'o item que entra deveria estar descendo').toBeGreaterThan(early + 2);
});

test('@stories para cima o primeiro item sai rolando pelo topo', async ({ page }) => {
	await page.goto('/');
	await page.locator('[data-component="stories"]').scrollIntoViewIfNeeded();
	await page.waitForTimeout(300);

	const firstId = await page
		.locator('.stories__entry.is-visible:not(.is-active)')
		.first()
		.getAttribute('data-story-id');
	const selector = `.stories__entry:not([data-story-ghost])[data-story-id="${firstId}"]`;

	await page.locator('[data-story-up]').click();
	await page.waitForTimeout(120);

	const during = await storyOffset(page, selector);
	expect(during, 'o primeiro item deveria ter começado a subir').toBeLessThan(-1);

	await page.waitForTimeout(480);

	const after = await storyOffset(page, selector);
	expect(after, 'o primeiro item deveria terminar fora da seção, acima').toBeLessThanOrEqual(-15);
});

test('@stories existe um único cartão ativo em todos os frames', async ({ page }) => {
	await page.goto('/');
	await page.locator('[data-component="stories"]').scrollIntoViewIfNeeded();
	await page.waitForTimeout(300);

	for (const direction of ['down', 'up']) {
		await page.evaluate(() => {
			window.__activeSamples = [];
			const start = performance.now();

			function frame(now) {
				window.__activeSamples.push(document.querySelectorAll('.stories__entry.is-active').length);

				if (now - start < 780) {
					requestAnimationFrame(frame);
				}
			}

			requestAnimationFrame(frame);
		});

		await page.locator(`[data-story-${direction}]`).click();
		await page.waitForTimeout(900);

		const samples = await page.evaluate(() => window.__activeSamples);
		const max = Math.max(...samples);

		expect(samples.length).toBeGreaterThan(10);
		expect(max, `${direction}: mais de um cartão ativo ao mesmo tempo`).toBeLessThanOrEqual(1);
		await expect(page.locator('.stories__entry.is-active')).toHaveCount(1);
		await expect(page.locator('[data-story-ghost]')).toHaveCount(0);
	}
});

test('@stories loop funciona com 2 e 3 histórias', async ({ page }) => {
	for (const count of [2, 3]) {
		await page.goto('/');
		await page.evaluate((target) => {
			const list = document.querySelector('.stories__list');
			const items = [...list.querySelectorAll('[data-story-entry]')];
			items.slice(0, items.length - target).forEach((item) => item.remove());
		}, count);

		await page.locator('[data-component="stories"]').scrollIntoViewIfNeeded();
		await page.waitForTimeout(200);

		await page.locator('[data-story-down]').click();
		await page.waitForTimeout(50);

		const ghostTop = await storyOffset(page, '[data-story-ghost]');
		expect(ghostTop, `${count} histórias: o clone deveria entrar por cima`).toBeLessThan(-2);

		await page.waitForTimeout(800);
		await expect(page.locator('.stories__entry')).toHaveCount(count);
		await expect(page.locator('.stories__entry.is-active')).toHaveCount(1);
		await expect(page.locator('[data-story-ghost]')).toHaveCount(0);

		const layout = await page.evaluate(() => {
			const list = document.querySelector('.stories__list');
			const band = getComputedStyle(list, '::after');
			const height = list.getBoundingClientRect().height;

			return {
				bandTop: (parseFloat(band.top) / height) * 100,
				tops: [...document.querySelectorAll('.stories__entry[data-story-entry]')].map((entry) =>
					parseFloat(entry.style.getPropertyValue('--story-top')),
				),
			};
		});

		const visibleHeight = (count - 1) * 22 + 34;
		const topOffset = (100 - visibleHeight) / 2;

		expect(layout.bandTop, `${count} histórias: faixa ativa`).toBeCloseTo(topOffset + (count - 1) * 22, 1);
		layout.tops.forEach((top, index) => {
			expect(top, `${count} histórias: slot ${index}`).toBeCloseTo(topOffset + index * 22, 1);
		});
	}
});

test('@stories mobile usa 80% da largura e título vertical', async ({ page }) => {
	for (const width of [320, 390]) {
		await page.setViewportSize({ width, height: 844 });
		await page.goto('/');
		await page.locator('.stories').scrollIntoViewIfNeeded();
		await page.waitForTimeout(400);

		const heading = await page.locator('.stories__heading').boundingBox();
		const firstTitle = await page
			.locator('.stories__entry.is-visible:not(.is-active) .stories__title:not([hidden])')
			.first()
			.boundingBox();
		const activeTitle = await page
			.locator('.stories__entry.is-active .stories__title:not([hidden])')
			.boundingBox();
		const active = await page.locator('.stories__entry.is-active').boundingBox();

		expect(heading).not.toBeNull();
		expect(firstTitle).not.toBeNull();
		expect(activeTitle).not.toBeNull();
		expect(active).not.toBeNull();

		expect(heading.x + heading.width).toBeLessThanOrEqual(firstTitle.x - 8);
		expect(Math.abs(activeTitle.x - firstTitle.x)).toBeLessThanOrEqual(1);
		expect(active.width).toBeGreaterThanOrEqual(width * 0.78);

		const clamp = await page
			.locator('.stories__entry.is-active .stories__summary')
			.evaluate((element) => getComputedStyle(element).webkitLineClamp);
		expect(clamp).toBe('4');
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
	}
});
