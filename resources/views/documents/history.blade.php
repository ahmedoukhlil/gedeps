@extends('layouts.app')

@section('title', 'Historique des Documents')


@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Navigation breadcrumb -->
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
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline ml-1">Historique des Documents</span>
            </li>
        </ol>
    </nav>
    
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                <i class="fas fa-clock text-white text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Historique des Documents
                </h1>
                <p class="text-gray-600">
                    Consultez l'historique complet de tous vos documents
                </p>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="text-green-800">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Barre de recherche globale - Filtrage en Temps Réel -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <div class="space-y-4">
            <!-- Recherche globale -->
            <div class="relative">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-primary-100 flex items-center justify-center">
                        <i class="fas fa-search text-primary-600 text-xs"></i>
                    </div>
                    <span>Recherche instantanée de documents</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Tapez pour filtrer les documents en temps réel..."
                           autocomplete="off"
                           class="w-full px-4 py-3 pl-12 pr-12 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-base transition-all duration-200 hover:border-primary-300">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-primary-500 text-lg" id="searchIcon"></i>
                    </div>
                    <button type="button" 
                            id="clearSearch"
                            class="hidden absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-danger-600 transition-colors">
                        <i class="fas fa-times-circle text-lg"></i>
                    </button>
                </div>
                
                <!-- Compteur de résultats -->
                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-start gap-2 p-3 bg-gradient-to-r from-primary-50 to-blue-50 border border-primary-200 rounded-lg flex-1">
                        <i class="fas fa-lightbulb text-primary-600 text-sm mt-0.5"></i>
                        <p class="text-xs text-primary-700 flex-1">
                            <strong class="font-semibold">Astuce :</strong> Le filtrage se fait instantanément pendant que vous tapez. Aucun rechargement de page !
                        </p>
                    </div>
                    <div class="ml-3 px-4 py-2 bg-success-100 border border-success-200 rounded-lg">
                        <span id="resultCount" class="text-sm font-bold text-success-700">{{ $allDocuments->count() }}</span>
                        <span class="text-xs text-success-600 ml-1">document(s)</span>
                    </div>
                </div>
            </div>

            <!-- Filtres avancés (optionnels) -->
            <div class="border-t pt-4">
                <button type="button" id="toggleAdvancedFilters" class="text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer relative z-10 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                    <i class="fas fa-filter mr-1"></i>
                    Filtres avancés
                    <i class="fas fa-chevron-down ml-1" id="filterToggleIcon"></i>
                </button>
                
                <div id="advancedFilters" class="hidden mt-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-1"></i>
                                Type
                            </label>
                            <div class="relative">
                            <select id="type" name="type" class="w-full px-4 py-2.5 sm:py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none bg-white text-sm sm:text-base">
                                <option value="">Tous les types</option>
                                <option value="contrat" {{ request('type') == 'contrat' ? 'selected' : '' }}>Contrat</option>
                                <option value="facture" {{ request('type') == 'facture' ? 'selected' : '' }}>Facture</option>
                                <option value="rapport" {{ request('type') == 'rapport' ? 'selected' : '' }}>Rapport</option>
                                <option value="lettre" {{ request('type') == 'lettre' ? 'selected' : '' }}>Lettre</option>
                                <option value="note_de_service" {{ request('type') == 'note_de_service' ? 'selected' : '' }}>Note de service</option>
                                <option value="autre" {{ request('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Statut
                            </label>
                            <div class="relative">
                            <select id="status" name="status" class="w-full px-4 py-2.5 sm:py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none bg-white text-sm sm:text-base">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>Signé</option>
                                <option value="paraphed" {{ request('status') == 'paraphed' ? 'selected' : '' }}>Paraphé</option>
                                <option value="signed_and_paraphed" {{ request('status') == 'signed_and_paraphed' ? 'selected' : '' }}>Signé et paraphé</option>
                            </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>
                                Date de début
                            </label>
                            <input type="date" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-blue-900">{{ $allDocuments->count() }}</h3>
                    <p class="text-blue-700 font-medium">Total documents</p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-orange-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-orange-900">{{ $allDocuments->where('status', 'pending')->count() }}</h3>
                    <p class="text-orange-700 font-medium">En attente</p>
                </div>
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-emerald-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-check-circle text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-emerald-900">{{ $allDocuments->where('status', 'signed')->count() }}</h3>
                    <p class="text-emerald-700 font-medium">Signés</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-edit text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-purple-900">{{ $allDocuments->where('status', 'paraphed')->count() }}</h3>
                    <p class="text-purple-700 font-medium">Paraphés</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des documents regroupés par type - Accordion horizontal -->
    @if($allDocuments->count() > 0)
        <div class="flex gap-4 overflow-x-auto pb-4" id="documentsList">
            @foreach($documentsByTypeOrdered as $typeLabel => $documents)
                @php
                    $typeId = Str::slug($typeLabel);
                    $totalDocs = $documents->count();
                    $totalPages = ceil($totalDocs / 10);
                    // Récupérer le type réel du premier document pour le data-attribute
                    $firstDocType = $documents->first()->type ?? 'autre';
                @endphp
                <!-- Section par type de document - Verticale -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden flex-shrink-0 w-80" data-type-column="{{ $typeId }}" data-document-type="{{ $firstDocType }}">
                    <!-- En-tête de la section (Type de document) -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-folder text-white text-lg"></i>
                            </div>
                            <div class="text-left flex-1">
                                <h2 class="text-lg font-bold text-white">{{ $typeLabel }}</h2>
                                <p class="text-xs text-blue-100">{{ $totalDocs }} document{{ $totalDocs > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des documents de ce type avec pagination -->
                    <div id="type-section-{{ $typeId }}" class="divide-y divide-gray-200 max-h-[600px] overflow-y-auto">
                        @foreach($documents as $index => $document)
                            <div class="document-item-accordion document-page-{{ $typeId }}-{{ floor($index / 10) + 1 }} {{ floor($index / 10) > 0 ? 'hidden' : '' }}"
                                 data-name="{{ strtolower($document->document_name ?? $document->filename_original) }}"
                                 data-type="{{ strtolower($document->type) }}"
                                 data-status="{{ strtolower($document->status) }}"
                                 data-uploader="{{ strtolower($document->uploader->name ?? '') }}"
                                 data-date="{{ $document->created_at->format('d/m/Y') }}">

                                <!-- En-tête cliquable du document -->
                                <button type="button"
                                        onclick="toggleDocument({{ $document->id }})"
                                        class="w-full px-4 py-3 hover:bg-gray-50 transition-colors duration-200 text-left">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow-md bg-gradient-to-br from-blue-500 to-indigo-600 flex-shrink-0">
                                                <i class="fas fa-file-pdf text-white text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-bold text-gray-900 truncate">
                                                    {{ $document->document_name ?? $document->filename_original }}
                                                </h3>
                                                <p class="text-xs text-gray-600 mt-0.5">
                                                    <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                                    {{ $document->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200 chevron-{{ $document->id }} text-xs"></i>
                                    </div>
                                </button>

                                <!-- Contenu détaillé (caché par défaut) -->
                                <div id="document-details-{{ $document->id }}" class="hidden px-4 pb-4">
                                    <div class="pt-3 border-t border-gray-200">
                                        <div class="space-y-3">
                                            @php
                                                $statusColors = [
                                                    'pending' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'icon' => 'clock', 'label' => 'En attente'],
                                                    'signed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'check-circle', 'label' => 'Signé'],
                                                    'paraphed' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'edit', 'label' => 'Paraphé'],
                                                    'signed_and_paraphed' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'icon' => 'check-double', 'label' => 'Signé et paraphé'],
                                                    'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'spinner', 'label' => 'En cours']
                                                ];
                                                $colors = $statusColors[$document->status] ?? $statusColors['pending'];
                                            @endphp
                                            <div>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $colors['bg'] }} {{ $colors['text'] }}">
                                                    <i class="fas fa-{{ $colors['icon'] }} mr-1"></i>
                                                    {{ $colors['label'] }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <p><i class="fas fa-user mr-1"></i> {{ $document->uploader->name ?? 'Inconnu' }}</p>
                                                <p><i class="fas fa-clock mr-1"></i> {{ $document->created_at->format('d/m/Y à H:i') }}</p>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="{{ route('documents.view', $document) }}"
                                                   class="flex-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-xs text-center">
                                                    <i class="fas fa-eye mr-1"></i> Voir
                                                </a>
                                                <a href="{{ route('documents.download', $document) }}"
                                                   class="flex-1 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-xs text-center">
                                                    <i class="fas fa-download mr-1"></i> Télécharger
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($totalPages > 1)
                        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <button type="button"
                                        onclick="changePage('{{ $typeId }}', 'prev')"
                                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                        id="prev-{{ $typeId }}">
                                    <i class="fas fa-chevron-left mr-1"></i> Préc.
                                </button>
                                <span class="text-xs text-gray-600 font-medium">
                                    Page <span id="current-page-{{ $typeId }}">1</span> / {{ $totalPages }}
                                </span>
                                <button type="button"
                                        onclick="changePage('{{ $typeId }}', 'next')"
                                        class="px-3 py-1.5 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                        id="next-{{ $typeId }}">
                                    Suiv. <i class="fas fa-chevron-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun document trouvé</h3>
            <p class="text-gray-600 mb-6">
                @if(request()->hasAny(['search', 'type', 'status', 'date_from']))
                    Aucun document ne correspond à vos critères de recherche.
                @else
                    Vous n'avez aucun document dans l'historique.
                @endif
            </p>
            
            <div class="flex justify-center gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                    <i class="fas fa-home mr-2 text-white"></i>
                    Retour à l'accueil
                </a>
                
                @if(request()->hasAny(['search', 'type', 'status', 'date_from']))
                    <a href="{{ route('documents.history') }}" class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-times mr-2 text-white"></i>
                        Effacer les filtres
                    </a>
                @endif
            </div>
        </div>
    @endif
    </div>
</div>

<script>
// Objet pour stocker la page actuelle de chaque type
const currentPages = {};

// Fonction globale de filtrage
function filterDocuments() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearSearch');
    const resultCount = document.getElementById('resultCount');
    const typeFilter = document.getElementById('type');
    const statusFilter = document.getElementById('status');
    const dateFromFilter = document.getElementById('date_from');

    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedType = typeFilter ? typeFilter.value.toLowerCase() : '';
    const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : '';
    const selectedDate = dateFromFilter ? dateFromFilter.value : '';

    console.log('Filtrage avec:', { searchTerm, selectedType, selectedStatus, selectedDate });

    let visibleCount = 0;

    // Afficher/Masquer le bouton effacer
    if (clearButton) {
        if (searchTerm.length > 0) {
            clearButton.classList.remove('hidden');
            clearButton.classList.add('flex');
        } else {
            clearButton.classList.add('hidden');
            clearButton.classList.remove('flex');
        }
    }

    // Récupérer toutes les colonnes de types
    const typeColumns = document.querySelectorAll('[data-type-column]');
    console.log('Nombre de colonnes trouvées:', typeColumns.length);

    typeColumns.forEach(function(columnParent) {
        const columnType = columnParent.getAttribute('data-document-type') || '';
        console.log('Colonne type:', columnType);
        let columnHasVisibleDocs = false;

        // Récupérer tous les documents de cette colonne
        const documentsInColumn = columnParent.querySelectorAll('.document-item-accordion');

        documentsInColumn.forEach(function(item) {
            const name = item.getAttribute('data-name') || '';
            const type = item.getAttribute('data-type') || '';
            const status = item.getAttribute('data-status') || '';
            const uploader = item.getAttribute('data-uploader') || '';
            const date = item.getAttribute('data-date') || '';

            let matches = true;

            // Filtre de recherche
            if (searchTerm.length > 0) {
                const searchableText = name + ' ' + type + ' ' + status + ' ' + uploader + ' ' + date;
                matches = matches && searchableText.includes(searchTerm);
            }

            // Filtre de type
            if (selectedType.length > 0) {
                matches = matches && type === selectedType;
            }

            // Filtre de statut
            if (selectedStatus.length > 0) {
                matches = matches && status === selectedStatus;
            }

            // Filtre de date
            if (selectedDate.length > 0) {
                const docDate = new Date(date.split('/').reverse().join('-'));
                const filterDate = new Date(selectedDate);
                matches = matches && docDate >= filterDate;
            }

            // Afficher ou masquer le document
            if (matches) {
                item.style.display = '';
                item.classList.remove('opacity-0');
                item.classList.add('opacity-100');
                visibleCount++;
                columnHasVisibleDocs = true;
            } else {
                item.classList.remove('opacity-100');
                item.classList.add('opacity-0');
                setTimeout(function() {
                    if (item.classList.contains('opacity-0')) {
                        item.style.display = 'none';
                    }
                }, 300);
            }
        });

        console.log('Colonne', columnType, '- Documents visibles:', columnHasVisibleDocs);

        // Afficher ou masquer la colonne entière selon si elle a des documents visibles
        if (columnHasVisibleDocs) {
            columnParent.style.display = '';
            columnParent.classList.remove('opacity-0');
            columnParent.classList.add('opacity-100');
        } else {
            columnParent.classList.remove('opacity-100');
            columnParent.classList.add('opacity-0');
            setTimeout(function() {
                if (columnParent.classList.contains('opacity-0')) {
                    columnParent.style.display = 'none';
                }
            }, 300);
        }
    });

    // Mettre à jour le compteur
    if (resultCount) {
        resultCount.textContent = visibleCount;

        // Changer la couleur du compteur
        const countContainer = resultCount.parentElement;
        if (visibleCount === 0) {
            countContainer.classList.remove('bg-success-100', 'border-success-200');
            countContainer.classList.add('bg-danger-100', 'border-danger-200');
            resultCount.classList.remove('text-success-700');
            resultCount.classList.add('text-danger-700');
            resultCount.nextElementSibling.classList.remove('text-success-600');
            resultCount.nextElementSibling.classList.add('text-danger-600');
        } else {
            countContainer.classList.remove('bg-danger-100', 'border-danger-200');
            countContainer.classList.add('bg-success-100', 'border-success-200');
            resultCount.classList.remove('text-danger-700');
            resultCount.classList.add('text-success-700');
            resultCount.nextElementSibling.classList.remove('text-danger-600');
            resultCount.nextElementSibling.classList.add('text-success-600');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearButton = document.getElementById('clearSearch');
    const resultCount = document.getElementById('resultCount');
    const documentItems = document.querySelectorAll('.document-item-accordion');
    const totalDocuments = documentItems.length;
    
    // Auto-focus sur le champ de recherche
    if (searchInput) {
        searchInput.focus();
        
        // Filtrage en temps réel - INSTANTANÉ
        searchInput.addEventListener('input', function() {
            filterDocuments();
        });
        
        // Bouton effacer
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
        
        // Raccourci clavier : Échap pour effacer
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    }
    
    // Ajouter les classes de transition aux documents
    documentItems.forEach(function(item) {
        item.classList.add('transition-all', 'duration-300', 'opacity-100');
    });

    // Ajouter les classes de transition aux colonnes de types
    const typeColumnsElements = document.querySelectorAll('[data-type-column]');
    typeColumnsElements.forEach(function(column) {
        column.classList.add('transition-all', 'duration-300', 'opacity-100');
    });

    // Gestion des filtres avancés
    const toggleButton = document.getElementById('toggleAdvancedFilters');
    const advancedFilters = document.getElementById('advancedFilters');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    const typeFilter = document.getElementById('type');
    const statusFilter = document.getElementById('status');
    const dateFromFilter = document.getElementById('date_from');

    console.log('🔧 Bouton filtres avancés trouvé:', toggleButton ? 'OUI' : 'NON');

    if (toggleButton && advancedFilters && filterToggleIcon) {
        console.log('✅ Event listener ajouté au bouton filtres avancés');
        toggleButton.addEventListener('click', function(e) {
            console.log('🖱️ Clic sur filtres avancés détecté !');
            e.preventDefault();
            e.stopPropagation();

            if (advancedFilters.classList.contains('hidden')) {
                console.log('📂 Ouverture des filtres avancés');
                advancedFilters.classList.remove('hidden');
                filterToggleIcon.classList.remove('fa-chevron-down');
                filterToggleIcon.classList.add('fa-chevron-up');
            } else {
                console.log('📁 Fermeture des filtres avancés');
                advancedFilters.classList.add('hidden');
                filterToggleIcon.classList.remove('fa-chevron-up');
                filterToggleIcon.classList.add('fa-chevron-down');
            }
        });
    } else {
        console.error('❌ Impossible d\'attacher l\'event listener - Éléments manquants');
    }

    // Ajouter des événements aux filtres avancés
    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            filterDocuments();
        });
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterDocuments();
        });
    }
    if (dateFromFilter) {
        dateFromFilter.addEventListener('change', function() {
            filterDocuments();
        });
    }

    // Initialiser la pagination pour chaque type
    const typeElements = document.querySelectorAll('[id^="current-page-"]');
    typeElements.forEach(element => {
        const typeId = element.id.replace('current-page-', '');
        currentPages[typeId] = 1;
        updatePaginationButtons(typeId);
    });

    // Test initial pour vérifier que tout fonctionne
    console.log('📊 Initialisation du filtrage - Colonnes disponibles:', document.querySelectorAll('[data-type-column]').length);
    console.log('📊 Documents disponibles:', document.querySelectorAll('.document-item-accordion').length);
});

