<#
.SYNOPSIS
  Verifica as tags de SEO do site em execucao.

.DESCRIPTION
  Checa titulo, descricao, canonical, Open Graph, Twitter Card, JSON-LD,
  robots noindex em busca/404, sitemap e robots.txt. Funciona com as tags do
  tema ou com um plugin de SEO. Sai com codigo 1 se alguma verificacao falhar.

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\seo-check.ps1

.EXAMPLE
  powershell -ExecutionPolicy Bypass -File tools\seo-check.ps1 -BaseUrl https://exemplo.com
#>
[CmdletBinding()]
param(
	[string] $BaseUrl = 'http://127.0.0.1:8080',
	[string[]] $Routes = @( '/', '/artigos/', '/socios/', '/areas-de-atuacao/', '/escritorios/' )
)

$ErrorActionPreference = 'Stop'
$BaseUrl = $BaseUrl.TrimEnd('/')

function Get-Page {
	param( [string] $Url )

	try {
		$response = Invoke-WebRequest -Uri $Url -UseBasicParsing -MaximumRedirection 5
		return [pscustomobject]@{
			Status  = [int] $response.StatusCode
			Content = [string] $response.Content
		}
	} catch {
		$response = $_.Exception.Response

		if ( $null -ne $response ) {
			$body = ''

			try {
				$stream = $response.GetResponseStream()
				$reader = New-Object System.IO.StreamReader( $stream )
				$body   = $reader.ReadToEnd()
				$reader.Dispose()
			} catch {
				$body = ''
			}

			return [pscustomobject]@{
				Status  = [int] $response.StatusCode
				Content = [string] $body
			}
		}

		throw
	}
}

$results = New-Object System.Collections.Generic.List[object]

function Add-Result {
	param(
		[string] $Name,
		[bool]   $Ok,
		[string] $Detail = ''
	)

	$results.Add(
		[pscustomobject]@{
			Check  = $Name
			Ok     = $Ok
			Detail = $Detail
		}
	)
}

function Get-MetaTag {
	param(
		[string] $Html,
		[string] $Value
	)

	$quote  = [char] 34
	$squote = [char] 39
	$pattern = '<meta[^>]*(?:property|name)=[' + $quote + $squote + ']' + [regex]::Escape( $Value ) + '[' + $quote + $squote + '][^>]*>'

	return [regex]::Match( $Html, $pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase )
}

function Get-MetaContent {
	param(
		[string] $Html,
		[string] $Value
	)

	$tag = Get-MetaTag -Html $Html -Value $Value

	if ( -not $tag.Success ) {
		return ''
	}

	$quote  = [char] 34
	$squote = [char] 39
	$content = [regex]::Match( $tag.Value, 'content=[' + $quote + $squote + ']([^' + $quote + $squote + ']*)[' + $quote + $squote + ']' )

	return $content.Groups[1].Value
}

function Test-Description {
	param( [string] $Html )

	$description = Get-MetaContent -Html $Html -Value 'description'

	if ( '' -eq $description ) {
		return @{ Ok = $false; Detail = 'ausente' }
	}

	$length = $description.Length

	if ( $length -lt 50 -or $length -gt 160 ) {
		return @{ Ok = $false; Detail = "$length caracteres" }
	}

	return @{ Ok = $true; Detail = "$length caracteres" }
}

function Test-JsonLd {
	param( [string] $Html )

	$blocks = [regex]::Matches( $Html, '(?s)<script type="application/ld\+json">(.*?)</script>' )

	if ( $blocks.Count -eq 0 ) {
		return @{ Ok = $false; Detail = 'ausente' }
	}

	foreach ( $block in $blocks ) {
		try {
			$data = $block.Groups[1].Value | ConvertFrom-Json
		} catch {
			return @{ Ok = $false; Detail = 'JSON invalido' }
		}

		if ( $null -ne $data.'@graph' ) {
			return @{ Ok = $true; Detail = "$($data.'@graph'.Count) nos" }
		}
	}

	return @{ Ok = $true; Detail = "$($blocks.Count) bloco(s)" }
}

