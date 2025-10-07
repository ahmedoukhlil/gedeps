<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\ParapheController;
use App\Http\Controllers\CombinedController;
use App\Http\Controllers\DocumentProcessController;
use App\Http\Controllers\SequentialSignatureController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route pour servir les fichiers PDF
Route::get('/storage/documents/{filename}', function ($filename) {
    $filePath = storage_path('app/public/documents/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->name('storage.documents');

// Route pour servir les PDF signés
Route::get('/storage/signed/{filename}', function ($filename) {
    $filePath = storage_path('app/public/documents/signed/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->name('storage.signed');

Route::get('/storage/signatures/{filename}', function ($filename) {
    $filePath = storage_path('app/public/signatures/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
});

Route::get('/storage/signed_documents/{filename}', function ($filename) {
    $filePath = storage_path('app/public/documents/signed/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->name('storage.signed_documents');

Route::get('/storage/combined_documents/{filename}', function ($filename) {
    $filePath = storage_path('app/public/documents/combined/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->name('storage.combined_documents');

Route::get('/storage/paraphed_documents/{filename}', function ($filename) {
    $filePath = storage_path('app/public/paraphed_documents/' . $filename);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->name('storage.paraphed_documents');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Route pour uploader le PDF signé généré côté client
Route::post('/documents/upload-signed-pdf', [DocumentProcessController::class, 'uploadSignedPdf'])->name('documents.upload-signed-pdf');

// Route de debug pour vérifier les signatures
Route::get('/debug/signature', function () {
    $user = auth()->user();
    return response()->json([
        'hasSignature' => $user->hasSignature(),
        'signatureUrl' => $user->getSignatureUrl(),
        'hasParaphe' => $user->hasParaphe(),
        'parapheUrl' => $user->getParapheUrl(),
    ]);
})->middleware('auth');

// Route de debug pour vérifier les fichiers PDF signés
Route::get('/debug/pdf-files/{documentId}', function ($documentId) {
    $document = \App\Models\Document::find($documentId);
    if (!$document) {
        return response()->json(['error' => 'Document non trouvé']);
    }
    
    $debug = [
        'document_id' => $document->id,
        'status' => $document->status,
        'original_path' => $document->path_original,
        'original_exists' => file_exists(storage_path('app/public/' . $document->path_original))
    ];
    
    if ($document->isSigned()) {
        $signature = $document->signatures()->latest()->first();
        if ($signature) {
            $debug['signature'] = [
                'path' => $signature->path_signed_pdf,
                'basename' => basename($signature->path_signed_pdf),
                'url' => route('storage.signed_documents', ['filename' => basename($signature->path_signed_pdf)]),
                'exists_in_signed' => file_exists(storage_path('app/public/documents/signed/' . basename($signature->path_signed_pdf))),
                'full_path' => storage_path('app/public/documents/signed/' . basename($signature->path_signed_pdf))
            ];
        }
    }
    
    if ($document->isParaphed()) {
        $paraphe = $document->paraphes()->latest()->first();
        if ($paraphe) {
            $debug['paraphe'] = [
                'path' => $paraphe->path_paraphed_pdf,
                'basename' => basename($paraphe->path_paraphed_pdf),
                'url' => route('storage.signed_documents', ['filename' => basename($paraphe->path_paraphed_pdf)]),
                'exists_in_signed' => file_exists(storage_path('app/public/documents/signed/' . basename($paraphe->path_paraphed_pdf))),
                'full_path' => storage_path('app/public/documents/signed/' . basename($paraphe->path_paraphed_pdf))
            ];
        }
    }
    
    return response()->json($debug);
})->middleware('auth');

// Routes pour la gestion des documents
Route::middleware(['auth'])->group(function () {
    
    // Page d'upload (Agents)
    Route::get('/documents/upload', [DocumentController::class, 'upload'])
         ->name('documents.upload');
    
    // Traitement de l'upload
    Route::post('/documents/upload', [DocumentController::class, 'store'])
         ->name('documents.store');
    
    // Page d'approbation (DG/DAF)
    Route::get('/documents/pending', [DocumentController::class, 'pending'])
         ->name('documents.pending');
    
    // Historique des documents
    Route::get('/documents/history', [DocumentController::class, 'history'])
         ->name('documents.history');
    
    // Profil utilisateur
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])
         ->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])
         ->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])
         ->name('profile.password');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])
         ->name('profile.destroy');
    
    // Téléchargement sécurisé
    Route::get('/documents/download/{document}', [DocumentController::class, 'download'])
         ->name('documents.download');
    
    // Visualisation dans le navigateur
    Route::get('/documents/view/{document}', [DocumentController::class, 'view'])
         ->name('documents.view');
    
    // Télécharger le PDF signé (pour agents et admins)
    Route::get('/documents/{document}/download-signed', [DocumentController::class, 'downloadSigned'])
         ->name('documents.download-signed');
});

// Routes pour les signatures (signataires uniquement)
Route::middleware(['auth'])->group(function () {
    Route::get('/signatures', [App\Http\Controllers\SignatureController::class, 'index'])
         ->name('signatures.index');
    
    // Route pour récupérer la signature de l'utilisateur (AVANT les routes avec paramètres)
    Route::get('/signatures/user-signature', [App\Http\Controllers\SignatureController::class, 'getUserSignature'])
         ->name('signatures.user-signature');
    
    // Route pour récupérer le paraphe de l'utilisateur
    Route::get('/signatures/user-paraphe', [App\Http\Controllers\SignatureController::class, 'getUserParaphe'])
         ->name('signatures.user-paraphe');
    
// API pour récupérer la signature de l'utilisateur connecté
Route::get('/api/user-signature', [App\Http\Controllers\SignatureController::class, 'getUserSignature'])
     ->name('api.user-signature');

// Route pour sauvegarder le PDF signé
Route::post('/signatures/save-signed-pdf', [App\Http\Controllers\SignatureController::class, 'saveSignedPdf'])
     ->name('signatures.save-signed-pdf');
    
    // Routes avec paramètres (après les routes spécifiques)
    Route::get('/signatures/{document}', [App\Http\Controllers\SignatureController::class, 'show'])
         ->name('signatures.show');
    
    Route::post('/signatures/{document}/sign', [App\Http\Controllers\SignatureController::class, 'sign'])
         ->name('signatures.sign');
    
    Route::post('/signatures/save-signed-pdf', [App\Http\Controllers\SignatureController::class, 'saveSignedPdf'])
         ->name('signatures.save-signed-pdf');
    
    Route::get('/signatures/{document}/download-signed', [App\Http\Controllers\SignatureController::class, 'downloadSigned'])
         ->name('signatures.download-signed');
    
    Route::get('/signatures/{document}/certificate', [App\Http\Controllers\SignatureController::class, 'generateCertificate'])
         ->name('signatures.certificate');
    
    // Routes de diagnostic
    Route::get('/api/debug-signed-documents', [App\Http\Controllers\SignatureController::class, 'debugSignedDocuments']);
    Route::get('/api/debug-signatures', [App\Http\Controllers\SignatureController::class, 'debugSignatures']);
    Route::get('/api/check-signed-files', [App\Http\Controllers\SignatureController::class, 'checkSignedFiles']);
    Route::get('/api/debug-document/{id}', [App\Http\Controllers\SignatureController::class, 'debugDocument']);
    
    // Routes unifiées pour le traitement des documents (remplace les anciennes routes)
    // Les anciennes routes /paraphes et /combined sont maintenant gérées par /documents/{id}/process/{action}
    
    // Routes unifiées pour le traitement des documents
    Route::get('/documents/{document}/process/{action?}', [DocumentProcessController::class, 'show'])
         ->name('documents.process.show')
         ->where('action', 'sign|paraphe|combined|view|download');
    
    Route::post('/documents/{document}/process', [DocumentProcessController::class, 'store'])
        ->name('documents.process.store');
});

// Routes d'administration (admin uniquement)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'dashboard'])
         ->name('admin.dashboard');
    
    Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'users'])
         ->name('admin.users');
    
    Route::post('/admin/users', [App\Http\Controllers\AdminController::class, 'createUser'])
         ->name('admin.users.create');
    
    Route::put('/admin/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])
         ->name('admin.users.update');
    
    Route::delete('/admin/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser'])
         ->name('admin.users.delete');
    
    Route::post('/admin/users/{user}/signature', [App\Http\Controllers\AdminController::class, 'uploadSignature'])
         ->name('admin.users.signature.upload');
    
    Route::delete('/admin/users/{user}/signature', [App\Http\Controllers\AdminController::class, 'deleteSignature'])
         ->name('admin.users.signature.delete');
    
    Route::post('/admin/users/{user}/paraphe', [App\Http\Controllers\AdminController::class, 'uploadParaphe'])
         ->name('admin.users.paraphe.upload');
    
    Route::delete('/admin/users/{user}/paraphe', [App\Http\Controllers\AdminController::class, 'deleteParaphe'])
         ->name('admin.users.paraphe.delete');
});

// Routes d'authentification
Route::middleware('guest')->group(function () {
    // Affichage des formulaires
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    // Traitement du formulaire de connexion
    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    });

});

// Route de déconnexion
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Route API pour récupérer les signataires
Route::get('/api/signers', function () {
    $signers = \App\Models\User::whereHas('role', function($query) { 
        $query->where('name', 'signataire'); 
    })->get(['id', 'name', 'email']);
    
    return response()->json($signers);
})->name('api.signers');

// Route de test pour vérifier les utilisateurs
Route::get('/test-users', function () {
    $users = \App\Models\User::whereIn('id', [3, 4])->get(['id', 'name', 'email']);
    $signers = \App\Models\User::whereHas('role', function($query) { 
        $query->where('name', 'signataire'); 
    })->get(['id', 'name', 'email']);
    
    return response()->json([
        'users_3_4' => $users,
        'all_signers' => $signers
    ]);
});

// Route de test pour vérifier l'authentification
Route::get('/test-auth', function () {
    if (!auth()->check()) {
        return response()->json(['authenticated' => false, 'message' => 'Non authentifié']);
    }
    
    $user = auth()->user();
    return response()->json([
        'authenticated' => true,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'is_signataire' => $user->isSignataire(),
        'is_agent' => $user->isAgent(),
        'is_admin' => $user->isAdmin(),
        'role' => $user->role ? $user->role->name : 'Aucun rôle'
    ]);
});

// Route de test pour les signatures séquentielles
Route::get('/test-sequential', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    $userId = $user->id;
    
    // Compter les documents avec signatures séquentielles
    $totalSequential = \App\Models\Document::where('sequential_signatures', true)->count();
    $userSequential = \App\Models\Document::where('sequential_signatures', true)
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();
    
    return "Utilisateur: {$user->name} (ID: {$userId})<br>" .
           "Rôle: " . ($user->role ? $user->role->name : 'Aucun') . "<br>" .
           "Total documents séquentiels: {$totalSequential}<br>" .
           "Documents pour cet utilisateur: {$userSequential}";
});

