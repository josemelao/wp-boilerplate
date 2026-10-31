# LWclone — tema WordPress

Site institucional em WordPress com **tema próprio** (`tema-base`), layout travado em código e conteúdo editável pelo cliente. O plano completo está em [`PLANO-DE-ACAO.md`](PLANO-DE-ACAO.md) e a referência visual é [`assets-full-page.png`](assets-full-page.png).

## Estado atual

- **F0 (Preparação) concluída**: ambiente local reproduzível, WordPress instalado e tema ativo.
- **F1 (Fundação) concluída**: tipos de conteúdo, meta boxes, “Opções do Site”, experiência do cliente no painel, header/footer e menus.
- **F2 (Home) implementada e validada pelo usuário**: as seções, estilos, interações, seed local e página individual de História estão no tema. A validação manual confirmou autoplay, carrosséis, Histórias, navegação, console e larguras de 360/768/1280 px.
- **F3 (Blog/Páginas/Sócios) implementada e validada**: listagens, singles, página, arquivo/perfil de sócio e 404 têm templates próprios; rotas e conteúdo passaram nas verificações locais e no passo a passo do usuário.
- **F4 (QA) concluída**: validação do usuário e Lighthouse mobile/desktop concluídos; desempenho, acessibilidade e boas práticas marcaram 100 nas duas medições.
- **F5 (Identidade visual) — primeira passada**: wordmark temporário **JT**, tokens, fontes self-hosted, imagens institucionais demo e ajustes visuais na home e páginas internas. Home aprovada.
- **F5.1 (Páginas internas) implementada**: blog, artigo, página, sócios, história e 404 revisados com leitura minimalista; compartilhamento nativo no artigo e na história, sem plugin.
- **F5.2 (Protótipo de conteúdo P0) implementada e validada**: áreas de atuação, escritórios, perfis de sócios demo, páginas Sobre/Contato/Reconhecimentos/Privacidade, menus, seção de áreas na home e footer reorganizado com escritórios linkados e CTA de contato.
- **F6.1 (Performance de mídia) implementada**: derivados WebP automáticos para uploads JPEG/PNG, qualidade 80, regeneração das 18 imagens e preload do hero na home.
- **F6.2 (SEO) implementada em modo híbrido**: descrição, Open Graph/Twitter, schema, trilha de navegação e `noindex` no tema; o tema se desliga quando um plugin de SEO está ativo. Plugin segue recomendado em produção (ver `docs/seo.md`).
- **F6.3 (Acessibilidade) implementada**: Lighthouse/axe em 100 nas páginas auditadas, ordem de títulos corrigida nos arquivos, alvo do compartilhamento ampliado e testes de teclado, foco, skip link, menu mobile e reflow de 320 a 1280 px.
- **F6.4 (Produção) iniciada**: pacote instalável em `dist/tema-base.zip` (tema 0.4.0) e documentação de produção/manual do cliente. Hospedagem, staging, SSL, backup, cache/CDN, formulário, LGPD e monitoramento dependem do cliente.
- **Ajuste visual da home (30/10/2026)**: faixas de superfície, depoimentos em azul de ponta a ponta, ícones de play/pause, barra de progresso no hero e correções nas Histórias.
- **Single de área de atuação (30/10/2026)**: serviços e setores editáveis, CTA e cards automáticos das outras áreas.
- **Single de escritório (30/10/2026)**: cabeçalho com imagem, horário de atendimento editável, contato, CTA e outros escritórios.
- **Single de sócio (30/10/2026)**: áreas linkadas, formação, idiomas, OAB e sidebar de perfil.
- **Single de história (30/10/2026)**: crédito da imagem, outras histórias e leitura preservada.

## Requisitos

- **Windows 10/11 x64** (o runtime é Windows-only).
- **PowerShell 5.1+** (já vem no Windows).
- **Internet** na primeira execução (~170 MB de download; ~500 MB em disco).
- **Portas 8080 e 3307 livres.**
- **Visual C++ Redistributable 2015–2022** — o script avisa se estiver faltando; download em <https://aka.ms/vs/17/release/vc_redist.x64.exe>.

Não é necessário ter PHP, MySQL, Docker, LocalWP, Composer ou privilégios de administrador.

## Rodar do zero (primeira vez)

```powershell
git clone https://github.com/josemelao/wp-boilerplate.git LWclone
cd LWclone
powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -Start
```

O `setup-local.ps1` é **idempotente** e faz, nesta ordem:

1. Baixa versões **pinadas** e confere o SHA-256 de cada arquivo.
2. Extrai PHP, MariaDB, WordPress e WP-CLI em `.local/` (fora do Git).
3. Escreve o `php.ini` com as extensões e limites do projeto.
4. Inicializa o MariaDB e cria o banco `wordpress` + usuário `wp`.
5. Cria o `wp-config.php`, instala o WordPress e ativa `pt_BR`.
6. Cria a **junction** `wp-content/themes/tema-base` → `tema-base/`.
7. Ativa o tema e configura permalinks `/%postname%/`.
8. Gera `.local/CREDENCIAIS.txt` e o manifesto `.local/env.json`.

Ao final: <http://127.0.0.1:8080/>.

Para definir outras senhas locais:

```powershell
powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 `
  -AdminPassword "sua-senha" -DbPassword "senha-do-banco" -Start
```

## Depois de um pull

- **Mudou só o tema ou a documentação?** Nada a fazer. A junction reflete `tema-base/` na hora; se o site estiver parado, rode `tools\dev.ps1 start`.
- **Mudou `tools/setup-local.ps1` ou alguma versão pinada?** Rode o setup de novo:

  ```powershell
  powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -Start
  ```

  O script compara o manifesto `.local/env.json` com as versões dele. Se bater, apenas reaplica a configuração. Se não bater, ele **para** e pede `-Reset`.
- **`-Reset`** recria o runtime do zero e **apaga o conteúdo local** (posts, mídia e banco):

  ```powershell
  powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -Reset -Start
  ```

- **Nunca copie `.local/` entre máquinas** e nunca commite `.local/` nem `.local/CREDENCIAIS.txt`. Cada pessoa gera o seu.

## Uso diário

```powershell
# subir / derrubar / status
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 stop
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 status

