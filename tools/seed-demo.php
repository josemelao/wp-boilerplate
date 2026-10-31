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
	array( 'Tendências que estão transformando o setor', 'Uma leitura das mudanças recentes e dos pontos que merecem atenção nos próximos meses.', 'artigo-01.jpg', 'artigo-01', 'Perspectivas', "As transformações do setor combinam tecnologia, novas exigências regulatórias e mudanças no comportamento dos clientes. O resultado é um ambiente que pede leitura atenta e decisões bem documentadas.\n\nNeste cenário, acompanhar sinais fracos e revisar premissas com regularidade ajuda a evitar respostas tardias. A análise deve considerar não apenas o que mudou, mas também o que ainda pode mudar." ),
	array( 'Como preparar decisões para o longo prazo', 'Planejamento, governança e análise de cenários ajudam a construir decisões mais sólidas.', 'artigo-02.jpg', 'artigo-02', 'Institucional', "Decisões de longo prazo dependem de informação confiável, critérios claros e governança bem definida. Quanto maior o horizonte, maior a importância de separar o que é fato do que é hipótese.\n\nUma boa preparação inclui cenários alternativos, revisão periódica e registro das premissas adotadas. Assim, a organização consegue ajustar o curso sem perder a coerência da estratégia." ),
	array( 'O valor de uma análise feita sob medida', 'Cada contexto pede perguntas próprias. Entenda por que a análise individual importa.', 'artigo-03.jpg', 'artigo-03', 'Perspectivas', "Soluções genéricas podem funcionar como ponto de partida, mas raramente capturam o contexto completo de uma organização. A análise sob medida começa pela escuta e pela identificação das perguntas que realmente importam.\n\nEsse processo exige proximidade, rigor e disposição para revisar conclusões. O objetivo não é produzir mais informação, e sim informação útil para a decisão." ),
	array( 'Mudanças regulatórias: o que acompanhar', 'Um resumo dos temas regulatórios que estão no radar das organizações.', 'artigo-04.jpg', 'artigo-04', 'Mercado', "Mudanças regulatórias costumam chegar acompanhadas de novas obrigações de documentação, governança e reporte. Antecipar esses movimentos reduz o custo de adaptação e melhora a qualidade das decisões.\n\nO acompanhamento deve ser contínuo, com fontes confiáveis e critérios claros para priorizar o que exige ação imediata e o que pode ser monitorado." ),
);

$areas = array(
	array( 'Contencioso e Arbitragem', 'Atuação estratégica em disputas complexas, negociação e resolução de conflitos.', 'destaque-01.jpg', 'contencioso-e-arbitragem', "A área acompanha casos contenciosos e arbitrais desde a avaliação inicial até a execução da decisão. O trabalho combina análise técnica, definição de estratégia e comunicação clara com o cliente.\n\nAtuamos em negociações, medidas de urgência, produção de provas e audiências, sempre com foco em preservar o interesse do cliente e reduzir exposição." ),
	array( 'Societário e M&A', 'Estruturação societária, governança e operações de fusão e aquisição.', 'destaque-02.jpg', 'societario-e-ma', "Prestamos assessoria em constituição e reorganização de sociedades, acordos de sócios, governança e operações de compra e venda de participações.\n\nO trabalho envolve due diligence, negociação de documentos, coordenação de especialistas e acompanhamento da implementação." ),
	array( 'Tributário', 'Planejamento, consultoria e defesa em matérias tributárias.', 'destaque-03.jpg', 'tributario', "A atuação tributária combina análise de riscos, planejamento e defesa administrativa ou judicial. O objetivo é dar previsibilidade às decisões e segurança jurídica às operações.\n\nA equipe acompanha mudanças regulatórias e avalia impactos concretos no negócio do cliente." ),
	array( 'Trabalhista', 'Consultoria preventiva e atuação em relações de trabalho.', 'destaque-04.jpg', 'trabalhista', "A área apoia empresas na estruturação de relações de trabalho, políticas internas e gestão de riscos. Também atua em negociações e processos trabalhistas.\n\nO trabalho é próximo do cliente e orientado à prevenção, com linguagem clara e foco na continuidade da operação." ),
);

