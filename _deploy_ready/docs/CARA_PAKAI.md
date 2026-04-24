# Cara Pakai — Sistem POS Pantau PG Rendeng

Panduan cepat untuk Pak Rahman & tim PG Rendeng. Setelah ikuti langkah di dokumen ini, sistem **langsung bisa dipakai test di lapangan**.

---

## Apa Isi Project Ini

```
SISTEM POS PANTAU PG RENDENG/
├── backend/                    ← server Laravel + MySQL + Fonnte
├── Petugas_App.html            ← aplikasi mobile untuk petugas pos (buka di HP)
├── Admin_Dashboard.html        ← dashboard admin untuk manajemen PG
├── Frontend_POS_Pantau.html    ← mockup desain awal (referensi)
├── setup-backend.bat           ← script install satu-klik (Windows)
├── serve-backend.bat           ← jalankan server (setelah install)
└── CARA_PAKAI.md               ← file ini
```

---

## Sebelum Mulai — Yang Harus Terinstall di PC/Laptop

1. **PHP 8.2+** dan **Composer** — paling praktis install **Laragon** ([laragon.org](https://laragon.org)). Laragon sudah include PHP, MySQL, Apache, dan Composer langsung jalan.
2. **MySQL** — sudah include di Laragon. Jalankan Laragon → tombol "Start All".
3. **Browser Chrome** atau Edge terbaru.
4. **Akun Fonnte** (untuk kirim WA) — daftar di [fonnte.com](https://fonnte.com), tambah device, scan QR WhatsApp, copy **device token**.

---

## Langkah 1 — Setup Database

1. Buka browser → `http://localhost/phpmyadmin` (Laragon sudah siapkan ini)
2. Klik **New** → buat database bernama: `pos_pantau_pgrendeng` → klik Create.

Itu saja untuk langkah ini. Tabel-tabel akan dibuat otomatis oleh langkah berikut.

---

## Langkah 2 — Install Backend (Otomatis)

Double-click file **`setup-backend.bat`** di folder project.

Script akan otomatis:
- Install library Laravel (butuh internet, ~2-3 menit pertama kali)
- Setup file konfigurasi `.env`
- Generate kunci aplikasi
- Buat 8 tabel di database
- Isi data dummy (6 pos, 8 akun, 8 kontrak petani)
- Jalankan server di `http://localhost:8000`

Kalau muncul error, biasanya karena:
- MySQL belum jalan → nyalakan Laragon lebih dulu
- PHP/Composer belum ada di PATH → install Laragon / restart komputer setelah install

---

## Langkah 3 — Konfigurasi Fonnte (Opsional tapi Disarankan)

Edit file `backend/.env` dengan Notepad, cari baris:
```
FONNTE_TOKEN=
```
Isi dengan token dari dashboard Fonnte:
```
FONNTE_TOKEN=abc123def456tokenfonnteanda
```
Simpan. Tidak perlu restart — Laravel baca ulang tiap request.

Kalau belum ada token Fonnte, sistem tetap jalan, cuma notifikasi WA tidak terkirim (tercatat sebagai `failed` di log).

---

## Langkah 4 — Akses Dashboard Admin

Di komputer yang sama:
1. Double-click **`Admin_Dashboard.html`** → buka di browser.
2. Isi:
   - **Server URL**: `http://localhost:8000`
   - **NIP**: `ADM-001`
   - **Password**: `admin123`
3. Klik **Masuk**. Dashboard muncul dengan 4 KPI + 2 chart + daftar truk.

Ganti tanggal, filter search, dan auto-refresh tiap 60 detik.

---

## Langkah 5 — Akses Aplikasi Petugas (di HP)

Ada 2 cara — pilih salah satu:

### Cara A: Via WiFi Lokal (Paling Mudah)
1. Pastikan HP dan laptop satu WiFi.
2. Di laptop, cek IP: buka CMD → ketik `ipconfig` → cari `IPv4 Address` (misal `192.168.1.10`).
3. Copy file **`Petugas_App.html`** ke Google Drive / kirim via WA → buka di HP.
4. Di app: **Server URL**: `http://192.168.1.10:8000` (pakai IP laptop, bukan `localhost`).
5. Login dengan akun petugas, misal `PTG-JPR-01` / `petugas123`.

### Cara B: Deploy ke Hosting
Untuk pakai di luar jaringan kantor, server harus di-deploy ke VPS atau hosting. Panduan deploy ada di `backend/README.md` bagian Roadmap.

---

## Akun Default (Ganti Setelah Testing)

| Role         | NIP          | Password        | Pos    |
|--------------|--------------|-----------------|--------|
| Admin        | `ADM-001`    | `admin123`      | —      |
| Supervisor   | `SPV-001`    | `supervisor123` | —      |
| Petugas Jepara    | `PTG-JPR-01` | `petugas123`   | JPR |
| Petugas Pati      | `PTG-PTI-01` | `petugas123`   | PTI |
| Petugas Rembang   | `PTG-RBG-01` | `petugas123`   | RBG |
| Petugas Japah     | `PTG-JPH-01` | `petugas123`   | JPH |
| Petugas Todanan   | `PTG-TDN-01` | `petugas123`   | TDN |
| Petugas Grobogan  | `PTG-GBG-01` | `petugas123`   | GBG |

Nomor kontrak dummy yang bisa dipakai test lookup: `KTR-PGR-2026-00001` s.d. `KTR-PGR-2026-00008`.

---

## Alur Kerja Harian Petugas

1. Buka aplikasi `Petugas_App.html` di HP.
2. Login dengan NIP + password.
3. Truk datang → tekan **+ Input Truk Baru**.
4. Isi: **Nomor Polisi**, **Nama Sopir**.
5. Ketik **Nomor Kontrak** → tekan **Cek** → nama petani & kebun otomatis muncul.
6. Tekan tombol foto → ambil foto muatan dari belakang truk.
7. Tekan **Submit & Verifikasi**. HP akan minta izin lokasi GPS (izinkan).
8. Kalau GPS valid → tampil **SPA Terverifikasi** + nomor SPA (contoh `JPR-20260422-012`). Petani otomatis dapat WA.
9. Kalau GPS ditolak (diluar radius, fake GPS) → tampil **SPA Ditolak** + alasan. Petugas pindah ke area terbuka lalu submit ulang.

---

## Alur Kerja Admin

1. Buka `Admin_Dashboard.html` di laptop kantor PG Rendeng.
2. Lihat 4 KPI hari ini: total truk, verified, rejected, fake GPS.
3. Bar chart "Truk per Pos" = breakdown 6 pos.
4. Line chart "Trend 7 Hari" = tren harian.
5. Tabel daftar truk bisa di-search & filter tanggal.
6. Section Alert = daftar SPA bermasalah (ditolak / fake GPS) 3 hari terakhir — **periksa ini tiap pagi**.

---

## Troubleshooting Cepat

| Masalah | Solusi |
|---------|--------|
| Petugas app error "Failed to fetch" | Cek IP laptop berubah? Update Server URL di app. Cek firewall Windows — izinkan port 8000. |
| Login "NIP atau password salah" | Pastikan caps-lock mati. NIP huruf besar. |
| Lookup kontrak "not_found" | Pastikan nomor kontrak persis — format `KTR-PGR-2026-00001`. |
| GPS selalu ditolak | Petugas harus di luar ruangan + tunggu 10 detik buat GPS lock. Radius default 50m dari titik pos. |
| Fake GPS terdeteksi padahal tidak pakai | Matikan aplikasi fake GPS / mock location di pengaturan developer HP. |
| WA tidak terkirim | Cek `FONNTE_TOKEN` di `.env`. Lihat log di `backend/storage/logs/laravel.log`. |
| Database error saat migrate | Pastikan database `pos_pantau_pgrendeng` sudah dibuat di phpMyAdmin. |

---

## Customize Selanjutnya (Setelah Testing OK)

1. **Koordinat pos** — edit `backend/database/seeders/PosPantauSeeder.php`, isi latitude/longitude sesuai GPS fisik setiap pos, lalu jalankan: `php artisan db:seed --class=PosPantauSeeder`
2. **Data kontrak real** — ganti isi `KontrakSeeder.php` dengan data petani + kebun + kontrak real PG Rendeng, atau buat CRUD kontrak via admin.
3. **Ganti password default** — WAJIB sebelum production. Login via tinker: `php artisan tinker` → `User::where('nip', 'ADM-001')->first()->update(['password' => Hash::make('password_baru')]);`
4. **Deploy ke server** — lihat `backend/README.md` untuk panduan deploy.

---

Sampai di sini sistem sudah **siap pakai** untuk pilot test di satu pos, lalu scale ke 6 pos bersamaan. Silakan test di salah satu pos dulu, catat feedback petugas lapangan, baru roll-out ke semua pos.
