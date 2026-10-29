# Plano de Ação — Site institucional em WordPress (tema próprio, conteúdo editável pelo cliente)

> **Documento de handoff.** Foi escrito para que outro agente/modelo (ou pessoa) continue o trabalho sem precisar do histórico da conversa. Leia inteiro antes de mexer em código. Criado em 29/10/2026.
> O estado atual do código (o que já existe, o que foi testado e o que não foi) fica no `README.md` do projeto, que é atualizado a cada entrega. Este plano muda pouco.

---

## 1. Contexto e objetivo

- O usuário (brasileiro, fala português; responda sempre em português) vai entregar um site a um **cliente final**. O cliente precisa **controlar o conteúdo** sem mexer no design.
- O cliente deve poder: cadastrar **fotos**, publicar **posts de blog**, cadastrar o **quadro de sócios** (cada sócio com página própria) e alimentar os **carrosséis e seções da home**.
- O layout é **travado no código** (tema PHP clássico). Não é um site editável por blocos/arrastar-e-soltar.
- Existe uma **referência visual** (print de um site institucional de escritório de advocacia). O usuário ainda **não tem os arquivos do site estático** nem identidade visual definida. O esqueleto é, por isso, **neutro**.
- Layout de referência, 6 seções na home:
  1. **Hero**: banners que trocam com *fade*, com bolinhas (bullets) e botão play/pause.
  2. **Carrossel arrastável** (drag) de cards grandes, com os vizinhos aparecendo nas laterais.
  3. **Depoimentos/reconhecimentos** que se alternam, com bullets.
  4. **"Histórias"**: lista de títulos sobre uma imagem de fundo; setas mudam o item em foco e a imagem de fundo faz crossfade; cada título é clicável e leva a um post/categoria.
  5. **Artigos em destaque**: 3 cards de posts + botão "Ler mais" que leva ao arquivo do blog.
  6. **Rodapé**: menu, redes sociais, lista de escritórios, copyright.

## 2. Decisões já tomadas (e o porquê)

| # | Decisão | Motivo |
|---|---|---|
| D1 | **Tema clássico customizado (PHP)**, layout travado em código. **Não** migrar para blocos/Gutenberg. | Migrar tudo para blocos custa mais e dá ao cliente liberdade para quebrar o design. O editor de blocos fica só no **corpo de posts, páginas e biografia dos sócios**. |
| D2 | **Ir direto para o WordPress**, sem montar antes um site estático completo. | Esse layout é feito de listas de conteúdo; o difícil é modelar o conteúdo, não o HTML. Fazer estático antes é fazer duas vezes. Protótipo estático só se o cliente precisar aprovar o visual (Figma ou uma página única). |
| D3 | Conteúdo = **Custom Post Types + imagem destacada + campos nativos** (meta boxes em código). **Sem ACF.** | Zero dependência e zero plugin pago (repeater/options page do ACF são Pro). Se surgir necessidade de campos repetidores, reavaliar ACF Pro, Meta Box ou Carbon Fields. |
| D4 | **JS vanilla**, sem Swiper, 1 arquivo (`assets/js/main.js`). | Sem dependência para vendorizar, pouco código, controle total. Swiper é alternativa aceitável se a complexidade crescer. |
| D5 | **Rascunho = oculto.** Não existe campo "ativo". | O cliente já entende "publicar/rascunho". Menos campos, menos erro. |
| D6 | **Ordem de exibição = `menu_order`** (campo "Ordem" no box Atributos). | Nativo; pode ganhar arrastar-e-soltar com plugin de ordenação (verificar compatibilidade). |
| D7 | CPTs ficam **no tema** nesta fase. | Simplicidade. **Antes de produção**, avaliar mover para plugin/mu-plugin (se trocar o tema, os tipos de conteúdo somem do painel; os dados não são apagados, mas ficam inacessíveis). |
| D8 | CPTs e metas com **`show_in_rest`** habilitado. | Permite popular e manter o conteúdo via WP-CLI, REST API ou MCP de WordPress. |
| D9 | O print de referência é **só referência estrutural**. | Identidade visual, textos, imagens e marca precisam ser próprios do cliente. Não copiar logo, fotos, textos ou CSS de site de terceiros. |
| D10 | Interpretação da seção "Histórias": a **lista à direita mostra todos os títulos** (o ativo em destaque) e o **painel inferior mostra título e resumo do item ativo**, com setas. | O print não deixa claro se a lista mostra todos ou só os próximos. Fácil de ajustar. |
| D11 | **Ambiente local padronizado = runtime portátil versionado** (`tools/setup-local.ps1`: PHP 8.3.36, MariaDB 11.4.13, WordPress 7.1.4, WP-CLI 2.12.0), Windows x64. LocalWP e `@wordpress/env` não são o caminho oficial. | Todos os colegas, mesmo sem nada instalado, chegam ao mesmo ambiente com um comando — sem GUI, Docker ou admin. LocalWP não é reproduzível (cada um escolhe versões/servidor na interface). |

