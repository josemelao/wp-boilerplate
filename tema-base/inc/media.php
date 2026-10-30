<?php
/**
 * Pipeline de mídia: WebP e preload do LCP.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gera derivados WebP para uploads JPEG/PNG quando o editor suporta.
 *
 * O arquivo original é preservado; os tamanhos registrados pelo tema
 * (tb-hero, tb-card, tb-story etc.) passam a ser WebP.
 *
 * @param array  $formats  Mapeamento de mime type de saída.
 * @param string $filename Caminho do arquivo.
 * @param string $mime_type Mime type de origem.
 * @return array
 */
function tb_webp_output_format( $formats, $filename, $mime_type ) {
	if ( ! in_array( $mime_type, array( 'image/jpeg', 'image/png' ), true ) ) {
		return $formats;
	}

	if ( ! function_exists( 'wp_image_editor_supports' ) || ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		return $formats;
	}

	$formats[ $mime_type ] = 'image/webp';

	return $formats;
}
add_filter( 'image_editor_output_format', 'tb_webp_output_format', 10, 3 );

/**
 * Ajusta a qualidade dos derivados WebP.
 *
 * @param int    $quality   Qualidade atual.
 * @param string $mime_type Mime type.
 * @return int
 */
function tb_webp_quality( $quality, $mime_type ) {
	return 'image/webp' === $mime_type ? 80 : $quality;
}
add_filter( 'wp_editor_set_quality', 'tb_webp_quality', 10, 2 );

/**
 * Pré-carrega a primeira imagem do hero apenas na home.
 *
 * @return void
 */
function tb_preload_home_hero() {
	if ( is_admin() || ! is_front_page() ) {
		return;
	}

	$banners = get_posts(
		array(
			'post_type'      => 'banner',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	if ( empty( $banners ) ) {
		return;
	}

	$image_id = get_post_thumbnail_id( $banners[0] );

	if ( ! $image_id ) {
		return;
	}

	$src = wp_get_attachment_image_src( $image_id, 'tb-hero' );

	if ( ! $src ) {
		return;
	}

	$srcset = wp_get_attachment_image_srcset( $image_id, 'tb-hero' );
	$sizes  = wp_get_attachment_image_sizes( $image_id, 'tb-hero' );

	printf( '<link rel="preload" as="image" href="%s"', esc_url( $src[0] ) );

	if ( $srcset ) {
		printf( ' imagesrcset="%s"', esc_attr( $srcset ) );
	}

	if ( $sizes ) {
		printf( ' imagesizes="%s"', esc_attr( $sizes ) );
	}

	echo ' fetchpriority="high">' . "\n";
}
add_action( 'wp_head', 'tb_preload_home_hero', 1 );
