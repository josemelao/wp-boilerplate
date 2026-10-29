<?php
/**
 * Verificações da F1 — Fundação.
 *
 * Executado por tools\test.ps1 (wp eval-file). Cada bloco imprime PASS/FAIL e
 * o processo termina com erro se algo falhar.
 *
 * @package Tema_Base
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 1 );
}

$GLOBALS['tb_test_failures'] = array();
$GLOBALS['tb_test_passes']   = 0;

// Estado do banco que o teste não pode destruir (restaurado no final).
$GLOBALS['tb_saved_options']      = get_option( 'tb_options', null );
$GLOBALS['tb_saved_front']        = get_option( 'show_on_front' );
$GLOBALS['tb_saved_page_on_front'] = get_option( 'page_on_front' );
$GLOBALS['tb_saved_page_for_posts'] = get_option( 'page_for_posts' );
$GLOBALS['tb_saved_sticky']       = get_option( 'sticky_posts' );

/**
 * Registra uma verificação.
 *
 * wp eval-file executa o arquivo dentro de uma função, então o estado
 * compartilhado fica em $GLOBALS.
 *
 * @param bool   $condition Resultado.
 * @param string $label     Descrição.
 * @param string $detail    Detalhe em caso de falha.
 * @return void
 */
function tb_test_check( $condition, $label, $detail = '' ) {
	if ( $condition ) {
		$GLOBALS['tb_test_passes']++;
		WP_CLI::log( 'PASS  ' . $label );
		return;
	}

	$GLOBALS['tb_test_failures'][] = $label . ( '' !== $detail ? ' (' . $detail . ')' : '' );
	WP_CLI::log( 'FAIL  ' . $label . ( '' !== $detail ? ' (' . $detail . ')' : '' ) );
}

/* -------------------------------------------------------------------------
 * 1. Funções públicas do tema
 * ---------------------------------------------------------------------- */

$tb_functions = array(
	'tb_opt',
	'tb_options_defaults',
	'tb_sanitize_options',
	'tb_get_social_links',
	'tb_get_offices',
	'tb_blog_url',
	'tb_query',
	'tb_featured_posts',
	'tb_save_meta',
	'tb_admin_default_order',
	'tb_reading_notice_needed',
);

WP_CLI::log( '' );
WP_CLI::log( '== Funções do tema' );

foreach ( $tb_functions as $tb_fn ) {
	tb_test_check( function_exists( $tb_fn ), 'função ' . $tb_fn . '() existe' );
}

