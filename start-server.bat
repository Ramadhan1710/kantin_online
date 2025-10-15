@echo off
echo ========================================
echo   STARTING KANTIN ONLINE SERVER
echo ========================================
echo.
echo Project Path: D:\PROJECT\php\kantin_online
echo Server URL: http://localhost:8000
echo.
echo Tekan Ctrl+C untuk stop server
echo ========================================
echo.

cd /d D:\PROJECT\php\kantin_online
php -S localhost:8000

pause
