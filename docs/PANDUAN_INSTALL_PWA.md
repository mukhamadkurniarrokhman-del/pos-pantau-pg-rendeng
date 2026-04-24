# Panduan Install PWA — POS Pantau PG Rendeng

Aplikasi petugas sekarang sudah jadi **PWA (Progressive Web App)** — artinya bisa di-install ke HP persis seperti aplikasi biasa, tapi tanpa Play Store, tanpa bayar developer account, tanpa APK.

---

## Langkah 1 — Jalankan Backend (server lokal / VPS)

Dari folder proyek:

```bash
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

Catatan penting:
- Kalau Anda deploy ke VPS produksi, pastikan **HTTPS aktif** (PWA wajib HTTPS, kecuali localhost).
- Untuk tes lokal pakai IP HP-laptop satu wifi, `php artisan serve --host=0.0.0.0` sudah cukup.
- Di produksi: pakai Nginx/Apache + Let's Encrypt untuk SSL gratis.

---

## Langkah 2 — Cek URL dari HP Petugas

Buka browser **Chrome** di HP petugas (Android) atau **Safari** (iPhone):

- Lokal (satu wifi):  `http://<IP-laptop>:8000/Petugas_App.html`
  Contoh: `http://192.168.1.10:8000/Petugas_App.html`
- Produksi:  `https://pos-pantau.pgrendeng.id/Petugas_App.html`

Aplikasi akan terbuka. Login dengan akun petugas yang sudah di-seed.

---

## Langkah 3 — Install ke Home Screen

### Android (Chrome)
1. Setelah login, muncul banner **"Install Aplikasi"** di bawah layar. Tap **Install**.
2. Kalau banner tidak muncul:
   - Tap tombol titik tiga di kanan atas Chrome
   - Pilih **"Install app"** atau **"Add to Home Screen"**
   - Konfirmasi
3. Icon **POS Pantau** muncul di home screen HP.

### iPhone (Safari)
1. Tap tombol **Share** (kotak dengan panah atas) di bawah.
2. Scroll ke bawah, pilih **"Add to Home Screen"**.
3. Tap **Add** di pojok kanan atas.
4. Icon **POS Pantau** muncul di home screen.

---

## Langkah 4 — Verifikasi Install Berhasil

Tutup Chrome/Safari sepenuhnya, lalu buka icon **POS Pantau** dari home screen.

Yang harus terjadi:
- Aplikasi buka **full screen** (tanpa bar browser)
- Splash screen warna hijau muncul sebentar
- Header atas warna hijau brand, logo putih
- Token login tetap tersimpan (tidak perlu login ulang)

Kalau masih muncul URL bar Chrome → artinya belum ter-install dengan benar. Ulangi Langkah 3.

---

## Langkah 5 — Tes Mode Offline

1. Matikan wifi & data seluler HP.
2. Buka aplikasi dari home screen.
3. Yang harus muncul:
   - UI aplikasi tetap tampil (shell di-cache oleh Service Worker)
   - Kalau coba kirim SPA → muncul halaman "Tidak Ada Koneksi" yang elegan
4. Nyalakan internet lagi → aplikasi otomatis kembali normal.

---

## Keuntungan Pakai PWA vs APK

| Hal | APK (Play Store) | PWA |
|-----|------------------|-----|
| Biaya developer | Rp 380rb (Google Play) | Rp 0 |
| Update | Minggu-an approval | Instan, refresh page |
| Hosting | Harus upload ke Play | Cukup di server Anda |
| Install | Dari Store | Add to Home Screen |
| Storage HP | Butuh APK ~10-20 MB | ~2 MB |
| Maintenance | Ribet | Edit HTML → push → selesai |

---

## Penting — Keamanan untuk HP Milik Petugas

Karena HP adalah **milik perusahaan** (bukan HP pribadi petugas), Anda perlu hardening tambahan:

### Wajib di sisi HP
1. **Aktifkan Device Owner mode** (opsional, lewat adb):
   - Blokir akses ke Opsi Pengembang
   - Kunci Wi-Fi & APN
   - Tidak bisa install aplikasi dari luar
2. **Pasang Google Play Protect** — auto-detect fake GPS app.
3. **Kiosk Mode** (optional): pakai aplikasi seperti **SureLock** gratis, paksa HP cuma bisa buka POS Pantau + WA.

### Sudah otomatis di aplikasi
- GPS dibandingkan dengan koordinat pos resmi → kalau jauh, SPA **otomatis ditolak**.
- `is_mock_location` flag dari Android → deteksi fake GPS.
- Foto dikirim ke server dengan timestamp & lokasi → audit trail lengkap.
- Semua SPA dicatat di admin dashboard dengan jarak real.

### Layer murah tambahan
- **Foto wajib selfie sopir + truk** dari kamera belakang (sudah ada di form input).
- **Cross-check manual** — admin reviewer spot-check 10% SPA/hari, lihat apakah foto konsisten.
- **Jadwal rotasi petugas** — jangan 1 orang jaga 1 pos permanen (mengurangi kolusi).

---

## Checklist Deploy ke Produksi

- [ ] Beli domain (contoh: pos-pantau.pgrendeng.id) — sekitar Rp 150rb/tahun
- [ ] Sewa VPS murah (Niagahoster/Biznet Gio KVM1) — Rp 50-100rb/bulan cukup
- [ ] Install Nginx + PHP 8.3 + MySQL 8 + Let's Encrypt (SSL gratis)
- [ ] Deploy Laravel: `git pull` + `composer install --no-dev` + `php artisan migrate --force` + `php artisan db:seed`
- [ ] Update `APP_URL`, `DB_*`, `FONNTE_TOKEN` di `.env`
- [ ] Test buka `https://domain/Petugas_App.html` di HP → Install → tes input SPA real
- [ ] Monitor Admin Dashboard dari laptop kantor

---

## Troubleshooting

**Banner "Install" tidak muncul**
- Chrome Android butuh: HTTPS + manifest valid + service worker registered + icon 192/512.
- Cek console browser → cari pesan error Service Worker.
- Buka `chrome://inspect` di laptop, sambungkan HP via USB, debug dari sana.

**Icon di home screen pakai logo browser, bukan logo POS Pantau**
- Cek `/icons/icon-192.png` dan `/icons/icon-512.png` bisa dibuka langsung di browser.
- Bersihkan cache Chrome, uninstall dari home screen, install ulang.

**Service Worker tidak update setelah deploy versi baru**
- Naikkan `CACHE_VERSION` di `backend/public/sw.js` (misal `pg-rendeng-v2`).
- Petugas cukup tutup-buka aplikasi → SW auto-update.
- Untuk paksa: Chrome Android → Settings → Site Settings → cari domain → Clear & Reset.

**"Offline" terus padahal wifi nyala**
- Cek `navigator.onLine` di console. Kadang Chrome cache status offline.
- Force reload: pull-to-refresh di Chrome, atau restart aplikasi dari home screen.

---

Siap dipakai! Total biaya: domain Rp 150rb/thn + VPS Rp 100rb/bln + Fonnte Rp 150rb/bln untuk 1000 WA.
