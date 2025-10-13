@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="container-fluid px-3 sm:px-4 lg:px-6">
    <div class="max-w-7xl mx-auto">
        <!-- Carte de Bienvenue Élégante -->
        <div class="card card-hover mb-6 sm:mb-8 overflow-hidden relative">
            <!-- Fond décoratif avec dégradé -->
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 opacity-10"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-400 rounded-full blur-3xl opacity-20 -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-primary-300 rounded-full blur-3xl opacity-20 -ml-24 -mb-24"></div>
            
            <div class="relative p-6 sm:p-8 lg:p-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Informations Utilisateur -->
                    <div class="flex items-center gap-4 sm:gap-6">
                        <!-- Avatar Élégant -->
                        <div class="relative flex-shrink-0">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-glow">
                                <span class="text-white text-2xl sm:text-3xl lg:text-4xl font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 bg-success-500 border-3 border-white rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                        
                        <!-- Texte de Bienvenue -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                                <i class="fas fa-sparkles text-primary-500 text-xl sm:text-2xl lg:text-3xl"></i>
                                <span class="truncate">Bienvenue, <span class="text-gradient">{{ auth()->user()->name }}</span> !</span>
                            </h1>
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Badge Rôle -->
                                <span class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl text-sm font-semibold shadow-elegant">
                                    @if(auth()->user()->isAdmin())
                                        <i class="fas fa-crown"></i>
                                        <span>Administrateur</span>
                                    @elseif(auth()->user()->isSignataire())
                                        <i class="fas fa-pen-fancy"></i>
                                        <span>Signataire</span>
                                    @else
                                        <i class="fas fa-user"></i>
                                        <span>Agent</span>
                                    @endif
                                </span>
                                
                                <!-- Email Badge -->
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs sm:text-sm">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                    <span class="truncate max-w-[200px]">{{ auth()->user()->email }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Principale -->
                    <div class="flex-shrink-0">
                        @if(auth()->user()->isAgent())
                            <a href="{{ route('documents.upload') }}" class="group inline-flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl shadow-elegant hover:shadow-glow hover:-translate-y-1 hover:scale-105 transition-all duration-300">
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center group-hover:rotate-6 transition-transform duration-300">
                                    <i class="fas fa-upload text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold">Nouveau Document</div>
                                    <div class="text-xs opacity-90">Soumettre pour signature</div>
                                </div>
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        @elseif(auth()->user()->isSignataire())
                            <a href="{{ route('documents.upload') }}" class="group inline-flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl shadow-elegant hover:shadow-glow hover:-translate-y-1 hover:scale-105 transition-all duration-300">
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center group-hover:rotate-6 transition-transform duration-300">
                                    <i class="fas fa-upload text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold">Soumettre Document</div>
                                    <div class="text-xs opacity-90">Nouveau document</div>
                                </div>
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="group inline-flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl shadow-elegant hover:shadow-glow hover:-translate-y-1 hover:scale-105 transition-all duration-300">
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center group-hover:rotate-6 transition-transform duration-300">
                                    <i class="fas fa-cog text-lg"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold">Administration</div>
                                    <div class="text-xs opacity-90">Gérer le système</div>
                                </div>
                                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform duration-300"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques essentielles - Grid responsive avec cartes élégantes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 lg:gap-6 mb-6 sm:mb-8">
            @if(auth()->user()->isAdmin())
                <!-- Utilisateurs -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['total_users'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Utilisateurs</p>
                        </div>
                    </div>
                </div>
                
                <!-- Documents -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-file-alt text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['total_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Documents</p>
                        </div>
                    </div>
                </div>
                
                <!-- En Attente -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-warning-400 to-warning-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-warning-400 to-warning-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['pending_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">En Attente</p>
                        </div>
                    </div>
                </div>
                
                <!-- Signés -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['signed_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Signés</p>
                        </div>
                    </div>
                </div>
                
                <!-- Séquentielles -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-info-400 to-info-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-info-400 to-info-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-list-ol text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['sequential_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Séquentielles</p>
                        </div>
                    </div>
                </div>
                
                <!-- En Cours -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-warning-400 to-warning-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-warning-400 to-warning-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['sequential_pending'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">En Cours</p>
                        </div>
                    </div>
                </div>

            @elseif(auth()->user()->isAgent())
                <!-- Mes Documents -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-file-alt text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['my_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Mes Documents</p>
                        </div>
                    </div>
                </div>
                
                <!-- En Attente -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-warning-400 to-warning-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-warning-400 to-warning-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['pending_approval'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">En Attente</p>
                        </div>
                    </div>
                </div>
                
                <!-- Signés -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['signed_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Signés</p>
                        </div>
                    </div>
                </div>
                
                <!-- Séquentielles Créées -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-info-400 to-info-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-info-400 to-info-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-list-ol text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['sequential_created'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Séquentielles Créées</p>
                        </div>
                    </div>
                </div>

            @elseif(auth()->user()->isSignataire())
                <!-- À Signer (Simples) -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-pen-fancy text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['pending_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">À Signer (Simples)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Séquentielles -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-info-400 to-info-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-info-400 to-info-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-list-ol text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['sequential_pending'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Séquentielles</p>
                        </div>
                    </div>
                </div>
                
                <!-- Signés (Simples) -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['signed_documents'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Signés (Simples)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Signés (Séquentielles) -->
                <div class="group relative card card-hover p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-success-400 to-success-600 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity duration-300"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-check-double text-white text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['sequential_signed'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 font-medium">Signés (Séquentielles)</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions rapides - Tailwind CSS uniquement - Responsive -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-soft sm:shadow-lg p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Actions Rapides</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                @if(auth()->user()->isAgent())
                    <a href="{{ route('documents.upload') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                        <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Soumettre</h4>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Nouveau document</p>
                        </div>
                        <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('documents.history') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                        <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Mes Documents</h4>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Historique complet</p>
                        </div>
                        <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                        </div>
                    </a>
                @endif

                @if(auth()->user()->isSignataire())
                    <a href="{{ route('signatures.index') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                        <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Signatures<span class="hidden sm:inline"> Simples</span></h4>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Documents en attente</p>
                        </div>
                        <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('signatures.simple.index') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                        <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Séquentielles</h4>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Workflow ordonné</p>
                        </div>
                        <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                        </div>
                    </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                        <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Admin</h4>
                            <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Administration</p>
                        </div>
                        <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                            <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                        </div>
                    </a>
                @endif

                <a href="{{ route('documents.pending') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                    <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">En Attente</h4>
                        <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Documents en cours</p>
                    </div>
                    <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                    </div>
                </a>
                
                <a href="{{ route('documents.history') }}" class="group flex items-center gap-3 sm:gap-4 lg:gap-5 p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-white to-blue-50 border-2 sm:border-3 border-transparent rounded-lg sm:rounded-xl shadow-md hover:shadow-xl sm:hover:shadow-2xl hover:-translate-y-1 sm:hover:-translate-y-2 hover:scale-102 sm:hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 sm:w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-2 sm:group-hover:w-4 transition-all duration-300"></div>
                    <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg sm:text-xl lg:text-2xl shadow-md sm:shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-0.5 sm:mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Historique</h4>
                        <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300 truncate">Tous les documents</p>
                    </div>
                    <div class="hidden sm:flex flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gray-100 items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-1 lg:group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-chevron-right text-sm lg:text-base"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
