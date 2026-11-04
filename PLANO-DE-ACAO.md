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
| D1 | **Tema clássico customizado (PHP)**, layout travado em código. **Não** migrar para blocos/Gutenberg. | Migrar tudo para blocos custa mais e dá ao cliente liberdade para quebrar o design. O editor de blocos fica só no **corpo de posts, páginas, histórias e biografias dos sócios**. |
| D2 | **Ir direto para o WordPress**, sem montar antes um site estático completo. | Esse layout é feito de listas de conteúdo; o difícil é modelar o conteúdo, não o HTML. Fazer estático antes é fazer duas vezes. Protótipo estático só se o cliente precisar aprovar o visual (Figma ou uma página única). |
| D3 | Conteúdo = **Custom Post Types + imagem destacada + campos nativos** (meta boxes em código). **Sem ACF.** | Zero dependência e zero plugin pago (repeater/options page do ACF são Pro). Se surgir necessidade de campos repetidores, reavaliar ACF Pro, Meta Box ou Carbon Fields. |
| D4 | **JS vanilla**, sem Swiper, 1 arquivo (`assets/js/main.js`). | Sem dependência para vendorizar, pouco código, controle total. Swiper é alternativa aceitável se a complexidade crescer. |
| D5 | **Rascunho = oculto.** Não existe campo "ativo". | O cliente já entende "publicar/rascunho". Menos campos, menos erro. |
| D6 | **Ordem de exibição = `menu_order`** (campo "Ordem" no box Atributos). | Nativo; pode ganhar arrastar-e-soltar com plugin de ordenação (verificar compatibilidade). |
| D7 | CPTs ficam **no tema** nesta fase. | Simplicidade. **Antes de produção**, avaliar mover para plugin/mu-plugin (se trocar o tema, os tipos de conteúdo somem do painel; os dados não são apagados, mas ficam inacessíveis). |
| D8 | CPTs e metas com **`show_in_rest`** habilitado. | Permite popular e manter o conteúdo via WP-CLI, REST API ou MCP de WordPress. |
| D9 | O print de referência é **só referência estrutural**. | Identidade visual, textos, imagens e marca precisam ser próprios do cliente. Não copiar logo, fotos, textos ou CSS de site de terceiros. |
| D10 | "Histórias" usa uma única lista circular: com 4 ou mais posts, mostra 3 títulos comuns (22% da altura cada) e o título ativo (34%); o ativo não é repetido em um painel separado. A superfície clara e as setas ficam fixas; o título e o resumo (primeiro parágrafo, com fallback para `_tb_summary`) mudam com a rotação. As setas/teclas ↑↓ percorrem os itens em loop, sem entradas instantâneas. O título ativo abre `/historias/{slug}/`; os outros selecionam. | Detalhes refinados pelo usuário a partir de screenshots e validação manual em 29/10/2026. |
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
- Testes E2E completos/CI — suíte básica implementada em 30/10/2026; ampliar cobertura e configurar CI continuam no backlog.

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
│   ├── seed-demo.ps1         ← gera imagens e popula um WP local com conteúdo de demonstração
│   ├── seed-demo.php         ← cria/atualiza o conteúdo via WP-CLI
│   ├── make-demo-images.php  ← gera imagens JPEG placeholder via GD
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
    ├── single.php  single-historia.php  page.php  404.php
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
        └── js/main.js        ← componentes: menu, faders, carrossel e histórias
