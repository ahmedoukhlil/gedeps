<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentParaphe;
use App\Models\User;
use App\Services\PdfSigningService;
use App\Services\NotificationService;
use App\Traits\CanProcessDocument;
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
        \Log::info('DocumentProcessController::show - Vérification du statut:', [
            'document_id' => $document->id,
            'status' => $document->status,
            'isSigned' => $document->isSigned(),
            'isParaphed' => $document->isParaphed(),
            'isFullyProcessed' => $document->isFullyProcessed()
        ]);
        
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
                'formAction' => '#',    // Pas de soumission possible
                'backUrl' => route('documents.pending'),
                'allowSignature' => false,  // Désactiver la signature
                'allowParaphe' => false,    // Désactiver le paraphe
                'allowBoth' => false,       // Désactiver les deux
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
        $allowBoth = $allowSignature && $allowParaphe;

        // Configuration selon l'action
        $config = $this->getActionConfig($action, $allowSignature, $allowParaphe, $allowBoth);
        
        // Générer l'URL du PDF avec encodage correct
        $pdfUrl = route('storage.documents', ['filename' => basename($document->path_original)]);
        
        // Obtenir les URLs des signatures et paraphes de l'utilisateur
        $user = auth()->user();
        $signatureUrl = $user->getSignatureUrl();
        $parapheUrl = $user->getParapheUrl();
        
        // Données pour la vue
        $viewData = [
            'document' => $document,
            'pdfUrl' => $pdfUrl,
            'signatureUrl' => $signatureUrl,
            'parapheUrl' => $parapheUrl,
            'formAction' => route('documents.process.store', $document),
            'backUrl' => route('documents.pending'),
            'allowSignature' => $allowSignature,
            'allowParaphe' => $allowParaphe,
            'allowBoth' => $allowBoth,
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
        // Log des données reçues
        \Log::info('DocumentProcessController::store - Données reçues:', $request->all());
        
        // Vérifier les permissions
        if (!$this->canProcess($document)) {
            \Log::error('DocumentProcessController::store - Permissions refusées pour document: ' . $document->id);
            return redirect()->route('documents.pending')
                ->with('error', 'Vous n\'avez pas l\'autorisation de traiter ce document.');
        }

        // Validation
        try {
            $validated = $request->validate([
                'action_type' => 'required|in:sign_only,paraphe_only,both',
                'signature_comment' => 'nullable|string|max:500',
                'paraphe_comment' => 'nullable|string|max:500',
                'signature_type' => 'required|in:png,live',
                'paraphe_type' => 'required|in:png,live',
                'live_signature_data' => 'nullable|string',
                'live_paraphe_data' => 'nullable|string',
                'signature_x' => 'nullable|numeric',
                'signature_y' => 'nullable|numeric',
                'paraphe_x' => 'nullable|numeric',
                'paraphe_y' => 'nullable|numeric',
            ]);
            
            // Assurer que les commentaires ont des valeurs par défaut
            $validated['signature_comment'] = $validated['signature_comment'] ?? '';
            $validated['paraphe_comment'] = $validated['paraphe_comment'] ?? '';
            
            \Log::info('DocumentProcessController::store - Validation réussie:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('DocumentProcessController::store - Erreur de validation:', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            \Log::info('DocumentProcessController::store - Début du traitement après validation');
            $actionType = $validated['action_type'];
            $user = auth()->user();
            \Log::info('DocumentProcessController::store - Action type: ' . $actionType . ', User: ' . $user->id);

            // Déterminer les positions
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
            // Le backend ne fait que recevoir les coordonnées pour validation
            \Log::info('DocumentProcessController::store - PDF signé généré côté frontend');
            \Log::info('DocumentProcessController::store - Coordonnées reçues pour validation:', [
                'signature_x' => $validated['signature_x'] ?? null,
                'signature_y' => $validated['signature_y'] ?? null,
                'paraphe_x' => $validated['paraphe_x'] ?? null,
                'paraphe_y' => $validated['paraphe_y'] ?? null,
                'action_type' => $actionType
            ]);
            
            // Le PDF signé sera envoyé par le frontend via la route upload-signed-pdf
            // Attendre que le frontend envoie le PDF signé
            $processedPdfPath = null; // Sera défini par uploadSignedPdf
            
            \Log::info('DocumentProcessController::store - Attente du PDF signé du frontend');
            
            // Les enregistrements de signature/paraphe seront créés par uploadSignedPdf
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
            case 'both':
                $document->update(['status' => Document::STATUS_SIGNED_AND_PARAPHED]);
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
            'both' => 'Document signé et paraphé avec succès !',
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
                    
                    case 'both':
                        $notificationService->notifyDocumentFullyProcessed($document, $signer, $agent);
                        break;
                }

                \Log::info('Notifications envoyées (asynchrone)', [
                    'document_id' => $document->id,
                    'action_type' => $actionType,
                    'signer' => $signer->name,
                    'agent' => $agent->name
                ]);
            });

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi des notifications', [
                'document_id' => $document->id,
                'action_type' => $actionType,
                'error' => $e->getMessage()
            ]);
        }
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
    private function getActionConfig(string $action, bool $allowSignature, bool $allowParaphe, bool $allowBoth): array
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
            'combined' => [
                'defaultAction' => 'both',
                'actionTitle' => 'Signature & Paraphe',
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
     * Obtenir l'URL du PDF signé ou original
     */
    private function getSignedPdfUrl(Document $document)
    {
        // Vérifier s'il existe un PDF signé stocké côté serveur
        $signedPdfPath = $this->getSignedPdfPath($document);
        
        if ($signedPdfPath && Storage::disk('public')->exists($signedPdfPath)) {
            \Log::info('PDF signé trouvé', [
                'document_id' => $document->id,
                'path' => $signedPdfPath,
                'url' => route('storage.signed', ['filename' => basename($signedPdfPath)])
            ]);
            return route('storage.signed', ['filename' => basename($signedPdfPath)]);
        }
        
        \Log::info('PDF signé non trouvé, utilisation du PDF original', [
            'document_id' => $document->id,
            'original_path' => $document->path_original
        ]);
        
        // Si pas de PDF signé stocké, afficher le PDF original
        return route('storage.documents', ['filename' => basename($document->path_original)]);
    }

    /**
     * Obtenir le chemin du PDF signé
     */
    private function getSignedPdfPath(Document $document)
    {
        // Chercher dans les signatures
        $signature = DocumentSignature::where('document_id', $document->id)->first();
        if ($signature && $signature->path_signed_pdf && $signature->path_signed_pdf !== 'frontend_generated') {
            \Log::info('PDF signé trouvé dans signature', [
                'document_id' => $document->id,
                'signature_id' => $signature->id,
                'path' => $signature->path_signed_pdf
            ]);
            return $signature->path_signed_pdf;
        }
        
        // Chercher dans les paraphes
        $paraphe = DocumentParaphe::where('document_id', $document->id)->first();
        if ($paraphe && $paraphe->path_paraphed_pdf && $paraphe->path_paraphed_pdf !== 'frontend_generated') {
            \Log::info('PDF signé trouvé dans paraphe', [
                'document_id' => $document->id,
                'paraphe_id' => $paraphe->id,
                'path' => $paraphe->path_paraphed_pdf
            ]);
            return $paraphe->path_paraphed_pdf;
        }
        
        \Log::info('Aucun PDF signé trouvé', [
            'document_id' => $document->id,
            'signature_exists' => $signature ? true : false,
            'paraphe_exists' => $paraphe ? true : false,
            'signature_path' => $signature ? $signature->path_signed_pdf : null,
            'paraphe_path' => $paraphe ? $paraphe->path_paraphed_pdf : null
        ]);
        
        return null;
    }

    /**
     * Uploader le PDF signé généré côté client
     */
    public function uploadSignedPdf(Request $request)
    {
        try {
            \Log::info('Upload PDF signé - Début', [
                'document_id' => $request->document_id,
                'has_file' => $request->hasFile('signed_pdf'),
                'file_size' => $request->hasFile('signed_pdf') ? $request->file('signed_pdf')->getSize() : 0
            ]);

            $request->validate([
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
                mkdir($signedDir, 0755, true);
                \Log::info('Répertoire signed créé: ' . $signedDir);
            }
            
            // Stocker le fichier dans le répertoire signed
            $path = $uploadedFile->storeAs('documents/signed', $filename, 'public');
            
            \Log::info('PDF signé stocké', [
                'path' => $path,
                'filename' => $filename,
                'full_path' => storage_path('app/public/' . $path),
                'exists' => file_exists(storage_path('app/public/' . $path))
            ]);
            
            // Créer ou mettre à jour les enregistrements de signature/paraphe avec le chemin du PDF signé
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
                ]);
                \Log::info('Signature créée', ['signature_id' => $signature->id, 'path' => $path]);
            } else {
                $signature->update(['path_signed_pdf' => $path]);
                \Log::info('Signature mise à jour', ['signature_id' => $signature->id, 'path' => $path]);
            }
            
            // Mettre à jour le statut du document
            $document->update([
                'status' => 'signed',
                'signed_at' => now()
            ]);
            
            \Log::info('Document mis à jour', [
                'document_id' => $document->id,
                'status' => 'signed',
                'path' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF signé stocké avec succès',
                'path' => $path,
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'upload du PDF signé: ' . $e->getMessage(), [
                'document_id' => $request->document_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
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
