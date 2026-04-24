@echo off
setlocal
title POS Pantau PG Rendeng - Buat Database

echo.
echo ==============================================
echo   BUAT DATABASE pos_pantau_pgrendeng
echo ==============================================
echo.

rem Cari mysql di PATH
where mysql >nul 2>nul
if errorlevel 1 (
  echo [ERROR] mysql tidak ditemukan di PATH.
  echo Silakan buka HeidiSQL via Laragon lalu buat database secara manual.
  pause
  exit /b 1
)

echo Membuat database jika belum ada...
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS pos_pantau_pgrendeng CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
  echo.
  echo [ERROR] Gagal membuat database.
  echo Cek:
  echo   1. Pastikan Laragon sudah Start All MySQL nyala
  echo   2. Kalau MySQL pakai password, jalankan manual: mysql -uroot -p
  pause
  exit /b 1
)

echo.
echo ==============================================
echo   BERHASIL! Database pos_pantau_pgrendeng siap.
echo ==============================================
echo.
echo Langkah berikutnya: double-click setup-backend.bat
echo.
pause
