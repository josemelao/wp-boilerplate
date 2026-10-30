# AGENTS.md

Instruções para sessões do OpenCode neste repositório.

## Leia antes de mexer

- `PLANO-DE-ACAO.md`: decisões D1–D11, escopo, fases e armadilhas. É o contrato do projeto.
- `README.md`: estado real, comandos, o que já foi testado e limitações.
- Ao concluir uma entrega, atualize o `README.md` registrando apenas o que foi verificado de fato.

## Visão geral

- Site institucional WordPress com tema clássico próprio em `tema-base/`; layout travado em PHP/CSS, conteúdo editável pelo cliente.
- Não migrar para Gutenberg/page builder nem adicionar ACF, jQuery, Swiper ou build de assets sem pedido explícito (D1–D4).
- 7 CPTs ficam no tema (`banner`, `destaque`, `depoimento`, `historia`, `socio`, `area`, `escritorio`), mais `post` nativo. Ordem = `menu_order`; rascunho = oculto; imagem = imagem destacada.
- Edite só `tema-base/`, `tools/`, `tests/` e `docs/`. `tema-base/` é exposto ao WP local por junction em `.local/wp/wp-content/themes/tema-base`; não edite essa cópia, o core nem plugins em `.local/`.
- A implementação atual está na branch `codex/trabalho`; `master` está atrás (sem F1–F4). Confirme com `git status`/`git log` antes de trocar de branch.
- F0–F4 concluídas e validadas; F5 (identidade visual) tem uma primeira passada com o wordmark temporário **JT**, e a F5.1 cobre as páginas internas e o compartilhamento. A F5.2 concluiu e validou o protótipo P0. A F6.1 iniciou a profissionalização com derivados WebP automáticos, a F6.2 concluiu o SEO híbrido (tema agora, plugin em produção) e a F6.3 concluiu a acessibilidade. A F6.4 preparou o pacote e a documentação de produção; infraestrutura, LGPD e contato dependem do cliente. Em 30/10/2026 a home recebeu ajuste visual, barra de progresso e correções nas Histórias. F7 pendente.
- Identidade visual: siga `docs/brand-guidelines.md`. Fontes self-hosted em `tema-base/assets/fonts/`; imagens demo e créditos em `tools/demo-images/` (`CREDITS.md`). Critérios por fase na seção 12 do plano.
- Mídia: `inc/media.php` gera derivados WebP para uploads JPEG/PNG. Depois de mudar tamanhos ou o pipeline, rode `tools\wp.ps1 media regenerate --yes`.
- Compartilhamento: componente próprio em `template-parts/share.php` + `tb_share_links()`. Web Share API é uma avaliação futura; não implementar sem teste real em mobile e necessidade comprovada.
- SEO: `inc/seo.php` emite descrição, Open Graph/Twitter, JSON-LD e trilha, e **se desliga** se Yoast, Rank Math, The SEO Framework, SEOPress, All in One SEO ou Slim SEO estiver ativo. Não adicionar tags de SEO soltas em templates; siga `docs/seo.md` e valide com `tools\seo-check.ps1`.

## Ambiente (Windows-only)

Runtime portátil não versionado em `.local/`: PHP 8.3.36, MariaDB 11.4.13, WordPress 7.1.4 pt-BR, WP-CLI 2.12.0. Portas 8080 (site) e 3307 (banco). Não use Docker, LocalWP nem `@wordpress/env`.

```powershell
# primeira vez (idempotente; baixa ~170 MB)
powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -Start

# dia a dia
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 stop
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 restart
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 status

# WP-CLI (não existe `wp` global)
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 post list

# conteúdo demo da home (não apaga conteúdo existente)
powershell -ExecutionPolicy Bypass -File tools\seed-demo.ps1

# verificações da fundação (MariaDB precisa estar rodando)
powershell -ExecutionPolicy Bypass -File tools\test.ps1

# pacote instalável para produção
powershell -ExecutionPolicy Bypass -File tools\build-zip.ps1
```

