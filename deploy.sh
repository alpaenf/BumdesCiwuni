#!/bin/bash

# Script Deploy simpan-pinjam
# Cara penggunaan: ./deploy.sh
# Pastikan script ini dijalankan dari dalam root folder project (simpan-pinjam)

# Konfigurasi Path (Sesuaikan jika perlu)
# Asumsi: Folder project dan 'public_html' berada di level yang sama di hosting
PROJECT_FOLDER=$(basename "$PWD")
PUBLIC_HTML_PATH="../public_html"

echo "========================================"
echo "🚀 Memulai Deployment simpan-pinjam"
echo "========================================"

# 1. Git Pull
echo "📥 1. Mengambil update terbaru dari Git..."
git pull origin main

if [ $? -ne 0 ]; then
    echo "❌ Gagal melakukan git pull. Cek koneksi atau konflik. (Lanjut proses...)"
fi

# 1.2 Build frontend assets (public/build tidak disimpan di git)
echo "🛠️  1.2. Build aset frontend (Vite)..."
if command -v npm >/dev/null 2>&1; then
    if [ -f package-lock.json ]; then
        npm ci --no-audit --no-fund || npm install --no-audit --no-fund
    else
        npm install --no-audit --no-fund
    fi
    npm run build
    if [ $? -ne 0 ]; then
        echo "❌ Build frontend gagal. Proses deploy dihentikan."
        exit 1
    fi
else
    if [ -f "public/build/manifest.json" ]; then
        echo "⚠️  npm tidak ditemukan. Lewati build server dan gunakan aset public/build yang sudah ada dari git."
    else
        echo "❌ npm tidak ditemukan dan public/build/manifest.json tidak ada."
        echo "   Solusi: build di lokal/CI lalu commit folder public/build."
        exit 1
    fi
fi

# 1.5 Check .env configuration
echo "🔧 1.5. Memeriksa konfigurasi .env..."
if ! grep -q "SESSION_SECURE_COOKIE=true" .env 2>/dev/null; then
    echo "⚠️  Warning: SESSION_SECURE_COOKIE belum di-set ke true di .env"
    echo "   Pastikan update .env dengan konfigurasi yang tepat untuk production."
fi

# 2. Pindahkan isi folder public ke public_html
echo "📂 2. Menyalin aset dari folder public ke $PUBLIC_HTML_PATH..."
# Buat folder jika belum ada (opsional, jaga-jaga)
mkdir -p "$PUBLIC_HTML_PATH"
# Menyalin semua isi folder public ke public_html
cp -r public/* "$PUBLIC_HTML_PATH/"

# Salin atau Buat .htaccess standard Laravel
echo "📝 Menyalin/Membuat .htaccess di public_html pengarah route..."
cat > "$PUBLIC_HTML_PATH/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# 3. Update index.php
echo "📝 3. Memperbarui index.php di public_html..."
cat > "$PUBLIC_HTML_PATH/index.php" << EOL
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists(\$maintenance = __DIR__.'/../$PROJECT_FOLDER/storage/framework/maintenance.php')) {
    require \$maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../$PROJECT_FOLDER/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application \$app */
\$app = require_once __DIR__.'/../$PROJECT_FOLDER/bootstrap/app.php';

\$app->handleRequest(Request::capture());
EOL

# 4. Fix Storage Symlink
echo "🔗 4. Memperbaiki Symbolic Link Storage..."
# Hapus link/folder storage lama di public_html untuk memastikan bersih
rm -rf "$PUBLIC_HTML_PATH/storage"

# Buat symlink baru yang benar
# Dari: public_html/storage
# Ke:   ../simpan-pinjam/storage/app/public
ln -s "../$PROJECT_FOLDER/storage/app/public" "$PUBLIC_HTML_PATH/storage"

# 4.2. Fix Uploads Symlink
echo "🔗 4.2. Memperbaiki Symbolic Link Uploads..."
# Pastikan folder target di project root ada
mkdir -p public/uploads/pinjaman public/uploads/nasabah public/uploads/kwitansi public/uploads/news public/uploads/portal

# Jika public_html/uploads adalah folder asli (bukan symlink) dan berisi data, pindahkan ke project root
if [ -d "$PUBLIC_HTML_PATH/uploads" ] && [ ! -L "$PUBLIC_HTML_PATH/uploads" ]; then
    echo "   Memindahkan file upload yang ada di public_html/uploads ke project root..."
    cp -rn "$PUBLIC_HTML_PATH/uploads"/* public/uploads/ 2>/dev/null
    rm -rf "$PUBLIC_HTML_PATH/uploads"
else
    # Jika itu link atau folder kosong, hapus saja untuk dibuat ulang
    rm -rf "$PUBLIC_HTML_PATH/uploads"
fi

# Buat symlink baru yang benar
ln -s "../$PROJECT_FOLDER/public/uploads" "$PUBLIC_HTML_PATH/uploads"

# 5. Composer Install (skip dev, ignore platform reqs karena perbedaan versi PHP)
echo "📦 5. Menjalankan Composer Install..."
if command -v php84 > /dev/null 2>&1; then
    php84 composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs 2>/dev/null || \
    php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs
else
    php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs
fi

# 6. Jalankan Database Migration
echo "🗃️  6. Menjalankan Database Migration..."
php artisan migrate --force

# 7. Clear & Cache Laravel
echo "🧹 7. Membersihkan dan re-cache Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "========================================"
echo "✅ Deployment simpan-pinjam Selesai!"
echo "========================================"
