# LWclone — tema WordPress

Site institucional em WordPress com **tema próprio** (`tema-base`), layout travado em código e conteúdo editável pelo cliente. O plano completo está em [`PLANO-DE-ACAO.md`](PLANO-DE-ACAO.md) e a referência visual é [`assets-full-page.png`](assets-full-page.png).

## Estado atual

- **F0 (Preparação) concluída**: ambiente local reproduzível, WordPress instalado e tema ativo.
- **F1 (Fundação) concluída**: 5 tipos de conteúdo, meta boxes, “Opções do Site”, experiência do cliente no painel, header/footer e menus.
- **F2 (Home) implementada e validada pelo usuário**: as seções, estilos, interações, seed local e página individual de História estão no tema. A validação manual confirmou autoplay, carrosséis, Histórias, navegação, console e larguras de 360/768/1280 px.
- **F3 (Blog/Páginas/Sócios) implementada e validada**: listagens, singles, página, arquivo/perfil de sócio e 404 têm templates próprios; rotas e conteúdo passaram nas verificações locais e no passo a passo do usuário.
- **F4 (QA) concluída**: validação do usuário e Lighthouse mobile/desktop concluídos; desempenho, acessibilidade e boas práticas marcaram 100 nas duas medições.

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
├── assets-full-page.png
├── .gitignore
├── .local/                 ← runtime gerado (fora do Git; inclui env.json)
├── tests/
│   └── f1-checks.php       ← verificações da fundação (wp eval-file)
├── tools/
│   ├── setup-local.ps1     ← instala o ambiente do zero
│   ├── dev.ps1             ← start / stop / status
│   ├── wp.ps1              ← atalho do WP-CLI
│   ├── test.ps1            ← roda tests/f1-checks.php
│   ├── router.php          ← rewrite do servidor embutido
│   ├── seed-demo.ps1       ← gera imagens e popula conteúdo da home
│   ├── seed-demo.php       ← cria/atualiza conteúdo via WP-CLI
│   ├── make-demo-images.php ← gera JPEGs placeholder usando GD
│   └── demo-images/        ← imagens geradas pelo seed
└── tema-base/
    ├── style.css           ← só o cabeçalho do tema (versão)
    ├── functions.php       ← carrega os módulos de inc/
    ├── header.php          ← skip link, marca, menu principal
    ├── footer.php          ← menu, redes, escritórios, copyright
    ├── front-page.php      ← home com as seções de conteúdo
    ├── single-historia.php ← página editorial própria de cada história
    ├── single.php          ← artigo individual
    ├── page.php            ← páginas institucionais
    ├── archive-socio.php   ← arquivo de sócios
    ├── single-socio.php    ← perfil individual de sócio
    ├── 404.php             ← página de conteúdo não encontrado
    ├── index.php           ← blog, categorias, busca e fallback
    ├── inc/
    │   ├── setup.php       ← supports, menus, tamanhos, enqueue
    │   ├── post-types.php  ← os 5 CPTs
    │   ├── meta-fields.php ← meta boxes, salvamento, REST
    │   ├── options-page.php← "Opções do Site"
    │   ├── helpers.php     ← tb_opt(), tb_query(), tb_featured_posts()
    │   └── admin-ux.php    ← colunas, ordem, dicas, aviso de Leitura
    ├── template-parts/     ← hero, destaques, depoimentos, histórias e artigos
    └── assets/
        ├── css/main.css    ← tokens, componentes e responsivo
        └── js/main.js      ← menu, faders, carrossel e histórias
```

O `router.php` é versionado de propósito: sem ele, um clone não reproduz os permalinks no `php -S`.

## Home e conteúdo de demonstração (F2)

`front-page.php` monta Hero, Destaques, Depoimentos, Histórias e Artigos em destaque. O rodapé é compartilhado pelo tema. Cada seção não imprime markup quando não há conteúdo publicado.

Para popular o WordPress local com conteúdo de demonstração, rode `tools\seed-demo.ps1`. O comando gera JPEGs de gradiente em `tools\demo-images\`, importa a mídia e cria/atualiza posts com slugs reservados para demonstração. Também configura as páginas locais **Início** e **Artigos** em Configurações → Leitura. O seed não apaga posts ou páginas existentes.

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
- SEO marcou 92 porque a home local não tem meta description. O plano recomenda configurar SEO/Open Graph com um plugin na fase de entrega/backlog, em vez de adicionar metadados incompletos no tema. Relatórios HTML/JSON (ignorados pelo Git) estão em `.local/lighthouse/`.

Validação manual confirmada pelo usuário: login e permissões do Editor; controles por teclado/foco; toque no carrossel; página inicial completa sem conteúdo; Histórias com 3 e 4 itens, além da configuração com 5; e comportamento visual em 360/768/1280 px. O usuário também já havia validado os fluxos F2/F3 descritos acima.

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

1. Seguir para a **F5 — Identidade visual**: definir logo, paleta e tipografia finais.
