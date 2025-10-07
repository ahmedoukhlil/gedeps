@echo off
echo 🚀 Test du Nouveau Module de Signature PDF
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

echo 🧪 Test des fichiers du nouveau module...
if exist "public\js\pdf-signature-module.js" (
    echo ✅ pdf-signature-module.js trouvé
) else (
    echo ❌ pdf-signature-module.js non trouvé
)

if exist "public\css\pdf-signature-module.css" (
    echo ✅ pdf-signature-module.css trouvé
) else (
    echo ❌ pdf-signature-module.css non trouvé
)

if exist "resources\views\signatures\show.blade.php" (
    echo ✅ show.blade.php trouvé
) else (
    echo ❌ show.blade.php non trouvé
)

echo.
echo 🧹 Nettoyage des anciens fichiers...
if exist "public\js\drag-drop-signature-editor.js" (
    echo ❌ Ancien drag-drop-signature-editor.js encore présent
) else (
    echo ✅ Ancien drag-drop-signature-editor.js supprimé
)

if exist "public\css\drag-drop-signature-editor.css" (
    echo ❌ Ancien drag-drop-signature-editor.css encore présent
) else (
    echo ✅ Ancien drag-drop-signature-editor.css supprimé
)

echo.
echo 📋 Instructions de test:
echo 1. Ouvrez http://localhost:8000/signatures/22 (ou un autre ID de document)
echo 2. Vérifiez que le nouveau module s'affiche
echo 3. Cliquez sur "Charger PDF" pour ouvrir le document
echo 4. Glissez l'image de signature sur le PDF
echo 5. Cliquez sur "Télécharger PDF signé"
echo 6. Vérifiez que le PDF téléchargé contient la signature
echo.

echo 🚀 Démarrage du serveur sur http://localhost:8000...
echo.
echo 💡 Ouvrez http://localhost:8000/signatures/22 dans votre navigateur
echo 💡 Testez le nouveau module de signature PDF
echo 💡 Vérifiez que l'ancien système a été retiré
echo 💡 Appuyez sur Ctrl+C pour arrêter le serveur
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
