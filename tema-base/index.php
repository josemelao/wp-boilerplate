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
	$tb_heading = sprintf( __( 'Resultados para: %s', 'tema-base' ), get_search_query( true ) );
} elseif ( is_home() && (int) get_option( 'page_for_posts' ) ) {
	$tb_heading = get_the_title( (int) get_option( 'page_for_posts' ) );
} elseif ( is_archive() ) {
	$tb_heading = get_the_archive_title();
} elseif ( is_singular() ) {
	$tb_heading = get_the_title();
} else {
	$tb_heading = get_bloginfo( 'name' );
}
?>

<section class="content-archive tb-section" aria-labelledby="archive-title">
	<header class="content-archive__header tb-container">
		<p class="tb-kicker">
			<?php
			if ( is_search() ) {
				esc_html_e( 'Busca', 'tema-base' );
			} else {
				esc_html_e( 'Insights', 'tema-base' );
			}
			?>
		</p>
		<h1 class="tb-section__title" id="archive-title"><?php echo wp_kses_post( $tb_heading ); ?></h1>
		<?php if ( is_category() || is_tag() || is_tax() ) : ?>
			<?php if ( get_the_archive_description() ) : ?>
				<div class="content-archive__description"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
			<?php endif; ?>
		<?php endif; ?>
	</header>

	<div class="tb-container">
		<?php if ( have_posts() ) : ?>
			<div class="insights__grid content-archive__grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card-post', null, array( 'post_id' => get_the_ID() ) );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( '← Anteriores', 'tema-base' ), 'next_text' => __( 'Próximos →', 'tema-base' ) ) ); ?>
		<?php else : ?>
			<div class="content-archive__empty">
				<p><?php esc_html_e( 'Nenhum conteúdo encontrado. Tente outra busca ou volte aos artigos.', 'tema-base' ); ?></p>
				<?php get_search_form(); ?>
				<a class="tb-button" href="<?php echo esc_url( tb_blog_url() ); ?>"><?php esc_html_e( 'Ver todos os artigos', 'tema-base' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
