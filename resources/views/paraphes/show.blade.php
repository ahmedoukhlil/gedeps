@extends('layouts.app')

@section('title', 'Parapher Document')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- En-tête du document -->
            <div class="modern-card">
                <div class="modern-header">
                    <div class="header-content">
                        <div class="header-title">
                            <h1 class="card-title">
                                <i class="fas fa-pen-nib"></i>
                                Parapher le Document
                            </h1>
                            <p class="card-subtitle">{{ $document->filename_original }}</p>
                        </div>
                        <div class="header-badge">
                            <span class="status-modern status-warning">
                                <i class="fas fa-clock"></i>
                                En Attente de Paraphe
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informations du document -->
                    <div class="document-details">
                        <div class="detail-item">
                            <label>Type de document :</label>
                            <span>{{ $document->type_name }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Description :</label>
                            <span>{{ $document->description }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Uploadé par :</label>
                            <span>{{ $document->uploader->name }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Date d'upload :</label>
                            <span>{{ $document->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone de paraphe -->
            <div class="modern-card">
                <div class="modern-header">
                    <div class="header-content">
                        <div class="header-title">
                            <h2 class="card-title">
                                <i class="fas fa-pen-nib"></i>
                                Zone de Paraphe
                            </h2>
                            <p class="card-subtitle">Paraphez le document avec votre paraphe électronique</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Outils de paraphe -->
                    <div class="paraphe-tools">
                        <div class="tool-group">
                            <h4>Type de Paraphe</h4>
                            <div class="tool-options">
                                <label class="tool-option">
                                    <input type="radio" name="paraphe_type" value="png" checked>
                                    <span class="option-content">
                                        <i class="fas fa-image"></i>
                                        <span>Paraphe PNG</span>
                                        <small>Utiliser votre paraphe pré-enregistré</small>
                                    </span>
                                </label>
                                
                                <label class="tool-option">
                                    <input type="radio" name="paraphe_type" value="live">
                                    <span class="option-content">
                                        <i class="fas fa-pen"></i>
                                        <span>Paraphe Live</span>
                                        <small>Créer un paraphe en temps réel</small>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="tool-group">
                            <h4>Actions</h4>
                            <div class="tool-buttons">
                                <button type="button" id="addParapheBtn" class="btn-modern btn-modern-primary">
                                    <i class="fas fa-pen-nib"></i>
                                    <span>Ajouter Paraphe</span>
                                </button>
                                
                                <button type="button" id="clearParaphesBtn" class="btn-modern btn-modern-secondary">
                                    <i class="fas fa-trash"></i>
                                    <span>Effacer</span>
                                </button>
                                
                                <button type="button" id="previewBtn" class="btn-modern btn-modern-info">
                                    <i class="fas fa-eye"></i>
                                    <span>Aperçu</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de paraphe live -->
                    <div id="liveParapheArea" class="live-paraphe-area" style="display: none;">
                        <div class="paraphe-canvas-container">
                            <canvas id="parapheCanvas" width="400" height="200"></canvas>
                        </div>
                        <div class="paraphe-controls">
                            <button type="button" id="clearCanvasBtn" class="btn-modern btn-modern-secondary btn-sm">
                                <i class="fas fa-eraser"></i>
                                <span>Effacer</span>
                            </button>
                            <button type="button" id="saveParapheBtn" class="btn-modern btn-modern-success btn-sm">
                                <i class="fas fa-save"></i>
                                <span>Sauvegarder</span>
                            </button>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="paraphe-instructions">
                        <h4>Instructions de Paraphe</h4>
                        <div class="instruction-list">
                            <div class="instruction-item">
                                <i class="fas fa-mouse-pointer"></i>
                                <span>Cliquez sur "Ajouter Paraphe" pour placer votre paraphe</span>
                            </div>
                            <div class="instruction-item">
                                <i class="fas fa-arrows-alt"></i>
                                <span>Glissez-déposez pour repositionner le paraphe</span>
                            </div>
                            <div class="instruction-item">
                                <i class="fas fa-pen"></i>
                                <span>Utilisez le paraphe live pour créer un paraphe personnalisé</span>
                            </div>
                            <div class="instruction-item">
                                <i class="fas fa-save"></i>
                                <span>Cliquez sur "Parapher" pour finaliser le document</span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de paraphe -->
                    <form id="parapheForm" action="{{ route('paraphes.store', $document) }}" method="POST">
                        @csrf
                        
                        <!-- Commentaire -->
                        <div class="form-group-modern">
                            <label for="paraphe_comment" class="form-label-modern">Commentaire (optionnel)</label>
                            <textarea id="paraphe_comment" 
                                      name="paraphe_comment" 
                                      class="form-control-modern" 
                                      rows="3" 
                                      placeholder="Ajoutez un commentaire pour ce paraphe..."></textarea>
                        </div>

                        <!-- Champs cachés -->
                        <input type="hidden" name="paraphe_type" id="paraphe_type" value="png">
                        <input type="hidden" name="live_paraphe_data" id="live_paraphe_data">
                        <input type="hidden" name="paraphe_x" id="paraphe_x">
                        <input type="hidden" name="paraphe_y" id="paraphe_y">
                        <input type="hidden" name="paraphe_positions" id="paraphe_positions">
                        <input type="hidden" name="is_multi_page" id="is_multi_page" value="false">
                        <input type="hidden" name="total_pages" id="total_pages" value="1">

                        <!-- Boutons d'action -->
                        <div class="form-actions">
                            <a href="{{ route('paraphes.index') }}" class="btn-modern btn-modern-secondary">
                                <i class="fas fa-arrow-left"></i>
                                <span>Retour</span>
                            </a>
                            
                            <button type="submit" id="submitParapheBtn" class="btn-modern btn-modern-success">
                                <i class="fas fa-pen-nib"></i>
                                <span>Parapher le Document</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Zone d'affichage PDF -->
            <div class="modern-card">
                <div class="pdf-header">
                    <div class="pdf-controls">
                        <button type="button" id="zoomInBtn" class="btn-modern btn-modern-info btn-sm">
                            <i class="fas fa-search-plus"></i>
                        </button>
                        <button type="button" id="zoomOutBtn" class="btn-modern btn-modern-info btn-sm">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <button type="button" id="resetZoomBtn" class="btn-modern btn-modern-info btn-sm">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </button>
                    </div>
                    <div class="pdf-title">
                        <i class="fas fa-file-pdf"></i>
                        Aperçu du Document
                    </div>
                </div>
                
                <div class="pdf-container">
                    <div id="pdfViewer" class="pdf-viewer">
                        <div class="pdf-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Chargement du PDF...</span>
                        </div>
                    </div>
                </div>
                
                <div class="pdf-footer">
                    <div class="pdf-info">
                        <span id="pageInfo">Page 1 sur 1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="{{ asset('js/pdf-overlay-paraphe-module.js') }}"></script>

<style>
/* Styles spécifiques au paraphe */
.paraphe-tools {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.tool-group {
    margin-bottom: 24px;
}

.tool-group h4 {
    margin: 0 0 16px 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.tool-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.tool-option {
    display: block;
    cursor: pointer;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.3s ease;
    background: white;
}

.tool-option:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.tool-option input[type="radio"] {
    display: none;
}

.tool-option input[type="radio"]:checked + .option-content {
    color: #667eea;
}

.option-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
}

.option-content i {
    font-size: 2rem;
    color: #6c757d;
}

.option-content span {
    font-weight: 600;
    color: #2c3e50;
}

.option-content small {
    color: #6c757d;
    font-size: 0.85rem;
}

.tool-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.live-paraphe-area {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 2px dashed #dee2e6;
}

.paraphe-canvas-container {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

#parapheCanvas {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: crosshair;
    background: white;
}

.paraphe-controls {
    display: flex;
    justify-content: center;
    gap: 12px;
}

.paraphe-instructions {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.paraphe-instructions h4 {
    margin: 0 0 16px 0;
    font-size: 1.2rem;
    font-weight: 600;
}

.instruction-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.instruction-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.instruction-item i {
    font-size: 1.2rem;
    color: #ffd700;
}

.document-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-item label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item span {
    color: #2c3e50;
    font-size: 1rem;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e9ecef;
}

.pdf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.pdf-controls {
    display: flex;
    gap: 8px;
}

.pdf-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.pdf-container {
    padding: 24px;
    background: #f8f9fa;
}

.pdf-viewer {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    min-height: 600px;
    position: relative;
    overflow: hidden;
}

.pdf-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 600px;
    color: #6c757d;
    gap: 16px;
}

.pdf-loading i {
    font-size: 2rem;
}

.pdf-footer {
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.pdf-info {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .tool-options {
        grid-template-columns: 1fr;
    }
    
    .tool-buttons {
        flex-direction: column;
    }
    
    .instruction-list {
        grid-template-columns: 1fr;
    }
    
    .document-details {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .pdf-header {
        flex-direction: column;
        gap: 16px;
    }
}
</style>

<script>
// Configuration PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Initialiser le module de paraphe
document.addEventListener('DOMContentLoaded', function() {
    const config = {
        pdfUrl: '{{ Storage::url($document->path_original) }}',
        containerId: 'pdfViewer',
        canvasId: 'parapheCanvas',
        addParapheBtnId: 'addParapheBtn',
        clearParaphesBtnId: 'clearParaphesBtn',
        previewBtnId: 'previewBtn',
        clearCanvasBtnId: 'clearCanvasBtn',
        saveParapheBtnId: 'saveParapheBtn',
        parapheTypeInputId: 'paraphe_type',
        liveParapheDataInputId: 'live_paraphe_data',
        parapheXInputId: 'paraphe_x',
        parapheYInputId: 'paraphe_y',
        paraphePositionsInputId: 'paraphe_positions',
        isMultiPageInputId: 'is_multi_page',
        totalPagesInputId: 'total_pages',
        submitBtnId: 'submitParapheBtn',
        formId: 'parapheForm',
        liveParapheAreaId: 'liveParapheArea',
        zoomInBtnId: 'zoomInBtn',
        zoomOutBtnId: 'zoomOutBtn',
        resetZoomBtnId: 'resetZoomBtn',
        pageInfoId: 'pageInfo'
    };

    const parapheModule = new PDFOverlayParapheModule(config);
    parapheModule.init();
});
</script>
@endsection
