<?php
/** Histórias em uma lista vertical circular com uma faixa ativa integrada. */

$tb_items = isset( $args['items'] ) ? array_values( (array) $args['items'] ) : array();

if ( empty( $tb_items ) ) {
	return;
}

$tb_active_index = count( $tb_items ) - 1;
$tb_visible_start = max( 0, count( $tb_items ) - 4 );
$tb_visible_count = count( $tb_items ) - $tb_visible_start;
$tb_visible_height = ( ( $tb_visible_count - 1 ) * 22 ) + 34;
$tb_top_offset = ( 100 - $tb_visible_height ) / 2;

$tb_story_teaser = static function ( $tb_item ) {
	$summary = tb_post_meta( $tb_item->ID, '_tb_summary' );

	if ( '' !== $summary ) {
		return $summary;
	}

	$content = apply_filters( 'the_content', $tb_item->post_content );

	if ( preg_match( '/<p\b[^>]*>(.*?)<\/p>/is', $content, $matches ) ) {
		$paragraph = trim( wp_strip_all_tags( $matches[1] ) );
		if ( '' !== $paragraph ) {
			return wp_trim_words( $paragraph, 28, '…' );
		}
	}

	return wp_trim_words( wp_strip_all_tags( $tb_item->post_content ), 28, '…' );
};
?>
<section class="stories" id="historias" aria-labelledby="stories-title" data-component="stories">
	<div class="stories__backdrops" aria-hidden="true">
		<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
			<?php $tb_image_id = tb_story_image_id( $tb_item->ID ); ?>
			<div class="stories__backdrop<?php echo $tb_index === $tb_active_index ? ' is-active' : ''; ?><?php echo $tb_image_id ? '' : ' stories__backdrop--fallback'; ?>" data-story-backdrop="<?php echo esc_attr( $tb_item->ID ); ?>">
				<?php if ( $tb_image_id ) : ?>
					<?php echo wp_get_attachment_image( $tb_image_id, 'tb-story', false, array( 'class' => 'stories__image', 'alt' => '', 'loading' => 'lazy', 'sizes' => '100vw' ) ); ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
		<div class="stories__scrim"></div>
	</div>
	<div class="tb-container stories__layout">
		<div class="stories__intro">
			<h2 class="stories__heading" id="stories-title"><?php echo esc_html( tb_opt( 'stories_heading' ) ); ?></h2>
		</div>
	</div>
	<div class="stories__list-wrap">
		<ol class="stories__list" aria-label="<?php esc_attr_e( 'Histórias', 'tema-base' ); ?>">
			<?php foreach ( $tb_items as $tb_index => $tb_item ) : ?>
				<?php
				$tb_active      = $tb_index === $tb_active_index;
				$tb_visible     = $tb_index >= $tb_visible_start;
				$tb_slot        = $tb_index - $tb_visible_start;
				$tb_top         = $tb_top_offset + ( $tb_slot * 22 );
				$tb_height      = $tb_active ? 34 : 22;
				$tb_left        = $tb_active ? 50 : 53.2;
				$tb_width       = $tb_active ? 50 : 26;
				$tb_inline_style = sprintf(
					'--story-top:%s%%;--story-height:%d%%;--story-left:%s%%;--story-width:%d%%;',
					number_format( $tb_top, 2, '.', '' ),
					$tb_height,
					number_format( $tb_left, 1, '.', '' ),
					$tb_width
				);
				?>
				<li class="stories__entry<?php echo $tb_active ? ' is-active' : ''; ?><?php echo $tb_visible ? ' is-visible' : ''; ?>" data-story-entry data-story-id="<?php echo esc_attr( $tb_item->ID ); ?>" data-story-title="<?php echo esc_attr( get_the_title( $tb_item ) ); ?>" style="<?php echo esc_attr( $tb_inline_style ); ?>" aria-hidden="<?php echo $tb_visible ? 'false' : 'true'; ?>"<?php echo $tb_visible ? '' : ' inert'; ?>>
					<a class="stories__title" data-story-link href="<?php echo esc_url( get_permalink( $tb_item ) ); ?>"<?php echo $tb_active ? ' aria-current="true"' : ' hidden'; ?>><?php echo esc_html( get_the_title( $tb_item ) ); ?></a>
					<span class="stories__title" data-story-label<?php echo $tb_active ? ' hidden' : ''; ?>><?php echo esc_html( get_the_title( $tb_item ) ); ?></span>
					<p class="stories__summary" data-story-summary aria-hidden="<?php echo $tb_active ? 'false' : 'true'; ?>"><?php echo esc_html( $tb_story_teaser( $tb_item ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( count( $tb_items ) > 1 ) : ?>
			<div class="stories__controls" role="group" aria-label="<?php esc_attr_e( 'Navegar pelas histórias', 'tema-base' ); ?>">
				<button type="button" data-story-down aria-label="<?php esc_attr_e( 'Mover para a próxima história', 'tema-base' ); ?>">
					<svg class="stories__arrow-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
				<button type="button" data-story-up aria-label="<?php esc_attr_e( 'Mover para a história anterior', 'tema-base' ); ?>">
					<svg class="stories__arrow-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M6 15l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>
		<?php endif; ?>
	</div>
	<?php
	$tb_active_item    = $tb_items[ $tb_active_index ];
	$tb_active_summary = $tb_story_teaser( $tb_active_item );
	?>
	<div class="stories__panel" data-story-panel>
		<a class="stories__panel-title" data-story-panel-link href="<?php echo esc_url( get_permalink( $tb_active_item ) ); ?>" aria-current="true"><?php echo esc_html( get_the_title( $tb_active_item ) ); ?></a>
		<p class="stories__panel-summary" data-story-panel-summary><?php echo esc_html( $tb_active_summary ); ?></p>
	</div>
	<div class="screen-reader-text" aria-live="polite" data-story-announcement></div>
</section>
