<?php

namespace App\Http\Livewire;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentList extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $typeFilter = '';
    public $search = '';
    public $showAllDocuments = false;

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        // Vérifier les permissions
        if (!Auth::user()->can('viewAny', Document::class)) {
            abort(403, 'Vous n\'avez pas l\'autorisation de voir les documents.');
        }

        // Déterminer si l'utilisateur peut voir tous les documents
        $this->showAllDocuments = Auth::user()->hasPermissionTo('documents.view');
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['statusFilter', 'typeFilter', 'search']);
        $this->resetPage();
    }

    public function getDocumentsProperty()
    {
        $query = Document::with(['uploader', 'signatures.signer']);

        // Filtrer par utilisateur si nécessaire
        if (!$this->showAllDocuments) {
            $query->forUser(Auth::id());
        }

        // Appliquer les filtres
        if ($this->statusFilter) {
            $query->byStatus($this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->byType($this->typeFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('filename_original', 'like', '%' . $this->search . '%')
                  ->orWhere('comment_agent', 'like', '%' . $this->search . '%')
                  ->orWhereHas('uploader', function ($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getStatusOptionsProperty()
    {
        return [
            '' => 'Tous les statuts',
            Document::STATUS_PENDING => 'En attente',
            Document::STATUS_IN_PROGRESS => 'En cours',
            Document::STATUS_SIGNED => 'Signé',
            Document::STATUS_REFUSED => 'Refusé',
        ];
    }

    public function getTypeOptionsProperty()
    {
        $options = ['' => 'Tous les types'];
        return array_merge($options, Document::TYPES);
    }

    public function render()
    {
        return view('livewire.document-list', [
            'documents' => $this->documents,
        ]);
    }
}