// Route de test pour vérifier l'ordre des signatures
Route::get('/test-sequential-order', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    $userId = $user->id;
    
    // Récupérer un document avec signatures séquentielles
    $document = \App\Models\Document::where('sequential_signatures', true)
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['sequentialSignatures.user'])
        ->first();
    
    if (!$document) {
        return "Aucun document avec signatures séquentielles trouvé pour cet utilisateur.";
    }
    
    $html = "<h3>Document: {$document->document_name}</h3>";
    $html .= "<p>Index actuel: {$document->current_signature_index}</p>";
    $html .= "<h4>Ordre des signatures:</h4><ul>";
    
    foreach ($document->sequentialSignatures->sortBy('signature_order') as $signature) {
        $isCurrent = $signature->signature_order == $document->current_signature_index + 1;
        $status = $signature->status;
        $html .= "<li>Ordre {$signature->signature_order}: {$signature->user->name} - {$status}";
        if ($isCurrent) {
            $html .= " <strong>(CURRENT)</strong>";
        }
        $html .= "</li>";
    }
    
    $html .= "</ul>";
    
    return $html;
});

// Route de test pour vérifier les données du formulaire
Route::post('/test-form-data', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'sequential_signers' => $request->input('sequential_signers', []),
        'sequential_signers_order' => $request->input('sequential_signers_order', []),
        'signature_type' => $request->input('signature_type'),
        'all_data' => $request->all()
    ]);
});

