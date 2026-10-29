# LWclone — tema WordPress

Site institucional em WordPress com **tema próprio** (`tema-base`), layout travado em código e conteúdo editável pelo cliente. O plano completo está em [`PLANO-DE-ACAO.md`](PLANO-DE-ACAO.md) e a referência visual é [`assets-full-page.png`](assets-full-page.png).

## Estado atual

- **F0 (Preparação) concluída**: runtime local portátil funcionando, WordPress instalado e tema mínimo ativo.
- O tema ainda é um **esqueleto** (`style.css`, `functions.php`, `index.php`). A fundação real (CPTs, metas, opções, header/footer) é a F1.

## Ambiente local

Não há PHP, MySQL nem LocalWP instalados globalmente. O projeto usa um runtime **portátil dentro de `.local/`** (fora do Git):

| Peça | Versão | Caminho |
|---|---|---|
| PHP | 8.3.36 (NTS x64) | `.local/php/` |
| MariaDB | 11.4.13 | `.local/mariadb/` + dados em `.local/db/` |
| WordPress | 7.1.4 (pt_BR) | `.local/wp/` |
| WP-CLI | phar | `.local/wp-cli.phar` |

- Site: <http://127.0.0.1:8080/>
- Painel: <http://127.0.0.1:8080/wp-admin/>
- Credenciais locais: `.local/CREDENCIAIS.txt` (não versionado)
- O tema `tema-base/` é mapeado ao vivo por uma **junction** em `.local/wp/wp-content/themes/tema-base`.

### Comandos

```powershell
# subir / derrubar / status
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 stop
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 status

# WP-CLI
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 post list
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 theme list
```

O servidor é o embutido do PHP (`php -S`) com `router.php`, que reproduz os permalinks (`/%postname%/`). Logs em `.local/logs/`.

### Recriar o runtime

`.local/` é descartável. Para recriar, baixe PHP 8.3 NTS x64, WordPress, WP-CLI e MariaDB 11.4 (winx64), repita a configuração do `php.ini` (extensões `mysqli`, `openssl`, `curl`, `mbstring`, `gd`, `exif`, `zip`, `fileinfo`) e rode `mariadb-install-db`. Um script de bootstrap automatizado ainda não existe.

## O que foi testado (F0)

Verificado de verdade:

- `php -v` → 8.3.36; extensões necessárias carregadas.
- `mysqld` sobe em `127.0.0.1:3307`; `wp` conecta no banco `wordpress`.
- `wp core version` → 7.1.4; `wp option get siteurl` → `http://127.0.0.1:8080`.
- `wp theme activate tema-base` → tema ativo.
- `GET /` → **200**, título `LW Clone`.
- `GET /wp-admin/` → **302** para o login (comportamento esperado).
- Permalinks configurados em `/%postname%/`.

Não testado ainda: login no painel pelo navegador, navegação de posts/permalinks, telas do tema, acessibilidade e responsivo. Serão cobertos nas próximas fases.

## Próximos passos

1. **F1 — Fundação**: `setup.php`, CPTs, meta boxes, “Opções do Site”, admin UX, header/footer, menus.
2. **F2 — Home**: as 6 seções com CSS/JS.
3. **F3 — Blog/Sócios/Páginas**.
4. **F4 — QA** (Editor, a11y, responsivo, `WP_DEBUG`).
