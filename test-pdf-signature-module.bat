@echo off
echo 🚀 Test du Module de Signature PDF - Drag & Drop
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

echo 🧪 Test du module de signature PDF...
if exist "public\pdf-signature-module.html" (
    echo ✅ pdf-signature-module.html trouvé
) else (
    echo ❌ pdf-signature-module.html non trouvé
)

echo.
echo 📋 Instructions de test:
echo 1. Ouvrez http://localhost:8000/pdf-signature-module.html
echo 2. Cliquez sur "Choisir un fichier PDF"
echo 3. Sélectionnez un fichier PDF local
echo 4. Glissez l'image de signature sur le PDF
echo 5. Cliquez sur "Télécharger PDF signé"
echo 6. Vérifiez que le PDF téléchargé contient la signature
echo.

echo 🚀 Démarrage du serveur sur http://localhost:8000...
echo.
echo 💡 Ouvrez http://localhost:8000/pdf-signature-module.html dans votre navigateur
echo 💡 Testez le glisser-déposer de signature sur le PDF
echo 💡 Vérifiez que le PDF signé se télécharge correctement
echo 💡 Appuyez sur Ctrl+C pour arrêter le serveur
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
