<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentParaphe;
use App\Services\PdfCombinedService;
use App\Traits\CanProcessDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CombinedController extends Controller
{
    use CanProcessDocument;
    
    protected $pdfCombinedService;

    public function __construct(PdfCombinedService $pdfCombinedService)
    {
        $this->pdfCombinedService = $pdfCombinedService;
    }

    /**
     * Afficher la page de signature et paraphe combinés
     */
    public function show(Document $document)
    {
        // Vérifier les permissions
        if (!$this->canProcess($document)) {
            return redirect()->route('documents.pending')
                ->with('error', 'Vous n\'avez pas l\'autorisation de traiter ce document.');
        }

        return view('combined.show', compact('document'));
    }

    /**
     * Traiter la signature et le paraphe combinés
     */
    public function store(Request $request, Document $document)
    {
        // Vérifier les permissions
        if (!$this->canProcess($document)) {
            return redirect()->route('documents.pending')
                ->with('error', 'Vous n\'avez pas l\'autorisation de traiter ce document.');
        }

        // Validation
        $validated = $request->validate([
            'signature_comment' => 'nullable|string|max:500',
            'paraphe_comment' => 'nullable|string|max:500',
            'signature_type' => 'required|in:png,live',
            'paraphe_type' => 'required|in:png,live',
            'live_signature_data' => 'required_if:signature_type,live|string',
            'live_paraphe_data' => 'required_if:paraphe_type,live|string',
            'signature_x' => 'nullable|numeric',
            'signature_y' => 'nullable|numeric',
            'paraphe_x' => 'nullable|numeric',
            'paraphe_y' => 'nullable|numeric',
            'action_type' => 'required|in:sign_only,paraphe_only,both',
        ]);

        try {
            $actionType = $validated['action_type'];
            $user = auth()->user();

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

            $combinedPdfPath = null;

            // Traiter selon le type d'action
            switch ($actionType) {
                case 'sign_only':
                    $combinedPdfPath = $this->handleSignatureOnly($document, $user, $validated, $signaturePosition);
                    break;
                
                case 'paraphe_only':
                    $combinedPdfPath = $this->handleParapheOnly($document, $user, $validated, $paraphePosition);
                    break;
                
                case 'both':
                    $combinedPdfPath = $this->handleBoth($document, $user, $validated, $signaturePosition, $paraphePosition);
                    break;
            }

            // Mettre à jour le statut du document
            $this->updateDocumentStatus($document, $actionType);

            $message = $this->getSuccessMessage($actionType);
            return redirect()->route('documents.pending')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du traitement : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Gérer la signature uniquement
     */
    private function handleSignatureOnly(Document $document, $user, $validated, $signaturePosition)
    {
        // Utiliser le service de signature existant
        $signatureService = app(\App\Services\PdfSignatureService::class);
        
        $signedPdfPath = $signatureService->signDocument(
            $document,
            $user,
            $validated['signature_comment'],
            $validated['signature_type'],
            $validated['live_signature_data'] ?? null,
            $signaturePosition
        );

        // Créer l'enregistrement de signature
        DocumentSignature::create([
            'document_id' => $document->id,
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_comment' => $validated['signature_comment'],
            'path_signed_pdf' => $signedPdfPath,
            'signature_type' => $validated['signature_type'],
        ]);

        return $signedPdfPath;
    }

    /**
     * Gérer le paraphe uniquement
     */
    private function handleParapheOnly(Document $document, $user, $validated, $paraphePosition)
    {
        // Utiliser le service de paraphe existant
        $parapheService = app(\App\Services\PdfParapheService::class);
        
        $paraphedPdfPath = $parapheService->parapheDocument(
            $document,
            $user,
            $validated['paraphe_comment'],
            $validated['paraphe_type'],
            $validated['live_paraphe_data'] ?? null,
            $paraphePosition
        );

        // Créer l'enregistrement de paraphe
        DocumentParaphe::create([
            'document_id' => $document->id,
            'paraphed_by' => $user->id,
            'paraphed_at' => now(),
            'paraphe_comment' => $validated['paraphe_comment'],
            'path_paraphed_pdf' => $paraphedPdfPath,
            'paraphe_type' => $validated['paraphe_type'],
        ]);

        return $paraphedPdfPath;
    }

    /**
     * Gérer signature et paraphe
     */
    private function handleBoth(Document $document, $user, $validated, $signaturePosition, $paraphePosition)
    {
        $combinedPdfPath = $this->pdfCombinedService->signAndParapheDocument(
            $document,
            $user,
            $validated['signature_comment'],
            $validated['paraphe_comment'],
            $validated['signature_type'],
            $validated['paraphe_type'],
            $validated['live_signature_data'] ?? null,
            $validated['live_paraphe_data'] ?? null,
            $signaturePosition,
            $paraphePosition
        );

        // Créer les enregistrements
        DocumentSignature::create([
            'document_id' => $document->id,
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_comment' => $validated['signature_comment'],
            'path_signed_pdf' => $combinedPdfPath,
            'signature_type' => $validated['signature_type'],
        ]);

        DocumentParaphe::create([
            'document_id' => $document->id,
            'paraphed_by' => $user->id,
            'paraphed_at' => now(),
            'paraphe_comment' => $validated['paraphe_comment'],
            'path_paraphed_pdf' => $combinedPdfPath,
            'paraphe_type' => $validated['paraphe_type'],
        ]);

        return $combinedPdfPath;
    }

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

}
