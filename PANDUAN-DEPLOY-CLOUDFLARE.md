# 🚀 Panduan Deploy Dashboard SEO ke Cloudflare Pages (via GitHub)

Dibuat: 5 Sep 2026 · Repo: `github.com/prject-csduosejoli/dasaahboard-seo-golden`

Dashboard SEO Anda sudah di GitHub (repo `dasaahboard-seo-golden`). Sekarang tinggal sambungkan ke **Cloudflare Pages** — gratis, otomatis ter-deploy tiap kali push, dan dapat domain `*.pages.dev`.

---

## ⚡ Cara TERCEPAT (rekomendasi) — Cloudflare Pages via GitHub

### Langkah 1 — Login ke Cloudflare
1. Buka **https://dash.cloudflare.com** di browser (login akun Cloudflare Anda).
   - Belum punya akun? Daftar gratis di https://dash.cloudflare.com/sign-up

### Langkah 2 — Masuk ke Cloudflare Pages
2. Klik menu **Workers & Pages** di sidebar kiri.
3. Klik **Create** (tombol biru, kanan atas) → pilih tab **Pages** → **Connect to Git**.

### Langkah 3 — Hubungkan GitHub
4. Klik **Connect to GitHub** (jika belum terhubung, ikuti otorisasi GitHub → Allow/Authorize).
5. Pilih repo: **`prject-csduosejoli/dasaahboard-seo-golden`**
6. Klik **Begin setup**.

### Langkah 4 — Seting Build (ini PENTING)
| Field | Isi |
|---|---|
| **Project name** | `dashboard-seo-golden` (bebas, lowercase) |
| **Production branch** | `main` |
| **Framework preset** | **None** (statis) |
| **Build command** | *(kosongkan)* |
| **Build output directory** | **`/`** (root — file HTML langsung di sana) |

> Karena ini file HTML statis, tidak perlu build. Output directory = root.

7. Klik **Save and Deploy**.

### Langkah 5 — Selesai! 🎉
8. Tunggu ±1 menit → status **Success**.
9. URL situs Anda: **`https://dashboard-seo-golden.pages.dev`**
   (bisa dilihat di halaman project → tab **Deployments**)

---

## 🔄 Update bulan depan (cukup sekali ini saja)

Setiap kali file dashboard di-update:
```bash
cd "D:\DATA SUPPORT\seo\dashboard"
git add dashboard-seo-golden.html
git commit -m "update dashboard"
git push origin main
```
Cloudflare Pages **otomatis** build & deploy versi baru — tanpa buka dashboard Cloudflare lagi.

---

## 🌐 (Opsional) Pakai Domain Sendiri

Kalau mau `dashboard.goldenstudio.id` atau subdomain lain:
1. Di project Pages → tab **Custom domains** → **Set up a custom domain**.
2. Masukkan subdomain → ikuti verifikasi (tambah CNAME di DNS Cloudflare).
3. Selesai — akses via domain Anda.

---

## 🛠️ Alternatif: Deploy via Wrangler CLI (tanpa buka dashboard web)

Kalau prefer pakai terminal (butuh **token API Cloudflare**):

```bash
# 1) Login sekali
npx wrangler login

# 2) Dari folder dashboard
cd "/d/DATA SUPPORT/seo/dashboard"
npx wrangler pages deploy . --project-name dashboard-seo-golden

# (opsional) kalau belum ada project, buat dulu:
npx wrangler pages project create dashboard-seo-golden --production-branch main
```

> Catatan: `wrangler login` membuka browser untuk otorisasi. Setelah login sekali, token tersimpan di `~/.wrangler`.

---

## ❓ Troubleshooting

| Masalah | Solusi |
|---|---|
| **Build failed / 404** | Pastikan "Build output directory" = `/` dan **Build command kosong** |
| **File tidak muncul** | Cek nama file: harus `dashboard-seo-golden.html` (bukan `index.html`) — akses via `https://<project>.pages.dev/dashboard-seo-golden.html`. Kalau mau root `/` langsung tampil, rename file jadi `index.html` lalu push lagi |
| **Login GitHub gagal** | Di dashboard Cloudflare → Workers & Pages → masih "Connect to GitHub" → klik tombol hijau "Connect GitHub" lagi |
| **Domain tidak aktif** | Pastikan DNS Cloudflare **orange cloud ON** (proxy) |
| **Push ditolak** | `git pull origin main --rebase` dulu, lalu push lagi |

---

## 📌 Ringkasan

- **Repo:** `github.com/prject-csduosejoli/dasaahboard-seo-golden.git`
- **Deploy:** Cloudflare Pages (connect Git, output root, no build)
- **URL hasil:** `https://dashboard-seo-golden.pages.dev`
- **Update:** cukup `git push` — sisanya otomatis

Selamat, dashboard SEO Golden Studio Anda akan tayang online! 🚀