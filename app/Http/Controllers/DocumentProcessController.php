<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentParaphe;
use App\Models\DocumentCachet;
use App\Models\User;
use App\Services\PdfSigningService;
use App\Services\NotificationService;
use App\Traits\CanProcessDocument;
use App\Events\DocumentSigned;
use App\Events\DocumentRefused;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentProcessController extends Controller
{
    use CanProcessDocument;
    
    // Services supprimés - Le frontend gère maintenant la génération PDF

    /**
     * Afficher la page de traitement unifiée
     */
    public function show(Document $document, string $action = 'sign')
    {
        // Vérifier les permissions
        if (!$this->canProcess($document)) {
            return redirect()->route('documents.pending')
                ->with('error', 'Vous n\'avez pas l\'autorisation de traiter ce document.');
        }

        // Si le document est déjà signé/paraphé, afficher en mode lecture seule
        if ($document->isSigned() || $document->isParaphed() || $document->isFullyProcessed()) {
            // Générer l'URL du PDF signé
            $pdfUrl = null;
            
            // Déterminer l'URL du PDF à afficher (signé ou original)
            $pdfUrl = $this->getSignedPdfUrl($document);

            $viewData = [
                'document' => $document,
                'pdfUrl' => $pdfUrl,
                'signatureUrl' => null, // Pas de signature en mode lecture seule
                'parapheUrl' => null,   // Pas de paraphe en mode lecture seule
                'cachetUrl' => null,    // Pas de cachet en mode lecture seule
                'cachetPUrl' => null,
                'cachetFUrl' => null,
                'hasCachetP' => false,
                'hasCachetF' => false,
                'formAction' => '#',    // Pas de soumission possible
                'backUrl' => route('documents.pending'),
                'allowSignature' => false,  // Désactiver la signature
                'allowParaphe' => false,    // Désactiver le paraphe
                'allowCachet' => false,     // Désactiver le cachet
                'allowBoth' => false,       // Désactiver les deux
                'allowAll' => false,        // Désactiver tout
                'defaultAction' => 'view_only',
                'actionTitle' => 'Document Signé',
                'actionIcon' => 'file-signature',
                'statusClass' => 'success',
                'statusIcon' => 'check-circle',
                'statusText' => $document->status_label,
                'submitText' => 'Document Signé',
                'isReadOnly' => true
            ];

            return view('documents.process', $viewData);
        }

        // Déterminer les actions disponibles
        $allowSignature = $this->canSign($document);
        $allowParaphe = $this->canParaphe($document);
        $allowCachet = $this->canCachet($document);
        $allowBoth = $allowSignature && $allowParaphe;
        $allowAll = $allowSignature && $allowParaphe && $allowCachet;

        // Configuration selon l'action
        $config = $this->getActionConfig($action, $allowSignature, $allowParaphe, $allowCachet, $allowBoth, $allowAll);
        
        // Générer l'URL du PDF avec encodage correct
        $pdfUrl = route('storage.documents', ['filename' => basename($document->path_original)]);
        
        // Obtenir les URLs des signatures, paraphes et cachets de l'utilisateur
        $user = auth()->user();
        $signatureUrl = $user->getSignatureUrl();
        $parapheUrl = $user->getParapheUrl();
        $cachetUrl = $user->getCachetUrl(); // Rétrocompatibilité (cachet P)
        $cachetPUrl = $user->getCachetPUrl();
        $cachetFUrl = $user->getCachetFUrl();

        // Données pour la vue
        $viewData = [
            'document' => $document,
            'pdfUrl' => $pdfUrl,
            'signatureUrl' => $signatureUrl,
            'parapheUrl' => $parapheUrl,
            'cachetUrl' => $cachetUrl, // Rétrocompatibilité
            'cachetPUrl' => $cachetPUrl,
            'cachetFUrl' => $cachetFUrl,
            'hasCachetP' => $user->hasCachetP(),
            'hasCachetF' => $user->hasCachetF(),
            'formAction' => route('documents.process.store', $document),
            'backUrl' => route('documents.pending'),
            'allowSignature' => $allowSignature,
            'allowParaphe' => $allowParaphe,
            'allowCachet' => $allowCachet,
            'allowBoth' => $allowBoth,
            'allowAll' => $allowAll,
            'defaultAction' => $config['defaultAction'],
            'actionTitle' => $config['actionTitle'],
            'actionIcon' => $config['actionIcon'],
            'statusClass' => $config['statusClass'],
            'statusIcon' => $config['statusIcon'],
            'statusText' => $config['statusText'],
            'submitText' => $config['submitText']
        ];

        return view('documents.process', $viewData);
    }

    /**
     * Traiter l'action sur le document
     */
    public function store(Request $request, Document $document)
    {
        // Log des données reçues// Vérifier les permissions
        if (!$this->canProcess($document)) {return redirect()->route('documents.pending')
                ->with('error', 'Vous n\'avez pas l\'autorisation de traiter ce document.');
        }

        // Validation
        try {
            $validated = $request->validate([
                'action_type' => 'required|in:sign_only,paraphe_only,cachet_only,both,sign_paraphe,sign_cachet,paraphe_cachet,all',
                'signature_comment' => 'nullable|string|max:500',
                'paraphe_comment' => 'nullable|string|max:500',
                'cachet_comment' => 'nullable|string|max:500',
                'signature_type' => 'nullable|in:png,live',
                'paraphe_type' => 'nullable|in:png,live',
                'cachet_type' => 'nullable|in:png,live',
                'live_signature_data' => 'nullable|string',
                'live_paraphe_data' => 'nullable|string',
                'live_cachet_data' => 'nullable|string',
                'signature_x' => 'nullable|numeric',
                'signature_y' => 'nullable|numeric',
                'paraphe_x' => 'nullable|numeric',
                'paraphe_y' => 'nullable|numeric',
                'cachet_x' => 'nullable|numeric',
                'cachet_y' => 'nullable|numeric',
            ]);
            
            // Assurer que les commentaires ont des valeurs par défaut
            $validated['signature_comment'] = $validated['signature_comment'] ?? '';
            $validated['paraphe_comment'] = $validated['paraphe_comment'] ?? '';
            $validated['cachet_comment'] = $validated['cachet_comment'] ?? '';
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {$actionType = $validated['action_type'];
            $user = auth()->user();// Déterminer les positions
            $signaturePosition = null;
            $paraphePosition = null;

            if (isset($validated['signature_x']) && isset($validated['signature_y'])) {
                $signaturePosition = [
                    'x' => $validated['signature_x'],
                    'y' => $validated['signature_y']
                ];
            }

            if (isset($validated['paraphe_x']) && isset($validated['paraphe_y'])) {
                $paraphePosition = [
                    'x' => $validated['paraphe_x'],
                    'y' => $validated['paraphe_y']
                ];
            }

            $processedPdfPath = null;

            // Le PDF signé est généré côté frontend et envoyé via uploadPdfToServer
            // Le backend ne fait que recevoir les coordonnées pour validation// Le PDF signé sera envoyé par le frontend via la route upload-signed-pdf
            // Attendre que le frontend envoie le PDF signé
            $processedPdfPath = null; // Sera défini par uploadSignedPdf// Les enregistrements de signature/paraphe seront créés par uploadSignedPdf
            // quand le frontend enverra le PDF signé
            
            // Pour l'instant, on ne fait que valider les données
            // Le frontend se chargera d'envoyer le PDF signé via uploadSignedPdf
            
            $message = 'Données validées - Le PDF signé sera généré côté client';
            
            // Retourner une réponse JSON pour le frontend
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('documents.process.show', [
                    'document' => $document->id,
                    'action' => 'view'
                ])
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du traitement : ' . $e->getMessage())
                ->withInput();
        }
    }

    // Méthodes supprimées - Le frontend gère maintenant la génération PDF

    /**
     * Mettre à jour le statut du document
     */
    private function updateDocumentStatus(Document $document, string $actionType)
    {
        switch ($actionType) {
            case 'sign_only':
                $document->update(['status' => Document::STATUS_SIGNED]);
                break;
            case 'paraphe_only':
                $document->update(['status' => Document::STATUS_PARAPHED]);
                break;
            case 'cachet_only':
                $document->update(['status' => 'cacheted']); // Nouveau statut pour cachet
                break;
            case 'both':
            case 'sign_paraphe':
                $document->update(['status' => Document::STATUS_SIGNED_AND_PARAPHED]);
                break;
            case 'sign_cachet':
                $document->update(['status' => 'signed_and_cacheted']);
                break;
            case 'paraphe_cachet':
                $document->update(['status' => 'paraphed_and_cacheted']);
                break;
            case 'all':
                $document->update(['status' => 'fully_processed']);
                break;
        }
    }

    /**
     * Obtenir le message de succès
     */
    private function getSuccessMessage(string $actionType): string
    {
        return match ($actionType) {
            'sign_only' => 'Document signé avec succès !',
            'paraphe_only' => 'Document paraphé avec succès !',
            'cachet_only' => 'Document cacheté avec succès !',
            'both' => 'Document signé et paraphé avec succès !',
            'sign_paraphe' => 'Document signé et paraphé avec succès !',
            'sign_cachet' => 'Document signé et cacheté avec succès !',
            'paraphe_cachet' => 'Document paraphé et cacheté avec succès !',
            'all' => 'Document entièrement traité avec succès !',
            default => 'Document traité avec succès !',
        };
    }

    /**
     * Envoyer les notifications par email (asynchrone)
     */
    private function sendNotifications(Document $document, string $actionType)
    {
        try {
            $signer = auth()->user();
            $agent = $document->uploader;

            if (!$agent) {
                \Log::warning('Agent non trouvé pour le document', ['document_id' => $document->id]);
                return;
            }

            // Envoyer les notifications de manière asynchrone
            dispatch(function () use ($document, $actionType, $signer, $agent) {
                $notificationService = new NotificationService();
                
                switch ($actionType) {
                    case 'sign_only':
                        $notificationService->notifyDocumentSigned($document, $signer, $agent);
                        break;
                    
                    case 'paraphe_only':
                        $notificationService->notifyDocumentParaphed($document, $signer, $agent);
                        break;
                    
                    case 'cachet_only':
                        // Pour l'instant, utiliser la même notification que le paraphe
                        $notificationService->notifyDocumentParaphed($document, $signer, $agent);
                        break;
                    
                    case 'both':
                    case 'sign_paraphe':
                        $notificationService->notifyDocumentFullyProcessed($document, $signer, $agent);
                        break;
                    
                    case 'sign_cachet':
                    case 'paraphe_cachet':
                    case 'all':
                        $notificationService->notifyDocumentFullyProcessed($document, $signer, $agent);
                        break;
                }});

        } catch (\Exception $e) {}
    }

    /**
     * Télécharger un document paraphé
     */
    public function download(Document $document)
    {
        $latestParaphe = $document->paraphes()->latest()->first();
        
        if (!$latestParaphe || !Storage::disk('public')->exists($latestParaphe->path_paraphed_pdf)) {
            return redirect()->back()->with('error', 'PDF paraphé non trouvé.');
        }

        return Storage::disk('public')->download($latestParaphe->path_paraphed_pdf);
    }

    /**
     * Obtenir la configuration selon l'action
     */
    private function getActionConfig(string $action, bool $allowSignature, bool $allowParaphe, bool $allowCachet, bool $allowBoth, bool $allowAll): array
    {
        $configs = [
            'sign' => [
                'defaultAction' => 'sign_only',
                'actionTitle' => 'Signer le Document',
                'actionIcon' => 'pen-fancy',
                'statusClass' => 'warning',
                'statusIcon' => 'clock',
                'statusText' => 'En Attente de Signature',
                'submitText' => 'Signer le Document'
            ],
            'paraphe' => [
                'defaultAction' => 'paraphe_only',
                'actionTitle' => 'Parapher le Document',
                'actionIcon' => 'pen-nib',
                'statusClass' => 'warning',
                'statusIcon' => 'clock',
                'statusText' => 'En Attente de Paraphe',
                'submitText' => 'Parapher le Document'
            ],
            'cachet' => [
                'defaultAction' => 'cachet_only',
                'actionTitle' => 'Cacheter le Document',
                'actionIcon' => 'stamp',
                'statusClass' => 'warning',
                'statusIcon' => 'clock',
                'statusText' => 'En Attente de Cachet',
                'submitText' => 'Cacheter le Document'
            ],
            'combined' => [
                'defaultAction' => $allowAll ? 'all' : ($allowBoth ? 'both' : 'sign_only'),
                'actionTitle' => 'Traitement Complet',
                'actionIcon' => 'pen-fancy',
                'statusClass' => 'warning',
                'statusIcon' => 'clock',
                'statusText' => 'En Attente de Traitement',
                'submitText' => 'Traiter le Document'
            ]
        ];

        return $configs[$action] ?? $configs['combined'];
    }
    
    /**
     * Vérifier si l'utilisateur peut cacheter le document
     */
    private function canCachet(Document $document): bool
    {
        $user = auth()->user();
        
        // Un agent peut cacheter ses propres documents
        if ($user->isAgent() && $document->uploaded_by === $user->id) {
            return true;
        }
        
        // Un signataire peut cacheter les documents qui lui sont assignés
        if ($user->isSignataire() && $document->signer_id === $user->id) {
            return true;
        }
        
        // Un admin peut cacheter tous les documents
        if ($user->isAdmin()) {
            return true;
        }
        
        return false;
    }

    /**
     * Obtenir l'URL du PDF signé ou original
     */
    private function getSignedPdfUrl(Document $document)
    {
        // Vérifier s'il existe un PDF signé stocké côté serveur
        $signedPdfPath = $this->getSignedPdfPath($document);
        
        if ($signedPdfPath && Storage::disk('public')->exists($signedPdfPath)) {return route('storage.signed', ['filename' => basename($signedPdfPath)]);
        }// Si pas de PDF signé stocké, afficher le PDF original
        return route('storage.documents', ['filename' => basename($document->path_original)]);
    }

    /**
     * Obtenir le chemin du PDF signé
     */
    private function getSignedPdfPath(Document $document)
    {
        // Chercher dans les signatures
        $signature = DocumentSignature::where('document_id', $document->id)->first();
        if ($signature && $signature->path_signed_pdf && $signature->path_signed_pdf !== 'frontend_generated') {return $signature->path_signed_pdf;
        }
        
        // Chercher dans les paraphes
        $paraphe = DocumentParaphe::where('document_id', $document->id)->first();
        if ($paraphe && $paraphe->path_paraphed_pdf && $paraphe->path_paraphed_pdf !== 'frontend_generated') {return $paraphe->path_paraphed_pdf;
        }return null;
    }

    /**
     * Uploader le PDF signé généré côté client
     */
    public function uploadSignedPdf(Request $request)
    {
        try {$request->validate([
                'signed_pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
                'document_id' => 'required|integer|exists:documents,id'
            ]);

            $document = Document::findOrFail($request->document_id);
            
            // Vérifier les permissions
            if (!$this->canProcess($document)) {
                \Log::warning('Upload PDF signé - Permission refusée', ['document_id' => $document->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas l\'autorisation de traiter ce document.'
                ], 403);
            }

            // Obtenir le fichier uploadé
            $uploadedFile = $request->file('signed_pdf');
            
            // Générer un nom de fichier unique
            $filename = 'signed_' . $document->id . '_' . time() . '.pdf';
            
            // Vérifier que le répertoire existe
            $signedDir = storage_path('app/public/documents/signed');
            if (!file_exists($signedDir)) {
                mkdir($signedDir, 0755, true);}
            
            // Stocker le fichier dans le répertoire signed
            $path = $uploadedFile->storeAs('documents/signed', $filename, 'public');// Créer ou mettre à jour les enregistrements de signature/paraphe avec le chemin du PDF signé
            $user = auth()->user();
            
            // Créer ou mettre à jour l'enregistrement de signature
            $signature = DocumentSignature::where('document_id', $document->id)->first();
            if (!$signature) {
                $signature = DocumentSignature::create([
                    'document_id' => $document->id,
                    'signed_by' => $user->id,
                    'signed_at' => now(),
                    'signature_comment' => 'PDF généré côté client',
                    'path_signed_pdf' => $path,
                    'signature_type' => 'client_generated'
                ]);} else {
                $signature->update(['path_signed_pdf' => $path]);}
            
            // Mettre à jour le statut du document
            $document->update([
                'status' => 'signed',
                'signed_at' => now()
            ]);

            // Déclencher l'événement DocumentSigned pour envoyer les notifications
            DocumentSigned::dispatch($document, $signature);

            \Log::info('Événement DocumentSigned déclenché', [
                'document_id' => $document->id,
                'signature_id' => $signature->id,
                'uploader_email' => $document->uploader->email ?? 'N/A'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF signé stocké avec succès',
                'path' => $path,
                'filename' => $filename
            ]);

        } catch (\Exception $e) {return response()->json([
                'success' => false,
                'message' => 'Erreur lors du stockage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si l'utilisateur peut traiter ce document
     */
    private function canProcess(Document $document)
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Vérifier si l'utilisateur est le signataire du document
        if ($document->signer_id === $user->id) {
            return true;
        }

        // Vérifier si l'utilisateur a le rôle signataire
        if ($user->role && $user->role->name === 'signataire') {
            return true;
        }

        return false;
    }
}
