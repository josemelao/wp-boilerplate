# LWclone — tema WordPress

Site institucional em WordPress com **tema próprio** (`tema-base`), layout travado em código e conteúdo editável pelo cliente. O plano completo está em [`PLANO-DE-ACAO.md`](PLANO-DE-ACAO.md) e a referência visual é [`assets-full-page.png`](assets-full-page.png).

## Estado atual

- **F0 (Preparação) concluída**: ambiente local reproduzível, WordPress instalado e tema ativo.
- **F1 (Fundação) concluída**: 5 tipos de conteúdo, meta boxes, “Opções do Site”, experiência do cliente no painel, header/footer e menus.
- **F2 (Home) em implementação**: as seções, estilos, interações, seed local e página individual de História estão no tema; os ajustes do último teste manual aguardam nova conferência no navegador.
- Blog e páginas de sócio entram na **F3**.

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
    ├── index.php           ← fallback (blog/categorias/busca)
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

Implementados: template inicial, cinco seções de conteúdo (com rodapé compartilhado), controles de hero e depoimentos, scroll-snap e arrasto do carrossel, lista circular de histórias com seleção fixa na última posição, single editorial das histórias, cards de artigos, CSS responsivo e seed de demonstração.

Correções registradas após o teste manual de 29/10/2026: retomada do autoplay após o foco sair do componente, arrasto por mouse, setas laterais nos depoimentos, lista de histórias circular vertical com link para single própria e imagens demo de histórias com cores mais distintas. A nova revisão visual dessas correções ainda está pendente.

Verificado após os ajustes: seed executado com sucesso; `/` responde **200**; `/historias/historia-01/` responde **200** e usa `single-historia.php`; a lista da home aponta para essa URL.

**Pendente de nova conferência após os ajustes:** autoplay do hero e dos depoimentos, arrasto do carrossel com mouse, setas laterais dos depoimentos, rotação vertical das histórias e navegação para a single. Revalidar também seções sem conteúdo, console, teclado/toque e tamanhos responsivos antes de concluir a F2.

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

1. **F2 — Home**: as 6 seções com CSS/JS + seed de conteúdo demo.
2. **F3 — Blog/Sócios/Páginas**.
3. **F4 — QA** (Editor, a11y, responsivo, `WP_DEBUG`).