$area_content = array(
	'contencioso-e-arbitragem' => array(
		'services' => "Contencioso cível e empresarial\nArbitragem nacional e internacional\nMedidas de urgência e produção de provas\nNegociação e resolução de disputas",
		'sectors'  => "Infraestrutura\nEnergia\nConstrução e incorporação",
	),
	'tributario'               => array(
		'services' => "Planejamento tributário\nConsultoria em tributos indiretos\nDefesa em autos de infração\nContencioso administrativo e judicial",
		'sectors'  => "Indústria\nVarejo\nServiços financeiros",
	),
	'trabalhista'              => array(
		'services' => "Consultoria preventiva\nPolíticas internas e compliance trabalhista\nNegociações coletivas\nContencioso trabalhista",
		'sectors'  => "Indústria\nServiços\nTecnologia",
	),
);

$offices = array(
	array( 'São Paulo', "Av. Exemplo, 1000 — 12º andar\nSão Paulo/SP", 'destaque-01.jpg', 'sao-paulo', '+55 11 0000-0000', 'saopaulo@jt.example', 'https://maps.google.com/?q=Av.+Exemplo+1000+Sao+Paulo', "O escritório de São Paulo concentra as equipes de contencioso, societário e tributário, com atendimento próximo a clientes em diferentes setores.\n\nA unidade é o ponto de contato para assuntos institucionais e novas demandas." ),
	array( 'Rio de Janeiro', "Rua Exemplo, 500 — 8º andar\nRio de Janeiro/RJ", 'banner-02.jpg', 'rio-de-janeiro', '+55 21 0000-0000', 'riodejaneiro@jt.example', 'https://maps.google.com/?q=Rua+Exemplo+500+Rio+de+Janeiro', "A unidade do Rio de Janeiro atende clientes com foco em contencioso, arbitragem e relações institucionais.\n\nA equipe local trabalha de forma integrada com os demais escritórios." ),
);

$office_excerpts = array(
	'sao-paulo'      => 'Unidade de São Paulo, com equipes de contencioso, societário e tributário.',
	'rio-de-janeiro' => 'Unidade do Rio de Janeiro, com foco em contencioso, arbitragem e relações institucionais.',
);

$socios = array(
	array( 'Ana Exemplo', 'Sócia', 'Contencioso e Arbitragem', 'ana.exemplo@jt.example', '+55 11 0000-0001', 'https://www.linkedin.com/', 'socio-01.jpg', 'ana-exemplo', "Ana Exemplo atua em disputas complexas, arbitragem e negociação estratégica. Acompanha clientes em setores regulados e em situações de alta exposição.\n\nSua prática combina análise técnica, coordenação de equipes e comunicação clara com conselhos e áreas de negócio." ),
	array( 'Bruno Exemplo', 'Sócio', 'Societário e M&A', 'bruno.exemplo@jt.example', '+55 11 0000-0002', 'https://www.linkedin.com/', 'socio-02.jpg', 'bruno-exemplo', "Bruno Exemplo assessora operações societárias, governança e transações de fusão e aquisição. Tem experiência em due diligence e negociação de documentos complexos.\n\nTrabalha de forma integrada com equipes financeiras, regulatórias e tributárias." ),
	array( 'Carla Exemplo', 'Sócia', 'Tributário', 'carla.exemplo@jt.example', '+55 11 0000-0003', 'https://www.linkedin.com/', 'socio-03.jpg', 'carla-exemplo', "Carla Exemplo atua em planejamento tributário, consultoria e defesa em disputas administrativas. Acompanha mudanças regulatórias e seus impactos nas decisões de negócio.\n\nSua abordagem prioriza previsibilidade, documentação e segurança jurídica." ),
	array( 'Diego Exemplo', 'Sócio', 'Trabalhista', 'diego.exemplo@jt.example', '+55 21 0000-0004', 'https://www.linkedin.com/', 'socio-04.jpg', 'diego-exemplo', "Diego Exemplo assessora empresas em relações de trabalho, políticas internas e gestão de riscos. Também atua em negociações e processos trabalhistas.\n\nSeu trabalho combina prevenção, treinamento de equipes e acompanhamento próximo da operação." ),
);