/* -------------------------------------------------------------------------
 * 2. Custom Post Types
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Custom Post Types' );

$tb_cpts = array(
	'banner'     => array( 'public' => false, 'thumbnail' => true, 'archive' => false ),
	'destaque'   => array( 'public' => false, 'thumbnail' => true, 'archive' => false ),
	'depoimento' => array( 'public' => false, 'thumbnail' => false, 'archive' => false ),
	'historia'   => array( 'public' => false, 'thumbnail' => true, 'archive' => false ),
	'socio'      => array( 'public' => true, 'thumbnail' => true, 'archive' => true ),
);

foreach ( $tb_cpts as $tb_slug => $tb_expected ) {
	$tb_obj = get_post_type_object( $tb_slug );

	tb_test_check( null !== $tb_obj, 'CPT ' . $tb_slug . ' registrado' );

	if ( null === $tb_obj ) {
		continue;
	}

	tb_test_check( (bool) $tb_obj->public === $tb_expected['public'], 'CPT ' . $tb_slug . ' public correto', 'public=' . var_export( $tb_obj->public, true ) );
	tb_test_check( post_type_supports( $tb_slug, 'title' ), 'CPT ' . $tb_slug . ' suporta título' );
	tb_test_check( post_type_supports( $tb_slug, 'page-attributes' ), 'CPT ' . $tb_slug . ' suporta ordem (menu_order)' );
	tb_test_check( (bool) $tb_obj->show_in_rest, 'CPT ' . $tb_slug . ' show_in_rest' );
	tb_test_check( (bool) $tb_obj->has_archive === $tb_expected['archive'], 'CPT ' . $tb_slug . ' has_archive correto' );
	tb_test_check( post_type_supports( $tb_slug, 'thumbnail' ) === $tb_expected['thumbnail'], 'CPT ' . $tb_slug . ' thumbnail correto' );
	tb_test_check( '' !== (string) $tb_obj->menu_icon, 'CPT ' . $tb_slug . ' tem ícone próprio' );
}

$tb_socio = get_post_type_object( 'socio' );
tb_test_check( $tb_socio && 'socios' === $tb_socio->rewrite['slug'], 'CPT socio usa /socios/' );
tb_test_check( post_type_supports( 'socio', 'editor' ), 'CPT socio suporta biografia (editor)' );

$tb_historia = get_post_type_object( 'historia' );
tb_test_check( $tb_historia && 'Todas as Histórias' === $tb_historia->labels->all_items, 'rótulo de História no feminino (all_items)' );
tb_test_check( $tb_historia && 'Nova História' === $tb_historia->labels->new_item, 'rótulo de História no feminino (new_item)' );
tb_test_check( $tb_historia && 'Nenhuma História encontrada.' === $tb_historia->labels->not_found, 'rótulo de História no feminino (not_found)' );

/* -------------------------------------------------------------------------
 * 3. Metas registradas para REST
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Metas (REST)' );

$tb_meta_map = array(
	'banner'     => array( '_tb_label', '_tb_text', '_tb_link', '_tb_link_label' ),
	'destaque'   => array( '_tb_label', '_tb_link', '_tb_link_label' ),
	'depoimento' => array( '_tb_quote', '_tb_source' ),
	'historia'   => array( '_tb_summary', '_tb_link' ),
	'socio'      => array( '_tb_role', '_tb_email', '_tb_phone', '_tb_linkedin' ),
);

foreach ( $tb_meta_map as $tb_type => $tb_keys ) {
	$tb_registered = get_registered_meta_keys( 'post', $tb_type );

	foreach ( $tb_keys as $tb_key ) {
		$tb_ok = isset( $tb_registered[ $tb_key ] )
			&& ! empty( $tb_registered[ $tb_key ]['show_in_rest'] )
			&& ! empty( $tb_registered[ $tb_key ]['auth_callback'] );

		tb_test_check( $tb_ok, $tb_type . ' → ' . $tb_key . ' registrada em REST com auth_callback' );
	}
}

// O auth_callback não pode liberar visitante anônimo.
$tb_banner_meta = get_registered_meta_keys( 'post', 'banner' );
if ( isset( $tb_banner_meta['_tb_label']['auth_callback'] ) ) {
	wp_set_current_user( 0 );
	$tb_allowed = call_user_func( $tb_banner_meta['_tb_label']['auth_callback'], false, '_tb_label', 0 );
	tb_test_check( false === (bool) $tb_allowed, 'auth_callback nega visitante anônimo' );
}

/* -------------------------------------------------------------------------
 * 4. Tamanhos de imagem
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Tamanhos de imagem' );

$tb_sizes = wp_get_registered_image_subsizes();
$tb_wanted = array(
	'tb-hero'  => array( 1920, 800 ),
	'tb-slide' => array( 1280, 640 ),
	'tb-story' => array( 1600, 900 ),
	'tb-socio' => array( 640, 800 ),
	'tb-card'  => array( 720, 480 ),
);

foreach ( $tb_wanted as $tb_name => $tb_dim ) {
	$tb_ok = isset( $tb_sizes[ $tb_name ] )
		&& (int) $tb_sizes[ $tb_name ]['width'] === $tb_dim[0]
		&& (int) $tb_sizes[ $tb_name ]['height'] === $tb_dim[1];

	tb_test_check( $tb_ok, 'tamanho ' . $tb_name . ' = ' . $tb_dim[0] . 'x' . $tb_dim[1] );
}

/* -------------------------------------------------------------------------
 * 5. Menus
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Menus' );

$tb_menus = get_registered_nav_menus();
tb_test_check( isset( $tb_menus['primary'] ), 'menu primary registrado' );
tb_test_check( isset( $tb_menus['footer'] ), 'menu footer registrado' );

/* -------------------------------------------------------------------------
 * 6. Opções do Site
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Opções do Site' );

$tb_defaults = function_exists( 'tb_options_defaults' ) ? tb_options_defaults() : array();
$tb_expected_keys = array(
	'insights_heading',
	'stories_heading',
	'social_linkedin',
	'social_instagram',
	'social_x',
	'social_facebook',
	'social_youtube',
	'offices',
	'subscribe_label',
	'subscribe_url',
	'footer_note',
);

foreach ( $tb_expected_keys as $tb_key ) {
	tb_test_check( isset( $tb_defaults[ $tb_key ] ), 'default de ' . $tb_key . ' definido' );
}

if ( function_exists( 'tb_opt' ) ) {
	update_option( 'tb_options', array( 'insights_heading' => 'Artigos' ) );
	tb_test_check( 'Artigos' === tb_opt( 'insights_heading' ), 'tb_opt lê valor salvo' );

	// Opção salva e vazia deve continuar vazia (o cliente apagou o texto).
	update_option( 'tb_options', array( 'footer_note' => '' ) );
	tb_test_check( '' === tb_opt( 'footer_note' ), 'valor vazio salvo continua vazio' );

	// Sem opção salva, vale o default do tema.
	delete_option( 'tb_options' );
	tb_test_check( '' !== tb_opt( 'footer_note' ), 'sem opção salva, tb_opt usa o default' );
	tb_test_check( false === tb_opt( 'inexistente', false ), 'tb_opt devolve default para chave inexistente' );
}

if ( function_exists( 'tb_register_options' ) ) {
	// admin_init não roda no CLI; registra manualmente antes de inspecionar.
	tb_register_options();

	$tb_settings = get_registered_settings();
	tb_test_check( isset( $tb_settings['tb_options'] ), 'opção tb_options registrada' );
	tb_test_check(
		isset( $tb_settings['tb_options']['sanitize_callback'] ) && 'tb_sanitize_options' === $tb_settings['tb_options']['sanitize_callback'],
		'register_setting tem sanitize_callback'
	);
}

$tb_editor = get_role( 'editor' );
tb_test_check( $tb_editor && $tb_editor->has_cap( 'edit_pages' ), 'papel Editor tem edit_pages' );
tb_test_check( $tb_editor && ! $tb_editor->has_cap( 'manage_options' ), 'papel Editor não tem manage_options' );

if ( function_exists( 'tb_options_capability' ) ) {
	tb_test_check( 'edit_pages' === tb_options_capability(), 'página de opções usa capability edit_pages' );
	tb_test_check(
		false !== has_filter( 'option_page_capability_tb_options_group', 'tb_options_capability' ),
		'Editor pode SALVAR as opções (options.php)'
	);
}

if ( function_exists( 'tb_sanitize_options' ) ) {
	$tb_dirty = array(
		'insights_heading' => '<script>alert(1)</script>Artigos',
		'social_linkedin'  => 'javascript:alert(1)',
		'social_x'         => 'https://x.com/exemplo',
		'offices'          => "Sao Paulo\nRio de Janeiro<script>x</script>",
		'invasor'          => 'nao deveria existir',
	);
	$tb_clean = tb_sanitize_options( $tb_dirty );

	tb_test_check( false === strpos( $tb_clean['insights_heading'], '<script>' ), 'sanitize remove tags do título' );
	tb_test_check( '' === $tb_clean['social_linkedin'], 'sanitize rejeita javascript:' );
	tb_test_check( 'https://x.com/exemplo' === $tb_clean['social_x'], 'sanitize mantém URL válida' );
	tb_test_check( false === strpos( $tb_clean['offices'], '<script>' ), 'sanitize limpa escritórios' );
	tb_test_check( ! isset( $tb_clean['invasor'] ), 'sanitize descarta chave desconhecida' );
	tb_test_check( count( $tb_clean ) === count( $tb_defaults ), 'sanitize mantém exatamente as chaves previstas' );
}

$tb_options = get_option( 'tb_options', array() );
update_option( 'tb_options', $tb_options );
delete_option( 'tb_options' );

/* -------------------------------------------------------------------------
 * 7. Salvamento de meta
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Salvamento de meta' );

wp_set_current_user( 1 );

$tb_post_id = wp_insert_post(
	array(
		'post_type'   => 'banner',
		'post_title'  => 'Banner de teste F1',
		'post_status' => 'draft',
	)
);

tb_test_check( $tb_post_id > 0, 'post de teste criado' );
tb_test_check( has_action( 'save_post', 'tb_save_meta' ), 'save_post ligado a tb_save_meta' );

if ( $tb_post_id > 0 && function_exists( 'tb_render_meta_box' ) ) {
	ob_start();
	tb_render_meta_box( get_post( $tb_post_id ) );
	$tb_box = ob_get_clean();

	tb_test_check( false !== strpos( $tb_box, 'name="_tb_label"' ), 'meta box renderiza os campos' );
	tb_test_check( false !== strpos( $tb_box, 'name="tb_meta_nonce"' ), 'meta box imprime nonce' );
}

if ( $tb_post_id > 0 && function_exists( 'tb_save_meta' ) ) {
	$_POST = array(
		'tb_meta_nonce'  => wp_create_nonce( 'tb_save_meta' ),
		'_tb_label'      => '  <b>Rótulo</b> com tags  ',
		'_tb_text'       => "Texto <script>alert(1)</script>\nsegunda linha",
		'_tb_link'       => 'javascript:alert(1)',
		'_tb_link_label' => 'Conheça',
	);

	tb_save_meta( $tb_post_id );

	tb_test_check( '<b>' !== get_post_meta( $tb_post_id, '_tb_label', true ), 'label salvo sem tags' );
	tb_test_check( 'Rótulo com tags' === get_post_meta( $tb_post_id, '_tb_label', true ), 'label salvo com trim', var_export( get_post_meta( $tb_post_id, '_tb_label', true ), true ) );
	tb_test_check( false === strpos( get_post_meta( $tb_post_id, '_tb_text', true ), '<script>' ), 'texto salvo sem script' );
	tb_test_check( '' === get_post_meta( $tb_post_id, '_tb_link', true ), 'URL javascript: descartada' );
	tb_test_check( 'Conheça' === get_post_meta( $tb_post_id, '_tb_link_label', true ), 'link_label salvo' );

	// O mesmo callback deve liberar quem pode editar o post.
	if ( isset( $tb_banner_meta['_tb_label']['auth_callback'] ) ) {
		$tb_allowed_post = call_user_func( $tb_banner_meta['_tb_label']['auth_callback'], false, '_tb_label', $tb_post_id );
		tb_test_check( true === (bool) $tb_allowed_post, 'auth_callback libera admin no próprio post' );
	}

	// Nonce inválido não altera nada.
	$_POST['tb_meta_nonce'] = 'invalido';
	$_POST['_tb_link_label'] = 'Nao deveria entrar';
	tb_save_meta( $tb_post_id );
	tb_test_check( 'Conheça' === get_post_meta( $tb_post_id, '_tb_link_label', true ), 'nonce inválido não grava' );

	// Usuário sem permissão não altera nada.
	$_POST['tb_meta_nonce'] = wp_create_nonce( 'tb_save_meta' );
	wp_set_current_user( 0 );
	tb_save_meta( $tb_post_id );
	tb_test_check( 'Conheça' === get_post_meta( $tb_post_id, '_tb_link_label', true ), 'sem capability não grava' );

	wp_set_current_user( 1 );
	wp_delete_post( $tb_post_id, true );
	$_POST = array();
}

/* -------------------------------------------------------------------------
 * 8. Admin UX
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Admin UX' );

$tb_columns = apply_filters(
	'manage_banner_posts_columns',
	array(
		'cb'    => 'cb',
		'title' => 'Título',
		'date'  => 'Data',
	)
);

tb_test_check( isset( $tb_columns['tb_image'] ), 'coluna Imagem em banner' );
tb_test_check( isset( $tb_columns['tb_order'] ), 'coluna Ordem em banner' );

$tb_columns_dep = apply_filters( 'manage_depoimento_posts_columns', array( 'title' => 'Título' ) );
tb_test_check( ! isset( $tb_columns_dep['tb_image'] ), 'depoimento não mostra coluna Imagem (sem thumbnail)' );
tb_test_check( isset( $tb_columns_dep['tb_order'] ), 'depoimento mostra coluna Ordem' );
tb_test_check( has_action( 'admin_menu', 'tb_add_options_page' ), 'página de opções registrada no menu' );

if ( function_exists( 'tb_image_size_tips' ) ) {
	$tb_tips = tb_image_size_tips();
	tb_test_check( isset( $tb_tips['post'] ) && 720 === $tb_tips['post'][0] && 480 === $tb_tips['post'][1], 'dica de imagem para posts (tb-card)' );
}
tb_test_check( has_action( 'pre_get_posts', 'tb_admin_default_order' ), 'ordem padrão no admin registrada' );
tb_test_check( has_action( 'admin_post_thumbnail_html', 'tb_thumbnail_tip' ), 'dica de tamanho da imagem registrada' );
tb_test_check( has_action( 'admin_notices', 'tb_reading_notice' ), 'aviso de Leitura registrado' );

if ( function_exists( 'tb_reading_notice_needed' ) ) {
	update_option( 'show_on_front', 'posts' );
	tb_test_check( true === tb_reading_notice_needed(), 'aviso aparece sem página inicial estática' );

	// show_on_front=page, mas sem page_on_front (home apagada).
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', 0 );
	update_option( 'page_for_posts', 123 );
	tb_test_check( true === tb_reading_notice_needed(), 'aviso aparece sem página inicial definida' );

	// Configuração completa: sem aviso.
	update_option( 'page_on_front', 123 );
	update_option( 'page_for_posts', 456 );
	tb_test_check( false === tb_reading_notice_needed(), 'aviso some com tudo configurado' );

	update_option( 'show_on_front', $GLOBALS['tb_saved_front'] );
	update_option( 'page_on_front', $GLOBALS['tb_saved_page_on_front'] );
	update_option( 'page_for_posts', $GLOBALS['tb_saved_page_for_posts'] );
}

/* -------------------------------------------------------------------------
 * 9. Helpers de conteúdo
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Helpers' );

if ( function_exists( 'tb_blog_url' ) ) {
	$tb_page_id = wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_title'  => 'Blog F1',
			'post_status' => 'publish',
		)
	);
	$tb_saved_posts_page = get_option( 'page_for_posts' );
	update_option( 'page_for_posts', $tb_page_id );

	tb_test_check( get_permalink( $tb_page_id ) === tb_blog_url(), 'tb_blog_url usa a página de posts' );

	update_option( 'page_for_posts', $tb_saved_posts_page );
	wp_delete_post( $tb_page_id, true );
}

if ( function_exists( 'tb_query' ) ) {
	$tb_q = tb_query( 'socio' );
	tb_test_check( $tb_q instanceof WP_Query, 'tb_query devolve WP_Query' );
	tb_test_check( 'menu_order' === $tb_q->query_vars['orderby'], 'tb_query ordena por menu_order' );
	tb_test_check( true === (bool) $tb_q->query_vars['no_found_rows'], 'tb_query usa no_found_rows' );
	tb_test_check( 'publish' === $tb_q->query_vars['post_status'], 'tb_query só publicados' );
}

if ( function_exists( 'tb_featured_posts' ) ) {
	$tb_post_ids = array();

	for ( $tb_i = 1; $tb_i <= 4; $tb_i++ ) {
		$tb_post_ids[] = wp_insert_post(
			array(
				'post_type'   => 'post',
				'post_title'  => 'Post de teste F1 ' . $tb_i,
				'post_status' => 'publish',
			)
		);
	}

	stick_post( $tb_post_ids[0] );

	$tb_feat = tb_featured_posts( 3 );
	tb_test_check( 3 === count( $tb_feat ), 'tb_featured_posts devolve exatamente 3', 'count=' . count( $tb_feat ) );
	tb_test_check( ! empty( $tb_feat ) && (int) $tb_feat[0]->ID === (int) $tb_post_ids[0], 'post fixado vem primeiro' );

	unstick_post( $tb_post_ids[0] );
	update_option( 'sticky_posts', $GLOBALS['tb_saved_sticky'] );

	foreach ( $tb_post_ids as $tb_cleanup_id ) {
		wp_delete_post( $tb_cleanup_id, true );
	}
}

/* -------------------------------------------------------------------------
 * 10. Header / footer
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Header e footer' );

if ( function_exists( 'tb_get_social_links' ) ) {
	update_option(
		'tb_options',
		array_merge(
			tb_options_defaults(),
			array(
				'social_linkedin' => 'https://www.linkedin.com/company/exemplo',
				'social_x'        => '',
				'offices'         => "Sao Paulo\nRio de Janeiro",
			)
		)
	);

	$tb_links = tb_get_social_links();
	tb_test_check( isset( $tb_links['linkedin'] ), 'rede preenchida aparece' );
	tb_test_check( ! isset( $tb_links['x'] ), 'rede vazia não aparece' );
	tb_test_check( 'https://www.linkedin.com/company/exemplo' === $tb_links['linkedin']['url'], 'URL da rede correta' );

	$tb_offices = tb_get_offices();
	tb_test_check( is_array( $tb_offices ), 'tb_get_offices devolve array' );
}

if ( function_exists( 'get_header' ) ) {
	// O CLI não define SERVER_NAME; wp_resource_hints() avisa sem ele.
	if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
		$_SERVER['SERVER_NAME'] = '127.0.0.1';
	}

	ob_start();
	get_header();
	$tb_header = ob_get_clean();

	tb_test_check( false !== strpos( $tb_header, 'skip-link' ), 'header tem link pular para o conteúdo' );
	tb_test_check( false !== strpos( $tb_header, 'site-header' ), 'header tem cabeçalho do site' );
	tb_test_check( false !== strpos( $tb_header, '<nav' ) && false !== strpos( $tb_header, 'id="menu-principal"' ), 'header imprime nav do menu principal' );
	tb_test_check( false !== strpos( $tb_header, '<html' ), 'header abre o documento' );
	tb_test_check( false !== strpos( $tb_header, 'no-js' ), 'header marca html sem JS (fallback do menu)' );
	tb_test_check( false !== strpos( $tb_header, 'tabindex="-1"' ), 'main é focável (skip link funciona)' );
}

if ( function_exists( 'get_footer' ) ) {
	ob_start();
	get_footer();
	$tb_footer = ob_get_clean();

	tb_test_check( false !== strpos( $tb_footer, 'site-footer' ), 'footer tem rodapé do site' );
	tb_test_check( false !== strpos( $tb_footer, '</html>' ), 'footer fecha o documento' );
	tb_test_check( false !== strpos( $tb_footer, 'linkedin' ), 'footer imprime rede preenchida' );
	tb_test_check( false !== strpos( $tb_footer, 'Escrit' ) || false !== strpos( $tb_footer, 'escrit' ), 'footer imprime escritórios' );
}

/* -------------------------------------------------------------------------
 * 11. Requisitos do tema declarados
 * ---------------------------------------------------------------------- */

