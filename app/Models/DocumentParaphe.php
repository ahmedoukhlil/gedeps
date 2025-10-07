<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentParaphe extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'paraphed_by',
        'paraphed_at',
        'paraphe_comment',
        'path_paraphed_pdf',
        'paraphe_type',
        'paraphe_positions',
        'is_multi_page',
        'total_pages',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'paraphed_at' => 'datetime',
        'paraphe_positions' => 'array',
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
     * Relation avec l'utilisateur qui a paraphé
     */
    public function parapher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paraphed_by');
    }
}
