<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Constantes pour les rôles
     */
    const ADMIN = 'admin';
    const AGENT = 'agent';
    const SIGNATAIRE = 'signataire';

    /**
     * Vérifier si le rôle est admin
     */
    public function isAdmin(): bool
    {
        return $this->name === self::ADMIN;
    }

    /**
     * Vérifier si le rôle est agent
     */
    public function isAgent(): bool
    {
        return $this->name === self::AGENT;
    }

    /**
     * Vérifier si le rôle est signataire
     */
    public function isSignataire(): bool
    {
        return $this->name === self::SIGNATAIRE;
    }
}
