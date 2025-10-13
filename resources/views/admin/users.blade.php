@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')

@push('styles')
<style>
/* Le menu déroulant utilise position fixed pour déborder sans affecter le tableau */
[id^="dropdown-"] {
    position: fixed !important;
    z-index: 9999 !important;
}

/* Le conteneur du bouton Actions ne doit pas élargir la cellule */
.relative.inline-block {
    display: inline-flex;
    vertical-align: middle;
}

/* Styles personnalisés pour le menu déroulant responsive */
@media (max-width: 639px) {
    /* Sur mobile, le menu prend plus d'espace */
    [id^="dropdown-"] {
        max-width: calc(100vw - 2rem) !important;
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%) !important;
    }
    
    /* Bouton Actions sur mobile */
    .relative.inline-block button {
        min-width: 44px; /* Taille minimum tactile */
    }
}

/* Animation du menu déroulant */
[id^="dropdown-"]:not(.hidden) {
    animation: dropdownFadeIn 0.15s ease-out;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Amélioration du hover sur les items du menu */
[id^="dropdown-"] button:hover {
    transform: translateX(2px);
    transition: all 0.15s ease-in-out;
}

/* Scrollbar personnalisée pour le menu */
[id^="dropdown-"] {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

[id^="dropdown-"]::-webkit-scrollbar {
    width: 6px;
}

[id^="dropdown-"]::-webkit-scrollbar-track {
    background: #f7fafc;
}

[id^="dropdown-"]::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

[id^="dropdown-"]::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Badge responsive */
@media (max-width: 640px) {
    .inline-flex.items-center.px-2.py-0\.5 {
        font-size: 0.65rem;
        padding: 0.125rem 0.375rem;
    }
}
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
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
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-all duration-200">
                        <i class="fas fa-cog text-xs"></i>
                        <span class="hidden sm:inline font-medium">Administration</span>
                    </a>
                </li>
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </li>
                <li>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-100 text-primary-700 rounded-lg font-semibold">
                        <i class="fas fa-users text-xs"></i>
                        <span class="hidden sm:inline">Utilisateurs</span>
                    </span>
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
                                <i class="fas fa-users-cog text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 bg-success-500 border-3 border-white rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                        
                        <!-- Titre et Description -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3">
                                <i class="fas fa-sparkles text-primary-500 text-xl sm:text-2xl lg:text-3xl"></i>
                                <span class="truncate">Gestion des <span class="text-gradient">Utilisateurs</span></span>
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600 hidden sm:block">Créez, modifiez et gérez les comptes utilisateurs et leurs rôles</p>
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

        <!-- Formulaire de création d'utilisateur - Ultra Élégant -->
        <div class="card overflow-hidden mb-6 sm:mb-8 relative group">
            <!-- Fond décoratif animé -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-success-400 to-success-500 rounded-full blur-3xl opacity-10 -mr-32 -mt-32 group-hover:opacity-20 transition-opacity duration-500"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full blur-3xl opacity-10 -ml-24 -mb-24 group-hover:opacity-20 transition-opacity duration-500"></div>
            
            <!-- En-tête avec style avancé -->
            <div class="relative p-6 sm:p-8 border-b-2 border-gray-100 bg-gradient-to-r from-white via-success-50/30 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <!-- Icône principale avec animation -->
                        <div class="relative">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-elegant group-hover:shadow-glow group-hover:scale-105 transition-all duration-300">
                                <i class="fas fa-user-plus text-white text-2xl sm:text-3xl"></i>
                            </div>
                            <!-- Badge décoratif -->
                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-warning-500 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                                <i class="fas fa-star text-white text-xs"></i>
                            </div>
                </div>

                        <!-- Textes -->
                <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2 mb-1">
                                <i class="fas fa-sparkles text-success-500 text-lg"></i>
                                <span>Créer un nouvel utilisateur</span>
                            </h2>
                            <p class="text-sm text-gray-600 flex items-center gap-2">
                                <i class="fas fa-info-circle text-gray-400 text-xs"></i>
                                Ajoutez un nouveau membre à votre équipe en quelques clics
                            </p>
                        </div>
                </div>

                    <!-- Badge d'aide -->
                    <div class="hidden lg:flex items-center gap-2 px-4 py-2 bg-primary-50 border border-primary-200 rounded-xl">
                        <i class="fas fa-lightbulb text-primary-600"></i>
                        <span class="text-xs font-medium text-primary-700">Remplissez tous les champs</span>
                    </div>
                </div>
                </div>

            <!-- Formulaire avec design amélioré -->
            <div class="relative p-6 sm:p-8 lg:p-10">
                <form action="{{ route('admin.users.create') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Section Informations personnelles -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                                <i class="fas fa-id-card text-primary-600 text-sm"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Informations personnelles</h3>
                            <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Nom complet -->
                            <div class="group/input">
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-primary-100 flex items-center justify-center group-hover/input:bg-primary-200 transition-colors">
                                            <i class="fas fa-user text-primary-600 text-xs"></i>
                                        </div>
                                        <span>Nom complet</span>
                                        <span class="text-danger-500 text-xs">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           required
                                           class="input-elegant pl-10"
                                           placeholder="Ex: Jean Dupont">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="group/input">
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center group-hover/input:bg-blue-200 transition-colors">
                                            <i class="fas fa-envelope text-blue-600 text-xs"></i>
                                        </div>
                                        <span>Adresse email</span>
                                        <span class="text-danger-500 text-xs">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           required
                                           class="input-elegant pl-10"
                                           placeholder="Ex: jean.dupont@example.com">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <i class="fas fa-envelope text-sm"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Sécurité -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-warning-100 flex items-center justify-center">
                                <i class="fas fa-shield-alt text-warning-600 text-sm"></i>
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Sécurité et permissions</h3>
                            <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Mot de passe -->
                            <div class="group/input">
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-warning-100 flex items-center justify-center group-hover/input:bg-warning-200 transition-colors">
                                            <i class="fas fa-lock text-warning-600 text-xs"></i>
                                        </div>
                                        <span>Mot de passe</span>
                                        <span class="text-danger-500 text-xs">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           required
                                           class="input-elegant pl-10 pr-10"
                                           placeholder="Minimum 8 caractères">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                        <i class="fas fa-lock text-sm"></i>
                                    </div>
                                    <button type="button" 
                                            onclick="togglePasswordVisibility()"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                        <i class="fas fa-eye text-sm" id="password-eye"></i>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 flex items-center gap-1">
                                    <i class="fas fa-info-circle"></i>
                                    Utilisez au moins 8 caractères avec lettres et chiffres
                                </p>
                            </div>

                            <!-- Rôle -->
                            <div class="group/input">
                                <label for="role_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-success-100 flex items-center justify-center group-hover/input:bg-success-200 transition-colors">
                                            <i class="fas fa-user-tag text-success-600 text-xs"></i>
                                        </div>
                                        <span>Rôle utilisateur</span>
                                        <span class="text-danger-500 text-xs">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <select name="role_id" 
                                            id="role_id" 
                                            required 
                                            class="input-elegant pl-10 appearance-none">
                        <option value="">Sélectionner un rôle</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <i class="fas fa-user-tag text-sm"></i>
                                    </div>
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <i class="fas fa-chevron-down text-sm"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 flex items-center gap-1">
                                    <i class="fas fa-info-circle"></i>
                                    Définit les permissions d'accès de l'utilisateur
                                </p>
                            </div>
                        </div>
                </div>

                    <!-- Boutons d'action -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t-2 border-gray-100">
                    <button type="submit" 
                                class="group inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-success-500 to-success-600 text-white rounded-xl font-semibold shadow-elegant hover:shadow-glow hover:-translate-y-0.5 hover:scale-105 transition-all duration-300">
                            <i class="fas fa-plus-circle group-hover:rotate-90 transition-transform duration-300"></i>
                            <span>Créer l'utilisateur</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-300"></i>
                    </button>
                        
                        <button type="reset" 
                                class="group inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 hover:border-gray-400 hover:-translate-y-0.5 transition-all duration-300">
                            <i class="fas fa-redo group-hover:rotate-180 transition-transform duration-300"></i>
                            <span>Réinitialiser</span>
                        </button>
                        
                        <div class="flex-1"></div>
                        
                        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
                            <i class="fas fa-asterisk text-danger-500 text-xs"></i>
                            <span>Champs obligatoires</span>
                        </div>
                </div>
            </form>
            </div>
        </div>

        <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('password-eye');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        </script>

        <!-- Liste des utilisateurs - Tableau Élégant -->
        <div class="card card-hover overflow-hidden">
            <!-- En-tête du tableau avec design moderne -->
            <div class="relative p-4 sm:p-6 lg:p-8 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Titre avec badge -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-elegant">
                            <i class="fas fa-users text-white text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2">
                                <span>Liste des utilisateurs</span>
                                <span class="inline-flex items-center px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold">
                                    {{ $users->count() }}
                                </span>
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Gérez tous les comptes utilisateurs de la plateforme</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Utilisateur</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-tag text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Rôle</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-primary-500 text-sm"></i>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Date de création</span>
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
                        @foreach($users as $user)
                            <tr class="group hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent transition-all duration-200">
                                <!-- Utilisateur -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-shrink-0">
                                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-200">
                                                <span class="text-white text-base font-bold">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            @if($user->isAdmin())
                                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-warning-500 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-crown text-white text-xs"></i>
                                        </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                                                {{ $user->name }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <i class="fas fa-envelope text-gray-400 text-xs"></i>
                                                <span class="text-sm text-gray-600 truncate">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Rôle -->
                                <td class="px-6 py-4">
                                    @if($user->role)
                                        @php
                                            $roleConfig = [
                                                'admin' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-700', 'border' => 'border-danger-200', 'icon' => 'fa-crown'],
                                                'agent' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'fa-user-tie'],
                                                'signataire' => ['bg' => 'bg-success-100', 'text' => 'text-success-700', 'border' => 'border-success-200', 'icon' => 'fa-pen-fancy'],
                                            ];
                                            $config = $roleConfig[$user->role->name] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => 'fa-user'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $config['bg'] }} {{ $config['text'] }} rounded-lg text-sm font-semibold border {{ $config['border'] }}">
                                            <i class="fas {{ $config['icon'] }} text-xs"></i>
                                            {{ $user->role->display_name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold border border-gray-200">
                                            <i class="fas fa-user-slash text-xs"></i>
                                            Aucun rôle
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Date -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                                <i class="fas fa-clock text-[10px]"></i>
                                                {{ $user->created_at->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <!-- Badges de statut -->
                                    @if($user->isSignataire())
                                            <div class="flex items-center space-x-1">
                                                <!-- Badge Signature -->
                                        @if($user->hasSignature())
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" 
                                                          title="Signature configurée">
                                                        <i class="fas fa-pen-fancy mr-1"></i>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500" 
                                                          title="Pas de signature">
                                                        <i class="fas fa-pen-fancy mr-1"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Badge Paraphe -->
                                                @if($user->hasParaphe())
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" 
                                                          title="Paraphe configuré">
                                                        <i class="fas fa-pen-nib mr-1"></i>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500" 
                                                          title="Pas de paraphe">
                                                        <i class="fas fa-pen-nib mr-1"></i>
                                                    </span>
                                                @endif
                                                
                                                <!-- Badge Cachet -->
                                                @if($user->hasCachet())
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" 
                                                          title="Cachet configuré">
                                                        <i class="fas fa-stamp mr-1"></i>
                                                    </span>
                                        @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500" 
                                                          title="Pas de cachet">
                                                        <i class="fas fa-stamp mr-1"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <!-- Menu déroulant Actions -->
                                        <div class="relative inline-block text-left">
                                            <button onclick="toggleDropdown({{ $user->id }})" 
                                                    type="button"
                                                    class="inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                                <i class="fas fa-cog mr-1 text-indigo-600"></i>
                                                <span class="hidden sm:inline">Actions</span>
                                                <i class="fas fa-chevron-down ml-1 sm:ml-2 text-xs"></i>
                                            </button>
                                            
                                            <!-- Dropdown menu - Responsive -->
                                            <div id="dropdown-{{ $user->id }}" 
                                                 class="hidden w-64 sm:w-72 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 max-h-96 overflow-y-auto">
                                                <!-- Section Générale -->
                                                <div class="py-1">
                                                    <div class="px-4 py-2 bg-gray-50">
                                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Général</p>
                                                    </div>
                                                    <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', {{ $user->role_id ?? 'null' }}); toggleDropdown({{ $user->id }});"
                                                            type="button"
                                                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center transition-colors duration-150">
                                                        <i class="fas fa-edit w-5 text-indigo-600"></i>
                                                        <span class="ml-3 flex-1">Modifier l'utilisateur</span>
                                                    </button>
                                                </div>
                                    
                                    @if($user->isSignataire())
                                                    <!-- Section Signature -->
                                                    <div class="py-1">
                                                        <div class="px-4 py-2 bg-gray-50 flex items-center justify-between">
                                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Signature</p>
                                        @if($user->hasSignature())
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-check mr-1"></i>Configurée
                                                                </span>
                                        @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                                    Non configurée
                                                                </span>
                                        @endif
                                                        </div>
                                        @if($user->hasSignature())
                                                            <button onclick="viewSignature({{ $user->id }}, '{{ $user->name }}', '{{ $user->getSignatureUrl() }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-eye w-5 text-green-600"></i>
                                                                <span class="ml-3 flex-1">Voir la signature</span>
                                                            </button>
                                                            <button onclick="deleteSignature({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-trash w-5 text-red-600"></i>
                                                                <span class="ml-3 flex-1">Supprimer la signature</span>
                                                            </button>
                                        @else
                                                            <button onclick="uploadSignature({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-upload w-5 text-blue-600"></i>
                                                                <span class="ml-3 flex-1">Ajouter une signature</span>
                                                            </button>
                                        @endif
                                                    </div>
                                                    
                                                    <!-- Section Paraphe -->
                                                    <div class="py-1">
                                                        <div class="px-4 py-2 bg-gray-50 flex items-center justify-between">
                                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Paraphe</p>
                                        @if($user->hasParaphe())
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-check mr-1"></i>Configuré
                                                                </span>
                                        @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                                    Non configuré
                                                                </span>
                                        @endif
                                                        </div>
                                        @if($user->hasParaphe())
                                                            <button onclick="viewParaphe({{ $user->id }}, '{{ $user->name }}', '{{ $user->getParapheUrl() }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-eye w-5 text-green-600"></i>
                                                                <span class="ml-3 flex-1">Voir le paraphe</span>
                                                            </button>
                                                            <button onclick="deleteParaphe({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-trash w-5 text-red-600"></i>
                                                                <span class="ml-3 flex-1">Supprimer le paraphe</span>
                                                            </button>
                                                        @else
                                                            <button onclick="uploadParaphe({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-upload w-5 text-blue-600"></i>
                                                                <span class="ml-3 flex-1">Ajouter un paraphe</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Section Cachet -->
                                                    <div class="py-1">
                                                        <div class="px-4 py-2 bg-gray-50 flex items-center justify-between">
                                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cachet</p>
                                                            @if($user->hasCachet())
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-check mr-1"></i>Configuré
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                                    Non configuré
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($user->hasCachet())
                                                            <button onclick="viewCachet({{ $user->id }}, '{{ $user->name }}', '{{ $user->getCachetUrl() }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-eye w-5 text-green-600"></i>
                                                                <span class="ml-3 flex-1">Voir le cachet</span>
                                                            </button>
                                                            <button onclick="deleteCachet({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-trash w-5 text-red-600"></i>
                                                                <span class="ml-3 flex-1">Supprimer le cachet</span>
                                                            </button>
                                        @else
                                                            <button onclick="uploadCachet({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                    type="button"
                                                                    class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center transition-colors duration-150">
                                                                <i class="fas fa-upload w-5 text-blue-600"></i>
                                                                <span class="ml-3 flex-1">Ajouter un cachet</span>
                                                            </button>
                                        @endif
                                                    </div>
                                    @endif
                                    
                                    @if(!$user->isAdmin() || $user->id !== auth()->id())
                                                    <!-- Section Danger -->
                                                    <div class="py-1 bg-red-50">
                                                        <div class="px-4 py-2">
                                                            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Zone de danger</p>
                                                        </div>
                                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}'); toggleDropdown({{ $user->id }});"
                                                                type="button"
                                                                class="w-full text-left px-4 py-3 text-sm text-red-700 hover:bg-red-100 font-medium flex items-center transition-colors duration-150">
                                                            <i class="fas fa-user-times w-5 text-red-600"></i>
                                                            <span class="ml-3 flex-1">Supprimer l'utilisateur</span>
                                                        </button>
                                                    </div>
                                    @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de modification -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Modifier l'utilisateur</h3>
            </div>
            <form id="editForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="edit_email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="edit_role_id" class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                        <select name="role_id" id="edit_role_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Sélectionner un rôle</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'upload de signature -->
<div id="signatureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ajouter une signature</h3>
            </div>
            <form id="signatureForm" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="signature_file" class="block text-sm font-medium text-gray-700 mb-2">Fichier de signature (PNG uniquement)</label>
                        <input type="file" name="signature" id="signature_file" accept=".png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Format PNG uniquement, taille max: 2MB</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeSignatureModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de visualisation de signature -->
<div id="viewSignatureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Signature de <span id="signatureUserName"></span></h3>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <img id="signatureImage" src="" alt="Signature" class="max-w-full h-auto border border-gray-200 rounded">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeViewSignatureModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'upload de paraphe -->
<div id="parapheModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ajouter un paraphe</h3>
            </div>
            <form id="parapheForm" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="paraphe_file" class="block text-sm font-medium text-gray-700 mb-2">Fichier de paraphe (PNG uniquement)</label>
                        <input type="file" name="paraphe" id="paraphe_file" accept=".png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Format PNG uniquement, taille max: 2MB</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeParapheModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de visualisation de paraphe -->
<div id="viewParapheModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Paraphe de <span id="parapheUserName"></span></h3>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <img id="parapheImage" src="" alt="Paraphe" class="max-w-full h-auto border border-gray-200 rounded">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeViewParapheModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'upload de cachet -->
<div id="cachetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ajouter un cachet</h3>
            </div>
            <form id="cachetForm" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="cachet_file" class="block text-sm font-medium text-gray-700 mb-2">Fichier de cachet (PNG uniquement)</label>
                        <input type="file" name="cachet" id="cachet_file" accept=".png" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Format PNG uniquement, taille max: 2MB</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeCachetModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de visualisation de cachet -->
<div id="viewCachetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Cachet de <span id="cachetUserName"></span></h3>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <img id="cachetImage" src="" alt="Cachet" class="max-w-full h-auto border border-gray-200 rounded">
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeViewCachetModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(id, name, email, roleId) {
    document.getElementById('editForm').action = `/admin/users/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role_id').value = roleId || '';
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function deleteUser(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function uploadSignature(id, name) {
    document.getElementById('signatureForm').action = `/admin/users/${id}/signature`;
    document.getElementById('signatureModal').classList.remove('hidden');
}

function closeSignatureModal() {
    document.getElementById('signatureModal').classList.add('hidden');
    document.getElementById('signatureForm').reset();
}

function viewSignature(id, name, signatureUrl) {
    document.getElementById('signatureUserName').textContent = name;
    document.getElementById('signatureImage').src = signatureUrl;
    document.getElementById('viewSignatureModal').classList.remove('hidden');
}

function closeViewSignatureModal() {
    document.getElementById('viewSignatureModal').classList.add('hidden');
}

function deleteSignature(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la signature de "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}/signature`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function uploadParaphe(id, name) {
    document.getElementById('parapheForm').action = `/admin/users/${id}/paraphe`;
    document.getElementById('parapheModal').classList.remove('hidden');
}

function closeParapheModal() {
    document.getElementById('parapheModal').classList.add('hidden');
    document.getElementById('parapheForm').reset();
}

function viewParaphe(id, name, parapheUrl) {
    document.getElementById('parapheUserName').textContent = name;
    document.getElementById('parapheImage').src = parapheUrl;
    document.getElementById('viewParapheModal').classList.remove('hidden');
}

function closeViewParapheModal() {
    document.getElementById('viewParapheModal').classList.add('hidden');
}

function deleteParaphe(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le paraphe de "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}/paraphe`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function uploadCachet(id, name) {
    document.getElementById('cachetForm').action = `/admin/users/${id}/cachet`;
    document.getElementById('cachetModal').classList.remove('hidden');
}

function closeCachetModal() {
    document.getElementById('cachetModal').classList.add('hidden');
    document.getElementById('cachetForm').reset();
}

function viewCachet(id, name, cachetUrl) {
    document.getElementById('cachetUserName').textContent = name;
    document.getElementById('cachetImage').src = cachetUrl;
    document.getElementById('viewCachetModal').classList.remove('hidden');
}

function closeViewCachetModal() {
    document.getElementById('viewCachetModal').classList.add('hidden');
}

function deleteCachet(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le cachet de "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${id}/cachet`;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Gestion du dropdown
// ===============================================
// GESTION DES MENUS DÉROULANTS RESPONSIVE
// ===============================================
let currentOpenDropdown = null;
let currentOpenButton = null;

function toggleDropdown(userId, event) {
    const dropdown = document.getElementById(`dropdown-${userId}`);
    const button = event ? event.target.closest('button') : document.querySelector(`button[onclick*="toggleDropdown(${userId})"]`);
    
    // Si on clique sur le menu déjà ouvert, on le ferme
    if (currentOpenDropdown === dropdown && !dropdown.classList.contains('hidden')) {
        closeAllDropdowns();
        return;
    }
    
    // Fermer tous les menus ouverts
    closeAllDropdowns();
    
    // Ouvrir le nouveau menu avec animation
    dropdown.classList.remove('hidden');
    
    // Ajuster la position sur mobile si nécessaire
    adjustDropdownPosition(dropdown);
    
    currentOpenDropdown = dropdown;
    currentOpenButton = button;
    
    // Ajouter une classe active au bouton
    if (button) {
        button.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-1');
    }
}

function closeAllDropdowns() {
    if (currentOpenDropdown) {
        currentOpenDropdown.classList.add('hidden');
        currentOpenDropdown = null;
    }
    if (currentOpenButton) {
        currentOpenButton.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-1');
        currentOpenButton = null;
    }
}

function adjustDropdownPosition(dropdown) {
    // Réinitialiser les styles
    dropdown.style.right = '';
    dropdown.style.left = '';
    dropdown.style.top = '';
    dropdown.style.transform = '';
    dropdown.style.width = '';
    
    // Calculer la position par rapport au bouton
    setTimeout(() => {
        const button = currentOpenButton;
        if (!button) return;
        
        const buttonRect = button.getBoundingClientRect();
        const dropdownRect = dropdown.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        if (viewportWidth < 640) { // sm breakpoint - Mobile
            // Sur mobile, centrer horizontalement
            dropdown.style.left = '50%';
            dropdown.style.transform = 'translateX(-50%)';
            dropdown.style.top = `${buttonRect.bottom + 8}px`;
            dropdown.style.width = `${Math.min(256, viewportWidth - 32)}px`;
        } else { // Desktop
            // Position de base : sous le bouton, aligné à droite
            dropdown.style.top = `${buttonRect.bottom + 8}px`;
            
            // Calculer si le menu dépasse à droite
            const dropdownWidth = 288; // w-72 = 18rem = 288px
            const rightPosition = buttonRect.right;
            
            if (rightPosition < dropdownWidth) {
                // Pas assez d'espace à droite, aligner à gauche du bouton
                dropdown.style.left = `${buttonRect.left}px`;
                dropdown.style.right = 'auto';
            } else if (viewportWidth - rightPosition < 16) {
                // Très proche du bord droit, aligner à droite de la fenêtre
                dropdown.style.right = '16px';
                dropdown.style.left = 'auto';
            } else {
                // Assez d'espace, aligner à droite du bouton
                dropdown.style.left = `${rightPosition - dropdownWidth}px`;
                dropdown.style.right = 'auto';
            }
            
            // Vérifier si le menu dépasse en bas
            const menuHeight = dropdownRect.height || 400; // hauteur estimée
            if (buttonRect.bottom + menuHeight + 16 > viewportHeight) {
                // Ouvrir vers le haut
                dropdown.style.top = 'auto';
                dropdown.style.bottom = `${viewportHeight - buttonRect.top + 8}px`;
            }
        }
    }, 10);
}

// Fermer le menu quand on clique en dehors
document.addEventListener('click', function(event) {
    if (currentOpenDropdown && !event.target.closest('.relative.inline-block')) {
        closeAllDropdowns();
    }
});

// Fermer le menu avec la touche Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && currentOpenDropdown) {
        closeAllDropdowns();
        // Remettre le focus sur le bouton
        if (currentOpenButton) {
            currentOpenButton.focus();
        }
    }
});

// Ajuster la position lors du redimensionnement (sans fermer le menu)
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        if (currentOpenDropdown) {
            adjustDropdownPosition(currentOpenDropdown);
        }
    }, 150);
});

// Fermer le dropdown après sélection d'une action
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButtons = document.querySelectorAll('[id^="dropdown-"] button');
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function() {
            const dropdown = this.closest('[id^="dropdown-"]');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        });
    });
});
</script>
@endsection
