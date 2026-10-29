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
	$css_path = TB_DIR . '/assets/css/main.css';
	$js_path  = TB_DIR . '/assets/js/main.js';

	wp_enqueue_style(
		'tb-main',
		TB_URI . '/assets/css/main.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : TB_VERSION
	);

	wp_enqueue_script(
		'tb-main',
		TB_URI . '/assets/js/main.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : TB_VERSION,
		true
	);

	wp_script_add_data( 'tb-main', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'tb_enqueue_assets' );
