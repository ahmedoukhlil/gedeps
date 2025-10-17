/**
 * Module PDF Overlay Unifié - VERSION MOBILE-FIRST COMPLÈTE
 * Refonte totale avec approche mobile-first
 */

class PDFOverlayUnifiedModule {
    constructor(config) {
        this.config = config;
        
        // ========================================
        // PHASE 1: DÉTECTION DEVICE (UNE SEULE FOIS)
        // ========================================
        this.device = this.detectDevice();
        console.log('📱 Device détecté:', this.device);
        
        // État centralisé
        this.state = {
            currentPage: 1,
            totalPages: 0,
            scale: this.getInitialScale(),
            isPositioning: false,
            positioningType: null,
            activeElement: null,
            isProcessing: false,
            isResizing: false,
            isRendering: false
        };
        
        // Collections d'éléments
        this.signatures = [];
        this.paraphes = [];
        this.cachets = [];
        
        // Cache et cleanup
        this.cache = {
            pages: new Map(),
            rects: null,
            rectTime: 0,
            images: new Map()
        };
        
        this.cleanup = [];
        
        // URLs utilisateur
        this.userSignatureUrl = null;
        this.userParapheUrl = null;
        this.userCachetUrl = null;
        
        // Logger conditionnel
        this.debug = config.debug || false;
    }

    // ========================================
    // DÉTECTION DEVICE COMPLÈTE
    // ========================================
    detectDevice() {
        const ua = navigator.userAgent;
        const isMobileUA = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
        const isTablet = /iPad|Android/i.test(ua) && window.innerWidth >= 768;
        const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        const width = window.innerWidth;
        const height = window.innerHeight;

        // Déterminer le type
        let type = 'desktop';
        if (isMobileUA) {
            type = isTablet ? 'tablet' : 'phone';
        }

        // Taille d'écran
        let size = 'xl';
        if (width < 576) size = 'xs';
        else if (width < 768) size = 'sm';
        else if (width < 992) size = 'md';
        else if (width < 1200) size = 'lg';

        return {
            type,
            isMobile: type !== 'desktop',
            isTouch,
            viewport: { width, height, size },
            capabilities: {
                maxTouchPoints: navigator.maxTouchPoints,
                hasHover: window.matchMedia('(hover: hover)').matches,
                hasFinePointer: window.matchMedia('(pointer: fine)').matches,
                hasVibration: 'vibrate' in navigator
            },
            orientation: width > height ? 'landscape' : 'portrait',
            pixelRatio: window.devicePixelRatio || 1
        };
    }

    getInitialScale() {
        // Mobile: adapter à la largeur
        if (this.device.isMobile) {
            switch (this.device.viewport.size) {
                case 'xs': return 0.5;  // Phone portrait
                case 'sm': return 0.6;  // Phone landscape
                case 'md': return 0.75; // Tablet
                default: return 0.8;
            }
        }
        return 1.0; // Desktop
    }

    // ========================================
    // INITIALISATION
    // ========================================
    async init() {
        try {
            this.log('🚀 Initialisation module PDF...');
            
            // Charger le PDF
            await this.loadPDF();
            
            // Précharger les images en parallèle
            await this.preloadUserAssets();
            
            // Initialiser les événements selon le device
            this.initializeEvents();
            
            // Initialiser les canvas si nécessaire
            if (!this.config.isReadOnly) {
                this.initializeCanvases();
            }
            
            // Mettre à jour l'interface
            this.updateInterface();
            
            // Mode lecture seule
            if (this.config.isReadOnly) {
                this.disableAllInteractions();
            }
            
            this.showToast('PDF chargé avec succès', 'success');
            
        } catch (error) {
            console.error('❌ Erreur initialisation:', error);
            this.showToast('Erreur lors du chargement: ' + error.message, 'error');
        }
    }

    async loadPDF() {
        const loadingTask = pdfjsLib.getDocument(this.config.pdfUrl);
        this.pdfDoc = await loadingTask.promise;
        this.state.totalPages = this.pdfDoc.numPages;
        
        await this.renderPage(this.state.currentPage);
        this.updatePageInfo();
        
        // Événement pour l'extérieur
        document.dispatchEvent(new CustomEvent('pdfLoaded', {
            detail: { 
                totalPages: this.state.totalPages,
                currentPage: this.state.currentPage 
            }
        }));
    }

    async preloadUserAssets() {
        const promises = [
            this.loadUserSignature(),
            this.loadUserParaphe(),
            this.loadUserCachet()
        ];
        
        await Promise.allSettled(promises);
    }

