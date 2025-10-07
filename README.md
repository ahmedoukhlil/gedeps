# Module de Gestion de Documents avec Signature PDF

## Description

Ce module Laravel complet permet de gérer un workflow de documents avec signature PDF. Il inclut l'upload de documents, l'approbation par la direction, la signature électronique et l'historique complet.

## Fonctionnalités principales

### 🔐 Gestion des rôles et permissions
- **Agent** : Upload et consultation de ses propres documents
- **DG (Directeur Général)** : Gestion complète des documents (approbation, signature, refus)
- **DAF (Directeur Administratif et Financier)** : Même niveau d'accès que le DG

### 📄 Gestion des documents
- Upload sécurisé (PDF, PNG, JPG, JPEG)
- Validation des fichiers (taille, type MIME)
- Stockage organisé par type et date
- Workflow d'approbation complet

### ✍️ Signature électronique
- Signature par image PNG/JPEG
- Injection automatique dans les PDF
- Positionnement configurable
- Support des documents image (conversion en PDF)

### 📊 Interface utilisateur
- Composants Livewire réactifs
- Design TailwindCSS moderne
- Modals pour les actions
- Pagination et filtres

## Installation

Suivez les instructions détaillées dans le fichier `INSTALLATION.md`.

### Commandes rapides

```bash
# Installation des dépendances
composer require spatie/laravel-permission setasign/fpdi intervention/image livewire/livewire
npm install @tailwindcss/forms alpinejs

# Configuration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan storage:link

# Migration et permissions
php artisan migrate
php artisan db:seed --class=DocumentPermissionSeeder

# Compilation des assets
npm run build
```

## Structure du projet

```
app/
├── Http/
│   ├── Controllers/DocumentController.php
│   └── Livewire/
│       ├── UploadDocument.php
│       ├── DocumentApproval.php
│       └── DocumentList.php
├── Models/
│   ├── Document.php
│   ├── DocumentSignature.php
│   └── User.php (modifié)
├── Services/
│   └── PdfSigningService.php
├── Policies/
│   └── DocumentPolicy.php
├── Events/
│   ├── DocumentUploaded.php
│   ├── DocumentSigned.php
│   └── DocumentRefused.php
├── Notifications/
│   ├── DocumentSignedNotification.php
│   └── DocumentRefusedNotification.php
└── Listeners/
    ├── SendDocumentSignedNotification.php
    └── SendDocumentRefusedNotification.php

resources/views/
├── layouts/app.blade.php
├── documents/
│   ├── upload.blade.php
│   ├── pending.blade.php
│   └── history.blade.php
└── livewire/
    ├── upload-document.blade.php
    ├── document-approval.blade.php
    └── document-list.blade.php

database/
├── migrations/
│   ├── create_documents_table.php
│   └── create_document_signatures_table.php
└── seeders/
    └── DocumentPermissionSeeder.php
```

## Utilisation

### Routes disponibles

- `GET /documents/upload` - Page d'upload (Agent)
- `GET /documents/pending` - Page d'approbation (DG/DAF)
- `GET /documents/history` - Historique des documents (tous)
- `GET /documents/download/{document}` - Téléchargement sécurisé

### Composants Livewire

```blade
<!-- Dans vos vues -->
<livewire:upload-document />
<livewire:document-approval />
<livewire:document-list />
```

### Configuration

Personnalisez la position de signature dans `PdfSigningService` :

```php
$options = [
    'x' => 100,        // Position X (pixels)
    'y' => 100,        // Position Y (pixels)
    'width' => 150,    // Largeur de la signature
    'height' => 75,    // Hauteur de la signature
    'page' => -1,      // Page (-1 = dernière page)
];
```

## Sécurité

- Vérification des permissions sur toutes les actions
- Validation stricte des fichiers uploadés
- Stockage sécurisé avec liens symboliques
- Enregistrement des métadonnées de signature (IP, User-Agent)
- Protection CSRF sur tous les formulaires

## Tests

```bash
# Tests unitaires
php artisan test tests/Unit/Services/PdfSigningServiceTest.php

# Tests de workflow
php artisan test tests/Feature/DocumentWorkflowTest.php
```

## Personnalisation

### Types de documents

Modifiez `Document::TYPES` dans le modèle pour ajouter de nouveaux types.

### Position de signature

Ajustez les options par défaut dans `PdfSigningService::signPdf()`.

### Intégration externe

Le service inclut un placeholder pour intégrer DocuSign, Yousign ou d'autres prestataires.

## Support

Pour toute question ou problème, consultez la documentation Laravel et les packages utilisés :

- [Laravel Livewire](https://laravel-livewire.com/)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [FPDI](https://www.setasign.com/products/fpdi/downloads/)
- [Intervention Image](https://image.intervention.io/)

## Licence

Ce module est fourni sous licence MIT.