// Route de test pour vérifier l'ordre des signatures créées
Route::get('/test-created-signatures', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    // Récupérer le dernier document avec signatures séquentielles
    $document = \App\Models\Document::where('sequential_signatures', true)
        ->with(['sequentialSignatures.user'])
        ->orderBy('created_at', 'desc')
        ->first();
    
    if (!$document) {
        return "Aucun document avec signatures séquentielles trouvé.";
    }
    
    $html = "<h3>Dernier Document: {$document->document_name}</h3>";
    $html .= "<p>Créé le: {$document->created_at}</p>";
    $html .= "<p>Index actuel: {$document->current_signature_index}</p>";
    $html .= "<h4>Ordre des signatures créées:</h4><ul>";
    
    foreach ($document->sequentialSignatures->sortBy('signature_order') as $signature) {
        $isCurrent = $signature->signature_order == $document->current_signature_index + 1;
        $status = $signature->status;
        $html .= "<li>Ordre {$signature->signature_order}: {$signature->user->name} - {$status}";
        if ($isCurrent) {
            $html .= " <strong>(CURRENT - PROCHAIN À SIGNER)</strong>";
        }
        $html .= "</li>";
    }
    
    $html .= "</ul>";
    
    return $html;
});

// Route de test pour voir les logs
Route::get('/test-logs', function () {
    $logFile = storage_path('logs/laravel.log');
    if (!file_exists($logFile)) {
        return "Fichier de log non trouvé.";
    }
    
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $recentLines = array_slice($lines, -50); // Dernières 50 lignes
    
    return "<pre>" . implode("\n", $recentLines) . "</pre>";
});

// Route de test pour vérifier l'accès aux signatures séquentielles
Route::get('/test-sequential-access', function () {
    if (!auth()->check()) {
        return 'Non authentifié - Redirection vers login';
    }
    
    $user = auth()->user();
    $html = "<h3>Test d'Accès aux Signatures Séquentielles</h3>";
    $html .= "<p>Utilisateur: {$user->name} (ID: {$user->id})</p>";
    $html .= "<p>Email: {$user->email}</p>";
    $html .= "<p>Rôle: " . ($user->role ? $user->role->name : 'Aucun rôle') . "</p>";
    $html .= "<p>Est signataire: " . ($user->isSignataire() ? 'OUI' : 'NON') . "</p>";
    
    if (!$user->isSignataire()) {
        $html .= "<p style='color: red;'>ERREUR: Seuls les signataires peuvent accéder à cette page.</p>";
        return $html;
    }
    
    // Vérifier les documents disponibles
    $userId = $user->id;
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending')
                  ->whereRaw('signature_order = current_signature_index + 1');
        })
        ->with(['sequentialSignatures.user', 'uploader'])
        ->get();
    
    $html .= "<h4>Documents disponibles pour signature:</h4>";
    $html .= "<p>Nombre de documents: " . $documents->count() . "</p>";
    
    if ($documents->count() > 0) {
        $html .= "<ul>";
        foreach ($documents as $document) {
            $html .= "<li>{$document->document_name} (Créé par: {$document->uploader->name})</li>";
        }
        $html .= "</ul>";
    } else {
        $html .= "<p>Aucun document en attente de signature.</p>";
    }
    
    return $html;
});

// Route de test pour simuler la création d'un document avec signatures séquentielles
Route::get('/test-create-sequential', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    // Simuler les données du formulaire
    $testData = [
        'sequential_signers' => [3, 4], // IDs des signataires
        'sequential_signers_order' => [1, 2], // Ordre des signataires
        'signature_type' => 'sequential'
    ];
    
    $html = "<h3>Test de Création de Signatures Séquentielles</h3>";
    $html .= "<p>Données de test:</p>";
    $html .= "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Simuler la logique de création
    $signers = \App\Models\User::whereIn('id', $testData['sequential_signers'])->get();
    $orderArray = $testData['sequential_signers_order'];
    
    $html .= "<h4>Signataires trouvés:</h4><ul>";
    foreach ($signers as $signer) {
        $html .= "<li>{$signer->name} (ID: {$signer->id})</li>";
    }
    $html .= "</ul>";
    
    // Simuler la logique de mapping
    $signerOrderMap = [];
    foreach ($testData['sequential_signers'] as $index => $signerId) {
        $order = isset($orderArray[$index]) ? (int)$orderArray[$index] : ($index + 1);
        $signerOrderMap[$signerId] = $order;
    }
    
    $html .= "<h4>Mapping ordre:</h4><ul>";
    foreach ($signerOrderMap as $signerId => $order) {
        $signer = $signers->find($signerId);
        $html .= "<li>ID {$signerId} ({$signer->name}) -> Ordre {$order}</li>";
    }
    $html .= "</ul>";
    
    // Simuler la création des signataires avec ordre
    $signersWithOrder = [];
    foreach ($testData['sequential_signers'] as $signerId) {
        $signer = $signers->find($signerId);
        if ($signer) {
            $order = $signerOrderMap[$signerId];
            $signersWithOrder[] = [
                'user_id' => $signer->id,
                'order' => $order,
                'name' => $signer->name
            ];
        }
    }
    
    // Trier par ordre
    usort($signersWithOrder, function($a, $b) {
        return $a['order'] <=> $b['order'];
    });
    
    $html .= "<h4>Ordre final des signatures:</h4><ul>";
    foreach ($signersWithOrder as $signerData) {
        $html .= "<li>Ordre {$signerData['order']}: {$signerData['name']} (ID: {$signerData['user_id']})</li>";
    }
    $html .= "</ul>";
    
    return $html;
});