    async loadUserSignature() {
        try {
            const response = await fetch('/signatures/user-signature', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.signature_url) {
                    this.userSignatureUrl = data.signature_url;
                    await this.preloadImage(data.signature_url, 'signature');
                }
            }
        } catch (error) {
            this.log('⚠️ Signature non disponible');
        }
    }

    async loadUserParaphe() {
        try {
            const response = await fetch('/signatures/user-paraphe', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.paraphe_url) {
                    this.userParapheUrl = data.paraphe_url;
                    await this.preloadImage(data.paraphe_url, 'paraphe');
                }
            }
        } catch (error) {
            this.log('⚠️ Paraphe non disponible');
        }
    }

    async loadUserCachet() {
        try {
            const url = this.config.cachetUrl || '/signatures/user-cachet';
            const response = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && (data.cachet_url || data.cachetUrl)) {
                    this.userCachetUrl = data.cachet_url || data.cachetUrl;
                    await this.preloadImage(this.userCachetUrl, 'cachet');
                }
            }
        } catch (error) {
            this.log('⚠️ Cachet non disponible');
        }
    }

    async preloadImage(url, type) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                this.cache.images.set(type, img);
                resolve(img);
            };
            img.onerror = reject;
            img.src = url;
        });
    }

    // ========================================
    // ÉVÉNEMENTS - MOBILE FIRST
    // ========================================
    initializeEvents() {
        if (this.device.isMobile) {
            this.initMobileEvents();
        } else {
            this.initDesktopEvents();
        }
        
        // Événements communs
        this.initCommonEvents();
    }

    initMobileEvents() {
        this.log('📱 Initialisation événements mobile');

        // Boutons d'action - TOUCH PRIORITY
        this.attachMobileButton(this.config.addSignatureBtnId, () => this.startPositioning('signature'));
        this.attachMobileButton(this.config.addParapheBtnId, () => this.startPositioning('paraphe'));
        this.attachMobileButton(this.config.addCachetBtnId, () => this.startPositioning('cachet'));
        this.attachMobileButton(this.config.clearAllBtnId, () => this.clearAll());
        this.attachMobileButton(this.config.submitBtnId, () => this.submitForm());

        // Navigation
        this.attachMobileButton(this.config.prevPageBtnId, () => this.previousPage());
        this.attachMobileButton(this.config.nextPageBtnId, () => this.nextPage());
        this.attachMobileButton('firstPageBtn', () => this.goToPage(1));
        this.attachMobileButton('lastPageBtn', () => this.goToPage(this.state.totalPages));

        // Boutons de zoom
        this.attachMobileButton('zoomInBtn', () => this.zoomIn());
        this.attachMobileButton('zoomOutBtn', () => this.zoomOut());
        this.attachMobileButton('resetZoomBtn', () => this.resetZoom());

        // Canvas PDF - Touch gestures
        this.initMobilePDFGestures();
    }

    attachMobileButton(btnId, handler) {
        if (!btnId) return;
        
        const btn = document.getElementById(btnId);
        if (!btn) return;
        
        // Touch priority
        const touchHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Feedback haptique
            if (this.device.capabilities.hasVibration) {
                navigator.vibrate(50);
            }
            
            handler();
        };
        
        btn.addEventListener('touchstart', touchHandler, { passive: false });
        
        // Fallback click
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            handler();
        });
        
        this.cleanup.push(() => {
            btn.removeEventListener('touchstart', touchHandler);
            btn.removeEventListener('click', handler);
        });
    }

    initMobilePDFGestures() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        let touchState = {
            startTime: 0,
            startPos: null,
            isDragging: false,
            isPinching: false,
            lastDistance: 0,
            activeElement: null,
            dragStartElementPos: null
        };

        const touchStart = (e) => {
            touchState.startTime = Date.now();
            touchState.startPos = this.getTouchCoords(e);
            
            // Mode positionnement: placer l'élément
            if (this.state.isPositioning) {
                e.preventDefault();
                this.placeElementAtTouch(e);
                return;
            }
            
            // Pinch zoom (2 doigts)
            if (e.touches.length === 2) {
                e.preventDefault();
                touchState.isPinching = true;
                touchState.lastDistance = this.getTouchDistance(e);
                return;
            }
            
            // Vérifier si on touche un élément
            const element = this.findElementAtTouch(e);
            if (element) {
                e.preventDefault();
                e.stopPropagation();
                touchState.isDragging = true;
                touchState.activeElement = element;
                touchState.dragStartElementPos = { x: element.x, y: element.y };
                this.highlightElement(element);
                
                this.log('🎯 Début drag:', element);
            }
        };

        const touchMove = (e) => {
            // Pinch zoom
            if (touchState.isPinching && e.touches.length === 2) {
                e.preventDefault();
                const distance = this.getTouchDistance(e);
                const scale = distance / touchState.lastDistance;
                this.handlePinchZoom(scale);
                touchState.lastDistance = distance;
                return;
            }
            
            // Drag élément
            if (touchState.isDragging && touchState.activeElement) {
                e.preventDefault();
                e.stopPropagation();
                
                const coords = this.getTouchCoords(e);
                const delta = {
                    x: coords.x - touchState.startPos.x,
                    y: coords.y - touchState.startPos.y
                };
                
                // Nouvelle position = position initiale + déplacement
                const newX = touchState.dragStartElementPos.x + delta.x;
                const newY = touchState.dragStartElementPos.y + delta.y;
                
                this.updateElementPosition(touchState.activeElement, newX, newY);
                
                this.log('📍 Drag move:', { coords, delta, newPos: { x: newX, y: newY } });
            }
        };

        const touchEnd = (e) => {
            // Tap simple
            if (!touchState.isDragging && !touchState.isPinching) {
                const duration = Date.now() - touchState.startTime;
                if (duration < 300) {
                    this.handleTap(e);
                }
            }
            
            // Fin du drag
            if (touchState.isDragging) {
                this.log('✅ Fin drag');
                
                // Feedback haptique
                if (this.device.capabilities.hasVibration) {
                    navigator.vibrate(50);
                }
                
                this.updateFormData();
            }
            
            // Reset state
            if (touchState.activeElement) {
                this.unhighlightElement(touchState.activeElement);
            }
            
            touchState.isDragging = false;
            touchState.isPinching = false;
            touchState.activeElement = null;
            touchState.dragStartElementPos = null;
        };

        container.addEventListener('touchstart', touchStart, { passive: false });
        container.addEventListener('touchmove', touchMove, { passive: false });
        container.addEventListener('touchend', touchEnd, { passive: false });
        container.addEventListener('touchcancel', touchEnd, { passive: false });
        
        this.cleanup.push(() => {
            container.removeEventListener('touchstart', touchStart);
            container.removeEventListener('touchmove', touchMove);
            container.removeEventListener('touchend', touchEnd);
            container.removeEventListener('touchcancel', touchEnd);
        });
    }

    initDesktopEvents() {
        this.log('🖥️ Initialisation événements desktop');

        // Boutons - Click simple
        this.attachDesktopButton(this.config.addSignatureBtnId, () => this.startPositioningOverlay('signature'));
        this.attachDesktopButton(this.config.addParapheBtnId, () => this.startPositioningOverlay('paraphe'));
        this.attachDesktopButton(this.config.addCachetBtnId, () => this.startPositioningOverlay('cachet'));
        this.attachDesktopButton(this.config.clearAllBtnId, () => this.clearAll());
        this.attachDesktopButton(this.config.submitBtnId, () => this.submitForm());

        // Navigation
        this.attachDesktopButton(this.config.prevPageBtnId, () => this.previousPage());
        this.attachDesktopButton(this.config.nextPageBtnId, () => this.nextPage());
        this.attachDesktopButton('firstPageBtn', () => this.goToPage(1));
        this.attachDesktopButton('lastPageBtn', () => this.goToPage(this.state.totalPages));

        // Boutons de zoom
        this.attachDesktopButton('zoomInBtn', () => this.zoomIn());
        this.attachDesktopButton('zoomOutBtn', () => this.zoomOut());
        this.attachDesktopButton('resetZoomBtn', () => this.resetZoom());

        // Drag & drop desktop sur le container
        this.initDesktopDragDrop();

        // Zoom par molette (Ctrl+Scroll)
        this.initDesktopWheelZoom();
    }

    initDesktopDragDrop() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        let dragState = {
            isDragging: false,
            activeElement: null,
            startPos: null,
            dragStartElementPos: null
        };

        const mouseDown = (e) => {
            // Vérifier si on clique sur un élément
            const element = this.findElementAtMouse(e);
            if (element) {
                e.preventDefault();
                dragState.isDragging = true;
                dragState.activeElement = element;
                dragState.startPos = { x: e.clientX, y: e.clientY };
                dragState.dragStartElementPos = { x: element.x, y: element.y };
                this.highlightElement(element);
                
                this.log('🎯 Début drag desktop:', element);
            }
        };

        const mouseMove = (e) => {
            if (!dragState.isDragging || !dragState.activeElement) return;
            
            e.preventDefault();
            
            const containerRect = container.getBoundingClientRect();
            const currentPos = {
                x: e.clientX - containerRect.left,
                y: e.clientY - containerRect.top
            };
            
            const startPosRelative = {
                x: dragState.startPos.x - containerRect.left,
                y: dragState.startPos.y - containerRect.top
            };
            
            const delta = {
                x: currentPos.x - startPosRelative.x,
                y: currentPos.y - startPosRelative.y
            };
            
            const newX = dragState.dragStartElementPos.x + delta.x;
            const newY = dragState.dragStartElementPos.y + delta.y;
            
            this.updateElementPosition(dragState.activeElement, newX, newY);
        };

        const mouseUp = (e) => {
            if (dragState.isDragging) {
                this.log('✅ Fin drag desktop');
                this.updateFormData();
            }
            
            if (dragState.activeElement) {
                this.unhighlightElement(dragState.activeElement);
            }
            
            dragState.isDragging = false;
            dragState.activeElement = null;
            dragState.startPos = null;
            dragState.dragStartElementPos = null;
        };

        container.addEventListener('mousedown', mouseDown);
        document.addEventListener('mousemove', mouseMove);
        document.addEventListener('mouseup', mouseUp);
        
        this.cleanup.push(() => {
            container.removeEventListener('mousedown', mouseDown);
            document.removeEventListener('mousemove', mouseMove);
            document.removeEventListener('mouseup', mouseUp);
        });
    }

    initDesktopWheelZoom() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        const wheelHandler = (e) => {
            // Zoom uniquement avec Ctrl+Scroll
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();

                const delta = e.deltaY;
                const zoomFactor = delta > 0 ? 0.9 : 1.1; // Zoom out ou zoom in

                const newScale = this.state.scale * zoomFactor;
                this.state.scale = Math.max(0.3, Math.min(3.0, newScale));

                // Invalider le cache et re-rendre
                this.cache.pages.clear();
                this.invalidateConversionCache();
                this.renderPage(this.state.currentPage);

                this.showToast(`Zoom: ${Math.round(this.state.scale * 100)}%`, 'info');
            }
        };

        container.addEventListener('wheel', wheelHandler, { passive: false });

        this.cleanup.push(() => {
            container.removeEventListener('wheel', wheelHandler);
        });
    }

    attachDesktopButton(btnId, handler) {
        if (!btnId) return;

        const btn = document.getElementById(btnId);
        if (!btn) return;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            handler();
        });

        this.cleanup.push(() => {
            btn.removeEventListener('click', handler);
        });
    }

    initCommonEvents() {
        // Resize - throttled
        const resizeHandler = this.throttle(() => {
            this.handleResize();
        }, 250);
        
        window.addEventListener('resize', resizeHandler);
        
        // Orientation change (mobile)
        if (this.device.isMobile) {
            window.addEventListener('orientationchange', () => {
                setTimeout(() => this.handleOrientationChange(), 300);
            });
        }
        
        this.cleanup.push(() => {
            window.removeEventListener('resize', resizeHandler);
        });
    }

    // ========================================
    // POSITIONNEMENT - MOBILE FIRST
    // ========================================
    startPositioning(type) {
        if (this.state.isProcessing) return;
        
        const url = this.getUrlForType(type);
        if (!url) {
            this.showToast(`Aucun ${type} configuré`, 'error');
            return;
        }
        
        this.state.isPositioning = true;
        this.state.positioningType = type;
        
        // Mobile: Message + attente touch
        if (this.device.isMobile) {
            this.showToast(`Touchez l'écran pour placer le ${type}`, 'info');
        } else {
            // Desktop: Overlay
            this.showOverlay(type);
        }
    }

    startPositioningOverlay(type) {
        const url = this.getUrlForType(type);
        if (!url) {
            this.showToast(`Aucun ${type} configuré`, 'error');
            return;
        }
        
        this.showOverlay(type);
    }

    showOverlay(type) {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        const overlay = document.createElement('div');
        overlay.id = 'positioning-overlay';
        overlay.className = 'pdf-positioning-overlay';
        overlay.innerHTML = `
            <div class="overlay-message">
                <i class="fas fa-mouse-pointer"></i>
                <p>Cliquez pour positionner le ${type}</p>
            </div>
        `;
        
        container.style.position = 'relative';
        container.appendChild(overlay);

        const clickHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.placeElement(type, x, y);
            overlay.remove();
        };

        overlay.addEventListener('click', clickHandler);
        
        this.cleanup.push(() => overlay.remove());
    }

    placeElementAtTouch(e) {
        const coords = this.getTouchCoords(e);
        const type = this.state.positioningType;
        
        this.placeElement(type, coords.x, coords.y);
        
        // Feedback haptique
        if (this.device.capabilities.hasVibration) {
            navigator.vibrate([50, 100, 50]);
        }
        
        this.state.isPositioning = false;
        this.state.positioningType = null;
    }

    placeElement(type, x, y) {
        const size = this.getElementSize(type);
        const url = this.getUrlForType(type);
        
        const element = {
            id: Date.now(),
            type,
            page: this.state.currentPage,
            x: x - size.width / 2,
            y: y - size.height / 2,
            width: size.width,
            height: size.height,
            url
        };

        // Ajouter à la collection appropriée
        if (type === 'signature') this.signatures.push(element);
        else if (type === 'paraphe') this.paraphes.push(element);
        else if (type === 'cachet') this.cachets.push(element);
        
        this.renderElements();
        this.updateFormData();
        this.showToast(`${type} ajouté`, 'success');
    }

    getElementSize(type) {
        const baseWidth = type === 'signature' ? 120 : type === 'cachet' ? 100 : 80;
        
        // Adapter à la taille d'écran
        let scale = 1;
        if (this.device.isMobile) {
            switch (this.device.viewport.size) {
                case 'xs': scale = 0.6; break;
                case 'sm': scale = 0.7; break;
                case 'md': scale = 0.85; break;
            }
        }
        
        const width = baseWidth * scale;
        const ratio = type === 'cachet' ? 0.8 : 0.4;
        
        return {
            width,
            height: width * ratio
        };
    }

    getUrlForType(type) {
        switch (type) {
            case 'signature': return this.userSignatureUrl || this.config.signatureUrl;
            case 'paraphe': return this.userParapheUrl || this.config.parapheUrl;
            case 'cachet': return this.userCachetUrl || this.config.cachetUrl;
            default: return null;
        }
    }

    // ========================================
    // GESTION DES ÉLÉMENTS
    // ========================================
    findElementAtTouch(e) {
        const coords = this.getTouchCoords(e);
        return this.findElementAtCoords(coords);
    }

    findElementAtMouse(e) {
        const container = document.getElementById(this.config.containerId);
        const rect = container.getBoundingClientRect();
        const coords = {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
        return this.findElementAtCoords(coords);
    }

    findElementAtCoords(coords) {
        // Chercher dans toutes les collections
        const all = [
            ...this.signatures.map(s => ({ ...s, type: 'signature' })),
            ...this.paraphes.map(p => ({ ...p, type: 'paraphe' })),
            ...this.cachets.map(c => ({ ...c, type: 'cachet' }))
        ].filter(el => el.page === this.state.currentPage);
        
        // Trouver l'élément touché (en priorité le plus petit pour éviter les overlaps)
        const candidates = all.filter(el => {
            return coords.x >= el.x && coords.x <= el.x + el.width &&
                   coords.y >= el.y && coords.y <= el.y + el.height;
        });
        
        // Retourner le plus petit (le plus récent ou le plus précis)
        return candidates.length > 0 ? candidates[candidates.length - 1] : null;
    }

    updateElementPosition(element, newX, newY) {
        const collection = this.getCollection(element.type);
        const item = collection.find(i => i.id === element.id);
        
        if (item) {
            // Contraindre aux limites du container
            const container = document.getElementById(this.config.containerId);
            const rect = container.getBoundingClientRect();
            
            item.x = Math.max(0, Math.min(newX, rect.width - item.width));
            item.y = Math.max(0, Math.min(newY, rect.height - item.height));
            
            // Mettre à jour l'affichage
            this.updateElementDiv(item);
        }
    }

    updateElementDiv(element) {
        const div = document.querySelector(`[data-element-id="${element.id}"]`);
        if (div) {
            div.style.left = element.x + 'px';
            div.style.top = element.y + 'px';
        }
    }

    moveElement(element, coords) {
        const collection = this.getCollection(element.type);
        const item = collection.find(i => i.id === element.id);
        
        if (item) {
            const size = this.getElementSize(element.type);
            
            // Centrer l'élément sur le doigt/curseur
            const newX = coords.x - size.width / 2;
            const newY = coords.y - size.height / 2;
            
            this.updateElementPosition(element, newX, newY);
        }
    }

    highlightElement(element) {
        const div = document.querySelector(`[data-element-id="${element.id}"]`);
        if (div) {
            div.style.transform = 'scale(1.1)';
            div.style.boxShadow = '0 8px 16px rgba(0,0,0,0.3)';
            div.style.zIndex = '1001';
            div.style.opacity = '0.9';
            
            // Curseur approprié
            if (!this.device.isMobile) {
                div.style.cursor = 'grabbing';
            }
        }
    }

    unhighlightElement(element) {
        const div = document.querySelector(`[data-element-id="${element.id}"]`);
        if (div) {
            div.style.transform = 'scale(1)';
            div.style.boxShadow = 'none';
            div.style.zIndex = '1000';
            div.style.opacity = '1';
            
            // Restaurer le curseur
            if (!this.device.isMobile) {
                div.style.cursor = 'move';
            }
        }
    }

    getCollection(type) {
        switch (type) {
            case 'signature': return this.signatures;
            case 'paraphe': return this.paraphes;
            case 'cachet': return this.cachets;
            default: return [];
        }
    }

    // ========================================
    // RENDU
    // ========================================
    async renderPage(pageNum) {
        // Protection contre les rendus simultanés
        if (this.state.isRendering) {
            this.log('⏳ Rendu déjà en cours, ignoré');
            return;
        }

        const container = document.getElementById(this.config.containerId);
        if (!container) {
            console.error('❌ Container PDF introuvable');
            return;
        }

        this.state.isRendering = true;

        try {
            // Vérifier le cache
            const quality = this.getQuality();
            const cacheKey = `${pageNum}_${this.state.scale}_${quality}`;

            if (this.cache.pages.has(cacheKey)) {
                const cached = this.cache.pages.get(cacheKey);

                // Vider uniquement le canvas, pas les overlays
                const existingCanvas = container.querySelector('canvas');
                if (existingCanvas) {
                    existingCanvas.remove();
                }

                container.insertBefore(cached.cloneNode(true), container.firstChild);
                this.renderElements();
                this.log('✅ Page chargée depuis le cache');
                return;
            }

            // Rendu normal - Supprimer uniquement le canvas existant
            const existingCanvas = container.querySelector('canvas');
            if (existingCanvas) {
                existingCanvas.remove();
            }

            const page = await this.pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: this.state.scale });

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', {
                alpha: false,
                desynchronized: this.device.isMobile
            });

            // Pixel ratio adapté
            const pixelRatio = this.device.isMobile ?
                Math.min(this.device.pixelRatio, 2) :
                this.device.pixelRatio;

            canvas.width = viewport.width * pixelRatio;
            canvas.height = viewport.height * pixelRatio;
            canvas.style.width = '100%';
            canvas.style.height = 'auto';
            canvas.style.maxWidth = '100%';
            canvas.style.display = 'block';
            canvas.style.margin = '0 auto';

            ctx.scale(pixelRatio, pixelRatio);

            await page.render({
                canvasContext: ctx,
                viewport,
                intent: this.device.isMobile ? 'print' : 'display'
            }).promise;

            // Insérer le canvas au début du container (avant les overlays)
            container.insertBefore(canvas, container.firstChild);

            // Mettre en cache (limité)
            if (this.cache.pages.size >= 3) {
                const firstKey = this.cache.pages.keys().next().value;
                this.cache.pages.delete(firstKey);
            }
            this.cache.pages.set(cacheKey, canvas);

            this.renderElements();
            this.log('✅ Page rendue avec succès');

        } catch (error) {
            console.error('❌ Erreur lors du rendu de la page:', error);
            this.showToast('Erreur lors du rendu de la page', 'error');
        } finally {
            this.state.isRendering = false;
        }
    }

    getQuality() {
        if (this.device.type === 'phone') return 'medium';
        if (this.device.type === 'tablet') return 'high';
        return 'ultra';
    }

    renderElements() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        // Supprimer les anciens éléments
        container.querySelectorAll('.element-overlay').forEach(el => el.remove());

        // Afficher les éléments de la page courante
        const all = [
            ...this.signatures.map(s => ({ ...s, type: 'signature', color: '#28a745' })),
            ...this.paraphes.map(p => ({ ...p, type: 'paraphe', color: '#667eea' })),
            ...this.cachets.map(c => ({ ...c, type: 'cachet', color: '#8B5CF6' }))
        ].filter(el => el.page === this.state.currentPage);

        all.forEach(element => {
            const div = this.createElementDiv(element);
            container.appendChild(div);
        });
    }

    createElementDiv(element) {
        const div = document.createElement('div');
        div.className = 'element-overlay';
        div.dataset.elementId = element.id;
        div.style.cssText = `
            position: absolute;
            left: ${element.x}px;
            top: ${element.y}px;
            width: ${element.width}px;
            height: ${element.height}px;
            border: 2px solid ${element.color};
            border-radius: 4px;
            background: ${element.color}10;
            z-index: 1000;
            transition: all 0.2s ease;
            ${this.device.isMobile ? 'touch-action: none;' : 'cursor: move;'}
        `;

        if (element.url) {
            const img = document.createElement('img');
            img.src = element.url;
            img.style.cssText = `
                width: 100%;
                height: 100%;
                object-fit: contain;
                pointer-events: none;
            `;
            div.appendChild(img);
        }

        return div;
    }

    // ========================================
    // UTILITAIRES TOUCH
    // ========================================
    getTouchCoords(e) {
        const touch = e.touches?.[0] || e.changedTouches?.[0];
        if (!touch) return { x: 0, y: 0 };

        const container = document.getElementById(this.config.containerId);
        const rect = container.getBoundingClientRect();

        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    getTouchDistance(e) {
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        
        return Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
    }

    handleTap(e) {
        const coords = this.getTouchCoords(e);
        const width = this.device.viewport.width;
        
        // Navigation par zones
        if (coords.x < width * 0.2) {
            this.previousPage();
        } else if (coords.x > width * 0.8) {
            this.nextPage();
        }
    }

    handlePinchZoom(scale) {
        const newScale = this.state.scale * scale;
        const clampedScale = Math.max(0.3, Math.min(3.0, newScale));

        // Éviter les re-rendus si le changement est trop petit
        if (Math.abs(clampedScale - this.state.scale) > 0.01) {
            this.state.scale = clampedScale;

            // Throttle le rendu pour éviter trop d'appels
            if (!this._pinchZoomTimeout) {
                this._pinchZoomTimeout = setTimeout(() => {
                    this.cache.pages.clear();
                    this.invalidateConversionCache();
                    this.renderPage(this.state.currentPage);
                    this._pinchZoomTimeout = null;
                }, 100);
            }
        }
    }

    // ========================================
    // ZOOM
    // ========================================
    zoomIn() {
        const newScale = this.state.scale * 1.2;
        this.setZoom(newScale);
    }

    zoomOut() {
        const newScale = this.state.scale / 1.2;
        this.setZoom(newScale);
    }

    resetZoom() {
        const initialScale = this.getInitialScale();
        this.setZoom(initialScale);
    }

    setZoom(newScale) {
        const clampedScale = Math.max(0.3, Math.min(3.0, newScale));

        if (clampedScale === this.state.scale) {
            this.showToast('Limite de zoom atteinte', 'warning');
            return;
        }

        this.state.scale = clampedScale;

        // Invalider le cache et re-rendre
        this.cache.pages.clear();
        this.invalidateConversionCache();
        this.renderPage(this.state.currentPage);

        const percentage = Math.round(this.state.scale * 100);
        this.showToast(`Zoom: ${percentage}%`, 'info');

        // Mettre à jour l'affichage du zoom si un élément existe
        this.updateZoomDisplay(percentage);
    }

    updateZoomDisplay(percentage) {
        const zoomDisplay = document.getElementById('zoomDisplay');
        if (zoomDisplay) {
            zoomDisplay.textContent = `${percentage}%`;
        }

        const zoomValue = document.getElementById('zoomValue');
        if (zoomValue) {
            zoomValue.textContent = `${percentage}%`;
        }
    }

    // ========================================
    // NAVIGATION
    // ========================================
    async previousPage() {
        if (this.state.currentPage > 1) {
            this.state.currentPage--;
            await this.renderPage(this.state.currentPage);
            this.updatePageInfo();
        }
    }

    async nextPage() {
        if (this.state.currentPage < this.state.totalPages) {
            this.state.currentPage++;
            await this.renderPage(this.state.currentPage);
            this.updatePageInfo();
        }
    }

    async goToPage(pageNum) {
        if (pageNum >= 1 && pageNum <= this.state.totalPages) {
            this.state.currentPage = pageNum;
            await this.renderPage(this.state.currentPage);
            this.updatePageInfo();
        }
    }

    updatePageInfo() {
        const elements = [
            'pageInfo',
            'mobileCurrentPage',
            'currentPage'
        ];
        
        elements.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = this.state.currentPage;
        });
        
        const totalElements = [
            'mobileTotalPages',
            'totalPages'
        ];
        
        totalElements.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = this.state.totalPages;
        });
    }

    // ========================================
    // RESPONSIVE
    // ========================================
    handleResize() {
        // Éviter les rendus multiples
        if (this.state.isResizing) {
            this.log('⏳ Resize déjà en cours, ignoré');
            return;
        }

        this.state.isResizing = true;

        // Re-détecter le device
        const oldSize = this.device.viewport.size;
        const oldOrientation = this.device.orientation;
        this.device = this.detectDevice();

        // Si changement significatif de taille ou orientation
        const sizeChanged = oldSize !== this.device.viewport.size;
        const orientationChanged = oldOrientation !== this.device.orientation;

        if (sizeChanged || orientationChanged) {
            this.log('📐 Resize détecté:', { oldSize, newSize: this.device.viewport.size, oldOrientation, newOrientation: this.device.orientation });

            // Invalider les caches
            this.cache.pages.clear();
            this.invalidateConversionCache();
            this.state.scale = this.getInitialScale();

            // Re-rendre la page
            this.renderPage(this.state.currentPage).finally(() => {
                this.state.isResizing = false;
            });
        } else {
            // Juste un petit resize, pas besoin de tout recharger
            this.state.isResizing = false;
        }
    }

    handleOrientationChange() {
        this.device = this.detectDevice();
        this.cache.pages.clear();
        this.invalidateConversionCache();
        this.state.scale = this.getInitialScale();
        this.renderPage(this.state.currentPage);
    }

    // ========================================
    // CONVERSION COORDONNÉES - VERSION PRÉCISE
    // ========================================
    async convertHtmlToPdfX(htmlX) {
        const data = await this.getConversionData();
        if (!data) return htmlX;

        // 1. Obtenir la position relative dans le container (0-1)
        const relativeX = htmlX / data.containerWidth;
        
        // 2. Appliquer au viewport réel du PDF
        const pdfX = relativeX * data.pdfPageWidth;
        
        this.log('🔍 Conversion X:', {
            htmlX,
            containerWidth: data.containerWidth,
            relativeX: relativeX.toFixed(3),
            pdfPageWidth: data.pdfPageWidth,
            pdfX: Math.round(pdfX)
        });
        
        return Math.round(pdfX);
    }

    async convertHtmlToPdfY(htmlY, elementType = 'signature') {
        const data = await this.getConversionData();
        if (!data) return htmlY;

        // 1. Obtenir la position relative dans le container (0-1)
        const relativeY = htmlY / data.containerHeight;
        
        // 2. PDF a l'origine en bas à gauche, on inverse
        const invertedRelativeY = 1 - relativeY;
        
        // 3. Appliquer au viewport réel du PDF
        let pdfY = invertedRelativeY * data.pdfPageHeight;
        
        // 4. Ajuster pour la hauteur de l'élément
        const elementHeight = this.getElementSize(elementType).height;
        const elementHeightInPdf = (elementHeight / data.containerHeight) * data.pdfPageHeight;
        
        // On soustrait la hauteur car PDF mesure du bas
        pdfY -= elementHeightInPdf;
        
        this.log('🔍 Conversion Y:', {
            htmlY,
            containerHeight: data.containerHeight,
            relativeY: relativeY.toFixed(3),
            invertedRelativeY: invertedRelativeY.toFixed(3),
            pdfPageHeight: data.pdfPageHeight,
            elementHeight,
            elementHeightInPdf: elementHeightInPdf.toFixed(2),
            pdfY: Math.round(pdfY)
        });
        
        return Math.round(Math.max(0, pdfY));
    }

    async getConversionData() {
        const now = Date.now();
        
        // Cache de 100ms pour éviter les recalculs constants
        if (this.cache.conversionData && (now - this.cache.conversionTime) < 100) {
            return this.cache.conversionData;
        }

        const container = document.getElementById(this.config.containerId);
        const canvas = container?.querySelector('canvas');
        
        if (!canvas || !this.pdfDoc) {
            return null;
        }

        try {
            // Obtenir la page actuelle pour les dimensions réelles
            const page = await this.pdfDoc.getPage(this.state.currentPage);
            const viewport = page.getViewport({ scale: 1.0 }); // Scale 1.0 = dimensions originales
            
            const containerRect = container.getBoundingClientRect();
            
            const data = {
                container: containerRect,
                containerWidth: containerRect.width,
                containerHeight: containerRect.height,
                canvas: canvas,
                canvasDisplayWidth: canvas.offsetWidth,
                canvasDisplayHeight: canvas.offsetHeight,
                canvasRealWidth: canvas.width,
                canvasRealHeight: canvas.height,
                // Dimensions RÉELLES de la page PDF (en points)
                pdfPageWidth: viewport.width,
                pdfPageHeight: viewport.height,
                // Échelle d'affichage actuelle
                displayScale: this.state.scale,
                // Pixel ratio
                pixelRatio: this.device.isMobile ? 
                    Math.min(this.device.pixelRatio, 2) : 
                    this.device.pixelRatio
            };
            
            this.cache.conversionData = data;
            this.cache.conversionTime = now;
            
            this.log('📐 Données de conversion:', {
                containerSize: `${data.containerWidth}x${data.containerHeight}`,
                canvasDisplay: `${data.canvasDisplayWidth}x${data.canvasDisplayHeight}`,
                canvasReal: `${data.canvasRealWidth}x${data.canvasRealHeight}`,
                pdfSize: `${data.pdfPageWidth}x${data.pdfPageHeight}`,
                scale: data.displayScale,
                pixelRatio: data.pixelRatio
            });
            
            return data;
            
        } catch (error) {
            console.error('❌ Erreur getConversionData:', error);
            return null;
        }
    }

    // Invalider le cache de conversion (appelé lors de resize, zoom, etc.)
    invalidateConversionCache() {
        this.cache.conversionData = null;
        this.cache.conversionTime = 0;
    }

    // ========================================
    // FORMULAIRE ET SOUMISSION
    // ========================================
    updateFormData() {
        // Throttled pour éviter les appels trop fréquents
        if (!this.throttledUpdateForm) {
            this.throttledUpdateForm = this.throttle(() => {
                this.actualUpdateFormData();
            }, 100);
        }
        
        this.throttledUpdateForm();
    }

    async actualUpdateFormData() {
        // Mise à jour des champs cachés avec conversion async
        if (this.signatures[0]) {
            const pdfX = await this.convertHtmlToPdfX(this.signatures[0].x);
            const pdfY = await this.convertHtmlToPdfY(this.signatures[0].y, 'signature');
            this.updateHiddenField(this.config.signatureXInputId, pdfX);
            this.updateHiddenField(this.config.signatureYInputId, pdfY);
        }
        
        if (this.paraphes[0]) {
            const pdfX = await this.convertHtmlToPdfX(this.paraphes[0].x);
            const pdfY = await this.convertHtmlToPdfY(this.paraphes[0].y, 'paraphe');
            this.updateHiddenField(this.config.parapheXInputId, pdfX);
            this.updateHiddenField(this.config.parapheYInputId, pdfY);
        }
        
        if (this.cachets[0]) {
            const pdfX = await this.convertHtmlToPdfX(this.cachets[0].x);
            const pdfY = await this.convertHtmlToPdfY(this.cachets[0].y, 'cachet');
            this.updateHiddenField(this.config.cachetXInputId, pdfX);
            this.updateHiddenField(this.config.cachetYInputId, pdfY);
        }
        
        // Type d'action
        this.updateActionType();
    }

    updateHiddenField(fieldId, value) {
        if (!fieldId) return;
        
        const field = document.getElementById(fieldId);
        if (field && value !== undefined) {
            field.value = value;
        }
    }

    updateActionType() {
        const field = document.getElementById(this.config.actionTypeInputId);
        if (!field) return;

        const hasSignature = this.signatures.length > 0;
        const hasParaphe = this.paraphes.length > 0;
        const hasCachet = this.cachets.length > 0;

        if (hasSignature && hasParaphe && hasCachet) {
            field.value = 'all';
        } else if (hasSignature && hasParaphe) {
            field.value = 'both';
        } else if (hasSignature && hasCachet) {
            field.value = 'sign_cachet';
        } else if (hasParaphe && hasCachet) {
            field.value = 'paraphe_cachet';
        } else if (hasSignature) {
            field.value = 'sign_only';
        } else if (hasParaphe) {
            field.value = 'paraphe_only';
        } else if (hasCachet) {
            field.value = 'cachet_only';
        }
    }

    async submitForm() {
        if (this.state.isProcessing) return;
        
        if (this.signatures.length === 0 && this.paraphes.length === 0 && this.cachets.length === 0) {
            this.showToast('Veuillez ajouter au moins un élément', 'error');
            return;
        }

        this.state.isProcessing = true;
        this.showToast('Génération du PDF...', 'info');

        try {
            await this.generateFinalPdf();
            this.showToast('PDF généré avec succès !', 'success');
            
            // Redirection après un délai
            setTimeout(() => {
                const redirectUrl = this.config.redirectUrl || `/documents/${this.config.documentId}/process/view`;
                window.location.href = redirectUrl;
            }, 1500);
            
        } catch (error) {
            console.error('❌ Erreur génération PDF:', error);
            this.showToast('Erreur lors de la génération', 'error');
            this.state.isProcessing = false;
        }
    }

    // ========================================
    // GÉNÉRATION PDF - OPTIMISÉE
    // ========================================
    async generateFinalPdf() {
        // Charger pdf-lib si nécessaire
        if (!window.PDFLib) {
            await this.loadPdfLib();
        }

        const { PDFDocument } = window.PDFLib;
        
        // Charger le PDF original
        const existingPdfBytes = await fetch(this.config.pdfUrl)
            .then(res => res.arrayBuffer());
        const pdfDoc = await PDFDocument.load(existingPdfBytes);
        const pages = pdfDoc.getPages();

        // Charger toutes les images en parallèle
        const imagePromises = this.collectImagePromises();
        const loadedImages = await Promise.allSettled(imagePromises);

        // Embedder et dessiner
        for (const result of loadedImages) {
            if (result.status === 'rejected') continue;
            
            const { type, element, bytes } = result.value;
            
            try {
                const image = await pdfDoc.embedPng(bytes);
                const page = pages[element.page - 1];
                
                if (!page) continue;
                
                const pageSize = page.getSize();
                
                // Conversion PRÉCISE des coordonnées
                const pdfX = await this.convertHtmlToPdfX(element.x);
                const pdfY = await this.convertHtmlToPdfY(element.y, type);
                
                // Calculer la taille proportionnelle de l'élément en PDF
                const data = await this.getConversionData();
                const elementSize = this.getElementSize(type);
                
                // Convertir la taille HTML en taille PDF
                const widthRatio = elementSize.width / data.containerWidth;
                const heightRatio = elementSize.height / data.containerHeight;
                
                const width = widthRatio * pageSize.width;
                const height = heightRatio * pageSize.height;
                
                this.log('📝 Placement élément:', {
                    type,
                    page: element.page,
                    htmlPos: { x: element.x, y: element.y },
                    pdfPos: { x: pdfX, y: pdfY },
                    htmlSize: { width: elementSize.width, height: elementSize.height },
                    pdfSize: { width: width.toFixed(2), height: height.toFixed(2) },
                    pageSize: { width: pageSize.width, height: pageSize.height }
                });
                
                page.drawImage(image, {
                    x: pdfX,
                    y: pdfY,
                    width,
                    height,
                    opacity: 0.8
                });
                
            } catch (error) {
                console.error(`❌ Erreur pour ${type}:`, error);
            }
        }

        // Sauvegarder et envoyer
        const pdfBytes = await pdfDoc.save();
        await this.uploadPdfToServer(pdfBytes, `document_signe_${Date.now()}.pdf`);
    }

    collectImagePromises() {
        const promises = [];
        
        for (const signature of this.signatures) {
            if (signature.url) {
                promises.push(
                    fetch(signature.url)
                        .then(res => res.arrayBuffer())
                        .then(bytes => ({ type: 'signature', element: signature, bytes }))
                );
            }
        }
        
        for (const paraphe of this.paraphes) {
            if (paraphe.url) {
                promises.push(
                    fetch(paraphe.url)
                        .then(res => res.arrayBuffer())
                        .then(bytes => ({ type: 'paraphe', element: paraphe, bytes }))
                );
            }
        }
        
        for (const cachet of this.cachets) {
            if (cachet.url) {
                promises.push(
                    fetch(cachet.url)
                        .then(res => res.arrayBuffer())
                        .then(bytes => ({ type: 'cachet', element: cachet, bytes }))
                );
            }
        }
        
        return promises;
    }

    async loadPdfLib() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async uploadPdfToServer(pdfBytes, filename) {
        const formData = new FormData();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        formData.append('signed_pdf', blob, filename);
        formData.append('document_id', this.config.documentId);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        const uploadUrl = this.config.uploadUrl || '/documents/upload-signed-pdf';
        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) {
            throw new Error(`Erreur serveur: ${response.status}`);
        }

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur inconnue');
        }

        return result;
    }

    // ========================================
    // CANVAS (pour signature live)
    // ========================================
    initializeCanvases() {
        const signatureCanvas = document.getElementById(this.config.signatureCanvasId);
        if (signatureCanvas) {
            this.setupCanvas(signatureCanvas, 'signature');
        }

        const parapheCanvas = document.getElementById(this.config.parapheCanvasId);
        if (parapheCanvas) {
            this.setupCanvas(parapheCanvas, 'paraphe');
        }
    }

    setupCanvas(canvas, type) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastPos = { x: 0, y: 0 };

        const startDrawing = (e) => {
            isDrawing = true;
            const pos = this.getCanvasCoords(e, canvas);
            lastPos = pos;
        };

        const draw = (e) => {
            if (!isDrawing) return;
            
            e.preventDefault();
            const pos = this.getCanvasCoords(e, canvas);
            
            ctx.beginPath();
            ctx.moveTo(lastPos.x, lastPos.y);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = type === 'signature' ? '#28a745' : '#667eea';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.stroke();
            
            lastPos = pos;
        };

        const stopDrawing = () => {
            isDrawing = false;
        };

        // Touch events
        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing, { passive: false });
        
        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        
        this.cleanup.push(() => {
            canvas.removeEventListener('touchstart', startDrawing);
            canvas.removeEventListener('touchmove', draw);
            canvas.removeEventListener('touchend', stopDrawing);
            canvas.removeEventListener('mousedown', startDrawing);
            canvas.removeEventListener('mousemove', draw);
            canvas.removeEventListener('mouseup', stopDrawing);
        });
    }

    getCanvasCoords(e, canvas) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        
        let clientX, clientY;
        
        if (e.touches) {
            const touch = e.touches[0];
            clientX = touch.clientX;
            clientY = touch.clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }
        
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    // ========================================
    // INTERFACE
    // ========================================
    updateInterface() {
        // Mise à jour du bouton submit
        const submitBtn = document.getElementById(this.config.submitBtnId);
        if (submitBtn) {
            const text = submitBtn.querySelector('span');
            if (text) {
                const hasSignature = this.signatures.length > 0;
                const hasParaphe = this.paraphes.length > 0;
                const hasCachet = this.cachets.length > 0;
                
                if (hasSignature && hasParaphe && hasCachet) {
                    text.textContent = 'Traiter le Document';
                } else if (hasSignature && hasParaphe) {
                    text.textContent = 'Signer & Parapher';
                } else if (hasSignature) {
                    text.textContent = 'Signer le Document';
                } else if (hasParaphe) {
                    text.textContent = 'Parapher le Document';
                } else if (hasCachet) {
                    text.textContent = 'Cacheter le Document';
                } else {
                    text.textContent = 'Signer le Document';
                }
            }
        }
    }

    clearAll() {
        this.signatures = [];
        this.paraphes = [];
        this.cachets = [];
        
        this.renderElements();
        this.updateFormData();
        this.updateInterface();
        
        this.showToast('Tous les éléments supprimés', 'info');
    }

    disableAllInteractions() {
        const buttons = [
            this.config.addSignatureBtnId,
            this.config.addParapheBtnId,
            this.config.addCachetBtnId,
            this.config.clearAllBtnId,
            this.config.submitBtnId
        ];

        buttons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            }
        });

        this.showToast('Mode lecture seule', 'info');
    }

    // ========================================
    // NOTIFICATIONS
    // ========================================
    showToast(message, type = 'info') {
        let toast = document.querySelector('.pdf-toast');
        
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'pdf-toast';
            document.body.appendChild(toast);
        }

        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };

        toast.className = `pdf-toast pdf-toast-${type} show`;
        toast.innerHTML = `
            <div class="pdf-toast-content">
                <i class="fas fa-${icons[type]}"></i>
                <span>${message}</span>
            </div>
        `;

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // ========================================
    // UTILITAIRES
    // ========================================
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    log(...args) {
        if (this.debug) {
            console.log(...args);
        }
    }

    // ========================================
    // NETTOYAGE
    // ========================================
    destroy() {
        // Nettoyer tous les event listeners
        this.cleanup.forEach(fn => fn());
        this.cleanup = [];
        
        // Vider les caches
        this.cache.pages.clear();
        this.cache.images.clear();
        
        // Nettoyer les références
        this.signatures = [];
        this.paraphes = [];
        this.cachets = [];
        this.state = null;
        
        this.log('✅ Module détruit et nettoyé');
    }
}

