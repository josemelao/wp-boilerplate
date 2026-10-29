<?php
/**
 * Gera imagens JPEG de demonstração com gradientes usando a extensão GD.
 *
 * Uso: php tools/make-demo-images.php [diretório de saída]
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "Este script deve ser executado pela linha de comando.\n" );
	exit( 1 );
}

if ( ! extension_loaded( 'gd' ) ) {
	fwrite( STDERR, "A extensão GD do PHP é necessária para gerar as imagens.\n" );
	exit( 1 );
}

$output_dir = isset( $argv[1] ) ? $argv[1] : __DIR__ . '/demo-images';

if ( ! is_dir( $output_dir ) && ! mkdir( $output_dir, 0777, true ) && ! is_dir( $output_dir ) ) {
	fwrite( STDERR, "Não foi possível criar o diretório: {$output_dir}\n" );
	exit( 1 );
}

$images = array(
	'banner-01.jpg'   => array( 1920, 800, array( 30, 58, 49 ), array( 118, 139, 111 ), 'BANNER 01' ),
	'banner-02.jpg'   => array( 1920, 800, array( 39, 49, 60 ), array( 151, 129, 102 ), 'BANNER 02' ),
	'destaque-01.jpg' => array( 1280, 640, array( 37, 73, 62 ), array( 152, 139, 112 ), 'DESTAQUE 01' ),
	'destaque-02.jpg' => array( 1280, 640, array( 44, 58, 70 ), array( 166, 135, 98 ), 'DESTAQUE 02' ),
	'destaque-03.jpg' => array( 1280, 640, array( 62, 75, 69 ), array( 172, 157, 126 ), 'DESTAQUE 03' ),
	'destaque-04.jpg' => array( 1280, 640, array( 57, 62, 70 ), array( 132, 150, 143 ), 'DESTAQUE 04' ),
	'historia-01.jpg' => array( 1600, 900, array( 23, 57, 46 ), array( 83, 139, 101 ), 'HISTORIA 01' ),
	'historia-02.jpg' => array( 1600, 900, array( 14, 40, 75 ), array( 67, 123, 177 ), 'HISTORIA 02' ),
	'historia-03.jpg' => array( 1600, 900, array( 89, 34, 42 ), array( 167, 80, 88 ), 'HISTORIA 03' ),
	'historia-04.jpg' => array( 1600, 900, array( 100, 66, 20 ), array( 202, 153, 66 ), 'HISTORIA 04' ),
	'artigo-01.jpg'   => array( 720, 480, array( 37, 68, 59 ), array( 141, 151, 119 ), 'ARTIGO 01' ),
	'artigo-02.jpg'   => array( 720, 480, array( 44, 58, 73 ), array( 158, 133, 102 ), 'ARTIGO 02' ),
	'artigo-03.jpg'   => array( 720, 480, array( 73, 75, 65 ), array( 164, 145, 114 ), 'ARTIGO 03' ),
	'artigo-04.jpg'   => array( 720, 480, array( 43, 72, 67 ), array( 126, 150, 140 ), 'ARTIGO 04' ),
);

foreach ( $images as $filename => $image_data ) {
	list( $width, $height, $start, $end, $label ) = $image_data;
	$image = imagecreatetruecolor( $width, $height );

	for ( $x = 0; $x < $width; $x++ ) {
		$ratio = $x / max( 1, $width - 1 );
		$red   = (int) round( $start[0] + ( $end[0] - $start[0] ) * $ratio );
		$green = (int) round( $start[1] + ( $end[1] - $start[1] ) * $ratio );
		$blue  = (int) round( $start[2] + ( $end[2] - $start[2] ) * $ratio );
		$color = imagecolorallocate( $image, $red, $green, $blue );
		imageline( $image, $x, 0, $x, $height, $color );
	}

	imagealphablending( $image, true );
	$glow = imagecolorallocatealpha( $image, 235, 221, 184, 92 );
	imagefilledellipse( $image, (int) ( $width * 0.76 ), (int) ( $height * 0.25 ), (int) ( $width * 0.72 ), (int) ( $height * 1.2 ), $glow );
	$shade = imagecolorallocatealpha( $image, 9, 20, 17, 95 );
	imagefilledrectangle( $image, 0, (int) ( $height * 0.72 ), $width, $height, $shade );

	$white = imagecolorallocate( $image, 255, 255, 255 );
	imagestring( $image, 5, 32, $height - 46, $label . ' - IMAGEM DE DEMONSTRACAO', $white );

	$target = rtrim( $output_dir, '/\\' ) . DIRECTORY_SEPARATOR . $filename;
	if ( ! imagejpeg( $image, $target, 86 ) ) {
		imagedestroy( $image );
		fwrite( STDERR, "Não foi possível salvar: {$target}\n" );
		exit( 1 );
	}
	imagedestroy( $image );
}

fwrite( STDOUT, sprintf( "%d imagens JPEG geradas em %s\n", count( $images ), $output_dir ) );
