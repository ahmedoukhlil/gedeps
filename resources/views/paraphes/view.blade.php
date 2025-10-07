@extends('layouts.app')

@section('title', 'Voir Document Paraphé')

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
                                Document Paraphé
                            </h1>
                            <p class="card-subtitle">{{ $document->filename_original }}</p>
                        </div>
                        <div class="header-badge">
                            <span class="status-modern status-success">
                                <i class="fas fa-check-circle"></i>
                                Paraphé
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informations du paraphe -->
                    <div class="paraphe-details">
                        <div class="detail-item">
                            <label>Paraphé par :</label>
                            <span>{{ $latestParaphe->parapher->name }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Date de paraphe :</label>
                            <span>{{ $latestParaphe->paraphed_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="detail-item">
                            <label>Type de paraphe :</label>
                            <span>{{ ucfirst($latestParaphe->paraphe_type) }}</span>
                        </div>
                        @if($latestParaphe->paraphe_comment)
                            <div class="detail-item">
                                <label>Commentaire :</label>
                                <span>{{ $latestParaphe->paraphe_comment }}</span>
                            </div>
                        @endif
                    </div>
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
                        PDF Paraphé
                    </div>
                </div>
                
                <div class="pdf-container">
                    <div id="pdfViewer" class="pdf-viewer">
                        <iframe src="{{ $pdfUrl }}" 
                                width="100%" 
                                height="600px" 
                                style="border: none; border-radius: 8px;">
                        </iframe>
                    </div>
                </div>
                
                <div class="pdf-footer">
                    <div class="pdf-actions">
                        <a href="{{ route('paraphes.download', $document) }}" 
                           class="btn-modern btn-modern-success">
                            <i class="fas fa-download"></i>
                            <span>Télécharger PDF</span>
                        </a>
                        
                        <a href="{{ route('paraphes.certificate', $document) }}" 
                           class="btn-modern btn-modern-info">
                            <i class="fas fa-certificate"></i>
                            <span>Certificat</span>
                        </a>
                        
                        <a href="{{ route('paraphes.index') }}" 
                           class="btn-modern btn-modern-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques à la visualisation */
.paraphe-details {
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

.pdf-footer {
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.pdf-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .paraphe-details {
        grid-template-columns: 1fr;
    }
    
    .pdf-header {
        flex-direction: column;
        gap: 16px;
    }
    
    .pdf-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>
@endsection
