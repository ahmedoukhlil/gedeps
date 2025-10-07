@extends('layouts.app')

@section('title', 'Tableau de Bord Admin')

@section('content')
<div class="container mx-auto p-4">
    <!-- Navigation sophistiquée -->
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
                <i class="fas fa-cog"></i>
                <span class="hidden sm:inline ml-1">Administration</span>
            </li>
        </ol>
    </nav>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold sophisticated-heading mb-8">Tableau de Bord Administrateur</h1>
        
        @if(session('success'))
            <div class="mb-4 sophisticated-alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 sophisticated-alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="sophisticated-card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium sophisticated-caption truncate">Total Utilisateurs</dt>
                                <dd class="text-lg font-medium sophisticated-heading">{{ $stats['total_users'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sophisticated-card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium sophisticated-caption truncate">Agents</dt>
                                <dd class="text-lg font-medium sophisticated-heading">{{ $stats['total_agents'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sophisticated-card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium sophisticated-caption truncate">Signataires</dt>
                                <dd class="text-lg font-medium sophisticated-heading">{{ $stats['total_signataires'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sophisticated-card">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium sophisticated-caption truncate">Documents</dt>
                                <dd class="text-lg font-medium sophisticated-heading">{{ $stats['total_documents'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="sophisticated-card-header-title mb-4">Actions rapides</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.users') }}" 
                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <svg class="h-8 w-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium sophisticated-heading">Gérer les utilisateurs</h3>
                        <p class="text-sm sophisticated-caption">Créer et modifier les comptes</p>
                    </div>
                </a>

                <a href="{{ route('documents.history') }}" 
                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <svg class="h-8 w-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium sophisticated-heading">Voir les documents</h3>
                        <p class="text-sm sophisticated-caption">Consulter l'historique</p>
                    </div>
                </a>

                <a href="{{ route('documents.pending') }}" 
                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <svg class="h-8 w-8 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium sophisticated-heading">Documents en attente</h3>
                        <p class="text-sm sophisticated-caption">Suivre les approbations</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
