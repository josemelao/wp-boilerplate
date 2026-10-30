<#
.SYNOPSIS
  Gera o zip instalavel do tema.

.DESCRIPTION
  Empacota apenas a pasta tema-base no formato esperado pelo WordPress
  (tema-base/... na raiz do zip). O arquivo vai para dist\tema-base.zip por
  padrao e pode ser enviado em Aparência > Temas > Adicionar novo > Enviar tema.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\build-zip.ps1

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\build-zip.ps1 -Output dist\jt-0.4.0.zip
#>
[CmdletBinding()]
param(
	[string] $Output = ''
)

$ErrorActionPreference = 'Stop'

$root  = Split-Path -Parent $PSScriptRoot
$theme = Join-Path $root 'tema-base'
$dist  = Join-Path $root 'dist'
$zip   = if ( '' -ne $Output ) { $Output } else { Join-Path $dist 'tema-base.zip' }

if ( -not (Test-Path $theme) ) {
	throw "Pasta do tema nao encontrada: $theme"
}

$required = @( 'style.css', 'functions.php', 'index.php', 'assets\css\main.css', 'assets\js\main.js' )

foreach ( $file in $required ) {
	$path = Join-Path $theme $file

	if ( -not (Test-Path $path) ) {
		throw "Arquivo obrigatorio ausente no tema: $file"
	}
}

$style = Get-Content (Join-Path $theme 'style.css') -TotalCount 12
$versionLine = $style | Where-Object { $_ -match '^Version:' } | Select-Object -First 1

if ( -not $versionLine ) {
	throw 'Nao foi possivel ler a versao em tema-base\style.css.'
}

$version = ( $versionLine -replace '^Version:\s*', '' ).Trim()

if ( -not (Test-Path $dist) ) {
	New-Item -ItemType Directory -Path $dist | Out-Null
}

$zipDir = Split-Path -Parent $zip

if ( $zipDir -and -not (Test-Path $zipDir) ) {
	New-Item -ItemType Directory -Path $zipDir | Out-Null
}

if ( Test-Path $zip ) {
	Remove-Item $zip -Force
}

Compress-Archive -Path $theme -DestinationPath $zip -CompressionLevel Optimal

Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::OpenRead( $zip )
$entries = @( $archive.Entries | ForEach-Object { $_.FullName -replace '\\', '/' } )
$archive.Dispose()

$hasStyle = $entries -contains 'tema-base/style.css'
$hasFiles = ( $entries | Where-Object { $_ -like 'tema-base/*' } ).Count -gt 1
$hasJunk  = @( $entries | Where-Object { $_ -match '(^|/)(\.local|node_modules|tests|tools|docs|dist|\.git)(/|$)' } ).Count -gt 0

if ( -not $hasStyle -or -not $hasFiles ) {
	throw 'Zip gerado sem a estrutura esperada (tema-base/style.css na raiz).'
}

if ( $hasJunk ) {
	throw 'Zip contem pastas que nao deveriam ser distribuidas.'
}

$size = [math]::Round( ( (Get-Item $zip).Length / 1MB ), 2 )

Write-Host "Tema: tema-base $version"
Write-Host "Arquivo: $zip"
Write-Host "Tamanho: $size MB"
Write-Host "Entradas: $($entries.Count)"
Write-Host 'OK: zip pronto para upload no WordPress.'
