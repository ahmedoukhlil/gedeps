# 🌐 Accès Réseau Local - GEDEPS

## 📋 Vue d'ensemble

Ce guide explique comment rendre l'application GEDEPS accessible sur le réseau local, permettant à d'autres appareils (ordinateurs, tablettes, smartphones) de se connecter à l'application.

## 🚀 Démarrage Rapide

### Option 1: Script Windows (Recommandé)
```bash
# Double-cliquez sur le fichier
start-network-server.bat
```

### Option 2: Script PowerShell
```powershell
# Dans PowerShell
.\start-network-server.ps1
```

### Option 3: Commande manuelle
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## 🔍 Obtenir votre adresse IP

### Méthode 1: Script PHP
```bash
php get-network-info.php
```

### Méthode 2: Commande Windows
```cmd
ipconfig
```

### Méthode 3: Commande PowerShell
```powershell
Get-NetIPAddress -AddressFamily IPv4
```

## 📱 URLs d'accès

Une fois le serveur démarré, l'application sera accessible via :

- **Local (même machine)** : `http://localhost:8000`
- **Réseau local** : `http://192.168.150.207:8000` (remplacez par votre IP)

## 🔧 Configuration du Firewall

### Windows Defender Firewall
1. Ouvrez "Pare-feu Windows Defender"
2. Cliquez sur "Paramètres avancés"
3. Sélectionnez "Règles de trafic entrant"
4. Cliquez sur "Nouvelle règle"
5. Choisissez "Port" → "TCP" → "8000"
6. Autorisez la connexion

### Alternative rapide
```cmd
# Exécuter en tant qu'administrateur
netsh advfirewall firewall add rule name="GEDEPS Laravel" dir=in action=allow protocol=TCP localport=8000
```

## 📱 Test d'accès

### Depuis un autre appareil sur le réseau :
1. Connectez-vous au même réseau Wi-Fi
2. Ouvrez un navigateur
3. Accédez à `http://192.168.150.207:8000`
4. L'application devrait s'afficher

### Comptes de test disponibles :
- **Admin** : `admin@gedeps.com` / `12345678`
- **Agent** : `ahmed.mohamed@gedeps.com` / `12345678`
- **Signataire** : `abdellahi.sidi@gedeps.com` / `12345678`

## 🛠️ Dépannage

### Problème : "Connexion refusée"
- Vérifiez que le serveur est démarré avec `--host=0.0.0.0`
- Vérifiez les paramètres du firewall
- Assurez-vous que les appareils sont sur le même réseau

### Problème : "Page non trouvée"
- Vérifiez l'adresse IP avec `php get-network-info.php`
- Vérifiez que le port 8000 est libre
- Redémarrez le serveur

### Problème : "Lent ou instable"
- Vérifiez la qualité de la connexion réseau
- Fermez les autres applications utilisant le réseau
- Redémarrez le routeur si nécessaire

## 🔒 Sécurité

⚠️ **Important** : En mode réseau local, l'application est accessible à tous les appareils du réseau.

### Recommandations :
- Utilisez uniquement sur des réseaux de confiance
- Ne laissez pas le serveur ouvert en permanence
- Changez les mots de passe par défaut en production
- Utilisez HTTPS en production

## 📊 Monitoring

### Vérifier les connexions actives
```bash
# Voir les connexions sur le port 8000
netstat -an | findstr :8000
```

### Logs du serveur
Les logs s'affichent dans le terminal où le serveur est démarré.

## 🎯 Cas d'usage

### Démonstration
- Présentation à des clients
- Formation d'équipe
- Tests multi-utilisateurs

### Développement
- Tests sur différents appareils
- Validation responsive design
- Tests de performance réseau

### Production temporaire
- Déploiement rapide pour tests
- Environnement de staging local
- Validation avant déploiement cloud

## 📞 Support

En cas de problème :
1. Vérifiez ce guide de dépannage
2. Consultez les logs du serveur
3. Testez d'abord en local (`localhost:8000`)
4. Vérifiez la configuration réseau

---

**GEDEPS** - Gestion Électronique de Documents avec Signature
