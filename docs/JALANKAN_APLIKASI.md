# Cara Jalankan Aplikasi POS Pantau PG Rendeng

Panduan step-by-step untuk running pertama kali di Windows (laptop kantor).

---

## Prasyarat (install sekali aja)

Anda butuh 3 hal di laptop Windows:

1. **PHP 8.2 atau lebih baru** — paling gampang pakai [Laragon](https://laragon.org) (gratis, sekali install sudah dapat PHP + MySQL + Apache lengkap).
2. **Composer** — paket manajer PHP. Download dari https://getcomposer.org/Composer-Setup.exe, jalankan, klik Next-Next-Finish.
3. **MySQL** — sudah otomatis ada kalau pakai Laragon. Kalau pakai XAMPP, nyalakan MySQL dari Control Panel.

Cek semuanya sudah terpasang. Buka **Command Prompt** dan ketik:

```
php --version
composer --version
mysql --version
```

Ketiganya harus kasih output versi, bukan error "command not found". Kalau error → PATH belum ter-set, restart laptop setelah install.

---

## Setup sekali (first-time)

### Langkah 1 — Buat database MySQL

Double-click file **`buat-database.bat`** di folder proyek.

Kalau berhasil, muncul "BERHASIL! Database pos_pantau_pgrendeng siap." Kalau gagal (misal MySQL tidak nyala), buka Laragon → klik **Start All**, lalu ulangi.

Alternatif manual: buka **HeidiSQL** (bawaan Laragon), klik New → kasih nama `pos_pantau_pgrendeng`, collation `utf8mb4_unicode_ci`, klik OK.

### Langkah 2 — Install dependencies + seed data

Double-click **`setup-backend.bat`**. Script ini otomatis:
- Install semua library PHP via Composer (~2 menit pertama kali)
- Copy `.env` dari template
- Generate APP_KEY
- Jalankan migrate + seed (bikin tabel + data dummy 6 pos + akun default)
- Buat symlink `storage` untuk foto upload
- Langsung menjalankan server di `http://localhost:8000`

Kalau muncul error di step composer install → cek koneksi internet. Kalau error di migrate → pastikan database sudah dibuat di langkah 1.

### Langkah 3 — Tes aplikasi

Setelah setup-backend.bat jalan, window Command Prompt-nya akan tetap terbuka dengan tulisan:

```
Server running on [http://0.0.0.0:8000]
```

Buka browser, kunjungi:

**Petugas App** → http://localhost:8000/Petugas_App.html

Login dengan salah satu akun:
- `admin@pgrendeng.co.id` / `admin123` (admin)
- Atau via NIP: `PTG-JPR-01` / `petugas123` (petugas Jepara)
- Atau NIP: `PTG-PTI-01`, `PTG-RBG-01`, `PTG-JPH-01`, `PTG-TDN-01`, `PTG-GBG-01` dengan password `petugas123`

**Admin Dashboard** → http://localhost:8000/Admin_Dashboard.html

Login: `admin@pgrendeng.co.id` / `admin123`

---

## Hari-hari berikutnya

Tidak perlu setup lagi. Tinggal:

1. Nyalakan MySQL (via Laragon / XAMPP)
2. Double-click **`serve-backend.bat`**
3. Buka browser ke `http://localhost:8000/Petugas_App.html`

Kalau mau tutup: klik window CMD server-nya, tekan `Ctrl+C`, lalu `Y` + Enter.

---

## Akses dari HP untuk testing

Supaya HP petugas bisa buka aplikasi (satu WiFi dengan laptop):

1. Cari IP laptop. Buka CMD, ketik `ipconfig`, cari baris **IPv4 Address** di bagian WiFi (contoh `192.168.1.10`).
2. Pastikan firewall Windows tidak blok port 8000. Kalau muncul dialog saat pertama kali `php artisan serve`, pilih **Allow**.
3. Dari HP (satu wifi), buka Chrome → `http://192.168.1.10:8000/Petugas_App.html`.
4. Login → tap banner **Install** → aplikasi masuk ke home screen HP.

Note: untuk PWA di HP, Chrome Android biasanya mengizinkan install dari HTTP **hanya kalau IP lokal** (bukan HTTPS wajib). Kalau banner tidak muncul, pakai menu titik-tiga Chrome → **Add to Home Screen** manual.

---

## Untuk deploy produksi (nanti)

Setelah tes lokal sukses dan siap dipakai di lapangan:

1. Sewa VPS (rekomendasi: Biznet Gio, Niagahoster, DigitalOcean) ~Rp 80-150rb/bulan.
2. Domain (opsional) ~Rp 150rb/tahun.
3. Install Nginx + PHP 8.3 + MySQL + Let's Encrypt.
4. Upload proyek via Git atau FTP.
5. `cp .env.example .env` → isi `APP_URL`, `DB_*` production, `FONNTE_TOKEN`.
6. `composer install --no-dev --optimize-autoloader`
7. `php artisan migrate --force && php artisan db:seed --force`
8. `php artisan storage:link`
9. `php artisan config:cache && php artisan route:cache`
10. Setting Nginx untuk point ke folder `backend/public/`.

Nanti kalau sudah sampai tahap ini, saya bisa bantu bikin file konfigurasi Nginx + systemd service + script deploy.

---

## Kalau ada error

### "Connection refused" saat login
- Pastikan MySQL nyala. Cek di tray Windows apakah Laragon hijau.
- Cek `.env` → `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_USERNAME=root`, `DB_PASSWORD=` (kosong kalau default Laragon).

### "Cek kontrak tidak ditemukan" padahal seeder jalan
- Buka HeidiSQL → database `pos_pantau_pgrendeng` → tabel `kontrak` → cek apakah ada baris.
- Kalau kosong: `cd backend && php artisan db:seed --force`.

### "GPS tidak valid" padahal posisi benar
- Koordinat pos di seeder kemungkinan belum persis. Update manual di HeidiSQL tabel `pos_pantau` → kolom `latitude`, `longitude` → isi koordinat real dari Google Maps (klik kanan titik → Copy coordinates).
- Atau naikkan `radius_meter` jadi 500 sementara testing.

### Foto upload kasih error 500
- Cek folder `backend/storage/app/public/foto_muatan` — kalau belum ada, buat manual.
- Cek `backend/public/storage` harus symlink. Kalau tidak, `cd backend && php artisan storage:link`.

### Banner "Install PWA" tidak muncul
- Hanya muncul di HTTPS atau localhost. Untuk tes lokal dari HP di LAN, pakai menu titik-tiga Chrome → "Add to Home Screen" manual.
- Production wajib HTTPS supaya banner muncul otomatis.

---

Kalau ada error lain yang tidak ada di sini, copy pesan error lengkap dari CMD, kirim ke saya di sesi berikutnya dan saya debug.
