@extends('layouts.app')

@section('title', 'Signer le Document')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">📄 Signature de Document</h1>
            <p class="text-gray-600">Signez le document <strong>{{ $document->title }}</strong></p>
            
            <!-- Informations du document -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                    <span class="font-medium text-gray-700">Nom du fichier:</span>
                    <span class="text-gray-900">{{ $document->filename_original }}</span>
                    </div>
                    <div>
                    <span class="font-medium text-gray-700">Type:</span>
                    <span class="text-gray-900">{{ ucfirst($document->type) }}</span>
                    </div>
                    <div>
                    <span class="font-medium text-gray-700">Uploadé par:</span>
                    <span class="text-gray-900">{{ $document->uploader->name }}</span>
                    </div>
                    <div>
                    <span class="font-medium text-gray-700">Date d'upload:</span>
                    <span class="text-gray-900">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($document->description)
                <div class="mt-4">
                    <span class="font-medium text-gray-700">Description:</span>
                    <p class="text-gray-900 mt-1">{{ $document->description }}</p>
                </div>
            @endif
            
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-semibold text-blue-800 mb-2">📋 Instructions d'utilisation - Documents Multi-Pages</h3>
                <ol class="list-decimal list-inside text-blue-700 space-y-1">
                    <li>Le PDF se charge automatiquement avec toutes ses pages</li>
                    <li>Utilisez les flèches ◀ ▶ pour naviguer entre les pages</li>
                    <li>Glissez l'image de signature sur chaque page à l'emplacement souhaité</li>
                    <li>Chaque page peut être signée ou paraphée individuellement</li>
                    <li>Le statut des signatures s'affiche en temps réel</li>
                    <li>Cliquez sur "Enregistrer PDF Signé" pour sauvegarder le document final</li>
                </ol>
            </div>
                </div>
                
        <!-- Module de Signature PDF avec Fabric.js -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div id="pdf-signature-container">
                <div class="signature-module">
                    <!-- En-tête du module -->
                    <div class="module-header">
                        <h3>📄 Signature PDF Multi-Pages</h3>
                        <div class="module-controls">
                            <div class="page-navigation">
                                <button id="prevPageBtn" class="btn btn-sm" disabled>◀</button>
                                <span id="pageInfo">Page 1 sur 1</span>
                                <button id="nextPageBtn" class="btn btn-sm" disabled>▶</button>
                        </div>
                            <div class="zoom-controls">
                                <button id="zoomOutBtn" class="btn btn-sm">🔍-</button>
                                <span id="zoomLevel">100%</span>
                                <button id="zoomInBtn" class="btn btn-sm">🔍+</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Zone PDF avec Fabric.js -->
                    <div class="pdf-container">
                        <div id="pdfViewer" class="pdf-viewer">
                            <canvas id="pdfCanvas" class="pdf-canvas"></canvas>
                            <canvas id="fabricCanvas" class="fabric-canvas"></canvas>
                        </div>
                    </div>
                    
                    <!-- Panneau de signatures -->
                    <div class="signature-panel">
                        <h4>✍️ Signatures disponibles</h4>
                        <div class="signature-tools">
                            <button id="loadSignatureBtn" class="btn btn-primary">🔄 Charger Signature</button>
                            <button id="clearSignaturesBtn" class="btn btn-warning">🗑️ Effacer</button>
                            <button id="savePdfBtn" class="btn btn-success" disabled>💾 Enregistrer PDF</button>
                        </div>
                        <div class="signature-status" id="signatureStatus">
                            <span class="status-text">Aucune signature</span>
                        </div>
                    </div>
                    
                    <!-- Messages de statut -->
                    <div id="statusMessage" class="status-message" style="display: none;"></div>
                        </div>
                    </div>
                </div>

<!-- Meta pour l'utilisateur -->
<meta name="user-name" content="{{ auth()->user()->name }}">
<meta name="document-id" content="{{ $document->id }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Styles pour le module de signature multi-pages -->
<style>
.signature-status {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    margin-top: 8px;
    transition: all 0.3s ease;
}

