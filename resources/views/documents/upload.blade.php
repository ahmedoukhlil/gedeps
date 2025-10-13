@extends('layouts.app')

@section('title', 'Soumettre un Document')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
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
                <i class="fas fa-upload text-sm"></i>
                <span class="hidden sm:inline">Nouveau Document</span>
            </li>
        </ol>
    </nav>
    
    <!-- Carte d'En-tête Élégante -->
    <div class="card card-hover mb-6 sm:mb-8 overflow-hidden relative">
        <!-- Fond décoratif avec dégradé -->
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-success-500 via-success-600 to-success-700 opacity-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-success-400 rounded-full blur-3xl opacity-20 -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-success-300 rounded-full blur-3xl opacity-20 -ml-24 -mb-24"></div>
        
        <div class="relative p-6 sm:p-8 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <!-- Informations de la page -->
                <div class="flex items-center gap-4 sm:gap-6 flex-1">
                    <!-- Icône Élégante -->
                    <div class="relative flex-shrink-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-2xl bg-gradient-to-br from-success-400 to-success-600 flex items-center justify-center shadow-glow">
                            <i class="fas fa-cloud-upload-alt text-white text-2xl sm:text-3xl lg:text-4xl"></i>
                        </div>
                    </div>
                    
                    <!-- Titre et Description -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 flex items-center gap-2 sm:gap-3 flex-wrap">
                            <i class="fas fa-sparkles text-success-500 text-xl sm:text-2xl lg:text-3xl"></i>
                            <span>Soumettre un <span class="text-gradient">Document</span></span>
                        </h1>
                        <p class="text-sm sm:text-base text-gray-600">Envoyez vos documents pour signature électronique</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 sm:gap-3 flex-shrink-0">
                    <a href="{{ route('documents.pending') }}" class="group inline-flex items-center gap-2 px-4 sm:px-6 py-3 sm:py-4 bg-white border-2 border-gray-300 text-gray-700 rounded-xl shadow-elegant hover:shadow-lg hover:-translate-y-1 hover:border-gray-400 transition-all duration-300">
                        <i class="fas fa-clock text-sm sm:text-base"></i>
                        <span class="text-xs sm:text-sm font-semibold">En Attente</span>
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

    <!-- Formulaire -->
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                
                <!-- Section Informations du document -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-edit text-white text-lg font-bold"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Informations du document</h2>
                    <p class="text-gray-600">Décrivez votre document</p>
                </div>
            </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Nom du document -->
                        <div class="lg:col-span-2">
                    <label for="document_name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-signature mr-1"></i>
                                Nom du document *
                            </label>
                            <input type="text" 
                                   name="document_name" 
                                   id="document_name" 
                                   value="{{ old('document_name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('document_name') border-red-500 @enderror" 
                           placeholder="Ex: Contrat de service - Client ABC"
                                   required>
                            @error('document_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type de document -->
                        <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-1"></i>
                                Type de document *
                        </label>
                            <div class="relative">
                    <select name="type" id="type" class="w-full px-4 py-2.5 sm:py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('type') border-red-500 @enderror appearance-none bg-white text-sm sm:text-base" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="contrat" {{ old('type') == 'contrat' ? 'selected' : '' }}>Contrat</option>
                                    <option value="facture" {{ old('type') == 'facture' ? 'selected' : '' }}>Facture</option>
                                    <option value="rapport" {{ old('type') == 'rapport' ? 'selected' : '' }}>Rapport</option>
                                    <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                                </div>
                            </div>
                                @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-1"></i>
                        Description
                            </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                              placeholder="Description optionnelle du document">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section Type de signature -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-signature text-white text-lg font-bold"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Type de signature</h2>
                    <p class="text-gray-600">Choisissez le type de signature</p>
                </div>
            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Signature simple -->
                                <div class="signature-option" data-type="simple">
                                    <input type="radio" name="signature_type" value="simple" id="simple_signature" 
                                           {{ old('signature_type', 'simple') == 'simple' ? 'checked' : '' }}>
                                    <label for="simple_signature" class="block cursor-pointer">
                                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center shadow-md">
                                                    <i class="fas fa-pen-fancy text-white text-sm font-bold"></i>
                                                </div>
                                                <h3 class="font-semibold text-gray-900">Signature Simple</h3>
                                            </div>
                                            <p class="text-sm text-gray-600">Un seul signataire requis</p>
                                        </div>
                                    </label>
                                </div>

                                <!-- Signature séquentielle -->
                                <div class="signature-option" data-type="sequential">
                                    <input type="radio" name="signature_type" value="sequential" id="sequential_signature"
                                           {{ old('signature_type') == 'sequential' ? 'checked' : '' }}>
                                    <label for="sequential_signature" class="block cursor-pointer">
                                        <div class="option-card p-4 border-2 border-gray-200 rounded-lg hover:border-green-300 transition-colors">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center shadow-md">
                                                    <i class="fas fa-list-ol text-white text-sm font-bold"></i>
                                                </div>
                                                <h3 class="font-semibold text-gray-900">Signature Séquentielle</h3>
                                            </div>
                                            <p class="text-sm text-gray-600">Plusieurs signataires dans un ordre défini</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

        <!-- Section Signataires -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6" id="signers-section">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <i class="fas fa-user-friends text-white text-lg font-bold"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Signataires</h2>
                    <p class="text-gray-600">Configurez les signataires pour votre document</p>
                </div>
            </div>

            <!-- Signataire simple -->
            <div id="simple-signer" class="signer-section">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-user text-blue-700 mr-2 font-bold"></i>
                        <span class="font-medium text-blue-800">Signataire unique</span>
                    </div>
                    <p class="text-sm text-blue-700">Un seul signataire signera le document</p>
                </div>
                
                <label for="signer_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-1"></i>
                                Signataire *
                        </label>
                <div class="relative">
                    <select name="signer_id" id="signer_id" class="w-full px-4 py-2.5 sm:py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('signer_id') border-red-500 @enderror appearance-none bg-white text-sm sm:text-base">
                                    <option value="">Sélectionner un signataire</option>
                        @foreach(\App\Models\User::whereHas('role', function($query) { $query->where('name', 'signataire'); })->get() as $user)
                            <option value="{{ $user->id }}" {{ old('signer_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                    </div>
                </div>
                                @error('signer_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

            <!-- Signataires séquentiels -->
            <div id="sequential-signers" class="signer-section hidden">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-list-ol text-green-700 mr-2 font-bold"></i>
                        <span class="font-medium text-green-800">Signatures séquentielles</span>
                    </div>
                    <p class="text-sm text-green-700">Les signataires signeront dans l'ordre défini</p>
                </div>
                
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-list-ol mr-1"></i>
                    Signataires séquentiels *
                            </label>
                <div id="sequential-signers-list" class="space-y-3">
                                <!-- Les signataires seront ajoutés dynamiquement -->
                            </div>
                <button type="button" id="add-sequential-signer" class="mt-3 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-md">
                    <i class="fas fa-plus mr-2 font-bold"></i>
                    Ajouter un signataire
                            </button>
                            @error('sequential_signers')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                        </div>
                            </div>

        <!-- Section Fichier -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <span class="text-white text-lg font-bold">FILE</span>
                        </div>
                    <div>
                    <h2 class="text-xl font-bold text-gray-900">Fichier</h2>
                    <p class="text-gray-600">Sélectionnez le document à signer</p>
                    </div>
                        </div>

            <!-- Zone de téléchargement améliorée -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors" id="file-drop-zone">
                            <div class="space-y-4">
                    <div class="mx-auto w-16 h-16 bg-emerald-200 rounded-full flex items-center justify-center shadow-md">
                        <i class="fas fa-cloud-upload-alt text-emerald-800 text-2xl font-bold"></i>
                                    </div>
                                <div>
                        <label for="file" class="cursor-pointer">
                            <span class="text-lg font-medium text-gray-900">Glissez-déposez votre fichier ici</span>
                            <br>
                            <span class="text-sm text-gray-500">ou cliquez pour sélectionner</span>
                        </label>
                        <input type="file" 
                                       name="file" 
                               id="file" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               class="hidden @error('file') border-red-500 @enderror"
                                       required>
                            </div>
                    <div class="text-xs text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i>
                        Formats acceptés: PDF, DOC, DOCX, JPG, JPEG, PNG (max 50MB)
                                </div>
                    </div>
                        </div>

            <!-- Aperçu du fichier sélectionné -->
            <div id="file-preview" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-file text-blue-600 mr-3"></i>
                        <div>
                            <div class="font-medium text-gray-900" id="file-name"></div>
                            <div class="text-sm text-gray-500" id="file-size"></div>
                                </div>
                                </div>
                    <button type="button" id="remove-file" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

            @error('file')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Boutons d'action élégants -->
        <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4">
            <a href="{{ route('home') }}" class="group inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl shadow-md hover:shadow-lg hover:-translate-y-1 hover:border-gray-400 transition-all duration-300">
                <i class="fas fa-times group-hover:scale-110 transition-transform"></i>
                <span class="font-semibold">Annuler</span>
            </a>
            <button type="submit" class="group inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-success-500 to-success-600 hover:from-success-600 hover:to-success-700 text-white rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-cloud-upload-alt group-hover:scale-110 transition-transform"></i>
                <span class="font-bold">Soumettre le document</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
.signature-option {
    cursor: pointer;
    user-select: none;
}

.signature-option:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.signature-option label {
    cursor: pointer;
    width: 100%;
    display: block;
}

.signature-option input[type="radio"] {
    margin-right: 8px;
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.signature-option input[type="radio"]:checked + label .option-card {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.signature-option[data-type="sequential"] input[type="radio"]:checked + label .option-card {
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}
</style>
@endpush

<script>
// Script direct pour gérer les signatures séquentielles
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Script de signatures séquentielles chargé');
    
    // Fonction pour basculer entre les sections
    function toggleSignatureSections(type) {
        console.log('🔄 Basculement vers:', type);
        
        const simpleSigner = document.getElementById('simple-signer');
        const sequentialSigners = document.getElementById('sequential-signers');
        
        console.log('🔍 Éléments trouvés:', {
            simpleSigner: !!simpleSigner,
            sequentialSigners: !!sequentialSigners
        });
        
        if (type === 'simple') {
            if (simpleSigner) {
                simpleSigner.classList.remove('hidden');
                console.log('✅ Section simple affichée');
            }
            if (sequentialSigners) {
                sequentialSigners.classList.add('hidden');
                console.log('✅ Section séquentielle masquée');
            }
        } else if (type === 'sequential') {
            if (simpleSigner) {
                simpleSigner.classList.add('hidden');
                console.log('✅ Section simple masquée');
            }
            if (sequentialSigners) {
                sequentialSigners.classList.remove('hidden');
                console.log('✅ Section séquentielle affichée');
            }
        }
    }
    
    // Gestion des clics sur les options
    document.querySelectorAll('.signature-option').forEach(option => {
        option.addEventListener('click', function(e) {
            console.log('🖱️ Clic sur option:', this.dataset.type);
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                toggleSignatureSections(radio.value);
            }
        });
    });
    
    // Gestion des changements de radio
    document.querySelectorAll('input[name="signature_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('🔄 Radio changé:', this.value);
            toggleSignatureSections(this.value);
        });
    });
    
    // Initialisation
    const selectedRadio = document.querySelector('input[name="signature_type"]:checked');
    if (selectedRadio) {
        console.log('🎯 Initialisation avec:', selectedRadio.value);
        toggleSignatureSections(selectedRadio.value);
    }
    
    // Gestion des signataires séquentiels
    const addSequentialSignerBtn = document.getElementById('add-sequential-signer');
    const sequentialSignersList = document.getElementById('sequential-signers-list');
    let signerCount = 0;
    
    console.log('🔍 Éléments signataires:', {
        addSequentialSignerBtn: !!addSequentialSignerBtn,
        sequentialSignersList: !!sequentialSignersList
    });
    
    if (addSequentialSignerBtn && sequentialSignersList) {
        // Fonction pour remplir les options des signataires
        function populateSignerOptions(selectElement) {
            const existingSelect = document.getElementById('signer_id');
            if (existingSelect) {
                const options = existingSelect.querySelectorAll('option');
                options.forEach(option => {
                    if (option.value !== '') {
                        const newOption = option.cloneNode(true);
                        selectElement.appendChild(newOption);
                    }
                });
            }
        }
        
        // Ajouter un signataire séquentiel
        addSequentialSignerBtn.addEventListener('click', function() {
            console.log('➕ Ajout d\'un signataire séquentiel');
            signerCount++;
            const signerDiv = document.createElement('div');
            signerDiv.className = 'bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-3';
            signerDiv.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                            ${signerCount}
                        </div>
                        <span class="font-medium text-gray-900">Signataire ${signerCount}</span>
                    </div>
                    <button type="button" class="remove-signer text-red-500 hover:text-red-700 transition-colors">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="relative">
                    <select name="sequential_signers[]" class="w-full px-4 py-2.5 sm:py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-success-500 focus:border-success-500 appearance-none bg-white text-sm sm:text-base" required>
                        <option value="">Sélectionner un signataire</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                    </div>
                </div>
                <input type="hidden" name="sequential_signers_order[]" value="${signerCount}">
            `;
            sequentialSignersList.appendChild(signerDiv);
            
            // Remplir les options du select
            const selectElement = signerDiv.querySelector('select');
            populateSignerOptions(selectElement);
            
            console.log('✅ Signataire ajouté');
        });
        
        // Supprimer un signataire séquentiel
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-signer')) {
                console.log('🗑️ Suppression d\'un signataire');
                const signerDiv = e.target.closest('.bg-white.border');
                if (signerDiv) {
                    signerDiv.remove();
                }
            }
        });
    }
});
</script>

@push('scripts')
<script>
// Script simplifié pour les signataires séquentiels
document.addEventListener('DOMContentLoaded', function() {
    const addSequentialSignerBtn = document.getElementById('add-sequential-signer');
    const sequentialSignersList = document.getElementById('sequential-signers-list');
    let signerCount = 0;

    // Gestion des signataires séquentiels uniquement
    
    // Fonction pour remplir les options des signataires
    function populateSignerOptions(selectElement) {
        // Récupérer les options du select existant (signature simple)
        const existingSelect = document.getElementById('signer_id');
        if (existingSelect) {
            const options = existingSelect.querySelectorAll('option');
            options.forEach(option => {
                if (option.value !== '') {
                    const newOption = option.cloneNode(true);
                    selectElement.appendChild(newOption);
                }
            });
        }
    }

    // Ajouter un signataire séquentiel
    addSequentialSignerBtn.addEventListener('click', function() {
        signerCount++;
        const signerDiv = document.createElement('div');
        signerDiv.className = 'bg-white border border-gray-200 rounded-lg p-4 shadow-sm';
        signerDiv.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-200 rounded-full flex items-center justify-center mr-3 shadow-sm">
                        <span class="text-green-700 font-semibold text-sm">${signerCount}</span>
                    </div>
                    <span class="font-medium text-gray-900">Signataire ${signerCount}</span>
                </div>
                <button type="button" class="remove-signer text-red-500 hover:text-red-700 transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="relative">
                <select name="sequential_signers[]" class="w-full px-4 py-2.5 sm:py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-success-500 focus:border-success-500 appearance-none bg-white text-sm sm:text-base" required>
                    <option value="">Sélectionner un signataire</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                </div>
            </div>
            <!-- Champ caché pour l'ordre -->
            <input type="hidden" name="sequential_signers_order[]" value="${signerCount}">
        `;
        sequentialSignersList.appendChild(signerDiv);
        
        // Remplir les options du select
        const selectElement = signerDiv.querySelector('select');
        populateSignerOptions(selectElement);
        
        // Animation d'apparition
        signerDiv.style.opacity = '0';
        signerDiv.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            signerDiv.style.transition = 'all 0.3s ease';
            signerDiv.style.opacity = '1';
            signerDiv.style.transform = 'translateY(0)';
        }, 10);
    });

    // Supprimer un signataire séquentiel
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-signer')) {
            const signerDiv = e.target.closest('.bg-white');
            if (signerDiv) {
                // Animation de disparition
                signerDiv.style.transition = 'all 0.3s ease';
                signerDiv.style.opacity = '0';
                signerDiv.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    signerDiv.remove();
                    // Renuméroter les signataires
                    renumberSigners();
                }, 300);
            }
        }
    });

    // Renuméroter les signataires après suppression
    function renumberSigners() {
        const signerDivs = document.querySelectorAll('#sequential-signers-list .bg-white');
        signerDivs.forEach((div, index) => {
            const numberSpan = div.querySelector('.bg-green-200 span');
            const titleSpan = div.querySelector('.font-medium');
            const orderInput = div.querySelector('input[name="sequential_signers_order[]"]');
            
            if (numberSpan && titleSpan) {
                numberSpan.textContent = index + 1;
                titleSpan.textContent = `Signataire ${index + 1}`;
            }
            
            if (orderInput) {
                orderInput.value = index + 1;
            }
        });
        signerCount = signerDivs.length;
    }

    // Initialiser l'état selon la valeur sélectionnée
    const selectedType = document.querySelector('input[name="signature_type"]:checked');
    if (selectedType) {
        // Mettre en évidence l'option sélectionnée
        if (selectedType.value === 'simple') {
            selectedType.closest('.signature-option').classList.add('ring-2', 'ring-blue-500');
            selectedType.closest('.signature-option').querySelector('.option-card').classList.add('border-blue-500');
        } else if (selectedType.value === 'sequential') {
            selectedType.closest('.signature-option').classList.add('ring-2', 'ring-green-500');
            selectedType.closest('.signature-option').querySelector('.option-card').classList.add('border-green-500');
            simpleSigner.classList.add('hidden');
            sequentialSigners.classList.remove('hidden');
        }
    }

    // Gestion du fichier avec drag & drop
    const fileInput = document.getElementById('file');
    const fileDropZone = document.getElementById('file-drop-zone');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFileBtn = document.getElementById('remove-file');

    // Drag & drop events
    fileDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileDropZone.classList.add('border-blue-400', 'bg-blue-50');
    });

    fileDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileDropZone.classList.remove('border-blue-400', 'bg-blue-50');
    });

    fileDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        fileDropZone.classList.remove('border-blue-400', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelection(files[0]);
        }
    });

    // Click to select file
    fileDropZone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelection(e.target.files[0]);
        }
    });

    // Remove file
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
        fileDropZone.classList.remove('border-blue-400', 'bg-blue-50');
    });

    function handleFileSelection(file) {
        // Vérifier la taille du fichier (50MB max)
        const maxSize = 50 * 1024 * 1024; // 50MB
        if (file.size > maxSize) {
            alert('Le fichier est trop volumineux. Taille maximale: 50MB');
            return;
        }

        // Vérifier le type de fichier
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Type de fichier non supporté. Formats acceptés: PDF, DOC, DOCX, JPG, JPEG, PNG');
            return;
        }

        // Afficher l'aperçu
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        filePreview.classList.remove('hidden');
        
        // Mettre à jour l'input
        fileInput.files = new DataTransfer().files;
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endpush

@endsection