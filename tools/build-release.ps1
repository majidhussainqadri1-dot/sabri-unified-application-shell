$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Slug = "sabri-unified-application-shell"
$Version = "1.0.0"
$Prefix = "20-$Slug-$Version"
$Release = Join-Path $Root "release"
$StageRoot = Join-Path $env:TEMP ("sabri-shell-release-" + [guid]::NewGuid().ToString("N"))
$StagePlugin = Join-Path $StageRoot $Slug

Set-Location $Root
if (Test-Path -LiteralPath $Release) {
    Get-ChildItem -LiteralPath $Release -Force | Remove-Item -Force -Recurse
} else {
    New-Item -ItemType Directory -Force -Path $Release | Out-Null
}

& "$Root\tools\run-local-static-checks.ps1"

New-Item -ItemType Directory -Force -Path $StagePlugin | Out-Null
$AllowList = @(
    "sabri-unified-application-shell.php",
    "includes",
    "admin",
    "assets",
    "languages",
    "uninstall.php",
    "readme.txt",
    "README.md",
    "CHANGELOG.md",
    "MIGRATION.md",
    "ROLLBACK.md",
    "STAGING-ACCEPTANCE.md"
)
foreach ($ItemName in $AllowList) {
    $Source = Join-Path $Root $ItemName
    if (Test-Path -LiteralPath $Source) {
        Copy-Item -LiteralPath $Source -Destination $StagePlugin -Recurse -Force
    }
}

$ZipPath = Join-Path $Release "$Prefix.zip"
$ShaPath = Join-Path $Release "$Prefix.sha256"
$ReportPath = Join-Path $Release "$Prefix-TEST-REPORT.md"
Compress-Archive -LiteralPath $StagePlugin -DestinationPath $ZipPath -Force

Add-Type -AssemblyName System.IO.Compression.FileSystem
$Archive = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
try {
    $Top = @($Archive.Entries | ForEach-Object { (($_.FullName -replace "\\", "/") -split "/")[0] } | Sort-Object -Unique)
    if ($Top.Count -ne 1 -or $Top[0] -ne $Slug) {
        throw "ZIP must contain exactly one top-level folder."
    }
    foreach ($Entry in $Archive.Entries) {
        $EntryName = $Entry.FullName -replace "\\", "/"
        if ($EntryName -match "(^/|\.\./)") {
            throw "Path traversal rejected in ZIP."
        }
        $InsidePlugin = $EntryName -replace "^$Slug/", ""
        foreach ($DevPath in @(".github/", "tools/", "tests/", "TASK_LOG.md", ".gitignore", "release/", "vendor/", "node_modules/")) {
            if ($InsidePlugin -eq $DevPath.TrimEnd("/") -or $InsidePlugin.StartsWith($DevPath)) {
                throw "Development-only path found in release ZIP: $InsidePlugin"
            }
        }
        $Stream = $Entry.Open()
        try {
            $Buffer = New-Object byte[] 8192
            while ($Stream.Read($Buffer, 0, $Buffer.Length) -gt 0) {}
        } finally {
            $Stream.Dispose()
        }
    }
} finally {
    $Archive.Dispose()
}

$Hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $ZipPath).Hash.ToLowerInvariant()
"$Hash  $(Split-Path -Leaf $ZipPath)" | Set-Content -LiteralPath $ShaPath -Encoding ASCII

$Report = @"
# Sabri Unified Application Shell Test Report

Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss K")

## Automated and Static Tests

Local PowerShell static checks passed:
- Version consistency
- No whole-page output buffering
- No wp_body_open-to-wp_footer wrapper
- Exactly one Notifications output marker
- Responsive overflow rules
- Accessible drawer JavaScript
- README required limitations
- Dangerous PHP function scan
- Hard-coded secret scan
- No external runtime, CDN, or remote font dependencies
- No bundled font binaries
- CSS brace sanity
- ZIP CRC/read validation
- Path traversal rejection
- Exactly one ZIP top-level directory
- Development-only files absent from installable ZIP
- Exact release filenames

## WordPress Stub Tests

PHP/WordPress stub tests are included in tools/run-tests.php and run in GitHub Actions where PHP is available. Local PHP was not available in this Codex environment.

## Manual Staging Tests Still Required

- Activate on Hostinger staging before production.
- Verify live theme spacing across 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600, and 1920 px.
- Verify real companion-plugin destinations for messages, notifications, appointments, marketplace, doctors, and clinic pages.
- Verify cross-browser behavior manually. This report does not claim live production, live database, Hostinger, or cross-browser testing.

Messaging backend is not created by this plugin. Real calls are not created. End-to-end encryption is not claimed. Live streaming is not created. AI recommendations are not claimed. Full compatibility with every WordPress theme is not claimed. Hostinger staging testing is required before production activation.
"@
$Report | Set-Content -LiteralPath $ReportPath -Encoding UTF8

Remove-Item -LiteralPath $StageRoot -Recurse -Force

Write-Output "Built $ZipPath"
Write-Output "Built $ShaPath"
Write-Output "Built $ReportPath"
