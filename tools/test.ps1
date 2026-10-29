<#
.SYNOPSIS
  Roda as verificacoes do tema no WordPress local.

.DESCRIPTION
  Executa tests\f1-checks.php dentro do WordPress (wp eval-file), com o banco
  e o site do ambiente local. Requer que o MariaDB esteja rodando
  (tools\dev.ps1 start). Sai com codigo 1 se alguma verificacao falhar.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\test.ps1
#>
[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'

$root   = Split-Path -Parent $PSScriptRoot
$php    = Join-Path $root '.local\php\php.exe'
$wpCli  = Join-Path $root '.local\wp-cli.phar'
$wp     = Join-Path $root '.local\wp'
$checks = Join-Path $root 'tests\f1-checks.php'

if (-not (Test-Path $php) -or -not (Test-Path $wpCli)) {
	throw 'Ambiente local nao encontrado. Rode tools\setup-local.ps1 -Start.'
}

if (-not (Test-Path $checks)) {
	throw "Arquivo de verificacoes nao encontrado: $checks"
}

& $php $wpCli eval-file $checks --path="$wp"
exit $LASTEXITCODE