## 3. Escopo

**Dentro do escopo**
- Tema `tema-base` (neutro, responsivo, acessível no básico).
- 5 tipos de conteúdo: `banner`, `destaque`, `depoimento`, `historia`, `socio`, mais **posts** nativos para o blog.
- Home com as 6 seções acima, cada uma **se esconde sozinha** quando não há conteúdo.
- Blog: página de listagem, categorias, post individual, paginação.
- Sócios: arquivo `/socios/` e página própria de cada sócio.
- Páginas comuns (`page.php`).
- Menus: principal e rodapé.
- Página **"Opções do Site"** (redes sociais, escritórios, link/rótulo de inscrição, títulos de seções, nota do rodapé).
- Experiência do cliente no painel (colunas úteis, dicas de tamanho de imagem, ordem padrão).
- Script de **conteúdo de demonstração** (WP-CLI) e imagens placeholder.
- README, este plano e um zip instalável do tema.

**Fora do escopo (não fazer sem pedido explícito)**
- Multilíngue/tradução (o site de referência tem seletor de idioma; aqui **não**).
- Busca avançada (a busca nativa do WordPress existe, mas sem design dedicado), filtros por ano/área, mega menu.
- Formulário de contato e newsletter **funcionais** (decidir ferramenta depois: Contact Form 7, Fluent Forms, serviço de e-mail marketing). Hoje o botão de inscrição só abre uma URL configurada.
- E-commerce, área de membros/login, comentários moderados, páginas de "áreas de atuação" com filtros.
- Editor visual de seções / page builder / blocos customizados.
- Animações de scroll, dark mode, parallax.
- Identidade visual final (logo, paleta, tipografia) — vem do cliente.
- Migração de conteúdo existente, redirecionamentos 301 de site antigo.
- Hospedagem, DNS, SSL, e-mail, CDN.
- Instalação/configuração de plugins de SEO, cache, backup, segurança (apenas **recomendar**, ver seção 11).
- Banner de cookies/LGPD e analytics (decidir com o cliente).
- Testes automatizados E2E (opcional no backlog).

## 4. Arquitetura do projeto

