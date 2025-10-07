<?php

namespace App\Http\Livewire;

use App\Events\DocumentUploaded;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadDocument extends Component
{
    use WithFileUploads;

    public $file;
    public $type = '';
    public $comment_agent = '';
    public $isUploading = false;
    public $uploadProgress = 0;

    protected $rules = [
        'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240', // 10MB max
        'type' => 'required|string|in:contrat,facture,devis,bon_commande,rapport,autre',
        'comment_agent' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'file.required' => 'Veuillez sélectionner un fichier.',
        'file.mimes' => 'Le fichier doit être un PDF, PNG, JPG ou JPEG.',
        'file.max' => 'Le fichier ne doit pas dépasser 10MB.',
        'type.required' => 'Veuillez sélectionner un type de document.',
        'type.in' => 'Le type de document sélectionné n\'est pas valide.',
        'comment_agent.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
    ];

    public function mount()
    {
        // Vérifier les permissions
        if (!Auth::user()->can('create', Document::class)) {
            abort(403, 'Vous n\'avez pas l\'autorisation d\'uploader des documents.');
        }
    }

    public function updatedFile()
    {
        $this->validateOnly('file');
    }

    public function updatedType()
    {
        $this->validateOnly('type');
    }

    public function updatedCommentAgent()
    {
        $this->validateOnly('comment_agent');
    }

    public function upload()
    {
        $this->validate();

        try {
            $this->isUploading = true;
            $this->uploadProgress = 0;

            // Générer un nom de fichier unique
            $originalName = $this->file->getClientOriginalName();
            $extension = $this->file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;

            // Déterminer le répertoire de stockage
            $storagePath = 'documents/' . date('Y/m');
            $fullPath = $storagePath . '/' . $filename;

            // Créer le répertoire si nécessaire
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }

            // Stocker le fichier
            $this->file->storeAs($storagePath, $filename);

            $this->uploadProgress = 50;

            // Créer l'enregistrement en base
            $document = Document::create([
                'type' => $this->type,
                'path_original' => $fullPath,
                'filename_original' => $originalName,
                'comment_agent' => $this->comment_agent,
                'uploaded_by' => Auth::id(),
                'status' => Document::STATUS_PENDING,
            ]);

            $this->uploadProgress = 100;

            // Déclencher l'événement
            event(new DocumentUploaded($document));

            // Réinitialiser le formulaire
            $this->reset(['file', 'type', 'comment_agent']);
            $this->resetValidation();

            session()->flash('success', 'Document uploadé avec succès ! Il sera traité par la direction.');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'upload : ' . $e->getMessage());
        } finally {
            $this->isUploading = false;
            $this->uploadProgress = 0;
        }
    }

    public function removeFile()
    {
        $this->file = null;
        $this->resetValidation('file');
    }

    public function getTypesProperty()
    {
        return Document::TYPES;
    }

    public function render()
    {
        return view('livewire.upload-document');
    }
}
