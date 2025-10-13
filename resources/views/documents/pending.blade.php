@extends('layouts.app')

@section('title', 'Documents en Attente')

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
                <i class="fas fa-clock text-sm"></i>
                <span>En Attente</span>
            </li>
        </ol>
    </nav>
    
    <!-- Carte d'En-tête Élégante -->
    <div class="card card-hover mb-6 sm:mb-8 overflow-hidden relative">
        <!-- Fond décoratif avec dégradé -->
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 opacity-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-400 rounded-full blur-3xl opacity-20 -mr-32 -mt-32"></div>
        
        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Informations de la page -->
                <div class="flex items-center gap-4 sm:gap-6 flex-1">
                    <!-- Icône Élégante -->
                    <div class="relative flex-shrink-0">
                        @if(auth()->user()->isSignataire())
                            <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-glow">
                                <i class="fas fa-pen-fancy text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                            </div>
                        @elseif(auth()->user()->isAdmin())
                            <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-warning-400 to-warning-600 flex items-center justify-center shadow-glow">
                                <i class="fas fa-clock text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                            </div>
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-info-400 to-info-600 flex items-center justify-center shadow-glow">
                                <i class="fas fa-upload text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Titre et Description -->
                    <div class="flex-1 min-w-0">
                        @if(auth()->user()->isSignataire())
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                                <i class="fas fa-sparkles text-primary-500 text-xl sm:text-2xl lg:text-3xl"></i>
                                <span class="truncate">Documents à <span class="text-gradient">Signer</span></span>
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600">Voici les documents qui vous ont été assignés pour signature</p>
                        @elseif(auth()->user()->isAdmin())
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                                <i class="fas fa-sparkles text-warning-500 text-xl sm:text-2xl lg:text-3xl"></i>
                                <span class="truncate">Documents en <span class="text-gradient">Attente</span></span>
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600 hidden sm:block">Vue d'ensemble de tous les documents en attente de signature</p>
                        @else
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                                <i class="fas fa-sparkles text-info-500 text-xl sm:text-2xl lg:text-3xl"></i>
                                <span class="truncate">Mes Documents <span class="text-gradient">Soumis</span></span>
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600 hidden sm:block">Documents que vous avez soumis et qui sont en attente de signature</p>
                        @endif
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 sm:gap-3 flex-shrink-0">
                    <a href="{{ route('documents.history') }}" class="group inline-flex items-center gap-2 px-4 sm:px-6 py-3 sm:py-4 bg-white border-2 border-gray-300 text-gray-700 rounded-xl shadow-elegant hover:shadow-lg hover:-translate-y-1 hover:border-gray-400 transition-all duration-300">
                        <i class="fas fa-history text-sm sm:text-base"></i>
                        <span class="text-xs sm:text-sm font-semibold">Historique</span>
                    </a>
                    @if(auth()->user()->isAgent())
                        <a href="{{ route('documents.upload') }}" class="group inline-flex items-center gap-2 px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl shadow-elegant hover:shadow-glow hover:-translate-y-1 transition-all duration-300">
                            <i class="fas fa-plus text-sm sm:text-base"></i>
                            <span class="text-xs sm:text-sm font-semibold"><span class="hidden sm:inline">Nouveau </span>Document</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
        
    <!-- Statistiques rapides - Cartes Élégantes -->
    @if($documents->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 lg:gap-6 mb-6 sm:mb-8">
        <!-- Total Documents -->
        <div class="group relative card card-hover p-6 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
            <div class="relative flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $documents->count() }}</h3>
                    <p class="text-sm text-gray-600 font-medium">Documents en attente</p>
                </div>
            </div>
        </div>
        
        <!-- En attente de Signature -->
        <div class="group relative card card-hover p-6 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-warning-400 to-warning-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
            <div class="relative flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-warning-400 to-warning-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-clock text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $documents->where('status', 'pending')->count() }}</h3>
                    <p class="text-sm text-gray-600 font-medium">En attente de signature</p>
                </div>
            </div>
        </div>
        
        <!-- Documents Urgents -->
        <div class="group relative card card-hover p-6 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-danger-400 to-danger-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
            <div class="relative flex items-center gap-4">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-danger-400 to-danger-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $documents->where('created_at', '<', now()->subDays(7))->count() }}</h3>
                    <p class="text-sm text-gray-600 font-medium">Documents urgents</p>
                </div>
            </div>
        </div>
    </div>
    @endif
        
    @if($documents->count() > 0)
        <!-- Tableau des documents - Carte Élégante -->
        <div class="card card-hover overflow-hidden">
            <!-- En-tête du tableau avec design moderne -->
            <div class="relative p-4 sm:p-6 lg:p-8 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Titre avec badge -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-elegant">
                            <i class="fas fa-list text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                                @if(auth()->user()->isSignataire())
                                    <span class="hidden sm:inline">Documents à signer</span>
                                    <span class="sm:hidden">Documents</span>
                                @elseif(auth()->user()->isAdmin())
                                    <span class="hidden lg:inline">Tous les documents en attente</span>
                                    <span class="lg:hidden">Documents</span>
                                @else
                                    <span class="hidden sm:inline">Mes documents soumis</span>
                                    <span class="sm:hidden">Mes documents</span>
                                @endif
                                <span class="inline-flex items-center px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold">
                                    {{ $documents->count() }}
                                </span>
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Liste complète des documents en attente de traitement</p>
                        </div>
                    </div>
                    
                    <!-- Contrôles de tri -->
                    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                        <i class="fas fa-sort text-gray-400 text-sm"></i>
                        <select id="sort_by" class="border-0 bg-transparent text-sm text-gray-700 font-medium focus:outline-none focus:ring-0 pr-8">
                            <option value="created_at_desc">📅 Date (récent)</option>
                            <option value="created_at_asc">📅 Date (ancien)</option>
                            <option value="filename_asc">🔤 Nom (A-Z)</option>
                            <option value="filename_desc">🔤 Nom (Z-A)</option>
                            <option value="file_size_desc">📊 Taille (grand)</option>
                            <option value="file_size_asc">📊 Taille (petit)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Version desktop (lg+) -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-file-alt text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Document</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Type</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    @if(auth()->user()->isSignataire())
                                        <i class="fas fa-user-circle text-primary-500 text-sm"></i>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Soumis par</span>
                                    @elseif(auth()->user()->isAdmin())
                                        <i class="fas fa-users text-primary-500 text-sm"></i>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Soumis par / Assigné à</span>
                                    @else
                                        <i class="fas fa-user-check text-primary-500 text-sm"></i>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Assigné à</span>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Date de soumission</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tasks text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Statut</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-bolt text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($documents as $document)
                            @php
                                $isUrgent = $document->created_at < now()->subDays(7);
                                $daysSinceCreated = $document->created_at->diffInDays(now());
                            @endphp
                            <tr class="group hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent transition-all duration-200 {{ $isUrgent ? 'bg-red-50/30' : '' }}">
                                <!-- Document -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-shrink-0">
                                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-200">
                                                <i class="fas fa-file-pdf text-white text-lg"></i>
                                            </div>
                                            @if($isUrgent)
                                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-danger-500 rounded-full flex items-center justify-center animate-pulse">
                                                    <i class="fas fa-exclamation text-white text-xs"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                                                {{ $document->document_name ?? $document->filename_original }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs font-medium">
                                                    <i class="fas fa-weight-hanging text-gray-400 text-xs mr-1"></i>
                                                    {{ number_format($document->file_size / 1024, 1) }} KB
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Type -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-100 text-primary-700 rounded-lg text-sm font-semibold">
                                        <i class="fas fa-tag text-xs"></i>
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </td>
                                
                                <!-- Utilisateurs -->
                                <td class="px-6 py-4">
                                    @if(auth()->user()->isSignataire())
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center shadow-sm">
                                                <i class="fas fa-user text-gray-600 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ $document->uploader->name }}</span>
                                        </div>
                                    @elseif(auth()->user()->isAdmin())
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-sm">
                                                    <i class="fas fa-upload text-white text-xs"></i>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700">{{ $document->uploader->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 pl-1">
                                                <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
                                                <div class="w-6 h-6 bg-gradient-to-br from-success-400 to-success-600 rounded-full flex items-center justify-center shadow-sm">
                                                    <i class="fas fa-user-check text-white text-[10px]"></i>
                                                </div>
                                                <span class="text-xs font-medium text-gray-600">{{ $document->signer?->name ?? 'Non assigné' }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-sm">
                                                <i class="fas fa-user-check text-white text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ $document->signer?->name ?? 'Non assigné' }}</span>
                                        </div>
                                    @endif
                                </td>
                                
                                <!-- Date -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-900">{{ $document->created_at->format('d/m/Y') }}</span>
                                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-clock text-[10px]"></i>
                                                {{ $document->created_at->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <!-- Statut -->
                                <td class="px-6 py-4">
                                    @php
                                        $statusConfig = [
                                            'pending' => [
                                                'label' => 'En attente',
                                                'icon' => 'fa-clock',
                                                'bg' => 'bg-warning-100',
                                                'text' => 'text-warning-700',
                                                'border' => 'border-warning-200'
                                            ],
                                            'signed' => [
                                                'label' => 'Signé',
                                                'icon' => 'fa-check-circle',
                                                'bg' => 'bg-success-100',
                                                'text' => 'text-success-700',
                                                'border' => 'border-success-200'
                                            ],
                                            'paraphed' => [
                                                'label' => 'Paraphé',
                                                'icon' => 'fa-pen',
                                                'bg' => 'bg-info-100',
                                                'text' => 'text-info-700',
                                                'border' => 'border-info-200'
                                            ],
                                            'signed_and_paraphed' => [
                                                'label' => 'Signé & Paraphé',
                                                'icon' => 'fa-check-double',
                                                'bg' => 'bg-success-100',
                                                'text' => 'text-success-700',
                                                'border' => 'border-success-200'
                                            ],
                                            'cacheted' => [
                                                'label' => 'Cacheté',
                                                'icon' => 'fa-stamp',
                                                'bg' => 'bg-purple-100',
                                                'text' => 'text-purple-700',
                                                'border' => 'border-purple-200'
                                            ],
                                            'refused' => [
                                                'label' => 'Refusé',
                                                'icon' => 'fa-times-circle',
                                                'bg' => 'bg-danger-100',
                                                'text' => 'text-danger-700',
                                                'border' => 'border-danger-200'
                                            ],
                                        ];
                                        
                                        $currentStatus = $statusConfig[$document->status] ?? $statusConfig['pending'];
                                        $pulseClass = $isUrgent && $document->status === 'pending' ? 'animate-pulse' : '';
                                    @endphp
                                    
                                    <div class="flex flex-col gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $currentStatus['bg'] }} {{ $currentStatus['text'] }} rounded-lg text-sm font-semibold border {{ $currentStatus['border'] }} {{ $pulseClass }}">
                                            <i class="fas {{ $currentStatus['icon'] }} text-xs"></i>
                                            {{ $currentStatus['label'] }}
                                        </span>
                                        @if($isUrgent && $document->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-danger-50 text-danger-600 rounded text-xs font-medium">
                                                <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                                Urgent ({{ $daysSinceCreated }}j)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                                           class="group inline-flex items-center gap-1.5 px-3 py-2 bg-white border-2 border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 hover:border-gray-400 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200"
                                           title="Voir le document">
                                            <i class="fas fa-eye text-xs group-hover:scale-110 transition-transform"></i>
                                            <span class="hidden xl:inline">Voir</span>
                                        </a>
                                        
                                        @if(auth()->user()->isSignataire())
                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                                               class="group inline-flex items-center gap-1.5 px-3 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-lg text-sm font-semibold hover:from-primary-600 hover:to-primary-700 hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200"
                                               title="Signer le document">
                                                <i class="fas fa-pen-fancy text-xs group-hover:scale-110 transition-transform"></i>
                                                <span class="hidden xl:inline">Signer</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Version mobile/tablette -->
            <div class="lg:hidden space-y-4">
                @foreach($documents as $document)
                    @php
                        $isUrgent = $document->created_at < now()->subDays(7);
                        $daysSinceCreated = $document->created_at->diffInDays(now());
                    @endphp
                    <div class="mobile-document-card {{ $isUrgent ? 'urgent-card' : '' }}">
                        <div class="mobile-card-header">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-pdf text-white text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium sophisticated-heading truncate">{{ $document->document_name ?? $document->filename_original }}</h3>
                                    <p class="text-sm sophisticated-caption">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="status status-pending">
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mobile-card-body">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                                <div class="mobile-info-item">
                                    <div class="mobile-info-label">
                                        @if(auth()->user()->isSignataire())
                                            Soumis par
                                        @elseif(auth()->user()->isAdmin())
                                            Soumis par
                                        @else
                                            Assigné à
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user sophisticated-body text-xs"></i>
                                        </div>
                                        <span class="text-sm sophisticated-body">{{ $document->uploader->name }}</span>
                                    </div>
                                </div>
                                
                                <div class="mobile-info-item">
                                    <div class="mobile-info-label">Date</div>
                                    <div class="text-sm sophisticated-caption">
                                        {{ $document->created_at->format('d/m/Y') }} à {{ $document->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                            
                            @if(auth()->user()->isAdmin())
                                <div class="mobile-info-item mb-4">
                                    <div class="mobile-info-label">Assigné à</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user-check text-white text-xs"></i>
                                        </div>
                                        <span class="text-sm sophisticated-body">{{ $document->signer?->name ?? 'Non assigné' }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex items-center justify-between">
                                <div class="mobile-priority">
                                    @if($isUrgent)
                                        <span class="status status-urgent">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Urgent ({{ $daysSinceCreated }}j)
                                        </span>
                                    @elseif($daysSinceCreated > 3)
                                        <span class="status status-warning">
                                            <i class="fas fa-clock"></i>
                                            En attente ({{ $daysSinceCreated }}j)
                                        </span>
                                    @else
                                        <span class="status status-info">
                                            <i class="fas fa-clock"></i>
                                            Récent
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mobile-actions">
                                    <div class="flex gap-2">
                                        <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'view']) }}" 
                                           class="btn btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i>
                                            <span class="hidden sm:inline">Voir</span>
                                        </a>
                                        
                                        @if(auth()->user()->isSignataire())
                                            <a href="{{ route('documents.process.show', ['document' => $document, 'action' => 'sign']) }}" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-pen-fancy"></i>
                                                <span class="hidden sm:inline">Signer</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Message si aucun document -->
        <div class="empty-state">
            <div class="empty-icon">
                @if(auth()->user()->isSignataire())
                    <i class="fas fa-pen-fancy"></i>
                @elseif(auth()->user()->isAdmin())
                    <i class="fas fa-clock"></i>
                @else
                    <i class="fas fa-upload"></i>
                @endif
            </div>
            <h3>
                @if(auth()->user()->isSignataire())
                    Aucun document à signer
                @elseif(auth()->user()->isAdmin())
                    Aucun document en attente
                @else
                    Aucun document soumis
                @endif
            </h3>
            <p>
                @if(auth()->user()->isSignataire())
                    Vous n'avez actuellement aucun document assigné pour signature.
                @elseif(auth()->user()->isAdmin())
                    Tous les documents ont été traités.
                @else
                    Vous n'avez pas encore soumis de documents à l'approbation.
                @endif
            </p>
            @if(auth()->user()->isAgent())
                <div class="mt-6">
                    <a href="{{ route('documents.upload') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Soumettre un document
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>

<style>
/* Styles pour la vue Documents à Signer */
.stat-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 80px;
    border: 1px solid #f1f3f4;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    border-color: #e3f2fd;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.stat-total .stat-icon { 
    background: linear-gradient(135deg, #007bff, #0056b3);
}
.stat-pending .stat-icon { 
    background: linear-gradient(135deg, #ffc107, #ff8f00);
}
.stat-urgent .stat-icon { 
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.stat-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0;
    line-height: 1;
}

.stat-label {
    color: #64748b;
    margin: 4px 0 0 0;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Styles pour les lignes urgentes */
.urgent-row {
    background: linear-gradient(90deg, #fef2f2, #ffffff);
    border-left: 4px solid #dc3545;
}

.urgent-row:hover {
    background: linear-gradient(90deg, #fee2e2, #f9fafb);
}

/* Statuts de priorité */
.status-urgent {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Animation des lignes du tableau */
.document-row {
    transition: all 0.3s ease;
}

.document-row:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Cartes mobiles pour les documents */
.mobile-document-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s ease;
}

.mobile-document-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.mobile-document-card.urgent-card {
    border-left: 4px solid #dc3545;
    background: linear-gradient(90deg, #fef2f2, #ffffff);
}

.mobile-card-header {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.mobile-card-body {
    padding: 1rem;
}

.mobile-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.mobile-info-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mobile-priority {
    display: flex;
    align-items: center;
}

.mobile-actions .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    min-height: 36px;
}

.mobile-actions .btn i {
    font-size: 0.9rem;
}

/* Responsive amélioré */
@media (max-width: 768px) {
    .stat-card {
        padding: 16px 18px;
        height: 70px;
        gap: 16px;
    }
    
    .stat-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .mobile-document-card {
        margin-bottom: 1rem;
    }
    
    .mobile-card-header,
    .mobile-card-body {
        padding: 0.875rem;
    }
}

@media (max-width: 640px) {
    .stat-card {
        padding: 14px 16px;
        height: 65px;
        gap: 12px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .stat-number {
        font-size: 1.8rem;
    }
    
    .stat-label {
        font-size: 0.75rem;
    }
    
    .mobile-document-card {
        border-radius: 8px;
    }
    
    .mobile-card-header,
    .mobile-card-body {
        padding: 0.75rem;
    }
    
    .mobile-actions .btn {
        padding: 0.5rem;
        font-size: 0.75rem;
        min-height: 32px;
    }
    
    .mobile-actions .btn span {
        display: none;
    }
    
    .mobile-actions .btn i {
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .mobile-card-header {
        padding: 0.75rem 0.5rem;
    }
    
    .mobile-card-body {
        padding: 0.75rem 0.5rem;
    }
    
    .mobile-info-item {
        margin-bottom: 0.5rem;
    }
    
    .mobile-actions {
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    
    .mobile-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des statistiques
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animation des lignes du tableau
    const tableRows = document.querySelectorAll('.document-row');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, index * 50);
    });
});
</script>
@endsection