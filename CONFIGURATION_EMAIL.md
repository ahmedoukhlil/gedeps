# Configuration des Notifications par Email

Ce document explique comment configurer les notifications par email dans GEDEPS.

## Problème résolu

Les notifications par email ne fonctionnaient pas en raison de deux problèmes principaux :

1. **Authentification Gmail invalide** - Le mot de passe simple n'était pas accepté par Gmail
2. **Événements jamais déclenchés** - Les événements `DocumentSigned` et `DocumentRefused` n'étaient pas déclenchés après la signature

## Configuration Gmail (OBLIGATOIRE)

### Étape 1 : Activer la validation en 2 étapes

1. Connectez-vous au compte Gmail : `epsmr.noreply@gmail.com`
2. Allez sur https://myaccount.google.com/security
3. Activez la "Validation en deux étapes"

### Étape 2 : Générer un mot de passe d'application

1. Allez sur https://myaccount.google.com/apppasswords
2. Sélectionnez "Autre (nom personnalisé)"
3. Entrez "GEDEPS" comme nom
4. Cliquez sur "Générer"
5. **Copiez le mot de passe de 16 caractères** (exemple: `abcdEfghIjklMnop`)

### Étape 3 : Mettre à jour le fichier .env

Modifiez le fichier `.env` à la racine du projet :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=epsmr.noreply@gmail.com
MAIL_PASSWORD=votre_mot_de_passe_dapplication_16_caracteres
MAIL_FROM_ADDRESS="epsmr.noreply@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**⚠️ IMPORTANT** : Remplacez `votre_mot_de_passe_dapplication_16_caracteres` par le mot de passe généré à l'étape 2.

### Étape 4 : Redémarrer la configuration

Après avoir modifié le `.env`, exécutez :

```bash
php artisan config:clear
php artisan cache:clear
```

## Démarrer le Queue Worker (OBLIGATOIRE)

Les notifications utilisent le système de queue de Laravel. Vous DEVEZ démarrer le queue worker pour que les emails soient envoyés.

### En développement

Ouvrez un terminal et exécutez :

```bash
php artisan queue:work --queue=default --tries=3 --timeout=90
```

Laissez cette commande tourner en arrière-plan pendant que vous utilisez l'application.

### En production (avec Supervisor)

Créez un fichier de configuration Supervisor `/etc/supervisor/conf.d/gedeps-worker.conf` :

```ini
[program:gedeps-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/gedeps/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/gedeps/storage/logs/worker.log
stopwaitsecs=3600
```

Puis rechargez Supervisor :

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gedeps-worker:*
```

### Alternative : Cron Job (moins recommandé)

Ajoutez dans votre crontab :

```bash
* * * * * cd /path/to/gedeps && php artisan queue:work --stop-when-empty
```

## Vérification du fonctionnement

### 1. Tester l'envoi d'email manuellement

Créez un fichier de test `test-email.php` à la racine :

```php
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

Mail::raw('Test email depuis GEDEPS', function ($message) {
    $message->to('votre.email@example.com')
            ->subject('Test GEDEPS');
});

echo "Email envoyé !\n";
```

Exécutez :

```bash
php test-email.php
```

### 2. Vérifier les jobs dans la queue

```bash
php artisan queue:work --once
```

### 3. Consulter les logs

Les logs sont dans `storage/logs/laravel.log`. Recherchez :

```
Événement DocumentSigned déclenché
Notification de signature mise en queue
```

### 4. Tester une signature

1. Uploadez un document
2. Signez-le
3. Vérifiez que l'agent reçoit un email de notification
4. Consultez les logs pour vérifier le déclenchement de l'événement

## Types de notifications

### DocumentSigned

Envoyée à l'**agent qui a uploadé le document** lorsqu'un document est signé.

**Déclenchée dans** :
- `DocumentProcessController::uploadSignedPdf()` - ligne 473
- `SignatureController::sign()` - ligne 135
- `SignatureController::saveSignedPdf()` - ligne 288

### DocumentRefused

Envoyée à l'**agent qui a uploadé le document** lorsqu'un document est refusé.

**À implémenter** : Actuellement, il n'y a pas de fonctionnalité de refus dans l'application.

## Architecture des notifications

### Système utilisé : Events & Listeners

1. **Event** : `App\Events\DocumentSigned`
2. **Listener** : `App\Listeners\SendDocumentSignedNotification`
3. **Notification** : `App\Notifications\DocumentSignedNotification`

### Flux d'exécution

```
Signature du document
    ↓
DocumentSigned::dispatch($document, $signature)
    ↓
SendDocumentSignedNotification (listener)
    ↓
$uploader->notify(new DocumentSignedNotification($document))
    ↓
Queue: Job ajouté à la table jobs
    ↓
Queue Worker: Traite le job
    ↓
Email envoyé via SMTP Gmail
```

## Dépannage

### Erreur "Failed to authenticate on SMTP server"

**Cause** : Mot de passe Gmail invalide

**Solution** : Suivez les étapes 1-3 de la configuration Gmail ci-dessus

### Erreur "Expected response code 235 but got code 535"

**Cause** : Authentification Gmail refusée (mauvais mot de passe d'application)

**Solution** : Regénérez un nouveau mot de passe d'application

### Les emails ne sont pas envoyés

**Cause probable** : Queue worker non démarré

**Solution** : Démarrez le queue worker avec `php artisan queue:work`

**Vérification** :
```bash
# Vérifier les jobs en attente
php artisan queue:work --once

# Vérifier les jobs échoués
php artisan queue:failed
```

### Erreur "MAIL_ENCRYPTION not set"

**Cause** : Configuration manquante dans .env

**Solution** : Ajoutez `MAIL_ENCRYPTION=tls` dans le fichier .env

### Les événements ne sont pas déclenchés

**Vérification** : Consultez `storage/logs/laravel.log` et cherchez :

```
Événement DocumentSigned déclenché
```

Si absent, l'événement n'est pas déclenché. Vérifiez que le code suivant est présent après la création de la signature :

```php
DocumentSigned::dispatch($document, $signature);
```

## Maintenance

### Nettoyer les jobs échoués

```bash
# Lister les jobs échoués
php artisan queue:failed

# Réessayer tous les jobs échoués
php artisan queue:retry all

# Supprimer tous les jobs échoués
php artisan queue:flush
```

### Surveiller les logs

```bash
# Surveiller en temps réel
tail -f storage/logs/laravel.log | grep -i "notification\|email\|mail"
```

### Statistiques de la queue

```bash
# Voir le nombre de jobs en attente
php artisan queue:work --once --verbose
```

## Configuration alternative : Mailtrap (Développement)

Pour tester les emails sans envoyer de vrais emails, utilisez Mailtrap :

1. Créez un compte sur https://mailtrap.io
2. Obtenez vos identifiants SMTP
3. Modifiez le `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username_mailtrap
MAIL_PASSWORD=votre_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="epsmr.noreply@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Tous les emails seront capturés dans Mailtrap au lieu d'être envoyés.

## Support

En cas de problème persistant :

1. Vérifiez `storage/logs/laravel.log`
2. Vérifiez que le queue worker tourne : `ps aux | grep queue:work`
3. Testez la connexion SMTP manuellement
4. Vérifiez les événements dans les logs

---

**Dernière mise à jour** : 16 octobre 2025
**Version** : 1.0
