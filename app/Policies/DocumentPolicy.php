<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Déterminer si l'utilisateur peut voir tous les documents
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('documents.view') || 
               $user->hasPermissionTo('documents.view-own');
    }

    /**
     * Déterminer si l'utilisateur peut voir un document spécifique
     */
    public function view(User $user, Document $document): bool
    {
        // Les DG et DAF peuvent voir tous les documents
        if ($user->hasPermissionTo('documents.view')) {
            return true;
        }

        // Les agents ne peuvent voir que leurs propres documents
        if ($user->hasPermissionTo('documents.view-own')) {
            return $document->uploaded_by === $user->id;
        }

        return false;
    }

    /**
     * Déterminer si l'utilisateur peut créer un document
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('documents.upload');
    }

    /**
     * Déterminer si l'utilisateur peut modifier un document
     */
    public function update(User $user, Document $document): bool
    {
        // Seuls les DG et DAF peuvent modifier les documents
        if (!$user->hasPermissionTo('documents.approve')) {
            return false;
        }

        // On ne peut modifier que les documents en attente ou en cours
        return in_array($document->status, [Document::STATUS_PENDING, Document::STATUS_IN_PROGRESS]);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un document
     */
    public function delete(User $user, Document $document): bool
    {
        // Seuls les DG et DAF peuvent supprimer
        if (!$user->hasPermissionTo('documents.approve')) {
            return false;
        }

        // On ne peut supprimer que les documents refusés ou en attente
        return in_array($document->status, [Document::STATUS_PENDING, Document::STATUS_REFUSED]);
    }

    /**
     * Déterminer si l'utilisateur peut approuver un document
     */
    public function approve(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('documents.approve') && 
               $document->status === Document::STATUS_PENDING;
    }

    /**
     * Déterminer si l'utilisateur peut signer un document
     */
    public function sign(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('documents.sign') && 
               in_array($document->status, [Document::STATUS_PENDING, Document::STATUS_IN_PROGRESS]);
    }

    /**
     * Déterminer si l'utilisateur peut refuser un document
     */
    public function refuse(User $user, Document $document): bool
    {
        return $user->hasPermissionTo('documents.refuse') && 
               in_array($document->status, [Document::STATUS_PENDING, Document::STATUS_IN_PROGRESS]);
    }

    /**
     * Déterminer si l'utilisateur peut télécharger un document
     */
    public function download(User $user, Document $document): bool
    {
        // Les DG et DAF peuvent télécharger tous les documents
        if ($user->hasPermissionTo('documents.download') && $user->hasPermissionTo('documents.view')) {
            return true;
        }

        // Les agents ne peuvent télécharger que leurs propres documents
        if ($user->hasPermissionTo('documents.download') && $user->hasPermissionTo('documents.view-own')) {
            return $document->uploaded_by === $user->id;
        }

        return false;
    }

    /**
     * Déterminer si l'utilisateur peut voir l'historique
     */
    public function viewHistory(User $user): bool
    {
        return $user->hasPermissionTo('documents.history');
    }

    /**
     * Déterminer si l'utilisateur peut gérer les documents en attente
     */
    public function managePending(User $user): bool
    {
        return $user->hasPermissionTo('documents.approve') || 
               $user->hasPermissionTo('documents.sign') || 
               $user->hasPermissionTo('documents.refuse');
    }
}
