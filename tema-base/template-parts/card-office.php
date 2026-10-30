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
?>
<article class="office-card">
	<a class="office-card__link" href="<?php echo esc_url( get_permalink( $tb_office ) ); ?>">
		<?php if ( has_post_thumbnail( $tb_office ) ) : ?>
			<?php echo get_the_post_thumbnail( $tb_office, 'tb-card', array( 'class' => 'office-card__image', 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 90vw, 45vw' ) ); ?>
		<?php endif; ?>
		<div class="office-card__body">
			<p class="tb-kicker"><?php esc_html_e( 'Escritório', 'tema-base' ); ?></p>
			<h3 class="office-card__title"><?php echo esc_html( get_the_title( $tb_office ) ); ?></h3>
			<?php if ( $tb_address ) : ?>
				<p class="office-card__address"><?php echo nl2br( esc_html( $tb_address ) ); ?></p>
			<?php endif; ?>
			<span class="area-card__more"><?php esc_html_e( 'Ver unidade', 'tema-base' ); ?> <span aria-hidden="true">→</span></span>
		</div>
	</a>
</article>
