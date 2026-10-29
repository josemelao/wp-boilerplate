<?php
/**
 * Funções auxiliares usadas por templates, opções e admin.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tipos de conteúdo gerenciados pelo tema.
 *
 * @return string[]
 */
function tb_content_types() {
	return array( 'banner', 'destaque', 'depoimento', 'historia', 'socio' );
}

/**
 * Valores padrão das "Opções do Site".
 *
 * @return array
 */
function tb_options_defaults() {
	return array(
		'insights_heading' => __( 'Artigos em destaque', 'tema-base' ),
		'stories_heading'  => __( 'Histórias', 'tema-base' ),
		'social_linkedin'  => '',
		'social_instagram' => '',
		'social_x'         => '',
		'social_facebook'  => '',
		'social_youtube'   => '',
		'offices'          => '',
		'subscribe_label'  => __( 'Inscreva-se', 'tema-base' ),
		'subscribe_url'    => '',
		'footer_note'      => __( 'Todos os direitos reservados.', 'tema-base' ),
	);
}

/**
 * Lê uma opção do site com fallback para o padrão.
 *
 * @param string $key     Chave da opção.
 * @param mixed  $default Valor devolvido quando não há valor nem padrão.
 * @return mixed
 */
function tb_opt( $key, $default = null ) {
	$options = get_option( 'tb_options', array() );

	// Chave presente vale mesmo vazia: o cliente pode apagar um texto de propósito.
	if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
		return $options[ $key ];
	}

	if ( null === $default ) {
		$defaults = tb_options_defaults();

		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	return $default;
}

/**
 * Redes sociais preenchidas, na ordem de exibição.
 *
 * @return array<string,array{label:string,url:string}>
 */
function tb_get_social_links() {
	$networks = array(
		'linkedin'  => array(
			'label' => 'LinkedIn',
			'key'   => 'social_linkedin',
		),
		'instagram' => array(
			'label' => 'Instagram',
			'key'   => 'social_instagram',
		),
		'x'         => array(
			'label' => 'X',
			'key'   => 'social_x',
		),
		'facebook'  => array(
			'label' => 'Facebook',
			'key'   => 'social_facebook',
		),
		'youtube'   => array(
			'label' => 'YouTube',
			'key'   => 'social_youtube',
		),
	);

	$links = array();

	foreach ( $networks as $slug => $network ) {
		$url = tb_opt( $network['key'] );

		if ( '' === $url ) {
			continue;
		}

		$links[ $slug ] = array(
			'label' => $network['label'],
			'url'   => esc_url( $url ),
		);
	}

	return $links;
}

/**
 * Escritórios: uma cidade por linha em "Opções do Site".
 *
 * @return string[]
 */
function tb_get_offices() {
	$raw = (string) tb_opt( 'offices' );

	if ( '' === trim( $raw ) ) {
		return array();
	}

	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	$lines = array_map( 'trim', (array) $lines );

	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * URL do blog (página definida em Configurações → Leitura).
 *
 * @return string
 */
function tb_blog_url() {
	$page_id = (int) get_option( 'page_for_posts' );

	if ( $page_id ) {
		$permalink = get_permalink( $page_id );

		if ( $permalink ) {
			return $permalink;
		}
	}

	return home_url( '/' );
}

/**
 * Consulta padrão de um CPT: publicados, ordenados por menu_order.
 *
 * @param string $post_type Tipo de conteúdo.
 * @param array  $args      Argumentos extras para o WP_Query.
 * @return WP_Query
 */
function tb_query( $post_type, $args = array() ) {
	$defaults = array(
		'post_type'           => $post_type,
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'orderby'             => 'menu_order',
		'order'               => 'ASC',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	);

	return new WP_Query( array_merge( $defaults, (array) $args ) );
}

/**
 * Posts em destaque da home: fixados primeiro, completando com os recentes.
 *
 * @param int $count Quantidade desejada.
 * @return WP_Post[]
 */
function tb_featured_posts( $count = 3 ) {
	$count = max( 1, (int) $count );
	$ids   = array();
	$sticky = get_option( 'sticky_posts' );

	if ( ! empty( $sticky ) ) {
		$sticky_query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'post__in'            => array_map( 'intval', (array) $sticky ),
				'posts_per_page'      => $count,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);

		$ids = wp_list_pluck( $sticky_query->posts, 'ID' );
	}

	if ( count( $ids ) < $count ) {
		$recent_query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $count - count( $ids ),
				'post__not_in'        => $ids,
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);

		$ids = array_merge( $ids, wp_list_pluck( $recent_query->posts, 'ID' ) );
	}

	if ( empty( $ids ) ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'      => 'post',
			'post__in'       => $ids,
			'posts_per_page' => count( $ids ),
			'orderby'        => 'post__in',
		)
	);
}

/**
 * Fallback do menu principal: lista as páginas de topo.
 *
 * @return void
 */
function tb_menu_fallback() {
	echo '<ul class="site-nav__list">';
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
		)
	);
	echo '</ul>';
}
