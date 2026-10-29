<?php
/**
 * Custom Post Types do tema (D3/D7/D8 do plano).
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monta o conjunto de rótulos em português.
 *
 * @param string $singular Singular.
 * @param string $plural   Plural.
 * @return array
 */
function tb_post_type_labels( $singular, $plural ) {
	return array(
		'name'                  => $plural,
		'singular_name'         => $singular,
		'menu_name'             => $plural,
		'all_items'             => sprintf( 'Todos os %s', $plural ),
		'add_new'               => 'Adicionar novo',
		'add_new_item'          => sprintf( 'Adicionar %s', $singular ),
		'edit_item'             => sprintf( 'Editar %s', $singular ),
		'new_item'              => sprintf( 'Novo %s', $singular ),
		'view_item'             => sprintf( 'Ver %s', $singular ),
		'search_items'          => sprintf( 'Buscar %s', $plural ),
		'not_found'             => sprintf( 'Nenhum %s encontrado.', $singular ),
		'not_found_in_trash'    => sprintf( 'Nenhum %s na lixeira.', $singular ),
		'featured_image'        => 'Imagem',
		'set_featured_image'    => 'Definir imagem',
		'remove_featured_image' => 'Remover imagem',
		'use_featured_image'    => 'Usar como imagem',
	);
}

/**
 * Registra os 5 tipos de conteúdo.
 *
 * @return void
 */
function tb_register_post_types() {
	$base = array(
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'has_archive'         => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_in_nav_menus'   => false,
		'query_var'           => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
	);

	register_post_type(
		'banner',
		array_merge(
			$base,
			array(
				'labels'       => tb_post_type_labels( 'Banner', 'Banners do topo' ),
				'description'  => 'Slides do topo da home. Imagem recomendada: 1920 × 800 px.',
				'menu_icon'    => 'dashicons-format-image',
				'menu_position' => 20,
			)
		)
	);

	register_post_type(
		'destaque',
		array_merge(
			$base,
			array(
				'labels'       => tb_post_type_labels( 'Destaque', 'Destaques' ),
				'description'  => 'Cards do carrossel da home. Imagem recomendada: 1280 × 640 px.',
				'menu_icon'    => 'dashicons-images-alt2',
				'menu_position' => 21,
			)
		)
	);

	register_post_type(
		'depoimento',
		array_merge(
			$base,
			array(
				'labels'        => tb_post_type_labels( 'Depoimento', 'Depoimentos' ),
				'description'   => 'Reconhecimentos exibidos na home. O título é a manchete.',
				'menu_icon'     => 'dashicons-format-quote',
				'menu_position' => 22,
				'supports'      => array( 'title', 'page-attributes' ),
			)
		)
	);

	register_post_type(
		'historia',
		array_merge(
			$base,
			array(
				'labels'        => tb_post_type_labels( 'História', 'Histórias' ),
				'description'   => 'Itens da seção "Histórias". Imagem de fundo recomendada: 1600 × 900 px.',
				'menu_icon'     => 'dashicons-book-alt',
				'menu_position' => 23,
			)
		)
	);

	register_post_type(
		'socio',
		array_merge(
			$base,
			array(
				'labels'              => tb_post_type_labels( 'Sócio', 'Sócios' ),
				'description'         => 'Quadro de sócios, com página própria. Retrato recomendado: 640 × 800 px.',
				'menu_icon'           => 'dashicons-groups',
				'menu_position'       => 24,
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_in_nav_menus'   => true,
				'query_var'           => 'socio',
				'has_archive'         => true,
				'rewrite'             => array(
					'slug'       => 'socios',
					'with_front' => false,
				),
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			)
		)
	);
}
add_action( 'init', 'tb_register_post_types', 5 );

/**
 * Garante as URLs de /socios/ quando o tema é ativado.
 *
 * @return void
 */
function tb_flush_rewrite_on_activation() {
	tb_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'tb_flush_rewrite_on_activation' );
