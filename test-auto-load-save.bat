@echo off
echo 🚀 Test du Chargement Automatique et de la Sauvegarde
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

echo 🧪 Test des fichiers modifiés...
if exist "public\js\pdf-signature-module.js" (
    echo ✅ pdf-signature-module.js trouvé
) else (
    echo ❌ pdf-signature-module.js non trouvé
)

if exist "app\Http\Controllers\SignatureController.php" (
    echo ✅ SignatureController.php trouvé
) else (
    echo ❌ SignatureController.php non trouvé
)

echo.
echo 📋 Instructions de test:
echo 1. Ouvrez http://localhost:8000/signatures/22 (ou un autre ID de document)
echo 2. Vérifiez que le PDF se charge automatiquement (pas de bouton "Charger PDF")
echo 3. Vérifiez que le statut affiche "PDF chargé automatiquement"
echo 4. Glissez-déposez une signature sur le PDF
echo 5. Cliquez sur "Enregistrer PDF Signé" (au lieu de "Télécharger")
echo 6. Vérifiez que le PDF est enregistré sur le serveur
echo 7. Vérifiez les logs dans la console du navigateur (F12)
echo.

echo 🚀 Démarrage du serveur sur http://localhost:8000...
echo.
echo 💡 Ouvrez http://localhost:8000/signatures/22 dans votre navigateur
echo 💡 Testez le chargement automatique du PDF
echo 💡 Testez la sauvegarde au lieu du téléchargement
echo 💡 Appuyez sur Ctrl+C pour arrêter le serveur
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
