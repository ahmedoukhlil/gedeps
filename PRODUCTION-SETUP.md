# 🚀 Guide de Configuration Production - GEDEPS

## 📋 Prérequis

### Serveur
- **PHP** : 8.1+ (recommandé 8.3+)
- **Composer** : 2.0+
- **Node.js** : 16+ (recommandé 18+)
- **NPM** : 8+
- **Base de données** : MySQL 8.0+ ou PostgreSQL 13+
- **Serveur web** : Apache 2.4+ ou Nginx 1.18+

### Extensions PHP Requises
```bash
php -m | grep -E "(gd|mbstring|openssl|pdo|tokenizer|xml|zip|curl|fileinfo|json)"
```

## ⚙️ Configuration

### 1. Variables d'Environnement (.env)
```env
APP_NAME=GEDEPS
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gedeps_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="GEDEPS"

CACHE_DRIVER=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### 2. Configuration du Serveur Web

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/gedeps/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔧 Installation

### 1. Cloner le Projet
```bash
git clone https://github.com/your-repo/gedeps.git
cd gedeps
```

### 2. Installer les Dépendances
```bash
# Dépendances PHP
composer install --no-dev --optimize-autoloader

# Dépendances Node.js
npm install
```

### 3. Compiler les Assets
```bash
# Compilation pour la production
npm run build
```

### 4. Configuration Laravel
```bash
# Générer la clé d'application
php artisan key:generate

# Optimiser la configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Base de Données
```bash
# Exécuter les migrations
php artisan migrate --force

# Créer les utilisateurs de base
php artisan db:seed
```

## 🔐 Sécurité

### 1. Permissions
```bash
# Propriétaire du projet
sudo chown -R www-data:www-data /var/www/gedeps

# Permissions
sudo chmod -R 755 /var/www/gedeps
sudo chmod -R 775 /var/www/gedeps/storage
sudo chmod -R 775 /var/www/gedeps/bootstrap/cache
```

### 2. Configuration SSL
```bash
# Installer Certbot
sudo apt install certbot python3-certbot-apache

# Obtenir un certificat SSL
sudo certbot --apache -d your-domain.com
```

## 📊 Monitoring

### 1. Logs
```bash
# Surveiller les logs Laravel
tail -f storage/logs/laravel.log

# Surveiller les logs Apache
tail -f /var/log/apache2/error.log
```

### 2. Performance
```bash
# Optimiser Composer
composer dump-autoload --optimize

# Optimiser les routes
php artisan route:cache

# Optimiser les vues
php artisan view:cache
```

## 🚨 Dépannage

### 1. Problèmes Courants

#### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
ls -la storage/
ls -la bootstrap/cache/
```

#### Assets non chargés
```bash
# Vérifier le manifest Vite
cat public/build/manifest.json

# Recompiler les assets
npm run build
```

#### Base de données
```bash
# Tester la connexion
php artisan tinker
>>> DB::connection()->getPdo();
```

### 2. Commandes de Diagnostic
```bash
# Vérifier la configuration
php artisan about

# Vérifier les routes
php artisan route:list

# Vérifier les permissions
php artisan permission:show
```

## 📈 Optimisations

### 1. Cache
```bash
# Activer le cache de configuration
php artisan config:cache

# Activer le cache des routes
php artisan route:cache

# Activer le cache des vues
php artisan view:cache
```

### 2. Base de Données
```sql
-- Optimiser les tables MySQL
OPTIMIZE TABLE documents;
OPTIMIZE TABLE users;
OPTIMIZE TABLE document_signatures;
```

### 3. Serveur Web
```apache
# Compression GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

## 🔄 Mise à Jour

### 1. Sauvegarde
```bash
# Sauvegarder la base de données
mysqldump -u username -p gedeps_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Sauvegarder les fichiers
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/gedeps
```

### 2. Mise à Jour
```bash
# Récupérer les dernières modifications
git pull origin main

# Installer les nouvelles dépendances
composer install --no-dev --optimize-autoloader
npm install

# Compiler les nouveaux assets
npm run build

# Exécuter les migrations
php artisan migrate --force

# Optimiser la configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📞 Support

En cas de problème :
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Vérifiez la configuration : `php artisan about`
3. Vérifiez les permissions : `ls -la storage/`
4. Contactez l'équipe de développement

---

**GEDEPS** - Système de Gestion Électronique de Documents  
Version : 1.0.0  
Dernière mise à jour : $(date)