```
projeto-wp/
├── PLANO-DE-ACAO.md          ← este documento
├── README.md                 ← como rodar, estado atual, testes feitos
├── .local/                   ← runtime portátil gerado (fora do Git)
├── tools/
│   ├── setup-local.ps1       ← instala o ambiente do zero (Windows)
│   ├── dev.ps1               ← start/stop/status do site local
│   ├── wp.ps1                ← atalho do WP-CLI
│   ├── test.ps1              ← roda tests/f1-checks.php no WordPress
│   ├── router.php            ← rewrite dos permalinks no servidor embutido
│   ├── seed-demo.ps1         ← popula um WP local com conteúdo de demonstração (WP-CLI)
│   ├── make-demo-images.py   ← gera as imagens placeholder
│   ├── build-zip.ps1         ← gera dist/tema-base.zip
│   └── demo-images/*.jpg
├── tests/
│   └── f1-checks.php         ← verificações da fundação (CPTs, metas, opções, header/footer)
└── tema-base/                ← o tema (é esta pasta que vira zip e vai para wp-content/themes)
    ├── style.css             ← só o cabeçalho do tema
    ├── functions.php         ← carrega os módulos de inc/
    ├── header.php  footer.php
    ├── front-page.php        ← home (monta as 6 seções com template-parts)
    ├── index.php             ← lista de posts (blog, categorias, busca)
    ├── single.php  page.php  404.php
    ├── archive-socio.php  single-socio.php
    ├── inc/
    │   ├── setup.php         ← supports, menus, tamanhos de imagem, enqueue
    │   ├── post-types.php    ← os 5 CPTs
    │   ├── meta-fields.php   ← meta boxes, salvamento, registro REST
    │   ├── options-page.php  ← "Opções do Site"
    │   ├── helpers.php       ← tb_opt(), tb_query(), tb_featured_posts(), etc.
    │   └── admin-ux.php      ← colunas, ordenação padrão, dicas, aviso de Leitura
    ├── template-parts/
    │   ├── hero.php  carousel-featured.php  testimonials.php  stories.php  insights.php
    │   └── card-post.php  card-socio.php
    └── assets/
        ├── css/main.css      ← tokens em :root + BEM
        └── js/main.js        ← 5 componentes: header, fader (hero e depoimentos), carrossel, histórias
```

**Convenções:** prefixo `tb_` em funções PHP; `tb-` em handles, tamanhos de imagem e classes de utilidade; CSS em BEM (`.hero__slide`); JS ativado por `data-component="..."`; **PHP compatível com 7.4+** (sem `match`, `?->`, argumentos nomeados).

## 5. Modelo de conteúdo

Imagens usam a **imagem destacada** (thumbnail) do próprio WordPress. Título = título do post. Ordem = `menu_order`. Rascunho = oculto.

| CPT (slug) | Nome no painel | Público? | Imagem destacada | Campos (meta key → rótulo) |
|---|---|---|---|---|
| `banner` | Banners do topo | não (sem página própria) | imagem do hero (`tb-hero`, 1920×800) | `_tb_label` rótulo pequeno · `_tb_text` texto curto · `_tb_link` URL · `_tb_link_label` texto do botão (padrão "Leia mais") |
| `destaque` | Destaques | não | imagem do card (`tb-slide`, 1280×640) | `_tb_label` · `_tb_link` · `_tb_link_label` |
| `depoimento` | Depoimentos | não | — | título = manchete · `_tb_quote` texto · `_tb_source` fonte/autor |
| `historia` | Histórias | não | imagem de fundo (`tb-story`, 1600×900) | `_tb_summary` resumo · `_tb_link` URL de destino (post, categoria, página) |
| `socio` | Sócios | **sim**, arquivo em `/socios/` | retrato (`tb-socio`, 640×800) | título = nome · editor = biografia · `_tb_role` cargo · `_tb_email` · `_tb_phone` · `_tb_linkedin` |
| `post` (nativo) | Posts | sim | imagem do card (`tb-card`, 720×480) | categoria + resumo (excerpt). **Posts fixados** viram os "artigos em destaque" da home (se houver menos de 3, completa com os mais recentes). |

**Opções do Site** (opção `tb_options`, array): `insights_heading`, `stories_heading`, `social_linkedin`, `social_instagram`, `social_x`, `social_facebook`, `social_youtube`, `offices` (uma cidade por linha), `subscribe_label`, `subscribe_url`, `footer_note`.

**Menus:** `primary` (menu principal), `footer` (rodapé).

## 6. Especificação das seções da home

1. **Hero** (`template-parts/hero.php`, `data-component="hero"`)
   - Slides empilhados; só o ativo é visível (`opacity`/`visibility`), transição de ~0,8 s. Slides inativos recebem `inert` e `aria-hidden`.
   - Bullets (botões), play/pause (botão), autoplay ~6 s. Pausa ao passar o mouse/foco. **Sem autoplay** se `prefers-reduced-motion: reduce`.
   - A **primeira imagem** usa `loading="eager"` e `fetchpriority="high"` (é o LCP); as demais, `lazy`.
   - Se houver 1 só banner, esconde bullets/play.
