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
		$tb_address  = tb_post_meta( get_the_ID(), '_tb_address' );
		$tb_phone    = tb_post_meta( get_the_ID(), '_tb_phone' );
		$tb_email    = tb_post_meta( get_the_ID(), '_tb_email' );
		$tb_maps_url = tb_post_meta( get_the_ID(), '_tb_maps_url' );
		$tb_phone_uri = preg_replace( '/[^0-9+]/', '', $tb_phone );
		?>
		<article <?php post_class( 'office-single' ); ?>>
			<div class="tb-container office-single__layout">
				<div class="office-single__main">
					<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
					<p class="tb-kicker"><?php esc_html_e( 'Escritório', 'tema-base' ); ?></p>
					<h1 class="content-page__title"><?php the_title(); ?></h1>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
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
		</article>
		<?php
	endwhile;
endif;

get_footer();