# WP-CLI
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 post list
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 theme list
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 media regenerate --yes

# verificações do tema (F1)
powershell -ExecutionPolicy Bypass -File tools\test.ps1

# conteúdo de demonstração da home (F2)
powershell -ExecutionPolicy Bypass -File tools\seed-demo.ps1
```

Depois de reiniciar a máquina, basta rodar `dev.ps1 start`.

## Acessar o painel

- Site: <http://127.0.0.1:8080/>
- Painel: <http://127.0.0.1:8080/wp-admin/>
- Credenciais: `.local/CREDENCIAIS.txt` (não versionado)
- Padrão: usuário `admin`, senha `admin_local_2026` (ou a informada no setup)

Login verificado de ponta a ponta: `POST /wp-login.php` → **302** para `/wp-admin/` → **200** com o painel.

Para testar como **Editor** (o papel que o cliente vai usar):

```powershell
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 user create editor editor@example.com `
  --role=editor --user_pass=editor_local_2026 --display_name="Editor Teste"
```

O Editor vê os 5 tipos de conteúdo, edita páginas/posts e acessa **Opções do Site** — mas não acessa plugins, temas nem usuários.

## Testes do tema

```powershell
powershell -ExecutionPolicy Bypass -File tools\test.ps1
```

Executa `tests\f1-checks.php` dentro do WordPress (`wp eval-file`). Só precisa do MariaDB rodando. Sai com código 1 se algo falhar.

Cobre: CPTs registrados (público, arquivo, supports, REST), metas registradas na REST com `auth_callback`, tamanhos de imagem, menus, opções e sanitização, salvamento de meta (nonce, capability, sanitização), colunas e ordem do admin, helpers (`tb_opt`, `tb_query`, `tb_featured_posts`, `tb_blog_url`) e renderização de header/footer.

### Testes unitários e E2E

Os testes de navegador usam Playwright e precisam do site local rodando:

```powershell
# uma vez
npm install
npx playwright install chromium

# conteúdo conhecido para os testes
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
powershell -ExecutionPolicy Bypass -File tools\seed-demo.ps1

# execução
npm run test:unit
npm run test:e2e
npm run test:e2e:smoke
```

- `tests\unit\carousel-target.test.js` cobre a decisão de destino do carrossel de Destaques (arrasto lento, flick, limites e deslocamento mínimo).
- `tests\e2e\smoke.spec.js` cobre a home sem erros de console e as rotas principais (200/404).
- `tests\e2e\featured.spec.js` cobre arrasto, flick, setas, teclado, clique após arrasto e ausência de classes presas.
- `tests\e2e\stories.spec.js` cobre o fade simétrico de título e subtexto, a navegação pelas setas, a ausência de clique nos itens inativos e o alinhamento das setas.
- `tests\e2e\testimonials.spec.js` cobre a seção aberta, as setas laterais, o slide direcional, o autoplay após clique e a ausência de sobreposição em 320 px.
- `tests\e2e\area-single.spec.js` cobre serviços, setores, CTA, outras áreas e o esconder dos blocos vazios.
- `tests\e2e\office-single.spec.js` cobre horário, contato, CTA, outro escritório, estado vazio e alinhamento da largura.
- `tests\e2e\socio-single.spec.js` cobre cargo, áreas linkadas, formação, idiomas, OAB e o estado sem detalhes.
- `tests\e2e\story-single.spec.js` cobre crédito da imagem, outras histórias e o estado sem crédito.
- `tests\e2e\inner-pages.spec.js` cobre o compartilhamento do artigo, cópia do link, largura de leitura e alinhamento da imagem.
- `tests\e2e\search.spec.js` cobre a busca restrita a artigos, estado vazio e campo do rodapé.
- `tests\e2e\content-modules.spec.js` cobre áreas, escritórios, páginas institucionais, ordem dos arquivos e perfil de sócio.
- `tests\e2e\footer.spec.js` cobre a organização do rodapé, escritórios linkados e CTA de contato.
- `tests\e2e\seo.spec.js` cobre descrição, Open Graph, Twitter Card, schema, trilha visível e `noindex` em busca/404.
- `tests\e2e\a11y.spec.js` cobre estrutura, alt, ids únicos, ordem de títulos, skip link, foco visível, controles nomeados, menu mobile e reflow de 320 a 1280 px.
- Os testes E2E só leem o conteúdo local; não apagam posts nem alteram opções.

Verificação desta entrega em 30/10/2026: `npm run test:unit` → **12/12**; `npm run test:e2e` → **62/62**; `tools\test.ps1` → **171/171**; `tools\seo-check.ps1` → **65/65**; `php -l` em 40 arquivos e `node --check` nos dois arquivos JS sem erros.

## Ambiente padronizado

| Peça | Versão | Caminho |
|---|---|---|
| PHP | 8.3.36 (NTS x64) | `.local/php/` |
| MariaDB | 11.4.13 | `.local/mariadb/` + dados em `.local/db/` |
| WordPress | 7.1.4 (pt_BR) | `.local/wp/` |
| WP-CLI | 2.12.0 | `.local/wp-cli.phar` |

Ficam iguais para todo o time: versões, portas (8080/3307), URL, `php.ini`, permalinks, idioma, tema ativo e WP-CLI. As versões são **pinadas de propósito** — não troque por `latest`. O manifesto `.local/env.json` registra o que está instalado; é ele que permite ao script detectar divergência depois de um pull.

## Estrutura

