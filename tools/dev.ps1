<#
.SYNOPSIS
  Sobe, derruba ou consulta o WordPress local do projeto.

.DESCRIPTION
  Usa o runtime portátil em .local\ (PHP, MariaDB e WordPress), sem instalar
  nada globalmente. O tema em tema-base\ é mapeado ao vivo para o WordPress
  por uma junction em wp-content\themes\tema-base.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start
  powershell -ExecutionPolicy Bypass -File tools\dev.ps1 stop
  powershell -ExecutionPolicy Bypass -File tools\dev.ps1 status
#>
param(
	[ValidateSet('start', 'stop', 'status', 'restart')]
	[string]$Action = 'start'
)

$ErrorActionPreference = 'Stop'

$root     = Split-Path -Parent $PSScriptRoot
$local    = Join-Path $root '.local'
$php      = Join-Path $local 'php\php.exe'
$wp       = Join-Path $local 'wp'
$maria    = Join-Path $local 'mariadb'
$logs     = Join-Path $local 'logs'
$dbPort   = 3307
$httpPort = 8080

function Test-Port {
	param([int]$Port)
	return [bool](Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)
}

function Start-Db {
	if (Test-Port $dbPort) {
		Write-Host "MariaDB ja esta rodando na porta $dbPort."
		return
	}
	Start-Process -FilePath (Join-Path $maria 'bin\mysqld.exe') `
		-ArgumentList "--datadir=$(Join-Path $local 'db')", "--port=$dbPort", '--bind-address=127.0.0.1', '--console' `
		-RedirectStandardOutput (Join-Path $logs 'mariadb.out.log') `
		-RedirectStandardError (Join-Path $logs 'mariadb.err.log') `
		-WindowStyle Hidden
	for ($i = 0; $i -lt 40; $i++) {
		Start-Sleep -Milliseconds 500
		if (Test-Port $dbPort) {
			Write-Host "MariaDB iniciado na porta $dbPort."
			return
		}
	}
	throw 'MariaDB nao subiu. Veja .local\logs\mariadb.err.log'
}

function Start-Site {
	if (Test-Port $httpPort) {
		Write-Host "Servidor PHP ja esta rodando em http://127.0.0.1:$httpPort/"
		return
	}
	Start-Process -FilePath $php `
		-ArgumentList '-S', "127.0.0.1:$httpPort", '-t', $wp, (Join-Path $root 'tools\router.php') `
		-RedirectStandardOutput (Join-Path $logs 'php.out.log') `
		-RedirectStandardError (Join-Path $logs 'php.err.log') `
		-WindowStyle Hidden
	for ($i = 0; $i -lt 20; $i++) {
		Start-Sleep -Milliseconds 500
		if (Test-Port $httpPort) {
			Write-Host "Site disponivel em http://127.0.0.1:$httpPort/"
			return
		}
	}
	throw 'Servidor PHP nao subiu. Veja .local\logs\php.err.log'
}

function Stop-All {
	$procs = Get-Process mysqld, php -ErrorAction SilentlyContinue |
		Where-Object { $_.Path -like "$local*" }
	if (-not $procs) {
		Write-Host 'Nada rodando.'
		return
	}
	foreach ($p in $procs) {
		Write-Host "Encerrando $($p.ProcessName) (PID $($p.Id))"
		$p | Stop-Process -Force
	}
}

function Show-Status {
	$dbUp   = Test-Port $dbPort
	$httpUp = Test-Port $httpPort
	Write-Host ("MariaDB (porta {0}): {1}" -f $dbPort, $(if ($dbUp) { 'rodando' } else { 'parado' }))
	Write-Host ("Site    (porta {0}): {1}" -f $httpPort, $(if ($httpUp) { 'rodando' } else { 'parado' }))
	if ($httpUp) { Write-Host "URL: http://127.0.0.1:$httpPort/" }
}

switch ($Action) {
	'start'   { Start-Db; Start-Site; Show-Status }
	'stop'    { Stop-All }
	'restart' { Stop-All; Start-Sleep -Seconds 1; Start-Db; Start-Site; Show-Status }
	'status'  { Show-Status }
}