.signature-status.none {
    background-color: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.signature-status.partial {
    background-color: #fef3c7;
    color: #d97706;
    border: 1px solid #fed7aa;
}

.signature-status.complete {
    background-color: #d1fae5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.page-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.page-info button {
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    background-color: #f9fafb;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.page-info button:hover:not(:disabled) {
    background-color: #e5e7eb;
}

.page-info button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.module-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Styles pour le module Fabric.js */
.signature-module {
    max-width: 100%;
    margin: 0 auto;
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.module-controls {
    display: flex;
    gap: 20px;
    align-items: center;
}

.page-navigation, .zoom-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pdf-container {
    position: relative;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
    margin-bottom: 20px;
}

.pdf-viewer {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 600px;
}

.pdf-canvas, .fabric-canvas {
    position: absolute;
    top: 0;
    left: 0;
    border: 1px solid #ccc;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.fabric-canvas {
    pointer-events: auto;
    z-index: 10;
}

.signature-panel {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.signature-tools {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    flex-wrap: wrap;
}

.signature-status {
    padding: 10px;
    border-radius: 5px;
    font-weight: 500;
    margin-top: 10px;
}

.status-message {
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
    font-weight: 500;
}

.status-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-message.info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>

<!-- Scripts pour le module de signature avec Fabric.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

<!-- Script du module Fabric.js -->
<script src="/js/fabric-signature-module.js"></script>

@endsection
        
        if (!container) {
            console.error('❌ Container pdf-signature-container non trouvé !');
            return;
        }
        
        console.log('✅ Container trouvé:', container);
        
        // Configuration
        const pdfUrl = '/documents/view/{{ $document->id }}';
        const signatureUrl = '/storage/signatures/signature_{{ auth()->user()->id }}_*.png';
        const backendUrl = '/signatures/save-signed-pdf';
        const documentId = {{ $document->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        console.log('📋 Configuration:', { pdfUrl, signatureUrl, backendUrl, documentId, csrfToken });
        
        // Interface HTML
        container.innerHTML = `
            <div class="pdf-signature-module">
                <div class="module-header">
                    <h3>📄 Signature PDF - Glisser-Déposer</h3>
                    <div class="module-controls">
                        <span class="pdf-status">📄 PDF chargé automatiquement</span>
                        <div class="zoom-controls">
                            <button id="zoomOutBtn" class="btn btn-sm" disabled>🔍-</button>
                            <span id="zoomLevel" class="zoom-info">100%</span>
                            <button id="zoomInBtn" class="btn btn-sm" disabled>🔍+</button>
                        </div>
                        <div class="page-info">
                            Page <span id="currentPageSpan">1</span> sur <span id="totalPagesSpan">0</span>
                            <button id="prevPageBtn" class="btn btn-sm" disabled>◀</button>
                            <button id="nextPageBtn" class="btn btn-sm" disabled>▶</button>
                        </div>
                        <div class="signature-status" id="signatureStatus">
                            <span class="status-text">Aucune signature</span>
                        </div>
                        <button id="debugBtn" class="btn btn-sm" style="margin-top: 8px;">🔍 Debug Signatures</button>
                    </div>
                </div>

                <div class="module-content">
                    <div class="pdf-section">
                        <div class="pdf-canvas-container">
                            <div class="loading" id="loadingIndicator" style="display: none;">
                                <div class="spinner"></div>
                                <p>Chargement du PDF...</p>
                            </div>
                            <canvas id="pdfCanvas" class="pdf-canvas" style="display: none;"></canvas>
                        </div>
                    </div>
                    
                    <div class="signature-section">
                        <h4>✍️ Signatures disponibles</h4>
                        <div class="signature-item" id="signatureItem" draggable="true">
                            <img id="signatureImage" src="" alt="Signature" class="signature-img">
                            <div class="signature-label">Glissez-moi sur le PDF</div>
                        </div>
                        <button id="reloadSignatureBtn" class="btn btn-sm" style="width: 100%; margin-top: 10px;">🔄 Recharger Signature</button>
                        <button id="testSignatureBtn" class="btn btn-sm" style="width: 100%; margin-top: 5px; background: #ffc107; color: #000;">🧪 Test Signature</button>
                        <button id="testCoordinatesBtn" class="btn btn-sm" style="width: 100%; margin-top: 5px; background: #17a2b8; color: #fff;">📐 Test Coordonnées</button>
                        
                        <div class="download-section">
                            <button id="saveBtn" class="btn btn-success" disabled>
                                💾 Enregistrer PDF Signé
                                </button>
                            <div id="statusMessage" class="status-message" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Styles CSS intégrés
        const style = document.createElement('style');
        style.textContent = `
            .pdf-signature-module {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f8f9fa;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            
            .module-header {
                background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
                color: white;
                padding: 15px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            
            .module-header h3 {
                margin: 0;
                font-size: 18px;
            }
            
            .module-controls {
                display: flex;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
            }
            
            .pdf-status {
                background: rgba(255,255,255,0.2);
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 12px;
            }
            
            .zoom-controls {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .btn {
                background: #007bff;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.3s ease;
            }
            
            .btn:hover:not(:disabled) {
                background: #0056b3;
                transform: translateY(-1px);
            }
            
            .btn:disabled {
                background: #6c757d;
                cursor: not-allowed;
            }
            
            .btn-sm {
                padding: 4px 8px;
                font-size: 11px;
            }
            
            .btn-success {
                background: #28a745;
            }
            
            .btn-success:hover:not(:disabled) {
                background: #218838;
            }
            
            .zoom-info {
                background: rgba(255,255,255,0.2);
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                min-width: 40px;
                text-align: center;
            }
            
            .page-info {
                background: rgba(255,255,255,0.2);
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
            }
            
            .module-content {
                display: flex;
                min-height: 500px;
            }
            
            .pdf-section {
                flex: 2;
                padding: 20px;
                background: #ffffff;
            }
            
            .pdf-canvas-container {
                position: relative;
                border: 2px dashed #dee2e6;
                border-radius: 10px;
                background: white;
                min-height: 400px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                background-image: 
                    linear-gradient(45deg, #f8f9fa 25%, transparent 25%), 
                    linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), 
                    linear-gradient(45deg, transparent 75%, #f8f9fa 75%), 
                    linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
                background-size: 20px 20px;
                background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            }
            
            .pdf-canvas {
                max-width: 100%;
                max-height: 100%;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            
            .signature-canvas {
                position: absolute;
                top: 0;
                left: 0;
                pointer-events: none;
                z-index: 10;
                border-radius: 8px;
            }
            
            .signature-section {
                flex: 1;
                padding: 20px;
                background: #ffffff;
                border-left: 1px solid #dee2e6;
            }
            
            .signature-section h4 {
                color: #2c3e50;
                margin-bottom: 15px;
                font-size: 16px;
            }
            
            .signature-item {
                background: #f8f9fa;
                border: 2px dashed #6c757d;
                border-radius: 10px;
                padding: 15px;
                text-align: center;
                cursor: grab;
                transition: all 0.3s ease;
                margin-bottom: 15px;
            }
            
            .signature-item:hover {
                border-color: #007bff;
                background: #e3f2fd;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,123,255,0.2);
            }
            
            .signature-item:active {
                cursor: grabbing;
                transform: scale(0.95);
            }
            
            .signature-img {
                max-width: 150px;
                max-height: 75px;
                border-radius: 5px;
                margin-bottom: 8px;
            }
            
            .signature-label {
                color: #6c757d;
                font-size: 12px;
                font-weight: 500;
            }
            
            .download-section {
                margin-top: 20px;
            }
            
            .status-message {
                padding: 10px;
                border-radius: 5px;
                margin-top: 10px;
                font-size: 14px;
                text-align: center;
            }
            
            .status-message.success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            
            .status-message.error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            
            .status-message.info {
                background: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }
            
            .loading {
                text-align: center;
                padding: 20px;
            }
            
            .spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #007bff;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                animation: spin 1s linear infinite;
                margin: 0 auto 10px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .drop-indicator {
                position: absolute;
                border: 2px dashed #007bff;
                background: rgba(0,123,255,0.1);
                border-radius: 5px;
                pointer-events: none;
                z-index: 5;
            }
            
            .signature-preview {
                position: absolute;
                pointer-events: none;
                z-index: 10;
                opacity: 0.8;
                transform: translate(-50%, -50%);
            }
        `;
        document.head.appendChild(style);
        
        // Initialiser le module
        console.log('🚀 Initialisation du module de signature...');
        initSignatureModule();
        console.log('✅ Module de signature créé et initialisé avec succès !');
    }
    
    // Fonction d'initialisation du module
    function initSignatureModule() {
        console.log('🔧 Initialisation du module de signature...');
        const pdfUrl = '/documents/view/{{ $document->id }}';
        const signatureUrl = '/storage/signatures/signature_{{ auth()->user()->id }}_*.png';
        const backendUrl = '/signatures/save-signed-pdf';
        const documentId = {{ $document->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        console.log('📋 URLs configurées:', { pdfUrl, signatureUrl, backendUrl });
        
        // Éléments DOM
        const pdfCanvas = document.getElementById('pdfCanvas');
        if (!pdfCanvas) {
            console.error('❌ Canvas PDF non trouvé !');
            return;
        }
        const ctx = pdfCanvas.getContext('2d');
        const signatureItem = document.getElementById('signatureItem');
        if (!signatureItem) {
            console.error('❌ Élément signature non trouvé !');
            return;
        }
        
        console.log('✅ Éléments DOM trouvés:', { pdfCanvas, signatureItem });
        const signatureImage = document.getElementById('signatureImage');
        const saveBtn = document.getElementById('saveBtn');
        const statusMessage = document.getElementById('statusMessage');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const zoomOutBtn = document.getElementById('zoomOutBtn');
        const zoomInBtn = document.getElementById('zoomInBtn');
        const zoomLevel = document.getElementById('zoomLevel');
        const currentPageSpan = document.getElementById('currentPageSpan');
        const totalPagesSpan = document.getElementById('totalPagesSpan');
        const prevPageBtn = document.getElementById('prevPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const signatureStatus = document.getElementById('signatureStatus');
        const debugBtn = document.getElementById('debugBtn');
        const reloadSignatureBtn = document.getElementById('reloadSignatureBtn');
        const testSignatureBtn = document.getElementById('testSignatureBtn');
        
        // État de l'application
        let pdfDoc = null;
        let currentPage = 1;
        let totalPages = 0;
        let zoom = 1.0; // Format A4 par défaut (100%)
        let signatures = []; // Tableau pour stocker les signatures par page
        let signaturePosition = null;
        let signatureImageData = null;
        let signatureCanvas = null;
        let signatureCtx = null;
        
        // Plus besoin de tester les URLs car on utilise directement la base de données
        
        // Fonction pour vérifier l'existence d'un fichier (plus utilisée)
        async function checkFileExists(url) {
            try {
                const response = await fetch(url, { method: 'HEAD' });
                return response.ok;
            } catch (error) {
                return false;
            }
        }
        
        // Récupérer la signature de l'utilisateur depuis la base de données
        async function fetchUserSignature() {
            try {
                console.log('🔄 Récupération de la signature depuis la base de données...');
                console.log('🔑 CSRF Token:', csrfToken);
                console.log('🌐 URL:', window.location.href);
                
                const response = await fetch('/api/user-signature', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                console.log('📡 Réponse HTTP:', response.status, response.statusText);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('✅ Signature récupérée depuis la base de données:', data);
                    return data.signature_url || data.signature_data;
                } else {
                    const errorData = await response.json().catch(() => ({ message: 'Erreur inconnue' }));
                    console.log('❌ Erreur lors de la récupération de la signature:', response.status, errorData);
                    return null;
                }
            } catch (error) {
                console.log('❌ Erreur lors de la récupération de la signature:', error);
                return null;
            }
        }
        
        // Charger la signature depuis des données (base64 ou URL)
        function loadSignatureFromData(signatureData) {
            return new Promise((resolve, reject) => {
                console.log('🔄 Chargement de la signature depuis les données...');
                console.log('📸 Données de signature reçues:', signatureData);
                showStatus('Chargement de la signature de l\'utilisateur...', 'info');
                
                const img = new Image();
                img.crossOrigin = 'anonymous';
                
                img.onload = () => {
                    console.log('✅ Image de signature chargée avec succès:', img.width + 'x' + img.height);
                    console.log('🖼️ URL de l\'image:', img.src);
                    
                    // Afficher l'image dans la section "Signatures disponibles"
                    signatureImage.src = img.src;
                    signatureImageData = img;
                    console.log('✅ Signature de l\'utilisateur chargée:', img.width + 'x' + img.height);
                    showStatus('Signature de l\'utilisateur chargée avec succès', 'success');
                    
                    // Mettre à jour le label de la signature
                    const signatureLabel = document.querySelector('.signature-label');
                    if (signatureLabel) {
                        signatureLabel.textContent = 'Signature de l\'utilisateur - Glissez-moi sur le PDF';
                        console.log('🏷️ Label de signature mis à jour');
                    } else {
                        console.log('⚠️ Élément .signature-label non trouvé');
                    }
                    
                    // Vérifier que l'image est bien affichée
                    console.log('🖼️ signatureImage.src après chargement:', signatureImage.src);
                    console.log('🖼️ signatureImage.style.display:', signatureImage.style.display);
                    
                    resolve();
                };
                
                img.onerror = (error) => {
                    console.error('❌ Erreur lors du chargement de la signature de l\'utilisateur:', error);
                    console.error('❌ URL qui a échoué:', signatureData);
                    showStatus('Erreur de chargement de la signature de l\'utilisateur', 'error');
                    createDefaultSignature();
                    resolve();
                };
                
                console.log('🔄 Tentative de chargement de la signature de l\'utilisateur');
                console.log('🔗 URL de chargement:', signatureData);
                img.src = signatureData;
            });
        }
        
        // Initialisation
        init();
        
        async function init() {
            console.log('🚀 Initialisation du module de signature intégré');
            
            try {
                // Créer le canvas de signature
                createSignatureCanvas();
                
                // Charger la signature
                await loadSignature();
                
                // Charger le PDF
                await loadPDF();
                
                // Configurer les événements
                setupEventListeners();
                
                showStatus('Module initialisé avec succès', 'success');
            } catch (error) {
                console.error('❌ Erreur lors de l\'initialisation:', error);
                showStatus(`Erreur d'initialisation: ${error.message}`, 'error');
            }
        }
        
        // Créer le canvas de signature
        function createSignatureCanvas() {
            signatureCanvas = document.createElement('canvas');
            signatureCanvas.id = 'signature-canvas';
            signatureCanvas.className = 'signature-canvas';
            signatureCanvas.style.position = 'absolute';
            signatureCanvas.style.top = '0';
            signatureCanvas.style.left = '0';
            signatureCanvas.style.pointerEvents = 'none';
            signatureCanvas.style.zIndex = '10';
            
            const canvasContainer = pdfCanvas.parentElement;
            if (canvasContainer) {
                canvasContainer.style.position = 'relative';
                canvasContainer.appendChild(signatureCanvas);
            }
            
            signatureCtx = signatureCanvas.getContext('2d');
            console.log('✅ Canvas de signature créé');
        }
        
        // Charger la signature de l'utilisateur connecté depuis la base de données
        async function loadSignature() {
            console.log('📸 Chargement de la signature de l\'utilisateur connecté...');
            showStatus('Recherche de la signature de l\'utilisateur...', 'info');
            
            // Vérifier que l'élément signatureImage existe
            if (!signatureImage) {
                console.error('❌ Élément signatureImage non trouvé');
                showStatus('Erreur: Élément de signature non trouvé', 'error');
                return;
            }
            
            console.log('🖼️ Élément signatureImage trouvé:', signatureImage);
            
            try {
                const userSignature = await fetchUserSignature();
                if (userSignature) {
                    console.log('✅ Signature récupérée depuis la base de données:', userSignature);
                    showStatus('Signature de l\'utilisateur récupérée', 'success');
                    return loadSignatureFromData(userSignature);
                } else {
                    console.log('⚠️ Aucune signature trouvée dans la base de données');
                    showStatus('Aucune signature définie pour cet utilisateur', 'info');
                    createDefaultSignature();
                }
            } catch (error) {
                console.log('❌ Erreur lors de la récupération de la signature:', error);
                showStatus('Erreur lors de la récupération de la signature', 'error');
                createDefaultSignature();
            }
        }
        
        // Charger la signature depuis une URL spécifique
        function loadSignatureFromUrl(url) {
            return new Promise((resolve, reject) => {
                console.log(`🔄 Chargement de la signature depuis: ${url}`);
                showStatus(`Chargement de la signature depuis: ${url}`, 'info');
                
                const img = new Image();
                img.crossOrigin = 'anonymous';
                
                img.onload = () => {
                    // Afficher l'image dans la section "Signatures disponibles"
                    signatureImage.src = img.src;
                    signatureImageData = img;
                    console.log('✅ Signature chargée:', img.width + 'x' + img.height);
                    showStatus('Signature chargée avec succès', 'success');
                    
                    // Mettre à jour le label de la signature
                    const signatureLabel = document.querySelector('.signature-label');
                    if (signatureLabel) {
                        signatureLabel.textContent = 'Signature chargée - Glissez-moi sur le PDF';
                    }
                    
                    resolve();
                };
                
                img.onerror = (error) => {
                    console.error('❌ Erreur lors du chargement de la signature depuis:', url, error);
                    showStatus(`Erreur de chargement: ${url}`, 'error');
                    createDefaultSignature();
                    resolve();
                };
                
                console.log(`🔄 Tentative de chargement de l'image: ${url}`);
                img.src = url;
            });
        }
        
        // Créer une signature par défaut
        function createDefaultSignature() {
            console.log('🔄 Création d\'une signature par défaut');
            showStatus('Création d\'une signature par défaut...', 'info');
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = 200;
            canvas.height = 100;
            
            // Fond blanc
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Bordure
            ctx.strokeStyle = '#2c3e50';
            ctx.lineWidth = 2;
            ctx.strokeRect(5, 5, canvas.width - 10, canvas.height - 10);
            
            // Texte de la signature
            ctx.fillStyle = '#2c3e50';
            ctx.font = 'bold 16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Signature DG', canvas.width / 2, canvas.height / 2);
            
            // Sous-texte
            ctx.font = '12px Arial';
            ctx.fillText('Par défaut', canvas.width / 2, canvas.height / 2 + 20);
            
            const img = new Image();
            img.src = canvas.toDataURL();
            signatureImage.src = img.src;
            signatureImageData = img;
            
            // Mettre à jour le label de la signature
            const signatureLabel = document.querySelector('.signature-label');
            if (signatureLabel) {
                signatureLabel.textContent = 'Signature par défaut - Glissez-moi sur le PDF';
            }
            
            console.log('✅ Signature par défaut créée');
            showStatus('Signature par défaut créée', 'success');
        }
        
        // Fonction de test des URLs supprimée (plus nécessaire car on utilise la base de données)
        
        // Fonctions pour gérer les signatures multiples par page
        function saveCurrentPageSignature() {
            if (signaturePosition && signatureImageData) {
                signatures[currentPage - 1] = {
                    position: { ...signaturePosition },
                    imageData: signatureImageData,
                    timestamp: Date.now()
                };
                console.log(`💾 Signature sauvegardée pour la page ${currentPage}`);
            } else if (signaturePosition || signatureImageData) {
                console.log(`⚠️ Signature partielle détectée pour la page ${currentPage}:`, {
                    hasPosition: !!signaturePosition,
                    hasImageData: !!signatureImageData
                });
            }
        }
        
        function restoreSignaturesForPage(pageNum) {
            const pageIndex = pageNum - 1;
            if (signatures[pageIndex]) {
                const savedSignature = signatures[pageIndex];
                signaturePosition = savedSignature.position;
                signatureImageData = savedSignature.imageData;
                
                // Redessiner la signature sur le canvas
                if (signatureCanvas && signatureCtx) {
                    signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                    if (signatureImageData) {
                        const img = new Image();
                        img.onload = () => {
                            signatureCtx.drawImage(img, 
                                signaturePosition.x, 
                                signaturePosition.y, 
                                signaturePosition.width, 
                                signaturePosition.height
                            );
                        };
                        img.src = signatureImageData;
                    }
                }
                console.log(`🔄 Signature restaurée pour la page ${pageNum}`);
            } else {
                // Effacer la signature si aucune n'est sauvegardée
                signaturePosition = null;
                signatureImageData = null;
                if (signatureCanvas && signatureCtx) {
                    signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                }
            }
        }
        
        function updatePageNavigation() {
            currentPageSpan.textContent = currentPage;
            prevPageBtn.disabled = currentPage <= 1;
            nextPageBtn.disabled = currentPage >= totalPages;
            
            // Afficher le statut des signatures pour toutes les pages
            let statusText = '';
            let signedCount = 0;
            for (let i = 0; i < totalPages; i++) {
                const hasSignature = signatures[i] !== null;
                if (hasSignature) signedCount++;
                statusText += `Page ${i + 1}: ${hasSignature ? '✅' : '❌'} `;
            }
            
            // Mettre à jour l'affichage du statut
            const statusElement = signatureStatus.querySelector('.status-text');
            const signedPagesList = signatures.map((sig, index) => sig ? index + 1 : null).filter(p => p !== null);
            
            if (signedCount === totalPages) {
                statusElement.textContent = `✅ Toutes les pages signées (${signedCount}/${totalPages})`;
                signatureStatus.className = 'signature-status complete';
            } else if (signedCount > 0) {
                statusElement.textContent = `🟡 Pages signées: ${signedPagesList.join(', ')} (${signedCount}/${totalPages})`;
                signatureStatus.className = 'signature-status partial';
            } else {
                statusElement.textContent = `❌ Aucune page signée (0/${totalPages})`;
                signatureStatus.className = 'signature-status none';
            }
            
            console.log(`📊 Statut des signatures: ${statusText}`);
        }
        
        // Charger le PDF
        async function loadPDF() {
            console.log('📄 Chargement du PDF...');
            showLoading(true);
            
            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                pdfDoc = await loadingTask.promise;
                
                console.log(`✅ PDF chargé: ${pdfDoc.numPages} pages`);
                
                totalPages = pdfDoc.numPages;
                totalPagesSpan.textContent = totalPages;
                
                // Initialiser le tableau de signatures pour toutes les pages
                signatures = new Array(totalPages).fill(null);
                
                await renderPage(1);
                updatePageNavigation();
                
                showLoading(false);
                showStatus('PDF chargé avec succès', 'success');
            } catch (error) {
                console.error('❌ Erreur lors du chargement du PDF:', error);
                showLoading(false);
                showStatus(`Erreur de chargement PDF: ${error.message}`, 'error');
            }
        }
        
        // Rendre une page du PDF
        async function renderPage(pageNum) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: zoom });
            
            pdfCanvas.width = viewport.width;
            pdfCanvas.height = viewport.height;
            pdfCanvas.style.width = viewport.width + 'px';
            pdfCanvas.style.height = viewport.height + 'px';
            
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            await page.render(renderContext).promise;
            pdfCanvas.style.display = 'block';
            
            // Synchroniser le canvas de signature
            syncSignatureCanvas(viewport.width, viewport.height);
            
            // Restaurer les signatures existantes pour cette page
            restoreSignaturesForPage(pageNum);
            
            console.log(`✅ Page ${pageNum} rendue: ${viewport.width}x${viewport.height}`);
        }
        
        // Synchroniser le canvas de signature
        function syncSignatureCanvas(width, height) {
            if (!signatureCanvas) return;
            
            signatureCanvas.style.width = width + 'px';
            signatureCanvas.style.height = height + 'px';
            signatureCanvas.width = width;
            signatureCanvas.height = height;
            
            signatureCanvas.style.top = '0px';
            signatureCanvas.style.left = '0px';
            
            signatureCtx.clearRect(0, 0, width, height);
            
            console.log(`🔄 Canvas de signature synchronisé: ${width}x${height}`);
        }
        
        // Configurer les événements
        function setupEventListeners() {
            // Événements de glisser-déposer
            signatureItem.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', 'signature');
                console.log('🖱️ Début du glissement de la signature');
                console.log('🔍 État avant glissement:', {
                    signaturePosition: signaturePosition,
                    signatureImageData: signatureImageData
                });
            });

            pdfCanvas.parentElement.addEventListener('dragover', (e) => {
                e.preventDefault();
                showDropIndicator(e);
            });

            pdfCanvas.parentElement.addEventListener('dragleave', (e) => {
                hideDropIndicator();
            });

            pdfCanvas.parentElement.addEventListener('drop', (e) => {
                e.preventDefault();
                handleSignatureDrop(e);
            });

            // Boutons
            saveBtn.addEventListener('click', () => {
                sendSignedPDF();
            });
            
            zoomOutBtn.addEventListener('click', () => {
                zoom = Math.max(0.25, zoom - 0.25);
                updateZoom();
            });
            
            zoomInBtn.addEventListener('click', () => {
                zoom = Math.min(2.0, zoom + 0.25);
                updateZoom();
            });
            
            // Navigation entre pages
            prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    saveCurrentPageSignature();
                    currentPage--;
                    renderPage(currentPage);
                    updatePageNavigation();
                }
            });
            
            nextPageBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    saveCurrentPageSignature();
                    currentPage++;
                    renderPage(currentPage);
                    updatePageNavigation();
                }
            });
            
            reloadSignatureBtn.addEventListener('click', () => {
                loadSignature();
            });
            
            // Bouton de test pour forcer le chargement de la signature
            const testSignatureBtn = document.getElementById('testSignatureBtn');
            testSignatureBtn.addEventListener('click', async () => {
                console.log('🧪 Test de chargement de la signature...');
                console.log('🖼️ signatureImage avant test:', signatureImage);
                console.log('🖼️ signatureImage.src avant test:', signatureImage?.src);
                
                // Forcer le rechargement de la signature
                await loadSignature();
                
                console.log('🖼️ signatureImage après test:', signatureImage);
                console.log('🖼️ signatureImage.src après test:', signatureImage?.src);
            });
            
            // Bouton de debug
            debugBtn.addEventListener('click', () => {
                console.log('🔍 DEBUG - État complet des signatures:');
                console.log('📊 Tableau signatures:', signatures);
                console.log('📍 Signature actuelle:', { signaturePosition, signatureImageData });
                console.log('📄 Page actuelle:', currentPage);
                console.log('📄 Total pages:', totalPages);
                
                const hasAnySignature = signatures.some(sig => sig !== null) || 
                                      (signaturePosition && signatureImageData);
                console.log('✅ A des signatures:', hasAnySignature);
                
                showStatus(`Debug: ${hasAnySignature ? 'Signatures détectées' : 'Aucune signature'}`, hasAnySignature ? 'success' : 'error');
            });
            
            // Bouton de test des coordonnées
            const testCoordinatesBtn = document.getElementById('testCoordinatesBtn');
            testCoordinatesBtn.addEventListener('click', () => {
                console.log('📐 Test des coordonnées avec méthode pourcentage...');
                
                if (!signaturePosition) {
                    console.log('❌ Aucune position de signature définie');
                    showStatus('Placez d\'abord une signature sur le PDF', 'error');
                    return;
                }
                
                console.log('📍 Position de la signature:', signaturePosition);
                console.log('📍 Pourcentages:', { 
                    xPercent: signaturePosition.xPercent, 
                    yPercent: signaturePosition.yPercent 
                });
                
                // Simuler les dimensions PDF (A4)
                const pageWidth = 595; // A4 width in points
                const pageHeight = 842; // A4 height in points
                
                // Conversion en coordonnées PDF avec pourcentages
                const pdfX = signaturePosition.xPercent * pageWidth;
                const pdfY = (1 - signaturePosition.yPercent) * pageHeight;  // inversion Y
                
                console.log('📐 Conversion pourcentage:', { 
                    xPercent: signaturePosition.xPercent, 
                    yPercent: signaturePosition.yPercent 
                });
                console.log('📍 Position PDF calculée:', { pdfX, pdfY });
                
                showStatus(`Coordonnées: Canvas(${signaturePosition.x}, ${signaturePosition.y}) → PDF(${pdfX.toFixed(2)}, ${pdfY.toFixed(2)}) - Pourcentages(${(signaturePosition.xPercent*100).toFixed(1)}%, ${(signaturePosition.yPercent*100).toFixed(1)}%)`, 'info');
            });
        }
        
        // Afficher l'indicateur de drop
        function showDropIndicator(e) {
            hideDropIndicator();
            
            const indicator = document.createElement('div');
            indicator.className = 'drop-indicator';
            indicator.id = 'dropIndicator';
            
            const rect = pdfCanvas.parentElement.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            indicator.style.left = (x - 50) + 'px';
            indicator.style.top = (y - 25) + 'px';
            indicator.style.width = '100px';
            indicator.style.height = '50px';
            
            pdfCanvas.parentElement.appendChild(indicator);
        }
        
        // Masquer l'indicateur de drop
        function hideDropIndicator() {
            const indicator = document.getElementById('dropIndicator');
            if (indicator) {
                indicator.remove();
            }
        }
        
        // Gérer le drop de la signature
        function handleSignatureDrop(e) {
            console.log('🎯 Drop de la signature détecté');
            hideDropIndicator();
            
            const rect = pdfCanvas.getBoundingClientRect();
            const xCanvas = e.clientX - rect.left;
            const yCanvas = e.clientY - rect.top;
            
            // Coordonnées relatives en pourcentage (plus fiable)
            const xPercent = xCanvas / rect.width;
            const yPercent = yCanvas / rect.height;
            
            signaturePosition = { 
                x: xCanvas, 
                y: yCanvas,
                xPercent: xPercent,
                yPercent: yPercent
            };
            
            saveBtn.disabled = false;
            console.log('✅ Bouton "Enregistrer PDF Signé" activé');
            
            showSignaturePreview(xCanvas, yCanvas);
            
            showStatus('Signature positionnée avec succès', 'success');
            console.log(`📍 Position de la signature: Canvas(${xCanvas}, ${yCanvas}) - Pourcentages(${(xPercent*100).toFixed(1)}%, ${(yPercent*100).toFixed(1)}%)`);
            console.log('🔍 État après drop:', {
                signaturePosition: signaturePosition,
                signatureImageData: signatureImageData,
                hasImageData: !!signatureImageData
            });
        }
        
        // Afficher un aperçu de la signature
        function showSignaturePreview(x, y) {
            const oldPreview = document.getElementById('signaturePreview');
            if (oldPreview) {
                oldPreview.remove();
            }
            
            const preview = document.createElement('div');
            preview.id = 'signaturePreview';
            preview.className = 'signature-preview';
            preview.style.left = x + 'px';
            preview.style.top = y + 'px';
            
            const img = document.createElement('img');
            img.src = signatureImage.src;
            img.style.maxWidth = '100px';
            img.style.maxHeight = '50px';
            img.style.opacity = '0.7';
            
            preview.appendChild(img);
            pdfCanvas.parentElement.appendChild(preview);
        }
        
        // Envoyer le PDF signé
        async function sendSignedPDF() {
            console.log('📤 Début de l\'envoi du PDF signé...');
            
            // Sauvegarder la signature de la page actuelle avant de vérifier
            saveCurrentPageSignature();
            
            // Forcer la sauvegarde si on a une signature actuelle mais pas encore sauvegardée
            if (signaturePosition && signatureImageData && !signatures[currentPage - 1]) {
                signatures[currentPage - 1] = {
                    position: { ...signaturePosition },
                    imageData: signatureImageData,
                    timestamp: Date.now()
                };
                console.log(`💾 Signature forcée sauvegardée pour la page ${currentPage}`);
            }
            
            // Vérifier s'il y a au moins une signature (incluant la page actuelle)
            const hasAnySignature = signatures.some(sig => sig !== null) || 
                                  (signaturePosition && signatureImageData);
            if (!hasAnySignature) {
                console.log('❌ Aucune signature définie sur aucune page');
                console.log('🔍 Debug - État actuel:', {
                    signatures: signatures,
                    signaturePosition: signaturePosition,
                    signatureImageData: signatureImageData,
                    currentPage: currentPage
                });
                showStatus('Veuillez d\'abord positionner au moins une signature', 'error');
                return;
            }
            
            // Vérifier si toutes les pages sont signées (optionnel)
            const signedPagesCount = signatures.filter(sig => sig !== null).length;
            if (signedPagesCount < totalPages) {
                const missingPages = [];
                for (let i = 0; i < totalPages; i++) {
                    if (!signatures[i]) {
                        missingPages.push(i + 1);
                    }
                }
                console.log(`⚠️ Attention: ${totalPages - signedPagesCount} page(s) non signée(s): ${missingPages.join(', ')}`);
                showStatus(`⚠️ ${totalPages - signedPagesCount} page(s) non signée(s). Continuer quand même ?`, 'warning');
            }
            
            console.log('📍 Signatures disponibles:', signatures);
            console.log('📍 Signature actuelle:', { signaturePosition, signatureImageData });
            console.log('📄 URL du PDF:', pdfUrl);
            
            // Afficher un résumé des signatures
            const signedPagesSummary = signatures.map((sig, index) => sig ? index + 1 : null).filter(p => p !== null);
            console.log(`📊 Pages signées: ${signedPagesSummary.join(', ')} sur ${totalPages} pages`);
            
            showStatus('Génération du PDF signé...', 'info');
            saveBtn.disabled = true;
            
            try {
                console.log('🔧 Étape 1: Génération du PDF signé...');
                const signedPdfBytes = await generateSignedPDF();
                console.log('✅ PDF signé généré, taille:', signedPdfBytes.byteLength, 'bytes');
                
                console.log('📤 Étape 2: Upload vers le backend...');
                await uploadSignedPDF(signedPdfBytes);
                console.log('✅ Upload réussi');
                
                showStatus('PDF signé envoyé avec succès !', 'success');
                console.log('✅ PDF signé envoyé avec succès');
            } catch (error) {
                console.error('❌ Erreur détaillée lors de l\'envoi:', error);
                console.error('❌ Stack trace:', error.stack);
                console.error('❌ Type d\'erreur:', typeof error);
                console.error('❌ Message d\'erreur:', error.message);
                showStatus(`Erreur d'envoi: ${error.message || 'Erreur inconnue'}`, 'error');
                saveBtn.disabled = false;
            }
        }
        
        // Générer le PDF signé
        async function generateSignedPDF() {
            console.log('🔧 Génération du PDF signé avec PDF-lib...');
            console.log('📄 URL du PDF:', pdfUrl);
            console.log('📊 Signatures à traiter:', signatures);
            
            // Sauvegarder la signature de la page actuelle avant de générer
            saveCurrentPageSignature();
            
            const pdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);
            const pages = pdfDoc.getPages();
            
            console.log(`📄 Traitement de ${pages.length} pages avec signatures multiples`);
            
            // Traiter chaque page qui a une signature
            for (let pageIndex = 0; pageIndex < pages.length; pageIndex++) {
                const page = pages[pageIndex];
                const signatureData = signatures[pageIndex];
                
                if (signatureData && signatureData.position && signatureData.imageData) {
                    console.log(`📝 Traitement de la signature pour la page ${pageIndex + 1}`);
                    
                    const { width: pageWidth, height: pageHeight } = page.getSize();
                    
                    // Conversion en coordonnées PDF avec pourcentages
                    const pdfX = signatureData.position.xPercent * pageWidth;
                    const pdfY = (1 - signatureData.position.yPercent) * pageHeight;  // inversion Y
            
            // Taille de la signature (proportionnelle à la page PDF)
            const signatureWidth = pageWidth * 0.15;  // 15% de la largeur de la page
            const signatureHeight = pageHeight * 0.08;  // 8% de la hauteur de la page
                    
                    console.log(`📍 Page ${pageIndex + 1} - Position PDF:`, { pdfX, pdfY });
                    console.log(`📏 Page ${pageIndex + 1} - Taille signature:`, { signatureWidth, signatureHeight });
                    
                    try {
                        // Convertir l'image de signature en PNG
                        const signatureImageBytes = await fetch(signatureData.imageData).then(res => res.arrayBuffer());
                        const signatureImage = await pdfDoc.embedPng(signatureImageBytes);
                        
                        // Ajouter la signature à la page
                        page.drawImage(signatureImage, {
                x: pdfX,
                            y: pdfY,
                width: signatureWidth,
                            height: signatureHeight,
                        });
                        
                        console.log(`✅ Signature ajoutée à la page ${pageIndex + 1}`);
                    } catch (imageError) {
                        console.error(`❌ Erreur lors de l'ajout de la signature à la page ${pageIndex + 1}:`, imageError);
                        // Continuer avec les autres pages même si une signature échoue
                    }
                } else {
                    console.log(`⏭️ Aucune signature pour la page ${pageIndex + 1}`);
                }
            }
            
            console.log('🖼️ Intégration des images de signature terminée...');
            
            console.log('💾 Sauvegarde du PDF modifié...');
            const pdfBytesModified = await pdfDoc.save();
            console.log('✅ PDF signé généré, taille finale:', pdfBytesModified.byteLength, 'bytes');
            
            return pdfBytesModified;
        }
        
        // Upload du PDF signé
        async function uploadSignedPDF(pdfBytes) {
            console.log('📤 Upload vers le backend...');
            console.log('🔗 URL backend:', backendUrl);
            console.log('🔑 CSRF Token:', csrfToken);
            console.log('📄 Document ID:', documentId);
            console.log('📊 Signatures disponibles:', signatures);
            console.log('📦 Taille du PDF:', pdfBytes.byteLength, 'bytes');
            
            const formData = new FormData();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            formData.append('signed_pdf', blob, 'document_signe.pdf');
            formData.append('document_id', documentId);
            
            // Créer l'objet signature_data avec les informations multi-pages
            const signatureData = {
                signatures: signatures,
                is_multi_page: true,
                total_pages: totalPages,
                signed_pages_count: signatures.filter(sig => sig !== null).length,
                timestamp: new Date().toISOString(),
                user_id: {{ auth()->user()->id }},
                user_name: '{{ auth()->user()->name }}'
            };
            
            formData.append('signature_data', JSON.stringify(signatureData));
            
            console.log('📋 FormData créé avec les données suivantes:');
            for (let [key, value] of formData.entries()) {
                if (key === 'signature_data') {
                    console.log(`  ${key}:`, JSON.parse(value));
                } else {
                    console.log(`  ${key}:`, value);
                }
            }
            
            console.log('📋 Données de signature complètes:', signatureData);
            
            // Vérifier que signature_data est bien dans le FormData
            console.log('🔍 Vérification du FormData:');
            const signatureDataFromForm = formData.get('signature_data');
            console.log('📋 signature_data dans FormData:', signatureDataFromForm);
            console.log('📋 Type de signature_data:', typeof signatureDataFromForm);
            
            const response = await fetch(backendUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            console.log('📡 Réponse du serveur:', response.status, response.statusText);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ Erreur serveur:', errorText);
                throw new Error(`Erreur serveur: ${response.status} - ${errorText}`);
            }
            
            const result = await response.json();
            console.log('✅ Réponse du serveur:', result);
        }
        
        // Mettre à jour le zoom
        function updateZoom() {
            zoomLevel.textContent = Math.round(zoom * 100) + '%';
            renderPage(currentPage);
        }
        
        // Afficher le chargement
        function showLoading(show) {
            loadingIndicator.style.display = show ? 'block' : 'none';
        }
        
        // Afficher un message de statut
        function showStatus(message, type = 'info') {
            statusMessage.textContent = message;
            statusMessage.className = `status-message ${type}`;
            statusMessage.style.display = 'block';
            
            if (type === 'success') {
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            }
        }
    }
    
    initModule();
});
</script>
@endsection