<#
.SYNOPSIS
  Prepara do zero o ambiente local de desenvolvimento do projeto (Windows x64).

.DESCRIPTION
  Instala um runtime portátil em .local\ (fora do Git), sem exigir PHP, MySQL,
  Docker, LocalWP ou privilégios de administrador:

    - PHP 8.3.36 (NTS x64)
    - MariaDB 11.4.13
    - WordPress 7.1.4 (pt_BR)
    - WP-CLI 2.12.0

  Também cria o banco, instala o WordPress, mapeia tema-base\ para o WordPress
  por uma junction, ativa o tema e configura permalinks/idioma.

  O script é idempotente: pode ser executado de novo sem reinstalar o que já
  existe.

.PARAMETER AdminUser
  Usuário do administrador do WordPress local. Padrão: admin.

.PARAMETER AdminPassword
  Senha do administrador do WordPress local. Padrão: admin_local_2026.

.PARAMETER AdminEmail
  E-mail do administrador do WordPress local. Padrão: admin@example.com.

.PARAMETER DbPassword
  Senha do usuário wp do MariaDB local. Padrão: wp_local_2026.

.PARAMETER Start
  Sobe o site ao final da instalação.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\setup-local.ps1 -AdminPassword "outra-senha" -Start
#>
[CmdletBinding()]
param(
	[string]$AdminUser = 'admin',
	[string]$AdminPassword = 'admin_local_2026',
	[string]$AdminEmail = 'admin@example.com',
	[string]$DbPassword = 'wp_local_2026',
	[switch]$Start
)

$ErrorActionPreference = 'Continue'
$ProgressPreference = 'SilentlyContinue'

# --- Caminhos -----------------------------------------------------------------

$root      = Split-Path -Parent $PSScriptRoot
$local     = Join-Path $root '.local'
$downloads = Join-Path $local 'downloads'
$logs      = Join-Path $local 'logs'
$phpDir    = Join-Path $local 'php'
$php       = Join-Path $phpDir 'php.exe'
$mariaDir  = Join-Path $local 'mariadb'
$dbDir     = Join-Path $local 'db'
$wpDir     = Join-Path $local 'wp'
$wpCli     = Join-Path $local 'wp-cli.phar'
$themeDir  = Join-Path $root 'tema-base'

# Portas fixas: mantenha iguais em tools\dev.ps1.
$sitePort = 8080
$dbPort   = 3307
$siteUrl  = "http://127.0.0.1:$sitePort"

# --- Versões pinadas ----------------------------------------------------------
# NÃO use "latest": o time inteiro precisa do mesmo ambiente.

$phpUrl   = 'https://windows.php.net/downloads/releases/archives/php-8.3.36-nts-Win32-vs16-x64.zip'
$wpUrl    = 'https://wordpress.org/wordpress-7.1.4.zip'
$mariaUrl = 'https://downloads.mariadb.org/rest-api/mariadb/11.4.13/mariadb-11.4.13-winx64.zip'
$cliUrl   = 'https://github.com/wp-cli/wp-cli/releases/download/v2.12.0/wp-cli-2.12.0.phar'

$sha = @{
	'php.zip'       = '3c9242622c7402b47c38d2ec2ed779cef3a290fd6f2bde6c35f9c482699e7900'
	'wordpress.zip' = 'a147146c159362778ecbbbfb07bb20ddf4e85b130f971c824e656a8f938c5f87'
	'mariadb.zip'   = 'd62986d433eeebfde218560b276103831604a61e929e87f1a17f5aebd80257e2'
	'wp-cli.phar'   = 'ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c'
}

# --- Funções auxiliares -------------------------------------------------------

function Write-Step {
	param([string]$Message)
	Write-Host ""
	Write-Host "== $Message" -ForegroundColor Cyan
}

function Test-Port {
	param([int]$Port)
	return [bool](Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)
}

