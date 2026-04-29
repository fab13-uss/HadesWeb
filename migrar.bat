@echo off
title Worker de Migraciones - Hades
echo ================================
echo  Worker de Migraciones - Hades
echo ================================
echo.
echo Asegurate de tener la VPN activa antes de continuar.
echo.
cd /d %~dp0
php artisan queue:work --queue=migraciones --stop-when-empty
echo.
echo Worker finalizado.
pause