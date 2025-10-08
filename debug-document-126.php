<?php

// Script de debug pour le document 126

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Storage;

echo "=== Debug Document 126 ===\n";

$document = Document::find(126);
if (!$document) {
    echo "Document 126 non trouvé\n";
    exit;
}

echo "Document ID: {$document->id}\n";
echo "Nom: {$document->document_name}\n";
echo "Statut: {$document->status}\n";
echo "Path original: {$document->path_original}\n";
echo "Sequential signatures: " . ($document->sequential_signatures ? 'OUI' : 'NON') . "\n";

// Chercher les signatures
$signatures = DocumentSignature::where('document_id', 126)->get();
echo "\nSignatures trouvées: " . $signatures->count() . "\n";

foreach ($signatures as $signature) {
    echo "\nSignature ID: {$signature->id}\n";
    echo "Signed by: {$signature->signed_by}\n";
    echo "Path signed PDF: {$signature->path_signed_pdf}\n";
    echo "Signed at: {$signature->signed_at}\n";
    
    if ($signature->path_signed_pdf) {
        $exists = Storage::disk('public')->exists($signature->path_signed_pdf);
        echo "Fichier existe: " . ($exists ? 'OUI' : 'NON') . "\n";
        
        if ($exists) {
            $url = Storage::url($signature->path_signed_pdf);
            echo "URL générée: $url\n";
        }
    }
}

// Vérifier le fichier physique
$physicalFile = 'documents/signed/signed_126_1759888229.pdf';
$physicalExists = Storage::disk('public')->exists($physicalFile);
echo "\nFichier physique '{$physicalFile}' existe: " . ($physicalExists ? 'OUI' : 'NON') . "\n";

if ($physicalExists) {
    $physicalUrl = Storage::url($physicalFile);
    echo "URL physique: $physicalUrl\n";
}

echo "\n=== Debug terminé ===\n";
