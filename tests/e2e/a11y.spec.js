const { test, expect } = require('@playwright/test');

const pages = [
	'/',
	'/artigos/',
	'/areas-de-atuacao/',
	'/escritorios/',
	'/socios/',
	'/contato/',
	'/artigo-01/',
	'/areas-de-atuacao/tributario/',
	'/escritorios/sao-paulo/',
	'/socios/ana-exemplo/',
	'/historias/historia-04/',
	'/sobre/',
];

test('@a11y páginas novas têm estrutura, alt e ids únicos', async ({ page }) => {
	for ( const route of pages ) {
		await page.goto( route );

		await expect(page.locator('html')).toHaveAttribute('lang', /^pt-BR/);
		await expect(page.locator('h1')).toHaveCount(1);
		await expect(page.locator('main')).toHaveCount(1);

		const skippedHeading = await page.evaluate(() => {
			const levels = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6')).map((heading) => Number(heading.tagName[1]));
			let previous = 0;

			for ( const level of levels ) {
				if ( previous && level > previous + 1 ) {
					return { previous, level };
				}

				previous = level;
			}

			return null;
		});
		expect(skippedHeading, route).toBeNull();

		const missingAlt = await page.evaluate(() =>
			Array.from(document.querySelectorAll('img'))
				.filter((img) => !img.hasAttribute('alt'))
				.map((img) => img.getAttribute('src')),
		);
		expect(missingAlt, route).toEqual([]);

		const duplicateIds = await page.evaluate(() => {
			const counts = {};

			document.querySelectorAll('[id]').forEach((element) => {
				counts[element.id] = (counts[element.id] || 0) + 1;
			});

			return Object.entries(counts).filter(([, count]) => count > 1).map(([id]) => id);
		});
		expect(duplicateIds, route).toEqual([]);

		const unlabeled = await page.evaluate(() =>
			Array.from(document.querySelectorAll('input:not([type="hidden"]), textarea, select'))
				.filter((field) => {
					if (field.getAttribute('aria-label') || field.getAttribute('aria-labelledby')) {
						return false;
					}

					if (field.closest('label')) {
						return false;
					}

					return !(field.id && document.querySelector(`label[for="${CSS.escape(field.id)}"]`));
				})
				.map((field) => field.outerHTML.slice(0, 80)),
		);
		expect(unlabeled, route).toEqual([]);
	}
});

test('@a11y skip link aparece no primeiro Tab e leva ao conteúdo', async ({ page }) => {
	await page.goto('/');

	await page.keyboard.press('Tab');

	const skip = page.locator('.skip-link');
	await expect(skip).toBeFocused();
	await expect(skip).toBeVisible();

	const outline = await page.evaluate(() => {
		const style = getComputedStyle(document.activeElement);
		return { style: style.outlineStyle, width: parseFloat(style.outlineWidth) };
	});
	expect(outline.style).not.toBe('none');
	expect(outline.width).toBeGreaterThan(0);

	await page.keyboard.press('Enter');
	await expect(page).toHaveURL(/#conteudo$/);

	const focusedId = await page.evaluate(() => document.activeElement.id);
	expect(focusedId).toBe('conteudo');
});

test('@a11y trilha tem nome acessível e página atual', async ({ page }) => {
	await page.goto('/artigo-01/');

	const breadcrumb = page.locator('nav[aria-label="Trilha de navegação"]');
	await expect(breadcrumb).toBeVisible();
	await expect(breadcrumb.locator('[aria-current="page"]')).toHaveCount(1);
	await expect(breadcrumb.locator('a').first()).toHaveText(/Início/);
});

test('@a11y rodapé e compartilhamento têm controles nomeados', async ({ page }) => {
	await page.goto('/artigo-01/');

	const search = page.locator('.site-footer__search');
	const input = search.locator('input[type="search"]');
	await expect(input).toHaveCount(1);

	const inputName = await input.evaluate((element) => {
		const label = element.id ? document.querySelector(`label[for="${CSS.escape(element.id)}"]`) : null;
		return label ? label.textContent.trim() : element.getAttribute('aria-label');
	});
	expect(inputName).toBe('Buscar no site');

	await expect(search.locator('button[type="submit"]')).toHaveText(/Buscar/);

	const shareLinks = page.locator('.share__link');
	const shareCount = await shareLinks.count();
	expect(shareCount).toBeGreaterThan(4);

	for ( let index = 0; index < shareCount; index++ ) {
		await expect(shareLinks.nth(index)).toHaveAccessibleName(/\S/);
	}

	await expect(page.locator('[data-share-copy]')).toHaveAttribute('aria-label', /Copiar/);

	const smallTargets = await page.evaluate(() =>
		Array.from(document.querySelectorAll('.share__link'))
			.map((element) => element.getBoundingClientRect())
			.filter((rect) => rect.width < 24 || rect.height < 24)
			.map((rect) => `${Math.round(rect.width)}x${Math.round(rect.height)}`),
	);
	expect(smallTargets).toEqual([]);
});

test('@a11y não há rolagem horizontal de 320 a 1280 px', async ({ page }) => {
	const routes = [ '/', '/artigo-01/', '/areas-de-atuacao/tributario/', '/socios/ana-exemplo/', '/sobre/' ];

	for ( const width of [ 320, 360, 768, 1280 ] ) {
		await page.setViewportSize({ width, height: 800 });

		for ( const route of routes ) {
			await page.goto( route );

			const overflow = await page.evaluate(
				() => document.documentElement.scrollWidth - document.documentElement.clientWidth,
			);

			expect(overflow, `${route} em ${width}px`).toBeLessThanOrEqual(1);
		}
	}
});

test('@a11y menu mobile abre, fecha no Escape e devolve o foco', async ({ page }) => {
	await page.setViewportSize({ width: 360, height: 800 });
	await page.goto('/');

	const toggle = page.locator('[data-component="menu-toggle"]');
	await expect(toggle).toBeVisible();
	await expect(toggle).toHaveAttribute('aria-expanded', 'false');

	await toggle.click();
	await expect(toggle).toHaveAttribute('aria-expanded', 'true');

	await page.keyboard.press('Escape');
	await expect(toggle).toHaveAttribute('aria-expanded', 'false');
	await expect(toggle).toBeFocused();
});