```
LWclone/
├── PLANO-DE-ACAO.md
├── README.md
├── AGENTS.md
├── assets-full-page.png
├── package.json
├── playwright.config.js
├── .gitignore
├── .local/                 ← runtime gerado (fora do Git; inclui env.json)
├── docs/
│   ├── brand-guidelines.md ← paleta, tipografia e regras da identidade JT
│   ├── seo.md             ← decisão de SEO, plugin recomendado e checklist
│   ├── production.md      ← instalação, segurança, cache, LGPD e go-live
│   └── client-guide.md    ← manual do cliente (Editor)
├── tests/
│   ├── f1-checks.php       ← verificações da fundação (wp eval-file)
│   ├── unit/               ← testes Node do JS puro
│   └── e2e/                ← testes Playwright de navegador
├── tools/
│   ├── setup-local.ps1     ← instala o ambiente do zero
│   ├── dev.ps1             ← start / stop / status
│   ├── wp.ps1              ← atalho do WP-CLI
│   ├── test.ps1            ← roda tests/f1-checks.php
│   ├── seo-check.ps1       ← confere as tags de SEO do site em execução
│   ├── build-zip.ps1       ← gera dist/tema-base.zip para instalação
│   ├── router.php          ← rewrite do servidor embutido
│   ├── seed-demo.ps1       ← gera imagens e popula conteúdo da home
│   ├── seed-demo.php       ← cria/atualiza conteúdo via WP-CLI
│   ├── make-demo-images.php ← gera placeholders só quando a imagem não existe
│   ├── demo-images/        ← imagens demo temporárias
│   └── demo-images/CREDITS.md ← origem e licença das imagens
└── tema-base/
    ├── style.css           ← só o cabeçalho do tema (versão)
    ├── functions.php       ← carrega os módulos de inc/
    ├── header.php          ← skip link, marca, menu principal
    ├── footer.php          ← menu, redes, escritórios, copyright
    ├── front-page.php      ← home com as seções de conteúdo
    ├── single-historia.php ← página editorial própria de cada história
    ├── single-area.php     ← área de atuação individual
    ├── archive-area.php    ← arquivo de áreas de atuação
    ├── single-escritorio.php ← escritório individual
    ├── archive-escritorio.php ← arquivo de escritórios
    ├── single.php          ← artigo individual
    ├── page.php            ← páginas institucionais
    ├── archive-socio.php   ← arquivo de sócios
    ├── single-socio.php    ← perfil individual de sócio
    ├── 404.php             ← página de conteúdo não encontrado
    ├── index.php           ← blog, categorias, busca e fallback
    ├── searchform.php      ← formulário de busca compacto
    ├── inc/
    │   ├── setup.php       ← supports, menus, tamanhos, enqueue, busca só em posts
    │   ├── post-types.php  ← os 5 CPTs
    │   ├── meta-fields.php ← meta boxes, salvamento, REST
    │   ├── options-page.php← "Opções do Site"
    │   ├── helpers.php     ← tb_opt(), tb_query(), tb_featured_posts(), tb_share_links()
    │   ├── media.php       ← derivados WebP e preload do hero
    │   ├── seo.php         ← descrição, Open Graph, schema e trilha
    │   └── admin-ux.php    ← colunas, ordem, dicas, aviso de Leitura
    ├── template-parts/     ← hero, destaques, depoimentos, histórias, artigos, compartilhamento e trilha
    └── assets/
        ├── css/main.css    ← tokens, componentes e responsivo
        ├── fonts/          ← Libre Franklin, Source Sans 3 e Source Serif 4
        └── js/
            ├── main.js     ← menu, faders, carrossel e histórias
            └── carousel-target.js ← decisão pura do destino do carrossel
```

O `router.php` é versionado de propósito: sem ele, um clone não reproduz os permalinks no `php -S`.

## Home e conteúdo de demonstração (F2)

`front-page.php` monta Hero, Destaques, Depoimentos, Histórias e Artigos em destaque. O rodapé é compartilhado pelo tema. Cada seção não imprime markup quando não há conteúdo publicado.

Para popular o WordPress local com conteúdo de demonstração, rode `tools\seed-demo.ps1`. O comando usa as imagens institucionais temporárias de `tools\demo-images\` (se alguma estiver faltando, gera um placeholder de gradiente; imagens existentes não são sobrescritas), importa a mídia e cria/atualiza posts com slugs reservados para demonstração. Também configura as páginas locais **Início** e **Artigos**, os menus principal/rodapé e as opções de redes, escritórios e inscrição. O seed não apaga posts ou páginas existentes. Créditos das imagens em `tools\demo-images\CREDITS.md`.

## O que foi testado (F0)

Verificado de verdade:

- `setup-local.ps1` executado do zero em um clone limpo: **87,6 s**, exit 0.
- `-Reset` executado na máquina principal (com downloads em cache): **32,5 s**, exit 0.
- Manifesto `.local/env.json` gravado; com versão divergente, o script **aborta** e pede `-Reset`.
- `php -v` → 8.3.36; extensões `mysqli`, `openssl`, `curl`, `mbstring`, `gd`, `exif`, `zip`, `fileinfo`, `intl` carregadas.
- `mysqld` em `127.0.0.1:3307`; usuário `wp` conecta no banco `wordpress`.
- `wp core version` → 7.1.4; `wp option get siteurl` → `http://127.0.0.1:8080`.
- `wp theme activate tema-base` → tema ativo.
- `GET /` → **200**; `GET /hello-world/` → **200**; CSS do core → **200**.
- `GET /wp-admin/` sem login → **302**; login → **302** para o painel → **200**.
- Nenhum `Warning`/`Notice`/`Fatal` no log do PHP.

Não testado ainda: telas do tema, navegação completa, acessibilidade e responsivo — entram no QA (F4).

## O que foi testado (F1)

