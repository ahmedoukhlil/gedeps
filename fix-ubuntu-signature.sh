#!/bin/bash

# ========================================
# SCRIPT DE CORRECTION DES SIGNATURES - UBUNTU
# GEDEPS - Correction automatique des problèmes de signature
# ========================================

echo "🔧 Correction des Signatures - Ubuntu GEDEPS"
echo "=============================================="

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️ $1${NC}"
}

# ========================================
# 1. VÉRIFICATION DES PRÉREQUIS
# ========================================
echo ""
echo "🔍 Vérification des prérequis..."

# Vérifier si on est root ou sudo
if [ "$EUID" -ne 0 ]; then
    print_warning "Ce script nécessite des privilèges sudo. Relancez avec: sudo $0"
    exit 1
fi

# Vérifier la version d'Ubuntu
if [ -f /etc/os-release ]; then
    . /etc/os-release
    print_info "Système détecté: $NAME $VERSION"
else
    print_warning "Impossible de détecter la version du système"
fi

# ========================================
# 2. INSTALLATION DES EXTENSIONS PHP MANQUANTES
# ========================================
echo ""
echo "📦 Installation des extensions PHP..."

# Détecter la version de PHP
PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
print_info "Version PHP détectée: $PHP_VERSION"

# Extensions à installer
EXTENSIONS=(
    "php$PHP_VERSION-gd"
    "php$PHP_VERSION-imagick"
    "php$PHP_VERSION-curl"
    "php$PHP_VERSION-mbstring"
    "php$PHP_VERSION-xml"
    "php$PHP_VERSION-zip"
    "php$PHP_VERSION-dom"
    "php$PHP_VERSION-fileinfo"
    "php$PHP_VERSION-openssl"
)

for ext in "${EXTENSIONS[@]}"; do
    if dpkg -l | grep -q "^ii  $ext "; then
        print_success "$ext déjà installé"
    else
        print_info "Installation de $ext..."
        apt-get update
        apt-get install -y $ext
        if [ $? -eq 0 ]; then
            print_success "$ext installé avec succès"
        else
            print_error "Échec de l'installation de $ext"
        fi
    fi
done

# ========================================
# 3. CONFIGURATION DES PERMISSIONS
# ========================================
echo ""
echo "📁 Configuration des permissions..."

# Détecter l'utilisateur web
WEB_USER="www-data"
if [ -f /etc/apache2/envvars ]; then
    WEB_USER="www-data"
elif [ -f /etc/nginx/nginx.conf ]; then
    WEB_USER="www-data"
else
    print_warning "Utilisateur web par défaut: $WEB_USER"
fi

print_info "Utilisateur web: $WEB_USER"

# Dossiers à corriger
DIRECTORIES=(
    "storage/app"
    "storage/app/public"
    "storage/app/public/documents"
    "storage/app/public/documents/signed"
    "storage/app/public/signatures"
    "storage/logs"
    "bootstrap/cache"
    "public/storage"
)

for dir in "${DIRECTORIES[@]}"; do
    if [ -d "$dir" ]; then
        print_info "Correction des permissions pour $dir"
        chown -R $WEB_USER:$WEB_USER "$dir"
        chmod -R 755 "$dir"
        print_success "Permissions corrigées pour $dir"
    else
        print_warning "Dossier $dir non trouvé, création..."
        mkdir -p "$dir"
        chown -R $WEB_USER:$WEB_USER "$dir"
        chmod -R 755 "$dir"
        print_success "Dossier $dir créé avec les bonnes permissions"
    fi
done

# ========================================
# 4. CONFIGURATION PHP
# ========================================
echo ""
echo "⚙️ Configuration PHP..."

# Fichier de configuration PHP
PHP_INI="/etc/php/$PHP_VERSION/apache2/php.ini"
if [ -f "$PHP_INI" ]; then
    print_info "Configuration de $PHP_INI"
    
    # Sauvegarder la configuration actuelle
    cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"
    print_success "Sauvegarde de la configuration PHP créée"
    
    # Modifier les paramètres importants
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_INI"
    sed -i 's/post_max_size = .*/post_max_size = 50M/' "$PHP_INI"
    sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
    sed -i 's/max_input_time = .*/max_input_time = 300/' "$PHP_INI"
    
    print_success "Configuration PHP mise à jour"
else
    print_warning "Fichier de configuration PHP non trouvé: $PHP_INI"
fi

# ========================================
# 5. REDÉMARRAGE DES SERVICES
# ========================================
echo ""
echo "🔄 Redémarrage des services..."

# Redémarrer Apache
if systemctl is-active --quiet apache2; then
    print_info "Redémarrage d'Apache..."
    systemctl restart apache2
    if [ $? -eq 0 ]; then
        print_success "Apache redémarré avec succès"
    else
        print_error "Erreur lors du redémarrage d'Apache"
    fi
fi

# Redémarrer Nginx
if systemctl is-active --quiet nginx; then
    print_info "Redémarrage de Nginx..."
    systemctl restart nginx
    if [ $? -eq 0 ]; then
        print_success "Nginx redémarré avec succès"
    else
        print_error "Erreur lors du redémarrage de Nginx"
    fi
fi

# Redémarrer PHP-FPM
if systemctl is-active --quiet php$PHP_VERSION-fpm; then
    print_info "Redémarrage de PHP-FPM..."
    systemctl restart php$PHP_VERSION-fpm
    if [ $? -eq 0 ]; then
        print_success "PHP-FPM redémarré avec succès"
    else
        print_error "Erreur lors du redémarrage de PHP-FPM"
    fi
fi

# ========================================
# 6. NETTOYAGE DU CACHE LARAVEL
# ========================================
echo ""
echo "🧹 Nettoyage du cache Laravel..."

# Vérifier si Laravel est installé
if [ -f "artisan" ]; then
    print_info "Nettoyage du cache Laravel..."
    
    # Vider le cache
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Régénérer les liens symboliques
    php artisan storage:link
    
    print_success "Cache Laravel nettoyé"
else
    print_warning "Laravel non détecté, nettoyage du cache ignoré"
fi

# ========================================
# 7. TEST DE FONCTIONNEMENT
# ========================================
echo ""
echo "🧪 Test de fonctionnement..."

# Test de création d'image
php -r "
if (extension_loaded('gd')) {
    echo '✅ Extension GD disponible\n';
    \$img = imagecreate(100, 50);
    if (\$img) {
        echo '✅ Création d\'image réussie\n';
        imagedestroy(\$img);
    } else {
        echo '❌ Échec de création d\'image\n';
    }
} else {
    echo '❌ Extension GD non disponible\n';
}
"

# ========================================
# 8. RÉSUMÉ ET RECOMMANDATIONS
# ========================================
echo ""
echo "📋 Résumé et recommandations"
echo "=============================="

print_success "Correction terminée !"
echo ""
print_info "Prochaines étapes :"
echo "1. Testez la création de signature dans votre application"
echo "2. Vérifiez les logs : tail -f storage/logs/laravel.log"
echo "3. Si des problèmes persistent, consultez les logs d'erreur"
echo ""
print_info "Commandes utiles :"
echo "- Vérifier les permissions : ls -la storage/"
echo "- Vérifier les logs : tail -f storage/logs/laravel.log"
echo "- Tester PHP : php -m | grep gd"
echo "- Redémarrer Apache : sudo systemctl restart apache2"
echo ""
print_warning "Si les problèmes persistent, contactez l'administrateur système."

echo ""
echo "🔧 Correction terminée le $(date)"
