<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCachet;
use App\Services\PdfCachetService;
use App\Traits\CanProcessDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CachetController extends Controller
{
    use CanProcessDocument;
    
    protected $pdfCachetService;

    public function __construct(PdfCachetService $pdfCachetService)
    {
        $this->pdfCachetService = $pdfCachetService;
    }

    /**
     * Afficher la liste des documents à cacheter
     */
    public function index()
    {
        $user = auth()->user();
        
        // Récupérer les documents selon le rôle
        if ($user->isSignataire()) {
            $documents = Document::with(['uploader', 'signer'])
                ->where('signer_id', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->latest()
                ->paginate(10);
        } else {
            $documents = Document::with(['uploader', 'signer'])
                ->where('uploaded_by', $user->id)
                ->latest()
                ->paginate(10);
        }

        return view('cachets.index', compact('documents'));
    }

    /**
     * Afficher le formulaire de cachet
     */
    public function show(Document $document)
    {
        // Vérifier les permissions
        if (!$this->canCachet($document)) {
            return redirect()->route('cachets.index')
                ->with('error', 'Vous n\'avez pas l\'autorisation de cacheter ce document.');
        }

        return view('cachets.show', compact('document'));
    }

    /**
     * Traiter le cachet du document
     */
    public function store(Request $request, Document $document)
    {
        // Vérifier les permissions
        if (!$this->canCachet($document)) {
            return redirect()->route('cachets.index')
                ->with('error', 'Vous n\'avez pas l\'autorisation de cacheter ce document.');
        }

        // Validation
        $validated = $request->validate([
            'cachet_comment' => 'nullable|string|max:500',
            'cachet_type' => 'required|in:png,live',
            'live_cachet_data' => 'required_if:cachet_type,live|string',
            'cachet_x' => 'nullable|numeric',
            'cachet_y' => 'nullable|numeric',
            'cachet_positions' => 'nullable|array',
            'is_multi_page' => 'boolean',
            'total_pages' => 'integer|min:1',
        ]);

        try {
            // Déterminer le type de cachet
            $cachetType = $validated['cachet_type'];
            
            // Vérifier que l'utilisateur a un cachet si type PNG
            if ($cachetType === 'png' && !auth()->user()->hasCachet()) {
                return redirect()->back()
                    ->with('error', 'Vous devez avoir un cachet PNG configuré pour utiliser ce type de cachet.');
            }

            // Position personnalisée
            $customPosition = null;
            if (isset($validated['cachet_x']) && isset($validated['cachet_y'])) {
                $customPosition = [
                    'x' => $validated['cachet_x'],
                    'y' => $validated['cachet_y']
                ];
            }

            $cachetedPdfPath = $this->pdfCachetService->cachetDocument(
                $document, 
                auth()->user(), 
                $validated['cachet_comment'],
                $cachetType,
                $validated['live_cachet_data'] ?? null,
                $customPosition
            );

            // Créer l'enregistrement de cachet
            $cachetData = [
                'document_id' => $document->id,
                'cacheted_by' => auth()->id(),
                'cacheted_at' => now(),
                'cachet_comment' => $validated['cachet_comment'],
                'path_cacheted_pdf' => $cachetedPdfPath,
                'cachet_type' => $cachetType,
            ];
            
            // Ajouter les données multi-pages si présentes
            if (isset($validated['cachet_positions'])) {
                $cachetData['cachet_positions'] = $validated['cachet_positions'];
                $cachetData['is_multi_page'] = $validated['is_multi_page'] ?? false;
                $cachetData['total_pages'] = $validated['total_pages'] ?? 1;
            }
            
            $cachet = DocumentCachet::create($cachetData);

            // Mettre à jour le statut du document
            $document->update(['status' => 'cacheted']);

            $cachetTypeText = $cachetType === 'live' ? 'cachet live' : 'cachet PNG';
            return redirect()->route('cachets.index')
                ->with('success', "Document cacheté avec succès avec un {$cachetTypeText} ! Le PDF cacheté a été généré.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du cachet : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Télécharger le PDF cacheté
     */
    public function download(Document $document)
    {
        $latestCachet = $document->cachets()->latest()->first();
        
        if (!$latestCachet || !Storage::disk('public')->exists($latestCachet->path_cacheted_pdf)) {
            return redirect()->back()->with('error', 'PDF cacheté non trouvé.');
        }

        return Storage::disk('public')->download($latestCachet->path_cacheted_pdf);
    }

    /**
     * Afficher le PDF cacheté
     */
    public function view(Document $document)
    {
        $latestCachet = $document->cachets()->latest()->first();
        
        if (!$latestCachet || !Storage::disk('public')->exists($latestCachet->path_cacheted_pdf)) {
            return redirect()->back()->with('error', 'PDF cacheté non trouvé.');
        }

        $pdfUrl = Storage::disk('public')->url($latestCachet->path_cacheted_pdf);
        
        return view('cachets.view', compact('document', 'pdfUrl', 'latestCachet'));
    }

    /**
     * Générer un certificat de cachet
     */
    public function certificate(Document $document)
    {
        $latestCachet = $document->cachets()->latest()->first();
        
        if (!$latestCachet) {
            return redirect()->back()->with('error', 'Aucun cachet trouvé pour ce document.');
        }

        try {
            $certificatePath = $this->pdfCachetService->generateCachetCertificate(
                $document,
                $latestCachet->cacheter,
                $latestCachet->cachet_comment
            );

            return response()->download($certificatePath, 'certificat_cachet_' . $document->id . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la génération du certificat : ' . $e->getMessage());
        }
    }

    /**
     * Récupérer le cachet de l'utilisateur connecté
     */
    public function getUserCachet()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Vérifier si l'utilisateur a un cachet
            if (!$user->hasCachet()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun cachet configuré pour cet utilisateur'
                ], 404);
            }

            // Récupérer l'URL du cachet
            $cachetUrl = $user->getCachetUrl();
            
            if (!$cachetUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer l\'URL du cachet'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'cachet_url' => $cachetUrl,
                'cachetUrl' => $cachetUrl, // Compatibilité
                'hasCachet' => true,
                'has_cachet' => true // Compatibilité
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du cachet : ' . $e->getMessage()
            ], 500);
        }
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
}

