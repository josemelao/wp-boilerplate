<?php
/**
 * Página Contato: conteúdo, canais e escritórios.
 *
 * O formulário fica pendente de decisão do cliente.
 *
 * @package Tema_Base
 */

get_header();
?>
<article <?php post_class( 'content-page contact-page' ); ?>>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<header class="content-page__header tb-container">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<p class="tb-kicker"><?php esc_html_e( 'Contato', 'tema-base' ); ?></p>
				<h1 class="content-page__title"><?php the_title(); ?></h1>
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
			</div>
			<?php
		endwhile;
	endif;
	?>

	<?php
	$tb_offices = get_posts(
		array(
			'post_type'      => 'escritorio',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	if ( $tb_offices ) :
		?>
		<section class="contact-offices tb-section" aria-labelledby="contact-offices-title">
			<div class="tb-container">
				<h2 class="tb-section__title" id="contact-offices-title"><?php esc_html_e( 'Escritórios', 'tema-base' ); ?></h2>
				<div class="offices__grid">
					<?php foreach ( $tb_offices as $tb_office ) : ?>
						<?php get_template_part( 'template-parts/card-office-contact', null, array( 'post_id' => $tb_office->ID ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</article>
<?php
get_footer();
