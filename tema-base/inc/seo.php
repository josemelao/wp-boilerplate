<?php
/**
 * SEO essencial do tema: descrição, Open Graph, Twitter Cards, schema e
 * trilha de navegação.
 *
 * Quando um plugin de SEO reconhecido está ativo, o tema se desliga para não
 * duplicar tags. Em produção o plugin continua recomendado para redirects,
 * análise e painel editorial; este módulo garante o mínimo no protótipo e em
 * sites sem plugin.
 *
 * @package Tema_Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detecta um plugin de SEO que já cuida das tags.
 *
 * @return bool
 */
function tb_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SLIM_SEO_VERSION' );

	return (bool) apply_filters( 'tb_seo_plugin_active', $active );
}

if ( tb_seo_plugin_active() ) {
	return;
}

/**
 * Texto curto, sem HTML, limitado a um número de caracteres.
 *
 * @param string $text   Texto bruto.
 * @param int    $length Limite em caracteres.
 * @return string
 */
function tb_seo_excerpt( $text, $length = 160 ) {
	$text = wp_strip_all_tags( strip_shortcodes( (string) $text ) );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );

	if ( '' === $text ) {
		return '';
	}

	if ( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > $length ) {
		$text = mb_substr( $text, 0, $length - 1 );
		$text = preg_replace( '/\s+\S*$/u', '', $text );
		$text = rtrim( $text, " \t\n\r\0\x0B.,;:—-" ) . '…';
	} elseif ( ! function_exists( 'mb_strlen' ) && strlen( $text ) > $length ) {
		$text = substr( $text, 0, $length - 1 );
		$text = preg_replace( '/\s+\S*$/', '', $text );
		$text = rtrim( $text, " \t\n\r\0\x0B.,;:—-" ) . '…';
	}

	return $text;
}

/**
 * Descrição padrão da home, editável em "Opções do Site".
 *
 * @return string
 */
function tb_seo_home_description() {
	$description = tb_opt( 'seo_home_description' );

	if ( '' === $description ) {
		$description = get_bloginfo( 'description' );
	}

	if ( '' === $description ) {
		/* translators: %s: nome do site */
		$description = sprintf( __( 'Conheça as áreas de atuação, o time e os artigos de %s.', 'tema-base' ), get_bloginfo( 'name' ) );
	}

	return tb_seo_excerpt( $description );
}

/**
 * Descrição da página atual.
 *
 * @return string
 */
function tb_seo_description() {
	if ( is_front_page() ) {
		return tb_seo_home_description();
	}

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$custom  = get_post_meta( $post_id, '_tb_seo_description', true );

		if ( '' !== $custom ) {
			return tb_seo_excerpt( $custom );
		}

		$post = get_post( $post_id );

		if ( $post && has_excerpt( $post ) ) {
			return tb_seo_excerpt( $post->post_excerpt );
		}

		$content = $post ? $post->post_content : '';

		if ( 'socio' === get_post_type( $post_id ) ) {
			$role = get_post_meta( $post_id, '_tb_role', true );

			if ( '' !== $role ) {
				return tb_seo_excerpt( get_the_title( $post_id ) . ' — ' . $role . '. ' . $content );
			}
		}

		$description = tb_seo_excerpt( $content );

		if ( '' !== $description ) {
			return $description;
		}

		return tb_seo_excerpt( get_the_title( $post_id ) . ' — ' . get_bloginfo( 'name' ) );
	}

	if ( is_post_type_archive() ) {
		$object = get_queried_object();

		if ( $object instanceof WP_Post_Type ) {
			$map = array(
				'area'       => __( 'Áreas de atuação do escritório, com conteúdo e contato.', 'tema-base' ),
				'socio'      => __( 'Conheça o quadro de sócios e a trajetória de cada integrante.', 'tema-base' ),
				'escritorio' => __( 'Endereços, contato e informações de cada um dos nossos escritórios.', 'tema-base' ),
			);

			if ( isset( $map[ $object->name ] ) ) {
				return $map[ $object->name ];
			}
		}

		return '';
	}

	if ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );

		if ( $page_id ) {
			$page = get_post( $page_id );

			if ( $page && has_excerpt( $page ) ) {
				return tb_seo_excerpt( $page->post_excerpt );
			}
		}

		/* translators: %s: nome do site */
		return tb_seo_excerpt( sprintf( __( 'Artigos e conteúdos publicados por %1$s: análises, perspectivas e novidades.', 'tema-base' ), get_bloginfo( 'name' ) ) );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		return tb_seo_excerpt( term_description() );
	}

	if ( is_author() ) {
		return tb_seo_excerpt( get_the_author_meta( 'description', (int) get_queried_object_id() ) );
	}

	return '';
}

/**
 * URL canônica da página atual.
 *
 * @return string
 */
function tb_seo_canonical_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		$permalink = get_permalink();

		return $permalink ? $permalink : '';
	}

	if ( is_home() ) {
		return tb_blog_url();
	}

	if ( is_post_type_archive() ) {
		$object = get_queried_object();
		$link   = $object instanceof WP_Post_Type ? get_post_type_archive_link( $object->name ) : '';

		return $link ? $link : '';
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$object = get_queried_object();
		$link   = $object ? get_term_link( $object ) : '';

		return is_wp_error( $link ) ? '' : $link;
	}

	if ( is_author() ) {
		return get_author_posts_url( (int) get_queried_object_id() );
	}

	return '';
}

/**
 * Imagem do primeiro banner publicado, usada como padrão social.
 *
 * @return int
 */
function tb_seo_banner_image_id() {
	$banners = get_posts(
		array(
			'post_type'      => 'banner',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	return $banners ? (int) get_post_thumbnail_id( $banners[0] ) : 0;
}

/**
 * Imagem representativa: destacada, primeiro banner na home, ícone do site.
 *
 * @return array{url:string,width:int,height:int,alt:string}|array
 */
function tb_seo_image() {
	$image_id = 0;

	if ( is_front_page() ) {
		$image_id = tb_seo_banner_image_id();
	}

	if ( ! $image_id && is_singular() ) {
		$image_id = (int) get_post_thumbnail_id( (int) get_queried_object_id() );
	}

	if ( ! $image_id ) {
		$image_id = (int) get_option( 'site_icon' );
	}

	if ( ! $image_id ) {
		$image_id = (int) get_theme_mod( 'custom_logo' );
	}

	if ( ! $image_id ) {
		$image_id = tb_seo_banner_image_id();
	}

	if ( ! $image_id ) {
		return array();
	}

	$src = wp_get_attachment_image_src( $image_id, 'tb-slide' );

	if ( ! $src ) {
		$src = wp_get_attachment_image_src( $image_id, 'full' );
	}

	if ( ! $src ) {
		return array();
	}

	return array(
		'url'    => $src[0],
		'width'  => (int) $src[1],
		'height' => (int) $src[2],
		'alt'    => (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * Tipo Open Graph da página.
 *
 * @return string
 */
function tb_seo_og_type() {
	if ( is_singular( 'socio' ) ) {
		return 'profile';
	}

	if ( is_singular( 'post' ) || is_singular( 'historia' ) ) {
		return 'article';
	}

	return 'website';
}

/**
 * Título da página sem o sufixo do site.
 *
 * @return string
 */
function tb_seo_page_title() {
	if ( is_front_page() ) {
		return get_bloginfo( 'name' );
	}

	if ( is_singular() ) {
		return get_the_title( (int) get_queried_object_id() );
	}

	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}

	if ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );

		return $page_id ? get_the_title( $page_id ) : get_bloginfo( 'name' );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}

	return wp_get_document_title();
}

/**
 * Itens da trilha de navegação (nome + URL).
 *
 * @return array<int,array{name:string,url:string}>
 */
function tb_seo_breadcrumb_items() {
	$items = array(
		array(
			'name' => __( 'Início', 'tema-base' ),
			'url'  => home_url( '/' ),
		),
	);

	if ( is_front_page() ) {
		return array();
	}

	if ( is_singular() ) {
		$post_id = (int) get_queried_object_id();
		$type    = get_post_type( $post_id );

		if ( 'post' === $type ) {
			$blog_page = (int) get_option( 'page_for_posts' );
			$items[]   = array(
				'name' => $blog_page ? get_the_title( $blog_page ) : __( 'Artigos', 'tema-base' ),
				'url'  => tb_blog_url(),
			);

			$categories = get_the_category( $post_id );

			if ( ! empty( $categories ) ) {
				$items[] = array(
					'name' => $categories[0]->name,
					'url'  => get_category_link( $categories[0] ),
				);
			}
		} elseif ( 'page' === $type ) {
			$ancestors = array_reverse( get_post_ancestors( $post_id ) );

			foreach ( $ancestors as $ancestor_id ) {
				$items[] = array(
					'name' => get_the_title( $ancestor_id ),
					'url'  => get_permalink( $ancestor_id ),
				);
			}
		} elseif ( in_array( $type, array( 'area', 'socio', 'escritorio', 'historia' ), true ) ) {
			$archive = get_post_type_archive_link( $type );

			if ( $archive ) {
				$post_type = get_post_type_object( $type );
				$items[]   = array(
					'name' => $post_type ? $post_type->labels->name : $type,
					'url'  => $archive,
				);
			}
		}

		$items[] = array(
			'name' => get_the_title( $post_id ),
			'url'  => get_permalink( $post_id ),
		);

		return $items;
	}

	if ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );
		$items[] = array(
			'name' => $page_id ? get_the_title( $page_id ) : __( 'Artigos', 'tema-base' ),
			'url'  => tb_blog_url(),
		);
	} elseif ( is_post_type_archive() ) {
		$items[] = array(
			'name' => post_type_archive_title( '', false ),
			'url'  => tb_seo_canonical_url(),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array(
			'name' => single_term_title( '', false ),
			'url'  => tb_seo_canonical_url(),
		);
	}

	return $items;
}

/**
 * Nó Organization/LegalService do schema.
 *
 * @return array
 */
function tb_seo_organization_node() {
	$node = array(
		'@type'       => array( 'Organization', 'LegalService' ),
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => tb_seo_home_description(),
	);

	$logo = (int) get_theme_mod( 'custom_logo' );

	if ( $logo ) {
		$logo_src = wp_get_attachment_image_src( $logo, 'full' );

		if ( $logo_src ) {
			$node['logo'] = $logo_src[0];
		}
	}

	$social = tb_get_social_links();

	if ( ! empty( $social ) ) {
		$node['sameAs'] = array_values( wp_list_pluck( $social, 'url' ) );
	}

	$offices = get_posts(
		array(
			'post_type'      => 'escritorio',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	if ( ! empty( $offices ) ) {
		$departments = array();

		foreach ( $offices as $office ) {
			$department = array(
				'@type' => 'LocalBusiness',
				'name'  => get_the_title( $office ),
				'url'   => get_permalink( $office ),
			);

			$address = get_post_meta( $office->ID, '_tb_address', true );

			if ( '' !== $address ) {
				$department['address'] = array(
					'@type'         => 'PostalAddress',
					'streetAddress' => preg_replace( '/\s*\R\s*/', ', ', wp_strip_all_tags( $address ) ),
				);
			}

			$phone = get_post_meta( $office->ID, '_tb_phone', true );

			if ( '' !== $phone ) {
				$department['telephone'] = $phone;
			}

			$departments[] = $department;
		}

		$node['department'] = $departments;
	}

	return $node;
}

/**
 * Nó WebSite do schema.
 *
 * @return array
 */
function tb_seo_website_node() {
	return array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => tb_seo_home_description(),
		'inLanguage'      => get_bloginfo( 'language' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Nó WebPage/CollectionPage da página atual.
 *
 * @return array
 */
function tb_seo_page_node() {
	$canonical = tb_seo_canonical_url();
	$is_archive = is_archive() || is_home() || is_search();

	return array(
		'@type'       => $is_archive ? 'CollectionPage' : 'WebPage',
		'@id'         => ( $canonical ? $canonical : home_url( '/' ) ) . '#webpage',
		'url'         => $canonical ? $canonical : home_url( '/' ),
		'name'        => tb_seo_page_title(),
		'description' => tb_seo_description(),
		'isPartOf'    => array( '@id' => home_url( '/#website' ) ),
		'about'       => array( '@id' => home_url( '/#organization' ) ),
	);
}

/**
 * Nó de conteúdo específico (Article, Person, Service, LocalBusiness).
 *
 * @return array|null
 */
function tb_seo_content_node() {
	if ( ! is_singular() || is_front_page() ) {
		return null;
	}

	$post_id   = (int) get_queried_object_id();
	$type      = get_post_type( $post_id );
	$canonical = tb_seo_canonical_url();
	$image     = tb_seo_image();
	$node      = array();

	if ( 'post' === $type || 'historia' === $type ) {
		$node = array(
			'@type'            => 'Article',
			'@id'              => $canonical . '#article',
			'headline'         => get_the_title( $post_id ),
			'description'      => tb_seo_description(),
			'datePublished'    => get_the_date( DATE_W3C, $post_id ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
			'mainEntityOfPage' => array( '@id' => $canonical . '#webpage' ),
			'author'           => array( '@id' => home_url( '/#organization' ) ),
			'publisher'        => array( '@id' => home_url( '/#organization' ) ),
			'inLanguage'       => get_bloginfo( 'language' ),
		);

		if ( 'post' === $type ) {
			$categories = get_the_category( $post_id );

			if ( ! empty( $categories ) ) {
				$node['articleSection'] = $categories[0]->name;
			}
		}
	} elseif ( 'socio' === $type ) {
		$node = array(
			'@type'       => 'Person',
			'@id'         => $canonical . '#person',
			'name'        => get_the_title( $post_id ),
			'description' => tb_seo_description(),
			'url'         => $canonical,
			'worksFor'    => array( '@id' => home_url( '/#organization' ) ),
		);

		$role = get_post_meta( $post_id, '_tb_role', true );

		if ( '' !== $role ) {
			$node['jobTitle'] = $role;
		}

		$email = get_post_meta( $post_id, '_tb_email', true );

		if ( '' !== $email ) {
			$node['email'] = $email;
		}

		$phone = get_post_meta( $post_id, '_tb_phone', true );

		if ( '' !== $phone ) {
			$node['telephone'] = $phone;
		}

		$linkedin = get_post_meta( $post_id, '_tb_linkedin', true );

		if ( '' !== $linkedin ) {
			$node['sameAs'] = array( $linkedin );
		}

		$area_ids = array_values( array_filter( array_map( 'absint', (array) get_post_meta( $post_id, '_tb_areas', true ) ) ) );

		if ( $area_ids ) {
			$areas = get_posts(
				array(
					'post_type'      => 'area',
					'post_status'    => 'publish',
					'post__in'       => $area_ids,
					'posts_per_page' => -1,
					'orderby'        => 'post__in',
				)
			);

			if ( $areas ) {
				$node['knowsAbout'] = wp_list_pluck( $areas, 'post_title' );
			}
		}
	} elseif ( 'area' === $type ) {
		$node = array(
			'@type'       => 'Service',
			'@id'         => $canonical . '#service',
			'name'        => get_the_title( $post_id ),
			'description' => tb_seo_description(),
			'url'         => $canonical,
			'provider'    => array( '@id' => home_url( '/#organization' ) ),
		);
	} elseif ( 'escritorio' === $type ) {
		$node = array(
			'@type'            => 'LocalBusiness',
			'@id'              => $canonical . '#localbusiness',
			'name'             => get_the_title( $post_id ),
			'description'      => tb_seo_description(),
			'url'              => $canonical,
			'parentOrganization' => array( '@id' => home_url( '/#organization' ) ),
		);

		$address = get_post_meta( $post_id, '_tb_address', true );

		if ( '' !== $address ) {
			$node['address'] = array(
				'@type'         => 'PostalAddress',
				'streetAddress' => preg_replace( '/\s*\R\s*/', ', ', wp_strip_all_tags( $address ) ),
			);
		}

		$phone = get_post_meta( $post_id, '_tb_phone', true );

		if ( '' !== $phone ) {
			$node['telephone'] = $phone;
		}

		$email = get_post_meta( $post_id, '_tb_email', true );

		if ( '' !== $email ) {
			$node['email'] = $email;
		}
	} else {
		return null;
	}

	if ( ! empty( $image ) ) {
		$node['image'] = $image['url'];
	}

	return $node;
}

/**
 * Nó BreadcrumbList do schema.
 *
 * @return array|null
 */
function tb_seo_breadcrumb_node() {
	$items = tb_seo_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return null;
	}

	$elements = array();

	foreach ( $items as $index => $item ) {
		$elements[] = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => $item['name'],
			'item'     => $item['url'],
		);
	}

	$canonical = tb_seo_canonical_url();

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => ( $canonical ? $canonical : home_url( '/' ) ) . '#breadcrumb',
		'itemListElement' => $elements,
	);
}

/**
 * Monta o grafo JSON-LD da página.
 *
 * @return array
 */
function tb_seo_schema_graph() {
	$graph = array(
		tb_seo_organization_node(),
		tb_seo_website_node(),
		tb_seo_page_node(),
	);

	$content = tb_seo_content_node();

	if ( $content ) {
		$graph[] = $content;
	}

	$breadcrumb = tb_seo_breadcrumb_node();

	if ( $breadcrumb ) {
		$graph[] = $breadcrumb;
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
}

/**
 * Imprime as tags de SEO no <head>.
 *
 * @return void
 */
function tb_seo_head() {
	if ( is_search() || is_404() ) {
		return;
	}

	$description = tb_seo_description();
	$canonical   = tb_seo_canonical_url();
	$image       = tb_seo_image();
	$title       = tb_seo_page_title();
	$type        = tb_seo_og_type();

	echo "\n<!-- Tema Base: SEO -->\n";

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( ! is_singular() && '' !== $canonical ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	printf(
		'<meta property="og:url" content="%s">' . "\n",
		esc_url( '' !== $canonical ? $canonical : home_url( '/' ) )
	);

	if ( ! empty( $image ) ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image['url'] ) );
		printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image['width'] );
		printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image['height'] );

		if ( '' !== $image['alt'] ) {
			printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $image['alt'] ) );
		}
	}

	if ( 'article' === $type && is_singular() ) {
		$post_id = (int) get_queried_object_id();

		printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( DATE_W3C, $post_id ) ) );
		printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( DATE_W3C, $post_id ) ) );

		$categories = get_the_category( $post_id );

		if ( ! empty( $categories ) ) {
			printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $categories[0]->name ) );
		}
	}

	printf(
		'<meta name="twitter:card" content="%s">' . "\n",
		esc_attr( empty( $image ) ? 'summary' : 'summary_large_image' )
	);
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( ! empty( $image ) ) {
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image['url'] ) );
	}
}
add_action( 'wp_head', 'tb_seo_head', 2 );

