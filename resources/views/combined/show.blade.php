@extends('layouts.app')

@section('title', 'Signature & Paraphe')

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
                                <i class="fas fa-pen-fancy"></i>
                                <i class="fas fa-pen-nib"></i>
                                Signature & Paraphe
                            </h1>
                            <p class="card-subtitle">{{ $document->filename_original }}</p>
                        </div>
                        <div class="header-badge">
                            <span class="status-modern status-warning">
                                <i class="fas fa-clock"></i>
                                En Attente de Traitement
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

            <!-- Zone d'actions -->
            <div class="modern-card">
                <div class="modern-header">
                    <div class="header-content">
                        <div class="header-title">
                            <h2 class="card-title">
                                <i class="fas fa-cogs"></i>
                                Actions Disponibles
                            </h2>
                            <p class="card-subtitle">Choisissez les actions à effectuer sur ce document</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Sélection du type d'action -->
                    <div class="action-selection">
                        <h4>Type d'Action</h4>
                        <div class="action-options">
                            <label class="action-option">
                                <input type="radio" name="action_type" value="sign_only" checked>
                                <span class="option-content">
                                    <i class="fas fa-pen-fancy"></i>
                                    <span>Signature uniquement</span>
                                    <small>Apposer seulement une signature</small>
                                </span>
                            </label>
                            
                            <label class="action-option">
                                <input type="radio" name="action_type" value="paraphe_only">
                                <span class="option-content">
                                    <i class="fas fa-pen-nib"></i>
                                    <span>Paraphe uniquement</span>
                                    <small>Apposer seulement un paraphe</small>
                                </span>
                            </label>
                            
                            <label class="action-option">
                                <input type="radio" name="action_type" value="both">
                                <span class="option-content">
                                    <i class="fas fa-pen-fancy"></i>
                                    <i class="fas fa-pen-nib"></i>
                                    <span>Signature & Paraphe</span>
                                    <small>Apposer les deux sur le document</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Configuration de la signature -->
                    <div id="signatureConfig" class="config-section">
                        <h4>Configuration Signature</h4>
                        <div class="config-grid">
                            <div class="config-group">
                                <label class="form-label-modern">Type de Signature</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="signature_type" value="png" checked>
                                        <span>PNG</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="signature_type" value="live">
                                        <span>Live</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="config-group">
                                <label for="signature_comment" class="form-label-modern">Commentaire Signature</label>
                                <textarea id="signature_comment" 
                                          name="signature_comment" 
                                          class="form-control-modern" 
                                          rows="2" 
                                          placeholder="Commentaire pour la signature..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration du paraphe -->
                    <div id="parapheConfig" class="config-section" style="display: none;">
                        <h4>Configuration Paraphe</h4>
                        <div class="config-grid">
                            <div class="config-group">
                                <label class="form-label-modern">Type de Paraphe</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="paraphe_type" value="png" checked>
                                        <span>PNG</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="paraphe_type" value="live">
                                        <span>Live</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="config-group">
                                <label for="paraphe_comment" class="form-label-modern">Commentaire Paraphe</label>
                                <textarea id="paraphe_comment" 
                                          name="paraphe_comment" 
                                          class="form-control-modern" 
                                          rows="2" 
                                          placeholder="Commentaire pour le paraphe..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de paraphe live -->
                    <div id="liveParapheArea" class="live-area" style="display: none;">
                        <h4>Zone de Paraphe Live</h4>
                        <div class="canvas-container">
                            <canvas id="parapheCanvas" width="400" height="200"></canvas>
                        </div>
                        <div class="canvas-controls">
                            <button type="button" id="clearParapheCanvasBtn" class="btn-modern btn-modern-secondary btn-sm">
                                <i class="fas fa-eraser"></i>
                                <span>Effacer</span>
                            </button>
                            <button type="button" id="saveParapheBtn" class="btn-modern btn-modern-success btn-sm">
                                <i class="fas fa-save"></i>
                                <span>Sauvegarder</span>
                            </button>
                        </div>
                    </div>

                    <!-- Zone de signature live -->
                    <div id="liveSignatureArea" class="live-area" style="display: none;">
                        <h4>Zone de Signature Live</h4>
                        <div class="canvas-container">
                            <canvas id="signatureCanvas" width="400" height="200"></canvas>
                        </div>
                        <div class="canvas-controls">
                            <button type="button" id="clearSignatureCanvasBtn" class="btn-modern btn-modern-secondary btn-sm">
                                <i class="fas fa-eraser"></i>
                                <span>Effacer</span>
                            </button>
                            <button type="button" id="saveSignatureBtn" class="btn-modern btn-modern-success btn-sm">
                                <i class="fas fa-save"></i>
                                <span>Sauvegarder</span>
                            </button>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="instructions">
                        <h4>Instructions</h4>
                        <div class="instruction-list">
                            <div class="instruction-item">
                                <i class="fas fa-mouse-pointer"></i>
                                <span>Sélectionnez le type d'action souhaité</span>
                            </div>
                            <div class="instruction-item">
                                <i class="fas fa-pen"></i>
                                <span>Configurez les paramètres selon vos besoins</span>
                            </div>
                            <div class="instruction-item">
                                <i class="fas fa-eye"></i>
                                <span>Utilisez l'aperçu pour positionner les éléments</span>
                            </div>
                            <div class="instruction-item">
                                <i class="fas fa-check"></i>
                                <span>Validez pour finaliser le traitement</span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <form id="combinedForm" action="{{ route('combined.store', $document) }}" method="POST">
                        @csrf
                        
                        <!-- Champs cachés -->
                        <input type="hidden" name="action_type" id="action_type" value="sign_only">
                        <input type="hidden" name="signature_type" id="signature_type" value="png">
                        <input type="hidden" name="paraphe_type" id="paraphe_type" value="png">
                        <input type="hidden" name="live_signature_data" id="live_signature_data">
                        <input type="hidden" name="live_paraphe_data" id="live_paraphe_data">
                        <input type="hidden" name="signature_x" id="signature_x">
                        <input type="hidden" name="signature_y" id="signature_y">
                        <input type="hidden" name="paraphe_x" id="paraphe_x">
                        <input type="hidden" name="paraphe_y" id="paraphe_y">

                        <!-- Boutons d'action -->
                        <div class="form-actions">
                            <a href="{{ route('documents.pending') }}" class="btn-modern btn-modern-secondary">
                                <i class="fas fa-arrow-left"></i>
                                <span>Retour</span>
                            </a>
                            
                            <button type="submit" id="submitBtn" class="btn-modern btn-modern-success">
                                <i class="fas fa-check"></i>
                                <span>Traiter le Document</span>
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
<script src="{{ asset('js/pdf-overlay-combined-module.js') }}"></script>

