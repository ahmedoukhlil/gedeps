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

    <!-- Barre de recherche globale -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('documents.history') }}" class="space-y-4">
            <!-- Recherche globale -->
            <div class="relative">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>
                    Recherche globale
                </label>
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Rechercher par nom du document, soumissionnaire, type, statut..."
                           class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Recherche automatique : tapez pour rechercher par nom du document, soumissionnaire, type, statut ou description
                </p>
            </div>

            <!-- Filtres avancés (optionnels) -->
            <div class="border-t pt-4">
                <button type="button" id="toggleAdvancedFilters" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
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
                            <select id="type" name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous les types</option>
                                <option value="contrat" {{ request('type') == 'contrat' ? 'selected' : '' }}>Contrat</option>
                                <option value="facture" {{ request('type') == 'facture' ? 'selected' : '' }}>Facture</option>
                                <option value="rapport" {{ request('type') == 'rapport' ? 'selected' : '' }}>Rapport</option>
                                <option value="autre" {{ request('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>

                        <!-- Statut -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Statut
                            </label>
                            <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>Signé</option>
                                <option value="paraphed" {{ request('status') == 'paraphed' ? 'selected' : '' }}>Paraphé</option>
                                <option value="signed_and_paraphed" {{ request('status') == 'signed_and_paraphed' ? 'selected' : '' }}>Signé et paraphé</option>
                            </select>
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

            <div class="flex justify-between items-center pt-4 border-t">
                <div class="flex gap-3">
                    <a href="{{ route('documents.history') }}" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold">
                        <i class="fas fa-times mr-2 text-white"></i>
                        Effacer les filtres
                    </a>
                </div>
                <div class="text-sm text-gray-700 font-medium bg-gray-100 px-4 py-2 rounded-lg">
                    <i class="fas fa-info-circle mr-1 text-blue-600"></i>
                    {{ $documents->total() }} document(s) trouvé(s)
                </div>
            </div>
        </form>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-blue-900">{{ $documents->total() }}</h3>
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
                    <h3 class="text-3xl font-bold text-orange-900">{{ $documents->where('status', 'pending')->count() }}</h3>
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
                    <h3 class="text-3xl font-bold text-emerald-900">{{ $documents->where('status', 'signed')->count() }}</h3>
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
                    <h3 class="text-3xl font-bold text-purple-900">{{ $documents->where('status', 'paraphed')->count() }}</h3>
                    <p class="text-purple-700 font-medium">Paraphés</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des documents -->
    @if($documents->count() > 0)
        <div class="space-y-4">
            @foreach($documents as $document)
                @include('documents.document-card-history', ['document' => $document])
            @endforeach
        </div>

        <!-- Pagination -->
        @if($documents->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white rounded-lg shadow-md p-4">
                    {{ $documents->links('pagination.custom') }}
                </div>
            </div>
        @endif
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
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des filtres avancés
    const toggleButton = document.getElementById('toggleAdvancedFilters');
    const advancedFilters = document.getElementById('advancedFilters');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    
    if (toggleButton && advancedFilters && filterToggleIcon) {
        toggleButton.addEventListener('click', function() {
            if (advancedFilters.classList.contains('hidden')) {
                advancedFilters.classList.remove('hidden');
                filterToggleIcon.classList.remove('fa-chevron-down');
                filterToggleIcon.classList.add('fa-chevron-up');
            } else {
                advancedFilters.classList.add('hidden');
                filterToggleIcon.classList.remove('fa-chevron-up');
                filterToggleIcon.classList.add('fa-chevron-down');
            }
        });
    }
    
    // Auto-focus sur le champ de recherche
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.focus();
    }
    
    // Recherche en temps réel (optionnel)
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Optionnel : recherche automatique après 500ms de pause
                // this.form.submit();
            }, 500);
        });
    }
});
</script>
@endsection