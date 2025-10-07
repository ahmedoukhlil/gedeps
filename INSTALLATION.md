# Module de Gestion de Documents avec Signature PDF

## Installation

### 1. Installation des dépendances Composer

```bash
# Packages requis pour le module
composer require spatie/laravel-permission
composer require setasign/fpdi
composer require intervention/image
composer require livewire/livewire

# Si pas déjà installé
composer require laravel/sanctum
```

### 2. Installation des dépendances NPM

```bash
npm install @tailwindcss/forms
npm install alpinejs
```

### 3. Configuration des packages

```bash
# Publier les fichiers de configuration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Intervention\Image\ImageServiceProvider"

# Créer le lien symbolique pour le storage
php artisan storage:link
```

### 4. Exécution des migrations

```bash
# Migrations du module
php artisan migrate

# Créer les rôles et permissions
php artisan db:seed --class=DocumentPermissionSeeder
```

### 5. Configuration TailwindCSS

Ajouter dans `tailwind.config.js` :

```javascript
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Http/Livewire/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

### 6. Variables d'environnement

Ajouter dans `.env` :

```env
# Configuration des tailles de fichiers (en MB)
MAX_DOCUMENT_SIZE=10
MAX_SIGNATURE_SIZE=2

# Configuration des chemins de stockage
DOCUMENTS_PATH=documents
SIGNATURES_PATH=signatures
ARCHIVES_PATH=archives
```

### 7. Compilation des assets

```bash
npm run dev
# ou pour la production
npm run build
```

## Utilisation

### Rôles et Permissions

Le module utilise 3 rôles principaux :
- **Agent** : peut uploader et consulter ses propres documents
- **DG** (Directeur Général) : peut approuver, signer et refuser tous les documents
- **DAF** (Directeur Administratif et Financier) : peut approuver, signer et refuser tous les documents

### Routes disponibles

- `GET /documents/upload` - Page d'upload (Agent)
- `GET /documents/pending` - Page d'approbation (DG/DAF)
- `GET /documents/history` - Historique des documents (tous)
- `GET /documents/download/{document}` - Téléchargement sécurisé

### Composants Livewire

```blade
<!-- Upload de document -->
<livewire:upload-document />

<!-- Approbation de documents -->
<livewire:document-approval />

<!-- Liste des documents -->
<livewire:document-list />
```

### Personnalisation de la position de signature

Dans `PdfSigningService`, vous pouvez personnaliser la position de la signature via les options :

```php
$options = [
    'x' => 100,        // Position X (pixels)
    'y' => 100,        // Position Y (pixels)
    'width' => 150,    // Largeur de la signature
    'height' => 75,    // Hauteur de la signature
    'page' => -1,      // Page (-1 = dernière page)
];
```

## Structure des fichiers

```
app/
├── Http/Livewire/
│   ├── UploadDocument.php
│   ├── DocumentApproval.php
│   └── DocumentList.php
├── Models/
│   ├── Document.php
│   └── DocumentSignature.php
├── Services/
│   └── PdfSigningService.php
├── Policies/
│   └── DocumentPolicy.php
└── Events/
    ├── DocumentUploaded.php
    ├── DocumentSigned.php
    └── DocumentRefused.php

resources/views/livewire/
├── upload-document.blade.php
├── document-approval.blade.php
└── document-list.blade.php

database/migrations/
├── create_documents_table.php
└── create_document_signatures_table.php
```