// Route pour corriger l'ordre des signatures existantes
Route::get('/fix-sequential-order', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    // Récupérer tous les documents avec signatures séquentielles
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->with(['sequentialSignatures.user'])
        ->get();
    
    $html = "<h3>Correction de l'Ordre des Signatures Séquentielles</h3>";
    $html .= "<p>Documents trouvés: " . $documents->count() . "</p>";
    
    foreach ($documents as $document) {
        $html .= "<h4>Document: {$document->document_name}</h4>";
        $html .= "<p>Index actuel: {$document->current_signature_index}</p>";
        
        // Récupérer les signatures dans l'ordre de création
        $signatures = $document->sequentialSignatures->sortBy('created_at');
        
        $html .= "<h5>Signatures avant correction:</h5><ul>";
        foreach ($signatures as $signature) {
            $html .= "<li>Ordre {$signature->signature_order}: {$signature->user->name} - {$signature->status}</li>";
        }
        $html .= "</ul>";
        
        // Corriger l'ordre en fonction de l'ordre de création
        $correctOrder = 1;
        foreach ($signatures as $signature) {
            $signature->update(['signature_order' => $correctOrder]);
            $correctOrder++;
        }
        
        // Récupérer les signatures après correction
        $signatures = $document->sequentialSignatures->fresh()->sortBy('signature_order');
        
        $html .= "<h5>Signatures après correction:</h5><ul>";
        foreach ($signatures as $signature) {
            $isCurrent = $signature->signature_order == $document->current_signature_index + 1;
            $html .= "<li>Ordre {$signature->signature_order}: {$signature->user->name} - {$signature->status}";
            if ($isCurrent) {
                $html .= " <strong>(CURRENT)</strong>";
            }
            $html .= "</li>";
        }
        $html .= "</ul>";
    }
    
    return $html;
});

// Route de diagnostic détaillé pour les signatures séquentielles
Route::get('/debug-sequential-details', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    $userId = $user->id;
    
    $html = "<h3>Diagnostic Détaillé des Signatures Séquentielles</h3>";
    $html .= "<p>Utilisateur: {$user->name} (ID: {$userId})</p>";
    
    // 1. Vérifier tous les documents avec signatures séquentielles
    $allSequentialDocs = \App\Models\Document::where('sequential_signatures', true)
        ->with(['sequentialSignatures.user', 'uploader'])
        ->get();
    
    $html .= "<h4>1. Tous les documents avec signatures séquentielles:</h4>";
    $html .= "<p>Nombre total: " . $allSequentialDocs->count() . "</p>";
    
    if ($allSequentialDocs->count() > 0) {
        $html .= "<ul>";
        foreach ($allSequentialDocs as $doc) {
            $html .= "<li><strong>{$doc->document_name}</strong> (Statut: {$doc->status}, Index: {$doc->current_signature_index})</li>";
            $html .= "<ul>";
            foreach ($doc->sequentialSignatures->sortBy('signature_order') as $sig) {
                $isCurrent = $sig->signature_order == $doc->current_signature_index + 1;
                $html .= "<li>Ordre {$sig->signature_order}: {$sig->user->name} - {$sig->status}";
                if ($isCurrent) {
                    $html .= " <strong>(CURRENT)</strong>";
                }
                $html .= "</li>";
            }
            $html .= "</ul>";
        }
        $html .= "</ul>";
    }
    
    // 2. Vérifier les documents où l'utilisateur est impliqué
    $userDocs = \App\Models\Document::where('sequential_signatures', true)
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with(['sequentialSignatures.user', 'uploader'])
        ->get();
    
    $html .= "<h4>2. Documents où l'utilisateur est impliqué:</h4>";
    $html .= "<p>Nombre: " . $userDocs->count() . "</p>";
    
    if ($userDocs->count() > 0) {
        $html .= "<ul>";
        foreach ($userDocs as $doc) {
            $html .= "<li><strong>{$doc->document_name}</strong> (Statut: {$doc->status}, Index: {$doc->current_signature_index})</li>";
            $userSignature = $doc->sequentialSignatures->where('user_id', $userId)->first();
            if ($userSignature) {
                $html .= "<li>Signature de l'utilisateur: Ordre {$userSignature->signature_order}, Statut: {$userSignature->status}</li>";
                $isCurrent = $userSignature->signature_order == $doc->current_signature_index + 1;
                $html .= "<li>Est le prochain à signer: " . ($isCurrent ? 'OUI' : 'NON') . "</li>";
            }
        }
        $html .= "</ul>";
    }
    
    // 3. Vérifier la requête exacte du contrôleur
    $controllerDocs = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending')
                  ->whereRaw('signature_order = current_signature_index + 1');
        })
        ->with(['sequentialSignatures.user', 'uploader'])
        ->get();
    
    $html .= "<h4>3. Documents selon la requête du contrôleur:</h4>";
    $html .= "<p>Nombre: " . $controllerDocs->count() . "</p>";
    
    if ($controllerDocs->count() > 0) {
        $html .= "<ul>";
        foreach ($controllerDocs as $doc) {
            $html .= "<li><strong>{$doc->document_name}</strong></li>";
        }
        $html .= "</ul>";
    }
    
    return $html;
});

// Route pour corriger le statut des documents séquentiels
Route::get('/fix-document-status', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $html = "<h3>Correction du Statut des Documents Séquentiels</h3>";
    
    // Récupérer tous les documents avec signatures séquentielles et statut pending
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'pending')
        ->with(['sequentialSignatures.user', 'uploader'])
        ->get();
    
    $html .= "<p>Documents trouvés avec statut 'pending': " . $documents->count() . "</p>";
    
    if ($documents->count() > 0) {
        $html .= "<h4>Documents avant correction:</h4><ul>";
        foreach ($documents as $doc) {
            $html .= "<li><strong>{$doc->document_name}</strong> - Statut: {$doc->status}</li>";
        }
        $html .= "</ul>";
        
        // Corriger le statut de pending à in_progress
        $updated = \App\Models\Document::where('sequential_signatures', true)
            ->where('status', 'pending')
            ->update(['status' => 'in_progress']);
        
        $html .= "<h4>Correction effectuée:</h4>";
        $html .= "<p>Nombre de documents mis à jour: {$updated}</p>";
        
        // Vérifier après correction
        $documentsAfter = \App\Models\Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->with(['sequentialSignatures.user', 'uploader'])
            ->get();
        
        $html .= "<h4>Documents après correction:</h4><ul>";
        foreach ($documentsAfter as $doc) {
            $html .= "<li><strong>{$doc->document_name}</strong> - Statut: {$doc->status}</li>";
        }
        $html .= "</ul>";
        
        $html .= "<h4>Test de la requête du contrôleur:</h4>";
        $user = auth()->user();
        $userId = $user->id;
        
        $controllerDocs = \App\Models\Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->whereHas('sequentialSignatures', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending')
                      ->whereRaw('signature_order = current_signature_index + 1');
            })
            ->with(['sequentialSignatures.user', 'uploader'])
            ->get();
        
        $html .= "<p>Documents maintenant visibles pour l'utilisateur: " . $controllerDocs->count() . "</p>";
        
        if ($controllerDocs->count() > 0) {
            $html .= "<ul>";
            foreach ($controllerDocs as $doc) {
                $html .= "<li><strong>{$doc->document_name}</strong></li>";
            }
            $html .= "</ul>";
        }
    } else {
        $html .= "<p>Aucun document avec statut 'pending' trouvé.</p>";
    }
    
    return $html;
});