- `tools\test.ps1` → **153 verificações, 0 falhas** (executado em 29/10/2026 após as alterações locais desta entrega).
- Painel: os 5 tipos aparecem no menu (`Banners do topo`, `Destaques`, `Depoimentos`, `Histórias`, `Sócios`), com ícones diferentes.
- `post-new.php?post_type=banner` → **200**, com os 4 campos, nonce e a dica de tamanho de imagem; “Ordem” (Atributos) presente.
- Listagens: colunas **Imagem** e **Ordem**; dois banners com ordens 1 e 2 apareceram na ordem certa (1 antes de 2).
- Salvamento real de meta pelo formulário do `wp-admin`: `POST /wp-admin/post.php` → **302**, e no banco `_tb_label`, `_tb_link` e `_tb_link_label` corretos; `<script>` removido do texto.
- Nonce inválido e usuário sem permissão **não** gravam meta.
- “Opções do Site”: admin e **Editor** abrem a página (**200**); o Editor **salva** (`POST options.php` → **302**) e o valor muda no banco.
- Aviso de Leitura aparece para administrador quando não há página inicial estática/página de posts.
- Editor recebe **403** em `users.php`, `plugins.php` e `themes.php`.
- Front: `/` → **200**, `/socios/` → **200** (arquivo existe; template próprio na F3), `/hello-world/` → **200**, CSS e JS do tema → **200**.
- Header/footer reais: skip link, `main#conteudo`, menu principal, rodapé com redes só preenchidas e escritórios.
- Nenhum `Warning`/`Notice`/`Fatal` nos requests reais; `php -l` limpo em todos os arquivos PHP.

Não testado na F1: as 6 seções da home (F2), templates de post/página/sócio (F3), acessibilidade completa e responsivo em dispositivos (F4).

## Estado da F2

Implementados: template inicial, cinco seções de conteúdo (com rodapé compartilhado), controles e autoplay de Hero/Depoimentos, scroll-snap e arrasto do carrossel com settling/momentum, lista circular de Histórias com cartão e setas fixos, single editorial das histórias, cards de artigos, CSS responsivo e seed de demonstração.

Iterações solicitadas pelo usuário: corrigir drag do mouse que prendia/acionava links, suavizar o snap e acrescentar momentum leve; manter Hero e Depoimentos em rotação automática; aproximar Histórias da referência; eliminar a duplicação do item ativo; exibir até quatro slots (3 × 22% + 1 × 34%); mostrar o primeiro parágrafo como chamada; manter cartão e setas parados, reservar espaço para os controles e animar a lista circular sem itens aparecendo no meio da seção. A entrada para cima foi corrigida para começar fora da tela inferior antes de subir.

Verificado após os ajustes: seed executado com sucesso; `/` responde **200**; `/historias/historia-01/` responde **200** e usa `single-historia.php`; a lista da home aponta para essa URL.

O usuário informou **“validado”** após conferir autoplay do Hero/Depoimentos; arrasto do carrossel; setas dos Depoimentos; Histórias para cima e para baixo, sem salto, com cartão/setas fixos e link para a single; cards e rotas de artigos; e layout em 360/768/1280 px sem erros no Console. A lista usada tinha cinco histórias, incluindo “Teste”. Na validação F4, também confirmou toque, teclado/foco, histórias com 3 e 4 itens e home com seções vazias.

## Estado da F3

Implementados: listagem paginada de artigos, arquivo de busca/categorias, artigo individual com navegação anterior/próximo, páginas institucionais, arquivo e perfil de sócio, e página 404. Os templates usam os campos de contato definidos na F1 e mantêm o conteúdo editorial no editor do WordPress.

Verificado no ambiente local:

- PHP sem erro de sintaxe nos seis templates da F3 (`index.php`, `single.php`, `page.php`, `archive-socio.php`, `single-socio.php` e `404.php`).
- `tools\test.ps1` → **153 verificações da F1 passaram**.
- `/artigos/`, `/artigo-01/`, `/sample-page/`, `/socios/` e `/category/perspectivas/` responderam **200**; URL inexistente respondeu **404**.
- Um perfil temporário de sócio confirmou a rota `/socios/{slug}/`, renderização de nome, cargo, biografia e links de e-mail, telefone e LinkedIn; o registro temporário foi removido após a verificação.

O usuário informou **“validado”** após navegar por artigos, single, retorno, categoria, página, arquivo de sócios e 404. O perfil temporário usado na verificação automatizada foi removido; não há perfis de sócio publicados no conteúdo local agora.

## Estado da F4

Verificações locais feitas em 29/10/2026:

- Capacidades do papel Editor: `edit_posts`, `edit_pages` e `edit_others_posts` habilitadas; `manage_options`, `activate_plugins` e `edit_theme_options` desabilitadas. O usuário confirmou o login real e a validação do acesso como Editor.
- As cinco partes da home (`hero`, `carousel-featured`, `testimonials`, `stories`, `insights`) renderizaram saída vazia quando receberam `items=[]`.
- A parte de Histórias renderizou 3, 4 e 5 itens com exatamente um ativo e um grupo de controles em cada configuração. Isso confirma o markup/slots; a animação em navegador continua validada manualmente com cinco itens.
- Com `WP_DEBUG` e `WP_DEBUG_LOG` temporariamente ativos, as rotas da home, artigos, página, sócios, categoria e história responderam com 200 e uma URL inexistente com 404. Não foi criado `wp-content/debug.log`; `wp-config.php` foi restaurado para `WP_DEBUG=false`. O log do servidor contém acessos HTTP, sem linhas de warning/notice/fatal do PHP.
- `php -l` nos 23 arquivos PHP do tema → sem erros; `node --check assets/js/main.js` → sem erro; `tools\test.ps1` → **153 verificações da F1 passaram**. As sete rotas de conteúdo verificadas retornaram exatamente um `<h1>` no HTML inicial.
- Ajustes de acessibilidade feitos durante o QA: um `<h1>` ativo por vez no Hero (os outros títulos são `<h2>` e o nível acompanha a rotação), autoplay desligado por padrão quando `prefers-reduced-motion: reduce`, pausa dos faders em foco/hover e grupos de bullets com nome acessível.
- Lighthouse na home local: mobile — desempenho 100, acessibilidade 100, boas práticas 100, SEO 92; desktop — 100 nas quatro categorias. Mobile: FCP 1,2 s, LCP 1,5 s, Speed Index 1,2 s, TBT 0 ms e CLS 0. As pontuações são medições sintéticas do ambiente local e podem variar entre execuções.
- Para zerar os apontamentos de acessibilidade do primeiro Lighthouse, removi o `aria-label` redundante dos links dos cards e aumentei a área clicável dos bullets e controles de Histórias para pelo menos 24 × 24 px; as novas medições não apontaram falhas de acessibilidade.
- SEO marcou 92 porque a home local não tem meta description. O plano recomenda configurar SEO/Open Graph com um plugin na fase de entrega/backlog, em vez de adicionar metadados incompletos no tema. Relatórios HTML/JSON (ignorados pelo Git) estão em `.local/lighthouse/`. **Superado na F6.2:** o tema passou a emitir descrição, Open Graph, schema e trilha; ver `docs/seo.md`.

