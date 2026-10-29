<?php
/**
 * Página "Opções do Site" (option tb_options).
 *
 * Fica disponível para o papel Editor (capability edit_pages), para que o
 * cliente ajuste textos e links sem ser administrador.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability necessária para ver/editar as opções.
 *
 * @return string
 */
function tb_options_capability() {
	return 'edit_pages';
}

/**
 * Registra o menu da página.
 *
 * @return void
 */
function tb_add_options_page() {
	add_menu_page(
		__( 'Opções do Site', 'tema-base' ),
		__( 'Opções do Site', 'tema-base' ),
		tb_options_capability(),
		'tb-options',
		'tb_render_options_page',
		'dashicons-admin-generic',
		59
	);
}
add_action( 'admin_menu', 'tb_add_options_page' );

/*
 * options.php usa "manage_options" por padrão; sem este filtro o Editor vê a
 * página mas recebe 403 ao salvar. O plano exige que o Editor gerencie as
 * opções do site (seção 7).
 */
add_filter( 'option_page_capability_tb_options_group', 'tb_options_capability' );

/**
 * Sanitiza o array de opções, mantendo apenas as chaves previstas.
 *
 * @param mixed $input Valor enviado pelo formulário.
 * @return array
 */
function tb_sanitize_options( $input ) {
	$defaults = tb_options_defaults();
	$input    = is_array( $input ) ? $input : array();
	$clean    = array();

	foreach ( $defaults as $key => $default ) {
		$value = isset( $input[ $key ] ) ? $input[ $key ] : '';
		$value = is_scalar( $value ) ? (string) $value : '';

		switch ( $key ) {
			case 'social_linkedin':
			case 'social_instagram':
			case 'social_x':
			case 'social_facebook':
			case 'social_youtube':
			case 'subscribe_url':
				$clean[ $key ] = esc_url_raw( trim( $value ) );
				break;
			case 'offices':
				$clean[ $key ] = sanitize_textarea_field( $value );
				break;
			default:
				$clean[ $key ] = sanitize_text_field( $value );
				break;
		}
	}

	return $clean;
}

/**
 * Registra a opção e os campos.
 *
 * @return void
 */
function tb_register_options() {
	register_setting(
		'tb_options_group',
		'tb_options',
		array(
			'type'              => 'array',
			'default'           => tb_options_defaults(),
			'sanitize_callback' => 'tb_sanitize_options',
		)
	);

	add_settings_section(
		'tb_section_home',
		__( 'Seções da home', 'tema-base' ),
		'tb_section_home_intro',
		'tb-options'
	);

	add_settings_field(
		'insights_heading',
		__( 'Título de "Artigos em destaque"', 'tema-base' ),
		'tb_render_text_field',
		'tb-options',
		'tb_section_home',
		array(
			'key'         => 'insights_heading',
			'description' => __( 'Aparece acima dos cards de posts.', 'tema-base' ),
		)
	);

	add_settings_field(
		'stories_heading',
		__( 'Título da seção "Histórias"', 'tema-base' ),
		'tb_render_text_field',
		'tb-options',
		'tb_section_home',
		array(
			'key'         => 'stories_heading',
			'description' => __( 'Aparece na seção de histórias.', 'tema-base' ),
		)
	);

	add_settings_section(
		'tb_section_social',
		__( 'Redes sociais', 'tema-base' ),
		'tb_section_social_intro',
		'tb-options'
	);

	$socials = array(
		'social_linkedin'  => 'LinkedIn',
		'social_instagram' => 'Instagram',
		'social_x'         => 'X (Twitter)',
		'social_facebook'  => 'Facebook',
		'social_youtube'   => 'YouTube',
	);

	foreach ( $socials as $key => $label ) {
		add_settings_field(
			$key,
			$label,
			'tb_render_text_field',
			'tb-options',
			'tb_section_social',
			array(
				'key'         => $key,
				'type'        => 'url',
				'placeholder' => 'https://',
				'description' => __( 'Deixe em branco para não exibir.', 'tema-base' ),
			)
		);
	}

	add_settings_section(
		'tb_section_offices',
		__( 'Escritórios', 'tema-base' ),
		'tb_section_offices_intro',
		'tb-options'
	);

	add_settings_field(
		'offices',
		__( 'Lista de escritórios', 'tema-base' ),
		'tb_render_textarea_field',
		'tb-options',
		'tb_section_offices',
		array(
			'key'         => 'offices',
			'description' => __( 'Uma cidade por linha. Ex.: São Paulo', 'tema-base' ),
		)
	);

	add_settings_section(
		'tb_section_footer',
		__( 'Inscrição e rodapé', 'tema-base' ),
		'tb_section_footer_intro',
		'tb-options'
	);

	add_settings_field(
		'subscribe_label',
		__( 'Texto do botão de inscrição', 'tema-base' ),
		'tb_render_text_field',
		'tb-options',
		'tb_section_footer',
		array(
			'key' => 'subscribe_label',
		)
	);

	add_settings_field(
		'subscribe_url',
		__( 'URL de inscrição', 'tema-base' ),
		'tb_render_text_field',
		'tb-options',
		'tb_section_footer',
		array(
			'key'         => 'subscribe_url',
			'type'        => 'url',
			'placeholder' => 'https://',
			'description' => __( 'Enquanto estiver em branco, o botão de inscrição não aparece.', 'tema-base' ),
		)
	);

	add_settings_field(
		'footer_note',
		__( 'Nota do rodapé', 'tema-base' ),
		'tb_render_text_field',
		'tb-options',
		'tb_section_footer',
		array(
			'key'         => 'footer_note',
			'description' => __( 'Fica ao lado do copyright.', 'tema-base' ),
		)
	);
}
add_action( 'admin_init', 'tb_register_options' );

