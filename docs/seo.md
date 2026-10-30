# SEO — decisão e configuração

Atualizado em 30/10/2026.

## Estratégia adotada (F6.2)

O tema entrega o SEO essencial e se cala quando um plugin reconhecido assume o
trabalho. Isso permitiu melhorar o protótipo sem criar dependência e mantém o
caminho aberto para o plugin recomendado em produção.

- **Com plugin ativo**, o tema não imprime descrição, Open Graph, Twitter Card,
  JSON-LD nem trilha. O plugin é a fonte única.
- **Sem plugin**, o tema imprime esses itens a partir do conteúdo do WordPress.
- Plugins detectados: Yoast, Rank Math, The SEO Framework, SEOPress, All in One
  SEO e Slim SEO. O filtro `tb_seo_plugin_active` permite forçar o desligamento.

## O que o tema gera hoje

| Item | Origem |
|---|---|
| `<title>` | `title-tag` do WordPress |
| `meta description` | campo **SEO** do conteúdo → resumo → início do texto → texto padrão |
| Home | “Opções do Site → SEO → Descrição da página inicial”; vazio usa o slogan |
| Canonical | core nas páginas individuais; tema em home, blog, arquivos e taxonomias |
| Open Graph | `og:title`, `og:description`, `og:url`, `og:type`, `og:image`, `og:locale`, `article:*` |
| Twitter Card | `summary_large_image` com imagem; `summary` sem imagem |
| Schema | `Organization` + `LegalService`, `WebSite`, `WebPage`/`CollectionPage`, `Article`, `Person`, `Service`, `LocalBusiness`, `BreadcrumbList` |
| Trilha visível | posts, páginas, histórias, sócios, áreas e escritórios |
| `noindex` | busca e 404 (busca já vem do core; 404 é adicionado pelo tema) |
| Sitemap / robots | `wp-sitemap.xml` e `robots.txt` do core |

### Imagem social

Ordem de fallback: imagem destacada → ícone do site → logo → primeiro banner.
O tamanho usado é `tb-slide` (1280 × 640), próximo do 1,91:1 recomendado para
Open Graph.

### Onde editar

- **Descrição por conteúdo:** lateral do editor, caixa **SEO**. Máximo de 160
  caracteres; em branco usa o resumo ou o texto.
- **Descrição da home:** Opções do Site → SEO.
- **Imagem social:** imagem destacada do conteúdo. Na home, o primeiro banner.

## Plugin recomendado para produção

**The SEO Framework** é a primeira escolha: leve, sem anúncios ou upsell, schema,
sitemap, Open Graph, trilha e importação de dados. Alternativa: **Rank Math**
(gratuito, inclui redirecionamentos e análise, porém mais pesado e com upsell).

Instalar **um único plugin**. Não usar dois: as tags duplicam e o tema se desliga
por inteiro, deixando o plugin no comando.

### Checklist de configuração

1. Instalar e ativar **apenas um** plugin de SEO.
2. Conferir nome do site, slogan e logo em Configurações → Geral.
3. Revisar títulos e descrições dos modelos: home, artigos, páginas, áreas,
   escritórios, sócios e histórias.
4. Definir a imagem social padrão (1200 × 630) e preencher os perfis sociais.
5. Ativar schema de organização (`Organization`/`LegalService`) e trilha.
6. Manter o sitemap do plugin **ou** o do core (`/wp-sitemap.xml`), nunca os dois
   competindo pelo mesmo caminho.
7. Confirmar `blog_public` ligado em produção e busca/404 fora do índice.
8. Configurar os redirecionamentos 301 do site antigo no plugin.
9. Enviar o sitemap ao Google Search Console e ao Bing Webmaster Tools.
10. Rodar `tools\seo-check.ps1 -BaseUrl https://dominio` para conferir o head.

## Verificação

Com o site local no ar:

```powershell
powershell -ExecutionPolicy Bypass -File tools\seo-check.ps1
```

O script confere, em home, artigos, sócios, áreas e escritórios: status, título,
um único `h1`, descrição de 50–160 caracteres, canonical, Open Graph, Twitter
Card e JSON-LD válido. Também valida `noindex` em busca e 404, o sitemap e a
linha `Sitemap:` no `robots.txt`. Sai com código 1 em qualquer falha.

Os testes E2E em `tests/e2e/seo.spec.js` cobrem os mesmos pontos no navegador.

## Limites conhecidos

- O tema não faz redirecionamentos 301, análise de tráfego nem painel editorial
  de SEO; esses itens ficam com o plugin de produção.
- Não há Open Graph para o autor do post; a autoria aponta para a organização.
- A home usa o nome do site como título. Quando a identidade final chegar,
  revisar o título e a descrição em Opções do Site → SEO.
- `wp-sitemap.xml` do core cobre posts, páginas e CPTs públicos. Se o plugin
  assumir, desativar o sitemap do core para evitar duplicidade.
