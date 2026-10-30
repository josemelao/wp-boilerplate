<?php
/** Página de conteúdo não encontrado. */

get_header();
?>
<section class="not-found tb-section" aria-labelledby="not-found-title">
	<div class="tb-container not-found__inner">
		<p class="tb-kicker">404</p>
		<h1 class="not-found__title" id="not-found-title"><?php esc_html_e( 'Página não encontrada', 'tema-base' ); ?></h1>
		<p class="not-found__message"><?php esc_html_e( 'O endereço pode ter mudado ou não está disponível. Você pode voltar ao início ou buscar um conteúdo.', 'tema-base' ); ?></p>
		<div class="not-found__actions">
			<a class="tb-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ir para o início', 'tema-base' ); ?></a>
			<?php get_search_form(); ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
