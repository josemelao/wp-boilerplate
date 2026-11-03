<?php
/**
 * Formulário de busca do tema.
 *
 * @package Tema_Base
 */

$tb_search_id = wp_unique_id( 'tb-search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $tb_search_id ); ?>"><?php esc_html_e( 'Buscar no site', 'tema-base' ); ?></label>
	<input
		type="search"
		id="<?php echo esc_attr( $tb_search_id ); ?>"
		class="search-field"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Buscar no site', 'tema-base' ); ?>"
	>
	<button type="submit" class="search-submit"><?php esc_html_e( 'Buscar', 'tema-base' ); ?></button>
</form>
