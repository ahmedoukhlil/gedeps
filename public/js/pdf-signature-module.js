/**
 * Module de Signature PDF avec Fabric.js pour les signatures séquentielles
 * Basé sur le système existant mais adapté pour les signatures séquentielles
 */
class PDFSignatureModule {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 1;
        this.scale = 1.2; // Increased default scale for better quality
        this.fabricCanvas = null;
        this.userSignature = null;
        this.signatures = {};
        this.isInitialized = false;
        this.devicePixelRatio = window.devicePixelRatio || 1; // High DPI support
        this.qualityMode = 'high'; // Quality mode: 'low', 'medium', 'high', 'ultra'
        
        // Configuration PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        console.log('🚀 Module PDF Signature initialisé');
        this.init();
    }

    /**
     * Obtenir le ratio de pixels pour la qualité
     */
    getQualityPixelRatio() {
        const baseRatio = this.devicePixelRatio;
        
        switch (this.qualityMode) {
            case 'low':
                return Math.min(baseRatio, 1.0);
            case 'medium':
                return Math.min(baseRatio, 1.5);
            case 'high':
                return Math.min(baseRatio, 2.0);
            case 'ultra':
                return Math.min(baseRatio, 3.0);
            default:
                return Math.min(baseRatio, 2.0);
        }
    }

    /**
     * Changer le mode de qualité
     */
    setQualityMode(mode) {
        this.qualityMode = mode;
        if (this.pdfDoc) {
            this.renderPage(this.currentPage);
        }
    }
    
    async init() {
        try {
            console.log('🔄 Initialisation du module de signature...');
            
            // Vérifier PDF.js et Fabric.js
            if (typeof pdfjsLib === 'undefined') {
                throw new Error('PDF.js non chargé');
            }
            if (typeof fabric === 'undefined') {
                throw new Error('Fabric.js non chargé');
            }
            
            // Charger la signature utilisateur
            await this.loadUserSignature();
            
            // Initialiser Fabric.js
            this.initializeFabric();
            
            // Charger le PDF
            await this.loadPDF(this.config.pdfUrl);
            
            // Initialiser les événements
            this.initializeEvents();
            
            this.isInitialized = true;
            console.log('✅ Module de signature initialisé avec succès');
            this.showStatus('Module de signature prêt', 'success');
            
        } catch (error) {
            console.error('❌ Erreur initialisation:', error);
            this.showStatus(`Erreur: ${error.message}`, 'error');
        }
    }
    
    async loadUserSignature() {
        try {
            console.log('🔄 Chargement de la signature utilisateur...');
            
            const response = await fetch('/signatures/user-signature', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Erreur API: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📡 Réponse API signature:', data);
            
            if (data.success && data.signature_url) {
                this.userSignature = data.signature_url;
                console.log('✅ Signature utilisateur chargée:', this.userSignature);
                this.showStatus('Signature utilisateur chargée', 'success');
            } else {
                throw new Error('Aucune signature trouvée');
            }
            
        } catch (error) {
            console.error('❌ Erreur chargement signature:', error);
            this.showStatus(`Erreur signature: ${error.message}`, 'error');
            throw error;
        }
    }
    
    async loadPDF(pdfUrl) {
        try {
            console.log('🔄 Chargement du PDF...');
            this.showStatus('Chargement du PDF...', 'info');
            
            // Charger le PDF avec PDF.js
            this.pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
            this.totalPages = this.pdfDoc.numPages;
            this.currentPage = 1;
            
            console.log(`✅ PDF chargé: ${this.totalPages} pages`);
            
            // Rendre la première page
            await this.renderPage(this.currentPage);
            
            this.showStatus(`PDF chargé: ${this.totalPages} pages`, 'success');
            
        } catch (error) {
            console.error('❌ Erreur chargement PDF:', error);
            this.showStatus(`Erreur PDF: ${error.message}`, 'error');
            throw error;
        }
    }
    
    async renderPage(pageNum) {
        try {
            console.log(`🔄 Rendu de la page ${pageNum}...`);
            
            const page = await this.pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: this.scale });
            
            // Mettre à jour les canvas
            const pdfCanvas = document.getElementById('pdfCanvas');
            const fabricCanvas = document.getElementById('fabricCanvas');
            
            // Configuration haute qualité avec support DPI
            const pixelRatio = this.getQualityPixelRatio();
            const scaledViewport = page.getViewport({ scale: this.scale * pixelRatio });
            
            // Configurer le canvas PDF avec qualité améliorée
            pdfCanvas.width = scaledViewport.width;
            pdfCanvas.height = scaledViewport.height;
            pdfCanvas.style.width = viewport.width + 'px';
            pdfCanvas.style.height = viewport.height + 'px';
            
            // Configurer le canvas Fabric
            fabricCanvas.width = viewport.width;
            fabricCanvas.height = viewport.height;
            fabricCanvas.style.width = viewport.width + 'px';
            fabricCanvas.style.height = viewport.height + 'px';
            
            // Rendre la page PDF avec qualité améliorée
            const context = pdfCanvas.getContext('2d');
            context.scale(pixelRatio, pixelRatio);
            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';
            
            const renderContext = {
                canvasContext: context,
                viewport: scaledViewport,
                intent: 'display',
                enableWebGL: false,
                renderInteractiveForms: false
            };
            
            await page.render(renderContext).promise;
            
            // Mettre à jour Fabric.js
            if (this.fabricCanvas) {
                this.fabricCanvas.setDimensions({
                    width: viewport.width,
                    height: viewport.height
                });
            }
            
            // Restaurer les signatures de cette page
            this.restorePageSignatures(pageNum);
            
            // Mettre à jour l'interface
            this.updatePageInfo();
            this.updateNavigationButtons();
            
            console.log(`✅ Page ${pageNum} rendue`);
            
        } catch (error) {
            console.error(`❌ Erreur rendu page ${pageNum}:`, error);
            throw error;
        }
    }
    
    initializeFabric() {
        const fabricCanvas = document.getElementById('fabricCanvas');
        this.fabricCanvas = new fabric.Canvas(fabricCanvas, {
            selection: true,
            preserveObjectStacking: true
        });
        
        // Événements Fabric.js
        this.fabricCanvas.on('object:added', () => {
            this.updateSignatureStatus();
        });
        
        this.fabricCanvas.on('object:removed', () => {
            this.updateSignatureStatus();
        });
        
        this.fabricCanvas.on('object:modified', () => {
            this.savePageSignatures(this.currentPage);
        });
        
        console.log('✅ Fabric.js initialisé');
    }
    
    initializeEvents() {
        // Navigation des pages
        document.getElementById('prevPageBtn').addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.renderPage(this.currentPage);
            }
        });
        
        document.getElementById('nextPageBtn').addEventListener('click', () => {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.renderPage(this.currentPage);
            }
        });
        
        // Zoom
        document.getElementById('zoomInBtn').addEventListener('click', () => {
            this.scale = Math.min(this.scale * 1.2, 3.0);
            this.renderPage(this.currentPage);
        });
        
        document.getElementById('zoomOutBtn').addEventListener('click', () => {
            this.scale = Math.max(this.scale / 1.2, 0.5);
            this.renderPage(this.currentPage);
        });
        
        // Charger signature
        document.getElementById('loadSignatureBtn').addEventListener('click', () => {
            this.addSignatureToCurrentPage();
        });
        
        // Effacer signatures
        document.getElementById('clearSignaturesBtn').addEventListener('click', () => {
            this.clearAllSignatures();
        });
        
        // Sauvegarder PDF
        document.getElementById('savePdfBtn').addEventListener('click', () => {
            this.saveSignedPDF();
        });
        
        // Formulaire de signature
        const form = document.getElementById('signatureForm');
        const submitBtn = document.getElementById('submitSignature');
        
        if (form && submitBtn) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitSignature();
            });
        }
        
        console.log('✅ Événements initialisés');
    }
    
    async addSignatureToCurrentPage() {
        try {
            if (!this.userSignature) {
                this.showStatus('Aucune signature utilisateur disponible', 'error');
                return;
            }
            
            console.log(`🔄 Ajout de signature à la page ${this.currentPage}...`);
            
            // Créer l'image de signature
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            img.onload = () => {
                const fabricImg = new fabric.Image(img, {
                    left: 100,
                    top: 100,
                    scaleX: 0.5,
                    scaleY: 0.5,
                    selectable: true,
                    moveable: true,
                    hasControls: true,
                    hasBorders: true
                });
                
                this.fabricCanvas.add(fabricImg);
                this.fabricCanvas.setActiveObject(fabricImg);
                this.fabricCanvas.renderAll();
                
                this.savePageSignatures(this.currentPage);
                this.updateSignatureStatus();
                
                this.showStatus(`Signature ajoutée page ${this.currentPage}`, 'success');
            };
            
            img.src = this.userSignature;
            
        } catch (error) {
            console.error(`❌ Erreur ajout signature page ${this.currentPage}:`, error);
            this.showStatus(`Erreur signature: ${error.message}`, 'error');
        }
    }
    
    clearAllSignatures() {
        this.fabricCanvas.clear();
        this.signatures = {};
        this.updateSignatureStatus();
        this.showStatus('Toutes les signatures effacées', 'info');
    }
    
    savePageSignatures(pageNum) {
        const objects = this.fabricCanvas.toJSON();
        this.signatures[pageNum] = objects;
        console.log(`💾 Signatures page ${pageNum} sauvegardées`);
    }
    
    restorePageSignatures(pageNum) {
        if (this.signatures[pageNum]) {
            this.fabricCanvas.loadFromJSON(this.signatures[pageNum], () => {
                this.fabricCanvas.renderAll();
                this.updateSignatureStatus();
            });
        }
    }
    
    updatePageInfo() {
        document.getElementById('pageInfo').textContent = `Page ${this.currentPage} sur ${this.totalPages}`;
        document.getElementById('zoomLevel').textContent = `${Math.round(this.scale * 100)}%`;
    }
    
    updateNavigationButtons() {
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        
        prevBtn.disabled = this.currentPage <= 1;
        nextBtn.disabled = this.currentPage >= this.totalPages;
    }
    
    updateSignatureStatus() {
        const totalObjects = this.fabricCanvas.getObjects().length;
        const statusElement = document.getElementById('signatureStatus');
        const saveBtn = document.getElementById('savePdfBtn');
        const submitBtn = document.getElementById('submitSignature');
        
        if (totalObjects === 0) {
            statusElement.textContent = 'Aucune signature';
            statusElement.className = 'signature-status none';
            if (saveBtn) saveBtn.disabled = true;
            if (submitBtn) submitBtn.disabled = true;
        } else {
            statusElement.textContent = `${totalObjects} signature(s) sur cette page`;
            statusElement.className = 'signature-status partial';
            if (saveBtn) saveBtn.disabled = false;
            if (submitBtn) submitBtn.disabled = false;
        }
    }
    
    async saveSignedPDF() {
        try {
            console.log('🔄 Génération du PDF signé...');
            this.showStatus('Génération du PDF signé...', 'info');
            
            // Charger le PDF original
            const pdfResponse = await fetch(this.config.pdfUrl);
            const pdfBytes = await pdfResponse.arrayBuffer();
            
            // Créer un nouveau PDF avec PDF-lib
            const newPdfDoc = await PDFLib.PDFDocument.load(pdfBytes);
            const pages = newPdfDoc.getPages();
            
            // Ajouter les signatures à chaque page
            for (let pageNum = 1; pageNum <= pages.length; pageNum++) {
                if (this.signatures[pageNum]) {
                    const page = pages[pageNum - 1];
                    
                    // Pour chaque signature sur cette page
                    for (const obj of this.signatures[pageNum].objects) {
                        if (obj.type === 'image' && obj.src) {
                            try {
                                // Charger l'image de signature
                                const imgResponse = await fetch(obj.src);
                                const imgBytes = await imgResponse.arrayBuffer();
                                const signatureImage = await newPdfDoc.embedPng(imgBytes);
                                
                                // Ajouter la signature à la page
                                page.drawImage(signatureImage, {
                                    x: obj.left || 100,
                                    y: (page.getHeight() - (obj.top || 100)) - (obj.height * (obj.scaleY || 1)),
                                    width: (obj.width * (obj.scaleX || 1)) || 100,
                                    height: (obj.height * (obj.scaleY || 1)) || 50,
                                    opacity: 0.8
                                });
                            } catch (imgError) {
                                console.warn(`Erreur chargement image page ${pageNum}:`, imgError);
                            }
                        }
                    }
                }
            }
            
            // Sauvegarder le PDF
            const signedPdfBytes = await newPdfDoc.save();
            
            // Envoyer au serveur
            await this.uploadSignedPDF(signedPdfBytes);
            
        } catch (error) {
            console.error('❌ Erreur génération PDF signé:', error);
            this.showStatus(`Erreur: ${error.message}`, 'error');
        }
    }
    
    async uploadSignedPDF(pdfBytes) {
        try {
            console.log('🔄 Upload du PDF signé...');
            this.showStatus('Upload du PDF signé...', 'info');
            
            const formData = new FormData();
            formData.append('signed_pdf', new Blob([pdfBytes], { type: 'application/pdf' }), 'signed-document.pdf');
            formData.append('signature_data', JSON.stringify({
                is_multi_page: this.totalPages > 1,
                total_pages: this.totalPages,
                signatures: this.signatures,
                timestamp: new Date().toISOString()
            }));
            
            const response = await fetch(this.config.saveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erreur serveur');
            }
            
            const result = await response.json();
            this.showStatus('PDF signé sauvegardé avec succès', 'success');
            
            // Rediriger vers la page d'accueil
            setTimeout(() => {
                window.location.href = '/signatures-simple';
            }, 2000);
            
        } catch (error) {
            console.error('❌ Erreur upload PDF:', error);
            this.showStatus(`Erreur upload: ${error.message}`, 'error');
        }
    }
    
    async submitSignature() {
        try {
            console.log('🔄 Soumission de la signature...');
            
            // Préparer les données de signature
            const signatureData = {
                is_multi_page: this.totalPages > 1,
                total_pages: this.totalPages,
                signatures: this.signatures,
                timestamp: new Date().toISOString(),
                user_id: this.config.userId
            };
            
            // Mettre à jour le champ caché
            const signatureDataField = document.getElementById('signatureData');
            if (signatureDataField) {
                signatureDataField.value = JSON.stringify(signatureData);
            }
            
            // Soumettre le formulaire
            const form = document.getElementById('signatureForm');
            if (form) {
                form.submit();
            }
            
        } catch (error) {
            console.error('❌ Erreur soumission signature:', error);
            this.showStatus(`Erreur: ${error.message}`, 'error');
        }
    }
    
    showStatus(message, type = 'info') {
        const statusElement = document.getElementById('statusMessage');
        if (statusElement) {
            statusElement.textContent = message;
            statusElement.className = `status-message ${type}`;
            statusElement.style.display = 'block';
            
            // Masquer après 5 secondes
            setTimeout(() => {
                statusElement.style.display = 'none';
            }, 5000);
        }
        console.log(`📊 Status: ${message}`);
    }
}

// Exposer la classe globalement
window.PDFSignatureModule = PDFSignatureModule;

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation du Module de Signature PDF');
    
    // Vérifier que la configuration est disponible
    if (window.documentConfig) {
        const signatureModule = new PDFSignatureModule(window.documentConfig);
        window.signatureModule = signatureModule;
    } else {
        console.error('❌ Configuration non disponible');
    }
});
