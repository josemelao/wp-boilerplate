<?php
/**
 * Funções do tema.
 *
 * O tema é clássico: o layout fica no PHP/CSS e o cliente edita apenas
 * conteúdo (CPTs, imagem destacada, campos e "Opções do Site").
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TB_VERSION', '0.2.0' );
define( 'TB_DIR', get_template_directory() );
define( 'TB_URI', get_template_directory_uri() );

require_once TB_DIR . '/inc/helpers.php';
require_once TB_DIR . '/inc/setup.php';
require_once TB_DIR . '/inc/post-types.php';
require_once TB_DIR . '/inc/meta-fields.php';
require_once TB_DIR . '/inc/options-page.php';
require_once TB_DIR . '/inc/admin-ux.php';
