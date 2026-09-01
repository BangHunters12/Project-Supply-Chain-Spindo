# ==========================================
# Stage 1: Frontend Build (Node.js & Vite)
# ==========================================
FROM node:20-alpine AS frontend

WORKDIR /app

# Install dependencies terlebih dahulu (untuk caching)
COPY package.json package-lock.json ./
RUN npm ci

# Copy seluruh source code
COPY . .

# Build assets Vue & Tailwind (Vite)
RUN npm run build


# ==========================================
# Stage 2: Production PHP & Nginx Server
# ==========================================
# Menggunakan base image serversideup yang sudah dioptimasi untuk Laravel + Nginx
FROM serversideup/php:8.2-fpm-nginx

# Environment variable untuk produksi
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Pindah ke user root sementara untuk setup file & permission
USER root

# Hapus file default yang ada di /var/www/html (jika ada)
RUN rm -rf /var/www/html/*

# Copy seluruh file project dari lokal ke dalam container
COPY . /var/www/html

# Install dependency PHP tanpa dependensi dev
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy hasil build Vite dari Stage 1
COPY --from=frontend /app/public/build /var/www/html/public/build

# Atur permission yang benar untuk folder storage & cache Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Kembali menggunakan user non-root untuk keamanan (www-data)
USER www-data

# (Opsional) Jika ingin otomatis menjalankan migration saat deploy di Railway,
# hilangkan tanda pagar di bawah ini:
# ENV AUTORUN_LARAVEL_MIGRATION=true
