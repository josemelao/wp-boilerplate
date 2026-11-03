<?php
/**
 * Campos (meta boxes) dos CPTs e registro na REST API.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Definição dos campos por tipo de conteúdo (seção 5 do plano).
 *
 * @return array
 */
function tb_meta_fields() {
	return array(
		'banner'     => array(
			'title'  => 'Conteúdo do banner',
			'fields' => array(
				'_tb_label'      => array(
					'label'       => 'Etiqueta',
					'type'        => 'text',
					'description' => 'Texto curto acima do título (ex.: área de atuação).',
				),
				'_tb_text'       => array(
					'label'       => 'Frase de apoio',
					'type'        => 'textarea',
					'description' => 'Frase de apoio exibida sobre a imagem.',
				),
				'_tb_link'       => array(
					'label'       => 'URL de destino',
					'type'        => 'url',
					'description' => 'Para onde o botão leva. Deixe em branco para não exibir botão.',
				),
				'_tb_link_label' => array(
					'label'       => 'Texto do botão',
					'type'        => 'text',
					'default'     => 'Leia mais',
					'description' => 'Padrão: "Leia mais".',
				),
			),
		),
		'destaque'   => array(
			'title'  => 'Conteúdo do card',
			'fields' => array(
				'_tb_label'      => array(
					'label'       => 'Etiqueta',
					'type'        => 'text',
					'description' => 'Categoria ou área exibida no card.',
				),
				'_tb_link'       => array(
					'label'       => 'URL de destino',
					'type'        => 'url',
					'description' => 'Página ou post para onde o card leva.',
				),
				'_tb_link_label' => array(
					'label'       => 'Texto do link',
					'type'        => 'text',
					'default'     => 'Leia mais',
					'description' => 'Padrão: "Leia mais".',
				),
			),
		),
		'depoimento' => array(
			'title'  => 'Conteúdo do depoimento',
			'fields' => array(
				'_tb_quote'  => array(
					'label'       => 'Texto',
					'type'        => 'textarea',
					'description' => 'O depoimento em si.',
				),
				'_tb_source' => array(
					'label'       => 'Fonte',
					'type'        => 'text',
					'description' => 'Quem disse (ex.: publicação, cliente, ano).',
				),
			),
		),
		'historia'   => array(
			'title'  => 'Conteúdo da história',
			'fields' => array(
				'_tb_summary' => array(
					'label'       => 'Resumo',
					'type'        => 'textarea',
					'description' => 'Frase curta exibida na seção Histórias e nos resultados de busca.',
				),
				'_tb_link'    => array(
					'label'       => 'URL de destino',
					'type'        => 'url',
					'description' => 'Link relacionado opcional exibido na página individual da história.',
				),
				'_tb_credit'  => array(
					'label'       => 'Crédito da imagem',
					'type'        => 'text',
					'description' => 'Texto discreto exibido sobre a imagem. Opcional.',
				),
			),
		),
		'socio'      => array(
			'title'  => 'Dados do sócio',
			'fields' => array(
				'_tb_role'      => array(
					'label'       => 'Cargo',
					'type'        => 'text',
					'description' => 'Ex.: Sócio fundador.',
				),
				'_tb_areas'     => array(
					'label'       => 'Áreas de atuação',
					'type'        => 'relationship',
					'post_type'   => 'area',
					'description' => 'Selecione as áreas que aparecem no perfil.',
				),
				'_tb_education' => array(
					'label'       => 'Formação',
					'type'        => 'textarea',
					'description' => 'Uma formação por linha. Opcional.',
				),
				'_tb_languages' => array(
					'label'       => 'Idiomas',
					'type'        => 'textarea',
					'description' => 'Um idioma por linha. Opcional.',
				),
				'_tb_oab'       => array(
					'label'       => 'OAB',
					'type'        => 'text',
					'description' => 'Registro profissional. Opcional.',
				),
				'_tb_email'     => array(
					'label'       => 'E-mail',
					'type'        => 'email',
					'description' => 'Opcional.',
				),
				'_tb_phone'     => array(
					'label'       => 'Telefone',
					'type'        => 'text',
					'description' => 'Opcional.',
				),
				'_tb_linkedin'  => array(
					'label'       => 'LinkedIn',
					'type'        => 'url',
					'description' => 'URL completa do perfil. Opcional.',
				),
			),
		),
		'area'       => array(
			'title'  => 'Conteúdo da área',
			'fields' => array(
				'_tb_services' => array(
					'label'       => 'Como atuamos',
					'type'        => 'textarea',
					'description' => 'Um serviço por linha. A ordem é mantida no site.',
				),
				'_tb_sectors'  => array(
					'label'       => 'Setores',
					'type'        => 'textarea',
					'description' => 'Um setor por linha. Aparece como chips abaixo dos serviços. Opcional.',
				),
			),
		),
		'escritorio' => array(
			'title'  => 'Dados do escritório',
			'fields' => array(
				'_tb_address'  => array(
					'label'       => 'Endereço',
					'type'        => 'textarea',
					'description' => 'Endereço completo, uma linha por campo se preferir.',
				),
				'_tb_phone'    => array(
					'label'       => 'Telefone',
					'type'        => 'text',
					'description' => 'Telefone com DDD.',
				),
				'_tb_email'    => array(
					'label'       => 'E-mail',
					'type'        => 'email',
					'description' => 'Contato da unidade.',
				),
				'_tb_maps_url' => array(
					'label'       => 'Link do mapa',
					'type'        => 'url',
					'description' => 'URL do Google Maps ou similar. Opcional.',
				),
				'_tb_hours'    => array(
					'label'       => 'Horário de atendimento',
					'type'        => 'textarea',
					'description' => 'Um período por linha. Ex.: Segunda a sexta: 9h às 18h. Opcional.',
				),
			),
		),
	);
}

