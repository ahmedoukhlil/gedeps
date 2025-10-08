@extends('layouts.app')

@section('title', 'Signatures Séquentielles - Simple')

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
                <i class="fas fa-list-ol"></i>
                <span class="hidden sm:inline ml-1">Signatures Séquentielles</span>
            </li>
        </ol>
    </nav>
    
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            ✍️ Signatures Séquentielles
        </h1>
        <p class="text-gray-600">
            Documents en attente de votre signature dans l'ordre défini
        </p>
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

    <!-- Statistiques avec style EPS-One - Responsive amélioré -->
    <div class="stats-grid mb-8">
        <div class="signature-stats bg-white border border-gray-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-xl flex items-center justify-center mr-3 sm:mr-4 shadow-lg icon-responsive">
                    <i class="fas fa-file-signature text-white text-sm sm:text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="stat-number text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['total'] }}</h3>
                    <p class="stat-label text-sm sm:text-base text-gray-600 font-medium truncate">Total documents</p>
                </div>
            </div>
        </div>

        <div class="signature-stats bg-white border border-red-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-500 rounded-xl flex items-center justify-center mr-3 sm:mr-4 shadow-lg icon-responsive">
                    <i class="fas fa-pen-fancy text-white text-sm sm:text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="stat-number text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['to_sign'] }}</h3>
                    <p class="stat-label text-sm sm:text-base text-gray-600 font-medium truncate">À signer maintenant</p>
                </div>
            </div>
        </div>

        <div class="signature-stats bg-white border border-indigo-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-600 rounded-xl flex items-center justify-center mr-3 sm:mr-4 shadow-lg icon-responsive">
                    <i class="fas fa-clock text-white text-sm sm:text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="stat-number text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['waiting'] }}</h3>
                    <p class="stat-label text-sm sm:text-base text-gray-600 font-medium truncate">En attente</p>
                </div>
            </div>
        </div>

        <div class="signature-stats bg-white border border-emerald-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-700 rounded-xl flex items-center justify-center mr-3 sm:mr-4 shadow-lg icon-responsive">
                    <i class="fas fa-check-circle text-white text-sm sm:text-lg"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="stat-number text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['completed'] }}</h3>
                    <p class="stat-label text-sm sm:text-base text-gray-600 font-medium truncate">Signés</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents à signer maintenant - Responsive -->
    @if($documentsToSign->count() > 0)
        <div class="mb-8">
            <div class="bg-white border border-red-200 rounded-xl p-4 sm:p-6 mb-6 shadow-sm">
                <div class="section-header">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0 icon-responsive">
                            <i class="fas fa-pen-fancy text-white text-sm sm:text-base"></i>
                        </div>
                        <span class="text-lg sm:text-xl text-responsive">Documents à signer maintenant</span>
                        <span class="status-badge bg-red-500 text-white">{{ $documentsToSign->count() }}</span>
                    </div>
                </div>
                <p class="text-sm sm:text-base text-gray-600 mt-2 font-medium text-responsive">Ces documents nécessitent votre signature immédiate</p>
            </div>
            <div class="space-y-4">
                @foreach($documentsToSign as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'to_sign'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents en attente - Responsive -->
    @if($documentsWaiting->count() > 0)
        <div class="mb-8">
            <div class="bg-white border border-indigo-200 rounded-xl p-4 sm:p-6 mb-6 shadow-sm">
                <div class="section-header">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0 icon-responsive">
                            <i class="fas fa-clock text-white text-sm sm:text-base"></i>
                        </div>
                        <span class="text-lg sm:text-xl text-responsive">Documents en attente</span>
                        <span class="status-badge bg-indigo-600 text-white">{{ $documentsWaiting->count() }}</span>
                    </div>
                </div>
                <p class="text-sm sm:text-base text-gray-600 mt-2 font-medium text-responsive">Ces documents attendent que d'autres signataires terminent leur signature</p>
            </div>
            <div class="space-y-4">
                @foreach($documentsWaiting as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'waiting'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents signés - Responsive -->
    @if($documentsCompleted->count() > 0)
        <div class="mb-8">
            <div class="bg-white border border-emerald-200 rounded-xl p-4 sm:p-6 mb-6 shadow-sm">
                <div class="section-header">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-700 rounded-lg flex items-center justify-center flex-shrink-0 icon-responsive">
                            <i class="fas fa-check-circle text-white text-sm sm:text-base"></i>
                        </div>
                        <span class="text-lg sm:text-xl text-responsive">Documents signés</span>
                        <span class="status-badge bg-emerald-700 text-white">{{ $documentsCompleted->count() }}</span>
                    </div>
                </div>
                <p class="text-sm sm:text-base text-gray-600 mt-2 font-medium text-responsive">Ces documents ont été complètement signés</p>
            </div>
            <div class="space-y-4">
                @foreach($documentsCompleted as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'completed'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Aucun document - Responsive -->
    @if($stats['total'] == 0)
        <div class="text-center py-8 sm:py-12">
            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-signature text-gray-400 text-2xl sm:text-3xl"></i>
            </div>
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Aucun document en attente</h3>
            <p class="text-sm sm:text-base text-gray-600 mb-6">Vous n'avez actuellement aucun document en attente de signature.</p>
            
            <div class="flex justify-center">
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 sm:px-6 sm:py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm sm:text-base">
                    <i class="fas fa-home mr-2"></i>
                    <span class="hidden sm:inline">Retour à l'accueil</span>
                    <span class="sm:hidden">Accueil</span>
                </a>
            </div>
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
