<?php
/**
 * Card de escritório com canais de contato, usado na página Contato.
 *
 * @package Tema_Base
 */

$tb_office_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_office    = get_post( $tb_office_id );

if ( ! $tb_office ) {
	return;
}

$tb_address  = tb_post_meta( $tb_office_id, '_tb_address' );
$tb_phone    = tb_post_meta( $tb_office_id, '_tb_phone' );
$tb_email    = tb_post_meta( $tb_office_id, '_tb_email' );
$tb_maps_url = tb_post_meta( $tb_office_id, '_tb_maps_url' );
$tb_phone_uri = preg_replace( '/[^0-9+]/', '', $tb_phone );
?>
<article class="office-contact-card">
	<h3 class="office-contact-card__title"><?php echo esc_html( get_the_title( $tb_office ) ); ?></h3>
	<?php if ( $tb_address ) : ?>
		<address class="office-contact-card__address"><?php echo nl2br( esc_html( $tb_address ) ); ?></address>
	<?php endif; ?>
	<ul class="office-contact-card__list">
		<?php if ( $tb_phone && $tb_phone_uri ) : ?>
			<li><a href="tel:<?php echo esc_attr( $tb_phone_uri ); ?>"><?php echo esc_html( $tb_phone ); ?></a></li>
		<?php endif; ?>
		<?php if ( $tb_email ) : ?>
			<li><a href="mailto:<?php echo esc_attr( sanitize_email( $tb_email ) ); ?>"><?php echo esc_html( $tb_email ); ?></a></li>
		<?php endif; ?>
		<?php if ( $tb_maps_url ) : ?>
			<li><a href="<?php echo esc_url( $tb_maps_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ver no mapa', 'tema-base' ); ?> <span aria-hidden="true">↗</span><span class="screen-reader-text"><?php esc_html_e( '(abre em nova aba)', 'tema-base' ); ?></span></a></li>
		<?php endif; ?>
	</ul>
	<a class="office-contact-card__link" href="<?php echo esc_url( get_permalink( $tb_office ) ); ?>"><?php esc_html_e( 'Ver unidade', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
</article>