```

**Convenções:** prefixo `tb_` em funções PHP; `tb-` em handles, tamanhos de imagem e classes de utilidade; CSS em BEM (`.hero__slide`); JS ativado por `data-component="..."`; **PHP compatível com 7.4+** (sem `match`, `?->`, argumentos nomeados).

## 5. Modelo de conteúdo

Imagens usam a **imagem destacada** (thumbnail) do próprio WordPress. Título = título do post. Ordem = `menu_order`. Rascunho = oculto.

| CPT (slug) | Nome no painel | Público? | Imagem destacada | Campos (meta key → rótulo) |
|---|---|---|---|---|
| `banner` | Banners do topo | não (sem página própria) | imagem do hero (`tb-hero`, 1920×800) | `_tb_label` rótulo pequeno · `_tb_text` texto curto · `_tb_link` URL · `_tb_link_label` texto do botão (padrão "Leia mais") |
| `destaque` | Destaques | não | imagem do card (`tb-slide`, 1280×640) | `_tb_label` · `_tb_link` · `_tb_link_label` |
| `depoimento` | Depoimentos | não | — | título = manchete · `_tb_quote` texto · `_tb_source` fonte/autor |
| `historia` | Histórias | sim, página em `/historias/{slug}/` | imagem de fundo (`tb-story`, 1600×900) | editor = corpo da história · `_tb_summary` resumo · `_tb_link` link relacionado opcional |
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
   - `overflow-x: auto` + `scroll-snap` (centralizado). Arrastar com o mouse por eventos de mouse; toque usa o scroll nativo.
   - Setas anterior/próximo. Clique em link é cancelado se houve arrasto.
   - O card inteiro é clicável (link "esticado") quando há `_tb_link`.
3. **Depoimentos** (`testimonials.php`, `data-component="testimonials"`)
   - Itens sobrepostos na mesma célula de grid (altura = maior item), fade, bullets, autoplay ~8 s.
4. **Histórias** (`stories.php`, `data-component="stories"`)
   - Camadas de imagem de fundo empilhadas (crossfade); lista vertical circular com o ativo sempre no fim; setas e teclado (↑/↓) giram a lista e atualizam o fundo/painel; somente o título ativo é link para sua própria página `single-historia.php`.
5. **Artigos em destaque** (`insights.php`): `tb_featured_posts(3)` + botão para `tb_blog_url()`.
6. **Rodapé** (`footer.php`): menu `footer`, redes (só as preenchidas), escritórios, copyright + `footer_note`.

Toda seção **retorna sem imprimir nada** se não houver itens.

## 7. Experiência do cliente no painel (muito importante)

- Entregar ao cliente um usuário com papel **Editor** (não Administrador). "Opções do Site" já é acessível ao Editor (capacidade `edit_pages`).
- Rótulos em português, ícones diferentes por tipo, dicas de tamanho abaixo do box de imagem destacada.
- Listagens com colunas **Imagem** e **Ordem**; ordem padrão = `menu_order`.
- Listagens mostram um aviso curto com a regra de ordem e o tamanho de imagem recomendado.
- Campos agrupados quando há muitos: o sócio tem boxes **Perfil profissional** e **Contato**.
- Textareas usam a fonte normal do painel (sem `code`) e o box de autor fica oculto em posts/páginas.
- A área de **Caixas de metadados** abre por padrão para quem edita, respeitando a preferência do usuário depois disso.
- O widget de comentários não aparece no painel e os links internos dos menus usam caminhos relativos.
- **Manual do cliente:** submenu em Opções do Site, com seis tarefas, prints anotados e o caminho real de “Fixo” para destacar na home.
- Campos de texto curto têm `maxlength` e mostram o limite abaixo do campo; o **Resumo** recomenda até 240 caracteres.
- Na home de Histórias, o texto vem do **Resumo**; o primeiro parágrafo do editor é apenas fallback.
- Hero, títulos de cards e resumos usam `line-clamp` como rede de segurança visual; leads de single ficam em 4 linhas.
- Cards sem imagem usam o **placeholder institucional** (monograma JT, `aria-hidden`), e o hero tem fundo de fallback.
- **Artigos em destaque:** fixados primeiro, por data decrescente; se faltar, completa com os posts mais recentes.
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
- `title-tag` ativado. A F6.2 adotou o modo **híbrido** aprovado pelo usuário: o tema entrega descrição, Open Graph, Twitter Card, schema e trilha; ao detectar um plugin de SEO ativo, o tema se desliga. Em produção o plugin segue **recomendado** (The SEO Framework; alternativa Rank Math) para redirects, análise e painel editorial. Detalhes e checklist em `docs/seo.md`.
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

O seed de demonstração (`tools/seed-demo.ps1`) popula um WordPress local com conteúdo de exemplo para a F2.

**Fluxo recomendado:** git no projeto → mudar o tema → testar com o conteúdo demo → testar logado como Editor → rodar `tools\test.ps1` → registrar no README o que foi testado.

**Automação por agente:** agentes com acesso a arquivos (ex.: Claude Code) editam o tema. Um **MCP/REST do WordPress** serve para operar conteúdo (criar posts, subir mídia, cadastrar sócios); ele **não constrói o tema**. Verificar o estado atual do MCP oficial/Automattic antes de adotar. Para conferir fidelidade visual, usar screenshots (ex.: Playwright) comparando com a referência aprovada.

**Plugins recomendados em produção (não incluídos):** backup (UpdraftPlus ou similar), segurança (Wordfence ou similar), SEO (um só), cache (um só), formulário de contato, ordenação por arrastar (ex.: Simple Page Ordering — **conferir se funciona com CPT não hierárquico**).

## 12. Fases, checklist e critérios de aceite

| Fase | Entregas | Critério de aceite |
|---|---|---|
| **F0 Preparação** | Ambiente local padronizado, git, ler este plano | Em um clone limpo, `tools\setup-local.ps1` roda sem intervenção; WP local abre, tema aparece em Aparência; login no painel funciona |
| **F1 Fundação** | `setup`, CPTs, metas, opções, admin-ux, header/footer, menus | Painel mostra os 5 tipos; salvar um campo e ver no banco; Editor consegue abrir "Opções do Site" |
| **F2 Home** | 6 seções + CSS/JS + single de história + seed demo | Cada seção funciona com conteúdo demo e **some** sem conteúdo; histórias têm páginas próprias; sem erro no console; teclado e toque funcionam |
| **F3 Blog/Páginas/Sócios** | index, single, page, archive/single de sócio, 404 | Navegar blog → post → voltar; `/socios/` e página de sócio; categoria funciona |
| **F4 QA** | Teste como Editor, a11y, responsivo, desempenho | Testado em 360/768/1280 px; Lighthouse sem erros graves de acessibilidade; nenhum aviso PHP (`WP_DEBUG` ligado) |
| **F5 Identidade** | Tokens, fontes locais, logo, ajustes visuais finos | Visual do cliente aprovado |
| **F6 Entrega** | Hospedagem, permalinks, SSL, backup, manual, treinamento | Cliente publica um post e um sócio sozinho |
| **F7 Backlog** | Busca, contato/newsletter, SEO, LGPD, multilíngue (se pedido) | Priorizar com o usuário |

### Registro de execução e handoff — 29/10/2026

**Checkout:** branch `codex/trabalho`, publicada em `origin/codex/trabalho`. O último push anterior a este registro continha o commit `4de2f5f` (`Improve stories carousel animation and controls`); este handoff e os templates F3 estão sendo consolidados na mesma branch.

#### Solicitações do usuário e decisões incorporadas

- No início, o usuário percebeu que o conteúdo de outro computador não aparecia e que o site mostrava apenas “Hello world”. Foi esclarecido e registrado que Git sincroniza tema/código, mas o banco e uploads locais ficam em `.local/` (ignorado pelo Git); o seed repõe conteúdo demonstrativo, não conteúdo editorial de outra máquina. O usuário pediu buscar a versão remota mais recente, criou/acessou a branch de trabalho e solicitou iniciar o servidor local.
- O usuário acessou o painel e perguntou sobre plugins e tipos de conteúdo. Os CPTs/metas editáveis fazem parte da F1; Editor sem acesso a plugins, temas e usuários é uma restrição intencional do papel. As credenciais do WordPress local são específicas de cada ambiente e ficam em `.local/CREDENCIAIS.txt`, não neste documento.
- O usuário forneceu capturas da página de referência e pediu fidelidade de layout, com foco especial na seção **Histórias**, preservando a funcionalidade e o conteúdo editável no WordPress.
- Nos primeiros ajustes de Histórias, o usuário apontou título renderizado como parágrafo, desalinhamento e espaçamento irregular. Criou a quinta história (“Teste”) e pediu que a lista permanecesse regular com 3, 4 ou 5 itens.
- Ao ver o item ativo duplicado, especificou uma única lista de itens; o ativo pertence à mesma lista, recebe outro layout e mostra o primeiro parágrafo como chamada. Com quatro ou mais histórias, a seção deve mostrar quatro slots: três itens de 22% e o ativo de 34%, sem margens/paddings da lista em relação à seção. Com menos histórias, os slots disponíveis são centralizados.
- O usuário reportou setas visíveis em títulos inativos e texto passando por cima dos controles. Os controles agora são um grupo compartilhado, fora das linhas que giram, dentro da área fixa do cartão; texto reserva largura para os controles.
- Pediu que o cartão ativo ficasse parado enquanto os títulos animam. A superfície do cartão é desenhada fixa na lista; as entradas da lista (os posts) fazem a transição para dentro e para fora dela.
- Especificou movimento circular contínuo: nenhum item deve aparecer ou sumir no meio da seção. A seta para baixo foi validada primeiro; depois o usuário apontou que a entrada da seta para cima descia. O item que entra por baixo agora é posicionado fora da seção sem transição e, então, anima para cima. Para listas de até quatro itens, um clone visual temporário, inerte e oculto para tecnologia assistiva, fecha o loop; é removido ao fim da transição e não cria um segundo post/painel persistente.
- Nos carrosséis, o usuário relatou que o arrasto do mouse exigia cliques extras e abria links ao soltar. Pediu drag real e depois refinou a sensação: snap suave, momentum leve após soltar, inclusive antes da metade do card. O CSS/JS usa arrasto com mouse, evita ativar o link se houve drag, desativa o snap enquanto arrasta e aplica settling/momentum ao soltar.
- Pediu rotação automática para Hero e Depoimentos, sem depender de clique; os faders têm autoplay, controles manuais e pausa/resume por foco/interação.

#### Implementação registrada

- `tema-base/template-parts/stories.php`: itens únicos por post, resumo do primeiro parágrafo com fallback `_tb_summary`, um conjunto compartilhado de setas.
- `tema-base/assets/js/main.js`: rotação circular com os itens visíveis movendo-se antes da reorganização final, sincronização de título/resumo/fundo, seleção de títulos, controles de teclado e animação de entrada/saída nas duas direções.
- `tema-base/assets/css/main.css`: quatro slots proporcionais, cartão fixo, controles fixos, áreas de texto reservadas e responsividade; ajustes anteriores de drag, momentum e faders permanecem.
- `tools/dev.ps1`: inicialização dos serviços com `Start-Process` oculto e redirecionamento de logs. `tools/router.php`: normaliza `/wp-admin` para `/wp-admin/`, corrigindo links relativos que levavam ao tema em vez do painel quando a barra final era omitida. O papel Editor segue sem acesso a plugins/temas/usuários, conforme o modelo de entrega do plano.
- F3 implementada: `index.php` (blog/busca/categorias), `single.php` (artigo), `page.php`, `archive-socio.php`, `single-socio.php` e `404.php`; CSS para cards, artigo, conteúdo de página, arquivo/perfil de sócios e paginação. Os dados continuam gerenciáveis pelo WordPress e os contatos opcionais são escapados/sanitizados na saída.

#### Verificações e validação

- `tools/test.ps1`: **153 verificações da F1 passaram**.
- `php -l` em todos os PHP do tema: sem erros de sintaxe.
- Rotas locais: `/artigos/`, `/artigo-01/`, `/sample-page/`, `/socios/` e `/category/perspectivas/` retornaram 200; rota inexistente retornou 404.
- Um perfil de sócio temporário validou a single, cargo, biografia, e-mail, telefone e LinkedIn; o post temporário foi removido. Não há sócios publicados no banco local após esse teste.
- O usuário informou **“validado”** após o passo a passo manual que cobria autoplay do Hero/Depoimentos, drag do carrossel, setas de Depoimentos, rotação de Histórias nos dois sentidos, painel/setas fixos, títulos e single de História, cards/artigos, rotas do blog, conteúdo de sócio, 404, larguras de 360/768/1280 px e Console do navegador.

#### Pendências para retomar

- Identidade visual (F5), hospedagem/entrega (F6) e itens de backlog (F7) seguem dependendo dos dados e decisões do cliente.

#### F4 — QA concluída — 29/10/2026

- Capacidades WordPress do papel Editor verificadas por WP-CLI: pode editar posts/páginas e posts de outros autores; não pode gerenciar opções, plugins ou temas. O usuário confirmou o login real e a validação do acesso como Editor.
- Renderizadas diretamente com `items=[]` as partes `hero`, `carousel-featured`, `testimonials`, `stories` e `insights`; nenhuma imprimiu conteúdo. O usuário confirmou também a home completa sem seções com conteúdo.
- Renderizada a lista de Histórias com 3, 4 e 5 itens: cada caso tem uma entrada ativa e um único grupo de controles. O usuário confirmou visualmente as configurações com 3 e 4 histórias, além da revisão anterior da animação com 5.
- `WP_DEBUG`/`WP_DEBUG_LOG` foram ligados temporariamente enquanto se requisitaram a home, blog, post, página, arquivo de sócios, categoria, single de História e rota 404. Respostas: 200 nas rotas válidas e 404 na rota inexistente; nenhum `debug.log` foi criado. A configuração original (`WP_DEBUG=false`) foi restaurada. O `php.err.log` contém pedidos HTTP; não foram encontradas mensagens de warning/notice/fatal PHP.
- Checagem automática: 23 arquivos PHP sem erros de sintaxe; `node --check` sem erro; 153 verificações da F1 passaram; sete rotas verificadas expõem um `<h1>` no HTML inicial.
- Correções de semântica/acessibilidade introduzidas: Hero alterna um único `<h1>` ativo e títulos `<h2>` nos slides inativos; autoplay não inicia se `prefers-reduced-motion: reduce` estiver ativo; faders pausam em foco/hover e retomam ao sair; controles de bullets estão em grupos nomeados; história selecionada anuncia `aria-current="true"`.
- O usuário confirmou como validados: acesso/permissões do Editor; controles por teclado/foco; toque no carrossel; Histórias com 3/4/5 itens; home completa com seções vazias; e os tamanhos 360/768/1280 px, Console e interações F2/F3. A rotação para cima/baixo das Histórias foi validada após corrigir a direção da entrada.
- Lighthouse executado na home local, com perfil mobile padrão e desktop: desempenho/acessibilidade/boas práticas — **100/100/100** nos dois perfis; SEO — **92**. Métricas mobile: FCP 1,2 s; LCP 1,5 s; Speed Index 1,2 s; TBT 0 ms; CLS 0. Medições sintéticas locais, sujeitas a variação.
- Os primeiros resultados apontaram nomes acessíveis divergentes nos links dos cards e área insuficiente nos bullets/setas. O `aria-label` redundante foi removido; bullets e controles de Histórias passaram a ter alvo de pelo menos 24 × 24 px. A medição final ficou com 100 em acessibilidade e sem auditorias de acessibilidade reprovadas.
- SEO 92 decorre da falta de meta description na home local; configurar SEO/Open Graph fica para a fase de entrega/backlog e plugin definido pelo cliente. Relatórios HTML/JSON estão em `.local/lighthouse/` (pasta ignorada pelo Git).

#### Ajustes de carrossel e Histórias — 30/10/2026

- **Destaques:** o drag passou a ser aplicado em `requestAnimationFrame`; o settle usa uma mola criticamente amortecida (`SPRING_OMEGA=15`) com a velocidade real da soltura, sem `scrollTo({ behavior: 'smooth' })`. O destino é escolhido por velocidade: flick rápido avança no máximo um card; arrasto lento usa o card mais próximo; se o gesto já passou de um card, acompanha o alcançado. A velocidade decai se o usuário segura antes de soltar. `prefers-reduced-motion` vai direto ao card e resize recentraliza.
- A lógica de destino foi extraída para `tema-base/assets/js/carousel-target.js`, com teste unitário.
- **Histórias:** fade simétrico de 320 ms para título e subtexto; entrada branco → vermelho, saída vermelho → branco, com a mesma curva. O subtexto passou de `hidden` para opacidade e o `aria-hidden` é sincronizado no JS.
- Itens inativos deixaram de ser botões e não aceitam clique; a navegação entre histórias é feita somente pelas setas (e pelas teclas ↑/↓). O título ativo continua sendo link para a single.
- As setas usam SVG para alinhamento consistente: 52 × 40 px no desktop e 44 × 36 px no mobile. No mobile, os controles foram posicionados na linha inferior do cartão para preservar o espaço do texto.
- **Testes adicionados:** `tests/unit/carousel-target.test.js` (12), `tests/e2e/featured.spec.js` (8), `tests/e2e/stories.spec.js` (4), `tests/e2e/smoke.spec.js` (2), `tests/e2e/testimonials.spec.js` (1), `tests/e2e/inner-pages.spec.js` (3) e `tests/e2e/search.spec.js` (2). Total E2E: **20**.
- **Verificação em 30/10/2026:** `npm run test:unit` → 12/12; `npm run test:e2e` → 20/20; `tools/test.ps1` → 153/153; `php -l` em 29 arquivos e `node --check` nos dois JS sem erros.
- **Ajuste fino:** `--story-fade-duration` no CSS controla o fade; em `main.js`, `FLICK_VELOCITY`, `MIN_DRAG`, `RELEASE_DECAY`, `SPRING_OMEGA` e `DRAG_RESISTANCE` controlam o carrossel.
- **Pendências:** F5 (identidade visual), F6 (entrega/hospedagem, incluindo `build-zip.ps1` ainda ausente) e F7 (backlog). CI de E2E ainda não foi configurado.

#### F5 — primeira passada de identidade visual — 30/10/2026

- Wordmark temporário **JT** no header (título do site), à espera do logo definitivo.
- Direção sans-led: Libre Franklin para títulos/navegação, Source Sans 3 para corpo/UI e Source Serif 4 para citações. Fontes self-hosted em `tema-base/assets/fonts/`, com licenças OFL na mesma pasta.
- Paleta aplicada em tokens: ink `#121417`, paper `#ffffff`, paper-soft `#f3f1ec`, azul institucional `#16324a` e destaque vinho `#a51c30`.
- Diretrizes em `docs/brand-guidelines.md`.
- Ajustes visuais em header, hero, botões, Destaques, Depoimentos, Histórias, Artigos, rodapé e páginas internas.
- **Destaques:** destino do drag monotônico, mais peso no gesto (`FLICK_VELOCITY=0.32`, `MIN_DRAG=16`, resistência `0.9`) e mola criticamente amortecida (`SPRING_OMEGA=15`) que parte da velocidade da soltura.
- **Depoimentos:** seção aberta em `paper-soft`, sem painel/borda, com setas nas laterais da página.
- Imagens demo institucionais temporárias em `tools/demo-images/`, com créditos em `tools/demo-images/CREDITS.md`; o gerador de placeholders não sobrescreve imagens existentes.
- Seed atualizado para criar menus principal/rodapé, opções de redes/escritórios/inscrição e conteúdo dos artigos.
- **Verificação:** 12 testes unitários, 20 E2E e 153 verificações da F1 passando; 29 arquivos PHP sem erro de sintaxe.
- **Pendente:** aprovação visual do cliente, logo/fotografias definitivas e ajustes finos de contraste/espaçamento.

