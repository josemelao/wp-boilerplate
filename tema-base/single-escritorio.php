<?php
/**
 * Escritório individual.
 *
 * @package Tema_Base
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$tb_address   = tb_post_meta( get_the_ID(), '_tb_address' );
		$tb_phone     = tb_post_meta( get_the_ID(), '_tb_phone' );
		$tb_email     = tb_post_meta( get_the_ID(), '_tb_email' );
		$tb_maps_url  = tb_post_meta( get_the_ID(), '_tb_maps_url' );
		$tb_hours     = tb_lines( tb_post_meta( get_the_ID(), '_tb_hours' ) );
		$tb_excerpt   = has_excerpt() ? get_the_excerpt() : '';
		$tb_phone_uri = preg_replace( '/[^0-9+]/', '', $tb_phone );
		?>
		<article <?php post_class( 'office-single' ); ?>>
			<header class="content-page__header tb-container">
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<p class="tb-kicker"><?php esc_html_e( 'Escritório', 'tema-base' ); ?></p>
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

			<div class="tb-container office-single__layout">
				<div class="office-single__main">
					<div class="entry-content">
						<?php the_content(); ?>
					</div>

					<?php if ( $tb_hours ) : ?>
						<section class="office-hours" aria-labelledby="office-hours-title">
							<h2 class="office-hours__title" id="office-hours-title"><?php esc_html_e( 'Horário de atendimento', 'tema-base' ); ?></h2>
							<ul class="office-hours__list">
								<?php foreach ( $tb_hours as $tb_hour ) : ?>
									<li><?php echo esc_html( $tb_hour ); ?></li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>
				</div>

				<aside class="office-single__contact" aria-label="<?php esc_attr_e( 'Contato do escritório', 'tema-base' ); ?>">
					<h2 class="office-single__contact-title"><?php esc_html_e( 'Contato', 'tema-base' ); ?></h2>
					<?php if ( $tb_address ) : ?>
						<address class="office-single__address"><?php echo nl2br( esc_html( $tb_address ) ); ?></address>
					<?php endif; ?>
					<ul class="office-single__contact-list">
						<?php if ( $tb_phone && $tb_phone_uri ) : ?>
							<li><a href="tel:<?php echo esc_attr( $tb_phone_uri ); ?>"><?php echo esc_html( $tb_phone ); ?></a></li>
						<?php endif; ?>
						<?php if ( $tb_email ) : ?>
							<li><a href="mailto:<?php echo esc_attr( sanitize_email( $tb_email ) ); ?>"><?php echo esc_html( $tb_email ); ?></a></li>
						<?php endif; ?>
						<?php if ( $tb_maps_url ) : ?>
							<li><a href="<?php echo esc_url( $tb_maps_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ver no mapa', 'tema-base' ); ?> <span aria-hidden="true">↗</span><span class="screen-reader-text"><?php esc_html_e( '(abre em nova aba)', 'tema-base' ); ?></span></a></li>
						<?php endif; ?>
					</ul>
					<a class="tb-button office-single__cta" href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Fale conosco', 'tema-base' ); ?></a>
				</aside>
			</div>

			<section class="office-cta" aria-labelledby="office-cta-title">
				<div class="tb-container office-cta__inner">
					<h2 class="office-cta__title" id="office-cta-title"><?php esc_html_e( 'Fale com o nosso escritório', 'tema-base' ); ?></h2>
					<a class="tb-button office-cta__button" href="<?php echo esc_url( home_url( '/contato/' ) ); ?>"><?php esc_html_e( 'Fale conosco', 'tema-base' ); ?> <span aria-hidden="true">→</span></a>
				</div>
			</section>

			<?php
			$tb_related = tb_query(
				'escritorio',
				array(
					'posts_per_page' => 3,
					'post__not_in'   => array( get_the_ID() ),
				)
			);

			if ( $tb_related->have_posts() ) :
				?>
				<section class="office-related tb-section" aria-labelledby="office-related-title">
					<div class="tb-container">
						<h2 class="tb-section__title" id="office-related-title"><?php esc_html_e( 'Outros escritórios', 'tema-base' ); ?></h2>
						<div class="offices__grid">
							<?php
							while ( $tb_related->have_posts() ) :
								$tb_related->the_post();
								get_template_part( 'template-parts/card', 'office', array( 'post_id' => get_the_ID() ) );
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
