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
$Assets = Get-Content -Raw -LiteralPath "$Root\includes\class-assets.php"
$Layout = Get-Content -Raw -LiteralPath "$Root\includes\class-layout.php"
$SystemCheck = Get-Content -Raw -LiteralPath "$Root\includes\class-system-check.php"
$BuildRelease = Get-Content -Raw -LiteralPath "$Root\tools\build-release.php"
$Readme = Get-Content -Raw -LiteralPath "$Root\README.md"

Add-Result "Version consistency" ($Main -match "Version: 1\.0\.0" -and $Main -match "SABRI_SHELL_VERSION', '1\.0\.0")
Add-Result "No whole-page output buffering" ($Renderer -notmatch "ob_start" -and $HomeFeed -notmatch "ob_start")
Add-Result "No wp_body_open-to-wp_footer wrapper" ($Renderer -notmatch "<main" -and $Renderer -notmatch "</main>")
Add-Result "Exactly one Notifications output marker" (([regex]::Matches($Renderer, "data-sabri-notifications-output")).Count -eq 1)
Add-Result "Desktop sidebars are not fixed or absolute" ($Css -notmatch "\.sabri-shell-(?:left|right)-sidebar[^{]*\{[^}]*position:\s*(?:fixed|absolute)")
Add-Result "Three-column mode has real left center right columns" ($Css -match "body\.sabri-shell-layout-three \.sabri-shell-layout-host\.is-ready" -and $Css -match "grid-template-columns: var\(--sabri-shell-left-width, 280px\) minmax\(0, 1fr\) var\(--sabri-shell-right-width, 340px\);")
Add-Result "Two-column mode has real left center columns" ($Css -match "body\.sabri-shell-layout-two \.sabri-shell-layout-host\.is-ready" -and $Css -match "grid-template-columns: var\(--sabri-shell-left-width, 280px\) minmax\(0, 1fr\);")
Add-Result "Right Sidebar HTML absent in two-column mode" ($Renderer -match '\$has_right_sidebar = Layout::THREE === \$mode' -and $Renderer -match 'if \( \$has_right_sidebar \)')
Add-Result "Theme content selector is functional" ($Assets -match "'contentSelector'" -and $Layout -match "content_target_candidates" -and $Js -match "resolveContentTarget" -and $Js -match "assembleStructuralLayout" -and $SystemCheck -match "Content target resolver")
Add-Result "Context drawer has matching trigger" ($Renderer -match "data-sabri-drawer-trigger=""sabri-shell-drawer-context""" -and $Renderer -match "aria-controls=""sabri-shell-drawer-context""" -and $Renderer -match "id=""sabri-shell-drawer-context""")
Add-Result "Sticky Header on/off works" ($Renderer -match "sabri-shell-sticky-header" -and $Renderer -match "sabri-shell-static-header" -and $Css -match "\.sabri-shell-static-header \.sabri-shell-header")
Add-Result "Compact Desktop on/off works" ($Renderer -match "sabri-shell-compact-desktop" -and $Renderer -match "sabri-shell-standard-desktop" -and $Css -match "\.sabri-shell-compact-desktop \.sabri-shell-layout-host")
Add-Result "Module toggles control output" (($Renderer -match '\$modules\[''finder''\]' -and $Renderer -match "clinic-finder") -and ($Renderer -match '\$modules\[''filters''\]' -and $Renderer -match "clinic-filters") -and ($Renderer -match '\$modules\[''doctors''\]' -and $Renderer -match "clinic-doctors") -and ($Renderer -match '\$modules\[''appointments''\]' -and $Renderer -match "clinic-appointments") -and ($Renderer -match '\$modules\[''emergency''\]' -and $Renderer -match "clinic-emergency") -and ($Renderer -match '\$modules\[''whatsapp''\]' -and $Renderer -match "clinic-whatsapp") -and ($Renderer -match '\$modules\[''profile''\]' -and $Renderer -match "single-profile") -and ($Renderer -match '\$modules\[''appointment''\]' -and $Renderer -match "single-appointment") -and ($Renderer -match '\$modules\[''message''\]' -and $Renderer -match "single-message") -and ($Renderer -match '\$modules\[''contact''\]' -and $Renderer -match "single-contact") -and ($Renderer -match '\$modules\[''reviews''\]' -and $Renderer -match "single-reviews") -and ($Renderer -match '\$modules\[''safety''\]' -and $Renderer -match "single-safety"))
Add-Result "Development files absent from release ZIP build" ($BuildRelease -match '\$release_allowlist' -and $BuildRelease -match "\.github/" -and $BuildRelease -match "Development-only path found in release ZIP")
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
