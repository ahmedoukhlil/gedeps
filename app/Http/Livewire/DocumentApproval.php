<?php

namespace App\Http\Livewire;

use App\Events\DocumentRefused;
use App\Events\DocumentSigned;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Notifications\DocumentRefusedNotification;
use App\Notifications\DocumentSignedNotification;
use App\Services\PdfSigningService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DocumentApproval extends Component
{
    use WithPagination, WithFileUploads;

    public $selectedDocument;
    public $showPreviewModal = false;
    public $showSignModal = false;
    public $showRefuseModal = false;
    public $signatureFile;
    public $refusalComment = '';
    public $isProcessing = false;

    protected $rules = [
        'signatureFile' => 'required|file|mimes:png,jpg,jpeg|max:2048', // 2MB max
        'refusalComment' => 'required|string|max:1000',
    ];

    protected $messages = [
        'signatureFile.required' => 'Veuillez sélectionner un fichier de signature.',
        'signatureFile.mimes' => 'Le fichier de signature doit être un PNG, JPG ou JPEG.',
        'signatureFile.max' => 'Le fichier de signature ne doit pas dépasser 2MB.',
        'refusalComment.required' => 'Veuillez saisir un commentaire de refus.',
        'refusalComment.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
    ];

    public function mount()
    {
        // Vérifier les permissions
        if (!Auth::user()->can('managePending', Document::class)) {
            abort(403, 'Vous n\'avez pas l\'autorisation de gérer les documents en attente.');
        }
    }

    public function showPreview(Document $document)
    {
        if (!Auth::user()->can('view', $document)) {
            session()->flash('error', 'Vous n\'avez pas l\'autorisation de voir ce document.');
            return;
        }

        $this->selectedDocument = $document;
        $this->showPreviewModal = true;
    }

    public function showSignModal(Document $document)
    {
        if (!Auth::user()->can('sign', $document)) {
            session()->flash('error', 'Vous n\'avez pas l\'autorisation de signer ce document.');
            return;
        }

        $this->selectedDocument = $document;
        $this->showSignModal = true;
        $this->reset(['signatureFile']);
        $this->resetValidation('signatureFile');
    }

    public function showRefuseModal(Document $document)
    {
        if (!Auth::user()->can('refuse', $document)) {
            session()->flash('error', 'Vous n\'avez pas l\'autorisation de refuser ce document.');
            return;
        }

        $this->selectedDocument = $document;
        $this->showRefuseModal = true;
        $this->reset(['refusalComment']);
        $this->resetValidation('refusalComment');
    }

    public function signDocument()
    {
        $this->validateOnly('signatureFile');

        if (!$this->selectedDocument) {
            session()->flash('error', 'Aucun document sélectionné.');
            return;
        }

        try {
            $this->isProcessing = true;

            // Stocker l'image de signature
            $signaturePath = $this->storeSignatureFile();

            // Générer le chemin du PDF signé
            $signedPdfPath = 'archives/' . date('Y/m') . '/' . Str::uuid() . '.pdf';

            // Créer le répertoire si nécessaire
            $archiveDir = dirname($signedPdfPath);
            if (!Storage::exists($archiveDir)) {
                Storage::makeDirectory($archiveDir);
            }

            // Signer le PDF
            $pdfSigningService = app(PdfSigningService::class);
            $pdfSigningService->signPdf(
                $this->selectedDocument->path_original,
                $signaturePath,
                $signedPdfPath,
                [
                    'x' => 100,
                    'y' => 100,
                    'width' => 150,
                    'height' => 75,
                    'page' => -1, // Dernière page
                ]
            );

            // Créer l'enregistrement de signature
            $signature = DocumentSignature::create([
                'document_id' => $this->selectedDocument->id,
                'signed_by' => Auth::id(),
                'path_signature' => $signaturePath,
                'path_signed_pdf' => $signedPdfPath,
                'signed_at' => now(),
            ]);

            // Mettre à jour le statut du document
            $this->selectedDocument->update(['status' => Document::STATUS_SIGNED]);

            // Déclencher l'événement
            event(new DocumentSigned($this->selectedDocument, $signature));

            // Envoyer la notification
            $this->selectedDocument->uploader->notify(
                new DocumentSignedNotification($this->selectedDocument)
            );

            $this->closeModals();
            session()->flash('success', 'Document signé avec succès !');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la signature : ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function refuseDocument()
    {
        $this->validateOnly('refusalComment');

        if (!$this->selectedDocument) {
            session()->flash('error', 'Aucun document sélectionné.');
            return;
        }

        try {
            $this->isProcessing = true;

            // Créer l'enregistrement de refus
            $signature = DocumentSignature::create([
                'document_id' => $this->selectedDocument->id,
                'signed_by' => Auth::id(),
                'comment_manager' => $this->refusalComment,
            ]);

            // Mettre à jour le statut du document
            $this->selectedDocument->update(['status' => Document::STATUS_REFUSED]);

            // Déclencher l'événement
            event(new DocumentRefused($this->selectedDocument, $signature));

            // Envoyer la notification
            $this->selectedDocument->uploader->notify(
                new DocumentRefusedNotification($this->selectedDocument, $this->refusalComment)
            );

            $this->closeModals();
            session()->flash('success', 'Document refusé avec succès.');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors du refus : ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    private function storeSignatureFile(): string
    {
        $filename = Str::uuid() . '.' . $this->signatureFile->getClientOriginalExtension();
        $path = 'signatures/' . date('Y/m') . '/' . $filename;

        // Créer le répertoire si nécessaire
        $dir = dirname($path);
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $this->signatureFile->storeAs($dir, $filename);

        return $path;
    }

    public function closeModals()
    {
        $this->showPreviewModal = false;
        $this->showSignModal = false;
        $this->showRefuseModal = false;
        $this->selectedDocument = null;
        $this->reset(['signatureFile', 'refusalComment']);
        $this->resetValidation();
    }

    public function getDocumentsProperty()
    {
        return Document::pendingApproval()
            ->with(['uploader', 'signatures.signer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.document-approval', [
            'documents' => $this->documents,
        ]);
    }
}
