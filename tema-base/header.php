<?php
/**
 * Cabeçalho do site.
 *
 * @package Tema_Base
 */

?><!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.className = document.documentElement.className.replace(/\bno-js\b/, 'js');</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'tema-base' ); ?></a>

<header class="site-header">
	<div class="tb-container site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<p class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				</p>
			<?php endif; ?>
		</div>

		<button
			class="menu-toggle"
			type="button"
			aria-expanded="false"
			aria-controls="menu-principal"
			data-component="menu-toggle"
		>
			<span class="menu-toggle__box" aria-hidden="true">
				<span class="menu-toggle__bar"></span>
				<span class="menu-toggle__bar"></span>
				<span class="menu-toggle__bar"></span>
			</span>
			<span class="menu-toggle__label"><?php esc_html_e( 'Menu', 'tema-base' ); ?></span>
		</button>

		<nav class="site-nav" id="menu-principal" aria-label="<?php esc_attr_e( 'Menu principal', 'tema-base' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__list',
					'depth'          => 2,
					'fallback_cb'    => 'tb_menu_fallback',
				)
			);
			?>
		</nav>
	</div>
</header>

<main id="conteudo" class="site-main" tabindex="-1">