$socio_details = array(
	'ana-exemplo'   => array(
		'education' => "Graduação em Direito — USP\nMestrado em Direito Processual Civil — USP",
		'languages' => "Português\nInglês\nEspanhol",
		'oab'       => 'OAB/SP 000.001',
	),
	'bruno-exemplo' => array(
		'education' => "Graduação em Direito — FGV\nLL.M. em Direito Societário — FGV",
		'languages' => "Português\nInglês",
		'oab'       => 'OAB/SP 000.002',
	),
	'carla-exemplo' => array(
		'education' => "Graduação em Direito — USP\nEspecialização em Direito Tributário — IBDT",
		'languages' => "Português\nInglês\nAlemão",
		'oab'       => 'OAB/SP 000.003',
	),
);

$institutional_pages = array(
	array( 'Sobre', 'sobre', "O JT é um escritório institucional dedicado a decisões que exigem análise rigorosa, discrição e visão de longo prazo.\n\nReunimos diferentes especialidades para oferecer uma leitura completa de cada contexto, com equipes próximas e coordenação integrada." ),
	array( 'Contato', 'contato', "Para conversar com nossa equipe, escreva para contato@jt.example ou utilize os contatos dos escritórios.\n\nSão Paulo: +55 11 0000-0000\nRio de Janeiro: +55 21 0000-0000" ),
	array( 'Reconhecimentos', 'reconhecimentos', "Reconhecimentos e rankings demonstrativos para apresentação do layout.\n\nA versão final deve reunir prêmios, publicações e classificações reais, com ano, fonte e área de atuação." ),
	array( 'Privacidade', 'privacidade', "Esta é uma página demonstrativa de política de privacidade. Antes da publicação, o cliente deve fornecer o texto jurídico definitivo, incluindo bases legais, cookies, retenção e direitos dos titulares.\n\nContato do encarregado: privacidade@jt.example." ),
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
			'post_content' => $item[5],
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

foreach ( $areas as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'    => 'area',
			'post_name'    => $item[3],
			'post_title'   => $item[0],
			'post_excerpt' => $item[1],
			'post_content' => $item[4],
			'menu_order'   => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}

	if ( isset( $area_content[ $item[3] ] ) ) {
		update_post_meta( $id, '_tb_services', $area_content[ $item[3] ]['services'] );
		update_post_meta( $id, '_tb_sectors', $area_content[ $item[3] ]['sectors'] );
	}

	tb_seed_demo_image( $item[2], $id );
}

foreach ( $offices as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'    => 'escritorio',
			'post_name'    => $item[3],
			'post_title'   => $item[0],
			'post_excerpt' => isset( $office_excerpts[ $item[3] ] ) ? $office_excerpts[ $item[3] ] : $item[1],
			'post_content' => $item[7],
			'menu_order'   => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_tb_address', $item[1] );
	update_post_meta( $id, '_tb_phone', $item[4] );
	update_post_meta( $id, '_tb_email', $item[5] );
	update_post_meta( $id, '_tb_maps_url', $item[6] );

	if ( 'sao-paulo' === $item[3] ) {
		update_post_meta( $id, '_tb_hours', "Segunda a sexta: 9h às 18h\nSábado: 9h às 13h" );
	}

	tb_seed_demo_image( $item[2], $id );
}

