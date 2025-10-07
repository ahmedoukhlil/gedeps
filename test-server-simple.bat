@echo off
echo 🚀 Test simple du serveur Laravel
echo.

cd /d C:\wamp64\www\gedeps1

echo 📁 Répertoire de travail: %CD%
echo.

echo 🔧 Vérification de l'environnement...
php --version
echo.

echo 🗄️ Vérification de la base de données...
php artisan migrate:status
echo.

echo 🔑 Génération de la clé d'application...
php artisan key:generate
echo.

echo 🔗 Création du lien symbolique de stockage...
php artisan storage:link
echo.

echo 🧪 Test des fichiers de l'éditeur PDF-lib...
if exist "public\js\pdf-lib-editor.js" (
    echo ✅ pdf-lib-editor.js trouvé
) else (
    echo ❌ pdf-lib-editor.js non trouvé
)

if exist "public\css\pdf-lib-editor.css" (
    echo ✅ pdf-lib-editor.css trouvé
) else (
    echo ❌ pdf-lib-editor.css non trouvé
)

if exist "public\js\debug-pdf-loading.js" (
    echo ✅ debug-pdf-loading.js trouvé
) else (
    echo ❌ debug-pdf-loading.js non trouvé
)

echo.
echo 🚀 Démarrage du serveur sur http://localhost:8000...
echo.
echo 💡 Ouvrez http://localhost:8000 dans votre navigateur
echo 💡 Testez l'éditeur PDF-lib
echo 💡 Vérifiez les logs dans la console du navigateur
echo 💡 Appuyez sur Ctrl+C pour arrêter le serveur
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
