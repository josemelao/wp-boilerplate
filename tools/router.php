<?php
/**
 * Router para o servidor embutido do PHP (php -S).
 *
 * Serve arquivos e diretórios existentes; todo o resto cai no index.php
 * do WordPress, reproduzindo o rewrite dos permalinks.
 *
 * Este arquivo é versionado de propósito: sem ele, um clone do repositório
 * não consegue reproduzir as URLs amigáveis no ambiente local.
 */

$root   = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'wp';
$path   = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

// O PHP embutido serve /wp-admin sem a barra final. Como os links do painel
// são relativos, normalize a URL antes que eles possam apontar para a raiz.
if ( '/wp-admin' === $path ) {
	$query    = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY );
	$location = '/wp-admin/' . ( null !== $query ? '?' . $query : '' );
	header( 'Location: ' . $location, true, 302 );
	exit;
}

$target = realpath( $root . $path );

if ( '/' !== $path && false !== $target && ( is_file( $target ) || is_dir( $target ) ) ) {
	return false;
}

require $root . '/index.php';
