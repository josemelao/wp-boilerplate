<?php
/** Três artigos em destaque, completados pelos mais recentes. */

$tb_items = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $tb_items ) ) {
	return;
}
?>
<section class="tb-section insights" aria-labelledby="insights-title">
	<div class="tb-container">
		<div class="insights__heading">
			<div>
				<p class="tb-kicker"><?php esc_html_e( 'Publicações', 'tema-base' ); ?></p>
				<h2 class="tb-section__title" id="insights-title"><?php echo esc_html( tb_opt( 'insights_heading' ) ); ?></h2>
			</div>
			<a class="insights__all" href="<?php echo esc_url( tb_blog_url() ); ?>"><?php esc_html_e( 'Ver todos os artigos', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
		</div>
		<div class="insights__grid">
			<?php foreach ( $tb_items as $tb_item ) : ?>
				<?php get_template_part( 'template-parts/card', 'post', array( 'post_id' => $tb_item->ID ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
