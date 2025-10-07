<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'signature_path',
        'paraphe_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relation avec le rôle
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relation avec les documents uploadés
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Relation avec les documents à signer
     */
    public function documentsToSign(): HasMany
    {
        return $this->hasMany(Document::class, 'signer_id');
    }

    /**
     * Relation avec les signatures
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(DocumentSignature::class, 'signed_by');
    }

    /**
     * Relation avec les paraphes
     */
    public function paraphes(): HasMany
    {
        return $this->hasMany(DocumentParaphe::class, 'paraphed_by');
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === Role::ADMIN;
    }

    /**
     * Vérifier si l'utilisateur est agent
     */
    public function isAgent(): bool
    {
        return $this->role && $this->role->name === Role::AGENT;
    }

    /**
     * Vérifier si l'utilisateur est signataire
     */
    public function isSignataire(): bool
    {
        return $this->role && $this->role->name === Role::SIGNATAIRE;
    }

    /**
     * Vérifier si l'utilisateur a une signature
     */
    public function hasSignature(): bool
    {
        return !empty($this->signature_path) && \Storage::disk('public')->exists($this->signature_path);
    }

    /**
     * Obtenir l'URL de la signature
     */
    public function getSignatureUrl(): ?string
    {
        if ($this->hasSignature()) {
            // Forcer l'URL avec le port 8000 pour le développement
            $baseUrl = config('app.url', 'http://localhost:8000');
            if (strpos($baseUrl, ':8000') === false) {
                $baseUrl = 'http://localhost:8000';
            }
            return $baseUrl . '/storage/' . $this->signature_path;
        }
        return null;
    }

    /**
     * Vérifier si l'utilisateur a un paraphe
     */
    public function hasParaphe(): bool
    {
        return !empty($this->paraphe_path) && \Storage::disk('public')->exists($this->paraphe_path);
    }

    /**
     * Obtenir l'URL du paraphe
     */
    public function getParapheUrl(): ?string
    {
        if ($this->hasParaphe()) {
            // Forcer l'URL avec le port 8000 pour le développement
            $baseUrl = config('app.url', 'http://localhost:8000');
            if (strpos($baseUrl, ':8000') === false) {
                $baseUrl = 'http://localhost:8000';
            }
            return $baseUrl . '/storage/' . $this->paraphe_path;
        }
        return null;
    }
}
