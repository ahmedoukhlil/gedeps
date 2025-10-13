<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RepairMissingFiles extends Command
{
    protected $signature = 'storage:repair-missing';
    protected $description = 'Réparer les fichiers manquants';

    public function handle()
    {
        $this->info('🔧 Réparation des fichiers manquants...');
        
        $documents = Document::all();
        $repaired = 0;
        $missing = 0;
        
        foreach ($documents as $document) {
            $filePath = storage_path('app/public/' . $document->path_original);
            
            if (!file_exists($filePath)) {
                $missing++;
                $this->warn("❌ Fichier manquant: {$document->path_original}");
                
                // Essayer de trouver le fichier dans d'autres emplacements
                $found = $this->findFile($document);
                
                if ($found) {
                    $this->info("✅ Fichier trouvé et copié: {$found}");
                    $repaired++;
                } else {
                    $this->error("❌ Impossible de récupérer: {$document->filename_original}");
                    
                    // Créer un fichier de remplacement
                    $this->createReplacementFile($document);
                }
            } else {
                $this->info("✅ Fichier existe: {$document->path_original}");
            }
        }
        
        $this->info("🏁 Réparation terminée:");
        $this->info("   - Fichiers manquants: {$missing}");
        $this->info("   - Fichiers réparés: {$repaired}");
    }
    
    private function findFile(Document $document)
    {
        $possibleLocations = [
            storage_path('app/public/documents/' . $document->filename_original),
            storage_path('app/public/' . $document->filename_original),
            storage_path('app/documents/' . $document->filename_original),
            public_path('storage/documents/' . $document->filename_original),
            public_path('storage/' . $document->filename_original),
            // Chercher par nom de fichier
            storage_path('app/public/documents/' . basename($document->path_original)),
            storage_path('app/public/' . basename($document->path_original))
        ];
        
        foreach ($possibleLocations as $location) {
            if (file_exists($location)) {
                // Copier vers l'emplacement correct
                $targetPath = storage_path('app/public/' . $document->path_original);
                $targetDir = dirname($targetPath);
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                if (copy($location, $targetPath)) {
                    return $location;
                }
            }
        }
        
        return null;
    }
    
    private function createReplacementFile(Document $document)
    {
        $targetPath = storage_path('app/public/' . $document->path_original);
        $targetDir = dirname($targetPath);
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Créer un PDF de remplacement simple
        $replacementContent = $this->generateSimplePdf($document);
        file_put_contents($targetPath, $replacementContent);
        
        $this->info("📄 Fichier de remplacement créé: {$document->filename_original}");
    }
    
    private function generateSimplePdf(Document $document)
    {
        // Créer un PDF simple avec le nom du document
        $content = "%PDF-1.4\n";
        $content .= "1 0 obj\n";
        $content .= "<<\n";
        $content .= "/Type /Catalog\n";
        $content .= "/Pages 2 0 R\n";
        $content .= ">>\n";
        $content .= "endobj\n";
        $content .= "2 0 obj\n";
        $content .= "<<\n";
        $content .= "/Type /Pages\n";
        $content .= "/Kids [3 0 R]\n";
        $content .= "/Count 1\n";
        $content .= ">>\n";
        $content .= "endobj\n";
        $content .= "3 0 obj\n";
        $content .= "<<\n";
        $content .= "/Type /Page\n";
        $content .= "/Parent 2 0 R\n";
        $content .= "/MediaBox [0 0 612 792]\n";
        $content .= "/Contents 4 0 R\n";
        $content .= ">>\n";
        $content .= "endobj\n";
        $content .= "4 0 obj\n";
        $content .= "<<\n";
        $content .= "/Length 100\n";
        $content .= ">>\n";
        $content .= "stream\n";
        $content .= "BT\n";
        $content .= "/F1 12 Tf\n";
        $content .= "100 700 Td\n";
        $content .= "(Document: {$document->document_name}) Tj\n";
        $content .= "100 680 Td\n";
        $content .= "(Fichier original: {$document->filename_original}) Tj\n";
        $content .= "100 660 Td\n";
        $content .= "(Fichier de remplacement) Tj\n";
        $content .= "ET\n";
        $content .= "endstream\n";
        $content .= "endobj\n";
        $content .= "xref\n";
        $content .= "0 5\n";
        $content .= "0000000000 65535 f \n";
        $content .= "0000000009 00000 n \n";
        $content .= "0000000058 00000 n \n";
        $content .= "0000000115 00000 n \n";
        $content .= "0000000204 00000 n \n";
        $content .= "trailer\n";
        $content .= "<<\n";
        $content .= "/Size 5\n";
        $content .= "/Root 1 0 R\n";
        $content .= ">>\n";
        $content .= "startxref\n";
        $content .= "354\n";
        $content .= "%%EOF\n";
        
        return $content;
    }
}