- `.local/CREDENCIAIS.txt` tem as senhas locais; `.local/` é ignorado pelo Git e nunca deve ser commitado nem copiado entre máquinas.
- `-Reset` no setup apaga banco e uploads locais. Logs: `.local/logs/php.err.log` e `.local/logs/mariadb.err.log`.
- Sempre invoque scripts com `powershell -ExecutionPolicy Bypass -File ...`.

## Verificação

- Há três camadas de teste: `tests/f1-checks.php` (153 checks da fundação, via `tools\test.ps1`), `tests/unit/` (Node) e `tests/e2e/` (Playwright). Ainda não há CI configurado.
- `tools\test.ps1` usa `wp eval-file` e restaura opções do banco ao final; exige MariaDB de pé e retorna código 1 em falha.
- Testes unitários e E2E:

  ```powershell
  npm install
  npx playwright install chromium
  npm run test:unit
  npm run test:e2e
  ```

  Os E2E precisam do site rodando (`tools\dev.ps1 start`) e do conteúdo demo (`tools\seed-demo.ps1`). Eles leem o conteúdo local e não apagam posts.

- Checagens focadas:

  ```powershell
  .\.local\php\php.exe -l tema-base\inc\setup.php
  node --check tema-base\assets\js\main.js
  powershell -ExecutionPolicy Bypass -File tools\seo-check.ps1
  ```

- Para mudanças de front-end/rotas, suba o site e confirme HTTP: `/`, `/artigos/`, `/socios/` e `/historias/{slug}/` devem dar 200; URL inexistente deve dar 404.
- Teste o painel como **Editor**, não só como admin; é assim que o cliente vai usar.
- Antes de dizer que terminou: `tools\test.ps1`, `npm run test:unit`, `npm run test:e2e`, `php -l` nos PHPs alterados, `node --check` se mexeu no JS e as rotas afetadas. Não declare validado o que só foi lido.

## Convenções

- Interface e conteúdo em pt-BR; responda ao usuário em português.
- Prefixo `tb_` para funções PHP; `tb-` para handles, tamanhos de imagem e utilitários; CSS em BEM (`.hero__slide`); JS vanilla ativado por `data-component`, sem build e falhando em silêncio quando o HTML não existe.
- PHP compatível com **7.4+** (declarado em `style.css`): evite sintaxe de PHP 8, como `match`, `?->`, argumentos nomeados e tipos union.
- Tokens de identidade em `:root` no topo de `tema-base/assets/css/main.css`; o enqueue versiona CSS/JS por `filemtime`.
- Segurança: escape na saída (`esc_html`, `esc_attr`, `esc_url`), sanitize na entrada, nonce + `current_user_can()` em todo salvamento. Metas `_tb_*` precisam de `auth_callback` no registro REST.
- Seções da home retornam vazio quando não há conteúdo e não usam `the_content()`. Use `wp_reset_postdata()` após `WP_Query`; `no_found_rows => true` quando não há paginação.

## Armadilhas

- CPTs e metas moram no tema (D7): trocar de tema os esconde do painel (os dados permanecem).
- Depois de mudar CPT/slug/rewrite: `tools\wp.ps1 rewrite flush --hard`; `/socios/` dá 404 sem isso.
- Configurações → Leitura precisa de página inicial estática e página de posts; o seed configura, mas um reset de conteúdo pode desfazer. Sem isso, o blog e o botão “Ler mais” quebram.
- Mudou `add_image_size()`? Rode `tools\wp.ps1 media regenerate --yes`.
- `tools/router.php` é obrigatório para permalinks no `php -S` e normaliza `/wp-admin` para `/wp-admin/`; não remova.
- Só o LCP (primeiro slide do hero) deve ser `eager`/`fetchpriority="high"`; o restante é lazy.
