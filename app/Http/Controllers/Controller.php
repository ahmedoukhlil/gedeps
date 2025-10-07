<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Obtenir les statistiques pour le tableau de bord
     */
    protected function getDashboardStats()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return [
                'total_users' => \App\Models\User::count(),
                'total_agents' => \App\Models\User::whereHas('role', function($query) {
                    $query->where('name', 'agent');
                })->count(),
                'total_signataires' => \App\Models\User::whereHas('role', function($query) {
                    $query->where('name', 'signataire');
                })->count(),
                'total_documents' => \App\Models\Document::count(),
                'pending_documents' => \App\Models\Document::where('status', 'pending')->count(),
                'signed_documents' => \App\Models\Document::where('status', 'signed')->count(),
            ];
        } elseif ($user->isAgent()) {
            return [
                'my_documents' => \App\Models\Document::where('uploaded_by', $user->id)->count(),
                'pending_documents' => \App\Models\Document::where('uploaded_by', $user->id)
                    ->where('status', 'pending')->count(),
                'signed_documents' => \App\Models\Document::where('uploaded_by', $user->id)
                    ->where('status', 'signed')->count(),
            ];
        } elseif ($user->isSignataire()) {
            return [
                'documents_to_sign' => \App\Models\Document::where('signer_id', $user->id)
                    ->where('status', 'pending')->count(),
                'signed_documents' => \App\Models\Document::where('signer_id', $user->id)
                    ->where('status', 'signed')->count(),
            ];
        }
        
        return [];
    }
}
