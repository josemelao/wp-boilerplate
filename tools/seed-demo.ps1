<#
.SYNOPSIS
  Gera imagens e conteúdo de demonstração para a home do tema.

.DESCRIPTION
  Cria ou atualiza apenas os itens com slugs reservados para demonstração.
  Não apaga conteúdo existente. Configura a página inicial e a página de posts.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\seed-demo.ps1
#>
[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$php  = Join-Path $root '.local\php\php.exe'
$wp   = Join-Path $root '.local\wp-cli.phar'
$path = Join-Path $root '.local\wp'

if (-not (Test-Path $php) -or -not (Test-Path $wp) -or -not (Test-Path $path)) {
	throw 'Ambiente WordPress local não encontrado. Rode tools\setup-local.ps1 -Start.'
}

& $php (Join-Path $PSScriptRoot 'make-demo-images.php')
if ($LASTEXITCODE -ne 0) {
	exit $LASTEXITCODE
}

& $php $wp "--path=$path" eval-file (Join-Path $PSScriptRoot 'seed-demo.php')
exit $LASTEXITCODE
