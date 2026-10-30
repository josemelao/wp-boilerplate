<?php
/**
 * Bloco de compartilhamento de artigos e histórias.
 *
 * @package Tema_Base
 */

$tb_post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : (int) get_the_ID();
$tb_links   = tb_share_links( $tb_post_id );
$tb_url     = $tb_post_id ? get_permalink( $tb_post_id ) : '';

if ( empty( $tb_links ) || ! $tb_url ) {
	return;
}
?>
<aside class="share" aria-label="<?php esc_attr_e( 'Compartilhar', 'tema-base' ); ?>">
	<p class="share__label"><?php esc_html_e( 'Compartilhar', 'tema-base' ); ?></p>
	<ul class="share__list">
		<?php foreach ( $tb_links as $tb_slug => $tb_link ) : ?>
			<?php $tb_is_email = 'email' === $tb_slug; ?>
			<li>
				<a
					class="share__link"
					href="<?php echo esc_url( $tb_link['url'] ); ?>"
					<?php if ( ! $tb_is_email ) : ?>
						target="_blank"
						rel="noopener noreferrer"
					<?php endif; ?>
				>
					<?php echo esc_html( $tb_link['label'] ); ?>
					<?php if ( ! $tb_is_email ) : ?>
						<span class="screen-reader-text"><?php esc_html_e( '(abre em nova aba)', 'tema-base' ); ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
		<li>
			<button
				class="share__link"
				type="button"
				data-share-copy
				data-url="<?php echo esc_url( $tb_url ); ?>"
				aria-label="<?php esc_attr_e( 'Copiar link do artigo', 'tema-base' ); ?>"
			>
				<?php esc_html_e( 'Copiar link', 'tema-base' ); ?>
			</button>
		</li>
	</ul>
	<p class="share__status" aria-live="polite" data-share-status></p>
</aside>