/**
 * Imprime o JSON-LD no <head>.
 *
 * @return void
 */
function tb_seo_schema() {
	if ( is_search() || is_404() ) {
		return;
	}

	$json = wp_json_encode( tb_seo_schema_graph(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( ! $json ) {
		return;
	}

	printf( '<script type="application/ld+json">%s</script>' . "\n", $json );
}
add_action( 'wp_head', 'tb_seo_schema', 20 );

/**
 * Mantém a página 404 fora do índice.
 *
 * @param array $robots Diretivas atuais.
 * @return array
 */
function tb_seo_robots( $robots ) {
	if ( is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'tb_seo_robots' );

/**
 * Meta box de descrição SEO para posts, páginas e conteúdo público.
 *
 * @return void
 */
function tb_seo_add_meta_box() {
	add_meta_box(
		'tb-seo-description',
		__( 'SEO', 'tema-base' ),
		'tb_seo_render_meta_box',
		array( 'post', 'page', 'area', 'escritorio', 'socio', 'historia' ),
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'tb_seo_add_meta_box' );

/**
 * Renderiza o campo de descrição.
 *
 * @param WP_Post $post Post em edição.
 * @return void
 */
function tb_seo_render_meta_box( $post ) {
	$value = (string) get_post_meta( $post->ID, '_tb_seo_description', true );

	wp_nonce_field( 'tb_save_seo', 'tb_seo_nonce' );

	printf(
		'<textarea id="tb-seo-field-description" name="_tb_seo_description" rows="4" maxlength="160" class="widefat">%s</textarea>',
		esc_textarea( $value )
	);
	echo '<p class="description">' . esc_html__( 'Descrição para buscadores e redes sociais. Se ficar em branco, o tema usa o resumo ou o início do texto. Máximo de 160 caracteres.', 'tema-base' ) . '</p>';
}

/**
 * Salva a descrição SEO.
 *
 * @param int $post_id ID do post.
 * @return void
 */
function tb_seo_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['tb_seo_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['tb_seo_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'tb_save_seo' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$value = isset( $_POST['_tb_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_tb_seo_description'] ) ) : '';

	if ( '' === $value ) {
		delete_post_meta( $post_id, '_tb_seo_description' );
		return;
	}

	update_post_meta( $post_id, '_tb_seo_description', $value );
}
add_action( 'save_post', 'tb_seo_save_meta' );

/**
 * Expõe a descrição SEO na REST API.
 *
 * @return void
 */
function tb_seo_register_meta() {
	$post_types = array( 'post', 'page', 'area', 'escritorio', 'socio', 'historia' );

	foreach ( $post_types as $post_type ) {
		register_post_meta(
			$post_type,
			'_tb_seo_description',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => function ( $allowed = false, $meta_key = '', $post_id = 0 ) {
					$post_id = (int) $post_id;

					if ( $post_id > 0 ) {
						return current_user_can( 'edit_post', $post_id );
					}

					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'tb_seo_register_meta', 20 );
