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
 * Verifica se entrada e saída usam o mesmo fade, em sentidos opostos.
 *
 * @param {Array<Object>} samples Amostras pós-clique.
 */
function expectSymmetricFade(samples) {
	expect(samples.length).toBeGreaterThan(3);

	const first = samples[0];
	const last = samples[samples.length - 1];
	const middle = samples.reduce((closest, sample) => {
		const score =
			Math.abs(sample.incomingOpacity - 0.5) +
			Math.abs(sample.outgoingOpacity - 0.5);

		if (!closest || score < closest.score) {
			return { sample, score };
		}

		return closest;
	}, null).sample;

	expect(isRed(first.incomingColor), `título que entra não deveria começar vermelho: ${first.incomingColor}`).toBeFalsy();
	expect(first.incomingOpacity).toBeLessThan(0.95);
	expect(isNearWhite(first.outgoingColor), `título que sai não deveria começar branco: ${first.outgoingColor}`).toBeFalsy();
	expect(first.outgoingOpacity).toBeGreaterThan(0.05);

	expect(isRed(middle.incomingColor), `título entrando deveria estar em transição: ${middle.incomingColor}`).toBeFalsy();
	expect(isNearWhite(middle.incomingColor), `título entrando não deveria parecer branco: ${middle.incomingColor}`).toBeFalsy();
	expect(isRed(middle.outgoingColor), `título saindo deveria estar em transição: ${middle.outgoingColor}`).toBeFalsy();
	expect(isNearWhite(middle.outgoingColor), `título saindo não deveria parecer branco: ${middle.outgoingColor}`).toBeFalsy();

	expect(isRed(last.incomingColor), `título que entrou deveria terminar vermelho: ${last.incomingColor}`).toBeTruthy();
	expect(last.incomingOpacity).toBeGreaterThan(0.95);
	expect(isNearWhite(last.outgoingColor), `título que saiu deveria terminar branco: ${last.outgoingColor}`).toBeTruthy();
	expect(last.outgoingOpacity).toBeLessThan(0.05);
}

test('@stories seta para baixo faz fade simétrico de entrada e saída', async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('[data-component="stories"]')).toBeVisible();

	await expect(page.locator('.stories__entry')).toHaveCount(4);
	await expect(page.locator('.stories__entry.is-visible')).toHaveCount(4);
	await expect(page.locator('.stories__entry.is-active')).toHaveCount(1);

	const { samples, outgoingId } = await collectRotationSamples(page, 'down');

	expectSymmetricFade(samples);

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

	expectSymmetricFade(samples);
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

test('@stories mobile separa o título e usa a lista larga', async ({ page }) => {
	for (const width of [320, 390]) {
		await page.setViewportSize({ width, height: 844 });
		await page.goto('/');
		await page.locator('.stories').scrollIntoViewIfNeeded();
		await page.waitForTimeout(500);

		const heading = await page.locator('.stories__heading').boundingBox();
		const firstTitle = await page
			.locator('.stories__entry.is-visible:not(.is-active) .stories__title:not([hidden])')
			.first()
			.boundingBox();
		const activeTitle = await page
			.locator('.stories__entry.is-active .stories__title:not([hidden])')
			.boundingBox();
		const active = await page.locator('.stories__entry.is-active').boundingBox();
		const summary = await page.locator('.stories__entry.is-active .stories__summary').boundingBox();
		const controls = await page.locator('.stories__controls').boundingBox();

		expect(heading, 'título da seção visível').not.toBeNull();
		expect(firstTitle, 'primeira história visível').not.toBeNull();
		expect(activeTitle, 'título ativo visível').not.toBeNull();
		expect(active, 'card ativo visível').not.toBeNull();
		expect(summary, 'resumo ativo visível').not.toBeNull();
		expect(controls, 'controles visíveis').not.toBeNull();

		expect(heading.x + heading.width + 8).toBeLessThanOrEqual(firstTitle.x);
		expect(Math.abs(activeTitle.x - firstTitle.x)).toBeLessThanOrEqual(1);
		expect(active.width).toBeGreaterThanOrEqual(width * 0.75);
		expect(firstTitle.width).toBeGreaterThanOrEqual(width * 0.7);

		const clamp = await page
			.locator('.stories__entry.is-active .stories__summary')
			.evaluate((element) => getComputedStyle(element).webkitLineClamp);
		expect(clamp).toBe('4');

		expect(controls.y).toBeGreaterThanOrEqual(summary.y + summary.height);
		expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
	}
});

