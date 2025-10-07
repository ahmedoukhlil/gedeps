@echo off
echo Démarrage de l'application Laravel sur le réseau local...
echo.
echo URL d'accès local: http://localhost:8000
echo URL d'accès réseau: http://192.168.100.41:8000
echo.
echo Appuyez sur Ctrl+C pour arrêter le serveur
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause
