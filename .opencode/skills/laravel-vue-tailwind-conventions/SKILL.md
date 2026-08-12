---
name: laravel-vue-tailwind-conventions
description: Gunakan skill ini setiap kali mengerjakan fitur baru, memperbaiki bug, atau merangkum perubahan pada proyek Laravel + Vue + Tailwind ini. Mencakup pola penamaan, struktur folder, checklist verifikasi sebelum menganggap tugas selesai, dan format ringkasan pekerjaan.
---

# Laravel + Vue + Tailwind — Skill Konvensi Proyek

## Kapan dipakai
Setiap kali agent akan: menambah route/controller, membuat komponen Vue baru, mengubah migration/model, atau merangkum hasil kerja ke pengguna.

## Alur kerja yang disarankan
1. **Baca dulu** `AGENTS.md` di root untuk konteks stack & konvensi.
2. **Rencanakan** perubahan: file mana yang disentuh (backend controller/model, frontend komponen Vue, route).
3. **Eksekusi** perubahan sesuai konvensi:
   - Backend: Controller ramping → Service class untuk logic kompleks → Form Request untuk validasi.
   - Frontend: Komponen `.vue` baru pakai `<script setup>`, styling Tailwind utility-first.
4. **Verifikasi**:
   - `php artisan test` untuk backend.
   - `npm run build` untuk memastikan asset frontend tidak error.
   - Cek route terdaftar dengan `php artisan route:list` bila menambah endpoint.
5. **Ringkas hasil kerja** ke pengguna dengan format berikut (lihat bagian "Format ringkasan").

## Format ringkasan pekerjaan (WAJIB dipakai di akhir setiap task)
Ringkasan harus singkat, dalam bahasa Indonesia jika pengguna berbahasa Indonesia, mencakup:
- **Apa yang berubah** (daftar file, maksimal 1 baris per file)
- **Kenapa** (alasan singkat, bukan narasi panjang)
- **Cara verifikasi** (perintah spesifik yang bisa dijalankan pengguna, misal `php artisan test --filter=NamaTest`)
- **Yang belum dikerjakan / perlu perhatian** (kalau ada)

Jangan menulis ringkasan dalam bentuk esai panjang atau mengulang seluruh isi diff — cukup poin-poin actionable.

## Checklist sebelum bilang "selesai"
- [ ] Tidak ada error di `npm run build`
- [ ] Test terkait lulus (kalau ada)
- [ ] Tidak ada file `.env`, `vendor/`, `node_modules/` yang ter-commit
- [ ] Komponen Vue baru pakai Composition API + Tailwind utility class
- [ ] Endpoint baru punya validasi Form Request
