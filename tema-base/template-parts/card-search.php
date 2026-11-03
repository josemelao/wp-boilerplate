<?php
/**
 * Resultado da busca global em formato de lista editorial.
 *
 * @package Tema_Base
 */

$tb_result_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_result    = get_post( $tb_result_id );

if ( ! $tb_result ) {
	return;
}

$tb_excerpt = tb_search_excerpt( $tb_result );
$tb_type    = tb_search_type_label( $tb_result );
$tb_date    = 'post' === $tb_result->post_type ? get_the_date( '', $tb_result ) : '';
?>
<article class="search-result">
	<a class="search-result__link" href="<?php echo esc_url( get_permalink( $tb_result ) ); ?>">
		<div class="search-result__meta">
			<span class="search-result__type"><?php echo esc_html( $tb_type ); ?></span>
			<?php if ( '' !== $tb_date ) : ?>
				<time class="search-result__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $tb_result ) ); ?>"><?php echo esc_html( $tb_date ); ?></time>
			<?php endif; ?>
		</div>
		<h2 class="search-result__title"><?php echo esc_html( get_the_title( $tb_result ) ); ?></h2>
		<?php if ( '' !== $tb_excerpt ) : ?>
			<p class="search-result__excerpt"><?php echo esc_html( $tb_excerpt ); ?></p>
		<?php endif; ?>
	</a>
</article>
