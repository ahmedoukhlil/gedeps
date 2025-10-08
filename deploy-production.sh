#!/bin/bash

# Script de déploiement pour la production
# GEDEPS - Système de Gestion Électronique de Documents

echo "🚀 Déploiement en production - GEDEPS"
echo "======================================"

# 1. Vérifier les prérequis
echo "📋 Vérification des prérequis..."

# Vérifier PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé"
    exit 1
fi

# Vérifier Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé"
    exit 1
fi

# Vérifier Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé"
    exit 1
fi

# Vérifier NPM
if ! command -v npm &> /dev/null; then
    echo "❌ NPM n'est pas installé"
    exit 1
fi

echo "✅ Prérequis vérifiés"

# 2. Configuration de l'environnement
echo "⚙️ Configuration de l'environnement..."

# Copier le fichier .env.example vers .env si nécessaire
if [ ! -f .env ]; then
    echo "📝 Création du fichier .env..."
    cp .env.example .env
    echo "⚠️ Veuillez configurer le fichier .env avec vos paramètres de production"
fi

# 3. Installation des dépendances
echo "📦 Installation des dépendances..."

# Installer les dépendances PHP
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Installer les dépendances Node.js
echo "Installing Node.js dependencies..."
npm install

# 4. Compilation des assets
echo "🔨 Compilation des assets..."

# Compiler les assets avec Vite
echo "Building assets with Vite..."
npm run build

# 5. Configuration Laravel
echo "⚙️ Configuration Laravel..."

# Générer la clé d'application
php artisan key:generate

# Optimiser la configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Base de données
echo "🗄️ Configuration de la base de données..."

# Exécuter les migrations
php artisan migrate --force

# 7. Permissions
echo "🔐 Configuration des permissions..."

# Définir les permissions pour le stockage
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 8. Vérification finale
echo "✅ Vérification finale..."

# Vérifier que les fichiers CSS existent
if [ -f "public/css/app.css" ]; then
    echo "✅ CSS principal trouvé"
else
    echo "❌ CSS principal manquant"
fi

# Vérifier que les fichiers JS existent
if [ -f "public/js/app.js" ]; then
    echo "✅ JS principal trouvé"
else
    echo "❌ JS principal manquant"
fi

# Vérifier que le manifest Vite existe
if [ -f "public/build/manifest.json" ]; then
    echo "✅ Manifest Vite trouvé"
else
    echo "❌ Manifest Vite manquant"
fi

echo "🎉 Déploiement terminé avec succès!"
echo "======================================"
echo "📝 Prochaines étapes:"
echo "1. Configurez votre serveur web (Apache/Nginx)"
echo "2. Configurez votre base de données"
echo "3. Testez l'application"
echo "4. Configurez les notifications email"
echo "5. Configurez les permissions utilisateur"
