<?php
/**
 * Template de fallback (blog, categorias, busca).
 *
 * @package Tema_Base
 */

get_header();
?>

<div class="tb-container tb-section">
	<?php if ( have_posts() ) : ?>
		<?php if ( is_home() && ! is_front_page() ) : ?>
			<h1 class="tb-section__title"><?php single_post_title(); ?></h1>
		<?php endif; ?>

		<div class="tb-posts">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'tb-posts__item' ); ?>>
					<h2 class="tb-posts__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
					<?php the_excerpt(); ?>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nenhum conteúdo encontrado.', 'tema-base' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
