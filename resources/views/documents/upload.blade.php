@extends('layouts.app')

@section('title', 'Soumettre un Document')

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
                <i class="fas fa-upload"></i>
                <span class="hidden sm:inline ml-1">Nouveau Document</span>
            </li>
        </ol>
    </nav>
    
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            📄 Soumettre un Document
                    </h1>
        <p class="text-gray-600">
            Envoyez vos documents pour signature électronique
        </p>
        </div>
                
    <!-- Messages -->
            @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-3 font-bold"></i>
                <span class="text-green-800">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 mr-3 font-bold"></i>
                <span class="text-red-800">{{ session('error') }}</span>
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
                    <select name="type" id="type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="contrat" {{ old('type') == 'contrat' ? 'selected' : '' }}>Contrat</option>
                                    <option value="facture" {{ old('type') == 'facture' ? 'selected' : '' }}>Facture</option>
                                    <option value="rapport" {{ old('type') == 'rapport' ? 'selected' : '' }}>Rapport</option>
                                    <option value="autre" {{ old('type') == 'autre' ? 'selected' : '' }}>Autre</option>
                                </select>
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
                                <div class="signature-type-option" data-type="simple">
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors">
                                        <input type="radio" name="signature_type" value="simple" class="sr-only" 
                                               {{ old('signature_type', 'simple') == 'simple' ? 'checked' : '' }}>
                                        <div class="flex-1">
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
                                <div class="signature-type-option" data-type="sequential">
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors">
                                        <input type="radio" name="signature_type" value="sequential" class="sr-only"
                                               {{ old('signature_type') == 'sequential' ? 'checked' : '' }}>
                                        <div class="flex-1">
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
                    <select name="signer_id" id="signer_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('signer_id') border-red-500 @enderror">
                                    <option value="">Sélectionner un signataire</option>
                        @foreach(\App\Models\User::whereHas('role', function($query) { $query->where('name', 'signataire'); })->get() as $user)
                            <option value="{{ $user->id }}" {{ old('signer_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
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
                        Formats acceptés: PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)
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

        <!-- Boutons d'action -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-md">
                <i class="fas fa-times mr-2 font-bold"></i>
                Annuler
            </a>
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md">
                <i class="fas fa-upload mr-2 font-bold"></i>
                Soumettre le document
                    </button>
                </div>
                    </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const signatureTypeRadios = document.querySelectorAll('input[name="signature_type"]');
    const simpleSigner = document.getElementById('simple-signer');
    const sequentialSigners = document.getElementById('sequential-signers');
    const addSequentialSignerBtn = document.getElementById('add-sequential-signer');
    const sequentialSignersList = document.getElementById('sequential-signers-list');
    
    let signerCount = 0;

    // Gestion du changement de type de signature
    signatureTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'simple') {
                simpleSigner.classList.remove('hidden');
                sequentialSigners.classList.add('hidden');
            } else {
                simpleSigner.classList.add('hidden');
                sequentialSigners.classList.remove('hidden');
            }
        });
    });

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
                <select name="sequential_signers[]" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    <option value="">Sélectionner un signataire</option>
                    @foreach(\App\Models\User::whereHas('role', function($query) { $query->where('name', 'signataire'); })->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
            <!-- Champ caché pour l'ordre -->
            <input type="hidden" name="sequential_signers_order[]" value="${signerCount}">
        `;
        sequentialSignersList.appendChild(signerDiv);
        
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
    if (selectedType && selectedType.value === 'sequential') {
        simpleSigner.classList.add('hidden');
        sequentialSigners.classList.remove('hidden');
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
        // Vérifier la taille du fichier (10MB max)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert('Le fichier est trop volumineux. Taille maximale: 10MB');
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
@endsection