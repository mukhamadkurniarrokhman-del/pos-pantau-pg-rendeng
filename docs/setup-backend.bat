@echo off
setlocal
title POS Pantau PG Rendeng - Setup Backend

echo ==============================================
echo   SETUP BACKEND - POS PANTAU PG RENDENG
echo ==============================================
echo.

cd /d "%~dp0backend"
if errorlevel 1 (
  echo [ERROR] Folder backend tidak ditemukan.
  pause
  exit /b 1
)

echo [1/6] Cek PHP...
where php >nul 2>nul
if errorlevel 1 (
  echo [ERROR] PHP tidak ditemukan di PATH.
  echo Install PHP 8.2+ dulu lewat Laragon atau XAMPP.
  pause
  exit /b 1
)
echo       OK
echo.

echo [2/6] Cek Composer...
where composer >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Composer tidak ditemukan.
  echo Install dari https://getcomposer.org
  pause
  exit /b 1
)
echo       OK
echo.

echo [3/6] Install dependencies composer install...
call composer install --no-interaction --optimize-autoloader
if errorlevel 1 (
  echo [ERROR] Gagal composer install.
  pause
  exit /b 1
)
echo.

echo [4/6] Setup file .env...
if not exist ".env" (
  copy ".env.example" ".env" >nul
  echo       .env dibuat dari .env.example
) else (
  echo       .env sudah ada, dilewati
)
call php artisan key:generate --force
echo.

echo [5/6] Migrate database + seed data dummy...
call php artisan migrate:fresh --seed --force
if errorlevel 1 (
  echo [ERROR] Migrate gagal.
  echo Pastikan DB_DATABASE di .env sudah dibuat di MySQL.
  pause
  exit /b 1
)
echo.

echo [6/6] Buat symlink storage...
call php artisan storage:link
echo.

echo ==============================================
echo   SETUP SELESAI!
echo ==============================================
echo.
echo   Akun default:
echo     Admin      : ADM-001    / admin123
echo     Supervisor : SPV-001    / supervisor123
echo     Petugas    : PTG-JPR-01 / petugas123
echo.
echo   Menjalankan server di http://localhost:8000
echo   Tekan Ctrl+C untuk berhenti.
echo.

call php artisan serve --host=0.0.0.0 --port=8000
