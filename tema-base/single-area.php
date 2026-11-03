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
		$tb_excerpt  = has_excerpt() ? get_the_excerpt() : '';
		$tb_services = tb_lines( tb_post_meta( get_the_ID(), '_tb_services' ) );
		$tb_sectors  = tb_lines( tb_post_meta( get_the_ID(), '_tb_sectors' ) );
		$tb_team     = tb_get_area_socios( get_the_ID() );
		?>
		<article <?php post_class( 'area-single' ); ?>>
			<header class="content-page__header tb-container">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
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

				<?php if ( $tb_services ) : ?>
					<section class="area-services" aria-labelledby="area-services-title">
						<h2 class="area-section__title" id="area-services-title"><?php esc_html_e( 'Como atuamos', 'tema-base' ); ?></h2>
						<ul class="area-services__list">
							<?php foreach ( $tb_services as $tb_service ) : ?>
								<li><?php echo esc_html( $tb_service ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( $tb_sectors ) : ?>
					<section class="area-sectors" aria-labelledby="area-sectors-title">
						<h2 class="area-section__title" id="area-sectors-title"><?php esc_html_e( 'Setores', 'tema-base' ); ?></h2>
						<ul class="area-sectors__list">
							<?php foreach ( $tb_sectors as $tb_sector ) : ?>
								<li><span class="area-sectors__chip"><?php echo esc_html( $tb_sector ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</div>

			<?php if ( $tb_team ) : ?>
				<section class="area-team tb-section" aria-labelledby="area-team-title">
					<div class="tb-container">
						<h2 class="tb-section__title" id="area-team-title"><?php esc_html_e( 'Sócios da área', 'tema-base' ); ?></h2>
						<div class="socio-grid">
							<?php foreach ( $tb_team as $tb_socio ) : ?>
								<?php get_template_part( 'template-parts/card-socio', null, array( 'post_id' => $tb_socio->ID ) ); ?>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<section class="area-cta" aria-labelledby="area-cta-title">
				<div class="tb-container area-cta__inner">
					<h2 class="area-cta__title" id="area-cta-title"><?php esc_html_e( 'Precisa de apoio nesta área?', 'tema-base' ); ?></h2>
					<a class="tb-button area-cta__button" href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Fale com a equipe', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
				</div>
			</section>

			<?php
			$tb_related = tb_query(
				'area',
				array(
					'posts_per_page' => 3,
					'post__not_in'   => array( get_the_ID() ),
				)
			);

			if ( $tb_related->have_posts() ) :
				?>
				<section class="area-related tb-section" aria-labelledby="area-related-title">
					<div class="tb-container">
						<h2 class="tb-section__title" id="area-related-title"><?php esc_html_e( 'Outras áreas', 'tema-base' ); ?></h2>
						<div class="areas__grid">
							<?php
							while ( $tb_related->have_posts() ) :
								$tb_related->the_post();
								get_template_part( 'template-parts/card', 'area', array( 'post_id' => get_the_ID() ) );
							endwhile;
							?>
						</div>
					</div>
				</section>
				<?php
			endif;
			wp_reset_postdata();
			?>
		</article>
		<?php
	endwhile;
endif;

get_footer();
