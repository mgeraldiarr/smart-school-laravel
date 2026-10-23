@echo off
REM ==========================================================
REM  Smart School - Jalankan semua layanan development
REM  Klik dua kali file ini. Tutup jendela masing-masing untuk mematikan.
REM  Pengaturan (NGROK_URL, XAMPP_DIR) dibaca dari file .env
REM ==========================================================

set PORT=8000
set NGROK_URL=
set XAMPP_DIR=C:\xampp

cd /d "%~dp0"

if not exist ".env" (
    echo File .env belum ada. Jalankan setup-dev.bat terlebih dahulu.
    pause
    exit /b 1
)

for /f "usebackq eol=# tokens=1,* delims==" %%A in (".env") do (
    if /i "%%A"=="NGROK_URL" set "NGROK_URL=%%~B"
    if /i "%%A"=="XAMPP_DIR" set "XAMPP_DIR=%%~B"
)

php -r "exit(version_compare(PHP_VERSION, '8.3', '<') ? 1 : 0);"
if errorlevel 1 (
    echo PHP yang terpakai terlalu lama. Laravel 13 butuh PHP 8.3 ke atas.
    echo Jika memakai PHP dari XAMPP ^(8.2^), pasang PHP baru lewat https://php.new
    php -v
    pause
    exit /b 1
)

REM 1. MySQL (XAMPP)
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul
if errorlevel 1 (
    echo [1/4] Menyalakan MySQL XAMPP...
    start "MySQL (XAMPP)" /min cmd /c "%XAMPP_DIR%\mysql_start.bat"
    timeout /t 5 /nobreak >nul
) else (
    echo [1/4] MySQL sudah menyala.
)

REM 2. Laravel server (0.0.0.0 agar bisa diakses HP di Wi-Fi yang sama)
netstat -ano | findstr /R /C:":%PORT% .*LISTENING" >nul
if errorlevel 1 (
    echo [2/4] Menyalakan Laravel server di port %PORT%...
    start "Laravel Server" cmd /k php artisan serve --host=0.0.0.0 --port=%PORT%
) else (
    echo [2/4] Port %PORT% sudah dipakai, Laravel server dianggap sudah menyala.
)

REM 3. Queue worker (pengirim email OTP)
powershell -NoProfile -Command "if (Get-CimInstance Win32_Process -Filter \"Name='php.exe'\" | Where-Object { $_.CommandLine -match 'queue:work' }) { exit 0 } else { exit 1 }"
if errorlevel 1 (
    echo [3/4] Menyalakan queue worker...
    start "Queue Worker (Email OTP)" cmd /k php artisan queue:work
) else (
    echo [3/4] Queue worker sudah menyala.
)

REM 4. ngrok (akses untuk teman di luar jaringan)
where ngrok >nul 2>nul
if errorlevel 1 (
    echo [4/4] ngrok tidak ditemukan, dilewati. Pasang dari https://ngrok.com/download
) else (
    tasklist /FI "IMAGENAME eq ngrok.exe" | findstr /I "ngrok.exe" >nul
    if errorlevel 1 (
        echo [4/4] Menyalakan ngrok...
        if defined NGROK_URL (
            start "ngrok" cmd /k ngrok http %PORT% --url=%NGROK_URL%
        ) else (
            start "ngrok" cmd /k ngrok http %PORT%
        )
    ) else (
        echo [4/4] ngrok sudah menyala.
    )
)

echo.
echo Semua layanan siap.
echo   Lokal : http://127.0.0.1:%PORT%/api
if defined NGROK_URL (
    echo   Publik: %NGROK_URL%/api
) else (
    echo   Publik: lihat alamat "Forwarding" di jendela ngrok, lalu isi NGROK_URL di .env
)
echo.
pause