WP_CLI::log( '' );
WP_CLI::log( '== Requisitos' );

$tb_style = file_get_contents( TB_DIR . '/style.css' );
tb_test_check( false !== strpos( $tb_style, 'Requires at least: 6.3' ), 'tema declara requisito 6.3 (strategy/defer)' );
tb_test_check( false !== strpos( $tb_style, 'Requires PHP: 7.4' ), 'tema declara PHP 7.4' );

/* -------------------------------------------------------------------------
 * Resultado
 * ---------------------------------------------------------------------- */

// Restaura o banco (o teste não pode apagar a configuração do site).
if ( null === $GLOBALS['tb_saved_options'] ) {
	delete_option( 'tb_options' );
} else {
	update_option( 'tb_options', $GLOBALS['tb_saved_options'] );
}

WP_CLI::log( '' );

if ( $GLOBALS['tb_test_failures'] ) {
	WP_CLI::log( sprintf( '%d falha(s), %d passou(aram).', count( $GLOBALS['tb_test_failures'] ), $GLOBALS['tb_test_passes'] ) );
	foreach ( $GLOBALS['tb_test_failures'] as $tb_failure ) {
		WP_CLI::log( '  - ' . $tb_failure );
	}
	WP_CLI::error( 'F1: verificações falharam.' );
}

WP_CLI::success( sprintf( 'F1: %d verificações passaram.', $GLOBALS['tb_test_passes'] ) );