// Route de diagnostic détaillé de la requête du contrôleur
Route::get('/debug-controller-query', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    $userId = $user->id;
    
    $html = "<h3>Diagnostic Détaillé de la Requête du Contrôleur</h3>";
    $html .= "<p>Utilisateur: {$user->name} (ID: {$userId})</p>";
    
    // Test 1: Documents avec signatures séquentielles et statut in_progress
    $step1 = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->get();
    
    $html .= "<h4>Étape 1 - Documents avec signatures séquentielles et statut in_progress:</h4>";
    $html .= "<p>Nombre: " . $step1->count() . "</p>";
    
    // Test 2: Documents où l'utilisateur a une signature
    $step2 = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->get();
    
    $html .= "<h4>Étape 2 - Documents où l'utilisateur a une signature:</h4>";
    $html .= "<p>Nombre: " . $step2->count() . "</p>";
    
    if ($step2->count() > 0) {
        $html .= "<ul>";
        foreach ($step2 as $doc) {
            $userSignature = $doc->sequentialSignatures->where('user_id', $userId)->first();
            $html .= "<li><strong>{$doc->document_name}</strong> - Index: {$doc->current_signature_index}, Signature ordre: {$userSignature->signature_order}, Statut: {$userSignature->status}</li>";
        }
        $html .= "</ul>";
    }
    
    // Test 3: Documents où la signature est pending
    $step3 = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending');
        })
        ->get();
    
    $html .= "<h4>Étape 3 - Documents où la signature est pending:</h4>";
    $html .= "<p>Nombre: " . $step3->count() . "</p>";
    
    // Test 4: Documents où signature_order = current_signature_index + 1
    $step4 = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending')
                  ->whereRaw('signature_order = current_signature_index + 1');
        })
        ->get();
    
    $html .= "<h4>Étape 4 - Documents où signature_order = current_signature_index + 1:</h4>";
    $html .= "<p>Nombre: " . $step4->count() . "</p>";
    
    if ($step4->count() > 0) {
        $html .= "<ul>";
        foreach ($step4 as $doc) {
            $html .= "<li><strong>{$doc->document_name}</strong></li>";
        }
        $html .= "</ul>";
    }
    
    // Test 5: Vérifier les conditions manuellement
    $html .= "<h4>Test 5 - Vérification manuelle des conditions:</h4>";
    foreach ($step2 as $doc) {
        $userSignature = $doc->sequentialSignatures->where('user_id', $userId)->first();
        $isPending = $userSignature->status === 'pending';
        $isCurrent = $userSignature->signature_order == $doc->current_signature_index + 1;
        
        $html .= "<p><strong>{$doc->document_name}:</strong></p>";
        $html .= "<ul>";
        $html .= "<li>Signature pending: " . ($isPending ? 'OUI' : 'NON') . "</li>";
        $html .= "<li>Est le prochain à signer: " . ($isCurrent ? 'OUI' : 'NON') . "</li>";
        $html .= "<li>Signature order: {$userSignature->signature_order}</li>";
        $html .= "<li>Current index: {$doc->current_signature_index}</li>";
        $html .= "<li>Calcul: {$userSignature->signature_order} == {$doc->current_signature_index} + 1 = " . ($userSignature->signature_order == $doc->current_signature_index + 1 ? 'VRAI' : 'FAUX') . "</li>";
        $html .= "</ul>";
    }
    
    return $html;
});

// Route de test simple pour vérifier l'accès
Route::get('/test-simple', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    return "Connecté en tant que: {$user->name} (ID: {$user->id}) - Rôle: " . ($user->role ? $user->role->name : 'Aucun');
});

// Route de test qui simule exactement le contrôleur SequentialSignatureController
Route::get('/test-sequential-controller', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    if (!auth()->user()->isSignataire()) {
        return 'Seuls les signataires peuvent accéder à cette page.';
    }
    
    $userId = auth()->id();
    
    try {
        // Documents avec signatures séquentielles où l'utilisateur est le prochain à signer
        $documents = \App\Models\Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->whereHas('sequentialSignatures', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending')
                      ->whereRaw('signature_order = current_signature_index + 1');
            })
            ->with(['sequentialSignatures.user', 'uploader'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
            
        $html = "<h3>Test du Contrôleur SequentialSignatureController</h3>";
        $html .= "<p>Utilisateur: " . auth()->user()->name . " (ID: {$userId})</p>";
        $html .= "<p>Documents trouvés: " . $documents->count() . "</p>";
        
        if ($documents->count() > 0) {
            $html .= "<ul>";
            foreach ($documents as $doc) {
                $html .= "<li><strong>{$doc->document_name}</strong> (Créé par: {$doc->uploader->name})</li>";
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>Aucun document trouvé.</p>";
        }
        
        return $html;
        
    } catch (\Exception $e) {
        return "Erreur: " . $e->getMessage();
    }
});

// Route de test directe pour les signatures séquentielles (sans middleware)
Route::get('/test-sequential-direct', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    if (!auth()->user()->isSignataire()) {
        return 'Seuls les signataires peuvent accéder à cette page.';
    }
    
    $userId = auth()->id();
    
    try {
        // Documents avec signatures séquentielles où l'utilisateur est le prochain à signer
        $documents = \App\Models\Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->whereHas('sequentialSignatures', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending')
                      ->whereRaw('signature_order = current_signature_index + 1');
            })
            ->with(['sequentialSignatures.user', 'uploader'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
            
        // Utiliser la même vue que le contrôleur
        return view('signatures.sequential', compact('documents'));
        
    } catch (\Exception $e) {
        return "Erreur: " . $e->getMessage();
    }
});

// Route de test dans le même groupe de middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/test-middleware-auth', function () {
        return "Middleware auth fonctionne - Utilisateur: " . auth()->user()->name;
    });
});