2. **Carrossel arrastável** (`carousel-featured.php`, `data-component="carousel"`)
   - `overflow-x: auto` + `scroll-snap` (centralizado). Arrastar com o mouse via pointer events; toque usa o scroll nativo.
   - Setas anterior/próximo. Clique em link é cancelado se houve arrasto.
   - O card inteiro é clicável (link "esticado") quando há `_tb_link`.
3. **Depoimentos** (`testimonials.php`, `data-component="testimonials"`)
   - Itens sobrepostos na mesma célula de grid (altura = maior item), fade, bullets, autoplay ~8 s.
4. **Histórias** (`stories.php`, `data-component="stories"`)
   - Camadas de imagem de fundo empilhadas (crossfade); lista de títulos (link se houver `_tb_link`, senão botão que ativa o item); painel com título/resumo do ativo; setas e teclado (←/→).
5. **Artigos em destaque** (`insights.php`): `tb_featured_posts(3)` + botão para `tb_blog_url()`.
6. **Rodapé** (`footer.php`): menu `footer`, redes (só as preenchidas), escritórios, copyright + `footer_note`.

Toda seção **retorna sem imprimir nada** se não houver itens.

## 7. Experiência do cliente no painel (muito importante)

- Entregar ao cliente um usuário com papel **Editor** (não Administrador). "Opções do Site" já é acessível ao Editor (capacidade `edit_pages`).
- Rótulos em português, ícones diferentes por tipo, dicas de tamanho abaixo do box de imagem destacada.
- Listagens com colunas **Imagem** e **Ordem**; ordem padrão = `menu_order`.
- **Rascunho** esconde um item sem apagar.
- Aviso para administradores se Leitura (Configurações → Leitura) não estiver com página inicial estática + página de posts definidas — sem isso, o botão "Ler mais" e o blog quebram.
- Escrever um **manual curto do cliente** (PDF/Doc, com prints) na fase de entrega.
- Testar **sempre logado como Editor**, nunca só como admin.

## 8. Front-end: CSS/JS

- **Tokens** em `:root` (`--tb-color-*`, `--tb-font-*`, `--tb-container`, `--tb-gutter`). Trocar identidade = trocar tokens + fontes + logo.
- **Fontes:** o esqueleto usa fontes do sistema. Em produção, usar fontes **locais** (self-hosted, `font-display: swap`), nunca depender de CDN sem necessidade (LGPD/performance).
- **Acessibilidade** (referência: padrão de carrossel do W3C APG): botões reais, `aria-label`, `aria-current` nos bullets, `inert` nos slides escondidos, foco visível, pausa no hover/foco, `prefers-reduced-motion`, link "pular para o conteúdo", um único `<h1>` por página.
- **Responsivo:** mobile-first; menu vira botão (hambúrguer) abaixo de 900 px; seção "Histórias" empilha no mobile.
- **JS:** sem build, sem dependências. Cada componente falha em silêncio se o HTML não existir.

## 9. Segurança (checklist obrigatório)

- **Escapar na saída** sempre: `esc_html`, `esc_attr`, `esc_url`, `esc_textarea`. Nunca imprimir meta/option crua.
- **Sanitizar na entrada** (`sanitize_text_field`, `sanitize_textarea_field`, `esc_url_raw`, `sanitize_email`) e **verificar nonce + capacidade** (`current_user_can('edit_post', $id)`) em todo salvamento.
- Ignorar autosave e revisões ao salvar metas.
- Páginas de opções: `register_setting` com `sanitize_callback`.
- Metas com underscore são "protegidas"; por isso o registro REST define `auth_callback` explícito.
- Links externos com `rel="noopener noreferrer"`.
- Não commitar senhas/Application Passwords. Para automação via REST/MCP, usar **Application Password** de um usuário com o mínimo de permissão.

## 10. Desempenho e SEO

