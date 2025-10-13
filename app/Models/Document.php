<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_name',
        'type',
        'description',
        'path_original',
        'filename_original',
        'file_size',
        'mime_type',
        'comment_agent',
        'uploaded_by',
        'signer_id',
        'status',
        'signature_queue',
        'current_signature_index',
        'completed_signatures',
        'sequential_signatures',
        'last_signature_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signature_queue' => 'array',
        'completed_signatures' => 'array',
        'sequential_signatures' => 'boolean',
        'last_signature_at' => 'datetime',
    ];

    /**
     * Statuts possibles du document
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SIGNED = 'signed';
    const STATUS_PARAPHED = 'paraphed';
    const STATUS_SIGNED_AND_PARAPHED = 'signed_and_paraphed';
    const STATUS_REFUSED = 'refused';

    /**
     * Types de documents disponibles
     */
    const TYPES = [
        'contrat' => 'Contrat',
        'facture' => 'Facture',
        'devis' => 'Devis',
        'bon_commande' => 'Bon de commande',
        'rapport' => 'Rapport',
        'autre' => 'Autre',
    ];

    /**
     * Relation avec l'utilisateur qui a uploadé le document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relation avec l'utilisateur qui doit signer le document
     */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    /**
     * Relation avec les signatures du document
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class);
    }

    /**
     * Relation avec les paraphes du document
     */
    public function paraphes(): HasMany
    {
        return $this->hasMany(DocumentParaphe::class);
    }

    /**
     * Relation avec les cachets du document
     */
    public function cachets(): HasMany
    {
        return $this->hasMany(DocumentCachet::class);
    }

    /**
     * Obtenir la dernière signature du document
     */
    public function latestSignature(): HasMany
    {
        return $this->signatures()->latest();
    }

    /**
     * Vérifier si le document est signé
     */
    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED || $this->status === self::STATUS_SIGNED_AND_PARAPHED;
    }

    /**
     * Vérifier si le document est paraphé
     */
    public function isParaphed(): bool
    {
        return $this->status === self::STATUS_PARAPHED || $this->status === self::STATUS_SIGNED_AND_PARAPHED;
    }

    /**
     * Vérifier si le document est complètement traité (signé et paraphé)
     */
    public function isFullyProcessed(): bool
    {
        return $this->status === self::STATUS_SIGNED_AND_PARAPHED;
    }

    /**
     * Vérifier si le document est en attente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si le document est refusé
     */
    public function isRefused(): bool
    {
        return $this->status === self::STATUS_REFUSED;
    }

    /**
     * Obtenir l'URL publique du fichier original
     */
    public function getOriginalFileUrlAttribute(): string
    {
        return Storage::url($this->path_original);
    }

    /**
     * Obtenir l'URL publique du PDF signé (s'il existe)
     */
    public function getSignedFileUrlAttribute(): ?string
    {
        $latestSignature = $this->latestSignature()->first();
        if ($latestSignature && $latestSignature->path_signed_pdf) {
            return Storage::url($latestSignature->path_signed_pdf);
        }
        return null;
    }

    /**
     * Obtenir le nom du type de document
     */
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Obtenir la classe CSS pour le badge de statut
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_SIGNED => 'bg-green-100 text-green-800',
            self::STATUS_PARAPHED => 'bg-purple-100 text-purple-800',
            self::STATUS_SIGNED_AND_PARAPHED => 'bg-emerald-100 text-emerald-800',
            self::STATUS_REFUSED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_SIGNED => 'Signé',
            self::STATUS_PARAPHED => 'Paraphé',
            self::STATUS_SIGNED_AND_PARAPHED => 'Signé & Paraphé',
            self::STATUS_REFUSED => 'Refusé',
            default => 'Inconnu',
        };
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les documents d'un utilisateur
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Scope pour les documents en attente d'approbation
     */
    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Relation avec les signatures séquentielles
     */
    public function sequentialSignatures()
    {
        return $this->hasMany(SequentialSignature::class)->orderBy('signature_order');
    }

    /**
     * Obtenir le signataire actuel
     */
    public function getCurrentSigner()
    {
        return $this->sequentialSignatures()
            ->where('signature_order', $this->current_signature_index + 1)
            ->first();
    }

    /**
     * Obtenir le prochain signataire
     */
    public function getNextSigner()
    {
        return $this->sequentialSignatures()
            ->where('signature_order', $this->current_signature_index + 2)
            ->first();
    }

    /**
     * Vérifier si c'est le tour de l'utilisateur de signer
     */
    public function isUserTurnToSign(int $userId): bool
    {
        $currentSigner = $this->getCurrentSigner();
        return $currentSigner && $currentSigner->user_id === $userId;
    }

    /**
     * Passer au signataire suivant
     */
    public function moveToNextSigner(): bool
    {
        $this->current_signature_index++;
        $this->last_signature_at = now();
        
        // Vérifier si tous les signataires ont signé
        $totalSigners = $this->sequentialSignatures()->count();
        if ($this->current_signature_index >= $totalSigners) {
            $this->status = self::STATUS_SIGNED;
            $this->sequential_signatures = false;
        }
        
        return $this->save();
    }

    /**
     * Obtenir le pourcentage de progression des signatures
     */
    public function getSignatureProgress(): int
    {
        $totalSigners = $this->sequentialSignatures()->count();
        if ($totalSigners === 0) return 0;
        
        return round(($this->current_signature_index / $totalSigners) * 100);
    }

    /**
     * Obtenir les signatures complétées
     */
    public function getCompletedSignatures()
    {
        return $this->sequentialSignatures()->completed()->get();
    }

    /**
     * Obtenir les signatures en attente
     */
    public function getPendingSignatures()
    {
        return $this->sequentialSignatures()->pending()->get();
    }
}
