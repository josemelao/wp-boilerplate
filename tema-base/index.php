<?php
/**
 * Template de fallback (blog, categorias, busca, arquivos).
 *
 * @package Tema_Base
 */

get_header();

$tb_heading = '';

if ( is_search() ) {
	/* translators: %s: termo buscado */
	$tb_heading = sprintf( __( 'Resultados para: %s', 'tema-base' ), get_search_query() );
} elseif ( is_archive() ) {
	$tb_heading = get_the_archive_title();
} elseif ( is_singular() ) {
	$tb_heading = get_the_title();
} else {
	$tb_heading = get_bloginfo( 'name' );
}
?>

<div class="tb-container tb-section">
	<?php if ( '' !== $tb_heading ) : ?>
		<h1 class="tb-section__title"><?php echo wp_kses_post( $tb_heading ); ?></h1>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
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
