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

// Route pour servir les PDF signés (utilise le lien symbolique Laravel standard)
// Pas besoin de route personnalisée - Laravel gère déjà /storage/ via le lien symbolique

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

// Routes pour les signatures séquentielles
Route::get('/signatures-simple', [App\Http\Controllers\SimpleSequentialController::class, 'index'])->name('signatures.simple.index');
Route::get('/signatures-simple/debug', [App\Http\Controllers\SimpleSequentialController::class, 'debugSequentialSignatures'])->name('signatures.simple.debug');

Route::get('/signatures-simple/{document}', [App\Http\Controllers\SimpleSequentialController::class, 'show'])->name('signatures.simple.show');
Route::get('/signatures-simple/{document}/view', [App\Http\Controllers\SimpleSequentialController::class, 'show'])->name('signatures.simple.view');
Route::get('/signatures-simple/{document}/process', [App\Http\Controllers\SimpleSequentialController::class, 'process'])->name('signatures.simple.process');
Route::get('/signatures-simple/{document}/{action}', [App\Http\Controllers\SimpleSequentialController::class, 'show'])->name('signatures.simple.show.action');
Route::post('/signatures-simple/{document}/sign', [App\Http\Controllers\SimpleSequentialController::class, 'sign'])->name('signatures.simple.sign');
Route::post('/signatures-simple/{document}/upload-signed', [App\Http\Controllers\SimpleSequentialController::class, 'uploadSigned'])->name('signatures.simple.upload-signed');
Route::post('/signatures-simple/{document}/save-signed-pdf', [App\Http\Controllers\SimpleSequentialController::class, 'saveSignedPdf'])->name('signatures.simple.save-signed-pdf');
Route::post('/signatures-simple/{document}/upload-signed-pdf', [App\Http\Controllers\SimpleSequentialController::class, 'uploadSignedPdf'])->name('signatures.simple.upload-signed-pdf');

// Routes pour servir les fichiers
Route::get('/storage/signed/{filename}', function ($filename) {
    $filePath = storage_path('app/public/documents/signed/' . $filename);
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404);
})->name('storage.signed');