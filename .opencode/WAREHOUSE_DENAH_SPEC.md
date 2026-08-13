# Konteks Proyek: Denah Gudang Interaktif (SLOC Mapping)

> File ini adalah **context/spec file** untuk AI coding agent (Opencode + model GPT-5.6 "Luna" by Neosantara).
> Tujuannya: agar agent memahami (1) struktur data SLOC dari Excel, dan (2) gaya visual denah fisik
> yang harus direplikasi ke halaman web yang sedang dikembangkan.
>
> Taruh file ini di root repo (atau `.opencode/context.md`) dan referensikan di prompt kamu,
> misalnya: "Baca WAREHOUSE_DENAH_SPEC.md dulu sebelum mengerjakan task ini."

---

## 1. Domain Knowledge — Apa itu SLOC?

**SLOC** (Storage Location) adalah unit penyimpanan di gudang PT SPINDO Tbk Unit 7 Gresik.
Satu SLOC **bukan** satu blok tunggal — satu SLOC adalah **gabungan (grup) dari beberapa Blok**
yang letaknya bersebelahan secara fisik di denah.

### Aturan penggabungan Blok → SLOC

- Setiap **Gudang** (1, 2, 3, 4, dst) dibagi menjadi grid Blok dengan kode **Kolom (huruf A–L)**
  dan **Baris (angka 1, 2, 3)**.
- Blok-blok pada **baris 1** (baris paling depan/atas di denah) untuk 4 kolom berurutan
  (misal A–D) digabung menjadi **satu SLOC baris atas**.
- Blok-blok pada **baris 2 dan 3** (baris bawah, saling merge) untuk 4 kolom yang sama
  digabung menjadi **satu SLOC baris bawah**.
- Pola ini berulang per kelompok 4 kolom: **A–D**, **E–H**, **I–L** → menghasilkan
  3 pasang SLOC (atas+bawah) per Gudang, atau 6 SLOC per Gudang.

### Pola penamaan kode SLOC

Format: `SLOC 7<GUDANG><KELOMPOK><SUBBLOK>`

| Segmen | Arti | Contoh nilai |
|---|---|---|
| `7` | Prefix tetap (kode plant/unit) | selalu `7` |
| `<GUDANG>` | Huruf urutan gudang: A=Gudang1, B=Gudang2, C=Gudang3, D=Gudang4 | `A`, `B`, `C`, `D` |
| `<KELOMPOK>` | Huruf kelompok kolom: A = kolom A–D, B = kolom E–H, C = kolom I–L | `A`, `B`, `C` |
| `<SUBBLOK>` | Angka baris: 1 = baris depan (baris 1), 2 = baris belakang (baris 2+3 merge) | `1`, `2` |

Contoh: `SLOC 7AB2` = Gudang **1** (A), kelompok kolom **E–H** (B), baris belakang (2).

### Data mentah (dari Sloc_Data.xlsx, sheet "Sheet1")

| Gudang | Blok | SLOC |
|---|---|---|
| Gudang 1 | A1 - D1 | SLOC 7AA1 |
| Gudang 1 | A2, A3 - D2, D3 | SLOC 7AA2 |
| Gudang 1 | E1 - H1 | SLOC 7AB1 |
| Gudang 1 | E2, E3 - H2, H3 | SLOC 7AB2 |
| Gudang 1 | I1 - L1 | SLOC 7AC1 |
| Gudang 1 | I2, I3 - L2, L3 | SLOC 7AC2 |
| Gudang 2 | A1 - D1 | SLOC 7BA1 |
| Gudang 2 | A2, A3 - D2, D3 | SLOC 7BA2 |
| Gudang 2 | E1 - H1 | SLOC 7BB1 |
| Gudang 2 | E2, E3 - H2, H3 | SLOC 7BB2 |
| Gudang 2 | I1 - L1 | SLOC 7BC1 |
| Gudang 2 | I2, I3 - L2, L3 | SLOC 7BC2 |
| Gudang 3 | A1 - D1 | SLOC 7CA1 |
| Gudang 3 | A2, A3 - D2, D3 | SLOC 7CA2 |
| Gudang 3 | E1 - H1 | SLOC 7CB1 |
| Gudang 3 | E2, E3 - H2, H3 | SLOC 7CB2 |
| Gudang 3 | I1 - L1 | SLOC 7CC1 |
| Gudang 3 | I2, I3 - L2, L3 | SLOC 7CC2 |
| Gudang 4 | A1 - D1 | SLOC 7DA1 |
| Gudang 4 | A2, A3 - D2, D3 | SLOC 7DA1 ⚠️ |
| Gudang 4 | E1 - H1 | SLOC 7DB1 |
| Gudang 4 | E2, E3 - H2, H3 | SLOC 7DB2 |
| Gudang 4 | I1 - L1 | SLOC 7DC1 |
| Gudang 4 | I2, I3 - L2, L3 | SLOC 7DC2 |

