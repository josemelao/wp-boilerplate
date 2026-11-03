<?php
/**
 * Página Sobre: conteúdo, time e escritórios.
 *
 * @package Tema_Base
 */

get_header();
?>
<article <?php post_class( 'content-page about-page' ); ?>>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<header class="content-page__header tb-container">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<p class="tb-kicker"><?php esc_html_e( 'Sobre', 'tema-base' ); ?></p>
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
	$tb_team = get_posts(
		array(
			'post_type'      => 'socio',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	if ( $tb_team ) :
		?>
		<section class="about-team tb-section" aria-labelledby="about-team-title">
			<div class="tb-container">
				<h2 class="tb-section__title" id="about-team-title"><?php esc_html_e( 'Nosso time', 'tema-base' ); ?></h2>
				<div class="socio-grid">
					<?php foreach ( $tb_team as $tb_socio ) : ?>
						<?php get_template_part( 'template-parts/card-socio', null, array( 'post_id' => $tb_socio->ID ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

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
		<section class="about-offices tb-section" aria-labelledby="about-offices-title">
			<div class="tb-container">
				<h2 class="tb-section__title" id="about-offices-title"><?php esc_html_e( 'Onde estamos', 'tema-base' ); ?></h2>
				<div class="offices__grid">
					<?php foreach ( $tb_offices as $tb_office ) : ?>
						<?php get_template_part( 'template-parts/card-office', null, array( 'post_id' => $tb_office->ID ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="tb-container about-cta">
		<a class="tb-button" href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Fale conosco', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
	</div>
</article>
<?php
get_footer();
