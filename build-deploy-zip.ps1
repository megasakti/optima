# PowerShell Script: Packaging Optima Finance untuk Deploy ke Hostinger
$ErrorActionPreference = "Stop"

$destinationZip = "optima-deploy.zip"
if (Test-Path $destinationZip) {
    Remove-Item $destinationZip -Force
}

Write-Host "Mempersiapkan berkas untuk packaging..." -ForegroundColor Cyan

# Daftar folder dan file yang dimasukkan
$includeItems = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "artisan",
    "composer.json",
    "composer.lock",
    ".env.example",
    "deploy-hostinger.sh"
)

# Buat folder sementara
$tempDir = Join-Path $PSScriptRoot "temp_deploy_build"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

foreach ($item in $includeItems) {
    $sourcePath = Join-Path $PSScriptRoot $item
    if (Test-Path $sourcePath) {
        $destPath = Join-Path $tempDir $item
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

# Zip seluruh isi folder sementara
Write-Host "Mengompresi berkas ke $destinationZip..." -ForegroundColor Cyan
Compress-Archive -Path "$tempDir\*" -DestinationPath (Join-Path $PSScriptRoot $destinationZip) -CompressionLevel Optimal

# Hapus folder sementara
Remove-Item $tempDir -Recurse -Force

$zipSize = (Get-Item (Join-Path $PSScriptRoot $destinationZip)).Length / 1MB
Write-Host "Berhasil membuat $destinationZip ($([math]::Round($zipSize, 2)) MB)" -ForegroundColor Green
Write-Host "Berkas siap di-upload ke Hostinger via SCP / SFTP / File Manager." -ForegroundColor Yellow
