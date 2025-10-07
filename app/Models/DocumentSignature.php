<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentSignature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'signed_by',
        'path_signature',
        'path_signed_pdf',
        'signature_comment',
        'signed_at',
        'ip_address',
        'user_agent',
        'signature_type',
        'live_signature_data',
        'signature_positions',
        'page_number',
        'is_multi_page',
        'total_pages',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signature_positions' => 'array',
        'is_multi_page' => 'boolean',
    ];

    /**
     * Relation avec le document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Relation avec l'utilisateur qui a signé
     */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Obtenir l'URL publique de l'image de signature
     */
    public function getSignatureImageUrlAttribute(): ?string
    {
        if ($this->path_signature) {
            return Storage::url($this->path_signature);
        }
        return null;
    }

    /**
     * Obtenir l'URL publique du PDF signé
     */
    public function getSignedPdfUrlAttribute(): ?string
    {
        if ($this->path_signed_pdf) {
            return Storage::url($this->path_signed_pdf);
        }
        return null;
    }

    /**
     * Obtenir le nombre de pages signées
     */
    public function getSignedPagesCountAttribute(): int
    {
        if (!$this->is_multi_page || !$this->signature_positions) {
            return $this->page_number ? 1 : 0;
        }
        
        return count(array_filter($this->signature_positions, function($position) {
            return $position !== null;
        }));
    }
    
    /**
     * Vérifier si toutes les pages sont signées
     */
    public function isAllPagesSigned(): bool
    {
        if (!$this->is_multi_page) {
            return $this->page_number !== null;
        }
        
        return $this->getSignedPagesCountAttribute() === $this->total_pages;
    }
    
    /**
     * Obtenir les pages signées
     */
    public function getSignedPagesAttribute(): array
    {
        if (!$this->is_multi_page || !$this->signature_positions) {
            return $this->page_number ? [$this->page_number] : [];
        }
        
        $signedPages = [];
        foreach ($this->signature_positions as $pageIndex => $position) {
            if ($position !== null) {
                $signedPages[] = $pageIndex + 1; // Convertir en numérotation 1-based
            }
        }
        
        return $signedPages;
    }
    
    /**
     * Vérifier si la signature est complète (avec PDF signé)
     */
    public function isComplete(): bool
    {
        return !is_null($this->signed_at) && !is_null($this->path_signed_pdf);
    }

    /**
     * Vérifier si c'est un refus (pas de signature mais avec commentaire)
     */
    public function isRefusal(): bool
    {
        return is_null($this->signed_at) && !is_null($this->signature_comment);
    }

    /**
     * Obtenir le type d'action (signature ou refus)
     */
    public function getActionTypeAttribute(): string
    {
        if ($this->isComplete()) {
            return 'signature';
        } elseif ($this->isRefusal()) {
            return 'refus';
        }
        return 'en_cours';
    }

    /**
     * Scope pour les signatures complètes
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('signed_at')->whereNotNull('path_signed_pdf');
    }

    /**
     * Scope pour les refus
     */
    public function scopeRefusals($query)
    {
        return $query->whereNull('signed_at')->whereNotNull('signature_comment');
    }

    /**
     * Scope pour les signatures d'un utilisateur
     */
    public function scopeBySigner($query, int $userId)
    {
        return $query->where('signed_by', $userId);
    }

    /**
     * Boot method pour enregistrer automatiquement les métadonnées
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($signature) {
            if (request()) {
                $signature->ip_address = request()->ip();
                $signature->user_agent = request()->userAgent();
            }
        });
    }
}
