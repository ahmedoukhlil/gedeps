/**
 * Module PDF Overlay Combiné - Signature & Paraphe
 * Gestion des signatures et paraphes combinés sur documents PDF
 */
class PDFOverlayCombinedModule {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 0;
        this.scale = 1.0;
        this.signatures = [];
        this.paraphes = [];
        this.actionType = 'sign_only';
        this.signatureCanvas = null;
        this.parapheCanvas = null;
        this.signatureCtx = null;
        this.parapheCtx = null;
        this.isDrawingSignature = false;
        this.isDrawingParaphe = false;
        this.liveSignatureData = null;
        this.liveParapheData = null;
    }

    async init() {
        try {
            await this.loadPDF();
            this.initializeEvents();
            this.initializeCanvases();
            this.showStatus('PDF chargé avec succès', 'success');
        } catch (error) {
            console.error('Erreur lors du chargement du PDF:', error);
            this.showStatus('Erreur lors du chargement du PDF: ' + error.message, 'error');
        }
    }

    async loadPDF() {
        try {
            const loadingTask = pdfjsLib.getDocument(this.config.pdfUrl);
            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;
            
            await this.renderPage(1);
            this.updatePageInfo();
            this.showStatus('PDF chargé avec succès', 'success');
        } catch (error) {
            throw new Error('Impossible de charger le PDF: ' + error.message);
        }
    }

    async renderPage(pageNum) {
        const container = document.getElementById(this.config.containerId);
        container.innerHTML = '';

        const page = await this.pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: this.scale });
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        canvas.style.width = '100%';
        canvas.style.height = 'auto';
        canvas.style.border = '1px solid #ddd';
        canvas.style.borderRadius = '8px';

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        await page.render(renderContext).promise;
        container.appendChild(canvas);

        // Ajouter les signatures et paraphes existants
        this.renderSignatures(container);
        this.renderParaphes(container);
    }

    renderSignatures(container) {
        this.signatures.forEach(signature => {
            if (signature.page === this.currentPage) {
                const signatureElement = this.createSignatureElement(signature);
                container.appendChild(signatureElement);
            }
        });
    }

    renderParaphes(container) {
        this.paraphes.forEach(paraphe => {
            if (paraphe.page === this.currentPage) {
                const parapheElement = this.createParapheElement(paraphe);
                container.appendChild(parapheElement);
            }
        });
    }

    createSignatureElement(signature) {
        const signatureDiv = document.createElement('div');
        signatureDiv.className = 'signature-overlay';
        signatureDiv.style.position = 'absolute';
        signatureDiv.style.left = signature.x + 'px';
        signatureDiv.style.top = signature.y + 'px';
        signatureDiv.style.width = '80px';
        signatureDiv.style.height = '40px';
        signatureDiv.style.border = '2px solid #28a745';
        signatureDiv.style.borderRadius = '4px';
        signatureDiv.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
        signatureDiv.style.cursor = 'move';
        signatureDiv.style.zIndex = '1000';
        signatureDiv.draggable = true;
        signatureDiv.dataset.signatureId = signature.id;

        const icon = document.createElement('i');
        icon.className = 'fas fa-pen-fancy';
        icon.style.color = '#28a745';
        icon.style.fontSize = '16px';
        icon.style.position = 'absolute';
        icon.style.top = '50%';
        icon.style.left = '50%';
        icon.style.transform = 'translate(-50%, -50%)';
        signatureDiv.appendChild(icon);

        this.makeDraggable(signatureDiv, 'signature');
        return signatureDiv;
    }

    createParapheElement(paraphe) {
        const parapheDiv = document.createElement('div');
        parapheDiv.className = 'paraphe-overlay';
        parapheDiv.style.position = 'absolute';
        parapheDiv.style.left = paraphe.x + 'px';
        parapheDiv.style.top = paraphe.y + 'px';
        parapheDiv.style.width = '40px';
        parapheDiv.style.height = '20px';
        parapheDiv.style.border = '2px solid #667eea';
        parapheDiv.style.borderRadius = '4px';
        parapheDiv.style.backgroundColor = 'rgba(102, 126, 234, 0.1)';
        parapheDiv.style.cursor = 'move';
        parapheDiv.style.zIndex = '1000';
        parapheDiv.draggable = true;
        parapheDiv.dataset.parapheId = paraphe.id;

        const icon = document.createElement('i');
        icon.className = 'fas fa-pen-nib';
        icon.style.color = '#667eea';
        icon.style.fontSize = '12px';
        icon.style.position = 'absolute';
        icon.style.top = '50%';
        icon.style.left = '50%';
        icon.style.transform = 'translate(-50%, -50%)';
        parapheDiv.appendChild(icon);

        this.makeDraggable(parapheDiv, 'paraphe');
        return parapheDiv;
    }

    makeDraggable(element, type) {
        let isDragging = false;
        let startX, startY, initialX, initialY;

        element.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            initialX = element.offsetLeft;
            initialY = element.offsetTop;
            element.style.zIndex = '1001';
            element.style.transform = 'scale(1.05)';
            element.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.3)';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            element.style.left = (initialX + deltaX) + 'px';
            element.style.top = (initialY + deltaY) + 'px';
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                element.style.zIndex = '1000';
                element.style.transform = 'scale(1)';
                element.style.boxShadow = 'none';
                
                // Mettre à jour la position
                if (type === 'signature') {
                    const signatureId = element.dataset.signatureId;
                    const signature = this.signatures.find(s => s.id === signatureId);
                    if (signature) {
                        signature.x = element.offsetLeft;
                        signature.y = element.offsetTop;
                    }
                } else if (type === 'paraphe') {
                    const parapheId = element.dataset.parapheId;
                    const paraphe = this.paraphes.find(p => p.id === parapheId);
                    if (paraphe) {
                        paraphe.x = element.offsetLeft;
                        paraphe.y = element.offsetTop;
                    }
                }
            }
        });
    }

    initializeEvents() {
        // Gestion du type d'action
        document.querySelectorAll('input[name="action_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.actionType = e.target.value;
                this.updateInterface();
            });
        });

        // Gestion des types de signature/paraphe
        document.querySelectorAll('input[name="signature_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.toggleLiveSignatureArea(e.target.value === 'live');
            });
        });

        document.querySelectorAll('input[name="paraphe_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.toggleLiveParapheArea(e.target.value === 'live');
            });
        });

        // Boutons de zoom
        document.getElementById(this.config.zoomInBtnId).addEventListener('click', () => {
            this.zoomIn();
        });

        document.getElementById(this.config.zoomOutBtnId).addEventListener('click', () => {
            this.zoomOut();
        });

        document.getElementById(this.config.resetZoomBtnId).addEventListener('click', () => {
            this.resetZoom();
        });

        // Soumission du formulaire
        document.getElementById(this.config.combinedFormId).addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
    }

    initializeCanvases() {
        // Canvas de signature
        this.signatureCanvas = document.getElementById(this.config.signatureCanvasId);
        if (this.signatureCanvas) {
            this.signatureCtx = this.signatureCanvas.getContext('2d');
            this.setupCanvasEvents(this.signatureCanvas, this.signatureCtx, 'signature');
        }

        // Canvas de paraphe
        this.parapheCanvas = document.getElementById(this.config.parapheCanvasId);
        if (this.parapheCanvas) {
            this.parapheCtx = this.parapheCanvas.getContext('2d');
            this.setupCanvasEvents(this.parapheCanvas, this.parapheCtx, 'paraphe');
        }
    }

    setupCanvasEvents(canvas, ctx, type) {
        if (!canvas) return;

        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            lastX = e.clientX - rect.left;
            lastY = e.clientY - rect.top;
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;

            const rect = canvas.getBoundingClientRect();
            const currentX = e.clientX - rect.left;
            const currentY = e.clientY - rect.top;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.strokeStyle = type === 'signature' ? '#28a745' : '#667eea';
            ctx.lineWidth = type === 'signature' ? 3 : 2;
            ctx.lineCap = 'round';
            ctx.stroke();

            lastX = currentX;
            lastY = currentY;
        });

        canvas.addEventListener('mouseup', () => {
            isDrawing = false;
        });

        canvas.addEventListener('mouseout', () => {
            isDrawing = false;
        });

        // Boutons de contrôle
        const clearBtn = document.getElementById(`clear${type.charAt(0).toUpperCase() + type.slice(1)}CanvasBtn`);
        const saveBtn = document.getElementById(`save${type.charAt(0).toUpperCase() + type.slice(1)}Btn`);

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                if (type === 'signature') {
                    this.liveSignatureData = canvas.toDataURL('image/png');
                    this.showStatus('Signature live sauvegardée', 'success');
                } else {
                    this.liveParapheData = canvas.toDataURL('image/png');
                    this.showStatus('Paraphe live sauvegardé', 'success');
                }
            });
        }
    }

    updateInterface() {
        const signatureConfig = document.getElementById('signatureConfig');
        const parapheConfig = document.getElementById('parapheConfig');
        const liveSignatureArea = document.getElementById('liveSignatureArea');
        const liveParapheArea = document.getElementById('liveParapheArea');

        // Masquer toutes les sections
        signatureConfig.style.display = 'none';
        parapheConfig.style.display = 'none';
        liveSignatureArea.style.display = 'none';
        liveParapheArea.style.display = 'none';

        // Afficher selon le type d'action
        switch (this.actionType) {
            case 'sign_only':
                signatureConfig.style.display = 'block';
                break;
            case 'paraphe_only':
                parapheConfig.style.display = 'block';
                break;
            case 'both':
                signatureConfig.style.display = 'block';
                parapheConfig.style.display = 'block';
                break;
        }

        // Mettre à jour le bouton de soumission
        const submitBtn = document.getElementById(this.config.submitBtnId);
        const submitText = submitBtn.querySelector('span');
        
        switch (this.actionType) {
            case 'sign_only':
                submitText.textContent = 'Signer le Document';
                break;
            case 'paraphe_only':
                submitText.textContent = 'Parapher le Document';
                break;
            case 'both':
                submitText.textContent = 'Signer & Parapher le Document';
                break;
        }
    }

    toggleLiveSignatureArea(show) {
        const area = document.getElementById('liveSignatureArea');
        area.style.display = show ? 'block' : 'none';
    }

    toggleLiveParapheArea(show) {
        const area = document.getElementById('liveParapheArea');
        area.style.display = show ? 'block' : 'none';
    }

    zoomIn() {
        this.scale = Math.min(this.scale * 1.2, 3.0);
        this.renderPage(this.currentPage);
        this.showStatus(`Zoom: ${Math.round(this.scale * 100)}%`, 'info');
    }

    zoomOut() {
        this.scale = Math.max(this.scale / 1.2, 0.5);
        this.renderPage(this.currentPage);
        this.showStatus(`Zoom: ${Math.round(this.scale * 100)}%`, 'info');
    }

    resetZoom() {
        this.scale = 1.0;
        this.renderPage(this.currentPage);
        this.showStatus('Zoom réinitialisé', 'info');
    }

    updatePageInfo() {
        const pageInfo = document.getElementById(this.config.pageInfoId);
        if (pageInfo) {
            pageInfo.textContent = `Page ${this.currentPage} sur ${this.totalPages}`;
        }
    }

    handleFormSubmit(e) {
        // Remplir les champs cachés
        document.getElementById(this.config.actionTypeInputId).value = this.actionType;
        
        // Récupérer les types sélectionnés
        const signatureType = document.querySelector('input[name="signature_type"]:checked')?.value || 'png';
        const parapheType = document.querySelector('input[name="paraphe_type"]:checked')?.value || 'png';
        
        document.getElementById(this.config.signatureTypeInputId).value = signatureType;
        document.getElementById(this.config.parapheTypeInputId).value = parapheType;
        
        // Données live
        document.getElementById(this.config.liveSignatureDataInputId).value = this.liveSignatureData || '';
        document.getElementById(this.config.liveParapheDataInputId).value = this.liveParapheData || '';
        
        // Positions
        if (this.signatures.length > 0) {
            const firstSignature = this.signatures[0];
            document.getElementById(this.config.signatureXInputId).value = firstSignature.x;
            document.getElementById(this.config.signatureYInputId).value = firstSignature.y;
        }
        
        if (this.paraphes.length > 0) {
            const firstParaphe = this.paraphes[0];
            document.getElementById(this.config.parapheXInputId).value = firstParaphe.x;
            document.getElementById(this.config.parapheYInputId).value = firstParaphe.y;
        }

        this.showStatus('Soumission du formulaire en cours...', 'info');
    }

    showStatus(message, type = 'info') {
        // Créer ou mettre à jour le toast
        let toast = document.querySelector('.toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'toast';
            document.body.appendChild(toast);
        }

        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${this.getToastIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;

        // Afficher le toast
        toast.classList.add('show');
        
        // Masquer après 3 secondes
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Styles CSS pour les toasts
const style = document.createElement('style');
style.textContent = `
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        padding: 16px;
        margin-bottom: 12px;
        border-left: 4px solid #17a2b8;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 9999;
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

    .toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toast-content i {
        font-size: 1.2rem;
    }

    .toast.success .toast-content i {
        color: #28a745;
    }

    .toast.error .toast-content i {
        color: #dc3545;
    }

    .toast.warning .toast-content i {
        color: #ffc107;
    }

    .toast.info .toast-content i {
        color: #17a2b8;
    }
`;
document.head.appendChild(style);
