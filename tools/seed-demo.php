<?php
/**
 * Cria conteúdo demonstrativo para as seções da home.
 * Executar com: wp eval-file tools/seed-demo.php
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Execute este arquivo pelo WP-CLI.\n" );
}

/**
 * Cria ou atualiza um post demonstrativo pelo slug.
 *
 * @param array $data Dados de conteúdo.
 * @return int|WP_Error
 */
function tb_seed_demo_post( $data ) {
	$existing = get_page_by_path( $data['post_name'], OBJECT, $data['post_type'] );
	if ( $existing && '1' !== get_post_meta( $existing->ID, '_tb_demo_content', true ) ) {
		WP_CLI::error( 'O slug reservado para demonstração já pertence a outro conteúdo: ' . $data['post_name'] );
	}
	$data['ID'] = $existing ? $existing->ID : 0;
	$data['post_status'] = 'publish';

	$post_id = wp_insert_post( wp_slash( $data ), true );
	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_tb_demo_content', '1' );
	}

	return $post_id;
}

/**
 * Importa uma imagem de demonstração uma única vez e liga-a ao post.
 *
 * @param string $filename Nome do JPEG.
 * @param int    $post_id  Post associado.
 * @return int|false
 */
function tb_seed_demo_image( $filename, $post_id ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_tb_demo_asset',
			'meta_value'     => $filename,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		$attachment_id = (int) $existing[0];
		$source = __DIR__ . '/demo-images/' . $filename;
		$target = get_attached_file( $attachment_id );
		if ( $source && $target && file_exists( $source ) && copy( $source, $target ) ) {
			$metadata = wp_generate_attachment_metadata( $attachment_id, $target );
			if ( is_array( $metadata ) ) {
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}
		}
		set_post_thumbnail( $post_id, $attachment_id );
		return $attachment_id;
	}

	$source = __DIR__ . '/demo-images/' . $filename;
	if ( ! file_exists( $source ) ) {
		WP_CLI::warning( 'Imagem ausente: ' . $source );
		return false;
	}

	$upload = wp_upload_dir();
	$folder = trailingslashit( $upload['basedir'] ) . 'tb-demo';
	if ( ! wp_mkdir_p( $folder ) ) {
		WP_CLI::warning( 'Não foi possível criar a pasta de uploads de demonstração.' );
		return false;
	}

	$target = trailingslashit( $folder ) . $filename;
	if ( ! copy( $source, $target ) ) {
		WP_CLI::warning( 'Não foi possível copiar a imagem: ' . $filename );
		return false;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_status'    => 'inherit',
		),
		$target,
		$post_id,
		true
	);

	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::warning( $attachment_id->get_error_message() );
		return false;
	}

	update_post_meta( $attachment_id, '_tb_demo_asset', $filename );
	$metadata = wp_generate_attachment_metadata( $attachment_id, $target );
	if ( is_array( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}
	set_post_thumbnail( $post_id, $attachment_id );

	return (int) $attachment_id;
}

$stories = array(
	array( 'Nosso começo', 'A história começa com uma equipe pequena e uma ideia clara: oferecer um trabalho próximo, rigoroso e feito para durar.', 'historia-01.jpg', 'historia-01', "A primeira etapa foi ouvir com atenção e construir relações de confiança.\n\nCom uma equipe enxuta e uma atuação próxima, cada projeto ajudou a definir o jeito de trabalhar que seguimos até hoje." ),
	array( 'Novos caminhos', 'O crescimento veio da confiança de quem caminhou conosco e da disposição para encarar desafios diferentes.', 'historia-02.jpg', 'historia-02', "Novos desafios trouxeram novas áreas de conhecimento e ampliaram nossa capacidade de acompanhar clientes em momentos decisivos.\n\nCrescemos sem abrir mão da colaboração e da responsabilidade em cada escolha." ),
	array( 'Uma equipe diversa', 'Reunimos experiências e perspectivas diferentes para entender cada questão por inteiro.', 'historia-03.jpg', 'historia-03', "A diversidade de trajetórias da equipe ajuda a enxergar problemas por ângulos diferentes.\n\nEsse trabalho conjunto combina conhecimento, diálogo e atenção ao contexto de cada cliente." ),
	array( 'O que vem pela frente', 'Seguimos construindo relações de longo prazo, com responsabilidade e atenção às mudanças do nosso tempo.', 'historia-04.jpg', 'historia-04', "O futuro pede curiosidade, preparo e compromisso com relações duradouras.\n\nSeguimos atentos às mudanças e prontos para construir, junto com nossos clientes, os próximos capítulos." ),
);

$banners = array(
	array( 'Estratégia com propósito', 'Atuação próxima e pensamento de longo prazo para decisões que importam.', 'banner-01.jpg', 'banner-01', 'Conheça nossa atuação', 'https://example.com/atuacao', 'Saiba mais' ),
	array( 'Experiência que constrói confiança', 'Uma equipe preparada para acompanhar cada etapa do caminho.', 'banner-02.jpg', 'banner-02', 'Como trabalhamos', 'https://example.com/sobre', 'Conheça o escritório' ),
);

