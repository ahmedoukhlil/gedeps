/**
 * Module de Signature PDF avec Overlay HTML
 * Solution simple et fiable : PDF.js + HTML overlay + PDF-lib
 */
class PDFOverlaySignatureModule {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 1;
        this.userSignature = null;
        this.signatureOverlay = null;
        this.isInitialized = false;
        this.signaturePositions = {};
        
        // Configuration PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        console.log('🚀 Module PDF Overlay Signature initialisé');
        this.init();
    }
    
    async init() {
        try {
            console.log('🔄 Initialisation du module de signature overlay...');
            
            // Vérifier PDF.js
            if (typeof pdfjsLib === 'undefined') {
                throw new Error('PDF.js non chargé');
            }
            
            // Charger la signature utilisateur
            await this.loadUserSignature();
            
            // Initialiser les événements
            this.initializeEvents();
            
            // Charger le PDF
            await this.loadPDF(this.config.pdfUrl);
            
            this.isInitialized = true;
            console.log('✅ Module de signature overlay initialisé avec succès');
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
            const viewport = page.getViewport({ scale: 1.0 });
            
            // Créer le conteneur principal
            const container = document.getElementById('pdfContainer');
            container.innerHTML = '';
            container.style.position = 'relative';
            container.style.width = viewport.width + 'px';
            container.style.height = viewport.height + 'px';
            container.style.margin = '0 auto';
            container.style.border = '2px solid #dee2e6';
            container.style.background = '#f8f9fa';
            
            // Créer le canvas PDF
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.style.position = 'absolute';
            canvas.style.top = '0';
            canvas.style.left = '0';
            canvas.style.zIndex = '1';
            
            // Rendre la page
            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            
            await page.render(renderContext).promise;
            
            // Ajouter le canvas au conteneur
            container.appendChild(canvas);
            
            // Créer l'overlay pour les signatures
            this.createSignatureOverlay(container, viewport);
            
            console.log(`✅ Page ${pageNum} rendue`);
            
        } catch (error) {
            console.error(`❌ Erreur rendu page ${pageNum}:`, error);
            throw error;
        }
    }
    
    createSignatureOverlay(container, viewport) {
        // Créer l'overlay de signature
        this.signatureOverlay = document.createElement('div');
        this.signatureOverlay.style.position = 'absolute';
        this.signatureOverlay.style.top = '0';
        this.signatureOverlay.style.left = '0';
        this.signatureOverlay.style.width = '100%';
        this.signatureOverlay.style.height = '100%';
        this.signatureOverlay.style.zIndex = '10';
        this.signatureOverlay.style.pointerEvents = 'auto';
        
        container.appendChild(this.signatureOverlay);
        
        console.log('✅ Overlay de signature créé');
    }
    
    async addSignatureToPage(pageNum, position = null) {
        try {
            if (!this.userSignature) {
                throw new Error('Aucune signature utilisateur disponible');
            }
            
            console.log(`🔄 Ajout de signature à la page ${pageNum}...`);
            
            // Position par défaut si non spécifiée (bas à droite)
            if (!position) {
                position = {
                    x: 400,
                    y: 300,
                    width: 200,
                    height: 80
                };
            }
            
            // Créer l'élément de signature
            const signatureElement = document.createElement('div');
            signatureElement.className = 'signature-element';
            signatureElement.style.position = 'absolute';
            signatureElement.style.left = position.x + 'px';
            signatureElement.style.top = position.y + 'px';
            signatureElement.style.width = position.width + 'px';
            signatureElement.style.height = position.height + 'px';
            signatureElement.style.border = '2px solid #007bff';
            signatureElement.style.backgroundColor = 'rgba(0, 123, 255, 0.1)';
            signatureElement.style.display = 'flex';
            signatureElement.style.alignItems = 'center';
            signatureElement.style.justifyContent = 'center';
            signatureElement.style.fontSize = '14px';
            signatureElement.style.fontWeight = 'bold';
            signatureElement.style.color = '#007bff';
            signatureElement.style.cursor = 'move';
            signatureElement.style.userSelect = 'none';
            signatureElement.textContent = 'SIGNATURE';
            
            // Ajouter l'image de signature si disponible
            if (this.userSignature) {
                const img = document.createElement('img');
                img.src = this.userSignature;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'contain';
                
                // Ajouter des gestionnaires d'événements pour diagnostiquer
                img.onload = () => {
                    console.log('✅ Image de signature chargée avec succès:', this.userSignature);
                    this.showStatus('Image de signature chargée', 'success');
                };
                
                img.onerror = (error) => {
                    console.error('❌ Erreur de chargement de l\'image de signature:', error);
                    console.error('URL de l\'image:', this.userSignature);
                    this.showStatus('Erreur de chargement de l\'image de signature', 'error');
                    
                    // Afficher un texte de remplacement
                    signatureElement.innerHTML = '<div style="color: red; text-align: center; padding: 10px;">❌ Erreur de chargement de la signature</div>';
                };
                
                signatureElement.innerHTML = '';
                signatureElement.appendChild(img);
            } else {
                console.warn('⚠️ Aucune signature utilisateur disponible');
                this.showStatus('Aucune signature utilisateur disponible', 'warning');
            }
            
            // Rendre l'élément draggable
            this.makeDraggable(signatureElement);
            
            // Ajouter à l'overlay
            this.signatureOverlay.appendChild(signatureElement);
            
            // Sauvegarder la position
            this.signaturePositions[pageNum] = {
                x: position.x,
                y: position.y,
                width: position.width,
                height: position.height
            };
            
            this.showStatus(`Signature ajoutée page ${pageNum}`, 'success');
            
        } catch (error) {
            console.error(`❌ Erreur ajout signature page ${pageNum}:`, error);
            this.showStatus(`Erreur signature: ${error.message}`, 'error');
            throw error;
        }
    }
    
    makeDraggable(element) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        
        element.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(element.style.left);
            startTop = parseInt(element.style.top);
            
            element.style.cursor = 'grabbing';
            e.preventDefault();
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            element.style.left = (startLeft + deltaX) + 'px';
            element.style.top = (startTop + deltaY) + 'px';
        });
        
        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                element.style.cursor = 'move';
                
                // Sauvegarder la nouvelle position
                const pageNum = this.currentPage;
                this.signaturePositions[pageNum] = {
                    x: parseInt(element.style.left),
                    y: parseInt(element.style.top),
                    width: parseInt(element.style.width),
                    height: parseInt(element.style.height)
                };
                
            }
        });
    }
    
    async addSignatureToAllPages() {
        try {
            console.log('🔄 Ajout de signature à toutes les pages...');
            this.showStatus('Ajout de signature à toutes les pages...', 'info');
            
            for (let pageNum = 1; pageNum <= this.totalPages; pageNum++) {
                await this.addSignatureToPage(pageNum);
            }
            
            console.log(`✅ Signature ajoutée à ${this.totalPages} pages`);
            this.showStatus(`Signature ajoutée à ${this.totalPages} pages`, 'success');
            
        } catch (error) {
            console.error('❌ Erreur ajout signature toutes pages:', error);
            this.showStatus(`Erreur: ${error.message}`, 'error');
            throw error;
        }
    }
    
    async generateSignedPDF() {
        try {
            console.log('🔄 Génération du PDF signé...');
            this.showProgress('Génération du PDF signé...', 10);
            
            // Charger le PDF original avec PDF-lib (en parallèle)
            this.showProgress('Chargement des fichiers...', 20);
            const [pdfResponse, signatureResponse] = await Promise.all([
                fetch(this.config.pdfUrl),
                fetch(this.userSignature)
            ]);
            
            const [pdfBytes, signatureBytes] = await Promise.all([
                pdfResponse.arrayBuffer(),
                signatureResponse.arrayBuffer()
            ]);
            
            // Créer un nouveau PDF avec PDF-lib
            this.showProgress('Création du PDF...', 40);
            const newPdfDoc = await PDFLib.PDFDocument.load(pdfBytes);
            
            // Charger l'image de signature
            this.showProgress('Intégration de la signature...', 60);
            const signatureImage = await newPdfDoc.embedPng(signatureBytes);
            
            // Ajouter les signatures aux pages
            const pages = newPdfDoc.getPages();
            
            this.showProgress('Ajout des signatures aux pages...', 70);
            
            for (let pageNum = 1; pageNum <= pages.length; pageNum++) {
                if (this.signaturePositions[pageNum]) {
                    const page = pages[pageNum - 1];
                    const position = this.signaturePositions[pageNum];
                    
                    // Inverser la coordonnée Y pour PDF-lib (Y=0 est en bas)
                    const pageHeight = page.getHeight();
                    const pdfY = pageHeight - position.y - position.height;
                    
                    // Ajouter la signature à la page
                    page.drawImage(signatureImage, {
                        x: position.x,
                        y: pdfY,
                        width: position.width,
                        height: position.height,
                        opacity: 0.8
                    });
                }
                
                // Mettre à jour la progression
                const progress = 70 + (pageNum / pages.length) * 20;
                this.showProgress(`Traitement page ${pageNum}/${pages.length}...`, progress);
            }
            
            // Sauvegarder le PDF
            this.showProgress('Finalisation du PDF...', 90);
            const signedPdfBytes = await newPdfDoc.save();
            
            this.showStatus('PDF signé généré avec succès', 'success');
            
            return signedPdfBytes;
            
        } catch (error) {
            console.error('❌ Erreur génération PDF signé:', error);
            this.showStatus(`Erreur génération: ${error.message}`, 'error');
            throw error;
        }
    }
    
    async saveSignedPDF() {
        try {
            console.log('🔄 Sauvegarde du PDF signé...');
            this.showStatus('Sauvegarde du PDF signé...', 'info');
            
            // Générer le PDF signé
            const pdfBytes = await this.generateSignedPDF();
            
            // Préparer les données en parallèle
            const signatureData = {
                is_multi_page: this.totalPages > 1,
                total_pages: this.totalPages,
                signed_pages_count: Object.keys(this.signaturePositions).length,
                signatures: this.signaturePositions
            };
            
            // Créer un FormData pour l'envoi
            const formData = new FormData();
            formData.append('document_id', this.config.documentId);
            formData.append('signed_pdf', new Blob([pdfBytes], { type: 'application/pdf' }), 'signed-document.pdf');
            formData.append('signature_data', JSON.stringify(signatureData));
            
            
            // Envoyer au serveur
            const response = await fetch(this.config.saveUrl || '/signatures/save-signed-pdf', {
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
            window.location.href = '/';
            
        } catch (error) {
            console.error('❌ Erreur sauvegarde PDF:', error);
            this.showStatus(`Erreur sauvegarde: ${error.message}`, 'error');
            throw error;
        }
    }
    
    initializeEvents() {
        // Écouter les clics sur les boutons
        const signDocumentBtn = document.getElementById('signDocumentBtn');
        const initialDocumentBtn = document.getElementById('initialDocumentBtn');
        const savePdfBtn = document.getElementById('savePdfBtn');
        
        if (signDocumentBtn) {
            signDocumentBtn.addEventListener('click', () => this.signDocument());
        }
        
        if (initialDocumentBtn) {
            initialDocumentBtn.addEventListener('click', () => this.initialDocument());
        }
        
        if (savePdfBtn) {
            savePdfBtn.addEventListener('click', () => this.saveSignedPDF());
        }
    }
    
    async signDocument() {
        try {
            console.log('✍️ Signature du document...');
            this.showStatus('Signature du document...', 'info');
            
            // Ajouter la signature à la première page uniquement
            await this.addSignatureToPage(1);
            
            // Activer le bouton de sauvegarde
            const saveBtn = document.getElementById('savePdfBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.classList.remove('btn-secondary');
                saveBtn.classList.add('btn-success');
            }
            
            this.showStatus('Document signé - Prêt pour la sauvegarde', 'success');
            
        } catch (error) {
            console.error('❌ Erreur signature document:', error);
            this.showStatus(`Erreur signature: ${error.message}`, 'error');
        }
    }
    
    async initialDocument() {
        try {
            console.log('📝 Paraphe du document...');
            this.showStatus('Paraphe du document...', 'info');
            
            // Ajouter la signature à toutes les pages
            await this.addSignatureToAllPages();
            
            // Activer le bouton de sauvegarde
            const saveBtn = document.getElementById('savePdfBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.classList.remove('btn-secondary');
                saveBtn.classList.add('btn-success');
            }
            
            this.showStatus(`Document paraphé - ${this.totalPages} pages signées`, 'success');
            
        } catch (error) {
            console.error('❌ Erreur paraphe document:', error);
            this.showStatus(`Erreur paraphe: ${error.message}`, 'error');
        }
    }
    
    showStatus(message, type = 'info') {
        const statusElement = document.getElementById('signatureStatus');
        if (statusElement) {
            const timestamp = new Date().toLocaleTimeString();
            const statusContent = statusElement.querySelector('.status-content');
            const statusIcon = statusContent.querySelector('.status-icon i');
            const statusMessage = statusContent.querySelector('.status-message');
            
            // Mettre à jour l'icône selon le type
            const iconMap = {
                'success': 'fas fa-check-circle',
                'error': 'fas fa-exclamation-circle',
                'warning': 'fas fa-exclamation-triangle',
                'info': 'fas fa-info-circle'
            };
            
            if (statusIcon) {
                statusIcon.className = iconMap[type] || iconMap['info'];
            }
            
            // Mettre à jour le message
            if (statusMessage) {
                statusMessage.textContent = `[${timestamp}] ${message}`;
            }
            
            // Ajouter la classe de type au conteneur
            statusElement.className = `signature-status modern-status status-${type}`;
            
            // Animation de notification
            this.showToast(message, type);
        }
        console.log(`📊 Status: ${message}`);
    }
    
    showProgress(message, progress = 0) {
        const statusElement = document.getElementById('signatureStatus');
        if (statusElement) {
            const timestamp = new Date().toLocaleTimeString();
            const statusContent = statusElement.querySelector('.status-content');
            const statusMessage = statusContent.querySelector('.status-message');
            const statusProgress = statusContent.querySelector('.status-progress');
            const progressBar = statusProgress.querySelector('.progress-bar');
            
            // Mettre à jour le message
            if (statusMessage) {
                statusMessage.textContent = `[${timestamp}] ${message}`;
            }
            
            // Afficher/masquer la barre de progression
            if (progress > 0) {
                statusProgress.style.display = 'block';
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                }
            } else {
                statusProgress.style.display = 'none';
            }
            
            // Mettre à jour la classe de statut
            statusElement.className = 'signature-status modern-status status-info';
        }
    }
    
    // Méthodes utilitaires
    clearSignatures() {
        if (this.signatureOverlay) {
            this.signatureOverlay.innerHTML = '';
            this.signaturePositions = {};
            console.log('🧹 Signatures effacées');
            this.showStatus('Signatures effacées', 'info');
        }
    }
    
    getSignaturePositions() {
        return this.signaturePositions;
    }
    
    // Nouvelles fonctionnalités UX
    showToast(message, type = 'info', duration = 3000) {
        // Créer le conteneur de toast s'il n'existe pas
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        // Créer le toast
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const iconMap = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="${iconMap[type] || iconMap['info']}" style="font-size: 1.2rem;"></i>
                <div>
                    <div style="font-weight: 600; margin-bottom: 4px;">${this.getToastTitle(type)}</div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">${message}</div>
                </div>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Animation d'apparition
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Suppression automatique
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, duration);
    }
    
    getToastTitle(type) {
        const titles = {
            'success': 'Succès',
            'error': 'Erreur',
            'warning': 'Attention',
            'info': 'Information'
        };
        return titles[type] || 'Information';
    }
    
    // Raccourcis clavier
    initializeKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+S pour sauvegarder
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const saveBtn = document.getElementById('savePdfBtn');
                if (saveBtn && !saveBtn.disabled) {
                    this.saveSignedPDF();
                }
            }
            
            // Ctrl+1 pour signer
            if (e.ctrlKey && e.key === '1') {
                e.preventDefault();
                const signBtn = document.getElementById('signDocumentBtn');
                if (signBtn && !signBtn.disabled) {
                    this.signDocument();
                }
            }
            
            // Ctrl+2 pour parapher
            if (e.ctrlKey && e.key === '2') {
                e.preventDefault();
                const initialBtn = document.getElementById('initialDocumentBtn');
                if (initialBtn && !initialBtn.disabled) {
                    this.initialDocument();
                }
            }
            
            // Ctrl+E pour effacer
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                this.clearSignatures();
            }
            
            // F1 pour afficher l'aide
            if (e.key === 'F1') {
                e.preventDefault();
                this.showHelp();
            }
        });
    }
    
    showHelp() {
        const helpContent = `
            <div style="max-width: 500px;">
                <h4>🎯 Raccourcis clavier</h4>
                <div style="display: grid; gap: 8px; margin: 16px 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Signer le document</span>
                        <kbd>Ctrl</kbd> + <kbd>1</kbd>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Parapher le document</span>
                        <kbd>Ctrl</kbd> + <kbd>2</kbd>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Sauvegarder</span>
                        <kbd>Ctrl</kbd> + <kbd>S</kbd>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Effacer les signatures</span>
                        <kbd>Ctrl</kbd> + <kbd>E</kbd>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Afficher cette aide</span>
                        <kbd>F1</kbd>
                    </div>
                </div>
                <div style="margin-top: 16px; padding: 12px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #28a745;">
                    <strong>💡 Conseil :</strong> Utilisez la souris pour déplacer les signatures sur le document.
                </div>
            </div>
        `;
        
        this.showToast(helpContent, 'info', 8000);
    }
    
    // Amélioration des signatures draggables
    makeDraggable(element) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        let dragOffset = { x: 0, y: 0 };
        
        // Ajouter des indicateurs visuels
        element.addEventListener('mouseenter', () => {
            element.style.cursor = 'move';
            element.style.transform = 'scale(1.02)';
        });
        
        element.addEventListener('mouseleave', () => {
            if (!isDragging) {
                element.style.cursor = 'default';
                element.style.transform = 'scale(1)';
            }
        });
        
        element.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(element.style.left);
            startTop = parseInt(element.style.top);
            
            element.style.cursor = 'grabbing';
            element.style.transform = 'scale(1.05)';
            element.style.zIndex = '1000';
            
            // Ajouter un effet de sélection
            element.style.boxShadow = '0 8px 24px rgba(0, 123, 255, 0.4)';
            
            e.preventDefault();
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            const newLeft = startLeft + deltaX;
            const newTop = startTop + deltaY;
            
            // Limiter le déplacement dans les limites du conteneur
            const container = document.getElementById('pdfContainer');
            if (container) {
                const containerRect = container.getBoundingClientRect();
                const elementRect = element.getBoundingClientRect();
                
                const maxLeft = containerRect.width - elementRect.width;
                const maxTop = containerRect.height - elementRect.height;
                
                element.style.left = Math.max(0, Math.min(newLeft, maxLeft)) + 'px';
                element.style.top = Math.max(0, Math.min(newTop, maxTop)) + 'px';
            } else {
                element.style.left = newLeft + 'px';
                element.style.top = newTop + 'px';
            }
        });
        
        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                element.style.cursor = 'move';
                element.style.transform = 'scale(1.02)';
                element.style.zIndex = '10';
                element.style.boxShadow = '0 4px 12px rgba(0, 123, 255, 0.2)';
                
                // Sauvegarder la nouvelle position
                const pageNum = this.currentPage;
                this.signaturePositions[pageNum] = {
                    x: parseInt(element.style.left),
                    y: parseInt(element.style.top),
                    width: parseInt(element.style.width),
                    height: parseInt(element.style.height)
                };
                
                // Animation de confirmation
                this.showToast('Position de signature mise à jour', 'success', 2000);
            }
        });
    }
    
    // Amélioration de l'initialisation
    initializeEvents() {
        // Écouter les clics sur les boutons
        const signDocumentBtn = document.getElementById('signDocumentBtn');
        const initialDocumentBtn = document.getElementById('initialDocumentBtn');
        const savePdfBtn = document.getElementById('savePdfBtn');
        const clearSignaturesBtn = document.getElementById('clearSignaturesBtn');
        const helpBtn = document.getElementById('helpBtn');
        
        if (signDocumentBtn) {
            signDocumentBtn.addEventListener('click', () => this.signDocument());
        }
        
        if (initialDocumentBtn) {
            initialDocumentBtn.addEventListener('click', () => this.initialDocument());
        }
        
        if (savePdfBtn) {
            savePdfBtn.addEventListener('click', () => this.saveSignedPDF());
        }
        
        if (clearSignaturesBtn) {
            clearSignaturesBtn.addEventListener('click', () => {
                this.clearSignatures();
                this.showToast('Signatures effacées', 'info');
            });
        }
        
        if (helpBtn) {
            helpBtn.addEventListener('click', () => this.showHelp());
        }
        
        // Initialiser les raccourcis clavier
        this.initializeKeyboardShortcuts();
    }
}

// Exposer la classe globalement
window.PDFOverlaySignatureModule = PDFOverlaySignatureModule;

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initialisation du Module de Signature PDF Overlay');
    
    // Vérifier que la configuration est disponible
    if (window.documentConfig) {
        const signatureModule = new PDFOverlaySignatureModule(window.documentConfig);
        window.signatureModule = signatureModule;
        
        // Gérer l'affichage des raccourcis clavier
        const keyboardShortcuts = document.getElementById('keyboardShortcuts');
        let shortcutsVisible = false;
        
        // Afficher/masquer les raccourcis au survol
        if (keyboardShortcuts) {
            document.addEventListener('mouseenter', function(e) {
                if (e.target.closest('.keyboard-shortcuts')) {
                    shortcutsVisible = true;
                    keyboardShortcuts.classList.add('show');
                }
            });
            
            document.addEventListener('mouseleave', function(e) {
                if (e.target.closest('.keyboard-shortcuts')) {
                    shortcutsVisible = false;
                    setTimeout(() => {
                        if (!shortcutsVisible) {
                            keyboardShortcuts.classList.remove('show');
                        }
                    }, 2000);
                }
            });
        }
        
    } else {
        console.error('❌ Configuration non disponible');
    }
});