// Route de test pour signatures séquentielles (hors du groupe middleware)
Route::get('/signatures-sequential-test', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    if (!auth()->user()->isSignataire()) {
        return redirect()->back()->with('error', 'Seuls les signataires peuvent accéder à cette page.');
    }
    
    $userId = auth()->id();
    
    // Documents avec signatures séquentielles où l'utilisateur est le prochain à signer
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending')
                  ->whereRaw('signature_order = current_signature_index + 1');
        })
        ->with(['sequentialSignatures.user', 'uploader'])
        ->orderBy('updated_at', 'desc')
        ->paginate(10);
        
    return view('signatures.sequential', compact('documents'));
});

// ========================================
// NOUVEAU SYSTÈME DE SIGNATURES SÉQUENTIELLES - SIMPLE
// ========================================

// Routes simples pour les signatures séquentielles
Route::get('/signatures-simple', [App\Http\Controllers\SimpleSequentialController::class, 'index'])
    ->name('signatures.simple.index');
    
Route::get('/signatures-simple/{document}', [App\Http\Controllers\SimpleSequentialController::class, 'show'])
    ->name('signatures.simple.show');
    
Route::get('/signatures-simple/{document}/{action}', [App\Http\Controllers\SimpleSequentialController::class, 'show'])
    ->name('signatures.simple.show.action');
    
Route::post('/signatures-simple/{document}/sign', [App\Http\Controllers\SimpleSequentialController::class, 'sign'])
    ->name('signatures.simple.sign');

Route::post('/signatures-simple/{document}/paraphe', [App\Http\Controllers\SimpleSequentialController::class, 'paraphe'])
    ->name('signatures.simple.paraphe');

Route::post('/signatures-simple/{document}/sign-and-paraphe', [App\Http\Controllers\SimpleSequentialController::class, 'signAndParaphe'])
    ->name('signatures.simple.sign-and-paraphe');

// Route pour créer un document de test
Route::get('/create-test-document', [App\Http\Controllers\SimpleSequentialController::class, 'createTestDocument'])
    ->name('signatures.simple.create-test');

// Route de diagnostic pour les documents
Route::get('/debug-document/{id}', function ($id) {
    try {
        $document = \App\Models\Document::find($id);
        
        if (!$document) {
            return "Document avec l'ID {$id} non trouvé.";
        }
        
        $html = "<h3>Diagnostic du Document ID: {$id}</h3>";
        $html .= "<p><strong>Nom:</strong> {$document->document_name}</p>";
        $html .= "<p><strong>Type:</strong> {$document->type}</p>";
        $html .= "<p><strong>Statut:</strong> {$document->status}</p>";
        $html .= "<p><strong>Uploadé par:</strong> " . ($document->uploader ? $document->uploader->name : 'Inconnu') . "</p>";
        $html .= "<p><strong>Chemin:</strong> {$document->path_original}</p>";
        $html .= "<p><strong>Fichier existe:</strong> " . (file_exists(storage_path('app/public/' . $document->path_original)) ? 'OUI' : 'NON') . "</p>";
        
        if ($document->sequential_signatures) {
            $html .= "<p><strong>Signatures séquentielles:</strong> OUI</p>";
            $html .= "<p><strong>Index actuel:</strong> {$document->current_signature_index}</p>";
            $html .= "<p><strong>Nombre de signataires:</strong> " . $document->sequentialSignatures->count() . "</p>";
        }
        
        $html .= "<p><a href='/documents/view/{$id}'>Tester l'accès au document</a></p>";
        
        return $html;
        
    } catch (\Exception $e) {
        return "Erreur: " . $e->getMessage();
    }
});

// Route pour accéder à un document avec signatures séquentielles
Route::get('/document-sign/{document}', function (Document $document) {
    // Vérifier que c'est un document avec signatures séquentielles
    if (!$document->sequential_signatures) {
        return redirect()->back()->with('error', 'Ce document ne nécessite pas de signatures séquentielles.');
    }
    
    // Vérifier que l'utilisateur est connecté et est signataire
    if (!auth()->check() || !auth()->user()->isSignataire()) {
        return redirect()->route('login')->with('error', 'Vous devez être connecté en tant que signataire.');
    }
    
    // Rediriger vers la page de signature séquentielle
    return redirect()->route('signatures.simple.show', $document);
});

// Route pour sauvegarder le PDF signé (séquentiel)
Route::post('/signatures-simple/{document}/save-signed-pdf', [App\Http\Controllers\SimpleSequentialController::class, 'saveSignedPdf'])
    ->name('signatures.simple.save-signed-pdf');

// Route pour uploader le PDF signé (séquentiel)
Route::post('/signatures-simple/{document}/upload-signed-pdf', [App\Http\Controllers\SimpleSequentialController::class, 'uploadSignedPdf'])
    ->name('signatures.simple.upload-signed-pdf');

// Route de diagnostic pour l'état séquentiel
Route::get('/debug-sequential-state/{documentId}', function ($documentId) {
    $document = App\Models\Document::find($documentId);
    if (!$document) {
        return response()->json(['error' => 'Document non trouvé']);
    }
    
    $sequentialSignatures = $document->sequentialSignatures()
        ->with('user')
        ->orderBy('signature_order')
        ->get();
    
    return response()->json([
        'document_id' => $document->id,
        'status' => $document->status,
        'current_signature_index' => $document->current_signature_index,
        'sequential_signatures' => $sequentialSignatures->map(function($sig) {
            return [
                'order' => $sig->signature_order,
                'user_name' => $sig->user->name,
                'status' => $sig->status,
                'signed_at' => $sig->signed_at
            ];
        })
    ]);
});

