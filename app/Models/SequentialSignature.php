<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequentialSignature extends Model
{
    protected $fillable = [
        'document_id',
        'user_id', 
        'signature_order',
        'status',
        'signed_at',
        'signature_data',
        'notes',
        'paraphed_at',
        'paraphe_data',
        'paraphe_comment'
    ];

    protected $casts = [
        'signature_data' => 'array',
        'signed_at' => 'datetime',
        'paraphe_data' => 'array',
        'paraphed_at' => 'datetime'
    ];

    /**
     * Relation avec le document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Relation avec l'utilisateur signataire
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si c'est le tour de ce signataire
     */
    public function isCurrentTurn(): bool
    {
        $document = $this->document;
        return $document && $document->current_signature_index === $this->signature_order - 1;
    }

    /**
     * Marquer comme signé
     */
    public function markAsSigned(array $signatureData = [], string $notes = null): void
    {
        $this->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signature_data' => $signatureData,
            'notes' => $notes
        ]);
    }

    /**
     * Marquer comme ignoré
     */
    public function markAsSkipped(string $notes = null): void
    {
        $this->update([
            'status' => 'skipped',
            'notes' => $notes
        ]);
    }

    /**
     * Marquer comme paraphé
     */
    public function markAsParaphed(array $parapheData = [], string $parapheComment = null): void
    {
        $this->update([
            'status' => 'paraphed',
            'paraphed_at' => now(),
            'paraphe_data' => $parapheData,
            'paraphe_comment' => $parapheComment
        ]);
    }

    /**
     * Marquer comme signé ET paraphé
     */
    public function markAsSignedAndParaphed(array $signatureData = [], array $parapheData = [], string $notes = null, string $parapheComment = null): void
    {
        $this->update([
            'status' => 'signed_and_paraphed',
            'signed_at' => now(),
            'paraphed_at' => now(),
            'signature_data' => $signatureData,
            'paraphe_data' => $parapheData,
            'notes' => $notes,
            'paraphe_comment' => $parapheComment
        ]);
    }

    /**
     * Scope pour les signatures en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les signatures complétées
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['signed', 'skipped', 'paraphed', 'signed_and_paraphed']);
    }

    /**
     * Scope pour les paraphes
     */
    public function scopeParaphed($query)
    {
        return $query->whereIn('status', ['paraphed', 'signed_and_paraphed']);
    }

    /**
     * Scope pour les signatures et paraphes combinés
     */
    public function scopeSignedAndParaphed($query)
    {
        return $query->where('status', 'signed_and_paraphed');
    }
}