#### F5.1 — Páginas internas e compartilhamento — 30/10/2026

- Home mantida como aprovada; a subfase cobre blog, artigo, página, sócios, história e 404.
- Direção minimalista/conteúdo primeiro: cards sem borda, imagem opcional, coluna de leitura de até 48 rem, metadados em caixa alta e navegação discreta.
- Blog/arquivo/busca com grade 3/2/1 e estado vazio; busca restrita a artigos (`post`), formulário próprio em `searchform.php`, campo discreto no rodapé e contagem de resultados; perfil de sócio em duas colunas no desktop; história mantém o hero full-bleed.
- Artigo individual com coluna de leitura de 54 rem e imagem panorâmica em 21/9, alinhadas na mesma largura.
- Compartilhamento sem plugin em artigo e história: WhatsApp, X, LinkedIn, Facebook, e-mail e “copiar link”, com `noopener noreferrer`, `aria-label`, área de 44 px e fallback de clipboard. Instagram fica como copiar link/Web Share API por não ter URL oficial de compartilhamento web.
- **Web Share API:** não implementada. Avaliar no futuro, em dispositivo móvel real, se há necessidade concreta e ganho sobre “copiar link”; só adicionar depois dessa validação.
- Arquivos novos: `template-parts/share.php`, `tb_share_links()` em `inc/helpers.php` e `tests/e2e/inner-pages.spec.js`.
- **Verificação:** 12 testes unitários, 20 E2E e 153 verificações da F1 passando; 29 arquivos PHP sem erro de sintaxe.
- **Pendente:** aprovação visual das telas internas e revisão do cliente.

