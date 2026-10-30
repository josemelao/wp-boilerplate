<?php
/**
 * Rodapé do site.
 *
 * @package Tema_Base
 */

$tb_social_links = tb_get_social_links();
$tb_offices      = tb_get_office_links();
$tb_subscribe    = tb_opt( 'subscribe_url' );
$tb_subscribe_l  = tb_opt( 'subscribe_label' );
$tb_footer_note  = tb_opt( 'footer_note' );
$tb_contact_url  = home_url( '/contato/' );
?>
</main>

<footer class="site-footer">
	<div class="tb-container site-footer__inner">
		<div class="site-footer__top">
			<p class="site-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			</p>
			<div class="site-footer__search">
				<?php get_search_form(); ?>
			</div>
		</div>

		<div class="site-footer__main">
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Menu do rodapé', 'tema-base' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'site-footer__list',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( $tb_offices ) : ?>
				<section class="site-footer__offices" aria-labelledby="tb-escritorios">
					<h2 class="site-footer__heading" id="tb-escritorios"><?php esc_html_e( 'Escritórios', 'tema-base' ); ?></h2>
					<ul class="site-footer__offices-list">
						<?php foreach ( $tb_offices as $tb_office ) : ?>
							<li>
								<?php if ( $tb_office['url'] ) : ?>
									<a href="<?php echo esc_url( $tb_office['url'] ); ?>"><?php echo esc_html( $tb_office['label'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $tb_office['label'] ); ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<div class="site-footer__aside">
				<?php if ( $tb_social_links ) : ?>
					<ul class="site-footer__social">
						<?php foreach ( $tb_social_links as $tb_slug => $tb_link ) : ?>
							<li>
								<a
									class="site-footer__social-link site-footer__social-link--<?php echo esc_attr( $tb_slug ); ?>"
									href="<?php echo esc_url( $tb_link['url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
								>
									<?php echo esc_html( $tb_link['label'] ); ?>
									<span class="screen-reader-text"><?php esc_html_e( '(abre em nova aba)', 'tema-base' ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<a class="tb-button site-footer__contact" href="<?php echo esc_url( $tb_contact_url ); ?>"><?php esc_html_e( 'Fale conosco', 'tema-base' ); ?></a>

				<?php if ( '' !== $tb_subscribe && '' !== $tb_subscribe_l ) : ?>
					<p class="site-footer__subscribe">
						<a class="tb-button" href="<?php echo esc_url( $tb_subscribe ); ?>"><?php echo esc_html( $tb_subscribe_l ); ?></a>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="site-footer__bottom">
			<p class="site-footer__copy">
				<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
				<?php if ( '' !== $tb_footer_note ) : ?>
					<span class="site-footer__note"><?php echo esc_html( $tb_footer_note ); ?></span>
				<?php endif; ?>
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
