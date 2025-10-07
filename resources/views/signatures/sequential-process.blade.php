@extends('layouts.app')

@section('title', 'Signer le Document')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Navigation sophistiquée -->
    <nav class="sophisticated-breadcrumb mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li>
                <a href="{{ route('home') }}" class="sophisticated-breadcrumb-link">
                    <i class="fas fa-home"></i>
                    <span class="hidden sm:inline ml-1">Accueil</span>
                </a>
            </li>
            <li class="sophisticated-breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </li>
            <li>
                <a href="{{ route('signatures.sequential') }}" class="sophisticated-breadcrumb-link">
                    <i class="fas fa-list-ol"></i>
                    <span class="hidden sm:inline ml-1">Signatures Séquentielles</span>
                </a>
            </li>
            <li class="sophisticated-breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </li>
            <li class="sophisticated-breadcrumb-current">
                <i class="fas fa-pen-fancy"></i>
                <span class="hidden sm:inline ml-1">Signer Document</span>
            </li>
        </ol>
    </nav>

    <!-- Informations du document -->
    <div class="sophisticated-card mb-6">
        <div class="sophisticated-card-header">
            <h2 class="sophisticated-card-header-title flex items-center gap-2">
                <i class="fas fa-file-alt sophisticated-accent"></i>
                {{ $document->document_name }}
            </h2>
        </div>
        <div class="sophisticated-card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="sophisticated-label">Type</label>
                    <p class="sophisticated-body">{{ $document->type_name }}</p>
                </div>
                <div>
                    <label class="sophisticated-label">Uploadé par</label>
                    <p class="sophisticated-body">{{ $document->uploader->name }}</p>
                </div>
                <div>
                    <label class="sophisticated-label">Date de création</label>
                    <p class="sophisticated-body">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <label class="sophisticated-label">Votre ordre</label>
                    <p class="sophisticated-body">#{{ $currentSignature->signature_order }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progression des signatures -->
    <div class="sophisticated-card mb-6">
        <div class="sophisticated-card-header">
            <h3 class="sophisticated-card-header-title flex items-center gap-2">
                <i class="fas fa-chart-line sophisticated-accent"></i>
                Progression des Signatures
            </h3>
        </div>
        <div class="sophisticated-card-body">
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium sophisticated-body">Progression globale</span>
                    <span class="text-sm sophisticated-caption">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="sophisticated-gradient-primary h-3 rounded-full transition-all duration-500" 
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <!-- Liste des signataires -->
            <div class="space-y-3">
                @foreach($document->sequentialSignatures as $signature)
                    <div class="flex items-center justify-between p-3 rounded-lg
                        @if($signature->status === 'signed') bg-green-50 border border-green-200
                        @elseif($signature->status === 'skipped') bg-gray-50 border border-gray-200
                        @elseif($signature->isCurrentTurn()) bg-yellow-50 border border-yellow-200
                        @else bg-gray-50 border border-gray-200 @endif">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold
                                @if($signature->status === 'signed') sophisticated-gradient-primary text-white
                                @elseif($signature->status === 'skipped') bg-gray-400 text-white
                                @elseif($signature->isCurrentTurn()) bg-yellow-500 text-white
                                @else bg-gray-300 text-gray-600 @endif">
                                {{ $signature->signature_order }}
                            </div>
                            <div>
                                <p class="font-medium sophisticated-heading">{{ $signature->user->name }}</p>
                                <p class="text-sm sophisticated-caption">{{ $signature->user->email }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($signature->status === 'signed')
                                <span class="inline-flex items-center gap-1 text-sm text-green-600">
                                    <i class="fas fa-check"></i>
                                    Signé le {{ $signature->signed_at->format('d/m/Y H:i') }}
                                </span>
                            @elseif($signature->status === 'skipped')
                                <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                                    <i class="fas fa-times"></i>
                                    Ignoré
                                </span>
                            @elseif($signature->isCurrentTurn())
                                <span class="inline-flex items-center gap-1 text-sm text-yellow-600 font-medium">
                                    <i class="fas fa-clock"></i>
                                    En cours
                                </span>
                            @else
                                <span class="text-sm text-gray-500">En attente</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Zone de signature -->
    <div class="sophisticated-card">
        <div class="sophisticated-card-header">
            <h3 class="sophisticated-card-header-title flex items-center gap-2">
                <i class="fas fa-pen-fancy sophisticated-accent"></i>
                Signature du Document
            </h3>
        </div>
        <div class="sophisticated-card-body">
            <!-- PDF Viewer -->
            <div class="mb-6">
                <div class="bg-sophisticated-bg-secondary rounded-lg p-4 text-center">
                    <div class="w-16 h-16 sophisticated-gradient-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-pdf text-2xl text-white"></i>
                    </div>
                    <h4 class="text-lg font-semibold sophisticated-heading mb-2">Document PDF</h4>
                    <p class="sophisticated-body mb-4">Cliquez sur "Ouvrir le Document" pour commencer la signature</p>
                    <button onclick="openPDFViewer()" class="sophisticated-btn-primary">
                        <i class="fas fa-eye"></i>
                        <span>Ouvrir le Document</span>
                    </button>
                </div>
            </div>

            <!-- Formulaire de signature -->
            <form id="signatureForm" class="space-y-6">
                @csrf
                <div>
                    <label class="sophisticated-label">Notes (optionnel)</label>
                    <textarea name="notes" 
                              class="sophisticated-input" 
                              rows="3" 
                              placeholder="Ajoutez des commentaires ou notes pour cette signature..."></textarea>
                </div>

                <!-- Données de signature cachées -->
                <input type="hidden" name="signature_data" id="signatureData">

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t sophisticated-border">
                    <button type="button" onclick="skipSignature()" class="sophisticated-btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Ignorer cette signature</span>
                    </button>
                    <button type="submit" class="sophisticated-btn-primary ml-auto">
                        <i class="fas fa-check"></i>
                        <span>Signer le Document</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour le PDF Viewer -->
<div id="pdfViewerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="sophisticated-card max-w-6xl w-full max-h-[90vh] overflow-hidden">
            <div class="sophisticated-card-header">
                <h3 class="sophisticated-card-header-title">Document PDF - Signature</h3>
                <button onclick="closePDFViewer()" class="sophisticated-nav-link">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="sophisticated-card-body p-0">
                <div id="pdfContainer" class="w-full h-96 bg-gray-100 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-16 h-16 sophisticated-gradient-primary rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-pdf text-2xl text-white"></i>
                        </div>
                        <p class="sophisticated-body">Chargement du PDF...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ignorer la signature -->
<div id="skipModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="sophisticated-card max-w-md w-full">
            <div class="sophisticated-card-header">
                <h3 class="sophisticated-card-header-title">Ignorer la Signature</h3>
            </div>
            <div class="sophisticated-card-body">
                <p class="sophisticated-body mb-4">
                    Êtes-vous sûr de vouloir ignorer cette signature ? 
                    Le document passera automatiquement au prochain signataire.
                </p>
                <div class="mb-4">
                    <label class="sophisticated-label">Raison (obligatoire)</label>
                    <textarea id="skipReason" 
                              class="sophisticated-input" 
                              rows="3" 
                              placeholder="Expliquez pourquoi vous ignorez cette signature..." 
                              required></textarea>
                </div>
                <div class="flex gap-3">
                    <button onclick="closeSkipModal()" class="sophisticated-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>Annuler</span>
                    </button>
                    <button onclick="confirmSkip()" class="sophisticated-btn-primary">
                        <i class="fas fa-times"></i>
                        <span>Ignorer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let signatureData = {};

function openPDFViewer() {
    document.getElementById('pdfViewerModal').classList.remove('hidden');
    // Ici vous pouvez intégrer votre viewer PDF existant
    // Pour l'instant, on simule le chargement
    setTimeout(() => {
        document.getElementById('pdfContainer').innerHTML = `
            <div class="w-full h-full bg-white border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                <div class="text-center">
                    <div class="w-16 h-16 sophisticated-gradient-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-pdf text-2xl text-white"></i>
                    </div>
                    <p class="sophisticated-body mb-4">PDF Viewer intégré</p>
                    <p class="text-sm sophisticated-caption">Cliquez sur le document pour ajouter votre signature</p>
                </div>
            </div>
        `;
    }, 1000);
}

function closePDFViewer() {
    document.getElementById('pdfViewerModal').classList.add('hidden');
}

function skipSignature() {
    document.getElementById('skipModal').classList.remove('hidden');
}

function closeSkipModal() {
    document.getElementById('skipModal').classList.add('hidden');
    document.getElementById('skipReason').value = '';
}

function confirmSkip() {
    const reason = document.getElementById('skipReason').value.trim();
    if (!reason) {
        alert('Veuillez indiquer une raison pour ignorer cette signature.');
        return;
    }

    fetch(`{{ route('signatures.sequential.skip', $document) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            notes: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Signature Ignorée', data.message);
            setTimeout(() => {
                window.location.href = '{{ route("signatures.sequential") }}';
            }, 2000);
        } else {
            showError('Erreur', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue.');
    });
}

// Gestion du formulaire de signature
document.getElementById('signatureForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Simuler les données de signature (à remplacer par votre logique de signature)
    signatureData = {
        timestamp: new Date().toISOString(),
        coordinates: { x: 100, y: 200 },
        signature_image: 'data:image/png;base64,...' // Image de signature encodée
    };
    
    document.getElementById('signatureData').value = JSON.stringify(signatureData);
    
    const formData = new FormData(this);
    
    fetch(`{{ route('signatures.sequential.sign', $document) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_completed) {
                showSuccess('Document Entièrement Signé !', data.message, { duration: 6000 });
            } else {
                showSuccess('Signature Réussie', data.message);
                if (data.next_signer) {
                    showInfo('Prochain Signataire', `Le prochain signataire est: ${data.next_signer}`);
                }
            }
            setTimeout(() => {
                window.location.href = '{{ route("signatures.sequential") }}';
            }, data.is_completed ? 3000 : 2000);
        } else {
            showError('Erreur de Signature', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError('Erreur Système', 'Une erreur est survenue lors de la signature.');
    });
});
</script>
@endsection
