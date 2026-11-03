<?php
/**
 * Resultados da busca global.
 *
 * @package Tema_Base
 */

get_header();

global $wp_query;

$tb_found   = (int) $wp_query->found_posts;
$tb_term    = get_search_query();
$tb_heading = '' !== $tb_term
	/* translators: %s: termo buscado */
	? sprintf( __( 'Resultados para: %s', 'tema-base' ), $tb_term )
	: __( 'Busca', 'tema-base' );
$tb_count   = sprintf(
	/* translators: %s: número de resultados */
	_n( '%s resultado', '%s resultados', $tb_found, 'tema-base' ),
	number_format_i18n( $tb_found )
);
?>
<section class="content-archive search-results tb-section" aria-labelledby="search-title">
	<header class="content-archive__header tb-container">
		<p class="tb-kicker"><?php esc_html_e( 'Busca', 'tema-base' ); ?></p>
		<h1 class="tb-section__title" id="search-title"><?php echo esc_html( $tb_heading ); ?></h1>
		<p class="content-archive__count"><?php echo esc_html( $tb_count ); ?></p>
	</header>

	<div class="tb-container">
		<?php if ( have_posts() ) : ?>
			<ol class="search-results__list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="search-results__item">
						<?php get_template_part( 'template-parts/card-search', null, array( 'post_id' => get_the_ID() ) ); ?>
					</li>
					<?php
				endwhile;
				?>
			</ol>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( '← Anteriores', 'tema-base' ), 'next_text' => __( 'Próximos →', 'tema-base' ) ) ); ?>
		<?php else : ?>
			<div class="content-archive__empty">
				<p><?php esc_html_e( 'Nenhum conteúdo encontrado. Tente outra busca ou volte à página inicial.', 'tema-base' ); ?></p>
				<?php get_search_form(); ?>
				<a class="tb-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Voltar à página inicial', 'tema-base' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
