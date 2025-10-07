# Configuration des Notifications par Email

## 📧 Système de Notifications GEDEPS

Ce document explique comment configurer le système de notifications par email pour GEDEPS.

## 🚀 Fonctionnalités

- **Notification d'assignation** : L'agent assigne un document → Le signataire reçoit un email
- **Notification de signature** : Le signataire signe un document → L'agent reçoit un email
- **Notification de paraphe** : Le signataire paraphé un document → L'agent reçoit un email
- **Templates HTML** : Emails avec design moderne et responsive

## ⚙️ Configuration

### 1. Paramètres SMTP dans .env

```env
# Configuration SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="GEDEPS"

# Configuration des notifications
MAIL_NOTIFICATIONS_ENABLED=true
MAIL_QUEUE_ENABLED=false
MAIL_RETRY_ATTEMPTS=3
MAIL_RETRY_DELAY=60
```

### 2. Configuration Gmail (Recommandé)

1. **Activer l'authentification à 2 facteurs** sur votre compte Gmail
2. **Générer un mot de passe d'application** :
   - Aller dans Paramètres Google → Sécurité
   - Authentification à 2 facteurs → Mots de passe d'application
   - Générer un mot de passe pour "Mail"
3. **Utiliser ce mot de passe** dans `MAIL_PASSWORD`

### 3. Configuration Outlook/Hotmail

```env
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### 4. Configuration Yahoo

```env
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

## 📁 Structure des Fichiers

```
app/
├── Mail/
│   ├── DocumentAssignedNotification.php
│   ├── DocumentSignedNotification.php
│   └── DocumentParaphedNotification.php
├── Services/
│   └── NotificationService.php
resources/views/emails/
├── document-assigned.blade.php
├── document-signed.blade.php
└── document-paraphed.blade.php
config/
└── mail_notifications.php
```

## 🔄 Flux de Notifications

### 1. Assignation de Document
```
Agent upload document → NotificationService::notifyDocumentAssigned()
→ Email au Signataire
```

### 2. Signature de Document
```
Signataire signe document → NotificationService::notifyDocumentSigned()
→ Email à l'Agent
```

### 3. Paraphe de Document
```
Signataire paraphé document → NotificationService::notifyDocumentParaphed()
→ Email à l'Agent
```

## 🎨 Templates Email

Les templates sont responsives et incluent :
- **Header** avec logo GEDEPS
- **Informations du document** (nom, date, statut)
- **Boutons d'action** (liens vers l'application)
- **Footer** avec informations de contact

## 🐛 Dépannage

### Erreurs courantes

1. **"Connection could not be established"**
   - Vérifier les paramètres SMTP
   - Vérifier la connexion internet
   - Vérifier les credentials

2. **"Authentication failed"**
   - Vérifier le mot de passe d'application
   - Vérifier que l'authentification 2FA est activée

3. **"Emails not sent"**
   - Vérifier `MAIL_NOTIFICATIONS_ENABLED=true`
   - Vérifier les logs dans `storage/logs/laravel.log`

### Logs

Les notifications sont loggées dans :
- `storage/logs/laravel.log`
- Rechercher "Notification" pour voir les détails

## 🚀 Test des Notifications

1. **Créer un document** en tant qu'agent
2. **Vérifier l'email** du signataire assigné
3. **Signer le document** en tant que signataire
4. **Vérifier l'email** de l'agent

## 📊 Monitoring

Pour surveiller les notifications :
```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log | grep -i notification

# Voir les erreurs d'email
tail -f storage/logs/laravel.log | grep -i mail
```

## 🔧 Personnalisation

### Modifier les templates
Éditer les fichiers dans `resources/views/emails/`

### Modifier les sujets
Éditer les classes Mail dans `app/Mail/`

### Désactiver les notifications
```env
MAIL_NOTIFICATIONS_ENABLED=false
```

## 📞 Support

Pour toute question sur les notifications :
1. Vérifier les logs
2. Tester la configuration SMTP
3. Vérifier les permissions de l'application
