<?php
/**
 * Manual rápido do cliente no painel.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra o manual como submenu de "Opções do Site".
 *
 * @return void
 */
function tb_add_manual_page() {
	add_submenu_page(
		'tb-options',
		__( 'Manual rápido do site', 'tema-base' ),
		__( 'Manual do cliente', 'tema-base' ),
		tb_options_capability(),
		'tb-manual',
		'tb_render_manual_page'
	);
}
add_action( 'admin_menu', 'tb_add_manual_page' );

/**
 * Carrega o CSS do manual apenas na página dele.
 *
 * @param string $hook Hook da página atual.
 * @return void
 */
function tb_manual_assets( $hook ) {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	if ( 'tb-options_page_tb-manual' !== $hook && 'tb-manual' !== $page ) {
		return;
	}

	$path = TB_DIR . '/assets/css/admin-manual.css';

	wp_enqueue_style(
		'tb-admin-manual',
		TB_URI . '/assets/css/admin-manual.css',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : TB_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'tb_manual_assets' );

/**
 * Renderiza o manual rápido.
 *
 * @return void
 */
function tb_render_manual_page() {
	if ( ! current_user_can( tb_options_capability() ) ) {
		wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'tema-base' ) );
	}

	$tb_manual_images = TB_URI . '/assets/manual';
	?>
	<div class="wrap tb-manual">
		<h1><?php esc_html_e( 'Manual rápido do site', 'tema-base' ); ?></h1>
		<p class="tb-manual__lead">
			<?php esc_html_e( 'O layout é fixo. Você edita conteúdo, imagens, ordem e os textos das Opções do Site. Para menus, plugins, usuários ou identidade visual, fale com o administrador.', 'tema-base' ); ?>
		</p>

		<nav class="tb-manual__nav" aria-label="<?php esc_attr_e( 'Atalhos do manual', 'tema-base' ); ?>">
			<a href="#artigo"><?php esc_html_e( 'Publicar artigo', 'tema-base' ); ?></a>
			<a href="#destaque"><?php esc_html_e( 'Destacar na home', 'tema-base' ); ?></a>
			<a href="#imagem"><?php esc_html_e( 'Imagem destacada', 'tema-base' ); ?></a>
			<a href="#ordem"><?php esc_html_e( 'Ordem e rascunho', 'tema-base' ); ?></a>
			<a href="#opcoes"><?php esc_html_e( 'Opções do Site', 'tema-base' ); ?></a>
			<a href="#problemas"><?php esc_html_e( 'Problemas comuns', 'tema-base' ); ?></a>
		</nav>

		<section id="artigo" class="tb-manual__card">
			<h2><?php esc_html_e( '1. Publicar um artigo', 'tema-base' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Abra Posts → Adicionar novo.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Escreva o título e o texto no editor.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Preencha o Resumo: ele aparece nos cards, na busca e na descrição SEO.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Defina a Imagem destacada. Sem ela, o site usa o placeholder institucional.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Escolha a Categoria e clique em Publicar.', 'tema-base' ); ?></li>
			</ol>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Criar artigo', 'tema-base' ); ?></a>
			</p>
		</section>

		<section id="destaque" class="tb-manual__card">
			<h2><?php esc_html_e( '2. Artigos em destaque na home', 'tema-base' ); ?></h2>
			<p>
				<?php esc_html_e( 'A home mostra até três artigos: primeiro os fixados, do mais recente para o mais antigo; se faltar, completa com os posts mais recentes.', 'tema-base' ); ?>
			</p>
			<ol>
				<li><?php esc_html_e( 'Abra o post e, no painel lateral direito, escolha a aba Post (não “Bloco”).', 'tema-base' ); ?></li>
				<li>
					<?php esc_html_e( 'No bloco Status, clique no botão Publicado — o tooltip é “Mudar status: Publicado”.', 'tema-base' ); ?>
					<figure>
						<img src="<?php echo esc_url( $tb_manual_images . '/01-status.png' ); ?>" alt="<?php esc_attr_e( 'Botão Publicado no bloco Status do editor', 'tema-base' ); ?>" loading="lazy">
						<figcaption><?php esc_html_e( 'O botão de status fica no bloco Status, no painel lateral.', 'tema-base' ); ?></figcaption>
					</figure>
				</li>
				<li>
					<?php esc_html_e( 'No painel Status e visibilidade, marque “Fixo” — o texto de ajuda abaixo é “Fixar este post no topo do blog”.', 'tema-base' ); ?>
					<figure>
						<img src="<?php echo esc_url( $tb_manual_images . '/02-fixar.png' ); ?>" alt="<?php esc_attr_e( 'Opção Fixo no painel de status, com o texto Fixar este post no topo do blog', 'tema-base' ); ?>" loading="lazy">
						<figcaption><?php esc_html_e( 'A caixa se chama “Fixo”; o texto de ajuda explica que ela fixa o post no topo do blog.', 'tema-base' ); ?></figcaption>
					</figure>
				</li>
				<li><?php esc_html_e( 'Clique em Atualizar para salvar.', 'tema-base' ); ?></li>
			</ol>
			<p class="tb-manual__note">
				<?php esc_html_e( 'Na lista de posts, o caminho curto é Edição rápida → Fixar no topo. É a mesma configuração.', 'tema-base' ); ?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'Abrir Posts', 'tema-base' ); ?></a>
			</p>
		</section>

		<section id="imagem" class="tb-manual__card">
			<h2><?php esc_html_e( '3. Imagem destacada', 'tema-base' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Artigo, área e escritório: 720 × 480 px (3:2).', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Sócio: 640 × 800 px (4:5).', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'História: 1600 × 900 px (16:9).', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Banner: 1920 × 800 px. Destaque: 1280 × 640 px.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'O tema gera WebP automaticamente; não é preciso converter.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Preencha o Texto alternativo em Mídia → Biblioteca para melhorar acessibilidade e SEO.', 'tema-base' ); ?></li>
			</ul>
			<figure>
				<img src="<?php echo esc_url( $tb_manual_images . '/03-imagem.png' ); ?>" alt="<?php esc_attr_e( 'Seção de artigos da home com um card usando o placeholder institucional', 'tema-base' ); ?>" loading="lazy">
				<figcaption><?php esc_html_e( 'Sem imagem, o card do meio usa o placeholder institucional; os demais mantêm a foto.', 'tema-base' ); ?></figcaption>
			</figure>
		</section>

		<section id="ordem" class="tb-manual__card">
			<h2><?php esc_html_e( '4. Ordem e rascunho', 'tema-base' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'A Ordem fica em Atributos → Ordem, no painel lateral. O menor número aparece primeiro.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Cada listagem mostra a dica de imagem e a regra de ordem no topo.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Rascunho não aparece no site. Não existe campo “ativo”: publique para exibir.', 'tema-base' ); ?></li>
			</ul>
		</section>

		<section id="opcoes" class="tb-manual__card">
			<h2><?php esc_html_e( '5. Opções do Site', 'tema-base' ); ?></h2>
			<p><?php esc_html_e( 'Em Opções do Site você ajusta os textos e links globais, sem mexer no layout:', 'tema-base' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'títulos das seções da home;', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'redes sociais (só aparecem as preenchidas);', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'lista de escritórios, uma cidade por linha;', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'texto e URL de inscrição/newsletter;', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'nota do rodapé e descrição da página inicial.', 'tema-base' ); ?></li>
			</ul>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=tb-options' ) ); ?>"><?php esc_html_e( 'Abrir Opções do Site', 'tema-base' ); ?></a>
			</p>
		</section>

		<section id="problemas" class="tb-manual__card">
			<h2><?php esc_html_e( '6. Problemas comuns', 'tema-base' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Conteúdo não aparece: confira se está publicado e qual é a Ordem.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Imagem antiga continua: limpe o cache do site/CDN e recarregue.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Área ou sócio dá 404: o administrador precisa salvar Configurações → Links permanentes.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Login não entra: confira usuário/senha e se o link é o atual.', 'tema-base' ); ?></li>
				<li><?php esc_html_e( 'Card sem imagem: o placeholder aparece automaticamente; o ideal é definir a imagem destacada.', 'tema-base' ); ?></li>
			</ul>
		</section>

		<p class="tb-manual__footer">
			<?php
			printf(
				/* translators: %s: data da última atualização */
				esc_html__( 'Manual rápido — atualizado em %s. A versão completa será entregue na publicação.', 'tema-base' ),
				'04/11/2026'
			);
			?>
		</p>
	</div>
	<?php
}