foreach ( $socios as $index => $item ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'    => 'socio',
			'post_name'    => $item[7],
			'post_title'   => $item[0],
			'post_excerpt' => wp_trim_words( wp_strip_all_tags( $item[8] ), 28, '…' ),
			'post_content' => $item[8],
			'menu_order'   => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_tb_role', $item[1] );
	update_post_meta( $id, '_tb_email', $item[3] );
	update_post_meta( $id, '_tb_phone', $item[4] );
	update_post_meta( $id, '_tb_linkedin', $item[5] );

	$tb_area = get_page_by_path( sanitize_title( $item[2] ), OBJECT, 'area' );

	if ( $tb_area ) {
		update_post_meta( $id, '_tb_areas', array( (int) $tb_area->ID ) );
	} else {
		delete_post_meta( $id, '_tb_areas' );
	}

	if ( isset( $socio_details[ $item[7] ] ) ) {
		update_post_meta( $id, '_tb_education', $socio_details[ $item[7] ]['education'] );
		update_post_meta( $id, '_tb_languages', $socio_details[ $item[7] ]['languages'] );
		update_post_meta( $id, '_tb_oab', $socio_details[ $item[7] ]['oab'] );
	} else {
		delete_post_meta( $id, '_tb_education' );
		delete_post_meta( $id, '_tb_languages' );
		delete_post_meta( $id, '_tb_oab' );
	}

	tb_seed_demo_image( $item[6], $id );
}

$page_ids = array();
foreach ( $institutional_pages as $index => $page ) {
	$id = tb_seed_demo_post(
		array(
			'post_type'    => 'page',
			'post_name'    => $page[1],
			'post_title'   => $page[0],
			'post_content' => $page[2],
			'menu_order'   => $index + 1,
		)
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	$page_ids[ $page[1] ] = (int) $id;
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
update_option( 'date_format', 'j \d\e F \d\e Y' );

$demo_options = array_merge(
	tb_options_defaults(),
	(array) get_option( 'tb_options', array() ),
	array(
		'offices'            => "São Paulo\nRio de Janeiro",
		'social_linkedin'    => 'https://www.linkedin.com/',
		'social_instagram'   => 'https://www.instagram.com/',
		'subscribe_label'    => 'Inscreva-se',
		'subscribe_url'      => 'https://example.com/inscricao',
		'footer_note'        => 'Site de demonstração.',
	)
);
update_option( 'tb_options', $demo_options );

flush_rewrite_rules();

$demo_menu_items = array(
	array( 'title' => 'Início', 'type' => 'page', 'id' => (int) $home_id ),
	array( 'title' => 'Áreas', 'type' => 'custom', 'url' => home_url( '/areas-de-atuacao/' ) ),
	array( 'title' => 'Sócios', 'type' => 'custom', 'url' => home_url( '/socios/' ) ),
	array( 'title' => 'Artigos', 'type' => 'page', 'id' => (int) $blog_id ),
	array( 'title' => 'Escritórios', 'type' => 'custom', 'url' => home_url( '/escritorios/' ) ),
	array( 'title' => 'Sobre', 'type' => 'page', 'id' => $page_ids['sobre'] ),
	array( 'title' => 'Contato', 'type' => 'page', 'id' => $page_ids['contato'] ),
);

$footer_menu_items = array_merge(
	$demo_menu_items,
	array(
		array( 'title' => 'Reconhecimentos', 'type' => 'page', 'id' => $page_ids['reconhecimentos'] ),
		array( 'title' => 'Privacidade', 'type' => 'page', 'id' => $page_ids['privacidade'] ),
	)
);

$seed_menu = static function ( $name, $location, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );

	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $menu_item ) {
		wp_delete_post( $menu_item->ID, true );
	}

	foreach ( $items as $item ) {
		$args = array(
			'menu-item-title'  => $item['title'],
			'menu-item-status' => 'publish',
		);

		if ( 'custom' === $item['type'] ) {
			$args['menu-item-type'] = 'custom';
			$args['menu-item-url']  = $item['url'];
		} else {
			$args['menu-item-type']      = 'post_type';
			$args['menu-item-object']    = 'page';
			$args['menu-item-object-id'] = $item['id'];
		}

		wp_update_nav_menu_item( $menu_id, 0, $args );
	}

	$locations             = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
};

$seed_menu( 'Menu principal', 'primary', $demo_menu_items );
$seed_menu( 'Menu do rodapé', 'footer', $footer_menu_items );

WP_CLI::success( 'Conteúdo de demonstração criado/atualizado: 2 banners, 4 destaques, 3 depoimentos, 4 histórias, 4 artigos, 4 áreas, 2 escritórios, 4 sócios, páginas institucionais, menus e opções do site.' );
