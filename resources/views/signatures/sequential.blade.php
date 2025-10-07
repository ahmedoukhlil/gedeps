@extends('layouts.app')

@section('title', 'Signatures Séquentielles')

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
            <li class="sophisticated-breadcrumb-current">
                <i class="fas fa-list-ol"></i>
                <span class="hidden sm:inline ml-1">Signatures Séquentielles</span>
            </li>
        </ol>
    </nav>

    <!-- En-tête -->
    <div class="sophisticated-card mb-8">
        <div class="sophisticated-card-header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold sophisticated-heading flex items-center gap-3">
                        <i class="fas fa-list-ol sophisticated-accent"></i>
                        Signatures Séquentielles
                    </h1>
                    <p class="sophisticated-body mt-2">Documents en attente de votre signature dans l'ordre défini</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="sophisticated-badge">
                        <i class="fas fa-clock"></i>
                        {{ $documents->total() }} Document(s)
                    </div>
                    <!-- Navigation rapide -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('signatures.index') }}" class="sophisticated-nav-quick-link">
                            <i class="fas fa-pen-fancy"></i>
                            <span class="hidden sm:inline ml-1">Signatures Simples</span>
                        </a>
                        <a href="{{ route('documents.pending') }}" class="sophisticated-nav-quick-link">
                            <i class="fas fa-list"></i>
                            <span class="hidden sm:inline ml-1">Tous les Documents</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($documents->count() > 0)
        <!-- Liste des documents -->
        <div class="space-y-6">
            @foreach($documents as $document)
                <div class="sophisticated-card">
                    <div class="sophisticated-card-body">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Informations du document -->
                            <div class="flex-1">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 sophisticated-gradient-primary rounded-lg flex items-center justify-center shadow-sm">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold sophisticated-heading mb-1">
                                            {{ $document->document_name }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-4 text-sm sophisticated-body">
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-tag sophisticated-accent"></i>
                                                {{ $document->type_name }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-user sophisticated-accent"></i>
                                                {{ $document->uploader->name }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-calendar sophisticated-accent"></i>
                                                {{ $document->created_at->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progression des signatures -->
                            <div class="lg:w-80">
                                <div class="bg-sophisticated-bg-secondary rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium sophisticated-body">Progression</span>
                                        <span class="text-sm sophisticated-caption">{{ $document->getSignatureProgress() }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                        <div class="sophisticated-gradient-primary h-2 rounded-full transition-all duration-300" 
                                             style="width: {{ $document->getSignatureProgress() }}%"></div>
                                    </div>
                                    
                                    <!-- Signataires -->
                                    <div class="space-y-2">
                                        @foreach($document->sequentialSignatures as $signature)
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs
                                                        @if($signature->status === 'signed') sophisticated-gradient-primary text-white
                                                        @elseif($signature->status === 'skipped') bg-gray-400 text-white
                                                        @elseif($signature->isCurrentTurn()) bg-yellow-500 text-white
                                                        @else bg-gray-200 text-gray-600 @endif">
                                                        {{ $signature->signature_order }}
                                                    </div>
                                                    <span class="sophisticated-body">{{ $signature->user->name }}</span>
                                                </div>
                                                <div class="text-xs sophisticated-caption">
                                                    @if($signature->status === 'signed')
                                                        <i class="fas fa-check text-green-500"></i>
                                                    @elseif($signature->status === 'skipped')
                                                        <i class="fas fa-times text-gray-500"></i>
                                                    @elseif($signature->isCurrentTurn())
                                                        <span class="text-yellow-600 font-medium">En cours</span>
                                                    @else
                                                        <span class="text-gray-500">En attente</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row gap-2">
                                <a href="{{ route('signatures.sequential.show', $document) }}" 
                                   class="sophisticated-btn-primary text-center">
                                    <i class="fas fa-pen-fancy"></i>
                                    <span>Signer</span>
                                </a>
                                <button class="sophisticated-btn-secondary" 
                                        onclick="showDocumentDetails({{ $document->id }})">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Détails</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $documents->links() }}
        </div>
    @else
        <!-- État vide -->
        <div class="sophisticated-card">
            <div class="sophisticated-card-body text-center py-12">
                <div class="w-20 h-20 sophisticated-gradient-primary rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fas fa-check-circle text-3xl text-white"></i>
                </div>
                <h3 class="text-xl font-semibold sophisticated-heading mb-2">
                    Aucun document en attente
                </h3>
                <p class="sophisticated-body mb-6">
                    Vous n'avez actuellement aucun document en attente de signature séquentielle.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('documents.upload') }}" class="sophisticated-btn-primary">
                        <i class="fas fa-upload"></i>
                        <span>Nouveau Document</span>
                    </a>
                    <a href="{{ route('signatures.index') }}" class="sophisticated-btn-secondary">
                        <i class="fas fa-pen-fancy"></i>
                        <span>Signatures Simples</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal pour les détails du document -->
<div id="documentDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="sophisticated-card max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sophisticated-card-header">
                <h3 class="sophisticated-card-header-title">Détails du Document</h3>
                <button onclick="closeDocumentDetails()" class="sophisticated-nav-link">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="sophisticated-card-body" id="documentDetailsContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<script>
function showDocumentDetails(documentId) {
    fetch(`/signatures/sequential/${documentId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('documentDetailsContent').innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold sophisticated-heading mb-2">Informations du Document</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="sophisticated-label">Nom</label>
                                    <p class="sophisticated-body">${data.document.name}</p>
                                </div>
                                <div>
                                    <label class="sophisticated-label">Type</label>
                                    <p class="sophisticated-body">${data.document.type}</p>
                                </div>
                                <div>
                                    <label class="sophisticated-label">Uploadé par</label>
                                    <p class="sophisticated-body">${data.document.uploaded_by}</p>
                                </div>
                                <div>
                                    <label class="sophisticated-label">Date de création</label>
                                    <p class="sophisticated-body">${data.document.created_at}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold sophisticated-heading mb-2">Progression des Signatures</h4>
                            <div class="bg-sophisticated-bg-secondary rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium sophisticated-body">Progression</span>
                                    <span class="text-sm sophisticated-caption">${data.progress}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                                    <div class="sophisticated-gradient-primary h-2 rounded-full" style="width: ${data.progress}%"></div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="sophisticated-body">Signataire actuel: ${data.current_signature.user}</span>
                                        <span class="sophisticated-caption">Ordre: ${data.current_signature.order}</span>
                                    </div>
                                    ${data.next_signer ? `
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="sophisticated-body">Prochain signataire: ${data.next_signer.name}</span>
                                        <span class="sophisticated-caption">Ordre: ${data.next_signer.order}</span>
                                    </div>
                                    ` : '<p class="text-sm sophisticated-caption">Dernier signataire</p>'}
                                </div>
                            </div>
                        </div>
                        
                        ${data.completed_signatures.length > 0 ? `
                        <div>
                            <h4 class="font-semibold sophisticated-heading mb-2">Signatures Complétées</h4>
                            <div class="space-y-2">
                                ${data.completed_signatures.map(sig => `
                                    <div class="flex items-center justify-between text-sm bg-green-50 p-2 rounded">
                                        <span class="sophisticated-body">${sig.user}</span>
                                        <span class="sophisticated-caption">${sig.signed_at}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                document.getElementById('documentDetailsModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

function closeDocumentDetails() {
    document.getElementById('documentDetailsModal').classList.add('hidden');
}
</script>
@endsection
