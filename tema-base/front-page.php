<?php
/**
 * Página inicial: cada seção desaparece quando não há conteúdo publicado.
 *
 * @package Tema_Base
 */

get_header();

$tb_home_items = array(
	'banners'     => tb_query( 'banner' )->posts,
	'destaques'   => tb_query( 'destaque' )->posts,
	'depoimentos' => tb_query( 'depoimento' )->posts,
	'historias'   => tb_query( 'historia', array( 'order' => 'DESC' ) )->posts,
	'artigos'     => tb_featured_posts( 3 ),
);

if ( empty( $tb_home_items['banners'] ) ) :
	?>
	<h1 class="screen-reader-text"><?php bloginfo( 'name' ); ?></h1>
	<?php
endif;

get_template_part( 'template-parts/hero', null, array( 'items' => $tb_home_items['banners'] ) );
get_template_part( 'template-parts/carousel-featured', null, array( 'items' => $tb_home_items['destaques'] ) );
get_template_part( 'template-parts/testimonials', null, array( 'items' => $tb_home_items['depoimentos'] ) );
get_template_part( 'template-parts/stories', null, array( 'items' => $tb_home_items['historias'] ) );
get_template_part( 'template-parts/insights', null, array( 'items' => $tb_home_items['artigos'] ) );

get_footer();
