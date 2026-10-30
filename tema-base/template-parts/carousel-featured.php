<?php
/** Carrossel arrastável de destaques. */

$tb_items = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $tb_items ) ) {
	return;
}
?>
<section class="tb-section featured" aria-labelledby="featured-title" data-component="carousel">
	<div class="tb-container featured__heading">
		<div>
			<p class="tb-kicker"><?php esc_html_e( 'Conheça', 'tema-base' ); ?></p>
			<h2 class="tb-section__title" id="featured-title"><?php esc_html_e( 'Destaques', 'tema-base' ); ?></h2>
		</div>
		<?php if ( count( $tb_items ) > 1 ) : ?>
			<div class="featured__arrows">
				<button class="featured__arrow" type="button" data-carousel-prev aria-label="<?php esc_attr_e( 'Destaque anterior', 'tema-base' ); ?>">‹</button>
				<button class="featured__arrow" type="button" data-carousel-next aria-label="<?php esc_attr_e( 'Próximo destaque', 'tema-base' ); ?>">›</button>
			</div>
		<?php endif; ?>
	</div>
	<div class="featured__viewport" data-carousel-viewport tabindex="0" aria-label="<?php esc_attr_e( 'Carrossel de destaques. Use as setas do teclado para navegar.', 'tema-base' ); ?>">
		<ul class="featured__track">
			<?php foreach ( $tb_items as $tb_item ) : ?>
				<?php
				$tb_image_id = get_post_thumbnail_id( $tb_item );
				$tb_link     = tb_post_meta( $tb_item->ID, '_tb_link' );
				$tb_label    = tb_post_meta( $tb_item->ID, '_tb_label' );
				?>
				<li class="featured__item">
					<article class="featured-card">
						<?php if ( $tb_link ) : ?>
							<a class="featured-card__link" href="<?php echo esc_url( $tb_link ); ?>">
						<?php else : ?>
							<div class="featured-card__link">
						<?php endif; ?>
						<?php if ( $tb_image_id ) : ?>
							<?php echo wp_get_attachment_image( $tb_image_id, 'tb-slide', false, array( 'class' => 'featured-card__image', 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 86vw, 70vw' ) ); ?>
						<?php endif; ?>
						<div class="featured-card__content">
							<?php if ( $tb_label ) : ?><p class="tb-kicker featured-card__label"><?php echo esc_html( $tb_label ); ?></p><?php endif; ?>
							<h3 class="featured-card__title"><?php echo esc_html( get_the_title( $tb_item ) ); ?></h3>
							<?php if ( $tb_link ) : ?><span class="featured-card__more"><?php echo esc_html( tb_post_meta( $tb_item->ID, '_tb_link_label' ) ); ?> <span aria-hidden="true">→</span></span><?php endif; ?>
						</div>
						<?php if ( $tb_link ) : ?></a><?php else : ?></div><?php endif; ?>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
