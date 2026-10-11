#!/bin/bash
# ==============================================================
# Script Otomatisasi Deploy Laravel Optima Finance di Hostinger
# Jalankan di dalam direktori project Laravel di SSH server
# Contoh: bash deploy-hostinger.sh
# ==============================================================
set -e

echo "=========================================================="
echo "🚀 MEMULAI DEPLOYMENT OPTIMA FINANCE (HOSTINGER SSH)"
echo "=========================================================="

echo "🔍 [1/6] Memeriksa PHP & Composer..."
php -v | head -n 1
composer -V

echo "📦 [2/6] Memasang dependensi vendor (Composer Production)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔑 [3/6] Memastikan Application Key & Storage Link..."
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi
php artisan storage:link || true

echo "💾 [4/6] Menjalankan Migrasi & Seeder Database Bersih..."
php artisan migrate --force
php artisan db:seed --force

echo "⚡ [5/6] Mengoptimalkan Cache Laravel (Config, Route, View)..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔒 [6/6] Menyetel izin folder (Permissions)..."
chmod -R 775 storage bootstrap/cache

echo "=========================================================="
echo "🎉 DEPLOYMENT SELESAI DENGAN SUKSES!"
echo "=========================================================="