#### F5.2 — Protótipo de conteúdo P0 — 30/10/2026

- Novos CPTs públicos: `area` (`/areas-de-atuacao/`) e `escritorio` (`/escritorios/`), com arquivo, single e campos de contato do escritório.
- Home ganhou a seção **Áreas de atuação**; templates novos para áreas e escritórios, cards e página de contato da unidade.
- Seed demo cria 4 áreas, 2 escritórios, 4 sócios com retratos, páginas **Sobre**, **Contato**, **Reconhecimentos** e **Privacidade**, além dos menus principal/rodapé.
- Arquivos públicos ordenados por `menu_order`; busca continua restrita a artigos.
- Footer reorganizado em topo (marca + busca), três colunas (navegação, escritórios, redes/inscrição) e base de copyright; escritórios linkados e CTA “Fale conosco”.
- **Verificação:** 12 testes unitários, 26 E2E e 153 verificações da F1 passando; 37 arquivos PHP sem erro de sintaxe.
- **Validado pelo usuário em 30/10/2026:** áreas, escritórios, perfis de sócios, páginas institucionais e footer.
- **Pendente:** módulos P1/P2 (setores, news, carreiras, eventos).

#### F6 — Profissionalização técnica — F6.1 e F6.2 implementadas — 30/10/2026