test('@stories mobile desliza a lista e mantém o alinhamento final', async ({ page }) => {
	await page.setViewportSize({ width: 390, height: 844 });
	await page.goto('/');
	await page.locator('.stories').scrollIntoViewIfNeeded();
	await page.waitForTimeout(500);

	const list = await page.locator('.stories__list').boundingBox();
	const after = await page.locator('.stories__list').evaluate((element) => {
		const style = getComputedStyle(element, '::after');
		return {
			top: parseFloat(style.top),
			left: parseFloat(style.left),
			width: parseFloat(style.width),
			height: parseFloat(style.height),
		};
	});
	const surface = {
		top: list.y + after.top,
		left: list.x + after.left,
		width: after.width,
		height: after.height,
	};

	const active = page.locator('.stories__entry.is-active');
	const activeBox = await active.boundingBox();
	const activeTitle = await active.locator('.stories__title:not([hidden])').boundingBox();
	const firstEntry = page.locator('.stories__entry.is-visible:not(.is-active)').first();
	const firstId = await firstEntry.getAttribute('data-story-id');
	const movingLabel = page.locator(`.stories__entry[data-story-id="${firstId}"] .stories__title:not([hidden])`);
	const firstLabel = await movingLabel.boundingBox();

	expect(Math.abs(activeBox.y - surface.top)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeBox.height - surface.height)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeBox.x - surface.left)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeBox.width - surface.width)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeTitle.x - firstLabel.x)).toBeLessThanOrEqual(1);

	const beforeLabelY = firstLabel.y;
	await page.locator('[data-story-down]').click();
	await page.waitForTimeout(80);

	const duringLabelY = (await movingLabel.boundingBox()).y;
	expect(Math.abs(duringLabelY - beforeLabelY)).toBeGreaterThan(5);

	const duringActive = await page.locator('.stories__entry.is-active').boundingBox();
	const duringTitle = await page
		.locator('.stories__entry.is-active .stories__title:not([hidden])')
		.boundingBox();
	const duringSummary = await page.locator('.stories__entry.is-active .stories__summary').boundingBox();

	expect(Math.abs(duringActive.y - surface.top)).toBeLessThanOrEqual(1);
	expect(Math.abs(duringActive.height - surface.height)).toBeLessThanOrEqual(1);
	expect(Math.abs(duringActive.x - surface.left)).toBeLessThanOrEqual(1);
	expect(Math.abs(duringActive.width - surface.width)).toBeLessThanOrEqual(1);
	expect(duringTitle.y).toBeGreaterThanOrEqual(surface.top);
	expect(duringTitle.y + duringTitle.height).toBeLessThanOrEqual(surface.top + surface.height);
	expect(duringSummary.y).toBeGreaterThanOrEqual(surface.top);
	expect(duringSummary.y + duringSummary.height).toBeLessThanOrEqual(surface.top + surface.height);

	await page.waitForTimeout(700);

	const activeAfter = await page.locator('.stories__entry.is-active').boundingBox();
	const activeTitleAfter = await page
		.locator('.stories__entry.is-active .stories__title:not([hidden])')
		.boundingBox();
	const firstAfter = await page
		.locator('.stories__entry.is-visible:not(.is-active) .stories__title:not([hidden])')
		.first()
		.boundingBox();

	expect(Math.abs(activeAfter.y - surface.top)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeAfter.height - surface.height)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeAfter.x - surface.left)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeAfter.width - surface.width)).toBeLessThanOrEqual(1);
	expect(Math.abs(activeTitleAfter.x - firstAfter.x)).toBeLessThanOrEqual(1);
	expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(390);
});
