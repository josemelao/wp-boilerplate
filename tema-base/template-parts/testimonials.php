<?php
/** Depoimentos e reconhecimentos com alternância por fade. */

$tb_items = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $tb_items ) ) {
	return;
}

$tb_count = count( $tb_items );
?>
<section class="tb-section testimonials" role="region" aria-roledescription="<?php esc_attr_e( 'carrossel', 'tema-base' ); ?>" aria-labelledby="testimonials-title" data-component="testimonials" data-interval="8000">
	<div class="tb-container testimonials__inner">
		<p class="tb-kicker"><?php esc_html_e( 'Reconhecimento', 'tema-base' ); ?></p>
		<h2 class="tb-section__title" id="testimonials-title"><?php esc_html_e( 'O que dizem sobre nós', 'tema-base' ); ?></h2>
		<div class="testimonials__stage-wrap">
			<?php if ( $tb_count > 1 ) : ?><button class="testimonials__arrow" type="button" data-slide-prev aria-label="<?php esc_attr_e( 'Depoimento anterior', 'tema-base' ); ?>">‹</button><?php endif; ?>
			<div class="testimonials__stage" aria-live="off">
				<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
					<?php
					$tb_active = 0 === $tb_index;
					$tb_quote  = tb_post_meta( $tb_item->ID, '_tb_quote' );
					$tb_source = tb_post_meta( $tb_item->ID, '_tb_source' );
					?>
					<figure class="testimonials__item<?php echo $tb_active ? ' is-active' : ''; ?>" data-slide aria-hidden="<?php echo $tb_active ? 'false' : 'true'; ?>"<?php echo $tb_active ? '' : ' inert'; ?>>
						<blockquote class="testimonials__quote">
							<p class="testimonials__headline"><?php echo esc_html( get_the_title( $tb_item ) ); ?></p>
							<?php if ( $tb_quote ) : ?><p><?php echo esc_html( $tb_quote ); ?></p><?php endif; ?>
						</blockquote>
						<?php if ( $tb_source ) : ?><figcaption class="testimonials__source"><?php echo esc_html( $tb_source ); ?></figcaption><?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
			<?php if ( $tb_count > 1 ) : ?><button class="testimonials__arrow" type="button" data-slide-next aria-label="<?php esc_attr_e( 'Próximo depoimento', 'tema-base' ); ?>">›</button><?php endif; ?>
		</div>
		<?php if ( $tb_count > 1 ) : ?>
			<div class="testimonials__controls">
				<div class="testimonials__pagination" role="group" aria-label="<?php esc_attr_e( 'Escolher depoimento', 'tema-base' ); ?>">
					<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
						<button class="testimonials__bullet" type="button" data-slide-to="<?php echo esc_attr( $tb_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Mostrar depoimento %d', 'tema-base' ), $tb_index + 1 ) ); ?>" aria-current="<?php echo 0 === $tb_index ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button class="testimonials__toggle" type="button" data-rotation-toggle aria-label="<?php esc_attr_e( 'Pausar apresentação', 'tema-base' ); ?>" aria-pressed="false">
					<span data-toggle-label><?php esc_html_e( 'Pausar', 'tema-base' ); ?></span>
				</button>
			</div>
		<?php endif; ?>
	</div>
</section>
