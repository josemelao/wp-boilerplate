<?php
/**
 * Card de história usado em "Outras histórias".
 *
 * @package Tema_Base
 */

$tb_story_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_story    = get_post( $tb_story_id );

if ( ! $tb_story ) {
	return;
}

$tb_summary = tb_post_meta( $tb_story_id, '_tb_summary' );

if ( '' === $tb_summary ) {
	$tb_summary = wp_trim_words( wp_strip_all_tags( $tb_story->post_content ), 24, '…' );
}
?>
<article class="story-card">
	<a class="story-card__link" href="<?php echo esc_url( get_permalink( $tb_story ) ); ?>">
		<?php echo tb_card_image( $tb_story_id, 'tb-card', 'story-card__image', '(max-width: 700px) 90vw, (max-width: 1000px) 45vw, 30vw' ); ?>
		<div class="story-card__body">
			<p class="tb-kicker"><?php esc_html_e( 'História', 'tema-base' ); ?></p>
			<h3 class="story-card__title"><?php echo esc_html( get_the_title( $tb_story ) ); ?></h3>
			<?php if ( '' !== $tb_summary ) : ?>
				<p class="story-card__summary"><?php echo esc_html( $tb_summary ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
