<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SequentialSignature;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Afficher la page d'accueil
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $counts = $this->getCounts();

        return view('home', compact('counts'));
    }

    /**
     * Obtenir les compteurs pour les badges de notification
     */
    protected function getCounts()
    {
        $user = auth()->user();
        $counts = [
            'pending' => 0,
            'simple_signatures' => 0,
            'sequential_signatures' => 0,
        ];

        if ($user->isAdmin()) {
            $counts['pending'] = Document::whereIn('status', ['pending', 'in_progress'])->count();
        } elseif ($user->isAgent()) {
            $counts['pending'] = Document::where('uploaded_by', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])->count();
        } elseif ($user->isSignataire()) {
            // Documents simples à signer
            $counts['simple_signatures'] = Document::where('signer_id', $user->id)
                ->where('status', 'pending')
                ->where('sequential_signatures', false)
                ->count();

            // Signatures séquentielles en attente
            $counts['sequential_signatures'] = SequentialSignature::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();

            $counts['pending'] = $counts['simple_signatures'] + $counts['sequential_signatures'];
        }

        return $counts;
    }
}
