#!/usr/bin/env bash
# ==============================================================================
# MADRASAH ONE - Script Instalasi untuk cPanel Terminal / CloudLinux
# Jalankan di Terminal cPanel: bash deploy-cpanel.sh
# ==============================================================================

set -e

echo "=== MEMULAI DEPLOYMENT MADRASAH ONE DI CPANEL ==="

# 1. Cari Path PHP 8.4 atau 8.3 di cPanel (EasyApache / CloudLinux alt-php)
PHP_BIN=""
POSSIBLE_PHPS=(
    "/usr/local/bin/ea-php84"
    "/usr/local/bin/ea-php83"
    "/opt/cpanel/ea-php84/root/usr/bin/php"
    "/opt/cpanel/ea-php83/root/usr/bin/php"
    "/opt/alt/php84/usr/bin/php"
    "/opt/alt/php83/usr/bin/php"
    "$(which php 2>/dev/null || true)"
)

for p in "${POSSIBLE_PHPS[@]}"; do
    if [ -x "$p" ]; then
        VER=$($p -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || true)
        if [[ "$VER" == "8.4" || "$VER" == "8.3" ]]; then
            PHP_BIN="$p"
            break
        fi
    fi
done

if [ -z "$PHP_BIN" ]; then
    echo "[PERINGATAN] PHP 8.3/8.4 tidak ditemukan otomatis. Menggunakan default php CLI:"
    PHP_BIN="php"
fi

echo "✓ Menggunakan PHP CLI: $($PHP_BIN -v | head -n 1)"

# 2. Periksa .env
if [ ! -f .env ]; then
    if [ -f .env.production.example ]; then
        cp .env.production.example .env
    else
        cp .env.example .env
    fi
    echo "✓ Dibuat file .env dari template. Jangan lupa isi DB_DATABASE, DB_USERNAME, dan DB_PASSWORD!"
fi

# 3. Jalankan Composer jika file vendor belum ada
if [ ! -d "vendor" ]; then
    echo "Menyiapkan Composer..."
    if [ ! -f "composer.phar" ]; then
        if command -v curl &>/dev/null; then
            echo "Mengunduh composer.phar via curl..."
            curl -sS https://getcomposer.org/download/latest-stable/composer.phar -o composer.phar
        elif command -v wget &>/dev/null; then
            echo "Mengunduh composer.phar via wget..."
            wget -q https://getcomposer.org/download/latest-stable/composer.phar -O composer.phar
        fi
        chmod +x composer.phar
    fi

    echo "Menginstal dependensi vendor PHP..."
    $PHP_BIN -d memory_limit=512M composer.phar install --no-dev --optimize-autoloader --no-interaction
fi

# 4. Generate Key jika belum ada
if ! grep -q "APP_KEY=base64" .env; then
    $PHP_BIN artisan key:generate --force
fi

# 5. Storage Symlink
$PHP_BIN artisan storage:link --quiet || true

# 6. Migrasi Database & Seeder
echo "Menjalankan migrasi database..."
$PHP_BIN artisan migrate --force

read -p "Apakah ingin menambahkan akun default seeder? (y/n) [default: y]: " SEED_CHOICE
SEED_CHOICE=${SEED_CHOICE:-y}
if [[ "$SEED_CHOICE" =~ ^[Yy]$ ]]; then
    $PHP_BIN artisan db:seed --force
fi

# 7. Cache Produksi
echo "Mengoptimasi cache Laravel..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# 8. Set Izin Folder
chmod -R 775 storage bootstrap/cache

echo ""
echo "=== SELESAI! APLIKASI TELAH SIAP DI CPANEL ==="
echo "Jika folder aplikasi di luar public_html (contoh: /home/user/madrasah-one):"
echo "Pastikan cPanel Document Root diarahkan ke: /home/user/madrasah-one/public"
echo "Atau buat symlink dari public_html:"
echo "  ln -s /home/username/madrasah-one/public/* /home/username/public_html/"