Validação manual confirmada pelo usuário: login e permissões do Editor; controles por teclado/foco; toque no carrossel; página inicial completa sem conteúdo; Histórias com 3 e 4 itens, além da configuração com 5; e comportamento visual em 360/768/1280 px. O usuário também já havia validado os fluxos F2/F3 descritos acima.

## Ajustes de carrossel e Histórias (30/10/2026)

- **Destaques:** drag aplicado via `requestAnimationFrame`; settle com mola criticamente amortecida (`SPRING_OMEGA=15`), usando a velocidade real da soltura e sem `scrollTo({ behavior: 'smooth' })`; flick rápido avança no máximo um card; arrasto lento usa o card mais próximo; pausa antes de soltar reduz a velocidade; `prefers-reduced-motion` vai direto ao card; resize recentraliza.
- A decisão de destino ficou pura em `tema-base/assets/js/carousel-target.js`, com teste unitário.
- **Histórias:** fade simétrico de 320 ms para título e subtexto (`branco → vermelho` ao entrar, `vermelho → branco` ao sair); subtexto passou de `hidden` para opacidade; itens inativos são texto estático, sem clique; o título ativo continua link para a página individual.
- **Setas de Histórias:** SVG em vez de glifos, alinhamento horizontal consistente; desktop 52 × 40 px e mobile 44 × 36 px; no mobile os controles ficaram na linha inferior do cartão para não esmagar o texto.
- **Testes:** `tests/unit/` (12), `tests/e2e/featured.spec.js` (8), `tests/e2e/stories.spec.js` (8), `tests/e2e/story-single.spec.js` (3), `tests/e2e/smoke.spec.js` (2), `tests/e2e/testimonials.spec.js` (4), `tests/e2e/inner-pages.spec.js` (3), `tests/e2e/area-single.spec.js` (3), `tests/e2e/office-single.spec.js` (3), `tests/e2e/socio-single.spec.js` (2), `tests/e2e/search.spec.js` (2), `tests/e2e/content-modules.spec.js` (4), `tests/e2e/footer.spec.js` (1), `tests/e2e/media.spec.js` (1), `tests/e2e/seo.spec.js` (6), `tests/e2e/a11y.spec.js` (6), `tests/e2e/hero.spec.js` (3) e `tests/e2e/design.spec.js` (3). Total E2E: **62**.
- **Verificado em 30/10/2026:** `npm run test:unit` → 12/12; `npm run test:e2e` → 62/62; `tools\test.ps1` → 171/171; `tools\seo-check.ps1` → 65/65; `php -l` em 40 arquivos e `node --check` nos dois JS sem erros.
- **Ajuste fino:** `--story-fade-duration` no CSS controla o fade das Histórias; em `main.js`, `FLICK_VELOCITY`, `MIN_DRAG`, `RELEASE_DECAY`, `SPRING_OMEGA` e `DRAG_RESISTANCE` controlam o carrossel.

## Primeira passada de identidade visual — F5 (30/10/2026)

- Wordmark temporário **JT** no header, usando o título do site; trocar pelo logo definitivo no Customizer quando estiver disponível.
- Direção sans-led: Libre Franklin para títulos/navegação, Source Sans 3 para corpo/UI e Source Serif 4 para citações.
- Fontes self-hosted em `tema-base/assets/fonts/`, com licenças OFL na mesma pasta.
- Paleta: ink `#121417`, paper `#ffffff`, paper-soft `#f3f1ec`, azul institucional `#16324a` e destaque vinho `#a51c30`.
- Tokens e diretrizes em `docs/brand-guidelines.md`.
- Ajustes aplicados em header, hero, botões, Destaques, Depoimentos, Histórias, Artigos, rodapé e páginas internas.
- **Destaques:** destino do drag ficou monotônico (não volta depois de passar de um card), com mais peso: `FLICK_VELOCITY=0.32`, `MIN_DRAG=16`, resistência `0.9` e mola criticamente amortecida (`SPRING_OMEGA=15`) que parte da velocidade da soltura.
- **Depoimentos:** seção aberta em `paper-soft`, sem painel/borda; setas posicionadas nas laterais da página.
- Imagens demo institucionais temporárias substituídas em `tools/demo-images/`; créditos em `tools/demo-images/CREDITS.md`. O gerador de placeholders agora não sobrescreve imagens existentes.
- Seed atualizado para criar menus principal/rodapé, opções de redes/escritórios/inscrição e conteúdo de artigos.
- **Verificado em 30/10/2026:** `npm run test:unit` → 12/12; `npm run test:e2e` → 26/26; `tools\test.ps1` → 153/153; `php -l` em 37 arquivos e `node --check` nos dois JS sem erros.
- **Pendente:** aprovação visual do cliente, logo/fotografias definitivas e eventuais ajustes de contraste/espaçamento após revisão.

