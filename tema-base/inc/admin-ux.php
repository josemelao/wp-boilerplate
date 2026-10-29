<?php
/**
 * Experiência do cliente no painel (seção 7 do plano).
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tamanhos recomendados por tipo, para a dica da imagem destacada.
 *
 * @return array
 */
function tb_image_size_tips() {
	return array(
		'banner'   => array( 1920, 800 ),
		'destaque' => array( 1280, 640 ),
		'historia' => array( 1600, 900 ),
		'socio'    => array( 640, 800 ),
	);
}

/**
 * Colunas extras nas listagens dos CPTs.
 *
 * @param array  $columns   Colunas originais.
 * @param string $post_type Tipo de conteúdo da listagem.
 * @return array
 */
function tb_admin_columns( $columns, $post_type = '' ) {
	$extra = array();

	if ( '' === $post_type || post_type_supports( $post_type, 'thumbnail' ) ) {
		$extra['tb_image'] = __( 'Imagem', 'tema-base' );
	}

	$extra['tb_order'] = __( 'Ordem', 'tema-base' );

	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'title' === $key ) {
			$new = array_merge( $new, $extra );
		}
	}

	foreach ( $extra as $key => $label ) {
		if ( ! isset( $new[ $key ] ) ) {
			$new[ $key ] = $label;
		}
	}

	return $new;
}

/**
 * Conteúdo das colunas extras.
 *
 * @param string $column  Coluna.
 * @param int    $post_id ID do post.
 * @return void
 */
function tb_admin_column_content( $column, $post_id ) {
	if ( 'tb_image' === $column ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail(
				$post_id,
				array( 60, 60 ),
				array(
					'style' => 'width:60px;height:auto;display:block;',
					'alt'   => '',
				)
			);
		} else {
			echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'Sem imagem', 'tema-base' ) . '</span>';
		}

		return;
	}

	if ( 'tb_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}

/**
 * Permite ordenar por "Ordem" clicando no cabeçalho.
 *
 * @param array $columns Colunas ordenáveis.
 * @return array
 */
function tb_sortable_columns( $columns ) {
	$columns['tb_order'] = 'menu_order';

	return $columns;
}

/**
 * Ordem padrão no admin: menu_order ASC.
 *
 * @param WP_Query $query Consulta.
 * @return void
 */
function tb_admin_default_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$post_type = $query->get( 'post_type' );

	if ( is_string( $post_type ) && in_array( $post_type, tb_content_types(), true ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
}

/**
 * Dica de tamanho de imagem abaixo do box de imagem destacada.
 *
 * @param string $content HTML atual do box.
 * @return string
 */
function tb_thumbnail_tip( $content ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'post' !== $screen->base ) {
		return $content;
	}

	$post_type = get_post_type();
	$tips      = tb_image_size_tips();

	if ( ! isset( $tips[ $post_type ] ) ) {
		return $content;
	}

	$size = $tips[ $post_type ];

	$content .= sprintf(
		'<p class="description">%s</p>',
		esc_html(
			sprintf(
				/* translators: 1: largura, 2: altura */
				__( 'Tamanho recomendado: %1$d × %2$d px. A imagem é recortada automaticamente; prefira fotos na horizontal/vertical indicada.', 'tema-base' ),
				$size[0],
				$size[1]
			)
		)
	);

	return $content;
}

/**
 * A home/blog estão configurados? (Configurações → Leitura)
 *
 * @return bool
 */
function tb_reading_notice_needed() {
	$show_on_front = get_option( 'show_on_front' );
	$posts_page    = (int) get_option( 'page_for_posts' );

	return ( 'page' !== $show_on_front || 0 === $posts_page );
}

/**
 * Aviso para administradores quando a Leitura não está configurada.
 *
 * @return void
 */
function tb_reading_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! tb_reading_notice_needed() ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html__( 'O tema espera uma página inicial estática e uma página de posts definidas. Sem isso, o blog e o botão "Ler mais" não funcionam.', 'tema-base' ),
		esc_url( admin_url( 'options-reading.php' ) ),
		esc_html__( 'Configurar agora', 'tema-base' )
	);
}

/* --- Registro dos hooks por tipo de conteúdo ------------------------------ */

foreach ( tb_content_types() as $tb_type ) {
	add_filter(
		"manage_{$tb_type}_posts_columns",
		function ( $columns ) use ( $tb_type ) {
			return tb_admin_columns( $columns, $tb_type );
		}
	);
	add_filter( "manage_edit-{$tb_type}_sortable_columns", 'tb_sortable_columns' );
	add_action( "manage_{$tb_type}_posts_custom_column", 'tb_admin_column_content', 10, 2 );
}

add_action( 'pre_get_posts', 'tb_admin_default_order' );
add_filter( 'admin_post_thumbnail_html', 'tb_thumbnail_tip' );
add_action( 'admin_notices', 'tb_reading_notice' );