function Start-DetachedProcess {
	param(
		[string]$Exe,
		[string[]]$ExeArgs,
		[string]$OutLog,
		[string]$ErrLog
	)

	$argLine = ($ExeArgs | ForEach-Object {
		if ($_ -match '[\s"]') { '"' + ($_ -replace '"', '\"') + '"' } else { $_ }
	}) -join ' '

	$cmdFile = Join-Path $logs ('run-' + [IO.Path]::GetFileNameWithoutExtension($Exe) + '.cmd')
	Set-Content -Path $cmdFile -Value @(
		'@echo off',
		"`"$Exe`" $argLine > `"$OutLog`" 2> `"$ErrLog`""
	) -Encoding ASCII

	$result = Invoke-CimMethod -ClassName Win32_Process -MethodName Create -Arguments @{
		CommandLine = "cmd.exe /c `"$cmdFile`""
	}
	if ($result.ReturnValue -ne 0) {
		throw "Falha ao iniciar $Exe (Win32_Process retornou $($result.ReturnValue))"
	}
}

function Get-PinnedFile {
	param(
		[string]$Name,
		[string]$Url,
		[string]$Sha256
	)

	$dest = Join-Path $downloads $Name

	if ((Test-Path $dest) -and ((Get-FileHash $dest -Algorithm SHA256).Hash.ToLower() -eq $Sha256)) {
		Write-Host "  [ok] $Name"
		return $dest
	}

	Write-Host "  [download] $Name"
	curl.exe -sS -L --retry 3 --max-time 900 -o $dest $Url
	if ($LASTEXITCODE -ne 0) {
		throw "Falha ao baixar $Name de $Url"
	}

	$got = (Get-FileHash $dest -Algorithm SHA256).Hash.ToLower()
	if ($got -ne $Sha256) {
		throw "Checksum divergente para $Name`nEsperado: $Sha256`nObtido:   $got"
	}

	return $dest
}

function Expand-Zip {
	param(
		[string]$Zip,
		[string]$Destination
	)
	New-Item -ItemType Directory -Force -Path $Destination | Out-Null
	tar.exe -xf $Zip -C $Destination
	if ($LASTEXITCODE -ne 0) {
		throw "Falha ao extrair $Zip"
	}
}

function Start-Database {
	if (Test-Port $dbPort) {
		Write-Host "  MariaDB já está rodando na porta $dbPort."
		return
	}

	Start-DetachedProcess -Exe (Join-Path $mariaDir 'bin\mysqld.exe') `
		-ExeArgs @("--datadir=$dbDir", "--port=$dbPort", '--bind-address=127.0.0.1', '--console') `
		-OutLog (Join-Path $logs 'mariadb.out.log') `
		-ErrLog (Join-Path $logs 'mariadb.err.log')

	for ($i = 0; $i -lt 40; $i++) {
		Start-Sleep -Milliseconds 500
		if (Test-Port $dbPort) {
			Write-Host "  MariaDB iniciado na porta $dbPort."
			return
		}
	}

	throw 'MariaDB não subiu. Veja .local\logs\mariadb.err.log'
}

# --- Pré-requisitos -----------------------------------------------------------

Write-Step 'Verificando pré-requisitos'

if ($env:OS -ne 'Windows_NT') {
	throw 'Este script é exclusivo para Windows.'
}

if (-not [Environment]::Is64BitOperatingSystem) {
	throw 'É necessário Windows x64.'
}

if (-not (Test-Path "$env:SystemRoot\System32\msvcp140.dll")) {
	Write-Warning 'Visual C++ Redistributable 2015-2022 não encontrado. O PHP pode não iniciar. Baixe em https://aka.ms/vs/17/release/vc_redist.x64.exe'
}

if (-not (Test-Path $themeDir)) {
	throw "Pasta do tema não encontrada: $themeDir"
}

New-Item -ItemType Directory -Force -Path $local, $downloads, $logs | Out-Null

# --- Downloads ----------------------------------------------------------------

Write-Step 'Baixando componentes (versões pinadas)'

$phpZip   = Get-PinnedFile -Name 'php.zip'       -Url $phpUrl   -Sha256 $sha['php.zip']
$wpZip    = Get-PinnedFile -Name 'wordpress.zip' -Url $wpUrl    -Sha256 $sha['wordpress.zip']
$mariaZip = Get-PinnedFile -Name 'mariadb.zip'   -Url $mariaUrl -Sha256 $sha['mariadb.zip']
$cliPhar  = Get-PinnedFile -Name 'wp-cli.phar'   -Url $cliUrl   -Sha256 $sha['wp-cli.phar']

# --- Extração -----------------------------------------------------------------

Write-Step 'Extraindo componentes'

if (-not (Test-Path $php)) {
	Write-Host '  [extract] PHP'
	Expand-Zip -Zip $phpZip -Destination $phpDir
} else {
	Write-Host '  [ok] PHP'
}

if (-not (Test-Path (Join-Path $wpDir 'wp-load.php'))) {
	Write-Host '  [extract] WordPress'
	Expand-Zip -Zip $wpZip -Destination $local
	$extractedWp = Join-Path $local 'wordpress'
	if (Test-Path $extractedWp) {
		if (Test-Path $wpDir) { Remove-Item $wpDir -Recurse -Force }
		Move-Item $extractedWp $wpDir
	}
} else {
	Write-Host '  [ok] WordPress'
}

if (-not (Test-Path (Join-Path $mariaDir 'bin\mysqld.exe'))) {
	Write-Host '  [extract] MariaDB'
	Expand-Zip -Zip $mariaZip -Destination $local
	$extractedMaria = Get-ChildItem $local -Directory |
		Where-Object { $_.Name -like 'mariadb-*-winx64' } |
		Select-Object -First 1
	if (-not $extractedMaria) {
		throw 'Pasta extraída do MariaDB não encontrada.'
	}
	if (Test-Path $mariaDir) { Remove-Item $mariaDir -Recurse -Force }
	Move-Item $extractedMaria.FullName $mariaDir
} else {
	Write-Host '  [ok] MariaDB'
}

Copy-Item $cliPhar $wpCli -Force
Write-Host '  [ok] WP-CLI'

# --- php.ini ------------------------------------------------------------------

Write-Step 'Configurando o php.ini'

$iniPath = Join-Path $phpDir 'php.ini'
if (-not (Test-Path $iniPath)) {
	Copy-Item (Join-Path $phpDir 'php.ini-development') $iniPath
}
$ini = Get-Content $iniPath -Raw

$ini = $ini -replace '(?m)^;?extension_dir\s*=.*$', 'extension_dir = "ext"'

foreach ($ext in @('mysqli', 'openssl', 'curl', 'mbstring', 'gd', 'exif', 'zip', 'fileinfo', 'intl')) {
	if ((Test-Path (Join-Path $phpDir "ext\php_$ext.dll")) -and ($ini -notmatch "(?m)^extension\s*=\s*$ext\s*$")) {
		$ini += "`r`nextension=$ext"
	}
}

$ini = $ini -replace '(?m)^;?date\.timezone\s*=.*$', 'date.timezone = America/Sao_Paulo'
$ini = $ini -replace '(?m)^;?memory_limit\s*=.*$', 'memory_limit = 256M'
$ini = $ini -replace '(?m)^;?upload_max_filesize\s*=.*$', 'upload_max_filesize = 64M'
$ini = $ini -replace '(?m)^;?post_max_size\s*=.*$', 'post_max_size = 64M'

Set-Content -Path $iniPath -Value $ini -Encoding UTF8
Write-Host '  [ok] php.ini'

# --- Banco de dados -----------------------------------------------------------

Write-Step 'Preparando o banco de dados'

if (-not (Test-Path (Join-Path $dbDir 'mysql'))) {
	& (Join-Path $mariaDir 'bin\mariadb-install-db.exe') -d "$dbDir" -P $dbPort | Out-Null
	if ($LASTEXITCODE -ne 0) {
		throw 'Falha ao inicializar o MariaDB.'
	}
	Write-Host '  [ok] datadir inicializado'
} else {
	Write-Host '  [ok] datadir já inicializado'
}

Start-Database

$dbPassSql = $DbPassword.Replace("'", "''")
$sql = @(
	"CREATE DATABASE IF NOT EXISTS wordpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
	"CREATE USER IF NOT EXISTS 'wp'@'127.0.0.1' IDENTIFIED BY '$dbPassSql';",
	"CREATE USER IF NOT EXISTS 'wp'@'localhost' IDENTIFIED BY '$dbPassSql';",
	"GRANT ALL PRIVILEGES ON wordpress.* TO 'wp'@'127.0.0.1';",
	"GRANT ALL PRIVILEGES ON wordpress.* TO 'wp'@'localhost';",
	"FLUSH PRIVILEGES;"
) -join ' '

& (Join-Path $mariaDir 'bin\mysql.exe') -h 127.0.0.1 -P $dbPort -u root -e $sql 2>$null | Out-Null
if ($LASTEXITCODE -ne 0) {
	throw 'Falha ao criar o banco/usuário.'
}
Write-Host '  [ok] banco wordpress e usuário wp'

# --- WordPress ----------------------------------------------------------------

Write-Step 'Instalando o WordPress'

$configPath = Join-Path $wpDir 'wp-config.php'
if (-not (Test-Path $configPath)) {
	& $php $wpCli config create --path="$wpDir" --dbname=wordpress --dbuser=wp --dbpass=$DbPassword --dbhost="127.0.0.1:$dbPort" --locale=pt_BR --skip-check --force | Out-Null
	if ($LASTEXITCODE -ne 0) {
		throw 'Falha ao criar o wp-config.php.'
	}
	Write-Host '  [ok] wp-config.php'
} else {
	Write-Host '  [ok] wp-config.php já existe'
}

& $php $wpCli core is-installed --path="$wpDir" 2>$null | Out-Null
if ($LASTEXITCODE -ne 0) {
	& $php $wpCli core install --path="$wpDir" --url="$siteUrl" --title='LW Clone' --admin_user=$AdminUser --admin_password=$AdminPassword --admin_email=$AdminEmail --skip-email | Out-Null
	if ($LASTEXITCODE -ne 0) {
		throw 'Falha ao instalar o WordPress.'
	}
	Write-Host '  [ok] WordPress instalado'
} else {
	Write-Host '  [ok] WordPress já instalado'
}

& $php $wpCli language core install pt_BR --activate --path="$wpDir" 2>$null | Out-Null
Write-Host '  [ok] idioma pt_BR'

# --- Tema ---------------------------------------------------------------------

Write-Step 'Mapeando e ativando o tema'

$link = Join-Path $wpDir 'wp-content\themes\tema-base'
if (-not (Test-Path $link)) {
	New-Item -ItemType Junction -Path $link -Target $themeDir | Out-Null
	Write-Host '  [ok] junction tema-base -> tema-base\'
} else {
	Write-Host '  [ok] junction já existe'
}

& $php $wpCli theme activate tema-base --path="$wpDir" 2>$null | Out-Null
Write-Host '  [ok] tema ativo'

& $php $wpCli rewrite structure '/%postname%/' --path="$wpDir" 2>$null | Out-Null
& $php $wpCli rewrite flush --path="$wpDir" 2>$null | Out-Null
Write-Host '  [ok] permalinks /%postname%/'

# --- Credenciais --------------------------------------------------------------

$credentials = @(
	'# Credenciais do WordPress local (NAO commitar)',
	'',
	"- URL: $siteUrl/",
	"- Admin: $siteUrl/wp-admin/",
	"- Usuario: $AdminUser",
	"- Senha: $AdminPassword",
	"- E-mail: $AdminEmail",
	'',
	'- Banco: wordpress',
	'- Usuario do banco: wp',
	"- Senha do banco: $DbPassword",
	"- Host: 127.0.0.1:$dbPort"
) -join "`r`n"
Set-Content -Path (Join-Path $local 'CREDENCIAIS.txt') -Value $credentials -Encoding UTF8

# --- Resumo -------------------------------------------------------------------

Write-Step 'Pronto'

Write-Host "Site:      $siteUrl/"
Write-Host "Painel:    $siteUrl/wp-admin/"
Write-Host "Usuario:   $AdminUser"
Write-Host "Credenciais salvas em .local\CREDENCIAIS.txt"

if ($Start) {
	& (Join-Path $root 'tools\dev.ps1') start
} else {
	Write-Host ''
	Write-Host 'Para subir o site:'
	Write-Host '  powershell -ExecutionPolicy Bypass -File tools\dev.ps1 start'
}
