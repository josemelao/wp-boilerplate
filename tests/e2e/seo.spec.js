const { test, expect } = require('@playwright/test');

async function schemaNodes( page ) {
	const blocks = await page.locator('script[type="application/ld+json"]').allTextContents();
	const nodes  = [];

	for ( const block of blocks ) {
		const data = JSON.parse( block );

		if ( Array.isArray( data['@graph'] ) ) {
			nodes.push( ...data['@graph'] );
		} else {
			nodes.push( data );
		}
	}

	return nodes;
}

function nodeTypes( nodes ) {
	return nodes
		.map( ( node ) => node['@type'] )
		.flat()
		.filter( Boolean );
}

test('@seo home tem descrição, Open Graph e dados estruturados', async ({ page }) => {
	await page.goto('/');

	const description = page.locator('meta[name="description"]');
	await expect(description).toHaveCount(1);

	const text = await description.getAttribute('content');
	expect(text.length).toBeGreaterThan(50);
	expect(text.length).toBeLessThanOrEqual(160);

	await expect(page.locator('meta[property="og:title"]')).toHaveCount(1);
	await expect(page.locator('meta[property="og:type"]')).toHaveAttribute('content', 'website');
	await expect(page.locator('meta[property="og:url"]')).toHaveCount(1);
	await expect(page.locator('meta[property="og:image"]')).toHaveCount(1);
	await expect(page.locator('meta[name="twitter:card"]')).toHaveAttribute('content', 'summary_large_image');
	await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
	await expect(page.locator('h1')).toHaveCount(1);

	const types = nodeTypes(await schemaNodes(page));
	expect(types).toContain('Organization');
	expect(types).toContain('WebSite');

	const nodes = await schemaNodes(page);
	const organization = nodes.find(( node ) => Array.isArray(node['@type']) && node['@type'].includes('Organization'));
	expect(Array.isArray(organization.sameAs)).toBe(true);
});

test('@seo artigo tem metadados de artigo, breadcrumb e schema Article', async ({ page }) => {
	await page.goto('/artigo-01/');

	await expect(page.locator('meta[property="og:type"]')).toHaveAttribute('content', 'article');
	await expect(page.locator('meta[property="article:published_time"]')).toHaveCount(1);

	const description = await page.locator('meta[name="description"]').getAttribute('content');
	expect(description).toContain('Uma leitura das mudanças recentes');

	const breadcrumb = page.locator('nav[aria-label="Trilha de navegação"]');
	await expect(breadcrumb).toBeVisible();
	await expect(breadcrumb.locator('a').first()).toHaveText(/Início/);

	const types = nodeTypes(await schemaNodes(page));
	expect(types).toContain('Article');
	expect(types).toContain('BreadcrumbList');
});

test('@seo sócio tem schema Person e tipo de perfil', async ({ page }) => {
	await page.goto('/socios/ana-exemplo/');

	await expect(page.locator('meta[property="og:type"]')).toHaveAttribute('content', 'profile');

	const nodes = await schemaNodes(page);
	const types = nodeTypes(nodes);
	expect(types).toContain('Person');

	const person = nodes.find(( node ) => node['@type'] === 'Person');
	expect(person.name).toBe('Ana Exemplo');
	expect(person.knowsAbout).toContain('Contencioso e Arbitragem');
});

test('@seo área tem schema Service e trilha visível', async ({ page }) => {
	await page.goto('/areas-de-atuacao/tributario/');

	await expect(page.locator('nav[aria-label="Trilha de navegação"]')).toBeVisible();

	const types = nodeTypes(await schemaNodes(page));
	expect(types).toContain('Service');
	expect(types).toContain('BreadcrumbList');
});

test('@seo página institucional usa WebPage e tipo website', async ({ page }) => {
	await page.goto('/sobre/');

	await expect(page.locator('meta[property="og:type"]')).toHaveAttribute('content', 'website');

	const types = nodeTypes(await schemaNodes(page));
	expect(types).toContain('WebPage');
});

test('@seo busca e 404 ficam fora do índice', async ({ page }) => {
	await page.goto('/?s=teste');
	await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);

	const response = await page.goto('/nao-existe-xyz/');
	expect(response.status()).toBe(404);
	await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
});
