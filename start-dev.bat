@echo off
REM ==========================================================
REM  Smart School - Jalankan Semua Layanan Sekali Klik
REM ==========================================================

title Smart School - Dev Server
set PORT=8000
set NGROK_URL=
set XAMPP_DIR=C:\xampp

cd /d "%~dp0"

echo ==========================================================
echo       SMART SCHOOL - MENYIAPKAN SEMUA LAYANAN...
echo ==========================================================
echo.

if not exist ".env" (
    echo File .env belum ditemukan, menjalankan setup-dev.bat...
    call setup-dev.bat
)

for /f "usebackq eol=# tokens=1,* delims==" %%A in (".env") do (
    if /i "%%A"=="NGROK_URL" set "NGROK_URL=%%~B"
    if /i "%%A"=="XAMPP_DIR" set "XAMPP_DIR=%%~B"
)

php -r "exit(version_compare(PHP_VERSION, '8.3', '<') ? 1 : 0);"
if errorlevel 1 (
    echo [ERROR] PHP yang terpakai terlalu lama. Butuh PHP 8.3 ke atas.
    echo Pasang PHP baru lewat https://php.new
    php -v
    pause
    exit /b 1
)

REM ----------------------------------------------------------
REM 1. MySQL (XAMPP - Port 3306)
REM ----------------------------------------------------------
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul
if errorlevel 1 (
    echo [1/5] Menyalakan MySQL XAMPP...
    start "MySQL" /min cmd /c "%XAMPP_DIR%\mysql_start.bat"
    ping 127.0.0.1 -n 4 >nul
) else (
    echo [1/5] MySQL sudah aktif [Port 3306]
)

REM ----------------------------------------------------------
REM 2. Apache (XAMPP - Port 80 phpMyAdmin)
REM ----------------------------------------------------------
netstat -ano | findstr /R /C:":80 .*LISTENING" >nul
if errorlevel 1 (
    echo [2/5] Menyalakan Apache XAMPP phpMyAdmin...
    start "Apache" /min cmd /c "%XAMPP_DIR%\apache_start.bat"
    ping 127.0.0.1 -n 3 >nul
) else (
    echo [2/5] Apache sudah aktif [Port 80]
)

REM ----------------------------------------------------------
REM 3. Laravel Server (Port 8000)
REM ----------------------------------------------------------
netstat -ano | findstr /R /C:":%PORT% .*LISTENING" >nul
if errorlevel 1 (
    echo [3/5] Menyalakan Laravel server di port %PORT%...
    start "Laravel Server" cmd /k php artisan serve --host=0.0.0.0 --port=%PORT%
    ping 127.0.0.1 -n 3 >nul
) else (
    echo [3/5] Laravel Server sudah aktif [Port %PORT%]
)

REM ----------------------------------------------------------
REM 4. Queue Worker (Pengirim Email OTP)
REM ----------------------------------------------------------
powershell -NoProfile -Command "if (Get-CimInstance Win32_Process -Filter \"Name='php.exe'\" | Where-Object { $_.CommandLine -match 'queue:work' }) { exit 0 } else { exit 1 }"
if errorlevel 1 (
    echo [4/5] Menyalakan Queue Worker OTP...
    start "Queue Worker" cmd /k php artisan queue:work
) else (
    echo [4/5] Queue Worker OTP sudah aktif.
)

REM ----------------------------------------------------------
REM 5. ngrok (Akses Publik Online)
REM ----------------------------------------------------------
where ngrok >nul 2>nul
if errorlevel 1 (
    echo [5/5] ngrok tidak ditemukan - dilewati.
) else (
    tasklist /FI "IMAGENAME eq ngrok.exe" | findstr /I "ngrok.exe" >nul
    if errorlevel 1 (
        echo [5/5] Menyalakan ngrok...
        if defined NGROK_URL (
            start "ngrok" cmd /k ngrok http %PORT% --url=%NGROK_URL%
        ) else (
            start "ngrok" cmd /k ngrok http %PORT%
        )
    ) else (
        echo [5/5] ngrok sudah aktif.
    )
)

echo.
echo ==========================================================
echo        SEMUA LAYANAN SIAP DIGUNAKAN!
echo ==========================================================
echo.
echo   1. Database phpMyAdmin : http://localhost/phpmyadmin
echo   2. Admin Panel Web     : http://localhost:%PORT%/admin
echo   3. REST API Lokal      : http://127.0.0.1:%PORT%/api
if defined NGROK_URL (
echo   4. REST API Publik     : %NGROK_URL%/api
) else (
echo   4. REST API Publik     : Lihat jendela ngrok
)
echo.
echo Membuka phpMyAdmin di browser Anda...
start "" "http://localhost/phpmyadmin"
echo.
echo Tekan tombol apa saja untuk menutup jendela ini.
pause >nul
