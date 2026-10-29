<#
.SYNOPSIS
  Atalho para o WP-CLI do runtime local.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\wp.ps1 post list
  powershell -ExecutionPolicy Bypass -File tools\wp.ps1 theme list
#>
param(
	[Parameter(ValueFromRemainingArguments = $true)]
	[string[]]$WpArgs
)

$ErrorActionPreference = 'Continue'

$root = Split-Path -Parent $PSScriptRoot
$php  = Join-Path $root '.local\php\php.exe'
$cli  = Join-Path $root '.local\wp-cli.phar'
$wp   = Join-Path $root '.local\wp'

& $php $cli "--path=$wp" @WpArgs
exit $LASTEXITCODE