foreach ( $route in $Routes ) {
	$url  = $BaseUrl + $route
	$page = Get-Page -Url $url
	$html = $page.Content

	Add-Result "$route status 200" ( $page.Status -eq 200 ) "status $($page.Status)"

	if ( $page.Status -ne 200 ) {
		continue
	}

	$title = [regex]::Match( $html, '(?s)<title>(.*?)</title>' ).Groups[1].Value
	Add-Result "$route titulo" ( '' -ne $title.Trim() ) $title.Trim()

	$h1Count = ([regex]::Matches( $html, '<h1[\s>]', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase )).Count
	Add-Result "$route um h1" ( 1 -eq $h1Count ) "$h1Count encontrado(s)"

	$description = Test-Description -Html $html
	Add-Result "$route descricao" $description.Ok $description.Detail

	$canonical = ([regex]::Matches( $html, '<link[^>]*rel=[' + [char]34 + [char]39 + ']canonical[' + [char]34 + [char]39 + '][^>]*>', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase )).Count
	Add-Result "$route canonical" ( 1 -eq $canonical ) "$canonical encontrado(s)"

	foreach ( $property in @( 'og:title', 'og:description', 'og:url', 'og:image' ) ) {
		$value = Get-MetaContent -Html $html -Value $property
		Add-Result "$route $property" ( '' -ne $value ) $value
	}

	$ogType = Get-MetaContent -Html $html -Value 'og:type'
	Add-Result "$route og:type" ( '' -ne $ogType ) $ogType

	$twitter = Get-MetaContent -Html $html -Value 'twitter:card'
	Add-Result "$route twitter:card" ( '' -ne $twitter ) $twitter

	$jsonLd = Test-JsonLd -Html $html
	Add-Result "$route JSON-LD" $jsonLd.Ok $jsonLd.Detail
}

$searchPage = Get-Page -Url ( $BaseUrl + '/?s=teste' )
$searchRobots = Get-MetaContent -Html $searchPage.Content -Value 'robots'
Add-Result 'busca noindex' ( $searchRobots -match 'noindex' ) $searchRobots

$notFoundPage = Get-Page -Url ( $BaseUrl + '/rota-inexistente-seo-check/' )
Add-Result '404 status 404' ( 404 -eq $notFoundPage.Status ) "status $($notFoundPage.Status)"

$notFoundRobots = Get-MetaContent -Html $notFoundPage.Content -Value 'robots'
Add-Result '404 noindex' ( $notFoundRobots -match 'noindex' ) $notFoundRobots

$sitemap = Get-Page -Url ( $BaseUrl + '/wp-sitemap.xml' )
Add-Result 'sitemap 200' ( 200 -eq $sitemap.Status ) "status $($sitemap.Status)"

$robots = Get-Page -Url ( $BaseUrl + '/robots.txt' )
Add-Result 'robots.txt com sitemap' ( $robots.Content -match 'Sitemap:' ) ''

$failed = @( $results | Where-Object { -not $_.Ok } ).Count

foreach ( $result in $results ) {
	$line = '{0}  {1}' -f $( if ( $result.Ok ) { 'PASS' } else { 'FAIL' } ), $result.Check

	if ( '' -ne $result.Detail ) {
		$line += " ($($result.Detail))"
	}

	if ( $result.Ok ) {
		Write-Host $line
	} else {
		Write-Host $line -ForegroundColor Red
	}
}

Write-Host ''

if ( $failed -gt 0 ) {
	Write-Host "SEO: $failed falha(s) de $($results.Count) verificacoes." -ForegroundColor Red
	exit 1
}

Write-Host "SEO: $($results.Count) verificacoes passaram." -ForegroundColor Green
exit 0