$highlights = array(
	array( 'Decisões com visão de futuro', 'Estratégia', 'destaque-01.jpg', 'destaque-01', 'https://example.com/estrategia', 'Saiba mais' ),
	array( 'Relações construídas com confiança', 'Nossa abordagem', 'destaque-02.jpg', 'destaque-02', 'https://example.com/abordagem', 'Saiba mais' ),
	array( 'Experiência em diferentes setores', 'Conhecimento', 'destaque-03.jpg', 'destaque-03', 'https://example.com/conhecimento', 'Saiba mais' ),
	array( 'Presença e parceria em cada etapa', 'Proximidade', 'destaque-04.jpg', 'destaque-04', 'https://example.com/proximidade', 'Saiba mais' ),
);

$testimonials = array(
	array( 'Parceria de longo prazo', '“Uma equipe que entende o contexto e trabalha ao nosso lado para encontrar caminhos consistentes.”', 'Cliente institucional · depoimento demonstrativo', 'depoimento-01' ),
	array( 'Reconhecimento profissional', '“O cuidado com cada detalhe e a clareza na comunicação fazem diferença em todo o processo.”', 'Publicação setorial · reconhecimento demonstrativo', 'depoimento-02' ),
	array( 'Trabalho colaborativo', '“Encontramos uma parceria presente, responsável e preparada para questões complexas.”', 'Cliente empresarial · depoimento demonstrativo', 'depoimento-03' ),
);

$articles = array(
	array( 'Tendências que estão transformando o setor', 'Uma leitura das mudanças recentes e dos pontos que merecem atenção nos próximos meses.', 'artigo-01.jpg', 'artigo-01', 'Perspectivas' ),
	array( 'Como preparar decisões para o longo prazo', 'Planejamento, governança e análise de cenários ajudam a construir decisões mais sólidas.', 'artigo-02.jpg', 'artigo-02', 'Institucional' ),
	array( 'O valor de uma análise feita sob medida', 'Cada contexto pede perguntas próprias. Entenda por que a análise individual importa.', 'artigo-03.jpg', 'artigo-03', 'Perspectivas' ),
	array( 'Mudanças regulatórias: o que acompanhar', 'Um resumo dos temas regulatórios que estão no radar das organizações.', 'artigo-04.jpg', 'artigo-04', 'Mercado' ),
);

foreach ( $banners as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'  => 'banner',
			'post_name'  => $item[3],
			'post_title' => $item[0],
			'menu_order' => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_tb_label', 'Atuação integrada' );
	update_post_meta( $id, '_tb_text', $item[1] );
	update_post_meta( $id, '_tb_link', $item[5] );
	update_post_meta( $id, '_tb_link_label', $item[6] );
	tb_seed_demo_image( $item[2], $id );
}

foreach ( $highlights as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'  => 'destaque',
			'post_name'  => $item[3],
			'post_title' => $item[0],
			'menu_order' => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_tb_label', $item[1] );
	update_post_meta( $id, '_tb_link', $item[4] );
	update_post_meta( $id, '_tb_link_label', $item[5] );
	tb_seed_demo_image( $item[2], $id );
}

foreach ( $testimonials as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'  => 'depoimento',
			'post_name'  => $item[3],
			'post_title' => $item[0],
			'menu_order' => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_tb_quote', $item[1] );
	update_post_meta( $id, '_tb_source', $item[2] );
}

foreach ( $stories as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'  => 'historia',
			'post_name'  => $item[3],
			'post_title' => $item[0],
			'post_content' => $item[4],
			'menu_order' => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_tb_summary', $item[1] );
	tb_seed_demo_image( $item[2], $id );
}

$sticky_ids = (array) get_option( 'sticky_posts', array() );
$article_ids = array();
foreach ( $articles as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'    => 'post',
			'post_name'    => $item[3],
			'post_title'   => $item[0],
			'post_excerpt' => $item[1],
			'menu_order'   => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	$article_ids[] = (int) $id;
	$term = term_exists( $item[4], 'category' );
	if ( ! $term ) {
		$term = wp_insert_term( $item[4], 'category' );
	}
	if ( ! is_wp_error( $term ) ) {
		wp_set_post_categories( $id, array( (int) ( is_array( $term ) ? $term['term_id'] : $term ) ), false );
	}
	tb_seed_demo_image( $item[2], $id );
}

if ( ! empty( $article_ids ) ) {
	$sticky_ids[] = $article_ids[0];
	update_option( 'sticky_posts', array_values( array_unique( array_map( 'intval', $sticky_ids ) ) ) );
}

$home_id = tb_seed_demo_post(
	array(
		'post_type'  => 'page',
		'post_name'  => 'inicio',
		'post_title' => 'Início',
	)
);
$blog_id = tb_seed_demo_post(
	array(
		'post_type'  => 'page',
		'post_name'  => 'artigos',
		'post_title' => 'Artigos',
	)
);

if ( is_wp_error( $home_id ) || is_wp_error( $blog_id ) ) {
	WP_CLI::error( 'Não foi possível configurar as páginas de início e artigos.' );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', (int) $home_id );
update_option( 'page_for_posts', (int) $blog_id );

WP_CLI::success( 'Conteúdo de demonstração criado/atualizado: 2 banners, 4 destaques, 3 depoimentos, 4 histórias, 4 artigos e páginas Início/Artigos.' );
