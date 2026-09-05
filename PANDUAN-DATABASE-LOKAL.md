# 📊 SEO Dashboard Golden Studio — Database Lokal + Desain Profesional

## 🎯 Yang Baru
1. **Desain profesional** — layout sidebar (Overview, Kueri, Halaman, Negara & Perangkat, Pengaturan), KPI cards, grafik Chart.js, tabel interaktif.
2. **Database lokal MySQL** — semua data GSC tersimpan di database `seo_dashboard` (bukan cuma di file HTML).
3. **API PHP server-side** — login & data sekarang lewat server, aman (session cookie + CSRF token), bukan localStorage.
4. **Ganti password** — langsung dari UI (menu Pengaturan).

---

## 🗄 Struktur Database (MySQL)

Database: **`seo_dashboard`** (di Laragon MySQL, user `root` tanpa password)

| Tabel | Isi |
|---|---|
| `queries` | 349 kueri (kueri, klik, tayangan, CTR, posisi) |
| `pages` | 71 halaman |
| `countries` | 47 negara |
| `devices` | 3 perangkat |
| `daily` | 28 hari tren harian |
| `meta` | info periode ekspor |
| `users` | 1 user admin |

### Cara akses langsung (via terminal Laragon):
```bash
# Laragon → Terminal (MySQL)
mysql -u root seo_dashboard
# lalu:
SELECT * FROM queries ORDER BY clk DESC LIMIT 10;
SELECT COUNT(*) FROM queries;
```

---

## 🔐 Login

- Username: `admin@goldenstudio.id`
- Password: `golden123` ← **GANTI setelah install** (menu Pengaturan → Ganti Password)
- Auth **server-side**: session PHP + cookie HttpOnly + CSRF token
- Password disimpan sebagai **bcrypt hash** di tabel `users` (bukan plaintext)
- Salah password 5x → terkunci 5 menit

### Reset password (jika lupa):
```bash
mysql -u root seo_dashboard
# buat hash bcrypt baru (pakai PHP):
php -r "echo password_hash('password-baru', PASSWORD_DEFAULT);"
UPDATE users SET password_hash='<hasil-hash>' WHERE username='admin@goldenstudio.id';
```

---

## 📁 Struktur File (di `C:\laragon\www\seo-dashboard\`)

```
seo-dashboard/
├── index.html        ← dashboard profesional (frontend)
└── api/
    ├── config.php         ← koneksi DB + helper (session, JSON, rate limit)
    ├── login.php          ← login/logout (session + CSRF + lockout)
    ├── data.php           ← endpoint data (summary, daily, queries, pages, ...)
    ├── import.php         ← upload data GSC → simpan DB
    ├── change-password.php
    └── logout.php
```

---

## 📤 Cara Update Data Bulanan

1. Buka **Google Search Console** → **Performance** → atur periode → **Ekspor → Excel** (.xlsx)
2. Buka dashboard `http://seo-dashboard.test/` → login
3. Menu **Pengaturan** → **Impor Data GSC** → tarik file .xlsx ke kotak (atau klik)
4. Data otomatis masuk ke database `seo_dashboard`, semua grafik/tabel ter-update

---

## 🌐 Versi Cloudflare (statis, read-only)

Versi yang di-push ke GitHub & Cloudflare Pages **tetap berfungsi tapi mode offline**:
- Cloudflare Pages hanya serve file statis → **tidak ada PHP/MySQL**
- Dashboard otomatis **mendeteksi** tidak ada API → langsung tampil dengan data snapshot (tanpa login)
- Dikiri sidebar tertulis user **read-only**

**Artinya**: 
- `http://seo-dashboard.test/` (Laragon) = **full**: login server + database lokal + import
- `https://dasaahboard-seo-golden.pages.dev/` (Cloudflare) = **read-only**: lihat data snapshot terakhir

Kalau mau Cloudflare full (login + DB), butuh backend lain (misal Cloudflare Workers + D1 / KV) — bilang saja kalau mau dibuatkan.

---

## ▶️ Cara Menjalankan

1. Buka **Laragon** → **Start All** (Apache + MySQL)
2. Buka `http://seo-dashboard.test/`
3. Login → lihat dashboard

### Jika SQL dump dibutuhkan (backup database):
```bash
mysqldump -u root seo_dashboard > backup-seo-dashboard.sql
```

---

## ⚙️ Detail Teknis

- **Frontend**: HTML/CSS/JS murni, Chart.js (CDN), JSZip (CDN) untuk parse xlsx offline
- **Backend**: PHP 8.3 + MySQL 8.0 (via Laragon)
- **Fallback**: file `index.html` membawa snapshot data (base64) → tetap tampil walau API mati / di CF
- **Keamanan**: session HttpOnly, CSRF token, bcrypt, lockout 5x, prepared statements (anti SQL injection)