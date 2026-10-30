<?php
/** Artigo individual do blog. */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$tb_categories = get_the_category();
		?>
		<article <?php post_class( 'post-single' ); ?>>
			<header class="post-single__header tb-container">
				<?php if ( $tb_categories ) : ?>
					<p class="tb-kicker post-single__category"><?php echo esc_html( $tb_categories[0]->name ); ?></p>
				<?php endif; ?>
				<h1 class="post-single__title"><?php the_title(); ?></h1>
				<time class="post-single__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="post-single__figure tb-container">
					<?php the_post_thumbnail( 'tb-card', array( 'class' => 'post-single__image', 'fetchpriority' => 'high', 'loading' => 'eager' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="post-single__body tb-container">
				<div class="post-single__content entry-content">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<nav class="post-single__pages" aria-label="' . esc_attr__( 'Páginas do artigo', 'tema-base' ) . '">', 'after' => '</nav>' ) ); ?>
				</div>
				<div class="post-single__footer">
					<a class="tb-button" href="<?php echo esc_url( tb_blog_url() ); ?>">← <?php esc_html_e( 'Voltar aos artigos', 'tema-base' ); ?></a>
					<?php the_post_navigation( array( 'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Anterior', 'tema-base' ) . '</span><span class="nav-title">%title</span>', 'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Próximo', 'tema-base' ) . '</span><span class="nav-title">%title</span>' ) ); ?>
				</div>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
