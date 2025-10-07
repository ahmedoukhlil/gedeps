@extends('layouts.app')

@section('title', 'Signature de Document')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <div class="header-content">
                        <div class="header-title">
                            <h3 class="card-title">✍️ Signature de Document</h3>
                            <p class="card-subtitle">Interface moderne de signature PDF</p>
                        </div>
                        <div class="header-badge">
                            <span class="badge badge-info" id="documentInfo">Document #{{ $document->id }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Panneau de signature amélioré -->
                    <div class="signature-panel modern-panel">
                        <div class="panel-header">
                            <h4 class="panel-title">✍️ Outils de Signature</h4>
                            <div class="panel-actions">
                                <button class="btn btn-outline-secondary btn-sm" id="clearSignaturesBtn">
                                    <i class="fas fa-eraser"></i> Effacer
                                </button>
                                <button class="btn btn-outline-info btn-sm" id="helpBtn">
                                    <i class="fas fa-question-circle"></i> Aide
                                </button>
                            </div>
                        </div>
                        
                        <div class="signature-tools modern-tools">
                            <button id="signDocumentBtn" class="btn btn-primary btn-lg tool-btn">
                                <i class="fas fa-signature"></i>
                                <span>Signer le Document</span>
                                <small>Signature complète</small>
                            </button>
                            <button id="initialDocumentBtn" class="btn btn-info btn-lg tool-btn">
                                <i class="fas fa-pen"></i>
                                <span>Parapher le Document</span>
                                <small>Initiales sur toutes les pages</small>
                            </button>
                            <button id="savePdfBtn" class="btn btn-success btn-lg tool-btn" disabled>
                                <i class="fas fa-save"></i>
                                <span>Enregistrer PDF</span>
                                <small>Sauvegarder le document</small>
                            </button>
                        </div>

                        <!-- Instructions améliorées avec design moderne -->
                        <div class="signature-instructions modern-instructions">
                            <div class="instruction-header">
                                <h5><i class="fas fa-lightbulb"></i> Guide d'utilisation</h5>
                            </div>
                            <div class="instruction-grid">
                                <div class="instruction-item">
                                    <div class="instruction-icon">✍️</div>
                                    <div class="instruction-content">
                                        <strong>Signer</strong>
                                        <p>Signature complète sur la page 1 uniquement</p>
                                    </div>
                                </div>
                                <div class="instruction-item">
                                    <div class="instruction-icon">📝</div>
                                    <div class="instruction-content">
                                        <strong>Parapher</strong>
                                        <p>Initiales sur toutes les pages</p>
                                    </div>
                                </div>
                                <div class="instruction-item">
                                    <div class="instruction-icon">💾</div>
                                    <div class="instruction-content">
                                        <strong>Enregistrer</strong>
                                        <p>Sauvegarder le document final</p>
                                    </div>
                                </div>
                                <div class="instruction-item">
                                    <div class="instruction-icon">🖱️</div>
                                    <div class="instruction-content">
                                        <strong>Déplacer</strong>
                                        <p>Glissez-déposez pour repositionner</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statut amélioré avec indicateurs visuels -->
                        <div class="signature-status modern-status" id="signatureStatus">
                            <div class="status-content">
                                <div class="status-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="status-text">
                                    <span class="status-message">Aucune signature</span>
                                    <div class="status-progress" id="statusProgress" style="display: none;">
                                        <div class="progress-bar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zone d'affichage PDF avec overlay -->
                    <div class="pdf-display-area">
                        <div class="pdf-header">
                            <h5><i class="fas fa-file-pdf"></i> Aperçu du Document</h5>
                            <div class="pdf-controls">
                                <button class="btn btn-outline-secondary btn-sm" id="zoomInBtn">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="zoomOutBtn">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="resetZoomBtn">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div id="pdfContainer" style="border: 2px solid #dee2e6; border-radius: 8px; background: #f8f9fa; min-height: 600px; position: relative; margin: 0 auto;">
                            <div class="loading-placeholder" style="display: flex; align-items: center; justify-content: center; height: 600px; color: #6c757d;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Chargement...</span>
                                    </div>
                                    <p class="mt-2">Chargement du document PDF...</p>
                                </div>
                            </div>
                        </div>
                        <div class="pdf-footer">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Glissez-déposez les signatures pour les repositionner
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Indicateur de raccourcis clavier -->
<div class="keyboard-shortcuts" id="keyboardShortcuts">
    <div style="margin-bottom: 8px; font-weight: 600;">
        <i class="fas fa-keyboard"></i> Raccourcis clavier
    </div>
    <div style="display: grid; gap: 4px; font-size: 0.8rem;">
        <div style="display: flex; justify-content: space-between;">
            <span>Signer</span>
            <kbd>Ctrl</kbd> + <kbd>1</kbd>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Parapher</span>
            <kbd>Ctrl</kbd> + <kbd>2</kbd>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Sauvegarder</span>
            <kbd>Ctrl</kbd> + <kbd>S</kbd>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Effacer</span>
            <kbd>Ctrl</kbd> + <kbd>E</kbd>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Aide</span>
            <kbd>F1</kbd>
        </div>
    </div>
</div>

<!-- Configuration JavaScript -->
<script>
    window.documentConfig = {
        documentId: {{ $document->id }},
        pdfUrl: '{{ route("documents.view", $document->id) }}',
        csrfToken: '{{ csrf_token() }}',
        userSignature: null
    };
</script>

<!-- Charger les bibliothèques -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>