// Route pour créer un fichier PDF de test
Route::get('/create-test-pdf/{documentId}', function ($documentId) {
    try {
        $document = \App\Models\Document::find($documentId);
        
        if (!$document) {
            return "Document avec l'ID {$documentId} non trouvé.";
        }
        
        // Créer le répertoire s'il n'existe pas
        $directory = storage_path('app/public/documents');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Chemin du fichier
        $filePath = storage_path('app/public/' . $document->path_original);
        
        // Créer un PDF simple avec TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator('GEDEPS');
        $pdf->SetAuthor('Système de Test');
        $pdf->SetTitle($document->document_name);
        $pdf->SetSubject('Document de Test - Signatures Séquentielles');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $document->document_name, 0, 1, 'C');
        
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Type: ' . $document->type, 0, 1);
        $pdf->Cell(0, 10, 'Statut: ' . $document->status, 0, 1);
        $pdf->Cell(0, 10, 'Uploadé par: ' . ($document->uploader ? $document->uploader->name : 'Inconnu'), 0, 1);
        $pdf->Cell(0, 10, 'Date de création: ' . $document->created_at->format('d/m/Y H:i'), 0, 1);
        
        if ($document->sequential_signatures) {
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'SIGNATURES SÉQUENTIELLES', 0, 1, 'C');
            
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Index actuel: ' . $document->current_signature_index, 0, 1);
            $pdf->Cell(0, 10, 'Nombre de signataires: ' . $document->sequentialSignatures->count(), 0, 1);
            
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Liste des signataires:', 0, 1);
            
            $pdf->SetFont('helvetica', '', 10);
            foreach ($document->sequentialSignatures as $signature) {
                $status = $signature->status == 'completed' ? '✓ Signé' : '⏳ En attente';
                $pdf->Cell(0, 8, $signature->signature_order . '. ' . $signature->user->name . ' - ' . $status, 0, 1);
            }
        }
        
        $pdf->Ln(20);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'Document généré automatiquement par le système GEDEPS', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Date de génération: ' . now()->format('d/m/Y H:i:s'), 0, 1, 'C');
        
        // Sauvegarder le PDF
        $pdf->Output($filePath, 'F');
        
        return "✅ Fichier PDF créé avec succès !<br>" .
               "📁 Chemin: {$document->path_original}<br>" .
               "🔗 <a href='/documents/view/{$documentId}'>Voir le document</a><br>" .
               "🔗 <a href='/debug-document/{$documentId}'>Diagnostic</a>";
        
    } catch (\Exception $e) {
        return "❌ Erreur lors de la création du PDF: " . $e->getMessage();
    }
});

    // Route pour créer une signature test
    Route::post('/create-test-signature', function () {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non connecté'
                ], 401);
            }
            
            // Créer un fichier de signature test simple
            $signaturePath = 'signatures/test-signature-' . $user->id . '.png';
            $fullPath = storage_path('app/public/' . $signaturePath);
            
            // Créer le répertoire s'il n'existe pas
            $directory = dirname($fullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Créer une image de signature test simple
            $image = imagecreate(200, 100);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            
            imagefill($image, 0, 0, $white);
            imagestring($image, 5, 50, 40, $user->name, $black);
            
            imagepng($image, $fullPath);
            imagedestroy($image);
            
            // Mettre à jour l'utilisateur avec le chemin de signature
            $user->update(['signature_path' => $signaturePath]);
            
            $baseUrl = config('app.url', 'http://localhost:8000');
            $signatureUrl = $baseUrl . '/storage/' . $signaturePath;
            
            return response()->json([
                'success' => true,
                'message' => 'Signature test créée avec succès',
                'signature_path' => $signaturePath,
                'signature_url' => $signatureUrl,
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la signature test : ' . $e->getMessage()
            ], 500);
        }
    });

    // Route de diagnostic pour la logique séquentielle
    Route::get('/debug-sequential-logic/{documentId}', function ($documentId) {
    try {
        $document = \App\Models\Document::find($documentId);
        
        if (!$document) {
            return "Document avec l'ID {$documentId} non trouvé.";
        }
        
        $html = "<h3>Diagnostic de la Logique Séquentielle - Document ID: {$documentId}</h3>";
        $html .= "<p><strong>Nom:</strong> {$document->document_name}</p>";
        $html .= "<p><strong>Statut:</strong> {$document->status}</p>";
        $html .= "<p><strong>Index actuel:</strong> {$document->current_signature_index}</p>";
        $html .= "<p><strong>Signatures séquentielles:</strong> " . ($document->sequential_signatures ? 'OUI' : 'NON') . "</p>";
        
        $html .= "<h4>Signataires séquentiels:</h4>";
        $html .= "<ul>";
        foreach ($document->sequentialSignatures->sortBy('signature_order') as $signature) {
            $status = $signature->status == 'signed' ? '✓ Signé' : '⏳ En attente';
            $isCurrent = $signature->signature_order == $document->current_signature_index + 1;
            $currentClass = $isCurrent ? ' style="background: yellow; font-weight: bold;"' : '';
            $html .= "<li{$currentClass}>";
            $html .= "Ordre {$signature->signature_order}: {$signature->user->name} - {$status}";
            if ($isCurrent) {
                $html .= " <strong>(C'EST LE TOUR DE CET UTILISATEUR)</strong>";
            }
            $html .= "</li>";
        }
        $html .= "</ul>";
        
        $html .= "<h4>Prochain signataire:</h4>";
        $nextSigner = $document->sequentialSignatures()
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
        
        if ($nextSigner) {
            $html .= "<p><strong>Prochain:</strong> {$nextSigner->user->name} (Ordre: {$nextSigner->signature_order})</p>";
        } else {
            $html .= "<p><strong>Prochain:</strong> Aucun (toutes les signatures sont terminées)</p>";
        }
        
        $html .= "<p><a href='/signatures-simple'>Retour aux signatures</a></p>";
        
        return $html;
        
    } catch (\Exception $e) {
        return "Erreur: " . $e->getMessage();
    }
});

// Route de diagnostic pour les signatures séquentielles
Route::get('/debug-simple-sequential', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    $html = "<h3>Diagnostic du Système Simple</h3>";
    $html .= "<p>Utilisateur: {$user->name} (ID: {$user->id})</p>";
    $html .= "<p>Rôle: " . ($user->role ? $user->role->name : 'Aucun') . "</p>";
    $html .= "<p>Est signataire: " . ($user->isSignataire() ? 'OUI' : 'NON') . "</p>";
    
    if (!$user->isSignataire()) {
        $html .= "<p style='color: red;'>ERREUR: L'utilisateur n'est pas signataire.</p>";
        return $html;
    }
    
    try {
        $userId = $user->id;
        
        // Test de la requête
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending')
                  ->whereRaw('signature_order = current_signature_index + 1');
        })
        ->with(['sequentialSignatures.user', 'uploader'])
            ->get();
            
        $html .= "<p>Documents trouvés: " . $documents->count() . "</p>";
        
        if ($documents->count() > 0) {
            $html .= "<h4>Documents disponibles:</h4><ul>";
            foreach ($documents as $doc) {
                $html .= "<li>{$doc->document_name} (Statut: {$doc->status})</li>";
            }
            $html .= "</ul>";
        }
        
        $html .= "<p style='color: green;'>SUCCÈS: Le système fonctionne correctement.</p>";
        $html .= "<p><a href='/signatures-simple'>Cliquez ici pour accéder aux signatures</a></p>";
        
        return $html;
        
    } catch (\Exception $e) {
        $html .= "<p style='color: red;'>ERREUR: " . $e->getMessage() . "</p>";
        $html .= "<p>Fichier: " . $e->getFile() . "</p>";
        $html .= "<p>Ligne: " . $e->getLine() . "</p>";
        return $html;
    }
});

