<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Afficher le tableau de bord admin
     */
    public function dashboard()
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $stats = [
            'total_users' => User::count(),
            'total_agents' => User::whereHas('role', function($query) {
                $query->where('name', 'agent');
            })->count(),
            'total_signataires' => User::whereHas('role', function($query) {
                $query->where('name', 'signataire');
            })->count(),
            'total_documents' => \App\Models\Document::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function users()
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $users = User::with('role')->orderBy('created_at', 'desc')->get();
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function createUser(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'email_verified_at' => now(),
            ]);

            return redirect()->route('admin.users')
                ->with('success', 'Utilisateur créé avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser(Request $request, User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $validated['role_id'],
            ]);

            return redirect()->route('admin.users')
                ->with('success', 'Utilisateur mis à jour avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser(User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        // Empêcher la suppression de l'admin principal
        if ($user->isAdmin() && $user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte admin.');
        }

        try {
            $user->delete();

            return redirect()->route('admin.users')
                ->with('success', 'Utilisateur supprimé avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Uploader une signature pour un utilisateur
     */
    public function uploadSignature(Request $request, User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'signature' => 'required|file|mimes:png|max:2048', // PNG uniquement, max 2MB
        ]);

        try {
            // Supprimer l'ancienne signature si elle existe
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }

            // Stocker la nouvelle signature
            $file = $request->file('signature');
            $filename = 'signature_' . $user->id . '_' . time() . '.png';
            $path = $file->storeAs('signatures', $filename, 'public');

            // Mettre à jour l'utilisateur
            $user->update(['signature_path' => $path]);

            return redirect()->route('admin.users')
                ->with('success', 'Signature uploadée avec succès pour ' . $user->name . ' !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'upload de la signature : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer la signature d'un utilisateur
     */
    public function deleteSignature(User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        try {
            // Supprimer le fichier de signature
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }

            // Mettre à jour l'utilisateur
            $user->update(['signature_path' => null]);

            return redirect()->route('admin.users')
                ->with('success', 'Signature supprimée avec succès pour ' . $user->name . ' !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la signature : ' . $e->getMessage());
        }
    }

    /**
     * Uploader un paraphe pour un utilisateur
     */
    public function uploadParaphe(Request $request, User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'paraphe' => 'required|file|mimes:png|max:2048', // PNG uniquement, max 2MB
        ]);

        try {
            // Supprimer l'ancien paraphe si il existe
            if ($user->paraphe_path && Storage::disk('public')->exists($user->paraphe_path)) {
                Storage::disk('public')->delete($user->paraphe_path);
            }

            // Stocker le nouveau paraphe
            $file = $request->file('paraphe');
            $filename = 'paraphe_' . $user->id . '_' . time() . '.png';
            $path = $file->storeAs('paraphes', $filename, 'public');

            // Mettre à jour l'utilisateur
            $user->update(['paraphe_path' => $path]);

            return redirect()->route('admin.users')
                ->with('success', 'Paraphe uploadé avec succès pour ' . $user->name . ' !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'upload du paraphe : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer le paraphe d'un utilisateur
     */
    public function deleteParaphe(User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        try {
            // Supprimer le fichier de paraphe
            if ($user->paraphe_path && Storage::disk('public')->exists($user->paraphe_path)) {
                Storage::disk('public')->delete($user->paraphe_path);
            }

            // Mettre à jour l'utilisateur
            $user->update(['paraphe_path' => null]);

            return redirect()->route('admin.users')
                ->with('success', 'Paraphe supprimé avec succès pour ' . $user->name . ' !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du paraphe : ' . $e->getMessage());
        }
    }

    /**
     * Uploader un cachet pour un utilisateur
     */
    public function uploadCachet(Request $request, User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'cachet' => 'required|file|mimes:png|max:2048', // PNG uniquement, max 2MB
        ]);

        try {
            // Supprimer l'ancien cachet si il existe
            if ($user->cachet_path && Storage::disk('public')->exists($user->cachet_path)) {
                Storage::disk('public')->delete($user->cachet_path);
            }

            // Stocker le nouveau cachet
            $file = $request->file('cachet');
            $filename = 'cachet_' . $user->id . '_' . time() . '.png';
            $path = $file->storeAs('cachets', $filename, 'public');

            // Mettre à jour l'utilisateur
            $user->update(['cachet_path' => $path]);

            return redirect()->route('admin.users')
                ->with('success', 'Cachet uploadé avec succès pour ' . $user->name . ' !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'upload du cachet : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer le cachet d'un utilisateur
     */
    public function deleteCachet(User $user)
    {
        // Vérifier que l'utilisateur est admin
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        try {
            // Supprimer le fichier de cachet
            if ($user->cachet_path && Storage::disk('public')->exists($user->cachet_path)) {
                Storage::disk('public')->delete($user->cachet_path);
            }

            // Mettre à jour l'utilisateur
            $user->update(['cachet_path' => null]);

            return redirect()->route('admin.users')
                ->with('success', 'Cachet supprimé avec succès pour ' . $user->name . ' !');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du cachet : ' . $e->getMessage());
        }
    }
}