- **F6.1 Performance:** filtro `image_editor_output_format` em `inc/media.php` gera derivados WebP para uploads JPEG/PNG quando o editor suporta; qualidade WebP 80; original preservado; fallback para o formato original. `wp media regenerate --yes` regenerou 18 imagens. Preload do primeiro hero apenas na home, com `fetchpriority="high"`.
- Baseline antes: ~2,5 MB, ~2,39 MB em imagens, LCP local ~2,0 s. Depois: ~1,97 MB, ~1,80 MB em imagens, LCP local ~0,67 s (medida sintética local).
- Upload futuro verificado por importação temporária de JPEG: derivados `tb-*` gerados em `.webp`; anexo removido depois.
- **F6.2 SEO (modo híbrido):** `inc/seo.php` emite meta description (campo SEO → resumo → conteúdo → padrão), Open Graph, Twitter Card, canonical em home/arquivos, JSON-LD (`Organization`/`LegalService`, `WebSite`, `WebPage`/`CollectionPage`, `Article`, `Person`, `Service`, `LocalBusiness`, `BreadcrumbList`) e `noindex` na 404. Caixa SEO em post, página, área, escritório, sócio e história; opção de descrição da home; trilha visível nas páginas individuais. O tema se desliga quando Yoast, Rank Math, The SEO Framework, SEOPress, All in One SEO ou Slim SEO está ativo. `tools\seo-check.ps1` valida 65 itens; cobertura E2E em `tests/e2e/seo.spec.js`.
- **F6.3 Acessibilidade (30/10/2026):** Lighthouse/axe em 100 na home, artigos, áreas, escritórios, sócios, histórias, Sobre, Contato e busca. Corrigida a ordem de títulos nos arquivos (cards em `h2` sob o `h1`; `h3` na home), ampliado o alvo dos links de compartilhamento para 24 × 44 px e validados skip link, foco visível, menu mobile com Escape, rótulos de busca, `aria-current` da trilha e reflow de 320 a 1280 px. Cobertura em `tests/e2e/a11y.spec.js`.
- **F6.4 Produção (30/10/2026):** `tools\build-zip.ps1` gera `dist\tema-base.zip` (53 arquivos, 42 PHP sem erro, `tema-base/` na raiz) e o tema foi versionado em 0.4.0. `docs/production.md` cobre stack, instalação, segurança, desempenho, SEO, contato, LGPD, backup, monitoramento e go-live; `docs/client-guide.md` é o manual do cliente. Hospedagem, staging, SSL, backup, cache/CDN, formulário, LGPD e monitoramento seguem pendentes de contas/decisões do cliente.

