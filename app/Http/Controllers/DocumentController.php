<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /**
     * Afficher la page d'upload de documents
     */
    public function upload()
    {
        // Vérifier que l'utilisateur n'est pas un administrateur
        if (auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Les administrateurs ne peuvent pas soumettre des documents.');
        }
        
        return view('documents.upload');
    }

    /**
     * Traiter la soumission d'un document à l'approbation
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur n'est pas un administrateur
        if (auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Les administrateurs ne peuvent pas soumettre des documents.');
        }

        // Log des données reçues pour débogage
        \Log::info('Données reçues:', $request->all());

        try {
            // Validation personnalisée selon le type de signature
            $validationRules = [
                'document_name' => 'required|string|max:255',
                'type' => 'required|string|max:100',
                'description' => 'nullable|string|max:1000',
                'signature_type' => 'required|in:simple,sequential',
                'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            ];

            // Ajouter les règles selon le type de signature
            if ($request->input('signature_type') === 'simple') {
                $validationRules['signer_id'] = 'required|exists:users,id';
            } else {
                $validationRules['sequential_signers'] = 'required|array|min:1';
                $validationRules['sequential_signers.*'] = 'exists:users,id';
                $validationRules['sequential_signers_order'] = 'required|array|min:1';
                $validationRules['sequential_signers_order.*'] = 'integer|min:1';
            }

            $validated = $request->validate($validationRules);

            // Vérifier les signataires selon le type de signature
            if ($validated['signature_type'] === 'simple') {
                $signer = \App\Models\User::find($validated['signer_id']);
                if (!$signer || !$signer->isSignataire()) {
                    return redirect()->back()->with('error', 'Le signataire sélectionné n\'a pas le bon rôle.');
                }
            } else {
                // Vérifier que tous les signataires séquentiels ont le bon rôle
                $sequentialSigners = \App\Models\User::whereIn('id', $validated['sequential_signers'])->get();
                foreach ($sequentialSigners as $signer) {
                    if (!$signer->isSignataire()) {
                        return redirect()->back()->with('error', 'Tous les signataires doivent avoir le rôle signataire.');
                    }
                }
            }

            // Stocker le fichier
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            // Créer l'enregistrement en base de données
            $documentData = [
                'document_name' => $validated['document_name'],
                'type' => $validated['type'],
                'description' => $validated['description'],
                'filename_original' => $file->getClientOriginalName(),
                'path_original' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'status' => 'pending',
                'uploaded_by' => auth()->id(),
            ];

            // Ajouter les données selon le type de signature
            if ($validated['signature_type'] === 'simple') {
                $documentData['signer_id'] = $validated['signer_id'];
                $documentData['sequential_signatures'] = false;
            } else {
                $documentData['sequential_signatures'] = true;
                $documentData['current_signature_index'] = 0;
                $documentData['signature_queue'] = $validated['sequential_signers'];
                $documentData['completed_signatures'] = [];
            }

            $document = Document::create($documentData);

            // Gérer les signatures selon le type
            if ($validated['signature_type'] === 'simple') {
                // Signature simple - notification au signataire unique
                $notificationService = new NotificationService();
                $notificationService->notifyDocumentAssigned($document, $signer, auth()->user());
                
                $successMessage = 'Document soumis avec succès ! Il a été assigné à ' . $signer->name . ' pour signature. Une notification a été envoyée par email.';
            } else {
                // Signatures séquentielles - créer les enregistrements et notifier le premier
                $sequentialSigners = \App\Models\User::whereIn('id', $validated['sequential_signers'])->get();
                
                // Créer les enregistrements de signatures séquentielles dans l'ordre correct
                $signersWithOrder = [];
                $orderArray = $request->input('sequential_signers_order', []);
                
                // Créer un mapping entre les IDs et l'ordre
                $signerOrderMap = [];
                foreach ($validated['sequential_signers'] as $index => $signerId) {
                    $order = isset($orderArray[$index]) ? (int)$orderArray[$index] : ($index + 1);
                    $signerOrderMap[$signerId] = $order;
                }
                
                // Trier les signataires par ordre croissant (premier = ordre 1)
                $signersWithOrder = [];
                foreach ($validated['sequential_signers'] as $signerId) {
                    $signer = $sequentialSigners->find($signerId);
                    if ($signer) {
                        $order = $signerOrderMap[$signerId];
                        $signersWithOrder[] = [
                            'user_id' => $signer->id,
                            'order' => $order,
                            'name' => $signer->name
                        ];
                    }
                }
                
                // Trier par ordre croissant pour que l'ordre 1 soit en premier
                usort($signersWithOrder, function($a, $b) {
                    return $a['order'] - $b['order'];
                });
                
                // Créer les enregistrements dans l'ordre correct
                foreach ($signersWithOrder as $signerData) {
                    \App\Models\SequentialSignature::create([
                        'document_id' => $document->id,
                        'user_id' => $signerData['user_id'],
                        'signature_order' => $signerData['order'],
                        'status' => 'pending'
                    ]);
                }
                
                // Notifier le premier signataire (celui avec l'ordre 1)
                $firstSignerData = collect($signersWithOrder)->where('order', 1)->first();
                $firstSigner = $sequentialSigners->find($firstSignerData['user_id']);
                $notificationService = new NotificationService();
                $notificationService->notifyDocumentAssigned($document, $firstSigner, auth()->user());
                
                // Créer la liste des signataires dans l'ordre correct
                $signersList = collect($signersWithOrder)->sortBy('order')->pluck('name')->join(', ');
                $successMessage = 'Document soumis avec succès ! Il a été assigné à ' . count($signersWithOrder) . ' signataires dans l\'ordre : ' . $signersList . '. Le premier signataire a été notifié.';
            }

            return redirect()->route('documents.upload')
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Erreur de validation. Veuillez vérifier les champs.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la soumission : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les documents en attente de signature
     */
    public function pending()
    {
        // Récupérer les documents selon le rôle de l'utilisateur
        if (auth()->user()->isSignataire()) {
            // Signataire voit ses documents à signer
            $documents = Document::with(['uploader', 'signer'])
                ->where('status', Document::STATUS_PENDING)
                ->where('signer_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif (auth()->user()->isAdmin()) {
            // Admin voit tous les documents en attente
            $documents = Document::with(['uploader', 'signer'])
                ->where('status', Document::STATUS_PENDING)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Agent voit ses documents soumis en attente
            $documents = Document::with(['uploader', 'signer'])
                ->where('status', Document::STATUS_PENDING)
                ->where('uploaded_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('documents.pending', compact('documents'));
    }

    /**
     * Afficher l'historique des documents
     */
    public function history(Request $request)
    {
        $userId = auth()->id();
        
        // Récupérer les documents selon le rôle de l'utilisateur
        if (auth()->user()->isAdmin()) {
            // Admin voit tous les documents (signed, pending, paraphed, signed_and_paraphed, in_progress)
            $query = Document::whereIn('status', [
                Document::STATUS_SIGNED, 
                Document::STATUS_PENDING, 
                Document::STATUS_PARAPHED, 
                Document::STATUS_SIGNED_AND_PARAPHED,
                'in_progress'  // Ajouter in_progress pour les signatures séquentielles
            ])->with(['uploader', 'signer', 'signatures.signer', 'sequentialSignatures.user']);
        } elseif (auth()->user()->isAgent()) {
            // Agent voit ses documents uploadés (signed, pending, paraphed, signed_and_paraphed, in_progress)
            $query = Document::where('uploaded_by', $userId)
                ->whereIn('status', [
                    Document::STATUS_SIGNED, 
                    Document::STATUS_PENDING, 
                    Document::STATUS_PARAPHED, 
                    Document::STATUS_SIGNED_AND_PARAPHED,
                    'in_progress'  // Ajouter in_progress pour les signatures séquentielles
                ])
                ->with(['signer', 'signatures.signer', 'sequentialSignatures.user']);
        } else {
            // Autres utilisateurs voient les documents qui leur sont assignés OU les documents séquentiels où ils ont participé
            $query = Document::where(function($q) use ($userId) {
                // Documents assignés directement
                $q->where('signer_id', $userId)
                  ->whereIn('status', [
                      Document::STATUS_SIGNED, 
                      Document::STATUS_PENDING, 
                      Document::STATUS_PARAPHED, 
                      Document::STATUS_SIGNED_AND_PARAPHED
                  ]);
            })
            ->orWhere(function($q) use ($userId) {
                // Documents avec signatures séquentielles où l'utilisateur a participé
                $q->where('sequential_signatures', true)
                  ->whereIn('status', ['in_progress', 'signed'])
                  ->whereHas('sequentialSignatures', function($subQuery) use ($userId) {
                      $subQuery->where('user_id', $userId);
                  });
            })
            ->with(['uploader', 'signatures.signer', 'sequentialSignatures.user']);
        }

        // Appliquer la recherche globale
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                // Recherche dans les champs du document
                $q->where('filename_original', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('document_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('type', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('status', 'LIKE', "%{$searchTerm}%");
                
                // Recherche dans les relations
                $q->orWhereHas('uploader', function($subQuery) use ($searchTerm) {
                    $subQuery->where('name', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->orWhereHas('signer', function($subQuery) use ($searchTerm) {
                    $subQuery->where('name', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination et tri
        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('documents.history', compact('documents'));
    }

    /**
     * Télécharger un document de manière sécurisée
     */
    public function download(Document $document): BinaryFileResponse
    {
        // Construire le chemin complet du fichier
        $filePath = storage_path('app/public/' . $document->path_original);
        
        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            abort(404, 'Fichier non trouvé: ' . $document->path_original);
        }
        
        return response()->download($filePath, $document->filename_original);
    }


    /**
     * Afficher un document dans le navigateur
     */
    public function view(Document $document)
    {
        // Si le document est signé, rediriger vers la méthode du SignatureController
        if ($document->status === 'signed') {
            return app(\App\Http\Controllers\SignatureController::class)->downloadSigned($document);
        }

        // Si c'est un document avec signatures séquentielles, rediriger vers la page de signature
        if ($document->sequential_signatures && auth()->check() && auth()->user()->isSignataire()) {
            return redirect()->route('signatures.simple.show', $document);
        }

        // Sinon, afficher le document original
        if (!Storage::disk('public')->exists($document->path_original)) {
            abort(404, 'Fichier non trouvé');
        }

        $filePath = Storage::disk('public')->path($document->path_original);
        $mimeType = Storage::disk('public')->mimeType($document->path_original);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->filename_original . '"'
        ]);
    }

    /**
     * Ouvrir le PDF signé dans un nouvel onglet (pour les agents et admins)
     */
    public function downloadSigned(Document $document)
    {
        // Vérifier que l'utilisateur a le droit de voir ce document
        $canAccess = false;
        
        if (auth()->user()->isAdmin()) {
            $canAccess = true;
        } elseif (auth()->user()->isAgent() && $document->uploaded_by === auth()->id()) {
            $canAccess = true;
        } elseif ($document->signer_id === auth()->id()) {
            $canAccess = true;
        }

        if (!$canAccess) {
            return redirect()->back()->with('error', 'Accès non autorisé.');
        }

        // Récupérer la signature du document
        $signature = $document->signatures()->latest()->first();
        
        if (!$signature || !$signature->path_signed_pdf) {
            return redirect()->back()->with('error', 'Aucun PDF signé trouvé pour ce document.');
        }

        // Vérifier que le fichier existe
        if (!Storage::disk('public')->exists($signature->path_signed_pdf)) {
            return redirect()->back()->with('error', 'Le fichier PDF signé n\'existe plus.');
        }

        // Ouvrir le fichier dans un nouvel onglet
        $filePath = Storage::disk('public')->path($signature->path_signed_pdf);
        $mimeType = Storage::disk('public')->mimeType($signature->path_signed_pdf);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="signed_' . $document->filename_original . '"'
        ]);
    }
}
