<?php
/** Perfil individual de sócio. */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$tb_role     = tb_post_meta( get_the_ID(), '_tb_role' );
		$tb_email    = tb_post_meta( get_the_ID(), '_tb_email' );
		$tb_phone    = tb_post_meta( get_the_ID(), '_tb_phone' );
		$tb_linkedin = tb_post_meta( get_the_ID(), '_tb_linkedin' );
		$tb_phone_uri = preg_replace( '/[^0-9+]/', '', $tb_phone );
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
						<p class="tb-kicker"><?php esc_html_e( 'Sócio', 'tema-base' ); ?></p>
						<h1 class="socio-profile__name"><?php the_title(); ?></h1>
						<?php if ( $tb_role ) : ?><p class="socio-profile__role"><?php echo esc_html( $tb_role ); ?></p><?php endif; ?>
					</header>
					<?php if ( $tb_email || $tb_phone || $tb_linkedin ) : ?>
						<ul class="socio-profile__contact" aria-label="<?php esc_attr_e( 'Contato', 'tema-base' ); ?>">
							<?php if ( $tb_email ) : ?><li><a href="mailto:<?php echo esc_attr( sanitize_email( $tb_email ) ); ?>"><?php echo esc_html( $tb_email ); ?></a></li><?php endif; ?>
							<?php if ( $tb_phone && $tb_phone_uri ) : ?><li><a href="tel:<?php echo esc_attr( $tb_phone_uri ); ?>"><?php echo esc_html( $tb_phone ); ?></a></li><?php endif; ?>
							<?php if ( $tb_linkedin ) : ?><li><a href="<?php echo esc_url( $tb_linkedin ); ?>" target="_blank" rel="noopener noreferrer">LinkedIn <span aria-hidden="true">↗</span><span class="screen-reader-text"><?php esc_html_e( '(abre em nova aba)', 'tema-base' ); ?></span></a></li><?php endif; ?>
						</ul>
					<?php endif; ?>
					<div class="socio-profile__bio entry-content"><?php the_content(); ?></div>
					<a class="socio-profile__back" href="<?php echo esc_url( get_post_type_archive_link( 'socio' ) ); ?>">← <?php esc_html_e( 'Voltar aos sócios', 'tema-base' ); ?></a>
				</div>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