#### Ajuste visual da home e controles — 30/10/2026

- Superfícies da home: Destaques em `paper`; Áreas em `paper-soft` com bordas e cards brancos; Depoimentos em azul de ponta a ponta; Artigos com linha superior. Hero, Histórias, rodapé e páginas internas preservados.
- Depoimentos: faixa azul de ponta a ponta, sem faixas claras em cima ou embaixo; citação serifada maior, aspas decorativas, título/controles à esquerda e citação à direita no desktop; setas, bullets e botão de pausa em branco; empilhado no mobile. A troca é por **slide direcional** (próximo entra pela direita, anterior pela esquerda), a cada 6 s.
- A rotação dos depoimentos não pausa com o mouse sobre a seção; pausa apenas no botão, no foco/teclado e com a aba oculta.
- Botões de hero e depoimentos passaram a usar ícones de pause/play com `aria-label`, `aria-pressed` e alvo de 44 × 44 px.
- Barra de progresso no rodapé do hero: 2 px, vinho, intervalo de 6 s, pausa/retomada sincronizada com o timer e reset na troca manual; oculta em `prefers-reduced-motion`.
- Histórias: "Teste" recebeu a imagem demo `historia-01`; `tb_story_image_id()` adiciona fallback para o primeiro banner; a seta para cima anima apenas `top`/`height` (sem deslocamento horizontal) e o fundo começa a trocar no clique, em 320 ms, acompanhando o texto.
- **Verificação:** 12 unitários, 51 E2E, 153 da F1, 65 no `seo-check`, 39 arquivos PHP sem erro e Lighthouse de acessibilidade 100.

