<?php
/** Página editorial individual de uma história. */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$tb_image_id = get_post_thumbnail_id();
		$tb_summary  = tb_post_meta( get_the_ID(), '_tb_summary' );
		$tb_related  = tb_post_meta( get_the_ID(), '_tb_link' );
		?>
		<article <?php post_class( 'story-single' ); ?>>
			<header class="story-single__hero">
				<?php if ( $tb_image_id ) : ?>
					<?php echo wp_get_attachment_image( $tb_image_id, 'tb-story', false, array( 'class' => 'story-single__image', 'alt' => '', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '100vw' ) ); ?>
				<?php endif; ?>
				<div class="story-single__scrim" aria-hidden="true"></div>
				<div class="tb-container story-single__hero-content">
					<p class="tb-kicker tb-kicker--light"><?php esc_html_e( 'História', 'tema-base' ); ?></p>
					<h1><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( $tb_summary ) : ?><p class="story-single__lead"><?php echo esc_html( $tb_summary ); ?></p><?php endif; ?>
				</div>
			</header>
			<div class="tb-container story-single__body">
				<div class="story-single__content">
					<?php the_content(); ?>
					<?php if ( $tb_related ) : ?>
						<p class="story-single__related"><a class="tb-button" href="<?php echo esc_url( $tb_related ); ?>"><?php esc_html_e( 'Leia também', 'tema-base' ); ?> <span aria-hidden="true">→</span></a></p>
					<?php endif; ?>
				</div>
				<a class="story-single__back" href="<?php echo esc_url( home_url( '/#historias' ) ); ?>">← <?php esc_html_e( 'Voltar às histórias', 'tema-base' ); ?></a>
			</div>
		</article>
	<?php endwhile; ?>
<?php endif; ?>

<?php get_footer(); ?>
