# Produção — checklist de entrega (F6.4)

Atualizado em 30/10/2026.

O ambiente local está pronto e o tema pode ser empacotado. **A produção ainda
não foi provisionada**: hospedagem, domínio, SSL, backup, cache/CDN, formulário
de contato e LGPD dependem de decisões e contas do cliente.

## 1. Stack recomendada

| Peça | Recomendação |
|---|---|
| PHP | 8.2 ou 8.3 (o tema é compatível com 7.4+) |
| Banco | MySQL 8 ou MariaDB 10.6+ |
| Servidor | nginx ou Apache com HTTP/2 e HTTPS |
| WordPress | 6.3+ (testado localmente em 7.1) |
| Cache | cache de página no servidor ou plugin (um só) |
| CDN | Cloudflare ou equivalente |
| E-mail | SMTP transacional com SPF/DKIM/DMARC |

## 2. Empacotar e instalar

```powershell
powershell -ExecutionPolicy Bypass -File tools\build-zip.ps1
```

O arquivo sai em `dist\tema-base.zip` com a pasta `tema-base/` na raiz.

1. Aparência → Temas → Adicionar novo → **Enviar tema** → ativar.
2. Configurações → Leitura: página inicial estática e página de posts.
3. Configurações → Links permanentes: `/artigo/%postname%/` e salvar.
4. Criar/atribuir os menus **principal** e **rodapé**.
5. Preencher **Opções do Site**: redes, escritórios, inscrição, rodapé e SEO.
6. Cadastrar o conteúdo real (ou importar) e revisar as imagens destacadas.
7. Rodar `wp media regenerate --yes` depois de subir as imagens definitivas.
8. Conferir `/`, `/artigos/`, `/socios/`, `/areas-de-atuacao/` e `/escritorios/`.
9. Rodar `tools\seo-check.ps1 -BaseUrl https://dominio` e corrigir o que falhar.

Se `/socios/` ou `/areas-de-atuacao/` der 404, rodar
`wp rewrite flush --hard`.

## 3. Segurança

- [ ] Usuário administrador próprio, senha forte e 2FA.
- [ ] Cliente com papel **Editor**, sem acesso a plugins/temas/usuários.
- [ ] `DISALLOW_FILE_EDIT` e `DISALLOW_FILE_MODS` definidos conforme a operação.
- [ ] Atualizações automáticas de segurança e janela para plugins/tema.
- [ ] Remover temas e plugins não usados.
- [ ] HTTPS forçado, HSTS e cookies `Secure`.
- [ ] Limitar tentativas de login e proteger `wp-login.php`/`xmlrpc.php`.
- [ ] Salts e chaves do `wp-config.php` únicos por ambiente.
- [ ] `WP_DEBUG` desligado e logs fora da raiz pública.
- [ ] `blog_public` ligado (Configurações → Leitura).

## 4. Desempenho

O tema já entrega WebP automático, `srcset`, preload do LCP, JS com `defer`,
fontes self-hosted e CSS/JS versionados. Em produção, somar:

- [ ] Cache de página (LiteSpeed, nginx FastCGI ou plugin único).
- [ ] Compressão Brotli/gzip no servidor.
- [ ] CDN para estáticos e imagens.
- [ ] Cache de objeto (Redis/Memcached) se a hospedagem oferecer.
- [ ] Limpar o cache após trocar imagens, menus ou opções.
- [ ] Medir com PageSpeed Insights/Lighthouse **depois** do cache e do CDN.
- [ ] Confirmar que o primeiro slide do hero continua sem lazy-load.

O Lighthouse local marca desempenho 80 com LCP sintético alto, enquanto a
medição direta no navegador fica em ~0,67 s. A diferença é do `php -S` local;
a referência final precisa ser medida no staging/produção.

## 5. SEO

- [ ] Escolher **um** plugin e seguir `docs/seo.md`.
- [ ] Definir imagem social padrão e perfis sociais.
- [ ] Sitemap único (plugin ou core, não os dois).
- [ ] Enviar sitemap ao Google Search Console e Bing Webmaster Tools.
- [ ] Configurar redirecionamentos 301 do site antigo.
- [ ] Revisar títulos/descrições de home, artigos, áreas, escritórios e sócios.
- [ ] Conferir `noindex` em busca e 404.
- [ ] Validar com `tools\seo-check.ps1 -BaseUrl https://dominio`.

## 6. Formulário de contato

O tema entrega a página e o CTA, mas **não inclui formulário funcional**.

- [ ] Decidir plugin (Fluent Forms, Contact Form 7) ou serviço externo.
- [ ] Campos mínimos: nome, e-mail, assunto e mensagem.
- [ ] Consentimento LGPD e link para a política de privacidade.
- [ ] Anti-spam (honeypot e/ou Turnstile).
- [ ] Destinatário e cópia interna definidos.
- [ ] Testar envio, recebimento, resposta e registro.
- [ ] Definir retenção dos dados recebidos.

## 7. LGPD e cookies

- [ ] Publicar política de privacidade revisada juridicamente.
- [ ] Banner de cookies **somente** se houver analytics/marketing.
- [ ] Bloquear tags antes do consentimento (Consent Mode).
- [ ] Documentar base legal, retenção e contato do encarregado.
- [ ] Contrato/DPA com hospedagem e ferramentas de e-mail.
- [ ] Revisar links de terceiros e fontes externas.

## 8. Monitoramento e backup

- [ ] Monitoramento de uptime (UptimeRobot, Better Stack ou similar).
- [ ] Alertas de erro PHP e de banco.
- [ ] Backup diário de arquivos + banco, fora do servidor.
- [ ] Retenção definida (ex.: 30 dias) e **teste de restauração**.
- [ ] Rotina mensal de atualização e revisão de logs.
- [ ] Staging para testar atualizações antes da produção.

## 9. Go-live

- [ ] DNS e SSL válidos; redirecionamento `http → https`.
- [ ] `www` e domínio raiz apontando para o mesmo lugar.
- [ ] E-mail transacional testado (contato, senha, formulário).
- [ ] 404, busca e páginas institucionais revisadas.
- [ ] Usuários e permissões conferidos.
- [ ] Backup inicial pós-conteúdo.
- [ ] Manual do cliente entregue (`docs/client-guide.md`).
- [ ] Treinamento ou vídeo curto com o cliente.

## 10. Pendências do cliente

- Hospedagem, domínio e e-mail.
- Plugin de SEO, cache e formulário.
- Analytics/consentimento e texto jurídico de LGPD.
- Identidade final (logo, fotos, nome).
- Redirecionamentos e migração do site antigo.

## 11. Demonstração sem hospedagem

Enquanto a hospedagem não estiver contratada, o site pode ser apresentado por um
túnel temporário do Cloudflare. O passo a passo, o ajuste de URLs no
`wp-config.php`, o usuário temporário e o checklist de segurança estão em
`docs/demo-tunnel.md`.
