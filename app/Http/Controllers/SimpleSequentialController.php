<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SequentialSignature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SimpleSequentialController extends Controller
{
    /**
     * Afficher la liste des documents en attente de signature séquentielle
     */
    public function index()
    {
        try {
            // Vérification simple d'authentification
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Vérifier que l'utilisateur n'est pas un administrateur
            if ($user->isAdmin()) {
                return redirect()->back()->with('error', 'Les administrateurs ne peuvent pas accéder aux signatures séquentielles.');
            }
            
            $userId = $user->id;
            
            // Récupérer tous les documents avec signatures séquentielles (paginés)
            $allDocuments = Document::where('sequential_signatures', true)
                ->whereIn('status', ['in_progress', 'pending'])
                ->whereHas('sequentialSignatures', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with(['sequentialSignatures.user', 'uploader'])
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
                
            
            // Catégoriser les documents de la page actuelle
            $documentsToSign = collect();
            $documentsWaiting = collect();
            $documentsCompleted = collect();
            
            foreach ($allDocuments as $document) {
                $userSignature = $document->sequentialSignatures()
                    ->where('user_id', $userId)
                    ->first();
                
                if ($userSignature) {
                    if ($userSignature->status === 'signed') {
                        $documentsCompleted->push($document);
                    } elseif ($userSignature->signature_order == $document->current_signature_index + 1) {
                        $documentsToSign->push($document);
                    } else {
                        $documentsWaiting->push($document);
                    }
                }
            }
            
            // Statistiques (pour toutes les pages)
            $totalStats = Document::where('sequential_signatures', true)
                ->whereIn('status', ['in_progress', 'pending'])
                ->whereHas('sequentialSignatures', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();
            
            $totalToSign = 0;
            $totalWaiting = 0;
            $totalCompleted = 0;
            
            foreach ($totalStats as $document) {
                $userSignature = $document->sequentialSignatures()
                    ->where('user_id', $userId)
                    ->first();
                
                if ($userSignature) {
                    if ($userSignature->status === 'signed') {
                        $totalCompleted++;
                    } elseif ($userSignature->signature_order == $document->current_signature_index + 1) {
                        $totalToSign++;
                    } else {
                        $totalWaiting++;
                    }
                }
            }
            
            $stats = [
                'total' => $totalStats->count(),
                'to_sign' => $totalToSign,
                'waiting' => $totalWaiting,
                'completed' => $totalCompleted
            ];
                
            return view('signatures.simple-sequential', compact(
                'documentsToSign', 
                'documentsWaiting', 
                'documentsCompleted', 
                'stats',
                'allDocuments'
            ));
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une page d'erreur simple
            return response()->view('errors.simple', [
                'message' => 'Erreur lors du chargement des signatures séquentielles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher le formulaire de signature pour un document (interface identique à documents/process)
     */
    public function show(Document $document, string $action = 'sign')
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }
        
        // Détecter automatiquement l'action 'view' si l'URL contient '/view'
        if (request()->is('*view*') || $action === 'view') {
            $action = 'view';
        }
        
        // Si l'action est 'view', afficher le document signé (comme DocumentProcessController)
        if ($action === 'view') {
            // Vérifier si le document est signé
            if ($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed()) {
                // Générer l'URL du PDF signé
                $pdfUrl = $this->getSignedPdfUrl($document);
                
                // Créer un "virtual signer" pour éviter l'erreur dans la vue
                $document->signer = (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
                
                $viewData = [
                    'document' => $document,
                    'pdfUrl' => $pdfUrl,
                    'signatureUrl' => '/signatures/user-signature',
                    'parapheUrl' => '/signatures/user-paraphe',
                    'formAction' => route('signatures.simple.save-signed-pdf', $document),
                    'uploadUrl' => route('signatures.simple.upload-signed-pdf', $document),
                    'redirectUrl' => route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']),
                    'defaultAction' => 'signature',
                    'allowSignature' => false,
                    'allowParaphe' => false,
                    'allowBoth' => false,
                    'isReadOnly' => true,
                    'statusIcon' => 'check-circle',
                    'statusText' => 'Document signé',
                    'useViewControls' => true  // Indicateur pour utiliser les contrôles de vue
                ];
                
                // Récupérer les informations des signatures séquentielles
                $sequentialSignatures = $document->sequentialSignatures()
                    ->with('user')
                    ->orderBy('signature_order')
                    ->get();
                    
                $completedSignatures = $sequentialSignatures->where('status', 'signed');
                $pendingSignatures = $sequentialSignatures->where('status', 'pending');
                $currentSigner = $sequentialSignatures->where('signature_order', $document->current_signature_index + 1)->first();
                $nextSigner = $sequentialSignatures->where('signature_order', $document->current_signature_index + 2)->first();
                
                // Ajouter les variables séquentielles
                $viewData = array_merge($viewData, [
                    'sequentialSignatures' => $sequentialSignatures,
                    'completedSignatures' => $completedSignatures,
                    'pendingSignatures' => $pendingSignatures,
                    'currentSigner' => $currentSigner,
                    'nextSigner' => $nextSigner,
                    'displayPdfUrl' => $pdfUrl
                ]);
                
                return view('documents.process', $viewData);
            } else {
                // Si le document n'est pas encore signé, rediriger vers la signature
                return redirect()->route('signatures.simple.show', $document);
            }
        }
        
        // Vérification séquentielle : est-ce le tour de cet utilisateur ?
        $currentSignature = $document->sequentialSignatures()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
            
        if (!$currentSignature) {
            return redirect()->route('signatures.simple.index')
                ->with('error', 'Ce n\'est pas encore votre tour de signer ce document.');
        }

        // Variables identiques à documents/process.blade.php
        $pdfUrl = Storage::url($document->path_original);
        $signatureUrl = '/signatures/user-signature';
        $parapheUrl = '/signatures/user-paraphe';
        $formAction = route('signatures.simple.save-signed-pdf', $document);
        $uploadUrl = route('signatures.simple.upload-signed-pdf', $document);
        $redirectUrl = route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']);
        $defaultAction = 'signature';
        $allowSignature = true;
        $allowParaphe = true;
        $allowBoth = true;
        $isReadOnly = false;
        
        // Statut du document
        $statusIcon = 'clock';
        $statusText = 'En cours de signature';
        
        // Pour les signatures séquentielles, créer un signer virtuel
        $document->signer = (object) [
            'name' => 'Signatures Séquentielles',
            'id' => null
        ];
        
        // Récupérer les informations des signatures séquentielles
        $sequentialSignatures = $document->sequentialSignatures()
            ->with('user')
            ->orderBy('signature_order')
            ->get();
            
        $completedSignatures = $sequentialSignatures->where('status', 'signed');
        $pendingSignatures = $sequentialSignatures->where('status', 'pending');
        $currentSigner = $sequentialSignatures->where('signature_order', $document->current_signature_index + 1)->first();
        $nextSigner = $sequentialSignatures->where('signature_order', $document->current_signature_index + 2)->first();
        
        // Déterminer l'URL du PDF à afficher
        $displayPdfUrl = $pdfUrl;
        if ($document->status === 'signed') {
            // Si le document est signé, afficher le PDF signé
            $latestSignature = $document->signatures()->latest()->first();
            if ($latestSignature && $latestSignature->path_signed_pdf) {
                $displayPdfUrl = Storage::url($latestSignature->path_signed_pdf);
                // Debug temporaire
                \Log::info('SimpleSequentialController - displayPdfUrl généré', [
                    'document_id' => $document->id,
                    'status' => $document->status,
                    'path_signed_pdf' => $latestSignature->path_signed_pdf,
                    'displayPdfUrl' => $displayPdfUrl
                ]);
            }
        }

        // Utiliser exactement la même vue que les signatures simples
        return view('documents.process', compact(
            'document', 
            'currentSignature',
            'pdfUrl',
            'displayPdfUrl',
            'signatureUrl', 
            'parapheUrl',
            'formAction',
            'uploadUrl',
            'redirectUrl',
            'defaultAction',
            'allowSignature',
            'allowParaphe',
            'allowBoth',
            'isReadOnly',
            'statusIcon',
            'statusText',
            'sequentialSignatures',
            'completedSignatures',
            'pendingSignatures',
            'currentSigner',
            'nextSigner'
        ));
    }

    /**
     * Traiter la signature (logique séquentielle)
     */
    public function sign(Request $request, Document $document)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour signer ce document.'
            ], 401);
        }
        
        // Vérification séquentielle : est-ce le tour de cet utilisateur ?
        $currentSignature = $document->sequentialSignatures()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
            
        if (!$currentSignature) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de signer ce document.'
            ], 403);
        }

        // Utiliser exactement la même logique que DocumentProcessController
        return app(\App\Http\Controllers\DocumentProcessController::class)->store($request, $document);
    }

    /**
     * Traiter le paraphe (logique séquentielle)
     */
    public function paraphe(Request $request, Document $document)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour parapher ce document.'
            ], 401);
        }
        
        // Vérification séquentielle : est-ce le tour de cet utilisateur ?
        $currentSignature = $document->sequentialSignatures()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
            
        if (!$currentSignature) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de parapher ce document.'
            ], 403);
        }

        // Utiliser exactement la même logique que DocumentProcessController pour le paraphe
        return app(\App\Http\Controllers\DocumentProcessController::class)->store($request, $document);
    }

    /**
     * Traiter la signature ET le paraphe (logique séquentielle)
     */
    public function signAndParaphe(Request $request, Document $document)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour signer et parapher ce document.'
            ], 401);
        }
        
        // Vérification séquentielle : est-ce le tour de cet utilisateur ?
        $currentSignature = $document->sequentialSignatures()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
            
        if (!$currentSignature) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de signer et parapher ce document.'
            ], 403);
        }

        // Utiliser exactement la même logique que DocumentProcessController pour signature + paraphe
        return app(\App\Http\Controllers\DocumentProcessController::class)->store($request, $document);
    }

    /**
     * Sauvegarder le PDF signé (logique séquentielle)
     */
    public function saveSignedPdf(Request $request, Document $document)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour signer ce document.'
            ], 401);
        }
        
        // Vérification séquentielle : est-ce le tour de cet utilisateur ?
        $currentSignature = $document->sequentialSignatures()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
            
        if (!$currentSignature) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de signer ce document.'
            ], 403);
        }

        // Utiliser exactement la même logique que DocumentProcessController
        $result = app(\App\Http\Controllers\DocumentProcessController::class)->store($request, $document);
        
        // Vérifier le type de réponse
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            // Si c'est une redirection, considérer comme succès
            $success = true;
        } elseif ($result instanceof \Illuminate\Http\JsonResponse) {
            // Si c'est une réponse JSON, vérifier le succès
            $success = $result->getData()->success ?? false;
        } else {
            // Autres types de réponse
            $success = true;
        }
        
        // Après la signature, gérer la logique séquentielle
        if ($success) {
            // LOGIQUE SÉQUENTIELLE : Passer au signataire suivant
            $document->current_signature_index++;
            $document->last_signature_at = now();
            
            // Vérifier si toutes les signatures sont terminées
            $totalSigners = $document->sequentialSignatures()->count();
            if ($document->current_signature_index >= $totalSigners) {
                // Toutes les signatures sont terminées
                $document->status = 'signed';
                $document->sequential_signatures = false;
                
                // Notifier l'agent que le document est complètement signé
                $this->notifyDocumentCompleted($document);
            } else {
                // Notifier le prochain signataire
                $nextSigner = $document->sequentialSignatures()
                    ->where('signature_order', $document->current_signature_index + 1)
                    ->first();
                
                if ($nextSigner) {
                    $this->notifyNextSigner($document, $nextSigner->user, $user);
                }
            }
            
            $document->save();
            
            // Retourner une réponse avec redirection vers la page de visualisation (comme DocumentProcessController)
            $redirectUrl = route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']);
            
            
            return response()->json([
                'success' => true,
                'message' => 'Signature enregistrée avec succès',
                'redirect' => $redirectUrl
            ]);
        }
        
        return $result;
    }

    /**
     * Uploader le PDF signé (logique séquentielle)
     */
    public function uploadSignedPdf(Request $request, Document $document)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour signer ce document.'
            ], 401);
        }
        
        // Vérification séquentielle : est-ce le tour de cet utilisateur ?
        $currentSignature = $document->sequentialSignatures()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('signature_order', $document->current_signature_index + 1)
            ->first();
            
        if (!$currentSignature) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de signer ce document.'
            ], 403);
        }

        // Ajouter le document_id à la requête pour DocumentProcessController
        $request->merge(['document_id' => $document->id]);
        
        // Utiliser exactement la même logique que DocumentProcessController
        $result = app(\App\Http\Controllers\DocumentProcessController::class)->uploadSignedPdf($request);
        
        // Vérifier le type de réponse
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            // Si c'est une redirection, considérer comme succès
            $success = true;
        } elseif ($result instanceof \Illuminate\Http\JsonResponse) {
            // Si c'est une réponse JSON, vérifier le succès
            $success = $result->getData()->success ?? false;
        } else {
            // Autres types de réponse
            $success = true;
        }
        
        // Après l'upload, gérer la logique séquentielle
        if ($success) {
            // Marquer la signature séquentielle comme complétée
            $currentSignature->update([
                'status' => 'signed',
                'signed_at' => now(),
                'signature_data' => [
                    'timestamp' => now()->toISOString(),
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'signature_order' => $currentSignature->signature_order
                ]
            ]);
            
            // LOGIQUE SÉQUENTIELLE : Passer au signataire suivant
            $document->current_signature_index++;
            $document->last_signature_at = now();
            
            // Vérifier si toutes les signatures sont terminées
            $totalSigners = $document->sequentialSignatures()->count();
            if ($document->current_signature_index >= $totalSigners) {
                // Toutes les signatures sont terminées
                $document->status = 'signed';
                $document->sequential_signatures = false;
                
                // Notifier l'agent que le document est complètement signé
                $this->notifyDocumentCompleted($document);
            } else {
                // Notifier le prochain signataire
                $nextSigner = $document->sequentialSignatures()
                    ->where('signature_order', $document->current_signature_index + 1)
                    ->first();
                
                if ($nextSigner) {
                    $this->notifyNextSigner($document, $nextSigner->user, $user);
                }
            }
            
            $document->save();
            
            // Retourner une réponse avec redirection vers la page de visualisation (comme DocumentProcessController)
            $redirectUrl = route('signatures.simple.show.action', ['document' => $document, 'action' => 'view']);
            
            
            return response()->json([
                'success' => true,
                'message' => 'PDF signé enregistré avec succès',
                'redirect' => $redirectUrl
            ]);
        }
        
        return $result;
    }

    /**
     * Notifier que le document est complètement signé
     */
    private function notifyDocumentCompleted(Document $document)
    {
        try {
            // Notifier l'agent qui a uploadé le document
            $agent = $document->uploader;
            if ($agent) {
                // Ici vous pouvez ajouter une notification email ou autre
                // Document complètement signé
            }
        } catch (\Exception $e) {
            // Erreur notification
        }
    }

    /**
     * Notifier le prochain signataire
     */
    private function notifyNextSigner(Document $document, $nextSigner, $currentUser)
    {
        try {
            // Ici vous pouvez ajouter une notification email ou autre
            // Prochain signataire notifié
        } catch (\Exception $e) {
            // Erreur notification
        }
    }

    /**
     * Obtenir l'URL du PDF signé (copié de DocumentProcessController)
     */
    private function getSignedPdfUrl(Document $document)
    {
        // Vérifier s'il existe un PDF signé stocké côté serveur
        $signedPdfPath = $this->getSignedPdfPath($document);
        
        if ($signedPdfPath && Storage::disk('public')->exists($signedPdfPath)) {
            // Utiliser Storage::url() qui génère l'URL correcte via le lien symbolique
            return Storage::url($signedPdfPath);
        }
        
        // Si pas de PDF signé stocké, afficher le PDF original
        return Storage::url($document->path_original);
    }

    /**
     * Obtenir le chemin du PDF signé
     */
    private function getSignedPdfPath(Document $document)
    {
        // Chercher dans les signatures
        $signature = \App\Models\DocumentSignature::where('document_id', $document->id)->first();
        if ($signature && $signature->path_signed_pdf && $signature->path_signed_pdf !== 'frontend_generated') {
            return $signature->path_signed_pdf;
        }
        
        // Chercher dans les paraphes
        $paraphe = \App\Models\DocumentParaphe::where('document_id', $document->id)->first();
        if ($paraphe && $paraphe->path_paraphed_pdf && $paraphe->path_paraphed_pdf !== 'frontend_generated') {
            return $paraphe->path_paraphed_pdf;
        }
        
        return null;
    }

}
