<?php
/**
 * Trilha de navegação visível. Os dados vêm de tb_seo_breadcrumb_items().
 *
 * @package Tema_Base
 */

if ( is_front_page() ) {
	return;
}

if ( ! function_exists( 'tb_seo_breadcrumb_items' ) ) {
	return;
}

$tb_items = tb_seo_breadcrumb_items();

if ( count( $tb_items ) < 2 ) {
	return;
}

$tb_class = 'breadcrumbs';

if ( ! empty( $args['modifier'] ) ) {
	$tb_class .= ' ' . sanitize_html_class( $args['modifier'] );
}

$tb_last = count( $tb_items ) - 1;
?>
<nav class="<?php echo esc_attr( $tb_class ); ?>" aria-label="<?php esc_attr_e( 'Trilha de navegação', 'tema-base' ); ?>">
	<ol class="breadcrumbs__list">
		<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
			<li class="breadcrumbs__item">
				<?php if ( $tb_index === $tb_last || empty( $tb_item['url'] ) ) : ?>
					<span aria-current="page"><?php echo esc_html( $tb_item['name'] ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( $tb_item['url'] ); ?>"><?php echo esc_html( $tb_item['name'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
