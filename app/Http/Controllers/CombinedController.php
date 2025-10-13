<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentParaphe;
use App\Models\DocumentCachet;
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
            'cachet_comment' => 'nullable|string|max:500',
            'signature_type' => 'nullable|in:png,live',
            'paraphe_type' => 'nullable|in:png,live',
            'cachet_type' => 'nullable|in:png,live',
            'live_signature_data' => 'required_if:signature_type,live|string',
            'live_paraphe_data' => 'required_if:paraphe_type,live|string',
            'live_cachet_data' => 'required_if:cachet_type,live|string',
            'signature_x' => 'nullable|numeric',
            'signature_y' => 'nullable|numeric',
            'paraphe_x' => 'nullable|numeric',
            'paraphe_y' => 'nullable|numeric',
            'cachet_x' => 'nullable|numeric',
            'cachet_y' => 'nullable|numeric',
            'action_type' => 'required|in:sign_only,paraphe_only,cachet_only,sign_paraphe,sign_cachet,paraphe_cachet,all',
        ]);

        try {
            $actionType = $validated['action_type'];
            $user = auth()->user();

            // Déterminer les positions
            $signaturePosition = null;
            $paraphePosition = null;
            $cachetPosition = null;

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

            if (isset($validated['cachet_x']) && isset($validated['cachet_y'])) {
                $cachetPosition = [
                    'x' => $validated['cachet_x'],
                    'y' => $validated['cachet_y']
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
                
                case 'cachet_only':
                    $combinedPdfPath = $this->handleCachetOnly($document, $user, $validated, $cachetPosition);
                    break;
                
                case 'sign_paraphe':
                    $combinedPdfPath = $this->handleSignatureAndParaphe($document, $user, $validated, $signaturePosition, $paraphePosition);
                    break;
                
                case 'sign_cachet':
                    $combinedPdfPath = $this->handleSignatureAndCachet($document, $user, $validated, $signaturePosition, $cachetPosition);
                    break;
                
                case 'paraphe_cachet':
                    $combinedPdfPath = $this->handleParapheAndCachet($document, $user, $validated, $paraphePosition, $cachetPosition);
                    break;
                
                case 'all':
                    $combinedPdfPath = $this->handleAll($document, $user, $validated, $signaturePosition, $paraphePosition, $cachetPosition);
                    break;
                    
                // Compatibilité avec l'ancien 'both'
                case 'both':
                    $combinedPdfPath = $this->handleSignatureAndParaphe($document, $user, $validated, $signaturePosition, $paraphePosition);
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
     * Gérer le cachet uniquement
     */
    private function handleCachetOnly(Document $document, $user, $validated, $cachetPosition)
    {
        // Utiliser le service de cachet existant
        $cachetService = app(\App\Services\PdfCachetService::class);
        
        $cachetedPdfPath = $cachetService->cachetDocument(
            $document,
            $user,
            $validated['cachet_comment'] ?? null,
            $validated['cachet_type'],
            $validated['live_cachet_data'] ?? null,
            $cachetPosition
        );

        // Créer l'enregistrement de cachet
        DocumentCachet::create([
            'document_id' => $document->id,
            'cacheted_by' => $user->id,
            'cacheted_at' => now(),
            'cachet_comment' => $validated['cachet_comment'],
            'path_cacheted_pdf' => $cachetedPdfPath,
            'cachet_type' => $validated['cachet_type'],
        ]);

        return $cachetedPdfPath;
    }

    /**
     * Gérer signature et paraphe
     */
    private function handleSignatureAndParaphe(Document $document, $user, $validated, $signaturePosition, $paraphePosition)
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
     * Gérer signature et cachet
     */
    private function handleSignatureAndCachet(Document $document, $user, $validated, $signaturePosition, $cachetPosition)
    {
        // Pour l'instant, traiter séquentiellement
        // TODO: Créer un service combiné pour optimiser
        $signatureService = app(\App\Services\PdfSignatureService::class);
        $cachetService = app(\App\Services\PdfCachetService::class);
        
        // Signer d'abord
        $signedPdfPath = $signatureService->signDocument(
            $document,
            $user,
            $validated['signature_comment'] ?? null,
            $validated['signature_type'],
            $validated['live_signature_data'] ?? null,
            $signaturePosition
        );

        DocumentSignature::create([
            'document_id' => $document->id,
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_comment' => $validated['signature_comment'],
            'path_signed_pdf' => $signedPdfPath,
            'signature_type' => $validated['signature_type'],
        ]);

        // Puis cacheter
        $cachetedPdfPath = $cachetService->cachetDocument(
            $document,
            $user,
            $validated['cachet_comment'] ?? null,
            $validated['cachet_type'],
            $validated['live_cachet_data'] ?? null,
            $cachetPosition
        );

        DocumentCachet::create([
            'document_id' => $document->id,
            'cacheted_by' => $user->id,
            'cacheted_at' => now(),
            'cachet_comment' => $validated['cachet_comment'],
            'path_cacheted_pdf' => $cachetedPdfPath,
            'cachet_type' => $validated['cachet_type'],
        ]);

        return $cachetedPdfPath;
    }

    /**
     * Gérer paraphe et cachet
     */
    private function handleParapheAndCachet(Document $document, $user, $validated, $paraphePosition, $cachetPosition)
    {
        $parapheService = app(\App\Services\PdfParapheService::class);
        $cachetService = app(\App\Services\PdfCachetService::class);
        
        // Parapher d'abord
        $paraphedPdfPath = $parapheService->parapheDocument(
            $document,
            $user,
            $validated['paraphe_comment'] ?? null,
            $validated['paraphe_type'],
            $validated['live_paraphe_data'] ?? null,
            $paraphePosition
        );

        DocumentParaphe::create([
            'document_id' => $document->id,
            'paraphed_by' => $user->id,
            'paraphed_at' => now(),
            'paraphe_comment' => $validated['paraphe_comment'],
            'path_paraphed_pdf' => $paraphedPdfPath,
            'paraphe_type' => $validated['paraphe_type'],
        ]);

        // Puis cacheter
        $cachetedPdfPath = $cachetService->cachetDocument(
            $document,
            $user,
            $validated['cachet_comment'] ?? null,
            $validated['cachet_type'],
            $validated['live_cachet_data'] ?? null,
            $cachetPosition
        );

        DocumentCachet::create([
            'document_id' => $document->id,
            'cacheted_by' => $user->id,
            'cacheted_at' => now(),
            'cachet_comment' => $validated['cachet_comment'],
            'path_cacheted_pdf' => $cachetedPdfPath,
            'cachet_type' => $validated['cachet_type'],
        ]);

        return $cachetedPdfPath;
    }

    /**
     * Gérer signature, paraphe et cachet (tout)
     */
    private function handleAll(Document $document, $user, $validated, $signaturePosition, $paraphePosition, $cachetPosition)
    {
        $signatureService = app(\App\Services\PdfSignatureService::class);
        $parapheService = app(\App\Services\PdfParapheService::class);
        $cachetService = app(\App\Services\PdfCachetService::class);
        
        // Signer
        $signedPdfPath = $signatureService->signDocument(
            $document,
            $user,
            $validated['signature_comment'] ?? null,
            $validated['signature_type'],
            $validated['live_signature_data'] ?? null,
            $signaturePosition
        );

        DocumentSignature::create([
            'document_id' => $document->id,
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_comment' => $validated['signature_comment'],
            'path_signed_pdf' => $signedPdfPath,
            'signature_type' => $validated['signature_type'],
        ]);

        // Parapher
        $paraphedPdfPath = $parapheService->parapheDocument(
            $document,
            $user,
            $validated['paraphe_comment'] ?? null,
            $validated['paraphe_type'],
            $validated['live_paraphe_data'] ?? null,
            $paraphePosition
        );

        DocumentParaphe::create([
            'document_id' => $document->id,
            'paraphed_by' => $user->id,
            'paraphed_at' => now(),
            'paraphe_comment' => $validated['paraphe_comment'],
            'path_paraphed_pdf' => $paraphedPdfPath,
            'paraphe_type' => $validated['paraphe_type'],
        ]);

        // Cacheter
        $cachetedPdfPath = $cachetService->cachetDocument(
            $document,
            $user,
            $validated['cachet_comment'] ?? null,
            $validated['cachet_type'],
            $validated['live_cachet_data'] ?? null,
            $cachetPosition
        );

        DocumentCachet::create([
            'document_id' => $document->id,
            'cacheted_by' => $user->id,
            'cacheted_at' => now(),
            'cachet_comment' => $validated['cachet_comment'],
            'path_cacheted_pdf' => $cachetedPdfPath,
            'cachet_type' => $validated['cachet_type'],
        ]);

        return $cachetedPdfPath;
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
            case 'cachet_only':
                $document->update(['status' => 'cacheted']);
                break;
            case 'sign_paraphe':
            case 'both':
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
            'sign_paraphe', 'both' => 'Document signé et paraphé avec succès !',
            'sign_cachet' => 'Document signé et cacheté avec succès !',
            'paraphe_cachet' => 'Document paraphé et cacheté avec succès !',
            'all' => 'Document signé, paraphé et cacheté avec succès !',
            default => 'Document traité avec succès !',
        };
    }

}
