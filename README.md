# LWclone — tema WordPress

Site institucional em WordPress com **tema próprio** (`tema-base`), layout travado em código e conteúdo editável pelo cliente. O plano completo está em [`PLANO-DE-ACAO.md`](PLANO-DE-ACAO.md) e a referência visual é [`assets-full-page.png`](assets-full-page.png).

## Estado atual

- **F0 (Preparação) concluída**: ambiente local reproduzível, WordPress instalado e tema mínimo ativo.
- O tema ainda é um **esqueleto** (`style.css`, `functions.php`, `index.php`). A fundação real (CPTs, metas, opções, header/footer) é a F1.

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
```

Depois de reiniciar a máquina, basta rodar `dev.ps1 start`.

## Acessar o painel

- Site: <http://127.0.0.1:8080/>
- Painel: <http://127.0.0.1:8080/wp-admin/>
- Credenciais: `.local/CREDENCIAIS.txt` (não versionado)
- Padrão: usuário `admin`, senha `admin_local_2026` (ou a informada no setup)

Login verificado de ponta a ponta: `POST /wp-login.php` → **302** para `/wp-admin/` → **200** com o painel.

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
├── tools/
│   ├── setup-local.ps1     ← instala o ambiente do zero
│   ├── dev.ps1             ← start / stop / status
│   ├── wp.ps1              ← atalho do WP-CLI
│   └── router.php          ← rewrite do servidor embutido
└── tema-base/
    ├── style.css
    ├── functions.php
    └── index.php
```

O `router.php` é versionado de propósito: sem ele, um clone não reproduz os permalinks no `php -S`.

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

1. **F1 — Fundação**: `setup.php`, CPTs, meta boxes, “Opções do Site”, admin UX, header/footer, menus.
2. **F2 — Home**: as 6 seções com CSS/JS + seed de conteúdo demo.
3. **F3 — Blog/Sócios/Páginas**.
4. **F4 — QA** (Editor, a11y, responsivo, `WP_DEBUG`).
