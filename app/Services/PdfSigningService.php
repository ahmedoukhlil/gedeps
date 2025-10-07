<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfSigningService
{
    /**
     * Générer un PDF signé côté serveur
     */
    public function generateSignedPdf(Document $document, array $signatures = [], array $paraphes = [])
    {
        try {
            Log::info("PdfSigningService::generateSignedPdf - Début de génération", [
                'document_id' => $document->id,
                'signatures_count' => count($signatures),
                'paraphes_count' => count($paraphes)
            ]);

            // Charger le PDF original
            $originalPdfPath = Storage::disk('public')->path($document->path_original);
            
            if (!file_exists($originalPdfPath)) {
                throw new \Exception("PDF original non trouvé: {$originalPdfPath}");
            }

            // Créer le nom du fichier selon le type de traitement
            $timestamp = time();
            $originalFilename = pathinfo($document->filename_original, PATHINFO_FILENAME);
            
            // Déterminer le préfixe selon le statut du document
            $prefix = $this->getDocumentPrefix($document);
            $signedFilename = "{$prefix}_{$timestamp}_{$originalFilename}.pdf";
            $signedPath = "documents/signed/{$signedFilename}";

            // Créer le répertoire s'il n'existe pas
            $signedDir = storage_path('app/public/documents/signed');
            if (!is_dir($signedDir)) {
                mkdir($signedDir, 0755, true);
            }

            $signedPdfPath = $signedDir . '/' . $signedFilename;
            
            // Si des signatures/paraphes sont fournies, les ajouter au PDF
            if (!empty($signatures) || !empty($paraphes)) {
                Log::info("PdfSigningService::generateSignedPdf - Ajout des signatures/paraphes", [
                    'signatures' => $signatures,
                    'paraphes' => $paraphes
                ]);
                
                // Pour l'instant, copier le PDF original
                // TODO: Implémenter l'ajout réel des signatures/paraphes avec une bibliothèque PDF
                copy($originalPdfPath, $signedPdfPath);
                
                // Log des coordonnées pour debug
                foreach ($signatures as $index => $signature) {
                    Log::info("Signature {$index}:", [
                        'x' => $signature['x'],
                        'y' => $signature['y'],
                        'type' => $signature['type']
                    ]);
                }
                
                foreach ($paraphes as $index => $paraphe) {
                    Log::info("Paraphe {$index}:", [
                        'x' => $paraphe['x'],
                        'y' => $paraphe['y'],
                        'type' => $paraphe['type']
                    ]);
                }
            } else {
                // Pas de signatures/paraphes, copier le PDF original
                copy($originalPdfPath, $signedPdfPath);
            }

            Log::info("PDF signé généré: {$signedPath} dans {$signedDir}");
            
            return $signedPath;
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la génération du PDF signé: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtenir le préfixe du fichier selon le statut du document
     */
    private function getDocumentPrefix(Document $document)
    {
        switch ($document->status) {
            case 'signed':
                return 'signed';
            case 'paraphed':
                return 'paraphed';
            case 'signed_and_paraphed':
                return 'signed_paraphed';
            default:
                return 'processed';
        }
    }
}