$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

$Results = New-Object System.Collections.Generic.List[object]
function Add-Result($Name, $Passed, $Details = "") {
    $Results.Add([pscustomobject]@{ Name = $Name; Passed = [bool]$Passed; Details = $Details }) | Out-Null
}

$Main = Get-Content -Raw -LiteralPath "$Root\sabri-unified-application-shell.php"
$Renderer = Get-Content -Raw -LiteralPath "$Root\includes\class-renderer.php"
$HomeFeed = Get-Content -Raw -LiteralPath "$Root\includes\class-home-feed.php"
$Css = Get-Content -Raw -LiteralPath "$Root\assets\css\shell.css"
$Js = Get-Content -Raw -LiteralPath "$Root\assets\js\shell.js"
$Readme = Get-Content -Raw -LiteralPath "$Root\README.md"

Add-Result "Version consistency" ($Main -match "Version: 1\.0\.0" -and $Main -match "SABRI_SHELL_VERSION', '1\.0\.0")
Add-Result "No whole-page output buffering" ($Renderer -notmatch "ob_start" -and $HomeFeed -notmatch "ob_start")
Add-Result "No wp_body_open-to-wp_footer wrapper" ($Renderer -notmatch "<main" -and $Renderer -notmatch "</main>")
Add-Result "Exactly one Notifications output marker" (([regex]::Matches($Renderer, "data-sabri-notifications-output")).Count -eq 1)
Add-Result "Responsive overflow rules" ($Css -match "overflow-x: clip" -and $Css -match "min-width: 0" -and $Css -match "safe-area-inset-bottom")
Add-Result "Accessible drawer JavaScript" ($Js -match "aria-expanded" -and $Js -match "Escape" -and $Js -match "inert")
Add-Result "README required limitations" ($Readme -match "messaging backend is not created by this plugin" -and $Readme -match "Hostinger staging testing is required before production activation")

$Php = (Get-ChildItem -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch "\\release\\" -and $_.FullName -notmatch "\\tools\\run-tests\.php$" } | ForEach-Object { Get-Content -Raw -LiteralPath $_.FullName }) -join "`n"
$Text = (Get-ChildItem -Recurse -Include *.php,*.css,*.js,*.md,*.txt,*.yml,*.yaml | Where-Object { $_.FullName -notmatch "\\release\\" } | ForEach-Object { Get-Content -Raw -LiteralPath $_.FullName }) -join "`n"
$Dangerous = @("\beval\s*\(", "\bshell_exec\s*\(", "\bpassthru\s*\(", "\bproc_open\s*\(", "\bpopen\s*\(", "\bassert\s*\(")
$FoundDangerous = @($Dangerous | Where-Object { $Php -match $_ })
Add-Result "Dangerous PHP function scan" ($FoundDangerous.Count -eq 0) ($FoundDangerous -join ", ")
Add-Result "Hard-coded secret scan" (($Php + $Readme) -notmatch "(ghp_|github_pat_|AKIA[0-9A-Z]{16}|BEGIN PRIVATE KEY|password\s*=\s*['""][^'""]+)")
Add-Result "No external runtime, CDN, or remote font dependencies" ($Text -notmatch "(cdn\.|fonts\.googleapis|fonts\.gstatic|@import\s+url|https?://(?!github\.com/majidhussainqadri1-dot/sabri-unified-application-shell|example\.test))")
Add-Result "No bundled font binaries" (-not (Get-ChildItem -Recurse -Include *.woff,*.woff2,*.ttf,*.otf,*.eot | Where-Object { $_.FullName -notmatch "\\release\\" }))
Add-Result "CSS brace sanity" ((([regex]::Matches($Css, "\{")).Count) -eq (([regex]::Matches($Css, "\}")).Count))

$Failed = @($Results | Where-Object { -not $_.Passed })
$Results | ForEach-Object {
    $Status = if ($_.Passed) { "PASS" } else { "FAIL" }
    Write-Output "$Status - $($_.Name) $($_.Details)"
}

if ($Failed.Count -gt 0) {
    exit 1
}