### ⚠️ Anomali data yang WAJIB divalidasi/dinormalisasi oleh agent sebelum dipakai

1. Baris `Gudang 4, A2,A3-D2,D3` tertulis SLOC **`7DA1`**, padahal mengikuti pola seharusnya
   **`7DA2`** (duplikat dari baris di atasnya). Agent harus **flag / minta konfirmasi user**
   sebelum auto-fix, jangan diam-diam mengubah data sumber.
2. Ada whitespace tidak konsisten pada beberapa cell (`"Gudang 2 "` trailing space,
   `" I2, I3 - L2, L3"` leading space). Saat parsing, **selalu `.strip()`** semua string
   sebelum dipakai sebagai key/ID, dan jangan match berdasarkan string mentah.
3. Notasi `"A2, A3 - D2, D3"` berarti **rentang kolom A sampai D, baris 2 DAN 3 digabung**
   (bukan 2 range terpisah). Parser harus mengurai pola ini menjadi:
   `{kolom: [A,B,C,D], baris: [2,3]}`.

### Struktur data yang direkomendasikan (setelah normalisasi)

```json
{
  "sloc_code": "SLOC 7AA2",
  "gudang": "Gudang 1",
  "kolom_range": ["A", "B", "C", "D"],
  "baris_range": [2, 3],
  "blok_members": ["A2", "A3", "B2", "B3", "C2", "C3", "D2", "D3"]
}
```

Agent diminta membuat parser (Python/Node — sesuaikan stack project) yang membaca
`Sloc_Data.xlsx`, menormalisasi teks, mem-parse `Blok` menjadi daftar blok individual,
lalu menghasilkan struktur di atas per SLOC. Struktur inilah yang jadi **data source**
untuk render denah interaktif di web.

---

## 2. Referensi Visual — Gaya Denah Fisik yang Harus Direplikasi

Sumber: foto papan denah "Gudang / Warehouse 1 — PT SPINDO Tbk Unit 7 Gresik" (`Denah1.jpeg`).
Agent harus mereplikasi **gaya visual & struktur informasi**-nya (bukan copy identik logo/brand),
diterapkan ke komponen denah pada web app yang sedang dikembangkan, digerakkan oleh data SLOC di atas.

### 2.1 Struktur layout umum

- Denah berorientasi **landscape**, dengan **compass/mata angin** kecil di pojok kanan atas
  area denah (arah Utara–Selatan–Timur–Barat).
- Judul denah di atas: nama gudang + subtitle unit/lokasi.
- Area denah utama dibagi vertikal menjadi 3 zona berlabel di sisi kanan: **GUDANG 1 / GUDANG 2 / GUDANG 3**
  (grid rak-rak besar polos di bagian bawah = area storage utama non-blok).
- Bagian **atas** denah (row blok A–L) adalah area kerja utama yang harus di-render granular
  per Blok & di-grouping per SLOC — ini fokus utama replikasi.

### 2.2 Anatomi grid Blok (bagian yang harus jadi komponen interaktif)

- Grid kolom huruf **A → L** (12 kolom), masing-masing kolom = 1 blok fisik selebar sama.
- Setiap kolom punya **2 baris visual**:
  - Baris atas = Blok baris "1" (misal `A1`)
  - Baris bawah = Blok baris "2/3" digambar sebagai satu sel gabungan (visual merge) yang mewakili `A2+A3`
- Blok-blok dikelompokkan jadi **3 klaster per sisi** (kelompok kolom A–D, E–H, I–L),
  masing-masing klaster diberi **outline warna berbeda** untuk menandai batas grup SLOC:
  - Outline **merah** membungkus grup 4-kolom (SLOC group) secara keseluruhan (atas+bawah)
  - Outline **biru** membungkus keseluruhan klaster yang lebih besar (gabungan 3 grup SLOC dalam 1 sisi gudang)
- Setiap grup SLOC (4 kolom x baris) diberi label kecil di pojok, contoh: `LOC 7AA1`, `LOC 7AA2`
  → tampilkan sebagai **tooltip/label overlay** saat hover atau selalu-tampil kecil di sudut grup.
- Di tengah denah ada **jalur akses/gang** (digambar kuning) yang memisahkan 2 sisi klaster blok
  kiri dan kanan — ini merepresentasikan jalur forklift/manusia, render sebagai lorong kosong
  antar grup blok, jangan diisi blok.

### 2.3 Palet warna & elemen legenda (Legend)

