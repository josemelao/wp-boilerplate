<?php
/** Hero rotativo. */

$tb_items = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $tb_items ) ) {
	return;
}

$tb_count = count( $tb_items );
?>
<section class="hero" role="region" aria-roledescription="<?php esc_attr_e( 'carrossel', 'tema-base' ); ?>" aria-label="<?php esc_attr_e( 'Destaques principais', 'tema-base' ); ?>" data-component="hero" data-interval="6000">
	<div class="hero__slides">
		<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
			<?php
			$tb_active    = 0 === $tb_index;
			$tb_image_id  = get_post_thumbnail_id( $tb_item );
			$tb_label     = tb_post_meta( $tb_item->ID, '_tb_label' );
			$tb_text      = tb_post_meta( $tb_item->ID, '_tb_text' );
			$tb_link      = tb_post_meta( $tb_item->ID, '_tb_link' );
			$tb_link_label = tb_post_meta( $tb_item->ID, '_tb_link_label' );
			$tb_heading_tag = $tb_active ? 'h1' : 'h2';
			?>
			<article class="hero__slide<?php echo $tb_active ? ' is-active' : ''; ?>" data-slide aria-hidden="<?php echo $tb_active ? 'false' : 'true'; ?>"<?php echo $tb_active ? '' : ' inert'; ?>>
				<?php if ( $tb_image_id ) : ?>
					<?php
					$tb_image_args = array(
						'class'   => 'hero__image',
						'alt'     => '',
						'loading' => $tb_active ? 'eager' : 'lazy',
						'sizes'   => '100vw',
					);
					if ( $tb_active ) {
						$tb_image_args['fetchpriority'] = 'high';
					}
					echo wp_get_attachment_image( $tb_image_id, 'tb-hero', false, $tb_image_args );
					?>
				<?php endif; ?>
				<div class="hero__scrim" aria-hidden="true"></div>
				<div class="tb-container hero__content">
					<?php if ( '' !== $tb_label ) : ?><p class="hero__eyebrow"><?php echo esc_html( $tb_label ); ?></p><?php endif; ?>
					<<?php echo esc_html( $tb_heading_tag ); ?> class="hero__title"><?php echo esc_html( get_the_title( $tb_item ) ); ?></<?php echo esc_html( $tb_heading_tag ); ?>>
					<?php if ( '' !== $tb_text ) : ?><p class="hero__text"><?php echo esc_html( $tb_text ); ?></p><?php endif; ?>
					<?php if ( '' !== $tb_link ) : ?>
						<a class="tb-button hero__button" href="<?php echo esc_url( $tb_link ); ?>"><?php echo esc_html( $tb_link_label ); ?></a>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
	<?php if ( $tb_count > 1 ) : ?>
		<div class="hero__controls tb-container">
			<div class="hero__pagination" role="group" aria-label="<?php esc_attr_e( 'Escolher destaque', 'tema-base' ); ?>">
				<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
					<button class="hero__bullet" type="button" data-slide-to="<?php echo esc_attr( $tb_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Mostrar destaque %d', 'tema-base' ), $tb_index + 1 ) ); ?>" aria-current="<?php echo 0 === $tb_index ? 'true' : 'false'; ?>"></button>
				<?php endforeach; ?>
			</div>
			<button class="hero__toggle" type="button" data-rotation-toggle aria-label="<?php esc_attr_e( 'Pausar apresentação', 'tema-base' ); ?>" aria-pressed="false">
				<span data-toggle-label><?php esc_html_e( 'Pausar', 'tema-base' ); ?></span>
			</button>
		</div>
	<?php endif; ?>
</section>
