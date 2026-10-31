# Manual do cliente

Guia rápido para publicar e manter o site. O layout é fixo: você edita
conteúdo, imagens, ordem e textos das opções; o desenho não muda pelo painel.

## 1. Acesso

- Painel: `https://dominio/wp-admin/`
- Papel: **Editor** — publica posts e páginas, edita o conteúdo, mas não mexe em
  plugins, temas, usuários nem menus.
- Menus, plugins e configurações gerais ficam com o administrador.

## 2. Antes de publicar

- Imagem em **JPEG ou PNG**, já otimizada (evite arquivos acima de ~400 KB).
- O tema gera automaticamente as versões **WebP** usadas no site. Não é preciso
  converter nem instalar plugin de imagem.
- Todo conteúdo tem **título**, **imagem destacada** e, de preferência, **resumo**.
- **Rascunho fica oculto** no site. Não existe campo “ativo”: publique para
  aparecer.

## 3. Artigo (blog)

1. Posts → Adicionar novo.
2. Escreva o título e o texto no editor.
3. Preencha o **Resumo** (usado nos cards, na busca e na descrição SEO).
4. Defina a **Imagem destacada** (recomendado 1600 × 900 px).
5. Escolha a **Categoria**.
6. Na caixa **SEO**, se quiser, escreva uma descrição de até 160 caracteres.
7. **Publicar**.

O artigo aparece em `/artigos/`, na busca e nos destaques da home conforme a
data e os fixos.

## 4. Home

A home é montada por listas. Cada item tem **Ordem** em Atributos → Ordem
(menor número aparece primeiro) e **Rascunho não aparece**.

### Banners do topo
- Imagem: **1920 × 800 px**.
- Campos: rótulo pequeno, texto curto, URL do botão e texto do botão.
- O primeiro banner é o LCP da home; mantenha a imagem leve.
- O botão de pause/play e a barra na base do hero são automáticos: pausam a troca e mostram o tempo até o próximo banner.

### Destaques (carrossel)
- Imagem: **1280 × 640 px**.
- Campos: rótulo, URL e texto do link.

### Depoimentos
- Título = manchete; texto do depoimento e fonte/autor.
- Sem imagem.

### Histórias
- Título, imagem de fundo **1600 × 900 px**, resumo e URL relacionada.
- **Crédito da imagem:** texto curto exibido sobre o hero; opcional.
- Cada história tem página própria em `/historias/{slug}/`, com outras histórias ao final.
- A seção mostra até quatro itens por vez e gira em loop.

### Artigos em destaque
- São posts normais. Fixe até três em Posts → Fixar no topo, se quiser.
- O título da seção vem de **Opções do Site**.

## 5. Sócios

1. Sócios → Adicionar novo.
2. Título = nome; retrato **640 × 800 px**.
3. Preencha cargo, e-mail, telefone e LinkedIn (todos opcionais).
4. Em **Áreas de atuação**, marque as áreas que aparecem no perfil; cada uma vira um link.
5. Em **Formação** e **Idiomas**, use uma linha por item. **OAB** é texto livre.
6. Escreva a biografia no editor e defina a **Ordem**.
7. Publique. O perfil sai em `/socios/{slug}/`.

## 6. Áreas de atuação

- Título = nome da área; imagem **1280 × 640 px**.
- Resumo e texto explicam a atuação.
- No box **Conteúdo da área**:
  - **Como atuamos:** um serviço por linha; vira a lista numerada em duas colunas.
  - **Setores:** um setor por linha; vira chips. Opcional.
- Ordem controla a sequência em `/areas-de-atuacao/` e na home.
- Cada área tem página própria, CTA para o contato e cards automáticos das outras áreas.

## 7. Escritórios

- Título = cidade; imagem **1280 × 640 px**.
- Endereço, telefone, e-mail e link do mapa.
- **Horário de atendimento:** um período por linha (ex.: “Segunda a sexta: 9h às 18h”). Opcional.
- O resumo aparece no cabeçalho da página; o endereço fica no bloco de contato.
- O rodapé e a home usam a lista de **Opções do Site → Escritórios**; mantenha os
  nomes iguais aos títulos das páginas para o link funcionar.
- Cada escritório tem página própria, CTA de contato e cards automáticos dos outros escritórios.

## 8. Páginas institucionais

Sobre, Contato, Reconhecimentos e Privacidade são páginas normais: título, texto
e imagem destacada opcional. A caixa **SEO** também vale aqui.

## 9. Opções do Site

Acesse **Opções do Site** no menu lateral:

- títulos das seções da home;
- redes sociais (só aparecem as preenchidas);
- lista de escritórios (uma cidade por linha);
- texto e URL de inscrição/newsletter;
- nota do rodapé;
- **descrição da página inicial** usada por buscadores e redes sociais.

## 10. Imagens e ordem

- Ordem: Atributos → **Ordem**. Menor número primeiro.
- Imagem destacada: é a imagem do card, do banner ou do perfil.
- Texto alternativo: preencha em Mídia → biblioteca → campo “Texto alternativo”.
  Isso melhora acessibilidade e SEO.
- Se uma imagem não aparecer, confira se ela está publicada e se o cache foi
  limpo.

## 11. SEO por conteúdo

Cada post, página, área, escritório, sócio e história tem a caixa **SEO** na
lateral:

- **Descrição:** até 160 caracteres, para Google e redes sociais.
- Em branco, o site usa o resumo e depois o início do texto.
- A imagem social é a imagem destacada; sem ela, o site usa o ícone/logo ou o
  primeiro banner.

## 12. O que não muda pelo painel

- Estrutura e desenho das seções.
- Tamanhos e cortes das imagens.
- Cores, fontes e componentes.
- Criação de novos tipos de conteúdo (isso é código).

## 13. Problemas comuns

| Situação | O que fazer |
|---|---|
| Conteúdo não aparece | Confira se está **publicado**, não em rascunho, e a **Ordem**. |
| Imagem antiga continua | Limpe o cache do site/CDN e recarregue. |
| Área ou sócio dá 404 | Administrador: Configurações → Links permanentes → Salvar. |
| Formulário não chega | Verificar spam, destinatário e configuração de e-mail/SMTP. |
| Descrição errada no Google | Ajustar a caixa SEO ou o resumo; aguardar recaptura. |

Dúvidas de layout, novos módulos ou mudanças de identidade: falar com o
responsável técnico.
