<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SequentialSignature;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SequentialSignatureController extends Controller
{
    /**
     * Afficher la liste des documents en attente de signature séquentielle
     */
    public function index()
    {
        // Vérifier que l'utilisateur est authentifié et est un signataire
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        if (!auth()->user()->isSignataire()) {
            return redirect()->back()->with('error', 'Seuls les signataires peuvent accéder à cette page.');
        }
        
        $userId = auth()->id();
        
        // Log pour déboguer// Documents avec signatures séquentielles où l'utilisateur est le prochain à signer
        $documents = Document::where('sequential_signatures', true)
            ->where('status', 'in_progress')
            ->whereHas('sequentialSignatures', function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending')
                      ->whereRaw('signature_order = current_signature_index + 1');
            })
            ->with(['sequentialSignatures.user', 'uploader'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);return view('signatures.sequential', compact('documents'));
    }

    /**
     * Afficher le formulaire de signature séquentielle
     */
    public function show(Document $document)
    {
        $userId = auth()->id();
        
        // Vérifier si c'est le tour de l'utilisateur
        if (!$document->isUserTurnToSign($userId)) {
            return redirect()->route('signatures.sequential')
                ->with('error', 'Ce n\'est pas encore votre tour de signer ce document.');
        }

        $currentSignature = $document->getCurrentSigner();
        $nextSigner = $document->getNextSigner();
        $progress = $document->getSignatureProgress();
        $completedSignatures = $document->getCompletedSignatures();

        return view('signatures.sequential-process', compact(
            'document', 
            'currentSignature', 
            'nextSigner', 
            'progress', 
            'completedSignatures'
        ));
    }

    /**
     * Traiter la signature séquentielle
     */
    public function sign(Request $request, Document $document)
    {
        $userId = auth()->id();
        
        // Vérifier si c'est le tour de l'utilisateur
        if (!$document->isUserTurnToSign($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas encore votre tour de signer ce document.'
            ], 403);
        }

        $request->validate([
            'signature_data' => 'required|array',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        
        try {
            $currentSignature = $document->getCurrentSigner();
            $currentUser = Auth::user();
            
            // Marquer la signature comme complétée
            $currentSignature->markAsSigned(
                $request->signature_data,
                $request->notes
            );

            // Passer au signataire suivant
            $document->moveToNextSigner();

            // Gérer les notifications
            $notificationService = new NotificationService();
            
            if ($document->status === Document::STATUS_SIGNED) {
                // Toutes les signatures sont terminées
                $agent = User::find($document->uploaded_by);
                if ($agent) {
                    $notificationService->notifySequentialSignatureCompleted($document, $agent);
                }
            } else {
                // Notifier le prochain signataire
                $nextSigner = $document->getNextSigner();
                if ($nextSigner) {
                    $notificationService->notifyNextSequentialSigner($document, $nextSigner->user, $currentUser);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $document->status === Document::STATUS_SIGNED 
                    ? 'Document entièrement signé par tous les signataires !'
                    : 'Document signé avec succès. Le prochain signataire a été notifié.',
                'next_signer' => $document->getNextSigner()?->user->name,
                'progress' => $document->getSignatureProgress(),
                'is_completed' => $document->status === Document::STATUS_SIGNED
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la signature: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ignorer la signature (optionnel)
     */
    public function skip(Request $request, Document $document)
    {
        $userId = auth()->id();
        
        if (!$document->isUserTurnToSign($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de signer ce document.'
            ], 403);
        }

        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        DB::beginTransaction();
        
        try {
            $currentSignature = $document->getCurrentSigner();
            $currentUser = Auth::user();
            
            $currentSignature->markAsSkipped($request->notes);
            $document->moveToNextSigner();

            // Gérer les notifications
            $notificationService = new NotificationService();
            
            if ($document->status === Document::STATUS_SIGNED) {
                // Toutes les signatures sont terminées
                $agent = User::find($document->uploaded_by);
                if ($agent) {
                    $notificationService->notifySequentialSignatureCompleted($document, $agent);
                }
            } else {
                // Notifier le prochain signataire
                $nextSigner = $document->getNextSigner();
                if ($nextSigner) {
                    $notificationService->notifySignatureSkipped($document, $currentUser, $nextSigner->user);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $document->status === Document::STATUS_SIGNED 
                    ? 'Document entièrement signé par tous les signataires !'
                    : 'Signature ignorée. Le prochain signataire a été notifié.',
                'next_signer' => $document->getNextSigner()?->user->name,
                'progress' => $document->getSignatureProgress(),
                'is_completed' => $document->status === Document::STATUS_SIGNED
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'un document pour signature séquentielle
     */
    public function getDocumentDetails(Document $document)
    {
        $userId = auth()->id();
        
        if (!$document->isUserTurnToSign($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce n\'est pas votre tour de signer ce document.'
            ], 403);
        }

        $currentSignature = $document->getCurrentSigner();
        $nextSigner = $document->getNextSigner();
        $progress = $document->getSignatureProgress();
        $completedSignatures = $document->getCompletedSignatures();

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'name' => $document->document_name,
                'type' => $document->type_name,
                'uploaded_by' => $document->uploader->name,
                'created_at' => $document->created_at->format('d/m/Y H:i')
            ],
            'current_signature' => [
                'user' => $currentSignature->user->name,
                'order' => $currentSignature->signature_order
            ],
            'next_signer' => $nextSigner ? [
                'name' => $nextSigner->user->name,
                'order' => $nextSigner->signature_order
            ] : null,
            'progress' => $progress,
            'completed_signatures' => $completedSignatures->map(function($sig) {
                return [
                    'user' => $sig->user->name,
                    'signed_at' => $sig->signed_at->format('d/m/Y H:i'),
                    'status' => $sig->status
                ];
            })
        ]);
    }
}