Replikasikan sebagai **legend panel** di bagian bawah komponen denah, dengan swatch bentuk
sesuai kategori (kotak untuk area, lingkaran untuk tempat sampah):

| Elemen | Bentuk swatch | Warna (approx) | Keterangan |
|---|---|---|---|
| Tempat Tangga Muat | kotak garis-garis kuning | kuning `#F2C230`-ish, hatch pattern | area tangga muat |
| Jalur Bongkar Muat | kotak oranye | oranye `#F4A261`-ish | jalur forklift bongkar muat |
| Tempat Caddy | ikon "I-beam" kuning | kuning solid | lokasi caddy |
| Lokasi Penyimpanan Pipa/Plat | kotak oranye-merah | `#E76F51`-ish | area simpan pipa/plat |
| Tempat Sampah B3 | lingkaran merah tua | merah maroon `#8B1E1E`-ish | limbah B3 (berbahaya beracun) |
| Tempat Sampah Plastik/Kertas | lingkaran oranye | oranye `#F4A100`-ish | sampah anorganik |
| Tempat Sampah Tumbuhan/Organik | lingkaran hijau | hijau `#2A7A4B`-ish | sampah organik |
| Tempat Sampah Logam | lingkaran biru | biru `#2C6FBB`-ish | sampah logam |
| Tempat Penyimpanan Thinner/Varnish | kotak magenta/pink | `#C2185B`-ish | B3 cair |

- Panel legend diletakkan **di bawah kiri**, bentuk kolom sejajar (2–3 kolom), tiap item =
  swatch kecil + label teks singkat.

### 2.4 Header/branding area

- Logo perusahaan kiri atas, judul tengah besar (`Denah Gudang / Warehouse N`), subtitle
  (`PT ... Unit N ...`), dan badge K3/safety kanan atas (ikon plus/kesehatan).
- Panel kanan terpisah: **"Informasi Stok"** — tabel kecil berwarna (per-blok stock info),
  cukup direplikasi sebagai **panel sidebar collapsible** berisi tabel stok per SLOC/Blok
  yang datanya nanti bisa ditarik dari sumber data lain (bukan dari Sloc_Data.xlsx ini).

### 2.5 Interaktivitas yang disarankan untuk versi web (bukan di foto asli, tapi natural extension)

- Hover / klik satu Blok → highlight seluruh anggota SLOC yang sama (karena 1 SLOC = beberapa Blok).
- Tooltip menampilkan: kode SLOC, daftar Blok anggota, Gudang, dan (jika tersedia) status stok.
- Warna outline grup SLOC bisa dibuat dinamis per status (kosong/terisi/maintenance) selama
  tetap mempertahankan struktur grouping visual dari denah asli (outline per grup 4-kolom,
  outline lebih besar per klaster 3-grup).

---

## 3. Instruksi Kerja untuk Agent (Opencode / GPT-5.6 Luna)

1. **Baca & normalisasi** `Sloc_Data.xlsx` sesuai aturan di Bagian 1. Jangan hardcode hasil
   parse — tulis parser yang generik supaya kalau Gudang 5+ ditambahkan nanti, tetap jalan.
2. **Flag** anomali `Gudang 4 / SLOC 7DA1 duplikat` ke user, jangan auto-correct diam-diam.
3. **Bangun data model** (lihat contoh JSON di 1.) sebagai single source of truth untuk render.
4. **Render komponen denah** (grid blok interaktif) mengikuti struktur visual di Bagian 2:
   grid 12 kolom x 2 baris-visual per Gudang, grouping outline per SLOC, legend panel,
   compass, dan jalur tengah kosong.
5. Gunakan **komponen reusable** per Gudang (karena polanya identik untuk Gudang 1–4), cukup
   parameterized by `gudang_id` dan data SLOC hasil parsing.
6. Style warna ikuti palet di 2.3 sebagai default theme token (CSS variables / design tokens),
   supaya mudah disesuaikan tanpa hardcode hex di banyak tempat.
7. Sidebar "Informasi Stok" dibuat sebagai section terpisah yang **read data dari sumber lain**
   (belum ada di Excel ini) — buat placeholder/interface-nya dulu, jangan asumsikan skema data stok.

---

## 4. Yang TIDAK perlu direplikasi persis

- Logo & elemen branding fisik perusahaan (ganti dengan branding aplikasi/klien).
- Kondisi fisik papan (pantulan cahaya, coretan tangan, noda) — ini foto papan fisik, bukan
  bagian dari desain sistem.
- Tabel "Informasi Stok" versi cetak di kanan (format print date, dsb) — cukup ambil konsepnya
  (tabel identitas pipa per blok), bukan layout print-nya.
