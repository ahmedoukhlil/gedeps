@extends('layouts.app')

@section('title', 'Tableau de Bord Admin')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <!-- Breadcrumb Élégant -->
    <nav class="mb-6 sm:mb-8">
        <ol class="flex items-center gap-2 text-sm">
            <li>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-all duration-200">
                    <i class="fas fa-home text-xs"></i>
                    <span class="hidden sm:inline font-medium">Accueil</span>
                </a>
            </li>
            <li class="text-gray-400">
                <i class="fas fa-chevron-right text-xs"></i>
            </li>
            <li>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-100 text-primary-700 rounded-lg font-semibold">
                    <i class="fas fa-cog text-xs"></i>
                    <span class="hidden sm:inline">Administration</span>
                </span>
            </li>
        </ol>
    </nav>

    <div class="max-w-7xl mx-auto">
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
                                <i class="fas fa-crown text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 bg-warning-500 border-3 border-white rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-star text-white text-xs"></i>
                            </div>
                        </div>
                        
                        <!-- Titre et Description -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                                <i class="fas fa-sparkles text-primary-500 text-xl sm:text-2xl lg:text-3xl"></i>
                                <span class="truncate">Tableau de Bord <span class="text-gradient">Administrateur</span></span>
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600 hidden sm:block">Gérez les utilisateurs, les documents et les paramètres système</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if(session('success'))
            <div class="mb-4 sm:mb-6 alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 sm:mb-6 alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Statistiques Élégantes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Total Utilisateurs -->
            <div class="card card-hover group overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity"></div>
                <div class="relative p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold">
                            Total
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Utilisateurs</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Agents -->
            <div class="card card-hover group overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity"></div>
                <div class="relative p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-tie text-white text-xl"></i>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold">
                            Rôle
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Agents</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_agents'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Signataires -->
            <div class="card card-hover group overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-success-400 to-success-500 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity"></div>
                <div class="relative p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-pen-fancy text-white text-xl"></i>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 bg-success-100 text-success-700 rounded-lg text-xs font-semibold">
                            Rôle
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Signataires</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_signataires'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="card card-hover group overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-400 to-purple-500 rounded-full blur-3xl opacity-20 -mr-16 -mt-16 group-hover:opacity-30 transition-opacity"></div>
                <div class="relative p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-file-alt text-white text-xl"></i>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold">
                            Total
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Documents</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_documents'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Rapides - Tailwind CSS uniquement -->
        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="fas fa-bolt text-primary-500"></i>
                <span>Actions Rapides</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Gérer les utilisateurs -->
                <a href="{{ route('admin.users') }}" class="group flex items-center gap-4 sm:gap-5 p-5 sm:p-6 bg-gradient-to-br from-white to-blue-50 border-3 border-transparent rounded-xl shadow-md hover:shadow-2xl hover:-translate-y-2 hover:scale-105 hover:border-primary transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-primary-400 via-primary to-primary-600 group-hover:w-4 transition-all duration-300"></div>
                    <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xl sm:text-2xl shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-1 group-hover:text-primary-600 transition-colors duration-300 truncate">Gérer utilisateurs</h4>
                        <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300">Créer et modifier les comptes</p>
                    </div>
                    <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </div>
                </a>

                <!-- Voir les documents -->
                <a href="{{ route('documents.history') }}" class="group flex items-center gap-4 sm:gap-5 p-5 sm:p-6 bg-gradient-to-br from-white to-success-50 border-3 border-transparent rounded-xl shadow-md hover:shadow-2xl hover:-translate-y-2 hover:scale-105 hover:border-success transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-success-400 via-success to-success-600 group-hover:w-4 transition-all duration-300"></div>
                    <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center text-white text-xl sm:text-2xl shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-1 group-hover:text-success-600 transition-colors duration-300 truncate">Voir documents</h4>
                        <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300">Consulter l'historique</p>
                    </div>
                    <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-success group-hover:text-white group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </div>
                </a>

                <!-- Documents en attente -->
                <a href="{{ route('documents.pending') }}" class="group flex items-center gap-4 sm:gap-5 p-5 sm:p-6 bg-gradient-to-br from-white to-warning-50 border-3 border-transparent rounded-xl shadow-md hover:shadow-2xl hover:-translate-y-2 hover:scale-105 hover:border-warning transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-warning-400 via-warning to-warning-600 group-hover:w-4 transition-all duration-300"></div>
                    <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-warning-400 to-warning-600 flex items-center justify-center text-white text-xl sm:text-2xl shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-300">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-base sm:text-lg font-bold text-gray-900 mb-1 group-hover:text-warning-600 transition-colors duration-300 truncate">En attente</h4>
                        <p class="text-xs sm:text-sm text-gray-600 font-medium group-hover:text-gray-700 transition-colors duration-300">Suivre les approbations</p>
                    </div>
                    <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-warning group-hover:text-white group-hover:translate-x-2 group-hover:scale-110 transition-all duration-300">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
