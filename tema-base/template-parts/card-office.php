<?php
/**
 * Card de escritório.
 *
 * @package Tema_Base
 */

$tb_office_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_office    = get_post( $tb_office_id );

if ( ! $tb_office ) {
	return;
}

$tb_address = tb_post_meta( $tb_office_id, '_tb_address' );

$tb_heading_level = isset( $args['heading_level'] ) ? max( 2, min( 6, (int) $args['heading_level'] ) ) : 3;
$tb_heading_tag   = 'h' . $tb_heading_level;
?>
<article class="office-card">
	<a class="office-card__link" href="<?php echo esc_url( get_permalink( $tb_office ) ); ?>">
		<?php echo tb_card_image( $tb_office_id, 'tb-card', 'office-card__image', '(max-width: 700px) 90vw, 45vw' ); ?>
		<div class="office-card__body">
			<p class="tb-kicker"><?php esc_html_e( 'Escritório', 'tema-base' ); ?></p>
			<<?php echo esc_html( $tb_heading_tag ); ?> class="office-card__title"><?php echo esc_html( get_the_title( $tb_office ) ); ?></<?php echo esc_html( $tb_heading_tag ); ?>>
			<?php if ( $tb_address ) : ?>
				<p class="office-card__address"><?php echo nl2br( esc_html( $tb_address ) ); ?></p>
			<?php endif; ?>
			<span class="area-card__more"><?php esc_html_e( 'Ver unidade', 'tema-base' ); ?> <span aria-hidden="true">→</span></span>
		</div>
	</a>
</article>
