<?php
/** Card compacto de post usado na home. */

$tb_post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$tb_post    = get_post( $tb_post_id );

if ( ! $tb_post ) {
	return;
}

$tb_heading_level = isset( $args['heading_level'] ) ? max( 2, min( 6, (int) $args['heading_level'] ) ) : 3;
$tb_heading_tag   = 'h' . $tb_heading_level;

$tb_categories = get_the_category( $tb_post_id );
?>
<article class="post-card">
	<a class="post-card__link" href="<?php echo esc_url( get_permalink( $tb_post ) ); ?>">
		<?php if ( has_post_thumbnail( $tb_post ) ) : ?>
			<?php echo get_the_post_thumbnail( $tb_post, 'tb-card', array( 'class' => 'post-card__image', 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 90vw, (max-width: 1000px) 45vw, 30vw' ) ); ?>
		<?php endif; ?>
		<div class="post-card__body">
			<?php if ( ! empty( $tb_categories ) ) : ?><p class="tb-kicker post-card__category"><?php echo esc_html( $tb_categories[0]->name ); ?></p><?php endif; ?>
			<<?php echo esc_html( $tb_heading_tag ); ?> class="post-card__title"><?php echo esc_html( get_the_title( $tb_post ) ); ?></<?php echo esc_html( $tb_heading_tag ); ?>>
			<?php if ( has_excerpt( $tb_post ) ) : ?><p class="post-card__excerpt"><?php echo esc_html( get_the_excerpt( $tb_post ) ); ?></p><?php endif; ?>
			<time class="post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $tb_post ) ); ?>"><?php echo esc_html( get_the_date( '', $tb_post ) ); ?></time>
		</div>
	</a>
</article>
