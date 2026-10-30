<?php
/**
 * Arquivo de escritórios.
 *
 * @package Tema_Base
 */

get_header();
?>
<section class="content-archive tb-section" aria-labelledby="archive-title">
	<header class="content-archive__header tb-container">
		<p class="tb-kicker"><?php esc_html_e( 'Presença', 'tema-base' ); ?></p>
		<h1 class="tb-section__title" id="archive-title"><?php post_type_archive_title(); ?></h1>
		<p class="content-archive__description"><?php esc_html_e( 'Nossas unidades e os contatos diretos de cada escritório.', 'tema-base' ); ?></p>
	</header>
	<div class="tb-container">
		<?php if ( have_posts() ) : ?>
			<div class="offices__grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card', 'office', array( 'post_id' => get_the_ID() ) );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( '← Anteriores', 'tema-base' ), 'next_text' => __( 'Próximos →', 'tema-base' ) ) ); ?>
		<?php else : ?>
			<p class="content-archive__empty"><?php esc_html_e( 'Nenhum escritório publicado no momento.', 'tema-base' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
