# Deploy ke Laravel Cloud — Step by Step

Ikuti urutan ini persis. Copy-paste perintahnya ke CMD/PowerShell di Windows.

---

## Langkah 1 — Bikin repo di GitHub

1. Buka https://github.com/new
2. Repository name: **`pos-pantau-pg-rendeng`**
3. Description (opsional): `Sistem monitoring pos pantau penerimaan tebu PG Rendeng Kudus`
4. Pilih **Public** (Laravel Cloud free tier butuh public repo, atau bayar untuk private)
5. **JANGAN centang** "Add a README", "Add .gitignore", atau "Choose a license" — kosong total
6. Klik **Create repository**
7. Copy URL yang muncul, formatnya: `https://github.com/mukhamadkurniarrokhman-del/pos-pantau-pg-rendeng.git`

---

## Langkah 2 — Cek Git di laptop

Buka **Command Prompt** (ketik `cmd` di Start Menu), jalankan:

```
git --version
```

Kalau muncul `git version 2.xx.x` → lanjut Langkah 3.
Kalau error → install dulu dari https://git-scm.com/download/win (klik Next-Next-Finish, setelah install restart CMD).

Setting nama & email Git (sekali seumur hidup):

```
git config --global user.name "Mukhamad Kurniarrokhman"
git config --global user.email "mukhamadkurniarrokhman@gmail.com"
```

---

## Langkah 3 — Restructure folder (pindahkan backend/ ke root)

Laravel Cloud expect `composer.json` ada di root repo, bukan di subfolder. Kita perlu pindahkan isi `backend/` ke root proyek.

Buka CMD, masuk ke folder proyek:

```
cd "C:\Users\ASUS\Documents\Claude\Projects\SISTEM POS PANTAU PG RENDENG"
```

Jalankan perintah berikut **SATU PER SATU**, enter setelah setiap baris:

```
robocopy backend . /E /MOVE /NFL /NDL /NJH /NJS /NP
```

Ini memindahkan semua isi `backend/` ke folder root. Setelah selesai, folder `backend/` akan kosong dan bisa dihapus:

```
rmdir backend
```

Bikin folder `docs/` untuk file panduan lama + mockup:

```
mkdir docs
move CARA_PAKAI.md docs\
move JALANKAN_APLIKASI.md docs\
move PANDUAN_INSTALL_PWA.md docs\
move Frontend_POS_Pantau.html docs\
move Sistem_Penerimaan_Tebu_PG_Rendeng.html docs\
```

Hapus duplikat HTML yang di root (sudah ada di `public/`):

```
del Petugas_App.html
del Admin_Dashboard.html
```

Pindahkan BAT script ke docs (tidak dipakai di produksi):

```
move buat-database.bat docs\
move serve-backend.bat docs\
move setup-backend.bat docs\
move DEPLOY_LARAVEL_CLOUD.md docs\
```

Verifikasi struktur sudah benar:

```
dir
```

Yang harus muncul di root: `app`, `artisan`, `bootstrap`, `composer.json`, `composer.lock`, `config`, `database`, `docs`, `public`, `routes`, `storage`, `vendor`, `.env`, `.env.example`, `.gitignore`, `README.md`

---

## Langkah 4 — Init Git & push ke GitHub

Masih di CMD, folder proyek yang sudah di-restructure:

```
git init
git branch -M main
git add .
git commit -m "Initial commit: POS Pantau PG Rendeng - Laravel 11 + PWA"
```

Kalau commit sukses, tambahkan remote (ganti URL kalau beda dengan yang Anda copy di Langkah 1):

```
git remote add origin https://github.com/mukhamadkurniarrokhman-del/pos-pantau-pg-rendeng.git
git push -u origin main
```

GitHub akan minta login. Muncul popup "Sign in with your browser" → klik, otorisasi di browser, balik ke CMD. Tunggu sampai selesai (bisa 1-2 menit karena ada composer.lock 326 KB dan beberapa file besar — tapi `vendor/` sudah di-exclude via .gitignore jadi relatif cepat).

Setelah sukses, refresh halaman GitHub repo Anda di browser. File-file harus sudah muncul.

---

## Langkah 5 — Kembali ke Laravel Cloud, klik Connect to GitHub

Setelah push berhasil, beritahu saya **"sudah di-push"** dan saya akan:

1. Navigate Chrome ke halaman create application
2. Klik **Connect to GitHub** (minta izin Anda dulu)
3. Pilih repo `pos-pantau-pg-rendeng`
4. Setting Environment Variables (APP_KEY, DB_*, FONNTE_TOKEN, dll)
5. Provision database PostgreSQL (1x klik, gratis dari $5 credit)
6. Klik Deploy
7. Tunggu ~3 menit build selesai
8. Dapat URL production (misal `https://pos-pantau-abc123.laravel.cloud`)

---

## Kalau ada error di Langkah 3 atau 4

### Error: "robocopy is not recognized"
Anda pakai Windows terlalu lama tanpa update. Pakai alternatif:
```
xcopy backend\* .\ /E /H /Y
rmdir /S /Q backend
```

### Error: "nothing to commit"
Folder .git terdeteksi di parent directory. Cek:
```
git status
```
Kalau aneh, hapus folder `.git` yang tidak di folder proyek.

### Error push: "remote origin already exists"
Jalankan:
```
git remote remove origin
git remote add origin https://github.com/mukhamadkurniarrokhman-del/pos-pantau-pg-rendeng.git
```

### Error push: "failed to push some refs"
Ada commit di remote yang belum di-fetch. Normalnya tidak terjadi di repo baru. Kalau terjadi:
```
git pull origin main --allow-unrelated-histories
git push -u origin main
```

### File terlalu banyak / push lambat
Cek apakah `vendor/` ikut terpush (harusnya tidak):
```
git ls-files | findstr vendor
```
Kalau muncul hasil — ada yang salah dengan .gitignore. Laporkan ke saya, saya fix.

---

Setelah selesai Langkah 4, balas **"sudah di-push"** + screenshot GitHub repo Anda supaya saya bisa verifikasi isinya benar, lalu saya lanjutkan dengan Laravel Cloud setup.
