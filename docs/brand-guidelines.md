# Diretrizes de marca — JT

> Identidade temporária criada em 30/10/2026 para a primeira passada visual da F5. O nome “JT” é um wordmark provisório; substitua pelo logo e pelo nome definitivos quando estiverem disponíveis.

## Direção

Institucional, editorial e sóbrio. A referência estrutural é `assets-full-page.png`, usada apenas para hierarquia, ritmo, proporção e contraste — não para copiar marca, textos, imagens ou CSS.

- Sans-led: títulos, navegação e interface em sans.
- Serifada apenas em citações/depoimentos.
- Cantos retos, linhas finas, muito espaço branco.
- Fotografia institucional com overlays escuros e recortes editoriais.
- Um acento forte: vinho/vermelho para o item ativo das Histórias.

## Paleta

| Token | Valor | Uso |
|---|---|---|
| `--tb-color-ink` | `#121417` | Texto principal |
| `--tb-color-ink-soft` | `#4e545b` | Texto secundário |
| `--tb-color-paper` | `#ffffff` | Fundo principal |
| `--tb-color-paper-soft` | `#f3f1ec` | Fundos alternativos, rodapé, depoimentos |
| `--tb-color-line` | `#d8d4cc` | Bordas e divisores |
| `--tb-color-accent` | `#16324a` | Azul institucional, botões, links |
| `--tb-color-accent-contrast` | `#ffffff` | Texto sobre o azul |
| `--tb-color-highlight` | `#a51c30` | Destaque ativo das Histórias |
| `--tb-color-focus` | `#1a5fb4` | Foco visível |

## Tipografia

| Papel | Família | Pesos |
|---|---|---|
| Títulos e navegação | Libre Franklin | 400–700 |
| Corpo e interface | Source Sans 3 | 400–600 |
| Citações e depoimentos | Source Serif 4 | 400 normal/itálico |

As fontes são self-hosted em `tema-base/assets/fonts/`, com `font-display: swap`. As licenças OFL estão na mesma pasta.

## Wordmark

Enquanto não houver logo:

- usar “JT” em Libre Franklin, caixa alta, tracking amplo;
- o fallback do tema usa o título do site (`bloginfo('name')`);
- quando o logo existir, usar o suporte nativo de logo do WordPress (Aparência → Personalizar → Identidade do site).

## Imagens

- Hero: arquitetura, cidade ou fachada com área escura/livre para texto.
- Destaques: escritório, biblioteca, arquitetura e detalhes institucionais.
- Histórias: imagens editoriais exibidas em preto e branco via CSS.
- Artigos: cidade, documentos, livros e detalhes arquitetônicos.
- Sócios: evitar retratos de banco de imagens como se fossem a equipe real; usar placeholder neutro até haver fotos próprias.

As imagens demo atuais e seus créditos estão em `tools/demo-images/CREDITS.md`. São temporárias e devem ser substituídas antes da publicação.

## Componentes

- Botões: caixa alta, `0.6875rem`, tracking `0.14em`, borda de 1 px, cantos retos.
- Navegação: caixa alta, tracking `0.14em`.
- Seções: respiro vertical de `3–5.5rem`, títulos com `letter-spacing` negativo leve. Alternar superfícies: `paper`, `paper-soft` e o painel azul; separar faixas com linhas de 1 px.
- Cards de artigo: imagem 3:2, sem borda superior, data/categoria em caixa alta.
- Áreas na home: faixa `paper-soft` com cards brancos e borda fina.
- Depoimentos: faixa `--tb-color-accent` de ponta a ponta, com painel interno na mesma cor, citação em serifada itálica, aspas decorativas e controles em branco. A troca é por slide direcional, não fade.
- Histórias: fundo escuro, títulos em caixa alta, ativo em `--tb-color-highlight`; a imagem de fundo começa a trocar no clique e faz crossfade em 320 ms.
- Rodapé: fundo `--tb-color-paper-soft`, links em caixa alta.
- Controles de rotação: ícone de pause enquanto roda e de play quando pausado, alvo mínimo de 44 × 44 px, `aria-label` e `aria-pressed` atualizados.
- Barra de progresso do hero: 2 px na base, cor `--tb-color-highlight`, preenche no intervalo do carrossel e pausa/retoma junto com o timer.

## Acessibilidade

- Manter contraste mínimo AA em texto sobre imagem usando o scrim.
- Foco visível em todos os controles.
- Não depender só de cor para indicar o item ativo.
- Respeitar `prefers-reduced-motion`.
- Área clicável mínima de 44 × 44 px nos controles principais.