## F5.1 — Páginas internas e compartilhamento (30/10/2026)

- Home permanece como aprovada.
- **Blog/arquivo/busca:** cabeçalho simples, grade 3/2/1, cards sem borda e imagem opcional; estado vazio com busca. A busca é restrita a artigos (`post`) e mostra a contagem de resultados.
- **Artigo individual:** coluna de leitura de 54 rem, imagem panorâmica em 21/9, metadados em caixa alta, navegação discreta e bloco de compartilhamento após o conteúdo.
- **Página institucional:** mesmo ritmo de leitura, sem data/categoria.
- **Sócios:** cards sem borda; perfil em duas colunas no desktop e empilhado no mobile.
- **História individual:** hero full-bleed mantido, corpo em coluna de leitura e compartilhamento após o texto.
- **404/busca vazia:** layout curto, centrado e funcional.
- **Compartilhamento sem plugin:** WhatsApp, X, LinkedIn, Facebook, e-mail e “copiar link”; links externos com `noopener noreferrer`, botão de cópia com `navigator.clipboard` e fallback. Instagram não tem URL oficial de compartilhamento web; o fallback é copiar o link/Web Share API.
- **Busca:** formulário próprio em `searchform.php`, campo discreto no rodapé e busca limitada a artigos.
- **Web Share API — avaliar no futuro:** antes de implementar, testar em dispositivos móveis reais se há necessidade/ganho concreto sobre o botão “copiar link”. Não adicionar complexidade sem essa validação.
- **Arquivos novos:** `tema-base/template-parts/share.php`, `tb_share_links()` em `inc/helpers.php` e `tests/e2e/inner-pages.spec.js`.
- **Verificado em 30/10/2026:** `npm run test:unit` → 12/12; `npm run test:e2e` → 20/20; `tools\test.ps1` → 153/153; `php -l` em 29 arquivos e `node --check` sem erros.
- **Pendente:** aprovação visual das telas internas e revisão do cliente.

## F5.2 — Protótipo de conteúdo P0 (30/10/2026)

- Novos CPTs públicos: `area` (`/areas-de-atuacao/`) e `escritorio` (`/escritorios/`), com arquivo e single próprios.
- Campos do escritório: endereço, telefone, e-mail e link do mapa.
- Home ganhou a seção **Áreas de atuação** com três cards.
- Templates novos: `archive-area.php`, `single-area.php`, `archive-escritorio.php`, `single-escritorio.php`, `template-parts/areas.php`, `card-area.php` e `card-office.php`.
- Seed demo agora cria 4 áreas, 2 escritórios, 4 sócios com retratos, páginas **Sobre**, **Contato**, **Reconhecimentos** e **Privacidade**, além dos menus principal/rodapé.
- Arquivos públicos ordenados por `menu_order`; busca continua restrita a artigos.
- Footer reorganizado em topo (marca + busca), três colunas (navegação, escritórios, redes/inscrição) e base de copyright; escritórios linkados e CTA “Fale conosco”.
- **Verificado em 30/10/2026:** `npm run test:unit` → 12/12; `npm run test:e2e` → 26/26; `tools\test.ps1` → 153/153; `php -l` em 37 arquivos e `node --check` sem erros.
- **Validado pelo usuário em 30/10/2026:** áreas, escritórios, perfis de sócios, páginas institucionais e novo footer.
- **Pendente:** P1/P2 (setores, news, carreiras, eventos, busca global) ficam para depois da reação ao protótipo.

## F6 — Profissionalização técnica

### F6.1 — Performance de mídia (implementada em 30/10/2026)

Baseline antes: **~2,5 MB** no total, **~2,39 MB** em imagens, LCP local **~2,0 s** e CLS praticamente zero. O Lighthouse da F4 não representa mais o peso atual.

- `inc/media.php` usa `image_editor_output_format` para gerar derivados **WebP** de uploads JPEG/PNG quando o editor suporta; o original é preservado e há fallback para o formato original.
- Qualidade WebP definida em **80**.
- `wp media regenerate --yes` regenerou **18 imagens**.
- Preload do primeiro hero apenas na home, com `fetchpriority="high"`.
- Upload futuro verificado com importação temporária de JPEG: os tamanhos `tb-*` foram gerados em `.webp`; o anexo de teste foi removido.
- Depois: **~1,97 MB** no total, **~1,80 MB** em imagens, LCP local **~0,67 s** (medição sintética local).
- Cobertura em `tests/e2e/media.spec.js`.

### F6.2 — SEO (implementada em 30/10/2026)

Decisão: **híbrido**. O tema entrega o essencial agora e se desliga automaticamente quando um plugin de SEO reconhecido está ativo (Yoast, Rank Math, The SEO Framework, SEOPress, All in One SEO ou Slim SEO). Em produção o plugin continua recomendado para redirects, análise e painel editorial.

- `inc/seo.php`: meta description (campo SEO → resumo → conteúdo → padrão), Open Graph, Twitter Card, canonical em home/arquivos, JSON-LD (`Organization`/`LegalService`, `WebSite`, `WebPage`/`CollectionPage`, `Article`, `Person`, `Service`, `LocalBusiness` e `BreadcrumbList`) e `noindex` na 404.
- Caixa **SEO** na lateral do editor para post, página, área, escritório, sócio e história (`_tb_seo_description`, até 160 caracteres, registrada na REST).
- Opções do Site → SEO → descrição da página inicial.
- Imagem social: destacada → ícone do site → logo → primeiro banner, no tamanho `tb-slide` (1280 × 640).
- Trilha de navegação visível em artigo, página, história, sócio, área e escritório.
- `tools\seo-check.ps1` valida **65 itens** no site em execução; `tests/e2e/seo.spec.js` cobre o comportamento no navegador.
- Checklist do plugin em `docs/seo.md`.
- **Verificado em 30/10/2026:** 12/12 unitários, 32/32 E2E, 153/153 da F1, 65/65 no `seo-check` e `php -l` em 39 arquivos.
- **Pendente:** escolher e instalar o plugin em produção, configurar redirects 301, Search Console e a imagem social definitiva.

