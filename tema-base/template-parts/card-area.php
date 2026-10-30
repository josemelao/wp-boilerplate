<?php
/**
 * Card de área de atuação.
 *
 * @package Tema_Base
 */

$tb_area_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_area    = get_post( $tb_area_id );

if ( ! $tb_area ) {
	return;
}

$tb_excerpt = has_excerpt( $tb_area )
	? get_the_excerpt( $tb_area )
	: wp_trim_words( wp_strip_all_tags( $tb_area->post_content ), 20, '…' );

$tb_heading_level = isset( $args['heading_level'] ) ? max( 2, min( 6, (int) $args['heading_level'] ) ) : 3;
$tb_heading_tag   = 'h' . $tb_heading_level;
?>
<article class="area-card">
	<a class="area-card__link" href="<?php echo esc_url( get_permalink( $tb_area ) ); ?>">
		<?php if ( has_post_thumbnail( $tb_area ) ) : ?>
			<?php echo get_the_post_thumbnail( $tb_area, 'tb-card', array( 'class' => 'area-card__image', 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 90vw, (max-width: 1000px) 45vw, 30vw' ) ); ?>
		<?php endif; ?>
		<div class="area-card__body">
			<p class="tb-kicker"><?php esc_html_e( 'Área de atuação', 'tema-base' ); ?></p>
			<<?php echo esc_html( $tb_heading_tag ); ?> class="area-card__title"><?php echo esc_html( get_the_title( $tb_area ) ); ?></<?php echo esc_html( $tb_heading_tag ); ?>>
			<?php if ( $tb_excerpt ) : ?>
				<p class="area-card__excerpt"><?php echo esc_html( $tb_excerpt ); ?></p>
			<?php endif; ?>
			<span class="area-card__more"><?php esc_html_e( 'Conheça a área', 'tema-base' ); ?> <span aria-hidden="true">→</span></span>
		</div>
	</a>
</article>
