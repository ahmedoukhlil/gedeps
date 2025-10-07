/**
 * Module de Paraphe PDF avec Overlay HTML
 * Gestion du paraphe électronique sur documents PDF
 */
class PDFOverlayParapheModule {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 0;
        this.scale = 1.0;
        this.paraphes = [];
        this.isAddingParaphe = false;
        this.parapheType = 'png';
        this.liveParapheData = null;
        this.canvas = null;
        this.ctx = null;
        this.isDrawing = false;
        this.lastX = 0;
        this.lastY = 0;
    }

    async init() {
        try {
            await this.loadPDF();
            this.initializeEvents();
            this.initializeCanvas();
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

        // Ajouter les paraphes existants
        this.renderParaphes(container);
    }

    renderParaphes(container) {
        this.paraphes.forEach(paraphe => {
            if (paraphe.page === this.currentPage) {
                const parapheElement = this.createParapheElement(paraphe);
                container.appendChild(parapheElement);
            }
        });
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

        // Ajouter l'icône de paraphe
        const icon = document.createElement('i');
        icon.className = 'fas fa-pen-nib';
        icon.style.color = '#667eea';
        icon.style.fontSize = '12px';
        icon.style.position = 'absolute';
        icon.style.top = '50%';
        icon.style.left = '50%';
        icon.style.transform = 'translate(-50%, -50%)';
        parapheDiv.appendChild(icon);

        // Rendre draggable
        this.makeDraggable(parapheDiv);

        return parapheDiv;
    }

    makeDraggable(element) {
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
            element.style.boxShadow = '0 4px 12px rgba(102, 126, 234, 0.3)';
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
                
                // Mettre à jour la position du paraphe
                const parapheId = element.dataset.parapheId;
                const paraphe = this.paraphes.find(p => p.id === parapheId);
                if (paraphe) {
                    paraphe.x = element.offsetLeft;
                    paraphe.y = element.offsetTop;
                }
            }
        });
    }

    initializeEvents() {
        // Bouton ajouter paraphe
        document.getElementById(this.config.addParapheBtnId).addEventListener('click', () => {
            this.addParaphe();
        });

        // Bouton effacer paraphes
        document.getElementById(this.config.clearParaphesBtnId).addEventListener('click', () => {
            this.clearParaphes();
        });

        // Bouton aperçu
        document.getElementById(this.config.previewBtnId).addEventListener('click', () => {
            this.previewParaphes();
        });

        // Type de paraphe
        document.querySelectorAll('input[name="paraphe_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.parapheType = e.target.value;
                this.toggleLiveParapheArea();
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
        document.getElementById(this.config.formId).addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
    }

    initializeCanvas() {
        this.canvas = document.getElementById(this.config.canvasId);
        if (this.canvas) {
            this.ctx = this.canvas.getContext('2d');
            this.setupCanvasEvents();
        }
    }

    setupCanvasEvents() {
        if (!this.canvas) return;

        this.canvas.addEventListener('mousedown', (e) => {
            this.isDrawing = true;
            const rect = this.canvas.getBoundingClientRect();
            this.lastX = e.clientX - rect.left;
            this.lastY = e.clientY - rect.top;
        });

        this.canvas.addEventListener('mousemove', (e) => {
            if (!this.isDrawing) return;

            const rect = this.canvas.getBoundingClientRect();
            const currentX = e.clientX - rect.left;
            const currentY = e.clientY - rect.top;

            this.ctx.beginPath();
            this.ctx.moveTo(this.lastX, this.lastY);
            this.ctx.lineTo(currentX, currentY);
            this.ctx.strokeStyle = '#667eea';
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.stroke();

            this.lastX = currentX;
            this.lastY = currentY;
        });

        this.canvas.addEventListener('mouseup', () => {
            this.isDrawing = false;
        });

        this.canvas.addEventListener('mouseout', () => {
            this.isDrawing = false;
        });

        // Bouton effacer canvas
        document.getElementById(this.config.clearCanvasBtnId).addEventListener('click', () => {
            this.clearCanvas();
        });

        // Bouton sauvegarder paraphe
        document.getElementById(this.config.saveParapheBtnId).addEventListener('click', () => {
            this.saveLiveParaphe();
        });
    }

    addParaphe() {
        if (this.parapheType === 'png') {
            this.addPNGParaphe();
        } else {
            this.showLiveParapheArea();
        }
    }

    addPNGParaphe() {
        const parapheId = 'paraphe_' + Date.now();
        const paraphe = {
            id: parapheId,
            type: 'png',
            x: 50,
            y: 50,
            page: this.currentPage,
            width: 40,
            height: 20
        };

        this.paraphes.push(paraphe);
        this.renderParaphes(document.getElementById(this.config.containerId));
        this.showStatus('Paraphe PNG ajouté', 'success');
    }

    showLiveParapheArea() {
        const area = document.getElementById(this.config.liveParapheAreaId);
        area.style.display = 'block';
        this.showStatus('Zone de paraphe live activée', 'info');
    }

    toggleLiveParapheArea() {
        const area = document.getElementById(this.config.liveParapheAreaId);
        if (this.parapheType === 'live') {
            area.style.display = 'block';
        } else {
            area.style.display = 'none';
        }
    }

    clearCanvas() {
        if (this.ctx) {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }
    }

    saveLiveParaphe() {
        if (this.canvas) {
            this.liveParapheData = this.canvas.toDataURL('image/png');
            this.showStatus('Paraphe live sauvegardé', 'success');
        }
    }

    clearParaphes() {
        this.paraphes = [];
        const container = document.getElementById(this.config.containerId);
        const overlays = container.querySelectorAll('.paraphe-overlay');
        overlays.forEach(overlay => overlay.remove());
        this.showStatus('Tous les paraphes ont été effacés', 'info');
    }

    previewParaphes() {
        this.showStatus(`Aperçu: ${this.paraphes.length} paraphe(s) sur la page ${this.currentPage}`, 'info');
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
        document.getElementById(this.config.parapheTypeInputId).value = this.parapheType;
        document.getElementById(this.config.liveParapheDataInputId).value = this.liveParapheData || '';
        
        // Position du premier paraphe
        if (this.paraphes.length > 0) {
            const firstParaphe = this.paraphes[0];
            document.getElementById(this.config.parapheXInputId).value = firstParaphe.x;
            document.getElementById(this.config.parapheYInputId).value = firstParaphe.y;
        }

        // Positions de tous les paraphes
        document.getElementById(this.config.paraphePositionsInputId).value = JSON.stringify(this.paraphes);
        document.getElementById(this.config.totalPagesInputId).value = this.totalPages;

        this.showStatus('Soumission du paraphe en cours...', 'info');
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
