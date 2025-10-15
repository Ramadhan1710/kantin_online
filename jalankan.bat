@echo off
echo ========================================
echo   OPENING KANTIN ONLINE
echo ========================================
echo.
echo Starting PHP Server...
echo.

cd /d D:\PROJECT\php\kantin_online
start http://localhost:8000/login.php
php -S localhost:8000