// Fonction pour changer de page
function changePage(typeId, direction) {
    const currentPageElement = document.getElementById('current-page-' + typeId);
    const currentPage = currentPages[typeId] || 1;

    // Calculer le nombre total de pages
    const allDocuments = document.querySelectorAll('[class*="document-page-' + typeId + '-"]');
    const totalPages = Math.max(...Array.from(allDocuments).map(doc => {
        const match = doc.className.match(new RegExp('document-page-' + typeId + '-(\\d+)'));
        return match ? parseInt(match[1]) : 1;
    }));

    let newPage = currentPage;

    if (direction === 'next' && currentPage < totalPages) {
        newPage = currentPage + 1;
    } else if (direction === 'prev' && currentPage > 1) {
        newPage = currentPage - 1;
    }

    if (newPage !== currentPage) {
        // Masquer la page actuelle
        const currentDocs = document.querySelectorAll('.document-page-' + typeId + '-' + currentPage);
        currentDocs.forEach(doc => {
            doc.classList.add('hidden');
        });

        // Afficher la nouvelle page
        const newDocs = document.querySelectorAll('.document-page-' + typeId + '-' + newPage);
        newDocs.forEach(doc => {
            doc.classList.remove('hidden');
            doc.classList.add('animate-fadeIn');
        });

        // Mettre à jour la page actuelle
        currentPages[typeId] = newPage;
        currentPageElement.textContent = newPage;

        // Mettre à jour l'état des boutons
        updatePaginationButtons(typeId);
    }
}

