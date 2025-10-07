# Script PowerShell pour démarrer le serveur Laravel
Write-Host "🚀 Démarrage du serveur Laravel..." -ForegroundColor Green
Write-Host ""

# Changer de répertoire
Set-Location "C:\wamp64\www\gedeps1"
Write-Host "📁 Répertoire de travail: $(Get-Location)" -ForegroundColor Blue
Write-Host ""

# Vérifier PHP
Write-Host "🔧 Vérification de l'environnement..." -ForegroundColor Yellow
php --version
Write-Host ""

# Vérifier la base de données
Write-Host "🗄️ Vérification de la base de données..." -ForegroundColor Yellow
php artisan migrate:status
Write-Host ""

# Générer la clé d'application
Write-Host "🔑 Génération de la clé d'application..." -ForegroundColor Yellow
php artisan key:generate
Write-Host ""

# Créer le lien symbolique
Write-Host "🔗 Création du lien symbolique de stockage..." -ForegroundColor Yellow
php artisan storage:link
Write-Host ""

# Démarrer le serveur
Write-Host "🚀 Démarrage du serveur sur http://localhost:8000..." -ForegroundColor Green
Write-Host ""
Write-Host "💡 Ouvrez http://localhost:8000 dans votre navigateur" -ForegroundColor Cyan
Write-Host "💡 Appuyez sur Ctrl+C pour arrêter le serveur" -ForegroundColor Cyan
Write-Host ""

php artisan serve --host=0.0.0.0 --port=8000
