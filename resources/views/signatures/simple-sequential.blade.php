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

    <!-- Statistiques avec style EPS-One -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-file-signature text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</h3>
                    <p class="text-gray-600 font-medium">Total documents</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-red-200 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-pen-fancy text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['to_sign'] }}</h3>
                    <p class="text-gray-600 font-medium">À signer maintenant</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-indigo-200 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-clock text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['waiting'] }}</h3>
                    <p class="text-gray-600 font-medium">En attente</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-emerald-200 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-emerald-700 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-check-circle text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['completed'] }}</h3>
                    <p class="text-gray-600 font-medium">Signés</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents à signer maintenant -->
    @if($documentsToSign->count() > 0)
        <div class="mb-8">
            <div class="bg-white border border-red-200 rounded-xl p-4 mb-6 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-pen-fancy text-white"></i>
                    </div>
                    Documents à signer maintenant
                    <span class="ml-3 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">{{ $documentsToSign->count() }}</span>
                </h2>
                <p class="text-gray-600 mt-2 font-medium">Ces documents nécessitent votre signature immédiate</p>
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
        <div class="mb-8">
            <div class="bg-white border border-indigo-200 rounded-xl p-4 mb-6 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    Documents en attente
                    <span class="ml-3 bg-indigo-600 text-white px-3 py-1 rounded-full text-sm font-semibold">{{ $documentsWaiting->count() }}</span>
                </h2>
                <p class="text-gray-600 mt-2 font-medium">Ces documents attendent que d'autres signataires terminent leur signature</p>
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
        <div class="mb-8">
            <div class="bg-white border border-emerald-200 rounded-xl p-4 mb-6 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <div class="w-10 h-10 bg-emerald-700 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    Documents signés
                    <span class="ml-3 bg-emerald-700 text-white px-3 py-1 rounded-full text-sm font-semibold">{{ $documentsCompleted->count() }}</span>
                </h2>
                <p class="text-gray-600 mt-2 font-medium">Ces documents ont été complètement signés</p>
            </div>
            <div class="space-y-4">
                @foreach($documentsCompleted as $document)
                    @include('signatures.document-card', ['document' => $document, 'status' => 'completed'])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Aucun document -->
    @if($stats['total'] == 0)
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-signature text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun document en attente</h3>
            <p class="text-gray-600 mb-6">Vous n'avez actuellement aucun document en attente de signature.</p>
            
            <div class="flex justify-center gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Retour à l'accueil
                </a>
                
                <a href="/create-test-document" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Créer un document de test
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