### F6.3 — Acessibilidade (implementada em 30/10/2026)

- Lighthouse (axe) marcou **100 em acessibilidade** na home, artigos (arquivo e artigo), áreas (arquivo e individual), escritórios (arquivo e individual), sócios (arquivo e perfil), histórias, Sobre, Contato e busca. A rota 404 não é auditável pelo Lighthouse porque responde 404; o `noindex` dela é coberto por E2E.
- **Ordem de títulos corrigida** nos arquivos de artigos, áreas e escritórios: os cards usam `h2` abaixo do `h1` da página e continuam `h3` dentro das seções da home, que já têm `h2`.
- **Compartilhamento:** o link “X” tinha 7 px de largura; todos os `.share__link` agora têm alvo mínimo de 24 × 44 px.
- **Teclado e foco:** skip link aparece no primeiro Tab, leva ao conteúdo e exibe contorno visível; menu mobile abre/fecha com Escape e devolve o foco ao botão; trilha tem nome acessível e `aria-current`.
- **Formulários e controles:** busca do rodapé e do estado vazio têm rótulo; links de compartilhamento e botão de cópia têm nome acessível.
- **Reflow:** sem rolagem horizontal em 320, 360, 768 e 1280 px nas páginas verificadas.
- Cobertura em `tests/e2e/a11y.spec.js`.
- **Verificado em 30/10/2026:** 12/12 unitários, 38/38 E2E, 153/153 da F1, 65/65 no `seo-check`, `php -l` em 39 arquivos.
- **Observação:** o Lighthouse local marcou desempenho 80 com LCP sintético de 5,4 s; a medição direta no navegador segue em ~0,67 s. Reavaliar em staging com cache/CDN na F6.4.

### F6.4 — Produção (pacote e documentação prontos; infraestrutura pendente)

- `tools\build-zip.ps1` gera `dist\tema-base.zip` com `tema-base/` na raiz, valida os arquivos obrigatórios e recusa pacotes com pastas de desenvolvimento.
- Pacote verificado em 30/10/2026: **0,19 MB**, **47 arquivos**, **36 PHP sem erro de sintaxe**, fontes, CSS/JS e `style.css` na versão **0.4.0**.
- Tema, `TB_VERSION` e `package.json` atualizados para **0.4.0**.
- `docs/production.md`: stack recomendada, instalação, segurança, desempenho, SEO, contato, LGPD, backup, monitoramento, go-live e pendências do cliente.
- `docs/client-guide.md`: manual do cliente para posts, home, sócios, áreas, escritórios, páginas, opções e SEO.
- **Não provisionado:** hospedagem, staging, SSL, backup, cache/CDN, formulário funcional, LGPD/cookies e monitoramento. Dependem de contas e decisões do cliente.

### Pendente na F6.4 e F7

- **F6.4:** executar o checklist de `docs/production.md` no ambiente contratado, com staging e testes de restauração.
- **F7 Backlog:** setores, news, carreiras, eventos, busca global, newsletter e multilíngue, conforme demanda.

## Ajuste visual da home e controles (30/10/2026)

- **Ritmo de superfícies:** Destaques em branco; Áreas em `paper-soft` com bordas de 1 px e cards brancos; Depoimentos em **azul de ponta a ponta**; Artigos com linha superior. Hero, Histórias, rodapé e páginas internas foram preservados.
- **Depoimentos:** citação serifada maior, aspas decorativas, título/controles à esquerda e citação à direita no desktop; setas, bullets e botão de pausa em branco; empilhado no mobile. A troca é por **slide direcional** (próximo entra pela direita, anterior pela esquerda), a cada 6 s. A rotação **não pausa com o mouse sobre a seção**; pausa apenas no botão, no foco/teclado e com a aba oculta.
- **Play/pause:** hero e depoimentos usam ícones SVG (pause rodando, play pausado), com `aria-label`, `aria-pressed` e alvo de 44 × 44 px.
- **Barra do hero:** 2 px na base, cor vinho, preenche no intervalo do carrossel (6 s), pausa/retoma junto com o timer e reinicia na troca manual; oculta em `prefers-reduced-motion`.
- **Histórias:** o item “Teste” recebeu a imagem demo `historia-01`; itens sem imagem caem no primeiro banner ou em gradiente azul/ink. A seta para cima anima apenas `top`/`height` (sem deslize horizontal) e o fundo começa a trocar **no clique**, em 320 ms, acompanhando o texto.
- **Testes:** `hero.spec.js` (3), `design.spec.js` (3), `stories.spec.js` (8) e `testimonials.spec.js` (4). Lighthouse de acessibilidade segue **100**.
- **Observação:** a imagem do “Teste” é conteúdo local de demonstração; deve ser substituída pela definitiva antes da publicação.

## Single de área de atuação (30/10/2026)

- Novos campos no CPT `area`: `_tb_services` e `_tb_sectors`, ambos textarea com uma entrada por linha. `tb_lines()` limpa, apara e remove linhas vazias.
- Layout: cabeçalho com resumo e imagem 21:9; texto do editor; **Como atuamos** em duas colunas numeradas; **Setores** em chips; faixa azul de CTA; **Outras áreas** com até três cards automáticos. Foto, texto e listas usam a largura interna do container (70 rem no desktop), alinhados ao título.
- Blocos condicionais: sem serviços ou setores, a seção correspondente não é impressa.
- Seed: três áreas com conteúdo e **Societário e M&A** sem os campos, cobrindo o estado vazio.
- **Testes:** `tests/e2e/area-single.spec.js` (3), F1 com **157** verificações e **54** E2E.
- **Verificado em 30/10/2026:** 360/768/1280 px e Lighthouse de acessibilidade **100**.
- **Pendente:** revisão do texto e das listas pelo cliente; imagem e resumo continuam no editor/excerto.