// Fonction pour mettre à jour l'état des boutons de pagination
function updatePaginationButtons(typeId) {
    const prevButton = document.getElementById('prev-' + typeId);
    const nextButton = document.getElementById('next-' + typeId);
    const currentPage = currentPages[typeId] || 1;

    // Calculer le nombre total de pages
    const allDocuments = document.querySelectorAll('[class*="document-page-' + typeId + '-"]');
    const totalPages = Math.max(...Array.from(allDocuments).map(doc => {
        const match = doc.className.match(new RegExp('document-page-' + typeId + '-(\\d+)'));
        return match ? parseInt(match[1]) : 1;
    }));

    // Désactiver le bouton précédent si on est sur la première page
    if (prevButton) {
        if (currentPage <= 1) {
            prevButton.disabled = true;
            prevButton.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            prevButton.disabled = false;
            prevButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // Désactiver le bouton suivant si on est sur la dernière page
    if (nextButton) {
        if (currentPage >= totalPages) {
            nextButton.disabled = true;
            nextButton.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            nextButton.disabled = false;
            nextButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Fonction pour toggle l'affichage des détails du document
function toggleDocument(documentId) {
    const detailsDiv = document.getElementById('document-details-' + documentId);
    const chevron = document.querySelector('.chevron-' + documentId);

    if (detailsDiv.classList.contains('hidden')) {
        // Ouvrir
        detailsDiv.classList.remove('hidden');
        detailsDiv.classList.add('animate-fadeIn');
        chevron.classList.add('rotate-180');
    } else {
        // Fermer
        detailsDiv.classList.add('hidden');
        detailsDiv.classList.remove('animate-fadeIn');
        chevron.classList.remove('rotate-180');
    }
}
</script>

<style>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.rotate-180 {
    transform: rotate(180deg);
}

/* Style pour le scroll horizontal */
#documentsList {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

#documentsList::-webkit-scrollbar {
    height: 8px;
}

#documentsList::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

#documentsList::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

#documentsList::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Style pour le scroll vertical dans les sections */
[id^="type-section-"] {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}

[id^="type-section-"]::-webkit-scrollbar {
    width: 6px;
}

[id^="type-section-"]::-webkit-scrollbar-track {
    background: #f8fafc;
}

[id^="type-section-"]::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

[id^="type-section-"]::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
@endsection