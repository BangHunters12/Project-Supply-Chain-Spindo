---
name: code-style
description: Aturan gaya kode untuk proyek Laravel + Vue + Tailwind. Selalu aktif.
activation: always_on
---

# Code Style — Laravel + Vue + Tailwind

- Komponen Vue baru wajib `<script setup>` + Composition API.
- Styling hanya lewat utility class Tailwind di template, bukan file `.css` custom baru.
- Controller Laravel tetap ramping; logic bisnis kompleks dipindah ke Service class di `app/Services/`.
- Semua input dari request divalidasi lewat Form Request class, bukan validasi manual di controller.
- Penamaan variabel & method: camelCase di PHP dan JS, snake_case hanya untuk nama kolom database.
- Setiap migration baru harus reversible (isi method `down()`).

## Larangan emoji/emoticon
- JANGAN pernah pakai emoji atau emoticon di UI (heading, label kartu, item menu, badge, notifikasi, dsb) maupun di commit message. Contoh yang HARUS dihindari: 🗺️, 📦, 🔄, ✅ ditempel di depan teks judul.
- Kalau butuh penanda visual, pakai icon set konsisten (Lucide/Heroicons via komponen SVG) yang diberi warna dan ukuran lewat Tailwind, bukan karakter emoji dari font sistem.

## Anti tampilan generik-"AI" (WAJIB dibaca sebelum membangun UI baru)
Banyak dashboard yang dibuat AI langsung terlihat template karena pola-pola berikut. Hindari:
- Kartu ringkasan template: judul di atas kiri (huruf kecil kapital semua, abu-abu) + angka besar + badge oranye/hijau kecil di kanan. Kalau semua kartu statistik punya struktur identik seperti ini, itu ciri "AI look" — variasikan hierarki, ukuran, atau highlight per kartu sesuai kepentingan datanya, jangan copy-paste komponen yang sama 4 kali.
- Badge status berwarna solid oranye/hijau/biru dengan sudut rounded-full dan teks kapital pendek ("AVAILABLE", "SYSTEM ONLINE") ditempel di banyak tempat — ini pola template. Kalau dipakai, batasi ke satu makna konsisten (misalnya hanya untuk status), jangan jadi dekorasi di setiap elemen.
- Ikon generik kotak rounded gradient oranye/ungu di pojok tiap kartu (dashboard-builder look). Ganti dengan sistem ikon yang benar-benar merepresentasikan datanya, bukan dekorasi kosong, atau hilangkan kalau tidak menambah pemahaman.
- Warna aksen tunggal yang dipakai berulang di semua tombol/badge/border tanpa hierarki (semua oranye). Buat hierarki warna: satu warna aksen untuk aksi utama, warna netral untuk elemen sekunder, jangan semua elemen penting diwarnai sama.
- Header aplikasi dengan logo kotak rounded-gradient + judul besar + subjudul abu-abu kecil di sebelahnya — pola sangat umum di dashboard AI-generated. Pertimbangkan pendekatan lain: wordmark tipografi kuat tanpa ikon kotak, atau layout header yang lebih spesifik ke industri (di sini: industri pipa baja/manufaktur — bisa ambil referensi visual dari dunia industrial/warehouse, bukan generic SaaS dashboard).
- Setiap membangun atau mengubah UI baru, jalankan proses ini secara internal sebelum menulis kode: (1) tentukan 4-6 warna spesifik untuk brand ini (bukan default oranye-ungu-gradient), (2) tentukan pasangan tipografi display+body yang punya karakter, bukan font default Tailwind (Inter) tanpa variasi, (3) tentukan satu elemen "signature" yang unik untuk dashboard ini, dan sisanya dibuat tenang/disiplin di sekitarnya, (4) periksa ulang: kalau hasilnya mirip dashboard admin generik mana pun, revisi.

## Domain Knowledge — Produk Pipa PT SPINDO Tbk

### Kategori Produk (pipe_categories)
- **Pipa Hitam (PH)**: Pipa baja karbon tanpa lapisan galvanis. Dipakai untuk konstruksi, scaffolding, gas.
- **Pipa Galvanis (PG)**: Pipa baja karbon dengan lapisan seng (zinc). Tersedia dalam varian **dengan drat** (threaded) dan **tanpa drat** (plain end). Kode SAP sama untuk drat dan non-drat.

### Ukuran Nominal & Isi Bundle (pcs_per_bundle)
| Ukuran (inci) | Pcs/Bundle |
|---|---|
| 1/2" | 217 |
| 3/4" | 169 |
| 1" | 127 |
| 1-1/4" | 91 |
| 1-1/2" | 61 |
| 2" | 61 |
| 2-1/2" | 37 |
| 3" | 37 |
| 4" | 19 |
| 5" | 10 |
| 6" | 10 |
| 8" | 7 |

### Kode SAP (pipe_products.sap_code)
Setiap ukuran punya beberapa varian spesifikasi (spec_name) dengan kode SAP yang berbeda. Contoh untuk 1":
- CAC (OD 31.2mm): MR 1, B SIZE, SD, K SIZE, W SIZE, BSA
- CAK (OD 32.0mm): BSM
- CAX (OD 33.3mm): TIPIS
- CAY (OD 33.4mm): MEDIUM, SCH. 40
- CBC (OD 33.5mm): TEBAL

Varian spec yang tersedia: MR 1, B SIZE, SD, K SIZE, W SIZE, BSA, BSM, TIPIS, MEDIUM, SCH. 40, TEBAL.

### Format Kode Material (material_code)
Contoh: `H1B07CAY0310-06000`
- H = Hitam (G = Galva)
- 1B = Plant/unit
- 07/08/10 = Klasifikasi spec
- CAY = Kode SAP
- 0310 = Dimensi referensi
- 06000 = Panjang (mm) → 6 meter

### Panjang Standar
Semua pipa diproduksi dengan panjang standar **6 meter** (6000mm). Panjang ini terenkode di bagian akhir kode material.

### Berat
Berat per bundle bervariasi per ukuran dan spec. Data tersedia untuk varian TIPIS dan MEDIUM dari tabel produksi. Berat dinyatakan dalam kilogram per bundle.
