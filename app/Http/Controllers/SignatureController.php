<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Services\PdfSignatureService;
use App\Events\DocumentSigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    /**
     * Afficher les documents à signer pour le signataire connecté
     */
    public function index()
    {
        // Vérifier que l'utilisateur n'est pas un administrateur
        if (auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Les administrateurs ne peuvent pas signer des documents.');
        }

        $documents = Document::where('signer_id', auth()->id())
            ->where('status', 'pending')
            ->with(['uploader'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('signatures.index', compact('documents'));
    }

    /**
     * Afficher un document pour signature
     * Redirige vers la route unifiée
     */
    public function show(Document $document)
    {
        // Vérifier que l'utilisateur n'est pas un administrateur
        if (auth()->user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Les administrateurs ne peuvent pas signer des documents.');
        }
        
        // Vérifier que l'utilisateur est le signataire assigné
        if ($document->signer_id !== auth()->id()) {
            return redirect()->route('signatures.index')->with('error', 'Document non trouvé.');
        }

        // Rediriger vers la route unifiée
        return redirect()->route('documents.process.show', ['document' => $document, 'action' => 'sign']);
    }

    /**
     * Signer un document
     */
    public function sign(Request $request, Document $document)
    {
        // Vérifier que l'utilisateur est le signataire assigné
        if ($document->signer_id !== auth()->id()) {
            return redirect()->route('signatures.index')->with('error', 'Document non trouvé.');
        }

            $validated = $request->validate([
                'signature_comment' => 'nullable|string|max:1000',
                'signature_type' => 'required|in:live,png',
                'live_signature_data' => 'nullable|string',
                'signature_x' => 'nullable|numeric|min:0',
                'signature_y' => 'nullable|numeric|min:0',
                'signature_positions' => 'nullable|array',
                'total_pages' => 'nullable|integer|min:1',
                'is_multi_page' => 'nullable|boolean',
            ]);

        try {
            $signatureType = $validated['signature_type'];
            
            // Vérifier selon le type de signature
            if ($signatureType === 'png') {
                // Vérifier que l'utilisateur a une signature PNG
                if (!auth()->user()->hasSignature()) {
                    return redirect()->back()
                        ->with('error', 'Vous devez avoir une signature PNG assignée par l\'administrateur pour utiliser ce type de signature.');
                }
            } elseif ($signatureType === 'live') {
                // Vérifier que les données de signature live sont présentes
                if (empty($validated['live_signature_data'])) {
                    return redirect()->back()
                        ->with('error', 'Veuillez signer dans la zone de signature avant de soumettre.');
                }
            }

            // Créer le PDF signé
            $pdfSignatureService = new PdfSignatureService();
            // Préparer la position personnalisée si fournie
            $customPosition = null;
            if (isset($validated['signature_x']) && isset($validated['signature_y'])) {
                $customPosition = [
                    'x' => $validated['signature_x'],
                    'y' => $validated['signature_y']
                ];
            }

            $signedPdfPath = $pdfSignatureService->signDocument(
                $document, 
                auth()->user(), 
                $validated['signature_comment'],
                $signatureType,
                $validated['live_signature_data'] ?? null,
                $customPosition
            );

            // Créer l'enregistrement de signature
            $signatureData = [
                'document_id' => $document->id,
                'signed_by' => auth()->id(),
                'signed_at' => now(),
                'signature_comment' => $validated['signature_comment'],
                'path_signed_pdf' => $signedPdfPath,
                'signature_type' => $signatureType,
            ];
            
            // Ajouter les données multi-pages si présentes
            if (isset($validated['signature_positions'])) {
                $signatureData['signature_positions'] = $validated['signature_positions'];
                $signatureData['is_multi_page'] = $validated['is_multi_page'] ?? false;
                $signatureData['total_pages'] = $validated['total_pages'] ?? 1;
            }
            
            $signature = DocumentSignature::create($signatureData);

            // Mettre à jour le statut du document
            $document->update(['status' => 'signed']);

            // Déclencher l'événement DocumentSigned pour envoyer les notifications
            DocumentSigned::dispatch($document, $signature);

            $signatureTypeText = $signatureType === 'live' ? 'signature live' : 'signature PNG';
            return redirect()->route('signatures.index')
                ->with('success', "Document signé avec succès avec une {$signatureTypeText} ! Le PDF signé a été généré.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la signature : ' . $e->getMessage());
        }
    }

    /**
     * Ouvrir le PDF signé dans un nouvel onglet
     */
    public function downloadSigned(Document $document)
    {
        // Vérifier que l'utilisateur a le droit de voir ce document
        if ($document->signer_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return redirect()->route('signatures.index')->with('error', 'Accès non autorisé.');
        }

        // Récupérer la signature du document
        $signature = $document->signatures()->latest()->first();if (!$signature || !$signature->path_signed_pdf) {
            return redirect()->back()->with('error', 'Aucun PDF signé trouvé pour ce document.');
        }

        // Chercher le fichier dans le nouveau répertoire documents/signed
        $signedDocumentsPath = storage_path('app/public/documents/signed');
        $fileName = basename($signature->path_signed_pdf);
        $fullPath = $signedDocumentsPath . '/' . $fileName;if (!file_exists($fullPath)) {
            // Essayer de trouver le fichier par pattern (plus robuste)
            $alternativePath = null;
            if (is_dir($signedDocumentsPath)) {
                $filesInDirectory = scandir($signedDocumentsPath);
                $filesInDirectory = array_filter($filesInDirectory, function($file) {
                    return $file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
                });
                
                // Chercher un fichier qui contient l'ID du document
                $documentId = $document->id;
                foreach ($filesInDirectory as $file) {
                    if (strpos($file, "document_signe_{$documentId}_") === 0 || 
                        strpos($file, "signed_") === 0) {
                        $alternativePath = $signedDocumentsPath . '/' . $file;
                        break;
                    }
                }
            }if ($alternativePath && file_exists($alternativePath)) {
                $fullPath = $alternativePath;} else {
                return redirect()->back()->with('error', 'Le fichier PDF signé n\'existe plus dans le répertoire documents/signed.');
            }
        }

        // Ouvrir le fichier dans un nouvel onglet
        $mimeType = mime_content_type($fullPath);
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="signed_' . $document->filename_original . '"'
        ]);
    }

    /**
     * Générer un certificat de signature
     */
    public function generateCertificate(Document $document)
    {
        // Vérifier que l'utilisateur a le droit de voir ce document
        if ($document->signer_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return redirect()->route('signatures.index')->with('error', 'Accès non autorisé.');
        }

        // Récupérer la signature du document
        $signature = $document->signatures()->latest()->first();
        
        if (!$signature) {
            return redirect()->back()->with('error', 'Aucune signature trouvée pour ce document.');
        }

        try {
            // Générer le certificat
            $pdfSignatureService = new PdfSignatureService();
            $certificatePath = $pdfSignatureService->generateSignatureCertificate(
                $document,
                $signature->signer,
                $signature->signature_comment
            );

            // Télécharger le certificat
            $filename = 'certificat_signature_' . $document->id . '_' . time() . '.pdf';
            
            return response()->download($certificatePath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la génération du certificat : ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder le PDF signé
     */
    public function saveSignedPdf(Request $request)
    {
        try {
            // Logs de debug pour voir ce qui est reçu// Valider les données
            $request->validate([
                'signed_pdf' => 'required|file|mimes:pdf',
                'document_id' => 'required|integer',
                'signature_data' => 'required|string'
            ]);$documentId = $request->input('document_id');
            $signatureDataJson = $request->input('signature_data');
            $signatureData = json_decode($signatureDataJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Données de signature invalides: ' . json_last_error_msg());
            }// Vérifier que le document existe et appartient à l'utilisateur
            $document = Document::where('id', $documentId)
                ->where('signer_id', auth()->id())
                ->firstOrFail();

            // Sauvegarder le PDF signé dans le nouveau répertoire
            $signedPdf = $request->file('signed_pdf');
            $filename = 'document_signe_' . $document->id . '_' . time() . '.pdf';
            $path = $signedPdf->storeAs('documents/signed', $filename, 'public');

            // Créer l'enregistrement de signature avec données multi-pages
            $signatureRecord = [
                'document_id' => $document->id,
                'signed_by' => auth()->id(),
                'signed_at' => now(),
                'path_signed_pdf' => $path,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ];
            
            // Ajouter les données multi-pages si présentes
            if (isset($signatureData['is_multi_page']) && $signatureData['is_multi_page']) {
                $signatureRecord['is_multi_page'] = true;
                $signatureRecord['total_pages'] = $signatureData['total_pages'] ?? 1;
                $signatureRecord['signature_positions'] = $signatureData['signatures'] ?? [];
            }
            
            $signature = DocumentSignature::create($signatureRecord);

            // Mettre à jour le statut du document
            $document->update([
                'status' => 'signed',
                'signed_at' => now()
            ]);

            // Déclencher l'événement DocumentSigned pour envoyer les notifications
            DocumentSigned::dispatch($document, $signature);

            return response()->json([
                'success' => true,
                'message' => 'PDF signé enregistré avec succès',
                'document_id' => $document->id,
                'signed_pdf_path' => $path
            ]);

        } catch (\Exception $e) {return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }

    /**
     * Diagnostic des documents signés
     */
    public function debugSignedDocuments()
    {
        try {
            $documents = \App\Models\Document::where('status', 'signed')
                ->with(['signatures', 'signer', 'uploader'])
                ->orderBy('signed_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'documents' => $documents->toArray(),
                'count' => $documents->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du diagnostic : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Diagnostic des signatures
     */
    public function debugSignatures()
    {
        try {
            $signatures = \App\Models\DocumentSignature::with(['document', 'signer'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'signatures' => $signatures->toArray(),
                'count' => $signatures->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du diagnostic : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier les fichiers signés dans le répertoire
     */
    public function checkSignedFiles()
    {
        try {
            $signedDocumentsPath = storage_path('app/public/documents/signed');
            $files = [];
            
            if (is_dir($signedDocumentsPath)) {
                $fileList = scandir($signedDocumentsPath);
                foreach ($fileList as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $filePath = $signedDocumentsPath . '/' . $file;
                        $files[] = [
                            'name' => $file,
                            'path' => $filePath,
                            'size' => filesize($filePath),
                            'modified' => date('Y-m-d H:i:s', filemtime($filePath))
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'files' => $files,
                'count' => count($files),
                'directory_path' => $signedDocumentsPath,
                'directory_exists' => is_dir($signedDocumentsPath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des fichiers : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Diagnostic d'un document spécifique
     */
    public function debugDocument($id)
    {
        try {
            $document = \App\Models\Document::with(['uploader', 'signer', 'signatures'])
                ->find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document non trouvé',
                    'document' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'document' => $document->toArray(),
                'signatures_count' => $document->signatures()->count(),
                'latest_signature' => $document->signatures()->latest()->first()?->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du diagnostic du document : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer la signature de l'utilisateur connecté depuis la base de données
     */
    public function getUserSignature()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié'], 401);
            }
            
            // Vérifier si l'utilisateur a une signature_path dans la base de données
            if ($user->signature_path && !empty($user->signature_path)) {
                // Vérifier que le fichier existe
                if (Storage::disk('public')->exists($user->signature_path)) {
                    // Construire l'URL de la signature
                    $baseUrl = config('app.url');
                    $signatureUrl = $baseUrl . '/storage/' . $user->signature_path;
                    
                    return response()->json([
                        'success' => true,
                        'signature_url' => $signatureUrl,
                        'signature_path' => $user->signature_path,
                        'user_id' => $user->id,
                        'user_name' => $user->name
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le fichier de signature n\'existe pas sur le serveur',
                        'signature_path' => $user->signature_path,
                        'user_id' => $user->id
                    ], 404);
                }
            }
            
            // Si aucune signature_path n'est définie
            return response()->json([
                'success' => false,
                'message' => 'Aucune signature définie pour cet utilisateur',
                'user_id' => $user->id,
                'user_name' => $user->name
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la signature : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le paraphe de l'utilisateur connecté
     */
    public function getUserParaphe()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Vérifier si l'utilisateur a un paraphe
            if (!$user->hasParaphe()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun paraphe configuré pour cet utilisateur'
                ], 404);
            }

            // Récupérer l'URL du paraphe
            $parapheUrl = $user->getParapheUrl();
            
            if (!$parapheUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer l\'URL du paraphe'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'paraphe_url' => $parapheUrl,
                'parapheUrl' => $parapheUrl, // Compatibilité
                'hasParaphe' => true,
                'has_paraphe' => true // Compatibilité
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du paraphe : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le cachet Prestataire de l'utilisateur connecté
     */
    public function getUserCachetP()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            if ($user->hasCachetP()) {
                $cachetUrl = $user->getCachetPUrl();

                return response()->json([
                    'success' => true,
                    'cachet_url' => $cachetUrl,
                    'cachetUrl' => $cachetUrl, // Compatibilité
                    'cachet_path' => $user->cachet_p_path,
                    'cachet_type' => 'p',
                    'has_cachet' => true,
                    'hasCachet' => true, // Compatibilité
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun cachet Prestataire défini pour cet utilisateur',
                'user_id' => $user->id,
                'user_name' => $user->name
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du cachet P : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le cachet Fournisseur de l'utilisateur connecté
     */
    public function getUserCachetF()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            if ($user->hasCachetF()) {
                $cachetUrl = $user->getCachetFUrl();

                return response()->json([
                    'success' => true,
                    'cachet_url' => $cachetUrl,
                    'cachetUrl' => $cachetUrl, // Compatibilité
                    'cachet_path' => $user->cachet_f_path,
                    'cachet_type' => 'f',
                    'has_cachet' => true,
                    'hasCachet' => true, // Compatibilité
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun cachet Fournisseur défini pour cet utilisateur',
                'user_id' => $user->id,
                'user_name' => $user->name
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du cachet F : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le cachet de l'utilisateur connecté (rétrocompatibilité - renvoie cachet P)
     * @deprecated Utiliser getUserCachetP() ou getUserCachetF()
     */
    public function getUserCachet()
    {
        return $this->getUserCachetP();
    }
}
