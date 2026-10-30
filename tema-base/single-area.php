<?php
/**
 * Área de atuação individual.
 *
 * @package Tema_Base
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$tb_excerpt = has_excerpt() ? get_the_excerpt() : '';
		?>
		<article <?php post_class( 'area-single' ); ?>>
			<header class="content-page__header tb-container">
				<p class="tb-kicker"><?php esc_html_e( 'Área de atuação', 'tema-base' ); ?></p>
				<h1 class="content-page__title"><?php the_title(); ?></h1>
				<?php if ( $tb_excerpt ) : ?>
					<div class="content-archive__description"><?php echo esc_html( $tb_excerpt ); ?></div>
				<?php endif; ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="content-page__figure tb-container">
					<?php the_post_thumbnail( 'tb-slide', array( 'class' => 'content-page__image', 'loading' => 'eager' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="content-page__body tb-container">
				<div class="content-page__content entry-content">
					<?php the_content(); ?>
				</div>
				<p class="area-single__cta">
					<a class="tb-button" href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Fale com a equipe', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
				</p>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
