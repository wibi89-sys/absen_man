#!/usr/bin/env bash
# ==============================================================================
# MADRASAH ONE - VPS Automated Deployment & Installation Script
# Target OS: Ubuntu 22.04 / 24.04 LTS, Debian 11/12
# Recommended PHP: PHP 8.3 / PHP 8.4 with Nginx + MySQL/MariaDB
# ==============================================================================

set -e

# Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

clear
echo -e "${CYAN}==================================================================${NC}"
echo -e "${GREEN}       MADRASAH ONE - SCRIPT INSTALASI & DEPLOYMENT HOSTING       ${NC}"
echo -e "${CYAN}==================================================================${NC}"
echo -e "Script ini akan mengotomatisasi instalasi dependensi, konfigurasi .env,"
echo -e "migrasi database, perizinan folder, dan optimasi cache produksi."
echo ""

# 1. Periksa Apakah PHP Terpasang
echo -e "${BLUE}[1/8] Memeriksa Instalasi PHP...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}[ERROR] PHP belum terpasang di server ini!${NC}"
    echo -e "Silakan instal PHP 8.3 atau 8.4 beserta ekstensi yang dibutuhkan:"
    echo -e "sudo apt update && sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "  ${GREEN}✓${NC} PHP terdeteksi: Version ${PHP_VERSION}"

# 2. Periksa Ekstensi PHP
echo -e "${BLUE}[2/8] Memeriksa Ekstensi PHP yang Dibutuhkan...${NC}"
REQUIRED_EXT=("pdo_mysql" "mbstring" "xml" "curl" "zip" "bcmath" "fileinfo" "json")
MISSING_EXT=()

for ext in "${REQUIRED_EXT[@]}"; do
    if ! php -m | grep -qi "^$ext$"; then
        MISSING_EXT+=("$ext")
    fi
done

if [ ${#MISSING_EXT[@]} -gt 0 ]; then
    echo -e "${YELLOW}[PERINGATAN] Ekstensi PHP berikut belum aktif:${NC} ${MISSING_EXT[*]}"
    echo -e "Pastikan untuk menginstalnya agar fitur absensi & PDF berjalan lancar."
else
    echo -e "  ${GREEN}✓${NC} Seluruh ekstensi PHP penting telah lengkap."
fi

# 3. Periksa / Pasang Composer
echo -e "${BLUE}[3/8] Memeriksa Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}  Composer tidak ditemukan secara global, mengunduh composer.phar lokal...${NC}"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');"
    COMPOSER="php composer.phar"
else
    COMPOSER="composer"
    echo -e "  ${GREEN}✓${NC} Composer terpasang: $($COMPOSER --version | head -n 1)"
fi

# 4. Konfigurasi File Lingkungan (.env)
echo -e "${BLUE}[4/8] Menyiapkan Konfigurasi Lingkungan (.env)...${NC}"
if [ ! -f .env ]; then
    if [ -f .env.production.example ]; then
        cp .env.production.example .env
        echo -e "  ${GREEN}✓${NC} Berhasil menyalin .env.production.example ke .env"
    else
        cp .env.example .env
        echo -e "  ${GREEN}✓${NC} Berhasil menyalin .env.example ke .env"
    fi
else
    echo -e "  ${GREEN}✓${NC} File .env sudah ada, mempertahankan konfigurasi saat ini."
fi

# 5. Pasang Dependensi Vendor (Production Mode)
echo -e "${BLUE}[5/8] Menginstal Dependensi PHP (Composer)...${NC}"
$COMPOSER install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6. Generate Kunci Aplikasi & Symlink Storage
echo -e "${BLUE}[6/8] Menyiapkan APP_KEY dan Storage Link...${NC}"
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate --force
    echo -e "  ${GREEN}✓${NC} APP_KEY berhasil di-generate."
fi

php artisan storage:link --quiet || true
echo -e "  ${GREEN}✓${NC} Symlink storage publik berhasil dibuat."

# 7. Migrasi Database & Seeder
echo -e "${BLUE}[7/8] Migrasi Database...${NC}"
read -p "Apakah Anda ingin menjalankan migrasi database sekarang? (y/n) [default: y]: " RUN_MIGRATE
RUN_MIGRATE=${RUN_MIGRATE:-y}

if [[ "$RUN_MIGRATE" =~ ^[Yy]$ ]]; then
    read -p "Sertakan data seeder akun default (admin, guru, siswa, dll)? (y/n) [default: y]: " RUN_SEED
    RUN_SEED=${RUN_SEED:-y}

    if [[ "$RUN_SEED" =~ ^[Yy]$ ]]; then
        echo -e "  Menjalankan migrasi dan seeding data default..."
        php artisan migrate --force --seed
    else
        echo -e "  Menjalankan migrasi struktur tabel saja..."
        php artisan migrate --force
    fi
    echo -e "  ${GREEN}✓${NC} Migrasi database berhasil."
else
    echo -e "  ${YELLOW}!${NC} Migrasi dilewati. Anda dapat menjalankannya nanti dengan: php artisan migrate --force"
fi

# 8. Set Izin File & Cache Optimasi Produksi
echo -e "${BLUE}[8/8] Mengoptimalkan Cache & Mengatur Izin Folder...${NC}"

# Clear dan Cache Produksi
php artisan optimize:clear --quiet
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
php artisan event:cache --quiet

# Deteksi Web Server User (www-data / nginx / cpanel user)
CURRENT_USER=$(whoami)
WEB_USER="www-data"
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    WEB_USER="$CURRENT_USER"
fi

echo -e "  Mengatur hak akses folder storage & bootstrap/cache ke user '${WEB_USER}'..."
if [ "$CURRENT_USER" = "root" ]; then
    chown -R ${WEB_USER}:${WEB_USER} storage bootstrap/cache
fi
chmod -R 775 storage bootstrap/cache

echo ""
echo -e "${CYAN}==================================================================${NC}"
echo -e "${GREEN}   SELAMAT! APLIKASI MADRASAH ONE BERHASIL DIINSTAL DI HOSTING!   ${NC}"
echo -e "${CYAN}==================================================================${NC}"
echo ""
echo -e "Langkah Selanjutnya:"
echo -e "1. Pastikan ${YELLOW}APP_URL${NC} dan detail ${YELLOW}DB_*${NC} di file ${YELLOW}.env${NC} sudah sesuai dengan domain/database hosting Anda."
echo -e "2. Arahkan Document Root Web Server (Nginx / Apache) ke folder: ${GREEN}$(pwd)/public${NC}"
echo -e "3. Pasang sertifikat SSL gratis menggunakan Certbot:"
echo -e "   ${CYAN}sudo certbot --nginx -d domain-madrasah-anda.sch.id${NC}"
echo -e "4. Akun default tersimpan di file ${GREEN}akun.txt${NC}:"
echo -e "   - Super Admin : ${YELLOW}superadmin${NC} / password: ${YELLOW}password${NC}"
echo -e "   - Guru Piket  : ${YELLOW}piket${NC} / password: ${YELLOW}password${NC}"
echo -e "   - Siswa       : ${YELLOW}siswa${NC} / password: ${YELLOW}password${NC}"
echo ""