/**
 * Texto de apoio da seção da home.
 *
 * @return void
 */
function tb_section_home_intro() {
	echo '<p>' . esc_html__( 'Títulos exibidos nas seções da página inicial.', 'tema-base' ) . '</p>';
}

/**
 * Texto de apoio da seção de redes sociais.
 *
 * @return void
 */
function tb_section_social_intro() {
	echo '<p>' . esc_html__( 'Somente as redes preenchidas aparecem no rodapé.', 'tema-base' ) . '</p>';
}

/**
 * Texto de apoio da seção de escritórios.
 *
 * @return void
 */
function tb_section_offices_intro() {
	echo '<p>' . esc_html__( 'Cada linha vira um item da lista de escritórios no rodapé.', 'tema-base' ) . '</p>';
}

/**
 * Texto de apoio da seção de rodapé.
 *
 * @return void
 */
function tb_section_footer_intro() {
	echo '<p>' . esc_html__( 'Inscrição e texto do rodapé.', 'tema-base' ) . '</p>';
}

/**
 * Campo de texto/URL.
 *
 * @param array $args Argumentos do campo.
 * @return void
 */
function tb_render_text_field( $args ) {
	$key         = isset( $args['key'] ) ? $args['key'] : '';
	$type        = isset( $args['type'] ) ? $args['type'] : 'text';
	$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
	$value       = tb_opt( $key );

	printf(
		'<input type="%1$s" id="tb-%2$s" name="tb_options[%2$s]" value="%3$s" class="regular-text" placeholder="%4$s">',
		esc_attr( $type ),
		esc_attr( $key ),
		esc_attr( $value ),
		esc_attr( $placeholder )
	);

	if ( ! empty( $args['description'] ) ) {
		echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	}
}

/**
 * Campo de texto longo.
 *
 * @param array $args Argumentos do campo.
 * @return void
 */
function tb_render_textarea_field( $args ) {
	$key   = isset( $args['key'] ) ? $args['key'] : '';
	$value = tb_opt( $key );

	printf(
		'<textarea id="tb-%1$s" name="tb_options[%1$s]" rows="5" class="large-text code">%2$s</textarea>',
		esc_attr( $key ),
		esc_textarea( $value )
	);

	if ( ! empty( $args['description'] ) ) {
		echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	}
}

/**
 * Renderiza a página de opções.
 *
 * @return void
 */
function tb_render_options_page() {
	if ( ! current_user_can( tb_options_capability() ) ) {
		wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'tema-base' ) );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Ajustes de texto e links usados no site. O layout não muda por aqui.', 'tema-base' ); ?>
		</p>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'tb_options_group' );
			do_settings_sections( 'tb-options' );
			submit_button( __( 'Salvar opções', 'tema-base' ) );
			?>
		</form>
	</div>
	<?php
}
