@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
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
                <i class="fas fa-user"></i>
                <span class="hidden sm:inline ml-1">Mon Profil</span>
            </li>
        </ol>
    </nav>
    
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            👤 Mon Profil
        </h1>
        <p class="text-gray-600">
            Gérez vos informations personnelles et paramètres de sécurité
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informations du profil -->
        <div class="lg:col-span-2">
            <!-- Informations personnelles -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Informations personnelles</h2>
                        <p class="text-gray-600">Modifiez vos informations de base</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1"></i>
                                Nom complet
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-1"></i>
                                Adresse email
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors font-medium">
                            <i class="fas fa-save mr-2"></i>
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            <!-- Mot de passe -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-lock text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Sécurité</h2>
                        <p class="text-gray-600">Modifiez votre mot de passe</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-key mr-1"></i>
                            Mot de passe actuel
                        </label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1"></i>
                                Nouveau mot de passe
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1"></i>
                                Confirmer le mot de passe
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium">
                            <i class="fas fa-key mr-2"></i>
                            Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Informations du compte -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informations du compte
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nom</span>
                            <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-envelope text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</span>
                            <p class="text-sm font-semibold text-gray-900">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-tag text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rôle</span>
                            <p class="text-sm font-semibold text-gray-900">
                                @if($user->isAdmin())
                                    <i class="fas fa-crown mr-1"></i>Administrateur
                                @elseif($user->isSignataire())
                                    <i class="fas fa-pen-fancy mr-1"></i>Signataire
                                @else
                                    <i class="fas fa-user mr-1"></i>Agent
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Membre depuis</span>
                            <p class="text-sm font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone de danger -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-red-200">
                <h3 class="text-lg font-bold text-red-800 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Zone de danger
                </h3>
                
                <p class="text-sm text-gray-600 mb-4">
                    Supprimer votre compte est une action irréversible. Toutes vos données seront perdues.
                </p>

                <button onclick="openDeleteModal()" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium">
                    <i class="fas fa-trash mr-2"></i>
                    Supprimer le compte
                </button>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Modal de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Supprimer le compte</h3>
                        <p class="text-sm text-gray-600">Cette action est irréversible</p>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">
                    Êtes-vous sûr de vouloir supprimer votre compte ? Toutes vos données seront définitivement perdues.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    
                    <div class="mb-4">
                        <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmez votre mot de passe
                        </label>
                        <input type="password" 
                               id="delete_password" 
                               name="password" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeDeleteModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                            Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection
