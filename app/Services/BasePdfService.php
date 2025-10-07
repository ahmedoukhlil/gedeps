<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

abstract class BasePdfService
{
    /**
     * Vérifier que le document est un PDF
     */
    protected function validatePdfDocument(Document $document): void
    {
        if ($document->mime_type !== 'application/pdf') {
            throw new \Exception('Seuls les documents PDF peuvent être traités.');
        }
    }

    /**
     * Obtenir le chemin du fichier PDF original
     */
    protected function getOriginalPdfPath(Document $document): string
    {
        return Storage::disk('public')->path($document->path_original);
    }

    /**
     * Créer un fichier temporaire pour les données live
     */
    protected function createTempFile(string $liveData, string $prefix = 'temp'): string
    {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $liveData));
        $tempFile = tempnam(sys_get_temp_dir(), $prefix . '_');
        file_put_contents($tempFile, $imageData);
        return $tempFile;
    }

    /**
     * Nettoyer un fichier temporaire
     */
    protected function cleanupTempFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Obtenir le chemin de la signature
     */
    protected function getSignaturePath(User $signer, string $signatureType, ?string $liveSignatureData): ?string
    {
        if ($signatureType === 'png') {
            if (!$signer->hasSignature()) {
                throw new \Exception('Le signataire n\'a pas de signature PNG assignée.');
            }
            return Storage::disk('public')->path($signer->signature_path);
        } elseif ($signatureType === 'live') {
            if (empty($liveSignatureData)) {
                throw new \Exception('Les données de signature live sont manquantes.');
            }
            return $this->createTempFile($liveSignatureData, 'signature_live');
        }
        return null;
    }

    /**
     * Obtenir le chemin du paraphe
     */
    protected function getParaphePath(User $signer, string $parapheType, ?string $liveParapheData): ?string
    {
        if ($parapheType === 'png') {
            if (!$signer->hasParaphe()) {
                throw new \Exception('Le signataire n\'a pas de paraphe PNG assigné.');
            }
            return Storage::disk('public')->path($signer->paraphe_path);
        } elseif ($parapheType === 'live') {
            if (empty($liveParapheData)) {
                throw new \Exception('Les données de paraphe live sont manquantes.');
            }
            return $this->createTempFile($liveParapheData, 'paraphe_live');
        }
        return null;
    }

    /**
     * Stocker un PDF généré
     */
    protected function storePdf(string $pdfPath, string $filename, string $directory = 'documents'): string
    {
        $storedPath = Storage::disk('public')->putFileAs($directory, $pdfPath, $filename);
        $this->cleanupTempFile($pdfPath);
        return $storedPath;
    }

    /**
     * Générer un nom de fichier unique
     */
    protected function generateFilename(string $prefix, string $originalFilename): string
    {
        return $prefix . '_' . time() . '_' . $originalFilename;
    }
}
