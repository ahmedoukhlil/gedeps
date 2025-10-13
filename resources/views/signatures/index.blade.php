@extends('layouts.app')

@section('title', 'Documents à Signer')

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
                <i class="fas fa-pen-fancy text-sm"></i>
                <span class="hidden sm:inline">Documents à Signer</span>
            </li>
        </ol>
    </nav>

    <!-- Carte d'En-tête Élégante -->
    <div class="card card-hover mb-6 sm:mb-8 overflow-hidden relative">
        <!-- Fond décoratif avec dégradé -->
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-danger-500 via-danger-600 to-danger-700 opacity-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-danger-400 rounded-full blur-3xl opacity-20 -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-danger-300 rounded-full blur-3xl opacity-20 -ml-24 -mb-24"></div>
        
        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Informations de la page -->
                <div class="flex items-center gap-4 sm:gap-6 flex-1">
                    <!-- Icône Élégante -->
                    <div class="relative flex-shrink-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-danger-400 to-danger-600 flex items-center justify-center shadow-glow">
                            <i class="fas fa-pen-fancy text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                        </div>
                    </div>
                    
                    <!-- Titre et Description -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                            <i class="fas fa-sparkles text-danger-500 text-xl sm:text-2xl lg:text-3xl"></i>
                            <span class="truncate">Documents à <span class="text-gradient">Signer</span></span>
                        </h1>
                        <p class="text-sm sm:text-base text-gray-600">Documents en attente de votre signature</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 sm:gap-3 flex-shrink-0">
                    <a href="{{ route('documents.history') }}" class="group inline-flex items-center gap-2 px-4 sm:px-6 py-3 sm:py-4 bg-white border-2 border-gray-300 text-gray-700 rounded-xl shadow-elegant hover:shadow-lg hover:-translate-y-1 hover:border-gray-400 transition-all duration-300">
                        <i class="fas fa-history text-sm sm:text-base"></i>
                        <span class="text-xs sm:text-sm font-semibold">Historique</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <div>
                <h4 class="font-bold">Succès !</h4>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <h4 class="font-bold">Erreur</h4>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Statistiques Élégantes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total documents -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-file-signature text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $documents->count() }}</h3>
                        <p class="text-sm text-gray-600 font-medium">Total documents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- À signer -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-danger-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-danger-400 to-danger-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-pen-fancy text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $documents->where('status', 'pending')->count() }}</h3>
                        <p class="text-sm text-gray-600 font-medium">À signer</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- En cours -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-warning-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-warning-400 to-warning-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $documents->where('status', 'in_progress')->count() }}</h3>
                        <p class="text-sm text-gray-600 font-medium">En cours</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signés -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-success-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-success-400 to-success-600 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-check-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $documents->where('status', 'signed')->count() }}</h3>
                        <p class="text-sm text-gray-600 font-medium">Signés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Documents -->
    @if($documents->count() > 0)
        <!-- En-tête de section élégant -->
        <div class="card mb-6 overflow-hidden relative">
            <div class="absolute inset-0 bg-gradient-to-r from-danger-500 to-danger-600 opacity-5"></div>
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-danger-400 via-danger-500 to-danger-600"></div>
            <div class="relative p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-pen-fancy text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Documents à signer</h2>
                            <p class="text-sm text-gray-600">Ces documents nécessitent votre signature</p>
                        </div>
                    </div>
                    @if($documents->where('status', 'pending')->count() > 0)
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-danger-500 to-danger-600 text-white rounded-xl shadow-lg">
                            <i class="fas fa-exclamation-circle animate-pulse"></i>
                            <span class="font-bold">{{ $documents->where('status', 'pending')->count() }}</span>
                            <span class="text-sm">en attente</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Liste des documents -->
        <div class="space-y-4 mb-8">
            @foreach($documents as $document)
                @include('signatures.document-card-simple', ['document' => $document])
            @endforeach
        </div>
    @endif

    <!-- État vide élégant -->
    @if($documents->count() == 0)
        <div class="card card-hover">
            <div class="text-center py-16">
                <!-- Icône animée -->
                <div class="relative inline-block mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto">
                        <i class="fas fa-file-signature text-gray-400 text-4xl"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-success-500 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                </div>

                <!-- Texte -->
                <h3 class="text-2xl font-bold text-gray-900 mb-2 flex items-center justify-center gap-2">
                    <i class="fas fa-sparkles text-success-500"></i>
                    <span>Tout est à jour !</span>
                </h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Vous n'avez actuellement aucun document en attente de signature. 
                    Profitez de ce moment pour vous détendre !
                </p>
                
                <!-- Bouton -->
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold">Retour à l'accueil</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    @endif

    <!-- Pagination élégante -->
    @if(isset($documents) && $documents->hasPages())
        <div class="flex justify-center mt-8">
            <div class="card p-4">
                {{ $documents->links('pagination.custom') }}
            </div>
        </div>
    @endif
</div>
@endsection