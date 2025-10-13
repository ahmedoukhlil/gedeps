<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentCachet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'cacheted_by',
        'cacheted_at',
        'cachet_comment',
        'path_cacheted_pdf',
        'path_cachet',
        'cachet_type',
        'live_cachet_data',
        'ip_address',
        'user_agent',
        'cachet_positions',
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
        'cacheted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cachet_positions' => 'array',
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
     * Relation avec l'utilisateur qui a cacheté
     */
    public function cacheter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cacheted_by');
    }

    /**
     * Obtenir l'URL publique de l'image de cachet
     */
    public function getCachetImageUrlAttribute(): ?string
    {
        if ($this->path_cachet) {
            return Storage::url($this->path_cachet);
        }
        return null;
    }

    /**
     * Obtenir l'URL publique du PDF cacheté
     */
    public function getCachetedPdfUrlAttribute(): ?string
    {
        if ($this->path_cacheted_pdf) {
            return Storage::url($this->path_cacheted_pdf);
        }
        return null;
    }

    /**
     * Obtenir le nombre de pages cachetées
     */
    public function getCachetedPagesCountAttribute(): int
    {
        if (!$this->is_multi_page || !$this->cachet_positions) {
            return $this->page_number ? 1 : 0;
        }
        
        return count(array_filter($this->cachet_positions, function($position) {
            return $position !== null;
        }));
    }
    
    /**
     * Vérifier si toutes les pages sont cachetées
     */
    public function isAllPagesCacheted(): bool
    {
        if (!$this->is_multi_page) {
            return $this->page_number !== null;
        }
        
        return $this->getCachetedPagesCountAttribute() === $this->total_pages;
    }
    
    /**
     * Obtenir les pages cachetées
     */
    public function getCachetedPagesAttribute(): array
    {
        if (!$this->is_multi_page || !$this->cachet_positions) {
            return $this->page_number ? [$this->page_number] : [];
        }
        
        $cachetedPages = [];
        foreach ($this->cachet_positions as $pageIndex => $position) {
            if ($position !== null) {
                $cachetedPages[] = $pageIndex + 1; // Convertir en numérotation 1-based
            }
        }
        
        return $cachetedPages;
    }
    
    /**
     * Vérifier si le cachet est complet (avec PDF cacheté)
     */
    public function isComplete(): bool
    {
        return !is_null($this->cacheted_at) && !is_null($this->path_cacheted_pdf);
    }

    /**
     * Scope pour les cachets complets
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('cacheted_at')->whereNotNull('path_cacheted_pdf');
    }

    /**
     * Scope pour les cachets d'un utilisateur
     */
    public function scopeByCacheter($query, int $userId)
    {
        return $query->where('cacheted_by', $userId);
    }

    /**
     * Boot method pour enregistrer automatiquement les métadonnées
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cachet) {
            if (request()) {
                $cachet->ip_address = request()->ip();
                $cachet->user_agent = request()->userAgent();
            }
        });
    }
}

