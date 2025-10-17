@extends('layouts.app')

@section('title', 'Traiter le Document')

@section('content')
<div class="container-fluid">
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
            <li>
                <a href="{{ route('signatures.simple.index') }}" class="sophisticated-breadcrumb-link">
                    <i class="fas fa-list-ol"></i>
                    <span class="hidden sm:inline ml-1">Signatures Séquentielles</span>
                </a>
            </li>
            <li class="sophisticated-breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </li>
            <li class="sophisticated-breadcrumb-current">
                <i class="fas fa-pen-fancy"></i>
                <span class="hidden sm:inline ml-1">Traiter Document</span>
            </li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-12">
            <!-- En-tête du document -->
            <div class="card-eps">
                <div class="card-body">
                    <!-- Informations du document simplifiées -->
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6 mb-6 border sophisticated-border shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-white mb-2">
                                    <i class="fas fa-file-alt text-white"></i>
                                    <span>Type</span>
                                </div>
                                <div class="text-lg font-semibold text-white">{{ ucfirst($document->type) }}</div>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-white mb-2">
                                    <i class="fas fa-user text-white"></i>
                                    <span>Uploadé par</span>
                                </div>
                                <div class="text-lg font-semibold text-white">{{ $document->uploader->name }}</div>
                            </div>
                            
                            <div class="bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-white mb-2">
                                    <i class="fas fa-calendar text-white"></i>
                                    <span>Date</span>
                                </div>
                                <div class="text-lg font-semibold text-white">{{ $document->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        
                        @if($document->description)
                            <div class="mt-6 bg-white rounded-lg p-4 border sophisticated-border">
                                <div class="flex items-center gap-2 text-sm font-medium text-white mb-3">
                                    <i class="fas fa-align-left text-white"></i>
                                    <span>Description</span>
                                </div>
                                <div class="text-white leading-relaxed">{{ $document->description }}</div>
                            </div>
                        @endif
                        
                        <!-- Liste des signataires séquentiels -->
                        <div class="mt-6 bg-white rounded-lg p-4 border sophisticated-border">
                            <div class="flex items-center gap-2 text-sm font-medium text-white mb-3">
                                <i class="fas fa-users text-white"></i>
                                <span>Signataires séquentiels</span>
                            </div>
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
                    
                    <!-- Statut du document -->
                    <div class="document-status">
                        <div class="status-badge status-{{ $document->status }}">
                            <i class="fas fa-{{ $statusIcon }}"></i>
                            <span>{{ $statusText }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire caché pour les actions -->
            <form id="processForm" action="{{ $formAction }}" method="POST" style="display: block;">
                @csrf
                
                <!-- Champs cachés -->
                <input type="hidden" name="action_type" id="action_type" value="{{ $defaultAction }}">
                <input type="hidden" name="signature_type" id="signature_type" value="png">
                <input type="hidden" name="paraphe_type" id="paraphe_type" value="png">
                <input type="hidden" name="live_signature_data" id="live_signature_data">
                <input type="hidden" name="live_paraphe_data" id="live_paraphe_data">
                <input type="hidden" name="signature_x" id="signature_x">
                <input type="hidden" name="signature_y" id="signature_y">
                <input type="hidden" name="paraphe_x" id="paraphe_x">
                <input type="hidden" name="paraphe_y" id="paraphe_y">
                <input type="hidden" name="signature_comment" id="signature_comment" value="">
                <input type="hidden" name="paraphe_comment" id="paraphe_comment" value="">
            </form>

            <!-- Zone d'affichage PDF -->
            <div class="modern-card">
                <!-- Interface de traitement simplifiée -->
                <div class="bg-white rounded-lg shadow-sm border sophisticated-border p-4 mb-6">
                    <!-- Barre d'outils principale -->
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2 text-white">
                                <i class="fas fa-file-pdf text-white"></i>
                                <span class="font-semibold text-lg text-white">Document PDF</span>
                            </div>
                        </div>
                        
                        @if(isset($isReadOnly) && $isReadOnly)
                            <!-- Mode lecture seule -->
                            <div class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg shadow-sm">
                                <i class="fas fa-lock text-white"></i>
                                <span class="font-medium">Document Signé - Mode Lecture Seule</span>
                            </div>
                        @else
                            <!-- Actions principales -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex flex-wrap gap-2">
                                    @if($allowSignature)
                                        <button type="button" id="addSignatureBtn" class="inline-flex items-center gap-2 px-4 py-2 sophisticated-btn-primary text-white rounded-lg sophisticated-btn-primary focus:ring-2 sophisticated-focus focus:ring-offset-2 transition-colors" 
                                                aria-label="Ajouter une signature au document"
                                                aria-describedby="signature-help">
                                            <i class="fas fa-pen-fancy" aria-hidden="true"></i>
                                            <span>Signer</span>
                                        </button>
                                        <div id="signature-help" class="sr-only">
                                            Cliquez pour ajouter une signature au document. Vous pourrez ensuite cliquer sur le document pour la positionner.
                                        </div>
                                    @endif
                                    
                                    @if($allowParaphe)
                                        <button type="button" id="addParapheBtn" class="inline-flex items-center gap-2 px-4 py-2 sophisticated-btn-primary text-white rounded-lg sophisticated-btn-primary focus:ring-2 sophisticated-focus focus:ring-offset-2 transition-colors"
                                                aria-label="Ajouter un paraphe au document"
                                                aria-describedby="paraphe-help">
                                            <i class="fas fa-pen-nib" aria-hidden="true"></i>
                                            <span>Parapher</span>
                                        </button>
                                        <div id="paraphe-help" class="sr-only">
                                            Cliquez pour ajouter un paraphe au document. Vous pourrez ensuite cliquer sur le document pour le positionner.
                                        </div>
                                    @endif
                                    
                                    @if($allowCachet)
                                        <button type="button" id="addCachetBtn" class="inline-flex items-center gap-2 px-4 py-2 sophisticated-btn-primary text-white rounded-lg sophisticated-btn-primary focus:ring-2 sophisticated-focus focus:ring-offset-2 transition-colors"
                                                aria-label="Ajouter un cachet au document"
                                                aria-describedby="cachet-help">
                                            <i class="fas fa-stamp" aria-hidden="true"></i>
                                            <span>Cacheter</span>
                                        </button>
                                        <div id="cachet-help" class="sr-only">
                                            Cliquez pour ajouter un cachet au document. Vous pourrez ensuite cliquer sur le document pour le positionner.
                                        </div>
                                    @endif
                                    
                                    <button type="button" id="clearAllBtn" class="inline-flex items-center gap-2 px-4 py-2 sophisticated-btn-primary text-white rounded-lg sophisticated-btn-primary focus:ring-2 sophisticated-focus focus:ring-offset-2 transition-colors"
                                            aria-label="Effacer toutes les signatures et paraphes"
                                            aria-describedby="clear-help">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        <span>Effacer</span>
                                    </button>
                                    <div id="clear-help" class="sr-only">
                                        Cliquez pour supprimer toutes les signatures et paraphes du document.
                                    </div>
                                </div>
                                
                                <div class="flex gap-2">
                                    <button type="submit" form="processForm" id="submitBtn" class="inline-flex items-center gap-2 px-4 py-2 sophisticated-btn-primary text-white rounded-lg sophisticated-btn-primary focus:ring-2 sophisticated-focus focus:ring-offset-2 transition-colors"
                                            aria-label="Enregistrer le document avec les signatures et paraphes"
                                            aria-describedby="submit-help">
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                        <span>Enregistrer</span>
                                    </button>
                                    <div id="submit-help" class="sr-only">
                                        Cliquez pour enregistrer le document avec toutes les signatures et paraphes ajoutés.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contrôles PDF - toujours visibles mais compacts -->
                            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2">
                                <button type="button" id="zoomOutBtn" class="p-2 hover:bg-white/20 rounded transition-colors" title="Zoom arrière">
                                    <i class="fas fa-search-minus text-white"></i>
                                </button>
                                <span id="zoomLevel" class="text-white text-sm font-medium min-w-[3rem] text-center">100%</span>
                                <button type="button" id="zoomInBtn" class="p-2 hover:bg-white/20 rounded transition-colors" title="Zoom avant">
                                    <i class="fas fa-search-plus text-white"></i>
                                </button>
                                <div class="w-px h-6 bg-white/30 mx-1"></div>
                                <button type="button" id="resetZoomBtn" class="p-2 hover:bg-white/20 rounded transition-colors" title="Réinitialiser le zoom">
                                    <i class="fas fa-expand-arrows-alt text-white"></i>
                                </button>
                                <button type="button" id="autoFitBtn" class="p-2 hover:bg-white/20 rounded transition-colors" title="Ajuster automatiquement">
                                    <i class="fas fa-compress-arrows-alt text-white"></i>
                                </button>
                                <div class="w-px h-6 bg-white/30 mx-1"></div>
                                <button type="button" id="prevPageBtn" class="p-2 hover:bg-white/20 rounded transition-colors disabled:opacity-50" title="Page précédente" disabled>
                                    <i class="fas fa-chevron-left text-white"></i>
                                </button>
                                <span id="pageInfo" class="text-white text-sm font-medium min-w-[4rem] text-center">1/1</span>
                                <button type="button" id="nextPageBtn" class="p-2 hover:bg-white/20 rounded transition-colors disabled:opacity-50" title="Page suivante" disabled>
                                    <i class="fas fa-chevron-right text-white"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Zone d'affichage PDF -->
                <div id="pdfViewer" class="pdf-viewer-container">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts nécessaires -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Fabric.js via Vite (plus fiable que CDN) -->
@vite(['resources/js/fabric-bundle.js'])
<script src="{{ asset('js/pdf-overlay-unified-module.js') }}"></script>

<script>
// Configuration PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Prévenir les instances multiples d'Alpine.js
if (window.Alpine) {
    console.warn('Alpine.js déjà chargé, évitement des conflits...');
}

// Initialiser le module unifié simplifié
document.addEventListener('DOMContentLoaded', function() {
    const config = {
        pdfUrl: '{{ $pdfUrl }}',
        signatureUrl: '{{ $signatureUrl }}',
        parapheUrl: '{{ $parapheUrl }}',
        cachetUrl: '{{ $cachetUrl }}',
        documentId: {{ $document->id }},
        containerId: 'pdfViewer',
        processFormId: 'processForm',
        actionTypeInputId: 'action_type',
        signatureTypeInputId: 'signature_type',
        parapheTypeInputId: 'paraphe_type',
        liveSignatureDataInputId: 'live_signature_data',
        liveParapheDataInputId: 'live_paraphe_data',
        signatureXInputId: 'signature_x',
        signatureYInputId: 'signature_y',
        parapheXInputId: 'paraphe_x',
        parapheYInputId: 'paraphe_y',
        addSignatureBtnId: 'addSignatureBtn',
        addParapheBtnId: 'addParapheBtn',
        addCachetBtnId: 'addCachetBtn',
        clearAllBtnId: 'clearAllBtn',
        submitBtnId: 'submitBtn',
        zoomInBtnId: 'zoomInBtn',
        zoomOutBtnId: 'zoomOutBtn',
        resetZoomBtnId: 'resetZoomBtn',
        autoFitBtnId: 'autoFitBtn',
        prevPageBtnId: 'prevPageBtn',
        nextPageBtnId: 'nextPageBtn',
        pageInfoId: 'pageInfo',
        pdfContainerId: 'pdfViewer',
        allowSignature: {{ $allowSignature ? 'true' : 'false' }},
        allowParaphe: {{ $allowParaphe ? 'true' : 'false' }},
        allowBoth: {{ $allowBoth ? 'true' : 'false' }},
        isReadOnly: {{ isset($isReadOnly) && $isReadOnly ? 'true' : 'false' }}
    };

    const unifiedModule = new PDFOverlayUnifiedModule(config);
    
    // Initialiser le module
    unifiedModule.init().catch(error => {
        console.error('Erreur initialisation module PDF:', error);
        
        // Essayer l'initialisation manuelle après un délai
        setTimeout(() => {
            console.log('🔄 Tentative d\'initialisation manuelle...');
            unifiedModule.initManual();
        }, 2000);
    });
});
</script>

<style>
/* Styles pour l'interface sophistiquée */
.pdf-viewer-container {
    position: relative;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
}


/* Styles pour les boutons sophistiqués */
.sophisticated-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.sophisticated-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.sophisticated-focus:focus {
    outline: none;
    ring: 2px;
    ring-color: rgba(102, 126, 234, 0.5);
}

/* Styles pour les breadcrumbs */
.sophisticated-breadcrumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.sophisticated-breadcrumb-link {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.sophisticated-breadcrumb-link:hover {
    color: white;
}

.sophisticated-breadcrumb-separator {
    color: rgba(255, 255, 255, 0.6);
}

.sophisticated-breadcrumb-current {
    color: white;
    font-weight: 600;
}

/* Styles pour les cartes */
.card-eps {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.sophisticated-border {
    border-color: #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
    .sophisticated-breadcrumb {
        padding: 0.75rem 1rem;
    }
    
    .sophisticated-breadcrumb ol {
        flex-wrap: wrap;
    }
}
</style>
@endsection
