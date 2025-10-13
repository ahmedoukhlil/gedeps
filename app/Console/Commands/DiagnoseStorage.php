<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

class DiagnoseStorage extends Command
{
    protected $signature = 'storage:diagnose';
    protected $description = 'Diagnostiquer les problèmes de stockage des fichiers';

    public function handle()
    {
        $this->info('🔍 Diagnostic du stockage...');
        
        // Vérifier le lien symbolique
        $linkPath = public_path('storage');
        if (is_link($linkPath)) {
            $this->info('✅ Lien symbolique storage existe');
            $this->info('   Pointe vers: ' . readlink($linkPath));
        } else {
            $this->error('❌ Lien symbolique storage manquant');
            $this->info('   Exécutez: php artisan storage:link');
        }
        
        // Vérifier les répertoires
        $directories = [
            'storage/app/public',
            'storage/app/public/documents',
            'storage/app/public/signatures',
            'storage/app/public/documents/signed',
            'public/storage',
            'public/storage/documents'
        ];
        
        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (is_dir($fullPath)) {
                $this->info("✅ Répertoire existe: {$dir}");
                $files = glob($fullPath . '/*');
                $this->info("   Fichiers: " . count($files));
            } else {
                $this->warn("⚠️  Répertoire manquant: {$dir}");
            }
        }
        
        // Vérifier les documents avec des fichiers manquants
        $this->info('🔍 Vérification des documents...');
        $documents = Document::all();
        $missingFiles = 0;
        
        foreach ($documents as $document) {
            $filePath = storage_path('app/public/' . $document->path_original);
            if (!file_exists($filePath)) {
                $missingFiles++;
                $this->error("❌ Fichier manquant pour document {$document->id}: {$document->path_original}");
            }
        }
        
        if ($missingFiles === 0) {
            $this->info('✅ Tous les fichiers de documents sont présents');
        } else {
            $this->error("❌ {$missingFiles} fichiers de documents manquants");
        }
        
        // Vérifier les permissions
        $this->info('🔍 Vérification des permissions...');
        $storagePath = storage_path('app/public');
        if (is_writable($storagePath)) {
            $this->info('✅ Répertoire storage accessible en écriture');
        } else {
            $this->error('❌ Répertoire storage non accessible en écriture');
            $this->info('   Exécutez: chmod -R 755 storage/');
        }
        
        $this->info('🏁 Diagnostic terminé');
    }
}
