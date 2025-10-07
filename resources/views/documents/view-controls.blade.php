{{-- View Controls - Contrôles pour la vue en mode lecture seule --}}

<!-- Conteneur principal des contrôles PDF pour la vue -->
<div class="pdf-controls-container">
    
    <!-- Groupe 1: Informations du document -->
    <div class="control-group">
        <div class="flex flex-col items-center gap-2">
            <div class="text-white text-sm font-medium">
                <i class="fas fa-file-pdf" aria-hidden="true"></i>
                Document Signé - Mode Lecture
            </div>
            <div class="text-white text-xs opacity-75">
                Toutes les signatures ont été apposées
            </div>
        </div>
    </div>

    <!-- Groupe 2: Navigation PDF -->
    <div class="control-group">
        <div class="flex items-center gap-3 justify-center">
            <!-- Navigation rapide -->
            <div class="flex items-center gap-1">
                <button type="button" id="firstPageBtn" 
                        class="pdf-nav-btn"
                        title="Aller à la première page"
                        aria-label="Aller à la première page">
                    <i class="fas fa-angle-double-left" aria-hidden="true"></i>
                </button>
                
                <button type="button" id="prevPageBtn" 
                        class="pdf-nav-btn"
                        title="Page précédente"
                        aria-label="Aller à la page précédente">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
            </div>

            <!-- Indicateur de page -->
            <div id="pageInfo" class="page-indicator">
                <span id="currentPage">1</span> / <span id="totalPages">1</span>
            </div>

            <!-- Navigation suivante -->
            <div class="flex items-center gap-1">
                <button type="button" id="nextPageBtn" 
                        class="pdf-nav-btn"
                        title="Page suivante"
                        aria-label="Aller à la page suivante">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
                
                <button type="button" id="lastPageBtn" 
                        class="pdf-nav-btn"
                        title="Aller à la dernière page"
                        aria-label="Aller à la dernière page">
                    <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Groupe 3: Contrôles de zoom -->
    <div class="control-group">
        <div class="flex items-center gap-3 justify-center">
            <div class="zoom-controls">
                <button type="button" id="zoomOutBtn" 
                        class="zoom-btn"
                        title="Réduire le zoom"
                        aria-label="Réduire le zoom">
                    <i class="fas fa-search-minus" aria-hidden="true"></i>
                </button>
                
                <button type="button" id="autoFitBtn" 
                        class="zoom-btn"
                        title="Ajuster à la page"
                        aria-label="Ajuster la page à la largeur">
                    <i class="fas fa-compress-arrows-alt" aria-hidden="true"></i>
                </button>
                
                <button type="button" id="zoomInBtn" 
                        class="zoom-btn"
                        title="Augmenter le zoom"
                        aria-label="Augmenter le zoom">
                    <i class="fas fa-search-plus" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Groupe 4: Actions de visualisation -->
    <div class="control-group">
        <div class="quick-actions">
            <button type="button" id="resetZoomBtn" 
                    class="quick-action-btn"
                    title="Réinitialiser le zoom"
                    aria-label="Réinitialiser le zoom">
                <i class="fas fa-expand-arrows-alt" aria-hidden="true"></i>
                <span>Reset Zoom</span>
            </button>
            
            <button type="button" id="fullscreenBtn" 
                    class="quick-action-btn"
                    title="Mode plein écran"
                    aria-label="Basculer en mode plein écran">
                <i class="fas fa-expand" aria-hidden="true"></i>
                <span>Plein Écran</span>
            </button>
            
            <button type="button" id="downloadBtn" 
                    class="quick-action-btn"
                    title="Télécharger le PDF signé"
                    aria-label="Télécharger le PDF signé">
                <i class="fas fa-download" aria-hidden="true"></i>
                <span>Télécharger</span>
            </button>
        </div>
    </div>

    <!-- Groupe 5: Actions de navigation -->
    <div class="control-group">
        <div class="flex gap-3 justify-center">
            <a href="{{ route('signatures.simple.index') }}" 
               class="enhanced-btn enhanced-btn-secondary">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                <span>Retour à la Liste</span>
            </a>
            
            <a href="{{ route('signatures.simple.show', $document) }}" 
               class="enhanced-btn enhanced-btn-primary">
                <i class="fas fa-edit" aria-hidden="true"></i>
                <span>Mode Édition</span>
            </a>
        </div>
    </div>

    <!-- Groupe 6: Informations de statut (pour signatures séquentielles) -->
    @if(isset($sequentialSignatures) && $sequentialSignatures->count() > 0)
        <div class="control-group">
            <div class="flex flex-col items-center gap-2">
                <div class="text-white text-sm font-medium">
                    <i class="fas fa-users" aria-hidden="true"></i>
                    Signatures Séquentielles - Terminées
                </div>
                <div class="flex flex-wrap gap-2 justify-center">
                    @foreach($sequentialSignatures as $signature)
                        <div class="flex items-center gap-2 px-3 py-1 bg-green-500 bg-opacity-30 rounded-full text-xs">
                            <i class="fas fa-check-circle text-green-300"></i>
                            <span class="text-white">{{ $signature->user->name }}</span>
                            @if($signature->signed_at)
                                <span class="text-green-200 text-xs">
                                    ({{ \Carbon\Carbon::parse($signature->signed_at)->format('d/m/Y H:i') }})
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Groupe 7: Informations du document -->
    <div class="control-group">
        <div class="flex flex-col items-center gap-2">
            <div class="text-white text-sm font-medium">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                Informations du Document
            </div>
            <div class="flex flex-wrap gap-2 justify-center text-xs">
                <div class="px-2 py-1 bg-white bg-opacity-20 rounded-full">
                    <span class="text-white">ID: {{ $document->id }}</span>
                </div>
                <div class="px-2 py-1 bg-white bg-opacity-20 rounded-full">
                    <span class="text-white">Type: {{ $document->type }}</span>
                </div>
                <div class="px-2 py-1 bg-white bg-opacity-20 rounded-full">
                    <span class="text-white">Statut: {{ $document->status }}</span>
                </div>
                @if($document->last_signature_at)
                    <div class="px-2 py-1 bg-white bg-opacity-20 rounded-full">
                        <span class="text-white">Signé le: {{ \Carbon\Carbon::parse($document->last_signature_at)->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Menu mobile des contrôles (pour les petits écrans) -->
<div class="lg:hidden fixed bottom-4 right-4 z-50">
    <button id="mobileControlsToggle" 
            class="pdf-nav-btn bg-gradient-to-r from-green-500 to-blue-600 shadow-lg"
            title="Ouvrir les contrôles"
            aria-label="Ouvrir le menu des contrôles">
        <i class="fas fa-eye" aria-hidden="true"></i>
    </button>
</div>

<!-- Overlay mobile -->
<div id="mobileControlsOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-6 transform translate-y-full transition-transform duration-300">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Contrôles de Visualisation</h3>
            <button id="mobileControlsClose" 
                    class="p-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        
        <!-- Contrôles mobiles -->
        <div class="space-y-4">
            <!-- Navigation -->
            <div class="flex justify-center gap-2">
                <button type="button" id="mobileFirstPageBtn" class="pdf-nav-btn">
                    <i class="fas fa-angle-double-left" aria-hidden="true"></i>
                </button>
                <button type="button" id="mobilePrevPageBtn" class="pdf-nav-btn">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <div class="page-indicator">
                    <span id="mobileCurrentPage">1</span> / <span id="mobileTotalPages">1</span>
                </div>
                <button type="button" id="mobileNextPageBtn" class="pdf-nav-btn">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
                <button type="button" id="mobileLastPageBtn" class="pdf-nav-btn">
                    <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                </button>
            </div>
            
            <!-- Zoom -->
            <div class="flex justify-center gap-2">
                <button type="button" id="mobileZoomOutBtn" class="zoom-btn">
                    <i class="fas fa-search-minus" aria-hidden="true"></i>
                </button>
                <button type="button" id="mobileAutoFitBtn" class="zoom-btn">
                    <i class="fas fa-compress-arrows-alt" aria-hidden="true"></i>
                </button>
                <button type="button" id="mobileZoomInBtn" class="zoom-btn">
                    <i class="fas fa-search-plus" aria-hidden="true"></i>
                </button>
            </div>
            
            <!-- Actions -->
            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="mobileDownloadBtn" class="enhanced-btn enhanced-btn-primary text-sm">
                    <i class="fas fa-download" aria-hidden="true"></i>
                    <span>Télécharger</span>
                </button>
                <button type="button" id="mobileFullscreenBtn" class="enhanced-btn enhanced-btn-secondary text-sm">
                    <i class="fas fa-expand" aria-hidden="true"></i>
                    <span>Plein Écran</span>
                </button>
            </div>
            
            <!-- Navigation -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('signatures.simple.index') }}" class="enhanced-btn enhanced-btn-secondary text-sm">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    <span>Retour</span>
                </a>
                <a href="{{ route('signatures.simple.show', $document) }}" class="enhanced-btn enhanced-btn-primary text-sm">
                    <i class="fas fa-edit" aria-hidden="true"></i>
                    <span>Édition</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les contrôles mobiles -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileControlsToggle');
    const mobileOverlay = document.getElementById('mobileControlsOverlay');
    const mobileClose = document.getElementById('mobileControlsClose');
    
    if (mobileToggle && mobileOverlay && mobileClose) {
        mobileToggle.addEventListener('click', function() {
            mobileOverlay.classList.remove('hidden');
            setTimeout(() => {
                mobileOverlay.querySelector('.absolute').classList.remove('translate-y-full');
            }, 10);
        });
        
        mobileClose.addEventListener('click', function() {
            mobileOverlay.querySelector('.absolute').classList.add('translate-y-full');
            setTimeout(() => {
                mobileOverlay.classList.add('hidden');
            }, 300);
        });
        
        mobileOverlay.addEventListener('click', function(e) {
            if (e.target === mobileOverlay) {
                mobileClose.click();
            }
        });
    }
    
    // Gestion du téléchargement
    const downloadBtn = document.getElementById('downloadBtn');
    const mobileDownloadBtn = document.getElementById('mobileDownloadBtn');
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            // Télécharger le PDF signé
            const pdfUrl = '{{ $pdfUrl }}';
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = 'document_signe_{{ $document->id }}.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
    
    if (mobileDownloadBtn) {
        mobileDownloadBtn.addEventListener('click', function() {
            downloadBtn.click();
        });
    }
    
    // Gestion du plein écran
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const mobileFullscreenBtn = document.getElementById('mobileFullscreenBtn');
    
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }
    
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', toggleFullscreen);
    }
    
    if (mobileFullscreenBtn) {
        mobileFullscreenBtn.addEventListener('click', toggleFullscreen);
    }
});
</script>
