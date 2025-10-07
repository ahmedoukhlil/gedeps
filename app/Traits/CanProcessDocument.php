<?php

namespace App\Traits;

use App\Models\Document;

trait CanProcessDocument
{
    /**
     * Vérifier si l'utilisateur peut traiter le document
     */
    protected function canProcess(Document $document): bool
    {
        $user = auth()->user();
        
        // L'utilisateur peut traiter s'il est le signataire assigné
        if ($user->isSignataire() && $document->signer_id === $user->id) {
            return true;
        }
        
        // L'utilisateur peut traiter s'il a uploadé le document
        if ($document->uploaded_by === $user->id) {
            return true;
        }
        
        // L'admin peut traiter tous les documents
        if ($user->isAdmin()) {
            return true;
        }
        
        return false;
    }

    /**
     * Vérifier si l'utilisateur peut parapher le document
     */
    protected function canParaphe(Document $document): bool
    {
        // Vérifier d'abord les permissions de base
        if (!$this->canProcess($document)) {
            return false;
        }
        
        // Empêcher le paraphe si le document est déjà signé ou paraphé
        if ($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed()) {
            return false;
        }
        
        return true;
    }

    /**
     * Vérifier si l'utilisateur peut signer le document
     */
    protected function canSign(Document $document): bool
    {
        // Vérifier d'abord les permissions de base
        if (!$this->canProcess($document)) {
            return false;
        }
        
        // Empêcher la signature si le document est déjà signé ou paraphé
        if ($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed()) {
            return false;
        }
        
        return true;
    }
}
