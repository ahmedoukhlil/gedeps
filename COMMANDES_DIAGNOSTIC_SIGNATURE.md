# 🔧 COMMANDES DE DIAGNOSTIC - SIGNATURES UBUNTU

## Vue d'ensemble

Ce document fournit les commandes spécifiques pour diagnostiquer et corriger les problèmes de signature sur un serveur Ubuntu de production.

## 🔍 DIAGNOSTIC INITIAL

### 1. **Vérification de l'environnement système**

```bash
# Vérifier la version d'Ubuntu
lsb_release -a

# Vérifier la version de PHP
php -v

# Vérifier les extensions PHP installées
php -m | grep -E "(gd|imagick|curl|json|mbstring|openssl)"

# Vérifier l'espace disque
df -h

# Vérifier la mémoire disponible
free -h
```

### 2. **Vérification des permissions des dossiers**

```bash
# Vérifier les permissions du dossier storage
ls -la storage/

# Vérifier les permissions des sous-dossiers
ls -la storage/app/
ls -la storage/app/public/
ls -la storage/app/public/documents/
ls -la storage/app/public/signatures/

# Vérifier le propriétaire des dossiers
stat storage/
stat storage/app/
stat storage/app/public/
```

### 3. **Vérification des logs d'erreur**

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs Apache
sudo tail -f /var/log/apache2/error.log

# Logs Nginx (si utilisé)
sudo tail -f /var/log/nginx/error.log

# Logs système
sudo tail -f /var/log/syslog | grep -i php
```

## 🔧 CORRECTION DES PROBLÈMES

### 1. **Installation des extensions PHP manquantes**

```bash
# Mettre à jour les paquets
sudo apt update

# Installer les extensions PHP nécessaires
sudo apt install php-gd php-imagick php-curl php-mbstring php-xml php-zip php-dom php-fileinfo php-openssl

# Pour une version spécifique de PHP (ex: PHP 8.1)
sudo apt install php8.1-gd php8.1-imagick php8.1-curl php8.1-mbstring php8.1-xml php8.1-zip php8.1-dom php8.1-fileinfo php8.1-openssl

# Vérifier l'installation
php -m | grep -E "(gd|imagick|curl|json|mbstring|openssl)"
```

### 2. **Correction des permissions**

```bash
# Définir le bon propriétaire (remplacer www-data par l'utilisateur approprié)
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/

# Définir les bonnes permissions
sudo chmod -R 755 storage/
sudo chmod -R 755 bootstrap/cache/

# Permissions spécifiques pour les dossiers de signature
sudo chmod -R 775 storage/app/public/documents/
sudo chmod -R 775 storage/app/public/signatures/
```

### 3. **Création des dossiers manquants**

```bash
# Créer les dossiers nécessaires
sudo mkdir -p storage/app/public/documents
sudo mkdir -p storage/app/public/documents/signed
sudo mkdir -p storage/app/public/signatures
sudo mkdir -p storage/logs

# Définir les permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

### 4. **Configuration PHP**

```bash
# Trouver le fichier de configuration PHP
php --ini

# Éditer le fichier de configuration (remplacer par le chemin trouvé)
sudo nano /etc/php/8.1/apache2/php.ini

# Ou pour PHP-FPM
sudo nano /etc/php/8.1/fpm/php.ini
```

**Paramètres à modifier dans php.ini :**
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

### 5. **Redémarrage des services**

```bash
# Redémarrer Apache
sudo systemctl restart apache2

# Redémarrer Nginx (si utilisé)
sudo systemctl restart nginx

# Redémarrer PHP-FPM (si utilisé)
sudo systemctl restart php8.1-fpm

# Vérifier le statut des services
sudo systemctl status apache2
sudo systemctl status nginx
sudo systemctl status php8.1-fpm
```

## 🧪 TESTS DE FONCTIONNEMENT

### 1. **Test de création d'image PHP**

```bash
# Test simple de création d'image
php -r "
if (extension_loaded('gd')) {
    echo 'Extension GD disponible\n';
    \$img = imagecreate(100, 50);
    if (\$img) {
        echo 'Création d\'image réussie\n';
        imagedestroy(\$img);
    } else {
        echo 'Échec de création d\'image\n';
    }
} else {
    echo 'Extension GD non disponible\n';
}
"
```

### 2. **Test de sauvegarde de fichier**

```bash
# Test de création et sauvegarde d'image
php -r "
\$img = imagecreate(200, 100);
\$bg = imagecolorallocate(\$img, 255, 255, 255);
\$text = imagecolorallocate(\$img, 0, 0, 0);
imagestring(\$img, 5, 50, 40, 'TEST', \$text);
if (imagepng(\$img, 'test-signature.png')) {
    echo 'Image sauvegardée avec succès\n';
    unlink('test-signature.png');
} else {
    echo 'Échec de sauvegarde\n';
}
imagedestroy(\$img);
"
```

