# Demonstração para o cliente por túnel

Procedimento para mostrar o site local ao cliente sem contratar hospedagem.
Usa o **Cloudflare Quick Tunnel**: gratuito, sem conta e com URL HTTPS
temporária. O túnel expõe a máquina de quem roda o comando; não é staging.

## Pré-requisitos

- Site local funcionando (`tools\dev.ps1 start`).
- `cloudflared` instalado ou baixado em `.local\cloudflared.exe`.
- Acesso ao arquivo local `.local\wp\wp-config.php` (não versionado).

## 1. Instalar o cloudflared

Opção com winget:

```powershell
winget install --id Cloudflare.cloudflared --accept-source-agreements --accept-package-agreements
```

Opção sem winget: baixe `cloudflared-windows-amd64.exe` da página de releases
do Cloudflare e salve como `.local\cloudflared.exe`.

Confira:

```powershell
cloudflared --version
```

## 2. URLs dinâmicas no WordPress

Sem isso, o WordPress redireciona o visitante para `http://127.0.0.1:8080`.
Edite `.local\wp\wp-config.php` e insira o bloco abaixo **antes** de
`require_once ABSPATH . 'wp-settings.php';`:

```php
/* URLs dinâmicas: funcionam no local e no túnel. */
$tb_host   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '127.0.0.1:8080';
$tb_local  = in_array( $tb_host, array( '127.0.0.1:8080', 'localhost:8080' ), true );
$tb_tunnel = (bool) preg_match( '/(^|\.)(trycloudflare\.com|ngrok-free\.app|ngrok\.io)$/', $tb_host );

if ( ! $tb_local && ! $tb_tunnel ) {
	$tb_host = '127.0.0.1:8080';
}

$tb_https = ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] );

if ( $tb_https ) {
	$_SERVER['HTTPS'] = 'on';
}

define( 'WP_HOME', ( $tb_https ? 'https' : 'http' ) . '://' . $tb_host );
define( 'WP_SITEURL', ( $tb_https ? 'https' : 'http' ) . '://' . $tb_host );
```

As opções `home` e `siteurl` do banco não mudam. No local, o site continua em
`http://127.0.0.1:8080`; no túnel, assume o domínio HTTPS recebido.

## 3. Subir o site e o túnel

Em um terminal:

```powershell
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
```

Em outro:

```powershell
cloudflared tunnel --url http://127.0.0.1:8080
```

A saída traz uma URL parecida com:

```text
https://alguma-coisa-aleatoria.trycloudflare.com
```

Mantenha a janela aberta. A URL é efêmera: se o processo cair ou for reiniciado,
ela muda. O bloco do `wp-config.php` já cobre a troca.

## 4. Criar um usuário temporário

Nunca entregue o administrador. Crie um **Editor**:

```powershell
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 user create cliente-demo cliente@exemplo.com --role=editor --user_pass='SenhaForte2026!'
```

Anote o ID retornado para apagar depois.

## 5. Testar antes de enviar

Em uma janela anônima, abra a URL do túnel e confira:

- a home carrega com CSS e imagens;
- Sobre, Áreas, artigo e história continuam no domínio do túnel;
- `/wp-admin/` abre e o login do Editor funciona;
- nada redireciona para `127.0.0.1`;
- o menu e as imagens funcionam no celular.

Se o CSS sumir ou aparecer mixed content, confira o reconhecimento de HTTPS no
bloco do `wp-config.php` e reinicie o PHP:

```powershell
powershell -ExecutionPolicy Bypass -File tools\dev.ps1 restart
```

## 6. Enviar ao cliente

Envie em uma mensagem curta:

- URL do túnel;
- usuário e senha do Editor;
- o que revisar: home, páginas internas, áreas, sócios, histórias, textos e imagens;
- prazo para devolver comentários;
- aviso de que o link é temporário e público.

## 7. Encerrar e limpar

- `Ctrl+C` na janela do `cloudflared`.
- Apague o usuário temporário:

```powershell
powershell -ExecutionPolicy Bypass -File tools\wp.ps1 user delete <ID> --yes
```

O `wp-config.php` pode ficar como está: no local ele volta a usar
`127.0.0.1:8080` sozinho.

## Segurança

O Quick Tunnel **não tem autenticação**: quem tiver a URL entra. Portanto:

- use somente o usuário temporário de Editor;
- use senha forte e exclusiva;
- apague o usuário depois da demonstração;
- não divulgue o link em grupos;
- para uma porta de entrada com senha, use um túnel nomeado do Cloudflare com
  Access (exige domínio) ou o `ngrok` com `--basic-auth`.

## Problemas comuns

| Sintoma | Causa provável | Correção |
|---|---|---|
| Redireciona para `127.0.0.1` | bloco do `wp-config.php` ausente ou host fora do filtro | confira a posição do bloco e reinicie o PHP |
| CSS ou imagens quebradas | WordPress gerando `http://` | confira `X-Forwarded-Proto` e `$_SERVER['HTTPS']` |
| 404 nas páginas internas | permalinks | `tools\wp.ps1 rewrite flush --hard` |
| Login em loop | cookies do domínio antigo | limpe os cookies e use a URL exata do túnel |
| URL mudou | Quick Tunnel é efêmero | mantenha o processo aberto ou use túnel nomeado |
| Erro 1033 | túnel ou site parado | suba o `dev.ps1` e o `cloudflared` novamente |

## URL fixa

Para uma homologação de vários dias, use um **túnel nomeado** do Cloudflare com
um subdomínio próprio, por exemplo `demo.seudominio.com`. Exige o domínio no
Cloudflare, mas a URL não muda e permite colocar Access (login) na frente.
