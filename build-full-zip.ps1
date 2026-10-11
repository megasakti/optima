# PowerShell Script: Packaging Optima Finance LENGKAP (Termasuk VENDOR) untuk Upload File Manager
$ErrorActionPreference = "Stop"

$destinationZip = "optima-full-ready.zip"
if (Test-Path $destinationZip) {
    Remove-Item $destinationZip -Force
}

Write-Host "Mempersiapkan berkas lengkap untuk upload File Manager..." -ForegroundColor Cyan

# 1. Pastikan root .htaccess ada
$rootHtaccess = @"
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Lindungi berkas sensitif dari akses publik
    RewriteRule ^(\.env|composer\.(json|lock)|package(-lock)?\.json|phpunit\.xml|artisan) - [F,L,NC]
    RewriteRule ^storage/logs/ - [F,L,NC]
    # Cegah loop redirect jika request sudah berada di dalam /public
    RewriteCond %{REQUEST_URI} !^/public/
    # Arahkan semua request ke folder public
    RewriteRule ^(.*)$ public/`$1 [L]
</IfModule>
"@
Set-Content -Path (Join-Path $PSScriptRoot ".htaccess") -Value $rootHtaccess -Encoding UTF8

# 2. Folder dan file yang disertakan
$includeItems = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "vendor",
    "artisan",
    "composer.json",
    "composer.lock",
    ".env.example",
    ".htaccess"
)

# Buat folder sementara
$tempDir = Join-Path $PSScriptRoot "temp_deploy_full"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

foreach ($item in $includeItems) {
    $sourcePath = Join-Path $PSScriptRoot $item
    if (Test-Path $sourcePath) {
        $destPath = Join-Path $tempDir $item
        Write-Host "Menyalin $item..." -ForegroundColor Gray
        Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
    }
}

# Bersihkan cache dari folder bootstrap/cache dan storage
$bootstrapCache = Join-Path $tempDir "bootstrap\cache"
if (Test-Path $bootstrapCache) {
    Get-ChildItem -Path $bootstrapCache -Exclude ".gitignore" | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}

$storageLogs = Join-Path $tempDir "storage\logs"
if (Test-Path $storageLogs) {
    Get-ChildItem -Path $storageLogs -Exclude ".gitignore" | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}

$storageViews = Join-Path $tempDir "storage\framework\views"
if (Test-Path $storageViews) {
    Get-ChildItem -Path $storageViews -Exclude ".gitignore" | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}

$storageSessions = Join-Path $tempDir "storage\framework\sessions"
if (Test-Path $storageSessions) {
    Get-ChildItem -Path $storageSessions -Exclude ".gitignore" | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}

# Kompresi ke ZIP
Write-Host "Mengompresi seluruh paket ke $destinationZip (ini memerlukan waktu ~10-20 detik)..." -ForegroundColor Cyan
Compress-Archive -Path "$tempDir\*" -DestinationPath (Join-Path $PSScriptRoot $destinationZip) -CompressionLevel Optimal

# Bersihkan folder sementara
Remove-Item $tempDir -Recurse -Force

$zipSize = (Get-Item (Join-Path $PSScriptRoot $destinationZip)).Length / 1MB
Write-Host "BERHASIL! $destinationZip ($([math]::Round($zipSize, 2)) MB) siap diunggah ke File Manager Hostinger!" -ForegroundColor Green
