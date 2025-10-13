<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorage extends Command
{
    protected $signature = 'storage:fix';
    protected $description = 'Réparer les problèmes de stockage';

    public function handle()
    {
        $this->info('🔧 Réparation du stockage...');
        
        // Créer les répertoires manquants
        $directories = [
            'storage/app/public',
            'storage/app/public/documents',
            'storage/app/public/signatures',
            'storage/app/public/documents/signed',
            'storage/app/public/documents/cacheted'
        ];
        
        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (!is_dir($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
                $this->info("✅ Créé: {$dir}");
            } else {
                $this->info("✅ Existe déjà: {$dir}");
            }
        }
        
        // Supprimer et recréer le lien symbolique
        $linkPath = public_path('storage');
        if (is_link($linkPath) || is_dir($linkPath)) {
            if (is_link($linkPath)) {
                unlink($linkPath);
            } else {
                File::deleteDirectory($linkPath);
            }
            $this->info('🗑️  Ancien lien symbolique supprimé');
        }
        
        // Créer le nouveau lien symbolique
        $targetPath = storage_path('app/public');
        if (symlink($targetPath, $linkPath)) {
            $this->info('✅ Lien symbolique créé');
        } else {
            $this->error('❌ Impossible de créer le lien symbolique');
            $this->info('   Exécutez manuellement: ln -s ' . $targetPath . ' ' . $linkPath);
        }
        
        // Définir les permissions
        $this->info('🔧 Définition des permissions...');
        $storagePath = storage_path('app/public');
        chmod($storagePath, 0755);
        $this->info('✅ Permissions définies');
        
        $this->info('🏁 Réparation terminée');
        $this->info('   Vérifiez avec: php artisan storage:diagnose');
    }
}
