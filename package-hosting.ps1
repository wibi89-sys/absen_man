# ==============================================================================
# MADRASAH ONE - Packaging Script untuk Hosting Produksi (Windows PowerShell)
# Jalankan di PowerShell: .\package-hosting.ps1
# ==============================================================================

$ErrorActionPreference = "Stop"

# Setup Path Laragon jika tersedia
$env:PATH = "C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64;C:\laragon\bin\composer;C:\laragon\bin\nodejs\node-v22;C:\laragon\bin\git\bin;" + $env:PATH

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   MADRASAH ONE - PEMBUATAN PAKET ZIP PRODUKSI HOSTING    " -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan

# 1. Build Aset Frontend
Write-Host "`n[1/4] Mengompilasi Aset Frontend (Vite Production Build)..." -ForegroundColor Yellow
npm.cmd run build

# 2. Siapkan Vendor Produksi (--no-dev)
Write-Host "`n[2/4] Menyiapkan Dependensi Vendor PHP Produksi (No-Dev)..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Definisikan File ZIP Target
$timestamp = Get-Date -Format "yyyyMMdd-HHmm"
$zipFile = "madrasah-one-production-$timestamp.zip"
$tempDir = "temp_package_madrasah_one"

if (Test-Path $tempDir) {
    Remove-Item -Recurse -Force $tempDir
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

Write-Host "`n[3/4] Menyalin file proyek produksi (mengecualikan node_modules, .git, dll)..." -ForegroundColor Yellow

# Salin direktori utama
$excludeDirs = @("node_modules", ".git", "tests", "temp_package_madrasah_one")
Get-ChildItem -Path . -Exclude $excludeDirs | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination $tempDir -Recurse -Force
}

# Bersihkan log dan cache lokal dari folder sementara
if (Test-Path "$tempDir\storage\logs") {
    Get-ChildItem -Path "$tempDir\storage\logs\*.log" | Remove-Item -Force -ErrorAction SilentlyContinue
}
if (Test-Path "$tempDir\storage\framework\cache\data") {
    Get-ChildItem -Path "$tempDir\storage\framework\cache\data\*" -Recurse | Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}
if (Test-Path "$tempDir\storage\framework\sessions") {
    Get-ChildItem -Path "$tempDir\storage\framework\sessions\*" | Remove-Item -Force -ErrorAction SilentlyContinue
}
if (Test-Path "$tempDir\storage\framework\views") {
    Get-ChildItem -Path "$tempDir\storage\framework\views\*" | Remove-Item -Force -ErrorAction SilentlyContinue
}

# 4. Buat File ZIP
Write-Host "`n[4/4] Mengompresi paket ke $zipFile..." -ForegroundColor Yellow
Compress-Archive -Path "$tempDir\*" -DestinationPath $zipFile -Force
Remove-Item -Recurse -Force $tempDir

# Kembalikan vendor dev untuk lokal
Write-Host "`nMengembalikan dependensi dev untuk lingkungan lokal..." -ForegroundColor Gray
composer install --quiet

$fileSize = (Get-Item $zipFile).Length / 1MB
Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host "   PAKET ZIP BERHASIL DIBUAT: $zipFile" -ForegroundColor Green
Write-Host "   Ukuran File: $([math]::Round($fileSize, 2)) MB" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "`nLangkah selanjutnya:"
Write-Host "1. Upload file '$zipFile' ke cPanel File Manager atau VPS Anda."
Write-Host "2. Ekstrak file tersebut di server hosting."
Write-Host "3. Buka file PANDUAN_HOSTING.md untuk panduan lengkapnya!`n"
