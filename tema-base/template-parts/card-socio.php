<?php
/**
 * Card de sócio usado em seções editoriais.
 *
 * @package Tema_Base
 */

$tb_socio_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_socio    = get_post( $tb_socio_id );

if ( ! $tb_socio ) {
	return;
}

$tb_role          = tb_post_meta( $tb_socio_id, '_tb_role' );
$tb_heading_level = isset( $args['heading_level'] ) ? max( 2, min( 6, (int) $args['heading_level'] ) ) : 3;
$tb_heading_tag   = 'h' . $tb_heading_level;
?>
<article class="socio-card">
	<a class="socio-card__link" href="<?php echo esc_url( get_permalink( $tb_socio ) ); ?>">
		<?php echo tb_card_image( $tb_socio_id, 'tb-socio', 'socio-card__image', '(max-width: 700px) 90vw, (max-width: 1000px) 45vw, 30vw' ); ?>
		<div class="socio-card__body">
			<?php if ( $tb_role ) : ?><p class="tb-kicker socio-card__role"><?php echo esc_html( $tb_role ); ?></p><?php endif; ?>
			<<?php echo esc_html( $tb_heading_tag ); ?> class="socio-card__name"><?php echo esc_html( get_the_title( $tb_socio ) ); ?></<?php echo esc_html( $tb_heading_tag ); ?>>
			<span class="socio-card__more"><?php esc_html_e( 'Ver perfil', 'tema-base' ); ?> <span aria-hidden="true">→</span></span>
		</div>
	</a>
</article>
