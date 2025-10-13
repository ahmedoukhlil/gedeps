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
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistiques Élégantes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary-100 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="fas fa-file-signature text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">{{ $stats['total'] }}</h3>
                <p class="text-sm text-gray-600 font-medium">Total documents</p>
            </div>
        </div>

        <!-- À signer -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-danger-100 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-danger-500 to-danger-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="fas fa-pen-fancy text-white text-2xl"></i>
                    </div>
                    @if($stats['to_sign'] > 0)
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-danger-500"></span>
                        </span>
                    @endif
                </div>
                <h3 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">{{ $stats['to_sign'] }}</h3>
                <p class="text-sm text-gray-600 font-medium">À signer maintenant</p>
            </div>
        </div>

        <!-- En attente -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-warning-100 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">{{ $stats['waiting'] }}</h3>
                <p class="text-sm text-gray-600 font-medium">En attente</p>
            </div>
        </div>

        <!-- Signés -->
        <div class="card card-hover overflow-hidden relative group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-success-100 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-300"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">{{ $stats['completed'] }}</h3>
                <p class="text-sm text-gray-600 font-medium">Signés</p>
            </div>
        </div>
    </div>

    <!-- Documents à signer maintenant -->
    @if($documentsToSign->count() > 0)
        <div class="mb-6 sm:mb-8">
            <div class="card overflow-hidden border-l-4 border-danger-500 mb-4">
                <div class="p-4 sm:p-6 bg-gradient-to-r from-danger-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-danger-500 to-danger-700 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-pen-fancy text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Documents à signer maintenant</h2>
                                <p class="text-sm text-gray-600 mt-1">Ces documents nécessitent votre signature immédiate</p>
                            </div>
                        </div>
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 bg-danger-500 text-white rounded-lg font-bold shadow-md">
                            {{ $documentsToSign->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($documentsToSign as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'to_sign'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents en attente -->
    @if($documentsWaiting->count() > 0)
        <div class="mb-6 sm:mb-8">
            <div class="card overflow-hidden border-l-4 border-warning-500 mb-4">
                <div class="p-4 sm:p-6 bg-gradient-to-r from-warning-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-warning-500 to-warning-700 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Documents en attente</h2>
                                <p class="text-sm text-gray-600 mt-1">Ces documents attendent que d'autres signataires terminent</p>
                            </div>
                        </div>
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 bg-warning-500 text-white rounded-lg font-bold shadow-md">
                            {{ $documentsWaiting->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($documentsWaiting as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'waiting'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents signés -->
    @if($documentsCompleted->count() > 0)
        <div class="mb-6 sm:mb-8">
            <div class="card overflow-hidden border-l-4 border-success-500 mb-4">
                <div class="p-4 sm:p-6 bg-gradient-to-r from-success-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-success-500 to-success-700 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900">Documents signés</h2>
                                <p class="text-sm text-gray-600 mt-1">Ces documents ont été complètement signés</p>
                            </div>
                        </div>
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10 bg-success-500 text-white rounded-lg font-bold shadow-md">
                            {{ $documentsCompleted->count() }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                @foreach($documentsCompleted as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'completed'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- État vide élégant -->
    @if($stats['total'] == 0)
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
            <a href="{{ route('home') }}" class="group inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-xl shadow-elegant hover:shadow-glow transition-all duration-300 hover:-translate-y-1">
                <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                <span class="font-semibold">Retour à l'accueil</span>
            </a>
        </div>
    @endif

    <!-- Pagination -->
    @if(isset($allDocuments) && $allDocuments->hasPages())
        <div class="mt-8 flex justify-center">
            <div class="bg-white rounded-lg shadow-md p-4">
                {{ $allDocuments->links('pagination.custom') }}
            </div>
        </div>
    @endif
    </div>
</div>
@endsection
