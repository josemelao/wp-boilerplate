<?php
/**
 * Áreas de atuação na home.
 *
 * @package Tema_Base
 */

$tb_areas = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $tb_areas ) ) {
	return;
}

$tb_archive = get_post_type_archive_link( 'area' );
?>
<section class="tb-section areas" aria-labelledby="areas-title">
	<div class="tb-container">
		<div class="insights__heading">
			<div>
				<p class="tb-kicker"><?php esc_html_e( 'Atuação', 'tema-base' ); ?></p>
				<h2 class="tb-section__title" id="areas-title"><?php esc_html_e( 'Áreas de atuação', 'tema-base' ); ?></h2>
			</div>
			<?php if ( $tb_archive ) : ?>
				<a class="insights__all" href="<?php echo esc_url( $tb_archive ); ?>"><?php esc_html_e( 'Ver todas as áreas', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
			<?php endif; ?>
		</div>
		<div class="areas__grid">
			<?php foreach ( $tb_areas as $tb_area ) : ?>
				<?php get_template_part( 'template-parts/card', 'area', array( 'post_id' => $tb_area->ID ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