#### Single de área de atuação — 30/10/2026

- Campos `_tb_services` e `_tb_sectors` no CPT `area`, textarea com uma entrada por linha; `tb_lines()` limpa e apara a lista.
- Layout: cabeçalho com imagem 21:9; texto; **Como atuamos** em duas colunas numeradas; **Setores** em chips; faixa azul de CTA; **Outras áreas** com até três cards automáticos. Foto, texto e listas usam a largura interna do container (70 rem no desktop), alinhados ao título.
- Blocos condicionais: sem serviços/setores, a seção não é impressa.
- Seed preenche três áreas e deixa **Societário e M&A** sem os campos para cobrir o estado vazio.
- **Verificação:** 12 unitários, 54 E2E, 157 da F1, 65 no `seo-check`, 39 arquivos PHP sem erro e Lighthouse de acessibilidade 100 na single de área.

#### Single de escritório — 30/10/2026

- Campo `_tb_hours` (textarea, uma linha por período) somado a endereço, telefone, e-mail e mapa.
- Layout: cabeçalho com resumo e imagem 21:9; texto; horário; aside de contato; CTA azul; outros escritórios automáticos.
- Largura alinhada ao título; seed com São Paulo preenchido e Rio sem horário para cobrir o estado vazio.
- Corrigido o `\n` literal nos endereços do seed e separado o resumo do endereço.
- **Verificação:** 12 unitários, 57 E2E, 162 da F1, 65 no `seo-check`, 39 arquivos PHP sem erro e Lighthouse de acessibilidade 100 na single de escritório.

#### Single de sócio — 30/10/2026

- `_tb_role` limpo para cargo; novos campos `_tb_areas` (relação múltipla com `area`), `_tb_education`, `_tb_languages` e `_tb_oab`.
- Layout com retrato, chips de áreas linkadas, contato, bio e sidebar de formação/idiomas/OAB; card do arquivo mantém o desenho e mostra apenas o cargo.
- `Person.knowsAbout` inclui as áreas; seed separa cargo/área e preenche três perfis, deixando Diego sem detalhes.
- Teste do painel confirmou checkboxes de áreas e limpeza da relação quando nada é marcado.
- **Verificação:** 12 unitários, 59 E2E, 167 da F1, 65 no `seo-check`, 39 arquivos PHP sem erro e Lighthouse de acessibilidade 100 na single de sócio.

#### Single de história — 30/10/2026

- Campo `_tb_credit` para o crédito da imagem; exibido sobre o hero apenas quando há imagem e crédito.
- Layout mantém hero, corpo, link e compartilhamento; adiciona **Outras histórias** com três cards automáticos e exclui a atual. O corpo usa a largura do título (70 rem).
- Card novo em `template-parts/card-story.php`.
- Seed com crédito em três histórias; `historia-01` e “Teste” sem crédito para o estado vazio.
- `Article.creditText` no schema quando há crédito.
- **Verificação:** 12 unitários, 62 E2E, 168 da F1, 65 no `seo-check`, 40 arquivos PHP sem erro e Lighthouse de acessibilidade 100 na single de história.

