# 🚀 Panduan Deploy SEO Dashboard — Lokal (Laragon) & Cloudflare

## Ringkasan
Dashboard SEO Golden Studio kini punya **2 mode**:

| Mode | URL | Kemampuan |
|---|---|---|
| **Lokal (Laragon)** — full | `http://seo-dashboard.test/` | Login server + database MySQL + import xlsx + ganti password |
| **Cloudflare Pages** — statis | `https://dasaahboard-seo-golden.pages.dev/` | Read-only (snapshot data), tanpa login |

---

## A. Deploy Lokal (Laragon) — PRIMARY

### Prasyarat
- Laragon terinstall, Apache + MySQL **started**
- Ekstensi PHP: `pdo_mysql` aktif (default Laragon)

### Langkah
1. Copy folder `seo-dashboard` ke `C:\laragon\www\`
   ```
   C:\laragon\www\seo-dashboard\
   ├── index.html
   └── api\*.php
   ```
2. Pastikan **MySQL** aktif (Laragon → Start All)
3. Buka `http://seo-dashboard.test/` (hosts sudah diisi `127.0.0.1 seo-dashboard.test`)
   - Atau `http://localhost/seo-dashboard/`

### Database
Sudah otomatis ada: `seo_dashboard` (user `root`, tanpa password).
Jika di komputer lain: `mysql -u root < schema.sql` (schema.sql ada di repo).

---

## B. Deploy ke Cloudflare Pages — READ-ONLY

### Prasyarat
- Repo GitHub: `prject-csduosejoli/dasaahboard-seo-golden`
- Project CF Pages: `dasaahboard-seo-golden` (Connect to Git)

### Langkah
1. Push ke GitHub → auto-deploy ke CF Pages
2. `index.html` di root → tampil di `https://dasaahboard-seo-golden.pages.dev/`
3. Versi CF **otomatis mode offline** (deteksi tidak ada `/api/`) → tampil read-only dengan snapshot data

> ⚠️ File `api/*.php` ikut ke repo tapi **tidak dieksekusi** di CF Pages (statis-only). Tidak masalah — JS fallback ke mode offline.

### Kalau mau CF full-stack (login + DB): butuh Cloudflare Workers + D1/KV + R2 — hubungi developer.

---

## C. Update Data Bulanan

1. GSC → Performance → periode → **Ekspor → Excel**
2. Buka `http://seo-dashboard.test/` → login
3. **Pengaturan → Impor Data GSC** → tarik file
4. Otomatis masuk DB, dashboard ter-update
5. (Opsional) export snapshot baru ke index.html agar CF ikut update:
   - Jalankan script konversi (lihat `README` / minta developer)

---

## D. Troubleshooting

| Gejala | Solusi |
|---|---|
| `http://seo-dashboard.test/` tidak terbuka | Laragon → Start All; cek Apache running |
| Login "Koneksi gagal" | Pastikan MySQL aktif |
| Data lama (tidak update setelah impor) | Refresh halaman (Ctrl+F5) |
| Lupa password | `mysql -u root seo_dashboard` → update `users` (lihat PANDUAN-DATABASE-LOKAL.md) |
| CF Pages tampil Hello World | Pastikan file bernama `index.html` di root repo |