## Single de escritório (30/10/2026)

- Novo campo `_tb_hours` (textarea, um período por linha); os demais contatos continuam em `_tb_address`, `_tb_phone`, `_tb_email` e `_tb_maps_url`.
- Layout: cabeçalho com resumo e imagem 21:9; texto; horário de atendimento; aside de contato; faixa azul de CTA; **Outros escritórios** com até três cards automáticos.
- Foto, texto e conteúdo alinhados à largura do título, como na single de área.
- Seed: São Paulo com dois períodos de horário e Rio de Janeiro sem o campo, cobrindo o estado vazio.
- Corrigido também o endereço dos escritórios no seed, que usava `\n` literal; o resumo agora é uma frase própria, separada do endereço.
- **Testes:** `tests/e2e/office-single.spec.js` (3), F1 com **162** verificações e **57** E2E.
- **Verificado em 30/10/2026:** 360/1280 px e Lighthouse de acessibilidade **100**.

## Single de sócio (30/10/2026)

- `_tb_role` agora guarda só o cargo; a área saiu do texto do cargo.
- Novos campos: `_tb_areas` (relação múltipla com o CPT `area`), `_tb_education`, `_tb_languages` e `_tb_oab`.
- Layout: retrato + cabeçalho com cargo, **lead a partir do excerto**, chips de áreas linkadas e contato; bio em coluna de leitura; sidebar com formação, idiomas e OAB; retorno aos sócios.
- `Person` no schema ganhou `knowsAbout` com as áreas.
- Seed: cargos limpos, uma área por sócio e detalhes demo em Ana, Bruno e Carla; Diego cobre o estado sem detalhes.
- O card de `/socios/` mantém o layout e passa a exibir apenas o cargo.
- **Testes:** `tests/e2e/socio-single.spec.js` (2), F1 com **167** verificações e **59** E2E.
- **Verificado em 30/10/2026:** 360/1280 px, painel de metabox e Lighthouse de acessibilidade **100**.

## Single de história (30/10/2026)

- Novo campo `_tb_credit` (texto curto) para o crédito da imagem; aparece sobre o hero e só quando há imagem e crédito.
- Layout: hero atual preservado; crédito discreto; corpo, link “Leia também” e compartilhamento; **Outras histórias** com até três cards automáticos, excluindo a atual; retorno no fecho. O corpo usa a largura útil do container (70 rem no desktop), alinhado ao título.
- Card novo em `template-parts/card-story.php`, com imagem 3:2, kicker, título e resumo.
- Seed: crédito em três histórias; `historia-01` e “Teste” ficam sem crédito para o estado vazio.
- `Article.creditText` incluído no schema quando há crédito.
- A meta description da história usa `_tb_summary` antes do conteúdo.
- **Testes:** `tests/e2e/story-single.spec.js` (3), F1 com **168** verificações e **62** E2E.
- **Verificado em 30/10/2026:** 360/1280 px e Lighthouse de acessibilidade **100**.

## Ajustes de CPTs e comentários (30/10/2026)

- O excerto do sócio agora aparece como **lead** no cabeçalho; o seed usa uma frase própria, sem repetir a bio.
- A meta description de **história** usa `_tb_summary` antes do conteúdo.
- **Comentários e pingbacks desabilitados globalmente**: filtros de fechamento, remoção do suporte em posts/páginas, feed de comentários desligado e menu de comentários removido do painel.
- F1 passou a cobrir comentários fechados e ausência de suporte.

## Limitações conhecidas

- **Windows-only.** Mac/Linux precisam de outro caminho (Docker ou LocalWP), ainda não suportado.
- **MariaDB ≠ MySQL 8** de produção. Para este tema o risco é baixo, mas o banco local não é idêntico ao da hospedagem.
- **`php -S` ≠ nginx/Apache**: não reproduz cache, headers, HTTPS nem `.htaccess`.
- **Sem HTTPS e sem captura de e-mail** (MailHog). Não são necessários para este escopo.
- **LocalWP não é o caminho oficial** (decisão D11 do plano); quem usar fica por conta própria e pode divergir.

## Solução de problemas

**Porta ocupada**

```powershell
Get-NetTCPConnection -LocalPort 8080 -State Listen
Get-NetTCPConnection -LocalPort 3307 -State Listen
```

Encerre o processo dono da porta ou rode `tools\dev.ps1 stop`.

**PowerShell bloqueia o script**

```powershell
powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1
```

**PHP não inicia / falta DLL**

Instale o Visual C++ Redistributable 2015–2022 (link acima).

**Ver logs**

- `.local/logs/php.err.log`
- `.local/logs/mariadb.err.log`

**Checksum divergente**

Apague o arquivo correspondente em `.local/downloads/` e rode o setup de novo. Se persistir, a URL pinada mudou — não continue com versões diferentes.

**Recomeçar do zero**

```powershell
powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -Reset -Start
```

O `-Reset` para os serviços, apaga o runtime e reinstala. O conteúdo local (posts, mídia e banco) é perdido.

## Próximos passos

1. **Validar a F5.2 com o cliente:** apresentar o protótipo P0 e decidir o que entra/sai antes de avançar para P1/P2.
2. **F6 — Profissionalização técnica:** performance de imagens, SEO/schema, acessibilidade, staging, SSL, backup, contato, LGPD e manual.
3. **F7 — Backlog:** setores, news, carreiras, eventos, busca global, newsletter e multilíngue, conforme demanda.