- Tamanhos de imagem registrados com `add_image_size()`; usar `sizes` corretos. Ao mudar tamanhos, **regenerar miniaturas** (`wp media regenerate`).
- Não aplicar lazy-load na imagem do LCP (primeiro slide do hero).
- Carregar CSS/JS só do tema; JS com `defer`. Versionar assets por `filemtime`.
- `title-tag` ativado. Para SEO completo (meta description, Open Graph, sitemap), **recomendar** um plugin (Yoast, Rank Math ou The SEO Framework) em vez de reimplementar.
- Cache/otimização de imagens: plugin só em produção, depois de validar o tema.

## 11. Ambiente, comandos e fluxo de trabalho

**Ambiente local (padrão — D11):** Windows x64 + runtime portátil versionado. Na primeira vez, dentro do projeto:

```powershell
powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -Start
```

O script instala PHP 8.3.36, MariaDB 11.4.13, WordPress 7.1.4 e WP-CLI 2.12.0 em `.local/` (fora do Git), cria o banco, instala o WordPress, mapeia `tema-base/` por junction, ativa o tema e configura permalinks/idioma. É idempotente. Detalhes e pré-requisitos no `README.md`. LocalWP e `@wordpress/env` não são o caminho oficial.

```powershell
# dentro do projeto
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 theme activate tema-base
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 rewrite structure '/%postname%/' --hard
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 rewrite flush --hard
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 media regenerate --yes   # se mudar tamanhos de imagem
powershell -ExecutionPolicy Bypass -File tools\test.ps1                        # verificações do tema
```

O seed de demonstração (`tools/seed-demo.ps1`) será criado na F2.

**Fluxo recomendado:** git no projeto → mudar o tema → testar com o conteúdo demo → testar logado como Editor → rodar `tools\test.ps1` → registrar no README o que foi testado.

**Automação por agente:** agentes com acesso a arquivos (ex.: Claude Code) editam o tema. Um **MCP/REST do WordPress** serve para operar conteúdo (criar posts, subir mídia, cadastrar sócios); ele **não constrói o tema**. Verificar o estado atual do MCP oficial/Automattic antes de adotar. Para conferir fidelidade visual, usar screenshots (ex.: Playwright) comparando com a referência aprovada.

**Plugins recomendados em produção (não incluídos):** backup (UpdraftPlus ou similar), segurança (Wordfence ou similar), SEO (um só), cache (um só), formulário de contato, ordenação por arrastar (ex.: Simple Page Ordering — **conferir se funciona com CPT não hierárquico**).

## 12. Fases, checklist e critérios de aceite

| Fase | Entregas | Critério de aceite |
|---|---|---|
| **F0 Preparação** | Ambiente local padronizado, git, ler este plano | Em um clone limpo, `tools\setup-local.ps1` roda sem intervenção; WP local abre, tema aparece em Aparência; login no painel funciona |
| **F1 Fundação** | `setup`, CPTs, metas, opções, admin-ux, header/footer, menus | Painel mostra os 5 tipos; salvar um campo e ver no banco; Editor consegue abrir "Opções do Site" |
| **F2 Home** | 6 seções + CSS/JS | Cada seção funciona com conteúdo demo e **some** sem conteúdo; sem erro no console; teclado e toque funcionam |
| **F3 Blog/Páginas/Sócios** | index, single, page, archive/single de sócio, 404 | Navegar blog → post → voltar; `/socios/` e página de sócio; categoria funciona |
| **F4 QA** | Teste como Editor, a11y, responsivo, desempenho | Testado em 360/768/1280 px; Lighthouse sem erros graves de acessibilidade; nenhum aviso PHP (`WP_DEBUG` ligado) |
| **F5 Identidade** | Tokens, fontes locais, logo, ajustes visuais finos | Visual do cliente aprovado |
| **F6 Entrega** | Hospedagem, permalinks, SSL, backup, manual, treinamento | Cliente publica um post e um sócio sozinho |
| **F7 Backlog** | Busca, contato/newsletter, SEO, LGPD, multilíngue (se pedido) | Priorizar com o usuário |

## 13. Cuidados, armadilhas e precauções