// ========================================
// STYLES CSS MOBILE-FIRST
// ========================================
const styles = document.createElement('style');
styles.textContent = `
    /* BASE - MOBILE FIRST */
    .element-overlay {
        position: absolute;
        border: 2px solid;
        border-radius: 4px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        user-select: none;
        -webkit-user-select: none;
    }
    
    .pdf-positioning-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 123, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        cursor: crosshair;
    }
    
    .overlay-message {
        background: white;
        padding: 1rem 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-align: center;
    }
    
    .overlay-message i {
        font-size: 2rem;
        color: #007bff;
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .pdf-toast {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 9999;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 90vw;
    }
    
    .pdf-toast.show {
        transform: translateX(0);
    }
    
    .pdf-toast-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .pdf-toast-success .pdf-toast-content {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .pdf-toast-error .pdf-toast-content {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .pdf-toast-warning .pdf-toast-content {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .pdf-toast-info .pdf-toast-content {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    /* TABLET - min 768px */
    @media (min-width: 768px) {
        .pdf-toast {
            max-width: 400px;
        }
        
        .pdf-toast-content {
            padding: 1rem 1.5rem;
            font-size: 1rem;
        }
        
        .overlay-message {
            padding: 1.5rem 3rem;
        }
    }
    
    /* DESKTOP - min 992px */
    @media (min-width: 992px) {
        .element-overlay:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .pdf-positioning-overlay {
            cursor: crosshair;
        }
    }
    
    /* TOUCH OPTIMIZATIONS */
    @media (hover: none) and (pointer: coarse) {
        .element-overlay {
            border-width: 3px;
            min-width: 60px;
            min-height: 40px;
        }
        
        .pdf-toast {
            top: auto;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
        }
    }
`;

document.head.appendChild(styles);

// Export pour usage global
window.PDFOverlayUnifiedModule = PDFOverlayUnifiedModule;