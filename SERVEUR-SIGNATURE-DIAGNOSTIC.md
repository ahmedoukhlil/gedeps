# 🔍 Diagnostic des Problèmes de Signature sur le Serveur

## ✅ Corrections Appliquées

### **1. URL de Base Corrigée**
- ✅ **SignatureController.php** : Suppression du forçage à `localhost:8000`
- ✅ **User.php** : Suppression du forçage à `localhost:8000` pour signature et paraphe
- ✅ **URL dynamique** : Utilisation de `config('app.url')` pour s'adapter au serveur

### **2. Problèmes Potentiels sur le Serveur**

#### **A. Configuration de l'Application**
```bash
# Vérifier la configuration sur le serveur
APP_URL=https://votre-domaine.com  # Doit être configuré correctement
```

#### **B. Permissions de Stockage**
```bash
# Vérifier les permissions sur le serveur
chmod -R 755 storage/
chmod -R 755 public/storage/
chown -R www-data:www-data storage/
chown -R www-data:www-data public/storage/
```

#### **C. Lien Symbolique**
```bash
# Vérifier le lien symbolique sur le serveur
ls -la public/storage
# Doit pointer vers ../storage/app/public
```

#### **D. Configuration du Serveur Web**

**Apache (.htaccess)**
```apache
# Vérifier que le fichier public/.htaccess existe et contient :
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**
```nginx
# Configuration pour servir les fichiers statiques
location /storage/ {
    alias /path/to/your/app/storage/app/public/;
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 🔧 Solutions par Type d'Erreur

### **Erreur 404 - Signature non trouvée**
```bash
# Vérifier que le fichier existe
ls -la storage/app/public/signatures/
# Vérifier les permissions
chmod 644 storage/app/public/signatures/*
```

### **Erreur 500 - Erreur serveur**
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log
# Vérifier les logs du serveur web
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log
```

### **Erreur CORS**
```javascript
// Ajouter dans le contrôleur si nécessaire
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### **Problème de Cache**
```bash
# Vider le cache sur le serveur
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🚀 Script de Diagnostic pour le Serveur

### **1. Vérification des Fichiers**
```bash
#!/bin/bash
echo "=== Diagnostic Serveur ==="
echo "1. Vérification des fichiers de signature..."
find storage/app/public/signatures/ -name "*.png" -o -name "*.jpg" -o -name "*.jpeg" | head -10

echo "2. Vérification des permissions..."
ls -la storage/app/public/signatures/

echo "3. Vérification du lien symbolique..."
ls -la public/storage

echo "4. Test de l'URL de base..."
php artisan tinker --execute="echo config('app.url');"
```

### **2. Test de la Route**
```bash
# Tester la route directement
curl -H "Accept: application/json" https://votre-domaine.com/signatures/user-signature
```

### **3. Vérification de la Base de Données**
```sql
-- Vérifier les signatures des utilisateurs
SELECT id, name, signature_path FROM users WHERE signature_path IS NOT NULL;
```

## 📋 Checklist de Déploiement

### **Avant le Déploiement**
- [ ] `APP_URL` configuré correctement
- [ ] Permissions de stockage correctes
- [ ] Lien symbolique créé
- [ ] Cache vidé

### **Après le Déploiement**
- [ ] Test de la route `/signatures/user-signature`
- [ ] Vérification des fichiers de signature
- [ ] Test de chargement dans l'interface
- [ ] Vérification des logs d'erreur

## 🔍 Debug en Production

### **Activer les Logs Temporairement**
```php
// Dans SignatureController.php (temporaire)
\Log::info('Signature URL Debug', [
    'base_url' => config('app.url'),
    'signature_path' => $user->signature_path,
    'full_url' => $signatureUrl,
    'file_exists' => Storage::disk('public')->exists($user->signature_path)
]);
```

### **Test de Connectivité**
```bash
# Tester l'accès aux fichiers
curl -I https://votre-domaine.com/storage/signatures/signature_1.png
```

## ⚠️ Problèmes Courants

### **1. HTTPS vs HTTP**
- Vérifier que `APP_URL` utilise le bon protocole
- S'assurer que les certificats SSL sont valides

### **2. Chemin de Stockage**
- Vérifier que le chemin de stockage est correct
- S'assurer que les fichiers sont dans le bon répertoire

### **3. Permissions**
- Vérifier que le serveur web peut lire les fichiers
- S'assurer que les permissions sont correctes

### **4. Cache du Navigateur**
- Vider le cache du navigateur
- Tester en navigation privée

## 🎯 Solution Finale

Le problème principal était que le code forçait l'URL à `localhost:8000` même sur le serveur de production. Maintenant, l'application utilise `config('app.url')` qui s'adapte automatiquement à l'environnement.

**Vérifiez sur votre serveur :**
1. Que `APP_URL` est configuré correctement dans `.env`
2. Que les permissions de stockage sont correctes
3. Que le lien symbolique `public/storage` existe
4. Que les fichiers de signature sont accessibles via l'URL complète
