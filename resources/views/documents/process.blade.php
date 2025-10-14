@extends('layouts.app')

@section('title', 'Traiter le Document')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Fil d'Ariane Élégant -->
    <nav class="mb-6 sm:mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-xs sm:text-sm flex-wrap">
            <li>
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                    <i class="fas fa-home text-sm"></i>
                    <span class="hidden sm:inline">Accueil</span>
                </a>
            </li>
            <li class="text-gray-400">
                <i class="fas fa-chevron-right text-xs"></i>
            </li>
            @if(isset($document) && $document->sequential_signatures)
                <li>
                    <a href="{{ route('signatures.simple.index') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                        <i class="fas fa-list-ol text-sm"></i>
                        <span class="hidden sm:inline">Signatures Séquentielles</span>
                    </a>
                </li>
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </li>
                <li class="flex items-center gap-1.5 text-primary-600 font-semibold">
                    <i class="fas fa-pen-fancy text-sm"></i>
                    <span class="hidden sm:inline">Signer Document</span>
                </li>
            @else
                <li>
                    <a href="{{ route('signatures.index') }}" class="flex items-center gap-1.5 text-gray-600 hover:text-primary-600 transition-colors duration-200">
                        <i class="fas fa-pen-fancy text-sm"></i>
                        <span class="hidden sm:inline">Documents à Signer</span>
                    </a>
                </li>
                <li class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </li>
                <li class="flex items-center gap-1.5 text-primary-600 font-semibold">
                    <i class="fas fa-edit text-sm"></i>
                    <span class="hidden sm:inline">Traiter Document</span>
                </li>
            @endif
        </ol>
    </nav>
    
    <!-- En-tête du document -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">
                    {{ $document->title ?? 'Document à Signer' }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        PDF Document
                    </span>
                    <span class="text-gray-300">•</span>
                    <span class="flex items-center gap-1">
                        <i class="fas fa-clock text-blue-500"></i>
                        {{ $document->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone PDF optimisée pour mobile -->
    <div class="pdf-container-mobile">
        <div id="pdfViewer" class="pdf-viewer-mobile">
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <div class="text-lg font-medium text-white mb-2">Chargement du PDF...</div>
                <div class="text-sm text-white">Veuillez patienter</div>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== STYLES SIMPLIFIÉS SANS CONFLITS ===== */
.pdf-container-mobile {
    position: relative;
    background: #f8fafc;
    border-radius: 12px;
    overflow: visible;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    touch-action: pan-y pinch-zoom;
}

.pdf-viewer-mobile {
    position: relative;
    width: 100%;
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border-radius: 8px;
    margin: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: visible;
    touch-action: pan-y pinch-zoom;
}

.pdf-viewer-mobile canvas {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
    touch-action: pan-y pinch-zoom;
    pointer-events: auto;
    overflow: visible;
}

/* Mode lecture : permettre le défilement et le zoom */
.pdf-viewer-mobile canvas {
    touch-action: pan-x pan-y pinch-zoom;
    -webkit-overflow-scrolling: touch;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
    .pdf-container-mobile {
        margin: 0.5rem;
    }
    
    .pdf-viewer-mobile {
        margin: 0.5rem;
        min-height: 50vh;
    }
}
</style>

<script>
// Configuration PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Initialiser le module unifié simplifié
document.addEventListener('DOMContentLoaded', function() {
    const config = {
        pdfUrl: {!! json_encode($displayPdfUrl ?? $pdfUrl) !!},
        signatureUrl: {!! json_encode($signatureUrl) !!},
        parapheUrl: {!! json_encode($parapheUrl) !!},
        cachetUrl: {!! json_encode($cachetUrl ?? "/signatures/user-cachet") !!},
        uploadUrl: {!! json_encode($uploadUrl ?? "/documents/upload-signed-pdf") !!},
        redirectUrl: {!! json_encode($redirectUrl ?? "/documents/{$document->id}/process/view") !!},
        documentId: {!! json_encode($document->id) !!},
        containerId: 'pdfViewer',
        processFormId: 'processForm',
        actionTypeInputId: 'action_type',
        signatureTypeInputId: 'signature_type',
        parapheTypeInputId: 'paraphe_type',
        cachetTypeInputId: 'cachet_type',
        liveSignatureDataInputId: 'live_signature_data',
        liveParapheDataInputId: 'live_paraphe_data',
        liveCachetDataInputId: 'live_cachet_data',
        signatureXInputId: 'signature_x',
        signatureYInputId: 'signature_y',
        parapheXInputId: 'paraphe_x',
        parapheYInputId: 'paraphe_y',
        cachetXInputId: 'cachet_x',
        cachetYInputId: 'cachet_y',
        addSignatureBtnId: 'addSignatureBtn',
        addParapheBtnId: 'addParapheBtn',
        addCachetBtnId: 'addCachetBtn',
        addSignAndParapheBtnId: 'addSignAndParapheBtn',
        clearAllBtnId: 'clearAllBtn',
        submitBtnId: 'submitBtn',
        zoomInBtnId: 'zoomInBtn',
        zoomOutBtnId: 'zoomOutBtn',
        resetZoomBtnId: 'resetZoomBtn',
        prevPageBtnId: 'prevPageBtn',
        nextPageBtnId: 'nextPageBtn',
        firstPageBtnId: 'firstPageBtn',
        lastPageBtnId: 'lastPageBtn',
        currentPageNumberId: 'currentPageNumber',
        totalPagesNumberId: 'totalPagesNumber',
        signatureCanvasId: 'signatureCanvas',
        parapheCanvasId: 'parapheCanvas',
        cachetCanvasId: 'cachetCanvas',
        qualitySelectId: 'qualitySelect',
        allowSignature: {{ $allowSignature ? 'true' : 'false' }},
        allowParaphe: {{ $allowParaphe ? 'true' : 'false' }},
        allowCachet: {{ $allowCachet ? 'true' : 'false' }},
        allowBoth: {{ $allowBoth ? 'true' : 'false' }},
        allowAll: {{ $allowAll ? 'true' : 'false' }},
        isReadOnly: {{ isset($isReadOnly) && $isReadOnly ? 'true' : 'false' }}
    };

    // Initialiser le module PDF
    window.pdfOverlayModule = new PDFOverlayUnifiedModule(config);
    window.pdfOverlayModule.init();
});
</script>
@endsection
