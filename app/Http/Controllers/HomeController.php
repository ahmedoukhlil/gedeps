<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SequentialSignature;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Afficher la page d'accueil avec les statistiques
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $stats = $this->getDashboardStats();
        
        return view('home', compact('stats'));
    }

    /**
     * Obtenir les statistiques du dashboard
     */
    protected function getDashboardStats()
    {
        $user = auth()->user();
        $stats = [];

        if ($user->isAdmin()) {
            $stats = [
                'total_users' => \App\Models\User::count(),
                'total_documents' => Document::count(),
                'pending_documents' => Document::where('status', 'pending')->count(),
                'signed_documents' => Document::where('status', 'signed')->count(),
                'sequential_documents' => Document::where('sequential_signatures', true)->count(),
                'sequential_pending' => Document::where('sequential_signatures', true)
                    ->where('status', 'in_progress')->count(),
            ];
        } elseif ($user->isAgent()) {
            $stats = [
                'my_documents' => Document::where('uploaded_by', $user->id)->count(),
                'pending_approval' => Document::where('uploaded_by', $user->id)
                    ->whereIn('status', ['pending', 'in_progress'])->count(),
                'signed_documents' => Document::where('uploaded_by', $user->id)
                    ->where('status', 'signed')->count(),
                'sequential_created' => Document::where('uploaded_by', $user->id)
                    ->where('sequential_signatures', true)->count(),
            ];
        } elseif ($user->isSignataire()) {
            $stats = [
                'pending_documents' => Document::where('signer_id', $user->id)
                    ->where('status', 'pending')->count(),
                'signed_documents' => Document::where('signer_id', $user->id)
                    ->where('status', 'signed')->count(),
                'sequential_pending' => SequentialSignature::where('user_id', $user->id)
                    ->where('status', 'pending')->count(),
                'sequential_signed' => SequentialSignature::where('user_id', $user->id)
                    ->where('status', 'signed')->count(),
            ];
        }

        return $stats;
    }
}
