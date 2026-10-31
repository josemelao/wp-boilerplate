<?php
/** Perfil individual de sócio. */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$tb_role      = tb_post_meta( get_the_ID(), '_tb_role' );
		$tb_email     = tb_post_meta( get_the_ID(), '_tb_email' );
		$tb_phone     = tb_post_meta( get_the_ID(), '_tb_phone' );
		$tb_linkedin  = tb_post_meta( get_the_ID(), '_tb_linkedin' );
		$tb_oab       = tb_post_meta( get_the_ID(), '_tb_oab' );
		$tb_education = tb_lines( tb_post_meta( get_the_ID(), '_tb_education' ) );
		$tb_languages = tb_lines( tb_post_meta( get_the_ID(), '_tb_languages' ) );
		$tb_phone_uri = preg_replace( '/[^0-9+]/', '', $tb_phone );
		$tb_area_ids  = array_values( array_filter( array_map( 'absint', (array) tb_post_meta( get_the_ID(), '_tb_areas' ) ) ) );
		$tb_areas     = array();

		if ( $tb_area_ids ) {
			$tb_areas = get_posts(
				array(
					'post_type'      => 'area',
					'post_status'    => 'publish',
					'post__in'       => $tb_area_ids,
					'posts_per_page' => -1,
					'orderby'        => 'post__in',
				)
			);
		}

		$tb_has_details = ! empty( $tb_education ) || ! empty( $tb_languages ) || '' !== $tb_oab;
		?>
		<article <?php post_class( 'socio-profile' ); ?>>
			<div class="tb-container socio-profile__layout">
				<div class="socio-profile__portrait">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'tb-socio', array( 'class' => 'socio-profile__image', 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '(max-width: 700px) 90vw, 40vw' ) ); ?>
					<?php else : ?>
						<div class="socio-profile__placeholder" aria-hidden="true"></div>
					<?php endif; ?>
				</div>

				<div class="socio-profile__main">
					<header class="socio-profile__header">
						<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
						<p class="tb-kicker"><?php esc_html_e( 'Sócio', 'tema-base' ); ?></p>
						<h1 class="socio-profile__name"><?php the_title(); ?></h1>
						<?php if ( $tb_role ) : ?><p class="socio-profile__role"><?php echo esc_html( $tb_role ); ?></p><?php endif; ?>
						<?php if ( has_excerpt() ) : ?><p class="socio-profile__lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>

						<?php if ( $tb_areas ) : ?>
							<ul class="socio-profile__areas" aria-label="<?php esc_attr_e( 'Áreas de atuação', 'tema-base' ); ?>">
								<?php foreach ( $tb_areas as $tb_area ) : ?>
									<li>
										<a class="socio-profile__area-link" href="<?php echo esc_url( get_permalink( $tb_area ) ); ?>"><?php echo esc_html( get_the_title( $tb_area ) ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</header>

					<?php if ( $tb_email || $tb_phone || $tb_linkedin ) : ?>
						<ul class="socio-profile__contact" aria-label="<?php esc_attr_e( 'Contato', 'tema-base' ); ?>">
							<?php if ( $tb_email ) : ?><li><a href="mailto:<?php echo esc_attr( sanitize_email( $tb_email ) ); ?>"><?php echo esc_html( $tb_email ); ?></a></li><?php endif; ?>
							<?php if ( $tb_phone && $tb_phone_uri ) : ?><li><a href="tel:<?php echo esc_attr( $tb_phone_uri ); ?>"><?php echo esc_html( $tb_phone ); ?></a></li><?php endif; ?>
							<?php if ( $tb_linkedin ) : ?><li><a href="<?php echo esc_url( $tb_linkedin ); ?>" target="_blank" rel="noopener noreferrer">LinkedIn <span aria-hidden="true">↗</span><span class="screen-reader-text"><?php esc_html_e( '(abre em nova aba)', 'tema-base' ); ?></span></a></li><?php endif; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>

			<div class="tb-container socio-profile__body<?php echo $tb_has_details ? '' : ' socio-profile__body--single'; ?>">
				<div class="socio-profile__bio entry-content"><?php the_content(); ?></div>

				<?php if ( $tb_has_details ) : ?>
					<aside class="socio-profile__details" aria-label="<?php esc_attr_e( 'Perfil profissional', 'tema-base' ); ?>">
						<?php if ( $tb_education ) : ?>
							<section class="socio-profile__detail socio-profile__education" aria-labelledby="socio-education-title">
								<h2 class="socio-profile__detail-title" id="socio-education-title"><?php esc_html_e( 'Formação', 'tema-base' ); ?></h2>
								<ul>
									<?php foreach ( $tb_education as $tb_item ) : ?>
										<li><?php echo esc_html( $tb_item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</section>
						<?php endif; ?>

						<?php if ( $tb_languages ) : ?>
							<section class="socio-profile__detail socio-profile__languages" aria-labelledby="socio-languages-title">
								<h2 class="socio-profile__detail-title" id="socio-languages-title"><?php esc_html_e( 'Idiomas', 'tema-base' ); ?></h2>
								<ul class="socio-profile__chips">
									<?php foreach ( $tb_languages as $tb_item ) : ?>
										<li><span class="socio-profile__chip"><?php echo esc_html( $tb_item ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							</section>
						<?php endif; ?>

						<?php if ( '' !== $tb_oab ) : ?>
							<section class="socio-profile__detail socio-profile__oab" aria-labelledby="socio-oab-title">
								<h2 class="socio-profile__detail-title" id="socio-oab-title"><?php esc_html_e( 'OAB', 'tema-base' ); ?></h2>
								<p><?php echo esc_html( $tb_oab ); ?></p>
							</section>
						<?php endif; ?>
					</aside>
				<?php endif; ?>
			</div>

			<div class="tb-container">
				<a class="socio-profile__back" href="<?php echo esc_url( get_post_type_archive_link( 'socio' ) ); ?>">← <?php esc_html_e( 'Voltar aos sócios', 'tema-base' ); ?></a>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