1. **Permalinks/rewrite:** depois de mudar CPTs ou slugs, rodar `wp rewrite flush --hard` (ou Configurações → Links permanentes → Salvar). O tema já faz flush ao ser ativado. Sem isso, `/socios/` dá 404.
2. **Leitura:** definir **página inicial estática** e **página de posts** (Configurações → Leitura). O seed faz isso.
3. **CPT no tema:** trocar de tema faz os tipos sumirem do painel (ver D7).
4. **Imagens do cliente** nunca terão o recorte perfeito: usar `object-fit: cover` e orientar com as dicas de tamanho. Texto sobre imagem precisa de **overlay** para manter contraste.
5. **Texto longo:** layout deve aguentar títulos de 2–4 linhas e resumos longos; use `-webkit-line-clamp` nos cards se precisar.
6. **`wp_reset_postdata()`** depois de cada `WP_Query` customizada.
7. **Não usar `the_content()`** nas seções da home (elas vêm de metas e títulos).
8. **`WP_Query`:** usar `no_found_rows => true` quando não há paginação.
9. **Não reintroduzir Swiper/jQuery** sem motivo; se o fizer, vendorizar localmente.
10. **Não colar o HTML estático do usuário por cima** sem mapear para os `template-parts`: reaproveitar CSS/JS dele só depois de conferir as classes e os `data-component`.
11. **Idioma e charset:** o conteúdo é pt-BR; manter UTF-8 e `lang` correto (`language_attributes()`).
12. **Direitos autorais:** imagens demo são geradas (gradientes). Para o cliente, só imagens licenciadas/próprias.
13. **LGPD:** se usar Google Fonts remotas, analytics ou embeds, avisar o usuário e tratar consentimento.
14. **Atualizações:** combinar com o cliente **quem** atualiza WordPress/plugins e quem faz backup.
15. **Não editar o core, nem plugins de terceiros**; toda customização no tema.
16. **Honestidade sobre testes:** não declarar "testado" o que só foi lido ou checado com lint. Registrar no README exatamente o que foi verificado.

## 14. Perguntas em aberto (confirmar com o usuário)

- Paleta, fontes e logo do cliente? (O esqueleto é neutro.)
- Nome do cliente/site e domínio? Hospedagem escolhida?
- Quantas fotos/galerias o cliente espera cadastrar? A "galeria de fotos" precisa de página própria ou basta nos posts/páginas? (Hoje **não existe CPT de galeria**; se necessário, criar `galeria` com campo de múltiplas imagens.)
- A lista de sócios precisa de **categorias/áreas** (filtros)? 
- Formulário de contato e newsletter: qual ferramenta?
- Precisa de busca com design próprio? Multilíngue?
- O usuário vai fornecer o **site estático existente** (HTML/CSS/JS)? Se sim, mapear as classes dele para os `template-parts` (ver cuidado 10).

## 15. Como retomar o trabalho (prompt sugerido para o próximo agente)

> Leia `PLANO-DE-ACAO.md` e `README.md` por inteiro. Trabalhe só dentro de `tema-base/` e `tools/`. Não mude as decisões D1–D10 sem avisar o usuário. Antes de editar, confirme o estado atual no README. Ao terminar cada fase, rode o checklist da seção 12, registre no README **o que foi realmente testado** e **o que não foi**, e liste as pendências. Responda ao usuário em português.

## 16. Referências (conferir se ainda estão atuais)

- Hierarquia de templates: https://developer.wordpress.org/themes/basics/template-hierarchy/
- `register_post_type()`: https://developer.wordpress.org/reference/functions/register_post_type/
- Meta boxes personalizados: https://developer.wordpress.org/plugins/metadata/custom-meta-boxes/
- `register_post_meta()`: https://developer.wordpress.org/reference/functions/register_post_meta/
- Theme Handbook: https://developer.wordpress.org/themes/
- WP-CLI: https://wp-cli.org/
- Padrão de carrossel acessível (W3C APG): https://www.w3.org/WAI/ARIA/apg/patterns/carousel/
- LocalWP: https://localwp.com/
