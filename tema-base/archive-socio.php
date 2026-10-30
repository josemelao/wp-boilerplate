<?php
/** Arquivo público de sócios. */

get_header();
?>
<section class="content-archive tb-section" aria-labelledby="archive-title">
	<header class="content-archive__header tb-container">
		<p class="tb-kicker"><?php esc_html_e( 'Nosso time', 'tema-base' ); ?></p>
		<h1 class="tb-section__title" id="archive-title"><?php post_type_archive_title(); ?></h1>
	</header>
	<div class="tb-container">
		<?php if ( have_posts() ) : ?>
			<div class="socio-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$tb_role = tb_post_meta( get_the_ID(), '_tb_role' );
					?>
					<article <?php post_class( 'socio-card' ); ?>>
						<a class="socio-card__link" href="<?php echo esc_url( get_permalink() ); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'tb-socio', array( 'class' => 'socio-card__image', 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 90vw, (max-width: 1000px) 45vw, 30vw' ) ); ?>
							<?php else : ?>
								<div class="socio-card__placeholder" aria-hidden="true"></div>
							<?php endif; ?>
							<div class="socio-card__body">
								<?php if ( $tb_role ) : ?><p class="tb-kicker socio-card__role"><?php echo esc_html( $tb_role ); ?></p><?php endif; ?>
								<h2 class="socio-card__name"><?php the_title(); ?></h2>
								<span class="socio-card__more"><?php esc_html_e( 'Ver perfil', 'tema-base' ); ?> <span aria-hidden="true">→</span></span>
							</div>
						</a>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( '← Anterior', 'tema-base' ), 'next_text' => __( 'Próxima →', 'tema-base' ) ) ); ?>
		<?php else : ?>
			<p class="content-archive__empty"><?php esc_html_e( 'Nenhum perfil publicado no momento.', 'tema-base' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