<style>
/* Styles spécifiques à l'interface combinée */
.action-selection {
    margin-bottom: 32px;
}

.action-selection h4 {
    margin: 0 0 16px 0;
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 600;
}

.action-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.action-option {
    display: block;
    cursor: pointer;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    background: white;
}

.action-option:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.action-option input[type="radio"] {
    display: none;
}

.action-option input[type="radio"]:checked + .option-content {
    color: #667eea;
}

.option-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
}

.option-content i {
    font-size: 2rem;
    color: #6c757d;
}

.option-content span {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.option-content small {
    color: #6c757d;
    font-size: 0.9rem;
}

.config-section {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.config-section h4 {
    margin: 0 0 16px 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.config-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

.config-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.radio-group {
    display: flex;
    gap: 16px;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.radio-option input[type="radio"] {
    margin: 0;
}

.live-area {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 2px dashed #dee2e6;
}

.live-area h4 {
    margin: 0 0 16px 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.canvas-container {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

#signatureCanvas, #parapheCanvas {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    cursor: crosshair;
    background: white;
}

.canvas-controls {
    display: flex;
    justify-content: center;
    gap: 12px;
}

.instructions {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
}

.instructions h4 {
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
    .action-options {
        grid-template-columns: 1fr;
    }
    
    .config-grid {
        grid-template-columns: 1fr;
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

// Initialiser le module combiné
document.addEventListener('DOMContentLoaded', function() {
    const config = {
        pdfUrl: '{{ Storage::url($document->path_original) }}',
        containerId: 'pdfViewer',
        signatureCanvasId: 'signatureCanvas',
        parapheCanvasId: 'parapheCanvas',
        combinedFormId: 'combinedForm',
        actionTypeInputId: 'action_type',
        signatureTypeInputId: 'signature_type',
        parapheTypeInputId: 'paraphe_type',
        liveSignatureDataInputId: 'live_signature_data',
        liveParapheDataInputId: 'live_paraphe_data',
        signatureXInputId: 'signature_x',
        signatureYInputId: 'signature_y',
        parapheXInputId: 'paraphe_x',
        parapheYInputId: 'paraphe_y',
        submitBtnId: 'submitBtn',
        zoomInBtnId: 'zoomInBtn',
        zoomOutBtnId: 'zoomOutBtn',
        resetZoomBtnId: 'resetZoomBtn',
        pageInfoId: 'pageInfo'
    };

    const combinedModule = new PDFOverlayCombinedModule(config);
    combinedModule.init();
});
</script>
@endsection