/**
 * Agrupa os campos em boxes por tipo de conteúdo.
 *
 * Tipos com muitos campos são divididos para separar o essencial do contato.
 *
 * @return array
 */
function tb_meta_boxes() {
	$config = tb_meta_fields();
	$groups = array(
		'socio' => array(
			array(
				'id'     => 'perfil',
				'title'  => 'Perfil profissional',
				'keys'   => array( '_tb_role', '_tb_areas', '_tb_education', '_tb_languages', '_tb_oab' ),
			),
			array(
				'id'     => 'contato',
				'title'  => 'Contato',
				'keys'   => array( '_tb_email', '_tb_phone', '_tb_linkedin' ),
			),
		),
	);

	$boxes = array();

	foreach ( $config as $type => $box ) {
		if ( ! isset( $groups[ $type ] ) ) {
			$boxes[ $type ][] = array(
				'id'     => 'conteudo',
				'title'  => $box['title'],
				'fields' => $box['fields'],
			);
			continue;
		}

		foreach ( $groups[ $type ] as $group ) {
			$fields = array();

			foreach ( $group['keys'] as $key ) {
				if ( isset( $box['fields'][ $key ] ) ) {
					$fields[ $key ] = $box['fields'][ $key ];
				}
			}

			$boxes[ $type ][] = array(
				'id'     => $group['id'],
				'title'  => $group['title'],
				'fields' => $fields,
			);
		}
	}

	return $boxes;
}

/**
 * Sanitiza um valor de meta conforme o tipo do campo.
 *
 * @param mixed  $value Valor bruto.
 * @param string $type  Tipo do campo.
 * @param array  $field Configuração do campo.
 * @return string|array
 */
