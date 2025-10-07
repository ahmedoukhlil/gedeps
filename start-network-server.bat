@echo off
echo ========================================
echo   GEDEPS - Serveur Reseau Local
echo ========================================
echo.
echo Demarrage du serveur Laravel...
echo Le serveur sera accessible sur le reseau local
echo.
echo URLs d'acces:
echo - Local: http://localhost:8000
echo - Reseau: http://[VOTRE_IP]:8000
echo.
echo Pour trouver votre adresse IP, utilisez: ipconfig
echo.
echo Appuyez sur Ctrl+C pour arreter le serveur
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
