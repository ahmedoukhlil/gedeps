@extends('layouts.app')

@section('title', 'Signer le Document')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    ✍️ Signature du Document
                </h1>
                <p class="text-gray-600">
                    {{ $document->document_name }}
                </p>
            </div>
            <a href="{{ route('signatures.simple.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à la liste
            </a>
        </div>
    </div>

    <!-- Informations du document -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-8">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations du document</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Nom:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $document->document_name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Type:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $document->type_name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Créé par:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $document->uploader->name }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Créé le:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Progression des signatures</h3>
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Avancement</span>
                            <span class="text-sm text-gray-500">{{ $document->current_signature_index }}/{{ $document->sequentialSignatures->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-500 h-3 rounded-full" style="width: {{ ($document->current_signature_index / $document->sequentialSignatures->count()) * 100 }}%"></div>
                        </div>
                    </div>

                    <!-- Liste des signataires -->
                    <div class="space-y-2">
                        @foreach($document->sequentialSignatures->sortBy('signature_order') as $signature)
                            <div class="flex items-center justify-between p-2 rounded-lg
                                @if($signature->status === 'signed') bg-green-50 border border-green-200
                                @elseif($signature->signature_order == $document->current_signature_index + 1) bg-yellow-50 border border-yellow-200
                                @else bg-gray-50 border border-gray-200 @endif">
                                <div class="flex items-center">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold mr-3
                                        @if($signature->status === 'signed') bg-green-500 text-white
                                        @elseif($signature->signature_order == $document->current_signature_index + 1) bg-yellow-500 text-white
                                        @else bg-gray-400 text-white @endif">
                                        {{ $signature->signature_order }}
                                    </span>
                                    <span class="text-sm font-medium">{{ $signature->user->name }}</span>
                                </div>
                                <div class="text-xs">
                                    @if($signature->status === 'signed')
                                        <span class="text-green-600 font-medium">✓ Signé</span>
                                    @elseif($signature->signature_order == $document->current_signature_index + 1)
                                        <span class="text-yellow-600 font-medium">⏳ En cours</span>
                                    @else
                                        <span class="text-gray-500">⏳ En attente</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interface de signature PDF -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Signature du document</h3>
            
            <!-- Contrôles de signature -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-3 mb-4">
                    <button id="signDocumentBtn" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-pen-fancy mr-2"></i>
                        Signer le document
                    </button>
                    
                    <button id="initialDocumentBtn" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        <i class="fas fa-stamp mr-2"></i>
                        Parapher toutes les pages
                    </button>
                    
                    <button id="clearSignaturesBtn" class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <i class="fas fa-eraser mr-2"></i>
                        Effacer les signatures
                    </button>
                    
                    <button id="helpBtn" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-question-circle mr-2"></i>
                        Aide
                    </button>
                </div>
                
                <!-- Zone d'affichage du PDF -->
                <div id="pdfContainer" class="border-2 border-gray-300 rounded-lg bg-gray-50 min-h-96 flex items-center justify-center">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-file-pdf text-4xl mb-4"></i>
                        <p>Chargement du document PDF...</p>
                    </div>
                </div>
            </div>

            <!-- Formulaire de signature -->
            <form id="signatureForm" method="POST" action="{{ route('signatures.simple.sign', $document) }}">
                @csrf
                
                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (optionnel)
                    </label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Ajoutez des commentaires sur votre signature..."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Votre signature sera ajoutée au document et celui-ci passera au signataire suivant.
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('signatures.simple.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Annuler
                        </a>
                        
                        <button type="button" id="savePdfBtn" 
                                class="inline-flex items-center px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <i class="fas fa-save mr-2"></i>
                            <span id="submitText">Sauvegarder le document signé</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts nécessaires pour le système de signature PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="{{ asset('js/pdf-overlay-signature-module.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration pour le module de signature PDF
    window.documentConfig = {
        documentId: {{ $document->id }},
        pdfUrl: '{{ Storage::url($document->path_original) }}',
        csrfToken: '{{ csrf_token() }}',
        userId: {{ auth()->id() }},
        userName: '{{ auth()->user()->name }}',
        saveUrl: '{{ route("signatures.simple.save-signed-pdf", $document) }}'
    };
    
    console.log('🚀 Configuration du document:', window.documentConfig);
});
</script>
@endsection
