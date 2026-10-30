<?php
/** Página institucional. */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'content-page' ); ?>>
			<header class="content-page__header tb-container">
				<p class="tb-kicker"><?php esc_html_e( 'Informações', 'tema-base' ); ?></p>
				<h1 class="content-page__title"><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="content-page__figure tb-container">
					<?php the_post_thumbnail( 'tb-card', array( 'class' => 'content-page__image', 'loading' => 'eager' ) ); ?>
				</figure>
			<?php endif; ?>
			<div class="content-page__body tb-container">
				<div class="content-page__content entry-content">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<nav class="content-page__pages" aria-label="' . esc_attr__( 'Páginas do conteúdo', 'tema-base' ) . '">', 'after' => '</nav>' ) ); ?>
				</div>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