// Route de test simple pour vérifier que la route fonctionne
Route::get('/test-sequential-simple', function () {
    return "Route de test simple - Fonctionne !";
});

// Route de test pour vérifier l'accès aux signatures séquentielles
Route::get('/test-sequential-access', function () {
    if (!auth()->check()) {
        return 'Non authentifié - Redirection vers login';
    }
    
    $user = auth()->user();
    $html = "<h3>Test d'Accès aux Signatures Séquentielles</h3>";
    $html .= "<p>Utilisateur: {$user->name} (ID: {$user->id})</p>";
    $html .= "<p>Rôle: " . ($user->role ? $user->role->name : 'Aucun') . "</p>";
    $html .= "<p>Est signataire: " . ($user->isSignataire() ? 'OUI' : 'NON') . "</p>";
    
    if (!$user->isSignataire()) {
        $html .= "<p style='color: red;'>ERREUR: Seuls les signataires peuvent accéder à cette page.</p>";
        return $html;
    }
    
    $html .= "<p style='color: green;'>SUCCÈS: L'utilisateur peut accéder aux signatures séquentielles.</p>";
    $html .= "<p><a href='/signatures/sequential'>Cliquez ici pour accéder aux signatures séquentielles</a></p>";
    
    return $html;
});

// Route de diagnostic pour les signatures séquentielles
Route::get('/debug-sequential-system', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    $user = auth()->user();
    $html = "<h3>Diagnostic du Système de Signatures Séquentielles</h3>";
    $html .= "<p>Utilisateur: {$user->name} (ID: {$user->id})</p>";
    $html .= "<p>Rôle: " . ($user->role ? $user->role->name : 'Aucun') . "</p>";
    $html .= "<p>Est signataire: " . ($user->isSignataire() ? 'OUI' : 'NON') . "</p>";
    
    // Vérifier les documents avec signatures séquentielles
    $sequentialDocs = \App\Models\Document::where('sequential_signatures', true)->count();
    $html .= "<p>Documents avec signatures séquentielles: {$sequentialDocs}</p>";
    
    // Vérifier les utilisateurs signataires
    $signers = \App\Models\User::whereHas('role', function($q) { $q->where('name', 'signataire'); })->count();
    $html .= "<p>Utilisateurs signataires: {$signers}</p>";
    
    // Vérifier les signatures séquentielles
    $sequentialSignatures = \App\Models\SequentialSignature::count();
    $html .= "<p>Signatures séquentielles en base: {$sequentialSignatures}</p>";
    
    // Test de la requête du contrôleur
    if ($user->isSignataire()) {
        $userId = $user->id;
        $documents = \App\Models\Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->whereHas('sequentialSignatures', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending')
                      ->whereRaw('signature_order = current_signature_index + 1');
            })
            ->with(['sequentialSignatures.user', 'uploader'])
            ->get();
            
        $html .= "<p>Documents trouvés pour cet utilisateur: " . $documents->count() . "</p>";
        
        if ($documents->count() > 0) {
            $html .= "<h4>Documents disponibles:</h4><ul>";
            foreach ($documents as $doc) {
                $html .= "<li>{$doc->document_name} (Statut: {$doc->status})</li>";
            }
            $html .= "</ul>";
        }
    }
    
    return $html;
});

// Route pour créer un document de test avec signatures séquentielles
Route::get('/create-test-sequential-document', function () {
    if (!auth()->check()) {
        return 'Non authentifié';
    }
    
    // Vérifier qu'il y a des signataires
    $signers = \App\Models\User::whereHas('role', function($q) { $q->where('name', 'signataire'); })->get();
    
    if ($signers->count() < 2) {
        return 'Erreur: Il faut au moins 2 signataires pour tester les signatures séquentielles.';
    }
    
    // Créer un document de test
    $document = \App\Models\Document::create([
        'document_name' => 'Document de Test - Signatures Séquentielles',
        'type' => 'contrat',
        'description' => 'Document de test pour les signatures séquentielles',
        'filename_original' => 'test-sequential.pdf',
        'path_original' => 'documents/test-sequential.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'status' => 'in_progress',
        'uploaded_by' => auth()->id(),
        'sequential_signatures' => true,
        'current_signature_index' => 0,
        'signature_queue' => $signers->pluck('id')->toArray(),
        'completed_signatures' => []
    ]);
    
    // Créer les signatures séquentielles
    foreach ($signers as $index => $signer) {
        \App\Models\SequentialSignature::create([
            'document_id' => $document->id,
            'user_id' => $signer->id,
            'signature_order' => $index + 1,
            'status' => 'pending'
        ]);
    }
    
    return "Document de test créé avec succès ! ID: {$document->id}, Signataires: " . $signers->pluck('name')->join(', ');
});

// Route de test qui simule exactement signatures/sequential
Route::get('/test-sequential-exact', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    if (!auth()->user()->isSignataire()) {
        return redirect()->back()->with('error', 'Seuls les signataires peuvent accéder à cette page.');
    }
    
    $userId = auth()->id();
    
    $documents = \App\Models\Document::where('sequential_signatures', true)
        ->where('status', 'in_progress')
        ->whereHas('sequentialSignatures', function($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'pending')
                  ->whereRaw('signature_order = current_signature_index + 1');
        })
        ->with(['sequentialSignatures.user', 'uploader'])
        ->orderBy('updated_at', 'desc')
        ->paginate(10);
        
    return view('signatures.sequential', compact('documents'));
});

