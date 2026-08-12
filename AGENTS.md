# AGENTS.md

## Tentang proyek ini
Aplikasi web berbasis **Laravel** (backend/API + routing) dengan **Vue 3** (frontend, Composition API + `<script setup>`) dan **Tailwind CSS v4** (styling), dibundel lewat **Vite**.

## Stack & versi
- PHP >= 8.2, Laravel 11+
- Vue 3 (Composition API, SFC `.vue`)
- Tailwind CSS v4 (via `@tailwindcss/vite`, bukan `tailwind.config.js` lama)
- Vite sebagai bundler (`laravel-vite-plugin`)
- Package manager: Composer (PHP) & npm (JS)

## Struktur folder penting
- `app/` — logic backend Laravel (Models, Controllers, dsb)
- `routes/web.php` & `routes/api.php` — routing
- `resources/js/app.js` — entry point Vue
- `resources/js/components/*.vue` — komponen Vue (Single File Component)
- `resources/css/app.css` — entry Tailwind (`@import "tailwindcss";`)
- `resources/views/*.blade.php` — layout Blade yang memuat `@vite(...)` dan elemen `#app`
- `database/migrations/` — skema database

## Konvensi coding
- Komponen Vue WAJIB pakai `<script setup>` + Composition API, bukan Options API.
- Styling HANYA pakai utility class Tailwind langsung di template — hindari file CSS custom kecuali benar-benar perlu.
- Penamaan komponen Vue: PascalCase (`UserCard.vue`), penamaan file Blade: kebab-case.
- Controller Laravel ramping — logic bisnis kompleks masuk ke Service class atau Form Request, bukan menumpuk di Controller.
- Query database pakai Eloquent, hindari raw SQL kecuali perlu optimasi khusus.
- Setiap endpoint API baru harus punya validasi lewat Form Request (`php artisan make:request`).

## Perintah yang sering dipakai
```bash
composer install          # install dependency PHP
npm install                # install dependency JS
php artisan serve          # jalankan server Laravel
npm run dev                 # jalankan Vite dev server (HMR)
npm run build                # build produksi
php artisan migrate          # jalankan migration
php artisan make:controller NamaController
php artisan make:model NamaModel -m
```

## Sebelum agent menganggap tugas selesai
1. Jalankan `php artisan test` (atau `./vendor/bin/pest`) kalau ada test terkait.
2. Pastikan `npm run build` tidak error (cek tidak ada import/asset yang rusak).
3. Jangan commit file `.env`, `vendor/`, atau `node_modules/`.
4. Kalau menambah route baru, pastikan terdaftar di `routes/web.php` atau `routes/api.php` sesuai konteks (web vs API).

## Yang harus dihindari
- Jangan pakai Options API di komponen Vue baru.
- Jangan menulis CSS custom kalau utility Tailwind sudah cukup.
- Jangan taruh credential/API key langsung di kode — selalu lewat `.env` + `config/`.
