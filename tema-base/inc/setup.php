<?php
/**
 * Configuração do tema: supports, menus, tamanhos de imagem e assets.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuração executada em after_setup_theme.
 *
 * @return void
 */
function tb_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'tema-base' ),
			'footer'  => __( 'Menu do rodapé', 'tema-base' ),
		)
	);

	add_image_size( 'tb-hero', 1920, 800, true );
	add_image_size( 'tb-slide', 1280, 640, true );
	add_image_size( 'tb-story', 1600, 900, true );
	add_image_size( 'tb-socio', 640, 800, true );
	add_image_size( 'tb-card', 720, 480, true );
	set_post_thumbnail_size( 720, 480, true );
}
add_action( 'after_setup_theme', 'tb_setup' );

/**
 * Carrega CSS e JS do tema, com versão por filemtime.
 *
 * @return void
 */
function tb_enqueue_assets() {
	$css_path    = TB_DIR . '/assets/css/main.css';
	$js_path     = TB_DIR . '/assets/js/main.js';
	$target_path = TB_DIR . '/assets/js/carousel-target.js';

	wp_enqueue_style(
		'tb-main',
		TB_URI . '/assets/css/main.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : TB_VERSION
	);

	wp_enqueue_script(
		'tb-carousel-target',
		TB_URI . '/assets/js/carousel-target.js',
		array(),
		file_exists( $target_path ) ? (string) filemtime( $target_path ) : TB_VERSION,
		true
	);

	wp_enqueue_script(
		'tb-main',
		TB_URI . '/assets/js/main.js',
		array( 'tb-carousel-target' ),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : TB_VERSION,
		true
	);

	wp_script_add_data( 'tb-carousel-target', 'strategy', 'defer' );
	wp_script_add_data( 'tb-main', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'tb_enqueue_assets' );

/**
 * Restringe a busca pública a artigos.
 *
 * @param WP_Query $query Consulta.
 * @return void
 */
function tb_search_only_posts( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', 'post' );
	}
}
add_action( 'pre_get_posts', 'tb_search_only_posts' );

/**
 * Fecha comentários e pingbacks em todo o site.
 *
 * O tema não exibe comentários; manter o recurso aberto só cria ruído no
 * painel e superfície de spam.
 *
 * @return void
 */
function tb_disable_comments() {
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 10 );
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
}
add_action( 'init', 'tb_disable_comments', 20 );

/**
 * Remove o suporte nativo a comentários de posts e páginas.
 *
 * @return void
 */
function tb_remove_comments_support() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );
}
add_action( 'init', 'tb_remove_comments_support', 20 );

/**
 * Esconde o menu de comentários no painel.
 *
 * @return void
 */
function tb_remove_comments_menu() {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'tb_remove_comments_menu', 99 );

/**
 * Ordena arquivos públicos de conteúdo por menu_order.
 *
 * @param WP_Query $query Consulta.
 * @return void
 */
function tb_public_archive_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( is_post_type_archive( array( 'area', 'escritorio', 'socio' ) ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'tb_public_archive_order' );
