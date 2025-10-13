<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfCachetService extends BasePdfService
{
    /**
     * Cacheter un document
     */
    public function cachetDocument(
        Document $document,
        User $cacheter,
        ?string $cachetComment = null,
        string $cachetType = 'png',
        ?string $liveCachetData = null,
        ?array $customPosition = null
    ): string {
        try {
            // Valider que c'est un PDF
            $this->validatePdfDocument($document);

            Log::info("PdfCachetService::cachetDocument - Début du cachet", [
                'document_id' => $document->id,
                'cacheter_id' => $cacheter->id,
                'cachet_type' => $cachetType,
                'has_custom_position' => !is_null($customPosition)
            ]);

            // Obtenir le chemin de l'image du cachet
            $cachetPath = $this->getCachetPath($cacheter, $cachetType, $liveCachetData);

            if (!$cachetPath) {
                throw new \Exception("Impossible de récupérer le cachet.");
            }

            // Pour l'instant, on copie juste le PDF original
            // TODO: Implémenter l'ajout réel du cachet avec une bibliothèque PDF
            $originalPdfPath = $this->getOriginalPdfPath($document);
            
            // Créer le nom du fichier cacheté
            $timestamp = time();
            $originalFilename = pathinfo($document->filename_original, PATHINFO_FILENAME);
            $cachetedFilename = "cacheted_{$timestamp}_{$originalFilename}.pdf";
            
            // Créer le répertoire s'il n'existe pas
            $cachetedDir = storage_path('app/public/documents/cacheted');
            if (!is_dir($cachetedDir)) {
                mkdir($cachetedDir, 0755, true);
            }

            $cachetedPdfPath = $cachetedDir . '/' . $cachetedFilename;
            
            // Copier le PDF (en attendant l'implémentation réelle)
            copy($originalPdfPath, $cachetedPdfPath);

            // Nettoyer le fichier temporaire si c'est un cachet live
            if ($cachetType === 'live' && file_exists($cachetPath)) {
                $this->cleanupTempFile($cachetPath);
            }

            $storedPath = 'documents/cacheted/' . $cachetedFilename;
            
            Log::info("PDF cacheté généré: {$storedPath}");
            
            return $storedPath;

        } catch (\Exception $e) {
            Log::error("Erreur lors du cachet du document: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtenir le chemin du cachet
     */
    protected function getCachetPath(User $cacheter, string $cachetType, ?string $liveCachetData): ?string
    {
        if ($cachetType === 'png') {
            if (!$cacheter->hasCachet()) {
                throw new \Exception('Le cacheteur n\'a pas de cachet PNG assigné.');
            }
            return Storage::disk('public')->path($cacheter->cachet_path);
        } elseif ($cachetType === 'live') {
            if (empty($liveCachetData)) {
                throw new \Exception('Les données de cachet live sont manquantes.');
            }
            return $this->createTempFile($liveCachetData, 'cachet_live');
        }
        return null;
    }

    /**
     * Générer un certificat de cachet
     */
    public function generateCachetCertificate(
        Document $document,
        User $cacheter,
        ?string $cachetComment = null
    ): string {
        try {
            Log::info("Génération du certificat de cachet", [
                'document_id' => $document->id,
                'cacheter_id' => $cacheter->id
            ]);

            // Créer le contenu du certificat
            $certificateContent = $this->buildCertificateContent($document, $cacheter, $cachetComment);

            // Créer un fichier PDF temporaire pour le certificat
            $tempPath = tempnam(sys_get_temp_dir(), 'cert_cachet_');
            
            // Pour l'instant, créer un fichier texte
            // TODO: Utiliser une bibliothèque PDF pour créer un vrai certificat
            file_put_contents($tempPath, $certificateContent);

            return $tempPath;

        } catch (\Exception $e) {
            Log::error("Erreur lors de la génération du certificat de cachet: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Construire le contenu du certificat
     */
    private function buildCertificateContent(Document $document, User $cacheter, ?string $cachetComment): string
    {
        $content = "CERTIFICAT DE CACHET\n\n";
        $content .= "Document: {$document->document_name}\n";
        $content .= "Cacheté par: {$cacheter->name}\n";
        $content .= "Date: " . now()->format('d/m/Y H:i:s') . "\n";
        
        if ($cachetComment) {
            $content .= "Commentaire: {$cachetComment}\n";
        }
        
        $content .= "\nCe certificat atteste que le document a été cacheté électroniquement.\n";
        
        return $content;
    }
}