#### Ajustes de CPTs e comentários — 30/10/2026

- Excerto do sócio exibido como lead; seed com frase própria, sem duplicar a bio.
- Meta description de história passa a usar `_tb_summary` antes do conteúdo.
- Comentários e pingbacks fechados globalmente; suporte removido de post/page; feed de comentários desligado; menu de comentários removido do painel.
- **Verificação:** 12 unitários, 62 E2E, 171 da F1, 65 no `seo-check`, 40 arquivos PHP sem erro.

#### Páginas institucionais e conteúdo relacionado — 03/11/2026

- `page-sobre.php`: conteúdo, time e escritórios; CTA para Contato.
- `page-contato.php`: conteúdo, canais e cards de contato dos escritórios; sem formulário até decisão do cliente.
- Single de área ganhou **Sócios da área** via relação `_tb_areas` existente.
- Single de artigo ganhou **Artigos relacionados** por categoria, com até três posts e seção condicional.
- Conteúdo das páginas alinhado ao título em 54 rem; nenhum campo novo.
- **Verificação:** 12 unitários, 68 E2E, 172 da F1, 65 no `seo-check`, 44 arquivos PHP sem erro e Lighthouse de acessibilidade 100 em Sobre.

#### Busca global — 03/11/2026

- `search.php` e `card-search.php`; a consulta inclui posts, páginas, áreas, escritórios, sócios e histórias.
- Cada resultado tem etiqueta de seção; formulário passou a “Buscar no site”.
- Histórias agora são pesquisáveis; banners, destaques, depoimentos e anexos ficam fora.
- **Verificação:** 12 unitários, 75 E2E, 236 da F1, 65 no `seo-check`, 47 arquivos PHP sem erro.

#### Painel, textos, imagens e demonstração — 04/11/2026

**Checkout:** branch `codex/trabalho`; o código está em `0263133` e este registro é o commit seguinte. Sem push.

- **Polimento do painel:** o sócio foi dividido em **Perfil profissional** e **Contato**; textareas perderam a classe `code`; o suporte a autor saiu de posts/páginas; o widget de comentários saiu do painel; links internos dos menus passaram a usar caminhos relativos; as posições de menu dos CPTs foram para 30–36; as caixas de metadados abrem por padrão para novos editores.
- **Textos curtos:** `maxlength` em etiqueta (40), frase de apoio (160), botão/link (30), fonte (80), cargo (80), OAB (30) e crédito (120), com o limite visível abaixo do campo; `line-clamp` em hero, cards e leads; a home de Histórias passou a usar `_tb_summary` antes do primeiro parágrafo.
- **Imagens:** `tb_card_image()` centraliza imagem/placeholder; o placeholder institucional usa monograma **JT**, `aria-hidden` e a mesma proporção do card; aplicado a posts, áreas, escritórios, histórias, sócios, Destaques e arquivo de sócios; o hero ganhou fundo de fallback; a caixa da imagem avisa sobre o placeholder.
- **Destaques:** regra documentada — fixados primeiro (data decrescente), completando com os recentes (data decrescente). No editor o checkbox se chama **“Fixo”** e o texto de ajuda é “Fixar este post no topo do blog”; na Edição rápida o rótulo é “Fixar no topo”.
- **Histórias no mobile:** em até 700 px, a lista e o painel usam a geometria do desktop: lista à direita, alinhada ao título ativo, e título vertical à esquerda. O painel ativo fica fixo e troca o conteúdo, enquanto a lista desliza. Teste E2E em 320/390 px. Desktop acima de 700 px mantém o visual.
- **Demonstração:** Cloudflare Quick Tunnel documentado em `docs/demo-tunnel.md`, com URLs dinâmicas no `wp-config.php` local e usuário Editor temporário `cliente-demo` (recriado pelo atalho local `.local\demo-cliente.bat`). URL e credenciais ficam em `.local/DEMO-TUNEL.txt`, não versionado; o atalho inicia/para o túnel e mostra os dados. Site local, MariaDB e túnel estavam ativos no fechamento deste registro.
- **Manual rápido:** submenu **Opções do Site → Manual do cliente**, com seis seções e três prints anotados; link também no topo de Opções do Site. Arquivos: `inc/manual.php`, `assets/css/admin-manual.css` e `assets/manual/`.
- **Verificação em 04/11/2026:** 12 unitários, 75 E2E, 236 da F1, 65 no `seo-check`, 47 arquivos PHP sem erro; zip com 0,52 MB, 58 arquivos e 43 PHP.
- **Pendente:** aprovação do cliente pelo túnel; identidade final; formulário e LGPD; hospedagem/SSL/backup/cache; decisão sobre migrar os CPTs para plugin; manual completo em PDF; F7.

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
