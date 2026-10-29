<?php
/** Histórias relacionadas com imagens em crossfade. */

$tb_items = isset( $args['items'] ) ? (array) $args['items'] : array();

if ( empty( $tb_items ) ) {
	return;
}

$tb_active_index = count( $tb_items ) - 1;
?>
<section class="stories" id="historias" aria-labelledby="stories-title" data-component="stories">
	<div class="stories__backdrops" aria-hidden="true">
		<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
			<?php $tb_image_id = get_post_thumbnail_id( $tb_item ); ?>
			<div class="stories__backdrop<?php echo $tb_index === $tb_active_index ? ' is-active' : ''; ?>" data-story-backdrop="<?php echo esc_attr( $tb_item->ID ); ?>">
				<?php if ( $tb_image_id ) : ?>
					<?php echo wp_get_attachment_image( $tb_image_id, 'tb-story', false, array( 'class' => 'stories__image', 'alt' => '', 'loading' => 'lazy', 'sizes' => '100vw' ) ); ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		<div class="stories__scrim"></div>
	</div>
	<div class="tb-container stories__layout">
		<div class="stories__intro">
			<p class="tb-kicker tb-kicker--light"><?php esc_html_e( 'Nossa trajetória', 'tema-base' ); ?></p>
			<h2 class="stories__heading" id="stories-title"><?php echo esc_html( tb_opt( 'stories_heading' ) ); ?></h2>
		</div>
		<div class="stories__list-wrap">
			<ol class="stories__list" aria-label="<?php esc_attr_e( 'Histórias', 'tema-base' ); ?>">
				<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
					<?php $tb_active = $tb_index === $tb_active_index; ?>
					<li class="stories__entry<?php echo $tb_active ? ' is-active' : ''; ?>" data-story-entry data-story-id="<?php echo esc_attr( $tb_item->ID ); ?>" data-story-title="<?php echo esc_attr( get_the_title( $tb_item ) ); ?>" data-story-summary="<?php echo esc_attr( tb_post_meta( $tb_item->ID, '_tb_summary' ) ); ?>">
						<a class="stories__title" data-story-link href="<?php echo esc_url( get_permalink( $tb_item ) ); ?>"<?php echo $tb_active ? ' aria-current="true"' : ' hidden'; ?>><?php echo esc_html( get_the_title( $tb_item ) ); ?></a>
						<button class="stories__title" type="button" data-story-select aria-pressed="false"<?php echo $tb_active ? ' hidden' : ''; ?>><?php echo esc_html( get_the_title( $tb_item ) ); ?></button>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php if ( count( $tb_items ) > 1 ) : ?>
				<div class="stories__controls">
					<button type="button" data-story-up aria-label="<?php esc_attr_e( 'Mover para a história anterior', 'tema-base' ); ?>">↑</button>
					<button type="button" data-story-down aria-label="<?php esc_attr_e( 'Mover para a próxima história', 'tema-base' ); ?>">↓</button>
				</div>
			<?php endif; ?>
		</div>
		<div class="stories__detail" data-story-detail>
			<h3 data-story-heading><?php echo esc_html( get_the_title( $tb_items[ $tb_active_index ] ) ); ?></h3>
			<p data-story-summary><?php echo esc_html( tb_post_meta( $tb_items[ $tb_active_index ]->ID, '_tb_summary' ) ); ?></p>
		</div>
	</div>
	<div class="screen-reader-text" aria-live="polite" data-story-announcement></div>
</section>
