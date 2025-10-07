@echo off
echo 🚀 Test Final de l'Apposition de Signature
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

echo 🧪 Test des fichiers de l'éditeur PDF-lib simplifié...
if exist "public\js\simple-pdf-lib-editor.js" (
    echo ✅ simple-pdf-lib-editor.js trouvé
) else (
    echo ❌ simple-pdf-lib-editor.js non trouvé
)

if exist "public\css\simple-pdf-lib-editor.css" (
    echo ✅ simple-pdf-lib-editor.css trouvé
) else (
    echo ❌ simple-pdf-lib-editor.css non trouvé
)

if exist "public\js\test-simple-signature.js" (
    echo ✅ test-simple-signature.js trouvé
) else (
    echo ❌ test-simple-signature.js non trouvé
)

if exist "public\js\debug-signature-application.js" (
    echo ✅ debug-signature-application.js trouvé
) else (
    echo ❌ debug-signature-application.js non trouvé
)

echo.
echo 🚀 Démarrage du serveur sur http://localhost:8000...
echo.
echo 💡 Ouvrez http://localhost:8000 dans votre navigateur
echo 💡 Testez l'éditeur PDF-lib simplifié
echo 💡 Cliquez sur le PDF pour ajouter des signatures
echo 💡 Vérifiez les logs dans la console du navigateur
echo 💡 Pour tester directement: http://localhost:8000/test-pdf-direct.html?id=22
echo 💡 Appuyez sur Ctrl+C pour arrêter le serveur
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
