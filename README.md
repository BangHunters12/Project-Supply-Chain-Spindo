# Konfigurasi Agent untuk Antigravity & Opencode

Paket ini berisi file-file konteks & skill yang sudah disesuaikan untuk proyek Laravel + Vue + Tailwind Anda, siap dipakai di **Google Antigravity** maupun **Opencode**.

## Isi paket
```
.
├── AGENTS.md                                          # konteks proyek, dibaca kedua tool
├── .agents/
│   ├── rules/code-style.md                            # workspace rule (Antigravity)
│   └── skills/laravel-vue-tailwind-conventions/SKILL.md
└── .opencode/
    └── skills/laravel-vue-tailwind-conventions/SKILL.md   # duplikat, native path Opencode
```

## Cara pasang

### 1. Salin semuanya ke root proyek Laravel Anda
Copy folder `AGENTS.md`, `.agents/`, dan `.opencode/` ke root proyek (sejajar dengan `composer.json`, `package.json`).

### 2A. Implementasi di Antigravity
1. Install Antigravity dari **antigravity.google/download**.
2. Buka folder proyek Laravel Anda sebagai workspace.
3. Antigravity otomatis membaca `AGENTS.md` di root saat sesi dimulai, dan workspace rules di `.agents/rules/`.
4. Cek di **Settings → Agent** bahwa "nested AGENTS.md" / rules aktif (biasanya default aktif).
5. (Opsional) Kalau mau override khusus Antigravity yang lebih tinggi prioritasnya dari `AGENTS.md`, buat `GEMINI.md` di root — `GEMINI.md` selalu menang jika ada konflik aturan.
6. Skill di `.agents/skills/` akan otomatis dikenali sesuai konvensi `agents.md`/skills ekosistem yang didukung Antigravity.

### 2B. Implementasi di Opencode
1. Install: `npm install -g opencode-ai` (atau lewat Homebrew/curl sesuai OS Anda — cek `opencode.ai/docs`).
2. Jalankan `opencode` di root proyek, lalu `/connect` untuk hubungkan provider (Anthropic/OpenAI/dll).
3. Jalankan `/init` sekali agar Opencode mengindeks `AGENTS.md` Anda.
4. Skill di `.opencode/skills/laravel-vue-tailwind-conventions/SKILL.md` otomatis terdeteksi lewat native skill tool — tidak perlu dipanggil manual, agent akan memuatnya saat konteks relevan (mis. saat diminta bikin komponen Vue atau controller baru).
5. Kalau mau tambah aturan wajib eksplisit, tambahkan baris di `AGENTS.md`, contoh:
   ```
   - use laravel-vue-tailwind-conventions sebelum membuat controller atau komponen Vue baru
   - use verification-before-completion sebelum bilang tugas selesai
   ```

## Rekomendasi tambahan (opsional, unduh manual)

Kalau Anda mau skill/workflow yang lebih general-purpose (bukan spesifik proyek ini) seperti disiplin "plan → eksekusi → review" untuk task besar, ini sumber yang direkomendasikan komunitas — silakan unduh sesuai kebutuhan:

| Nama | Fungsi | Sumber |
|---|---|---|
| **Superpowers** (obra/superpowers) | Skill-set disiplin: brainstorm → plan → TDD → review sebelum agent bilang "selesai". Paling berpengaruh ke cara agent merangkum/menjalankan kerja. | `github.com/obra/superpowers` |
| **awesome-agent-skills** (VoltAgent) | Katalog 20+ skill siap pakai lintas-tool (Antigravity, Opencode, Claude Code, Cursor, dll) | `github.com/VoltAgent/awesome-agent-skills` |
| **agents.md** (standar resmi) | Referensi format `AGENTS.md` itu sendiri | `agents.md` |
| **agent-skills** (addyosmani) | Contoh skill production-grade + panduan setup khusus Opencode | `github.com/addyosmani/agent-skills` |

Cara pasang skill dari sumber di atas ke Opencode: clone/download repo tersebut, lalu copy folder skill yang relevan ke `.opencode/skills/<nama>/` atau `~/.config/opencode/skills/<nama>/` (global, berlaku semua proyek).

Untuk Antigravity: copy isi skill ke `.agents/skills/<nama>/SKILL.md` di workspace, atau `~/.gemini/` untuk global.

## Catatan
- `AGENTS.md` adalah standar lintas-tool yang juga dibaca Cursor, Claude Code, Codex, dan Copilot — jadi kalau nanti Anda ganti/tambah tool lain, file ini tetap terpakai tanpa perlu ditulis ulang.
- Kalau pakai Antigravity dan ingin aturan yang HANYA berlaku di Antigravity (override), pakai `GEMINI.md`, bukan edit `AGENTS.md` — supaya tetap portable ke tool lain.