<!-- Charger le module de signature overlay -->
<script src="{{ asset('js/pdf-overlay-signature-module.js') }}"></script>

<style>
/* Design moderne et amélioré */
.modern-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px;
    border: none;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.header-title .card-title {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-subtitle {
    margin: 4px 0 0 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.header-badge .badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

.modern-panel {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.panel-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
}

.panel-actions {
    display: flex;
    gap: 8px;
}

.modern-tools {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.tool-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border-radius: 12px;
    border: 2px solid transparent;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    min-height: 120px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.tool-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.tool-btn:hover::before {
    opacity: 1;
}

.tool-btn i {
    font-size: 2rem;
    margin-bottom: 8px;
    transition: transform 0.3s ease;
}

.tool-btn span {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 4px;
    color: inherit;
}

.tool-btn small {
    font-size: 0.85rem;
    opacity: 0.7;
    color: inherit;
}

.tool-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    border-color: rgba(0, 123, 255, 0.3);
}

.tool-btn:hover i {
    transform: scale(1.1);
}

.tool-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.modern-instructions {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%);
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #28a745;
    margin-bottom: 20px;
}

.instruction-header {
    margin-bottom: 16px;
}

.instruction-header h5 {
    margin: 0;
    color: #28a745;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.instruction-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.instruction-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    transition: transform 0.2s ease;
}

.instruction-item:hover {
    transform: translateY(-2px);
}

.instruction-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.instruction-content strong {
    display: block;
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 4px;
}

.instruction-content p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
}

.modern-status {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    padding: 16px;
    border-left: 4px solid #007bff;
    transition: all 0.3s ease;
}

.status-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-icon {
    font-size: 1.2rem;
    color: #007bff;
}

.status-text {
    flex: 1;
}

.status-message {
    font-weight: 500;
    color: #495057;
}

.status-progress {
    margin-top: 8px;
    height: 4px;
    background: rgba(0, 123, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #0056b3);
    border-radius: 2px;
    transition: width 0.3s ease;
    width: 0%;
}

.status-success {
    color: #28a745;
    border-left-color: #28a745;
}

.status-error {
    color: #dc3545;
    border-left-color: #dc3545;
}

.status-info {
    color: #007bff;
    border-left-color: #007bff;
}

.status-warning {
    color: #ffc107;
    border-left-color: #ffc107;
}

.pdf-display-area {
    margin-top: 24px;
}

.pdf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.pdf-header h5 {
    margin: 0;
    color: #495057;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pdf-controls {
    display: flex;
    gap: 8px;
}

.pdf-controls .btn {
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.pdf-controls .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.pdf-footer {
    margin-top: 12px;
    padding: 8px 16px;
    background: rgba(0, 123, 255, 0.05);
    border-radius: 6px;
    border-left: 3px solid #007bff;
    text-align: center;
}

.pdf-footer small {
    color: #6c757d;
    font-weight: 500;
}

#pdfContainer {
    border: 2px solid #dee2e6;
    border-radius: 12px;
    background: #f8f9fa;
    min-height: 600px;
    position: relative;
    margin: 0 auto;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

#pdfContainer:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.loading-placeholder {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 600px;
    color: #6c757d;
}

/* Styles pour les signatures draggables améliorées */
.signature-element {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    border: 2px solid #007bff;
    border-radius: 8px;
    background: rgba(0, 123, 255, 0.05);
    backdrop-filter: blur(10px);
}

.signature-element:hover {
    box-shadow: 0 8px 24px rgba(0, 123, 255, 0.3);
    transform: scale(1.02);
    border-color: #0056b3;
}

.signature-element:active {
    transform: scale(1.05);
    box-shadow: 0 12px 32px rgba(0, 123, 255, 0.4);
}

/* Animations améliorées */
@keyframes signatureAppear {
    from {
        opacity: 0;
        transform: scale(0.8) rotate(-5deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

.signature-element {
    animation: signatureAppear 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Indicateur de position amélioré */
.signature-element::after {
    content: '';
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border: 2px dashed #007bff;
    border-radius: 8px;
    opacity: 0;
    transition: all 0.3s ease;
    pointer-events: none;
}

.signature-element:hover::after {
    opacity: 0.6;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Responsive amélioré */
@media (max-width: 768px) {
    .modern-tools {
        grid-template-columns: 1fr;
    }
    
    .tool-btn {
        min-height: 100px;
        padding: 16px;
    }
    
    .instruction-grid {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    #pdfContainer {
        max-width: 100%;
        overflow-x: auto;
        min-height: 400px;
    }
}

@media (max-width: 480px) {
    .modern-panel {
        padding: 16px;
    }
    
    .tool-btn {
        padding: 12px;
        min-height: 80px;
    }
    
    .tool-btn i {
        font-size: 1.5rem;
    }
    
    .tool-btn span {
        font-size: 1rem;
    }
}

/* Toast notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    padding: 16px;
    margin-bottom: 12px;
    border-left: 4px solid #007bff;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 350px;
}

.toast.show {
    transform: translateX(0);
}

.toast.success {
    border-left-color: #28a745;
}

.toast.error {
    border-left-color: #dc3545;
}

.toast.warning {
    border-left-color: #ffc107;
}

/* Raccourcis clavier */
.keyboard-shortcuts {
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 12px;
    border-radius: 8px;
    font-size: 0.85rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1000;
}

.keyboard-shortcuts.show {
    opacity: 1;
}

.keyboard-shortcuts kbd {
    background: #333;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8rem;
    margin: 0 2px;
}
</style>
@endsection
