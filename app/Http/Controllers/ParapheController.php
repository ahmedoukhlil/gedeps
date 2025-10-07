<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentParaphe;
use App\Services\PdfParapheService;
use App\Traits\CanProcessDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ParapheController extends Controller
{
    use CanProcessDocument;
    
    protected $pdfParapheService;

    public function __construct(PdfParapheService $pdfParapheService)
    {
        $this->pdfParapheService = $pdfParapheService;
    }

    /**
     * Afficher la liste des documents à parapher
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

        return view('paraphes.index', compact('documents'));
    }

    /**
     * Afficher le formulaire de paraphe
     */
    public function show(Document $document)
    {
        // Vérifier les permissions
        if (!$this->canParaphe($document)) {
            return redirect()->route('paraphes.index')
                ->with('error', 'Vous n\'avez pas l\'autorisation de parapher ce document.');
        }

        return view('paraphes.show', compact('document'));
    }

    /**
     * Traiter le paraphe du document
     */
    public function store(Request $request, Document $document)
    {
        // Vérifier les permissions
        if (!$this->canParaphe($document)) {
            return redirect()->route('paraphes.index')
                ->with('error', 'Vous n\'avez pas l\'autorisation de parapher ce document.');
        }

        // Validation
        $validated = $request->validate([
            'paraphe_comment' => 'nullable|string|max:500',
            'paraphe_type' => 'required|in:png,live',
            'live_paraphe_data' => 'required_if:paraphe_type,live|string',
            'paraphe_x' => 'nullable|numeric',
            'paraphe_y' => 'nullable|numeric',
            'paraphe_positions' => 'nullable|array',
            'is_multi_page' => 'boolean',
            'total_pages' => 'integer|min:1',
        ]);

        try {
            // Déterminer le type de paraphe
            $parapheType = $validated['paraphe_type'];
            
            // Vérifier que l'utilisateur a un paraphe si type PNG
            if ($parapheType === 'png' && !auth()->user()->hasParaphe()) {
                return redirect()->back()
                    ->with('error', 'Vous devez avoir un paraphe PNG configuré pour utiliser ce type de paraphe.');
            }

            // Position personnalisée
            $customPosition = null;
            if (isset($validated['paraphe_x']) && isset($validated['paraphe_y'])) {
                $customPosition = [
                    'x' => $validated['paraphe_x'],
                    'y' => $validated['paraphe_y']
                ];
            }

            $paraphedPdfPath = $this->pdfParapheService->parapheDocument(
                $document, 
                auth()->user(), 
                $validated['paraphe_comment'],
                $parapheType,
                $validated['live_paraphe_data'] ?? null,
                $customPosition
            );

            // Créer l'enregistrement de paraphe
            $parapheData = [
                'document_id' => $document->id,
                'paraphed_by' => auth()->id(),
                'paraphed_at' => now(),
                'paraphe_comment' => $validated['paraphe_comment'],
                'path_paraphed_pdf' => $paraphedPdfPath,
                'paraphe_type' => $parapheType,
            ];
            
            // Ajouter les données multi-pages si présentes
            if (isset($validated['paraphe_positions'])) {
                $parapheData['paraphe_positions'] = $validated['paraphe_positions'];
                $parapheData['is_multi_page'] = $validated['is_multi_page'] ?? false;
                $parapheData['total_pages'] = $validated['total_pages'] ?? 1;
            }
            
            $paraphe = DocumentParaphe::create($parapheData);

            // Mettre à jour le statut du document
            $document->update(['status' => 'paraphed']);

            $parapheTypeText = $parapheType === 'live' ? 'paraphe live' : 'paraphe PNG';
            return redirect()->route('paraphes.index')
                ->with('success', "Document paraphé avec succès avec un {$parapheTypeText} ! Le PDF paraphé a été généré.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du paraphe : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Télécharger le PDF paraphé
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
     * Afficher le PDF paraphé
     */
    public function view(Document $document)
    {
        $latestParaphe = $document->paraphes()->latest()->first();
        
        if (!$latestParaphe || !Storage::disk('public')->exists($latestParaphe->path_paraphed_pdf)) {
            return redirect()->back()->with('error', 'PDF paraphé non trouvé.');
        }

        $pdfUrl = Storage::disk('public')->url($latestParaphe->path_paraphed_pdf);
        
        return view('paraphes.view', compact('document', 'pdfUrl', 'latestParaphe'));
    }

    /**
     * Générer un certificat de paraphe
     */
    public function certificate(Document $document)
    {
        $latestParaphe = $document->paraphes()->latest()->first();
        
        if (!$latestParaphe) {
            return redirect()->back()->with('error', 'Aucun paraphe trouvé pour ce document.');
        }

        try {
            $certificatePath = $this->pdfParapheService->generateParapheCertificate(
                $document,
                $latestParaphe->parapher,
                $latestParaphe->paraphe_comment
            );

            return response()->download($certificatePath, 'certificat_paraphe_' . $document->id . '.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la génération du certificat : ' . $e->getMessage());
        }
    }

}