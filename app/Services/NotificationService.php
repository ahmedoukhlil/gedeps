<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Mail\DocumentAssignedNotification;
use App\Mail\DocumentSignedNotification;
use App\Mail\DocumentParaphedNotification;
use App\Mail\SequentialSignatureNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Envoyer une notification d'assignation de document
     */
    public function notifyDocumentAssigned(Document $document, User $signer, User $agent): bool
    {
        try {
            Mail::to($signer->email)->send(new DocumentAssignedNotification($document, $signer, $agent));
            
            Log::info('Notification d\'assignation envoyée', [
                'document_id' => $document->id,
                'signer_email' => $signer->email,
                'agent_name' => $agent->name
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification d\'assignation', [
                'document_id' => $document->id,
                'signer_email' => $signer->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Envoyer une notification de signature de document (asynchrone)
     */
    public function notifyDocumentSigned(Document $document, User $signer, User $agent): bool
    {
        try {
            // Utiliser la queue pour l'envoi asynchrone
            Mail::to($agent->email)->queue(new DocumentSignedNotification($document, $signer, $agent));
            
            Log::info('Notification de signature mise en queue', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'signer_name' => $signer->name
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise en queue de la notification de signature', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Envoyer une notification de paraphe de document (asynchrone)
     */
    public function notifyDocumentParaphed(Document $document, User $signer, User $agent): bool
    {
        try {
            // Utiliser la queue pour l'envoi asynchrone
            Mail::to($agent->email)->queue(new DocumentParaphedNotification($document, $signer, $agent));
            
            Log::info('Notification de paraphe mise en queue', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'signer_name' => $signer->name
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise en queue de la notification de paraphe', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Envoyer une notification de traitement complet (signé et paraphé) (asynchrone)
     */
    public function notifyDocumentFullyProcessed(Document $document, User $signer, User $agent): bool
    {
        try {
            // Utiliser la queue pour l'envoi asynchrone
            Mail::to($agent->email)->queue(new DocumentSignedNotification($document, $signer, $agent));
            
            Log::info('Notification de traitement complet mise en queue', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'signer_name' => $signer->name,
                'status' => $document->status
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise en queue de la notification de traitement complet', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Notifier le prochain signataire dans une signature séquentielle
     */
    public function notifyNextSequentialSigner(Document $document, User $nextSigner, User $previousSigner): bool
    {
        try {
            Mail::to($nextSigner->email)->send(new SequentialSignatureNotification(
                $document, 
                $nextSigner, 
                $previousSigner, 
                false
            ));
            
            Log::info('Notification séquentielle envoyée au prochain signataire', [
                'document_id' => $document->id,
                'next_signer_email' => $nextSigner->email,
                'previous_signer_name' => $previousSigner->name,
                'progress' => $document->getSignatureProgress()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification séquentielle', [
                'document_id' => $document->id,
                'next_signer_email' => $nextSigner->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Notifier la finalisation d'une signature séquentielle
     */
    public function notifySequentialSignatureCompleted(Document $document, User $agent): bool
    {
        try {
            $completedSignatures = $document->getCompletedSignatures();
            $totalSigners = count($document->signature_queue ?? []);
            
            Mail::to($agent->email)->queue(new SequentialSignatureNotification(
                $document, 
                $agent, 
                null, 
                true
            ));
            
            Log::info('Notification de finalisation séquentielle envoyée', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'total_signers' => $totalSigners,
                'completed_count' => count($completedSignatures)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification de finalisation séquentielle', [
                'document_id' => $document->id,
                'agent_email' => $agent->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Notifier qu'un signataire a ignoré sa signature
     */
    public function notifySignatureSkipped(Document $document, User $skippedSigner, User $nextSigner = null): bool
    {
        try {
            if ($nextSigner) {
                // Notifier le prochain signataire avec un email spécial
                Mail::to($nextSigner->email)->send(new SequentialSignatureNotification(
                    $document, 
                    $nextSigner, 
                    $skippedSigner, 
                    false
                ));
            }
            
            // Notifier l'agent que la signature a été ignorée
            $agent = User::find($document->uploaded_by);
            if ($agent) {
                Mail::to($agent->email)->queue(new SequentialSignatureNotification(
                    $document, 
                    $agent, 
                    $skippedSigner, 
                    false
                ));
            }
            
            Log::info('Notification d\'ignorance de signature envoyée', [
                'document_id' => $document->id,
                'skipped_signer_email' => $skippedSigner->email,
                'next_signer_email' => $nextSigner ? $nextSigner->email : null
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification d\'ignorance', [
                'document_id' => $document->id,
                'skipped_signer_email' => $skippedSigner->email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
