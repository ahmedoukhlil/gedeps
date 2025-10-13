@extends('layouts.app')

@section('title', 'Signatures Séquentielles')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Fil d'Ariane Élégant -->
    <nav class="mb-6 sm:mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs sm:text-sm">
            <li>
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                    <i class="fas fa-home text-sm"></i>
                    <span class="hidden sm:inline">Accueil</span>
                </a>
            </li>
            <li class="text-gray-400">
                <i class="fas fa-chevron-right text-xs"></i>
            </li>
            <li class="flex items-center gap-1.5 text-primary-600 font-semibold">
                <i class="fas fa-list-ol text-sm"></i>
                <span class="hidden sm:inline">Signatures Séquentielles</span>
            </li>
        </ol>
    </nav>

    <!-- Carte d'En-tête Élégante -->
    <div class="card card-hover mb-6 sm:mb-8 overflow-hidden relative">
        <!-- Fond décoratif avec dégradé -->
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 opacity-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-400 rounded-full blur-3xl opacity-20 -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary-300 rounded-full blur-3xl opacity-20 -ml-24 -mb-24"></div>
        
        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Informations de la page -->
                <div class="flex items-center gap-4 sm:gap-6 flex-1">
                    <!-- Icône Élégante -->
                    <div class="relative flex-shrink-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-glow">
                            <i class="fas fa-list-ol text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                        </div>
                    </div>
                    
                    <!-- Titre et Description -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                            <i class="fas fa-sparkles text-primary-500 text-xl sm:text-2xl lg:text-3xl"></i>
                            <span class="truncate">Signatures <span class="text-gradient">Séquentielles</span></span>
                        </h1>
                        <p class="text-sm sm:text-base text-gray-600">Documents en attente de signature dans l'ordre défini</p>
                    </div>
                </div>
                
                <!-- Actions et Statistiques -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <!-- Badge de compteur -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white border-2 border-primary-200 rounded-xl shadow-sm">
                        <i class="fas fa-clock text-primary-500"></i>
                        <span class="text-sm font-semibold text-gray-900">{{ $documents->total() }} Document(s)</span>
                    </div>
                    
                    <!-- Navigation rapide -->
                    <a href="{{ route('signatures.index') }}" class="group inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-white hover:bg-primary-50 border-2 border-gray-200 hover:border-primary-300 text-gray-700 hover:text-primary-600 rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                        <i class="fas fa-pen-fancy text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs sm:text-sm font-medium hidden sm:inline">Simples</span>
                    </a>
                    <a href="{{ route('documents.pending') }}" class="group inline-flex items-center gap-2 px-3 sm:px-4 py-2 bg-white hover:bg-primary-50 border-2 border-gray-200 hover:border-primary-300 text-gray-700 hover:text-primary-600 rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                        <i class="fas fa-list text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs sm:text-sm font-medium hidden sm:inline">Tous</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($documents->count() > 0)
        <!-- Liste des documents -->
        <div class="space-y-4 sm:space-y-6">
            @foreach($documents as $document)
                <div class="card card-hover overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-6">
                            <!-- Informations du document -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-3 sm:gap-4">
                                    <!-- Icône PDF élégante -->
                                    <div class="relative flex-shrink-0">
                                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-danger-400 to-danger-600 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                                            <i class="fas fa-file-pdf text-white text-xl sm:text-2xl"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2 truncate">
                                            {{ $document->document_name }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-gray-600">
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fas fa-tag text-primary-500"></i>
                                                <span>{{ $document->type_name }}</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fas fa-user text-info-500"></i>
                                                <span>{{ $document->uploader->name }}</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fas fa-calendar text-success-500"></i>
                                                <span>{{ $document->created_at->format('d/m/Y') }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progression des signatures -->
                            <div class="lg:w-80 xl:w-96">
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-gray-900">Progression</span>
                                        <span class="text-sm font-bold text-primary-600">{{ $document->getSignatureProgress() }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 overflow-hidden shadow-inner">
                                        <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-2.5 rounded-full transition-all duration-500 shadow-sm" 
                                             style="width: {{ $document->getSignatureProgress() }}%"></div>
                                    </div>
                                    
                                    <!-- Signataires -->
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        @foreach($document->sequentialSignatures as $signature)
                                            <div class="flex items-center justify-between text-xs sm:text-sm p-2 rounded-lg
                                                @if($signature->status === 'signed') bg-success-50
                                                @elseif($signature->isCurrentTurn()) bg-warning-50
                                                @else bg-white @endif">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm
                                                        @if($signature->status === 'signed') bg-gradient-to-br from-success-500 to-success-600 text-white
                                                        @elseif($signature->status === 'skipped') bg-gray-400 text-white
                                                        @elseif($signature->isCurrentTurn()) bg-gradient-to-br from-warning-500 to-warning-600 text-white
                                                        @else bg-gray-200 text-gray-600 @endif">
                                                        {{ $signature->signature_order }}
                                                    </div>
                                                    <span class="font-medium text-gray-900 truncate max-w-[120px] sm:max-w-[150px]">{{ $signature->user->name }}</span>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    @if($signature->status === 'signed')
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-success-100 text-success-700 rounded-md">
                                                            <i class="fas fa-check text-xs"></i>
                                                            <span class="text-xs font-medium">Signé</span>
                                                        </span>
                                                    @elseif($signature->status === 'skipped')
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 rounded-md">
                                                            <i class="fas fa-times text-xs"></i>
                                                            <span class="text-xs font-medium">Ignoré</span>
                                                        </span>
                                                    @elseif($signature->isCurrentTurn())
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-warning-100 text-warning-700 rounded-md animate-pulse">
                                                            <i class="fas fa-clock text-xs"></i>
                                                            <span class="text-xs font-medium">En cours</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-500 rounded-md">
                                                            <i class="fas fa-hourglass-half text-xs"></i>
                                                            <span class="text-xs font-medium">Attente</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row lg:flex-col gap-2 lg:w-32">
                                <a href="{{ route('signatures.sequential.show', $document) }}" 
                                   class="group flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                                    <i class="fas fa-pen-fancy text-sm group-hover:scale-110 transition-transform"></i>
                                    <span class="text-sm font-semibold">Signer</span>
                                </a>
                                <button onclick="showDocumentDetails({{ $document->id }})" 
                                        class="group flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 rounded-lg shadow-sm hover:shadow-md transition-all duration-300">
                                    <i class="fas fa-info-circle text-sm group-hover:scale-110 transition-transform"></i>
                                    <span class="text-sm font-semibold">Détails</span>
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
        <!-- État vide élégant -->
        <div class="card text-center py-12 sm:py-16 lg:py-20">
            <div class="relative inline-block mb-6">
                <!-- Cercle de fond animé -->
                <div class="absolute inset-0 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-xl opacity-50 animate-pulse"></div>
                
                <!-- Icône -->
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-success-500 to-success-700 rounded-full flex items-center justify-center mx-auto shadow-2xl">
                    <i class="fas fa-check-circle text-3xl sm:text-4xl text-white"></i>
                </div>
            </div>
            
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 flex items-center justify-center gap-2">
                <i class="fas fa-sparkles text-success-500"></i>
                <span>Aucun document en attente</span>
            </h3>
            <p class="text-sm sm:text-base text-gray-600 mb-8 max-w-md mx-auto">
                Vous n'avez actuellement aucun document en attente de signature séquentielle.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('documents.upload') }}" class="group inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-xl shadow-elegant hover:shadow-glow transition-all duration-300 hover:-translate-y-1">
                    <i class="fas fa-upload group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold">Nouveau Document</span>
                </a>
                <a href="{{ route('signatures.index') }}" class="group inline-flex items-center justify-center gap-2 px-6 py-3 bg-white hover:bg-gray-50 border-2 border-gray-300 hover:border-primary-400 text-gray-700 hover:text-primary-600 rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                    <i class="fas fa-pen-fancy group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold">Signatures Simples</span>
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modal pour les détails du document -->
<div id="documentDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="card max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 opacity-0" id="documentDetailsModalContent">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-info-circle text-primary-500"></i>
                    <span>Détails du Document</span>
                </h3>
                <button onclick="closeDocumentDetails()" class="group w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-all duration-200">
                    <i class="fas fa-times text-lg group-hover:rotate-90 transition-transform duration-300"></i>
                </button>
            </div>
            <div class="p-6" id="documentDetailsContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<script>
function showDocumentDetails(documentId) {
    const modal = document.getElementById('documentDetailsModal');
    const modalContent = document.getElementById('documentDetailsModalContent');
    
    fetch(`/signatures/sequential/${documentId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('documentDetailsContent').innerHTML = `
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-file-alt text-primary-500"></i>
                                <span>Informations du Document</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Nom</label>
                                    <p class="text-sm font-medium text-gray-900">${data.document.name}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Type</label>
                                    <p class="text-sm font-medium text-gray-900">${data.document.type}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Uploadé par</label>
                                    <p class="text-sm font-medium text-gray-900">${data.document.uploaded_by}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Date de création</label>
                                    <p class="text-sm font-medium text-gray-900">${data.document.created_at}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-chart-line text-success-500"></i>
                                <span>Progression des Signatures</span>
                            </h4>
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-gray-900">Progression</span>
                                    <span class="text-lg font-bold text-primary-600">${data.progress}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-6 overflow-hidden shadow-inner">
                                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500" style="width: ${data.progress}%"></div>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                        <span class="text-sm font-medium text-gray-900">Signataire actuel: ${data.current_signature.user}</span>
                                        <span class="text-xs font-semibold text-primary-600 px-2 py-1 bg-primary-100 rounded">Ordre: ${data.current_signature.order}</span>
                                    </div>
                                    ${data.next_signer ? `
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                        <span class="text-sm font-medium text-gray-900">Prochain signataire: ${data.next_signer.name}</span>
                                        <span class="text-xs font-semibold text-warning-600 px-2 py-1 bg-warning-100 rounded">Ordre: ${data.next_signer.order}</span>
                                    </div>
                                    ` : '<p class="text-sm text-gray-500 text-center p-3 bg-white rounded-lg border border-gray-200">Dernier signataire</p>'}
                                </div>
                            </div>
                        </div>
                        
                        ${data.completed_signatures.length > 0 ? `
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-check-circle text-success-500"></i>
                                <span>Signatures Complétées</span>
                            </h4>
                            <div class="space-y-2">
                                ${data.completed_signatures.map(sig => `
                                    <div class="flex items-center justify-between p-3 bg-success-50 rounded-lg border border-success-200">
                                        <span class="text-sm font-medium text-gray-900">${sig.user}</span>
                                        <span class="text-xs text-success-600">${sig.signed_at}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                // Afficher le modal avec animation
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modalContent.classList.remove('scale-95', 'opacity-0');
                    modalContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

function closeDocumentDetails() {
    const modal = document.getElementById('documentDetailsModal');
    const modalContent = document.getElementById('documentDetailsModalContent');
    
    // Fermer avec animation
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('documentDetailsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDocumentDetails();
    }
});

// Fermer le modal avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('documentDetailsModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeDocumentDetails();
        }
    }
});
</script>
@endsection