### 3. **Test des permissions d'écriture**

```bash
# Test d'écriture dans storage
touch storage/test-write.txt
if [ $? -eq 0 ]; then
    echo "Écriture dans storage OK"
    rm storage/test-write.txt
else
    echo "Problème d'écriture dans storage"
fi

# Test d'écriture dans le dossier de signatures
touch storage/app/public/signatures/test-write.txt
if [ $? -eq 0 ]; then
    echo "Écriture dans signatures OK"
    rm storage/app/public/signatures/test-write.txt
else
    echo "Problème d'écriture dans signatures"
fi
```

## 🔄 NETTOYAGE LARAVEL

### 1. **Nettoyage du cache**

```bash
# Vider le cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Régénérer les liens symboliques
php artisan storage:link

# Optimiser l'application
php artisan optimize
```

### 2. **Vérification de la configuration**

```bash
# Vérifier la configuration Laravel
php artisan config:show

# Vérifier les routes
php artisan route:list

# Vérifier l'état de l'application
php artisan about
```

## 🚨 DIAGNOSTIC AVANCÉ

### 1. **Vérification des processus PHP**

```bash
# Voir les processus PHP en cours
ps aux | grep php

# Voir l'utilisation de la mémoire
ps aux | grep php | awk '{sum+=$6} END {print "Mémoire PHP utilisée: " sum/1024 " MB"}'
```

### 2. **Vérification des erreurs PHP**

```bash
# Vérifier les erreurs PHP dans les logs
sudo grep -i "error\|exception\|fatal" /var/log/apache2/error.log | tail -20

# Vérifier les erreurs dans les logs Laravel
grep -i "error\|exception\|fatal" storage/logs/laravel.log | tail -20
```

### 3. **Test de connectivité**

```bash
# Test de connectivité à la base de données
php artisan tinker
# Puis dans tinker :
# DB::connection()->getPdo();

# Test de l'application
curl -I http://votre-domaine.com
```

## 📋 COMMANDES DE MAINTENANCE

### 1. **Surveillance en temps réel**

```bash
# Surveiller les logs Laravel
tail -f storage/logs/laravel.log

# Surveiller les logs Apache
sudo tail -f /var/log/apache2/error.log

# Surveiller l'utilisation des ressources
watch -n 1 'ps aux | grep php'
```

### 2. **Nettoyage périodique**

```bash
# Nettoyer les logs anciens
find storage/logs -name "*.log" -mtime +7 -delete

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear

# Optimiser l'application
php artisan optimize
```

## 🎯 COMMANDES SPÉCIFIQUES POUR VOTRE PROBLÈME

### 1. **Diagnostic complet en une commande**

```bash
# Script de diagnostic complet
echo "=== DIAGNOSTIC SIGNATURES GEDEPS ===" && \
echo "Version PHP:" && php -v && \
echo "Extensions:" && php -m | grep -E "(gd|imagick|curl)" && \
echo "Permissions storage:" && ls -la storage/ && \
echo "Espace disque:" && df -h && \
echo "Mémoire:" && free -h && \
echo "Logs récents:" && tail -5 storage/logs/laravel.log
```

### 2. **Correction rapide**

```bash
# Correction rapide des problèmes courants
sudo chown -R www-data:www-data storage/ bootstrap/cache/ && \
sudo chmod -R 755 storage/ bootstrap/cache/ && \
php artisan cache:clear && \
php artisan storage:link && \
sudo systemctl restart apache2
```

### 3. **Test de signature spécifique**

```bash
# Créer un fichier de test de signature
cat > test-signature.php << 'EOF'
<?php
header('Content-Type: image/png');
$img = imagecreate(300, 150);
$bg = imagecolorallocate($img, 255, 255, 255);
$text = imagecolorallocate($img, 0, 0, 0);
$border = imagecolorallocate($img, 0, 0, 255);
imagerectangle($img, 10, 10, 290, 140, $border);
imagestring($img, 5, 100, 60, 'SIGNATURE TEST', $text);
imagepng($img);
imagedestroy($img);
?>
EOF

# Tester le fichier
php test-signature.php > test-output.png
if [ $? -eq 0 ]; then
    echo "Test de signature réussi"
    rm test-signature.php test-output.png
else
    echo "Échec du test de signature"
fi
```

## 📞 SUPPORT

Si les problèmes persistent après avoir exécuté ces commandes :

1. **Vérifiez les logs** : `tail -f storage/logs/laravel.log`
2. **Vérifiez les permissions** : `ls -la storage/`
3. **Testez manuellement** : Créez un fichier PHP simple pour tester GD
4. **Contactez l'administrateur** : Fournissez les résultats des commandes de diagnostic

---

**Note** : Remplacez `www-data` par l'utilisateur web approprié sur votre serveur (peut être `apache`, `nginx`, etc.).