function tb_sanitize_meta_value( $value, $type, $field = array() ) {
	if ( 'relationship' === $type ) {
		$ids       = is_array( $value ) ? $value : array();
		$ids       = array_map( 'absint', $ids );
		$ids       = array_values( array_unique( array_filter( $ids ) ) );
		$post_type = isset( $field['post_type'] ) ? $field['post_type'] : '';
		$valid     = array();

		if ( '' === $post_type ) {
			return array();
		}

		foreach ( $ids as $id ) {
			if ( get_post_type( $id ) === $post_type && 'publish' === get_post_status( $id ) ) {
				$valid[] = $id;
			}
		}

		return $valid;
	}

	$value = is_scalar( $value ) ? trim( (string) $value ) : '';

	switch ( $type ) {
		case 'url':
			return esc_url_raw( $value );
		case 'email':
			return sanitize_email( $value );
		case 'textarea':
			return sanitize_textarea_field( $value );
		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Lê uma meta com fallback para o padrão do campo.
 *
 * @param int    $post_id ID do post.
 * @param string $key     Meta key.
 * @return string
 */
function tb_post_meta( $post_id, $key ) {
	$value = get_post_meta( (int) $post_id, $key, true );

	if ( '' !== $value && null !== $value ) {
		return $value;
	}

	$config = tb_meta_fields();
	$type   = get_post_type( $post_id );

	if ( isset( $config[ $type ]['fields'][ $key ]['default'] ) ) {
		return $config[ $type ]['fields'][ $key ]['default'];
	}

	return '';
}

/**
 * Registra as meta boxes de cada tipo.
 *
 * @return void
 */
function tb_add_meta_boxes() {
	foreach ( tb_meta_boxes() as $post_type => $boxes ) {
		foreach ( $boxes as $box ) {
			add_meta_box(
				'tb-meta-' . $post_type . '-' . $box['id'],
				$box['title'],
				'tb_render_meta_box',
				$post_type,
				'normal',
				'high',
				array( 'tb_fields' => $box['fields'] )
			);
		}
	}
}
add_action( 'add_meta_boxes', 'tb_add_meta_boxes' );

/**
 * Renderiza a meta box.
 *
 * @param WP_Post    $post Post em edição.
 * @param array|null $box  Argumentos da meta box (campos do grupo).
 * @return void
 */
function tb_render_meta_box( $post, $box = null ) {
	$type   = get_post_type( $post );
	$fields = array();

	if ( is_array( $box ) && isset( $box['args']['tb_fields'] ) && is_array( $box['args']['tb_fields'] ) ) {
		$fields = $box['args']['tb_fields'];
	} else {
		$config = tb_meta_fields();

		if ( ! isset( $config[ $type ] ) ) {
			return;
		}

		$fields = $config[ $type ]['fields'];
	}

	if ( empty( $fields ) ) {
		return;
	}

	static $tb_nonce_printed = false;

	if ( ! $tb_nonce_printed ) {
		wp_nonce_field( 'tb_save_meta', 'tb_meta_nonce' );
		$tb_nonce_printed = true;
	}

	echo '<table class="form-table tb-meta-table"><tbody>';

	foreach ( $fields as $key => $field ) {
		$value     = get_post_meta( $post->ID, $key, true );
		$id        = 'tb-field-' . ltrim( $key, '_' );
		$type_attr = 'text';

		if ( 'relationship' === $field['type'] ) {
			$related_type = isset( $field['post_type'] ) ? $field['post_type'] : '';
			$selected     = array_map( 'absint', (array) $value );
			$options      = $related_type ? get_posts(
				array(
					'post_type'      => $related_type,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				)
			) : array();

			echo '<tr>';
			echo '<th scope="row">' . esc_html( $field['label'] ) . '</th>';
			echo '<td>';
			echo '<fieldset class="tb-relationship">';

			if ( $options ) {
				foreach ( $options as $option ) {
					$option_id = $id . '-' . $option->ID;

					printf(
						'<p><label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s[]" value="%3$d"%4$s> %5$s</label></p>',
						esc_attr( $option_id ),
						esc_attr( $key ),
						(int) $option->ID,
						checked( in_array( (int) $option->ID, $selected, true ), true, false ),
						esc_html( get_the_title( $option ) )
					);
				}
			} else {
				echo '<p class="description">' . esc_html__( 'Nenhum item publicado para selecionar.', 'tema-base' ) . '</p>';
			}

			echo '</fieldset>';

			if ( ! empty( $field['description'] ) ) {
				echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
			}

			echo '</td></tr>';
			continue;
		}

		$value = is_scalar( $value ) ? (string) $value : '';

		if ( 'url' === $field['type'] ) {
			$type_attr = 'url';
		} elseif ( 'email' === $field['type'] ) {
			$type_attr = 'email';
		}

		echo '<tr>';
		echo '<th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th>';
		echo '<td>';

		if ( 'textarea' === $field['type'] ) {
			printf(
				'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $key ),
				esc_textarea( $value )
			);
		} else {
			$placeholder = isset( $field['default'] ) ? $field['default'] : '';

			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" placeholder="%5$s">',
				esc_attr( $type_attr ),
				esc_attr( $id ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $placeholder )
			);
		}

		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}

		echo '</td></tr>';
	}

	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'Deixe um campo em branco para não exibir a informação no site.', 'tema-base' ) . '</p>';
}

/**
 * Salva as metas com nonce, capability e sanitização.
 *
 * @param int $post_id ID do post.
 * @return void
 */
function tb_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['tb_meta_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['tb_meta_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'tb_save_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$config = tb_meta_fields();
	$type   = get_post_type( $post_id );

	if ( ! isset( $config[ $type ] ) ) {
		return;
	}

	foreach ( $config[ $type ]['fields'] as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			if ( 'relationship' === $field['type'] ) {
				delete_post_meta( $post_id, $key );
			}

			continue;
		}

		$raw   = wp_unslash( $_POST[ $key ] );
		$value = tb_sanitize_meta_value( $raw, $field['type'], $field );

		if ( '' === $value || array() === $value ) {
			delete_post_meta( $post_id, $key );
			continue;
		}

		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post', 'tb_save_meta' );

/**
 * Expõe as metas na REST API (D8), com autorização explícita.
 *
 * @return void
 */
function tb_register_meta() {
	$config = tb_meta_fields();

	foreach ( $config as $post_type => $box ) {
		foreach ( $box['fields'] as $key => $field ) {
			$field_type = $field['type'];
			$args       = array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => function ( $value ) use ( $field_type, $field ) {
					return tb_sanitize_meta_value( $value, $field_type, $field );
				},
				'auth_callback'     => function ( $allowed = false, $meta_key = '', $post_id = 0 ) {
					$post_id = (int) $post_id;

					// Em criação via REST ainda não há post: exige edit_posts.
					if ( $post_id > 0 ) {
						return current_user_can( 'edit_post', $post_id );
					}

					return current_user_can( 'edit_posts' );
				},
			);

			if ( 'relationship' === $field_type ) {
				$args['type']         = 'array';
				$args['show_in_rest'] = array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				);
			}

			register_post_meta( $post_type, $key, $args );
		}
	}
}
add_action( 'init', 'tb_register_meta', 20 );
