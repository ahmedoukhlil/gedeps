/**
 * Module PDF Overlay Unifié - Signature & Paraphe
 * Gestion unifiée des signatures et paraphes sur documents PDF
 */
class PDFOverlayUnifiedModule {
    constructor(config) {
        this.config = config;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 0;
        this.scale = 0.8;
        this.signatures = [];
        this.paraphes = [];
        this.cachets = [];
        this.actionType = this.config.actionType || 'sign_only';
        this.signatureCanvas = null;
        this.parapheCanvas = null;
        this.cachetCanvas = null;
        this.signatureCtx = null;
        this.parapheCtx = null;
        this.cachetCtx = null;
        this.isDrawingSignature = false;
        this.isDrawingParaphe = false;
        this.isDrawingCachet = false;
        this.liveSignatureData = null;
        this.liveParapheData = null;
        this.liveCachetData = null;
        this.isPositioningActive = false;
        this.devicePixelRatio = window.devicePixelRatio || 1; // Support haute résolution
        this.qualityMode = 'ultra'; // Mode qualité: 'low', 'medium', 'high', 'ultra'
    }

    /**
     * Calculer le ratio de pixels pour la qualité
     */
    getQualityPixelRatio() {
        const baseRatio = this.devicePixelRatio;
        switch (this.qualityMode) {
            case 'low': return baseRatio * 1.0;
            case 'medium': return baseRatio * 1.2;
            case 'high': return baseRatio * 1.5;
            case 'ultra': return baseRatio * 2.0;
            default: return baseRatio * 1.2;
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
            await this.loadPDF();
            
            // Charger signature, paraphe et cachet en parallèle (sans bloquer si une échoue)
            await Promise.allSettled([
                this.loadUserSignature(),
                this.loadUserParaphe(),
                this.loadUserCachet()
            ]).then(results => {
                results.forEach((result, index) => {
                    const types = ['signature', 'paraphe', 'cachet'];
                    if (result.status === 'rejected') {
                        console.error(`❌ Erreur chargement ${types[index]}:`, result.reason);
                    }
                });
            });
            
            this.initializeEvents();
            this.initializeCanvases();
            this.updateInterface();
            this.updateNavigationButtons();
            
            // Si en mode lecture seule, désactiver toutes les interactions
            if (this.config.isReadOnly) {
                this.disableAllInteractions();
            }
            
            this.showStatus('PDF chargé avec succès', 'success');
        } catch (error) {
            console.error('Erreur lors du chargement du PDF:', error);
            this.showStatus('Erreur lors du chargement du PDF: ' + error.message, 'error');
        }
    }

    async loadUserSignature() {
        try {
            console.log('🔄 Chargement de la signature utilisateur...');
            
            const response = await fetch('/signatures/user-signature', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin' // Inclure les cookies de session
            });
            
            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    console.log('⚠️ Utilisateur non authentifié, signature non disponible');
                    return null;
                }
                throw new Error(`Erreur API: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📡 Réponse API signature:', data);
            
            if (data.success && data.signature_url) {
                this.userSignatureUrl = data.signature_url;
                console.log('✅ Signature utilisateur chargée:', this.userSignatureUrl);
            } else {
                console.warn('⚠️ Aucune signature utilisateur trouvée');
                this.userSignatureUrl = null;
            }
            
        } catch (error) {
            console.error('❌ Erreur chargement signature:', error);
            // Ne pas afficher d'erreur si l'utilisateur n'est pas authentifié
            if (!error.message.includes('401') && !error.message.includes('403')) {
                this.showStatus(`Erreur signature: ${error.message}`, 'error');
            }
            this.userSignatureUrl = null;
        }
    }

    async loadUserParaphe() {
        try {
            console.log('🔄 Chargement du paraphe utilisateur...');
            
            const response = await fetch('/signatures/user-paraphe', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    console.log('⚠️ Utilisateur non authentifié, paraphe non disponible');
                    return null;
                }
                throw new Error(`Erreur API: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📡 Réponse API paraphe:', data);
            
            if (data.success && data.paraphe_url) {
                this.userParapheUrl = data.paraphe_url;
                console.log('✅ Paraphe utilisateur chargé:', this.userParapheUrl);
            } else {
                console.warn('⚠️ Aucun paraphe utilisateur trouvé');
                this.userParapheUrl = null;
            }
            
        } catch (error) {
            console.error('❌ Erreur chargement paraphe:', error);
            this.userParapheUrl = null;
        }
    }

    async loadUserCachet() {
        try {
            console.log('🔄 Chargement du cachet utilisateur...');
            
            const cachetUrl = this.config.cachetUrl || '/signatures/user-cachet';
            console.log('📍 URL de l\'API cachet:', cachetUrl);
            
            const response = await fetch(cachetUrl, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            console.log('📡 Statut de la réponse:', response.status);
            
            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    console.log('⚠️ Utilisateur non authentifié, cachet non disponible');
                    return null;
                }
                if (response.status === 404) {
                    console.log('⚠️ Cachet non trouvé (404) - L\'utilisateur n\'a pas encore uploadé de cachet');
                    this.userCachetUrl = null;
                    return null;
                }
                throw new Error(`Erreur API: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📡 Réponse API cachet complète:', data);
            
            if (data.success && (data.cachet_url || data.cachetUrl)) {
                this.userCachetUrl = data.cachet_url || data.cachetUrl;
                console.log('✅ Cachet utilisateur chargé:', this.userCachetUrl);
            } else {
                console.warn('⚠️ Aucun cachet utilisateur trouvé dans la réponse');
                console.warn('⚠️ Données reçues:', data);
                this.userCachetUrl = null;
            }
            
        } catch (error) {
            console.error('❌ Erreur chargement cachet:', error);
            this.userCachetUrl = null;
        }
    }

    async loadPDF() {
        try {
            const loadingTask = pdfjsLib.getDocument(this.config.pdfUrl);
            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;
            
            // Déclencher l'événement pdfLoaded
            document.dispatchEvent(new CustomEvent('pdfLoaded', {
                detail: { 
                    totalPages: this.totalPages,
                    currentPage: this.currentPage 
                }
            }));
            
            // Afficher le PDF à 100% par défaut
            this.scale = 1.0;
            await this.renderPage(this.currentPage);
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
        
        // Calculer l'échelle responsive
        let responsiveScale = this.scale;
        
        if (container) {
            const containerWidth = container.clientWidth;
            const pageWidth = page.getViewport({ scale: 1.0 }).width;
            
            // Ajuster l'échelle selon la largeur du conteneur
            if (containerWidth < 768) {
                responsiveScale = Math.min(this.scale, containerWidth / pageWidth * 0.9);
            } else if (containerWidth < 1200) {
                responsiveScale = Math.min(this.scale, containerWidth / pageWidth * 0.8);
            }
        }
        
        const viewport = page.getViewport({ scale: responsiveScale });
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Configuration haute qualité avec support DPI
        const pixelRatio = this.getQualityPixelRatio();
        
        // Dimensions du canvas pour le rendu haute qualité
        canvas.width = viewport.width * pixelRatio;
        canvas.height = viewport.height * pixelRatio;
        
        // Dimensions d'affichage (conservation des dimensions originales)
        canvas.style.width = viewport.width + 'px';
        canvas.style.height = viewport.height + 'px';
        
        // Configuration du contexte pour la qualité
        ctx.scale(pixelRatio, pixelRatio);
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        canvas.style.width = '100%';
        canvas.style.height = 'auto';
        canvas.style.border = '1px solid #ddd';
        canvas.style.borderRadius = '8px';
        canvas.style.display = 'block';
        canvas.style.margin = '0 auto';
        canvas.style.maxWidth = '100%';
        
        // Styles responsive pour mobile
        canvas.style.transition = 'transform 0.2s ease';
        canvas.style.touchAction = 'pan-x pan-y';
        canvas.style.userSelect = 'none';
        canvas.style.webkitUserSelect = 'none';
        canvas.style.mozUserSelect = 'none';
        canvas.style.msUserSelect = 'none';
        
        // Ajouter les classes responsive
        canvas.classList.add('pdf-canvas-responsive');
        
        // Gestion des événements tactiles pour mobile - PERMETTRE LE SCROLLING
        canvas.addEventListener('touchstart', (e) => {
            // Ne pas bloquer le scrolling - seulement si on dessine
            if (this.isDrawingSignature || this.isDrawingParaphe || this.isDrawingCachet) {
            e.preventDefault();
            }
        }, { passive: true });
        
        canvas.addEventListener('touchmove', (e) => {
            // Ne pas bloquer le scrolling - seulement si on dessine
            if (this.isDrawingSignature || this.isDrawingParaphe || this.isDrawingCachet) {
            e.preventDefault();
            }
        }, { passive: true });
        
        canvas.addEventListener('touchend', (e) => {
            // Ne pas bloquer le scrolling - seulement si on dessine
            if (this.isDrawingSignature || this.isDrawingParaphe || this.isDrawingCachet) {
            e.preventDefault();
            }
        }, { passive: true });
        
        // Optimisations pour mobile/tablette - PERMETTRE LE SCROLLING
        canvas.style.touchAction = 'pan-x pan-y pinch-zoom'; // Permettre le scrolling et le zoom
        canvas.style.userSelect = 'none'; // Empêche la sélection de texte
        canvas.style.webkitUserSelect = 'none';
        canvas.style.mozUserSelect = 'none';
        canvas.style.msUserSelect = 'none';

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport,
            intent: 'display', // Optimisé pour l'affichage
            enableWebGL: false, // Désactiver WebGL pour la compatibilité
            renderInteractiveForms: false // Désactiver les formulaires interactifs pour les performances
        };

        try {
            await page.render(renderContext).promise;
            container.appendChild(canvas);
            
            // Ajouter les signatures et paraphes existants
            this.renderSignatures(container);
            this.renderParaphes(container);
            this.renderCachets(container);
        } catch (error) {
            console.error('Erreur lors du rendu de la page:', error);
            this.showStatus('Erreur lors du rendu de la page', 'error');
        }
    }


    createSignatureElement(signature) {
        const signatureDiv = document.createElement('div');
        signatureDiv.className = 'signature-overlay';
        signatureDiv.style.position = 'absolute';
        signatureDiv.style.left = signature.x + 'px';
        signatureDiv.style.top = signature.y + 'px';
        signatureDiv.style.width = signature.width + 'px';
        signatureDiv.style.height = signature.height + 'px';
        signatureDiv.style.border = '2px solid #28a745';
        signatureDiv.style.borderRadius = '4px';
        signatureDiv.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
        signatureDiv.style.cursor = 'move';
        signatureDiv.style.zIndex = '1000';
        signatureDiv.draggable = true;
        signatureDiv.dataset.signatureId = signature.id;

        if (signature.url) {
            const img = document.createElement('img');
            img.src = signature.url;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            img.style.borderRadius = '2px';
            
            // Ajouter des gestionnaires d'événements pour diagnostiquer
            img.onload = () => {
                console.log('✅ Image de signature chargée avec succès:', signature.url);
            };
            
            img.onerror = (error) => {
                console.error('❌ Erreur de chargement de l\'image de signature:', error);
                console.error('URL de l\'image:', signature.url);
                
                // Afficher un texte de remplacement
                signatureDiv.innerHTML = '<div style="color: red; text-align: center; padding: 10px; font-size: 12px;">❌ Erreur de chargement de la signature</div>';
            };
            
            signatureDiv.appendChild(img);
        } else {
            const icon = document.createElement('i');
            icon.className = 'fas fa-pen-fancy';
            icon.style.color = '#28a745';
            icon.style.fontSize = '16px';
            icon.style.position = 'absolute';
            icon.style.top = '50%';
            icon.style.left = '50%';
            icon.style.transform = 'translate(-50%, -50%)';
            signatureDiv.appendChild(icon);
        }

        return signatureDiv;
    }

    createParapheElement(paraphe) {
        const parapheDiv = document.createElement('div');
        parapheDiv.className = 'paraphe-overlay';
        parapheDiv.style.position = 'absolute';
        parapheDiv.style.left = paraphe.x + 'px';
        parapheDiv.style.top = paraphe.y + 'px';
        parapheDiv.style.width = paraphe.width + 'px';
        parapheDiv.style.height = paraphe.height + 'px';
        parapheDiv.style.border = '2px solid #667eea';
        parapheDiv.style.borderRadius = '4px';
        parapheDiv.style.backgroundColor = 'rgba(102, 126, 234, 0.1)';
        parapheDiv.style.cursor = 'move';
        parapheDiv.style.zIndex = '1000';
        parapheDiv.draggable = true;
        parapheDiv.dataset.parapheId = paraphe.id;

        if (paraphe.url) {
            const img = document.createElement('img');
            img.src = paraphe.url;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            img.style.borderRadius = '2px';
            
            // Gestion des erreurs d'image
            img.onerror = function() {
                console.error('Erreur de chargement de l\'image paraphe:', paraphe.url);
                // Remplacer par une icône de fallback
                const fallbackIcon = document.createElement('i');
                fallbackIcon.className = 'fas fa-exclamation-triangle';
                fallbackIcon.style.color = '#dc3545';
                fallbackIcon.style.fontSize = '16px';
                fallbackIcon.style.position = 'absolute';
                fallbackIcon.style.top = '50%';
                fallbackIcon.style.left = '50%';
                fallbackIcon.style.transform = 'translate(-50%, -50%)';
                parapheDiv.appendChild(fallbackIcon);
            };
            
            img.onload = function() {
                console.log('Image paraphe chargée avec succès:', paraphe.url);
            };
            
            parapheDiv.appendChild(img);
        } else {
            const icon = document.createElement('i');
            icon.className = 'fas fa-pen-nib';
            icon.style.color = '#667eea';
            icon.style.fontSize = '12px';
            icon.style.position = 'absolute';
            icon.style.top = '50%';
            icon.style.left = '50%';
            icon.style.transform = 'translate(-50%, -50%)';
            parapheDiv.appendChild(icon);
        }

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
        console.log('🚀 Initialisation des événements...');
        console.log('📋 Configuration reçue:', this.config);
        
        // Détection du type d'appareil
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        console.log('📱 Appareil tactile détecté:', isTouchDevice);
        console.log('📱 User Agent:', navigator.userAgent);
        console.log('📱 Touch Points:', navigator.maxTouchPoints);
        
        // Configuration du responsive
        this.setupResponsiveHandling();
        
        // Attendre que le DOM soit complètement chargé
        setTimeout(() => {
            this.attachEvents(isTouchDevice);
        }, 100);
    }
    
    setupResponsiveHandling() {
        // Gestion du redimensionnement de la fenêtre
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResponsiveResize();
            }, 250);
        });
        
        // Gestion de l'orientation sur mobile
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleResponsiveResize();
            }, 500);
        });
    }
    
    async handleResponsiveResize() {
        if (this.pdfDoc && this.currentPage) {
            try {
                await this.renderPage(this.currentPage);
                this.updatePageInfo();
            } catch (error) {
                console.warn('Erreur lors du redimensionnement responsive:', error);
            }
        }
    }

    attachEvents(isTouchDevice) {
        console.log('🔗 Attachement des événements...');
        
        // Vérifier que tous les éléments existent
        console.log('🔍 Vérification des éléments DOM...');
        console.log('📋 Tous les boutons dans le DOM:', document.querySelectorAll('button[id]'));
        console.log('📋 Bouton signature direct:', document.getElementById('addSignatureBtn'));
        console.log('📋 Bouton submit direct:', document.getElementById('submitBtn'));
        
        // Gestion des boutons de signature et paraphe
        if (this.config.addSignatureBtnId) {
            const addSignatureBtn = document.getElementById(this.config.addSignatureBtnId);
            console.log('🔍 Recherche du bouton signature:', {
                id: this.config.addSignatureBtnId,
                element: addSignatureBtn,
                found: !!addSignatureBtn
            });
        
        // DIAGNOSTIC : Vérifier tous les boutons disponibles
        const allButtons = document.querySelectorAll('button');
        console.log('🔍 DIAGNOSTIC - Tous les boutons disponibles:', Array.from(allButtons).map(btn => ({ id: btn.id, text: btn.textContent.trim() })));
            if (addSignatureBtn) {
                // Variable pour éviter les appels multiples
                let isProcessing = false;
                
                const handleSignatureClick = (e) => {
                    if (isProcessing) {
                        console.log('⚠️ Signature déjà en cours de traitement, ignoré');
                        return;
                    }
                    
                    isProcessing = true;
                    console.log('🖱️ Clic sur le bouton Signer détecté');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    this.addSignature();
                    
                    // Réinitialiser après un délai
                    setTimeout(() => {
                        isProcessing = false;
                    }, 1000);
                };
                
                // Événement clic pour desktop
                addSignatureBtn.addEventListener('click', handleSignatureClick);
                
                // Événements tactiles pour mobile/tablette
                if (isTouchDevice) {
                    console.log('📱 Ajout des événements tactiles pour le bouton signature');
                    
                    addSignatureBtn.addEventListener('touchstart', (e) => {
                        console.log('👆 Touch START sur le bouton Signer détecté');
                        e.preventDefault();
                        e.stopPropagation();
                        handleSignatureClick(e);
                    }, { passive: false });
                    
                    addSignatureBtn.addEventListener('touchend', (e) => {
                        console.log('👆 Touch END sur le bouton Signer détecté');
                        e.preventDefault();
                        e.stopPropagation();
                        // Ne pas appeler addSignature() ici pour éviter les doubles appels
                    }, { passive: false });
                }
                
            } else {
                console.error('❌ Bouton signature non trouvé avec l\'ID:', this.config.addSignatureBtnId);
            }
            
            // Test direct du bouton après un délai
            setTimeout(() => {
                const testBtn = document.getElementById(this.config.addSignatureBtnId);
                if (testBtn) {
                    console.log('🧪 Test du bouton signature après délai:', {
                        existe: !!testBtn,
                        visible: testBtn.offsetParent !== null,
                        disabled: testBtn.disabled,
                        style: testBtn.style.display,
                        computedStyle: window.getComputedStyle(testBtn).display
                    });
                    
                    // Test manuel de l'événement
                    testBtn.addEventListener('mousedown', () => {
                        console.log('🖱️ Mouse down détecté sur le bouton');
                    });
                    
                    testBtn.addEventListener('pointerdown', () => {
                        console.log('👆 Pointer down détecté sur le bouton');
                    });
                    
                    // Événements pointer pour une meilleure compatibilité
                    testBtn.addEventListener('pointerdown', (e) => {
                        console.log('👆 Pointer down détecté:', e.pointerType);
                        if (e.pointerType === 'touch') {
                            console.log('📱 Touch via pointer détecté');
                            e.preventDefault();
                            e.stopPropagation();
                            this.addSignature();
                        }
                    });
                }
            }, 1000);
        }

        if (this.config.addParapheBtnId) {
            const addParapheBtn = document.getElementById(this.config.addParapheBtnId);
            if (addParapheBtn) {
                // Variable pour éviter les appels multiples
                let isProcessingParaphe = false;
                
                const handleParapheClick = async (e) => {
                    if (isProcessingParaphe) {
                        console.log('⚠️ Paraphe déjà en cours de traitement, ignoré');
                        return;
                    }
                    
                    isProcessingParaphe = true;
                    console.log('🖱️ Clic sur le bouton Parapher détecté');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    await this.addParaphe();
                    
                    // Réinitialiser après un délai
                    setTimeout(() => {
                        isProcessingParaphe = false;
                    }, 1000);
                };
                
                // Événement clic pour desktop
                addParapheBtn.addEventListener('click', handleParapheClick);
                
                // Événements tactiles pour mobile/tablette
                if (isTouchDevice) {
                    addParapheBtn.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        handleParapheClick(e);
                    }, { passive: false });
                }
            }

            // Bouton Signer & Parapher
            const addSignAndParapheBtn = document.getElementById(this.config.addSignAndParapheBtnId);
            if (addSignAndParapheBtn) {
                let isProcessingSignAndParaphe = false;
                
                const handleSignAndParapheClick = async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (isProcessingSignAndParaphe) return;
                    
                    isProcessingSignAndParaphe = true;
                    await this.addSignAndParaphe();
                    
                    // Désactiver temporairement le bouton
                    addSignAndParapheBtn.disabled = true;
                    setTimeout(() => {
                        addSignAndParapheBtn.disabled = false;
                        isProcessingSignAndParaphe = false;
                    }, 1000);
                };
                
                // Événement clic pour desktop
                addSignAndParapheBtn.addEventListener('click', handleSignAndParapheClick);
                
                // Événements tactiles pour mobile/tablette
                if (isTouchDevice) {
                    addSignAndParapheBtn.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        handleSignAndParapheClick(e);
                    }, { passive: false });
                }
            }
        }

        // Bouton Cacheter
        if (this.config.addCachetBtnId) {
            const addCachetBtn = document.getElementById(this.config.addCachetBtnId);
            if (addCachetBtn) {
                // Variable pour éviter les appels multiples
                let isProcessingCachet = false;
                
                const handleCachetClick = async (e) => {
                    if (isProcessingCachet) {
                        console.log('⚠️ Cachet déjà en cours de traitement, ignoré');
                        return;
                    }
                    
                    isProcessingCachet = true;
                    console.log('🖱️ Clic sur le bouton Cacheter détecté');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    await this.addCachet();
                    
                    // Réinitialiser après un délai
                    setTimeout(() => {
                        isProcessingCachet = false;
                    }, 1000);
                };
                
                // Événement clic pour desktop
                addCachetBtn.addEventListener('click', handleCachetClick);
                
                // Événements tactiles pour mobile/tablette
                if (isTouchDevice) {
                    addCachetBtn.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        handleCachetClick(e);
                    }, { passive: false });
                }
            } else {
                console.log('⚠️ Bouton cachet non trouvé avec l\'ID:', this.config.addCachetBtnId);
            }
        }

        if (this.config.clearAllBtnId) {
            const clearAllBtn = document.getElementById(this.config.clearAllBtnId);
            if (clearAllBtn) {
                // Événement clic pour desktop
                clearAllBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.clearAll();
                });
                
                // Événements tactiles pour mobile/tablette
                if (isTouchDevice) {
                    clearAllBtn.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.clearAll();
                    }, { passive: false });
                }
            }
        }
        
        // Gestion du bouton "Signer le document" (Enregistrer/Submit)
        if (this.config.submitBtnId) {
            const submitBtn = document.getElementById(this.config.submitBtnId);
            if (submitBtn) {
                console.log('🔍 Bouton "Signer le document" trouvé:', submitBtn);
                
                // Événement clic pour desktop
                submitBtn.addEventListener('click', (e) => {
                    console.log('🖱️ Clic sur "Signer le document" détecté');
                    e.preventDefault();
                    e.stopPropagation();
                    this.submitForm();
                });
                
                // Événements tactiles pour mobile/tablette
                if (isTouchDevice) {
                    console.log('📱 Ajout des événements tactiles pour le bouton "Signer le document"');
                    
                    submitBtn.addEventListener('touchstart', (e) => {
                        console.log('👆 Touch START sur "Signer le document" détecté');
                        e.preventDefault();
                        e.stopPropagation();
                        this.submitForm();
                    }, { passive: false });
                    
                    submitBtn.addEventListener('touchend', (e) => {
                        console.log('👆 Touch END sur "Signer le document" détecté');
                        e.preventDefault();
                        e.stopPropagation();
                        // Ne pas appeler submitForm() ici pour éviter les doubles appels
                    }, { passive: false });
                    
                    // Événement pointer pour une meilleure compatibilité
                    submitBtn.addEventListener('pointerdown', (e) => {
                        console.log('👆 Pointer down sur "Signer le document":', e.pointerType);
                        if (e.pointerType === 'touch') {
                            console.log('📱 Touch via pointer sur "Signer le document"');
                            e.preventDefault();
                            e.stopPropagation();
                            this.submitForm();
                        }
                    });
                }
            } else {
                console.error('❌ Bouton "Signer le document" non trouvé avec l\'ID:', this.config.submitBtnId);
            }
            
            // Test direct du bouton "Signer le document" après un délai
            setTimeout(() => {
                const testSubmitBtn = document.getElementById(this.config.submitBtnId);
                if (testSubmitBtn) {
                    console.log('🧪 Test du bouton "Signer le document" après délai:', {
                        existe: !!testSubmitBtn,
                        visible: testSubmitBtn.offsetParent !== null,
                        disabled: testSubmitBtn.disabled,
                        style: testSubmitBtn.style.display,
                        computedStyle: window.getComputedStyle(testSubmitBtn).display,
                        text: testSubmitBtn.textContent
                    });
                    
                    // Test manuel de l'événement
                    testSubmitBtn.addEventListener('mousedown', () => {
                        console.log('🖱️ Mouse down détecté sur "Signer le document"');
                    });
                    
                    testSubmitBtn.addEventListener('pointerdown', () => {
                        console.log('👆 Pointer down détecté sur "Signer le document"');
                    });
                }
            }, 1000);
        }

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
        if (this.config.zoomInBtnId) {
            const zoomInBtn = document.getElementById(this.config.zoomInBtnId);
            if (zoomInBtn) {
                zoomInBtn.addEventListener('click', () => {
                    this.zoomIn();
                });
            }
        }

        if (this.config.zoomOutBtnId) {
            const zoomOutBtn = document.getElementById(this.config.zoomOutBtnId);
            if (zoomOutBtn) {
                zoomOutBtn.addEventListener('click', () => {
                    this.zoomOut();
                });
            }
        }

        if (this.config.resetZoomBtnId) {
            const resetZoomBtn = document.getElementById(this.config.resetZoomBtnId);
            if (resetZoomBtn) {
                resetZoomBtn.addEventListener('click', () => {
                    this.resetZoom();
                });
            }
        }



        if (this.config.prevPageBtnId) {
            const prevPageBtn = document.getElementById(this.config.prevPageBtnId);
            if (prevPageBtn) {
                console.log('✅ Bouton page précédente trouvé et connecté');
                prevPageBtn.addEventListener('click', () => {
                    console.log('🧭 Navigation vers page précédente');
                    this.previousPage();
                });
            } else {
                console.warn('⚠️ Bouton page précédente non trouvé:', this.config.prevPageBtnId);
            }
        }

        if (this.config.nextPageBtnId) {
            const nextPageBtn = document.getElementById(this.config.nextPageBtnId);
            if (nextPageBtn) {
                console.log('✅ Bouton page suivante trouvé et connecté');
                nextPageBtn.addEventListener('click', () => {
                    console.log('🧭 Navigation vers page suivante');
                    this.nextPage();
                });
            } else {
                console.warn('⚠️ Bouton page suivante non trouvé:', this.config.nextPageBtnId);
            }
        }

        // Navigation rapide - Première page
        const firstPageBtn = document.getElementById('firstPageBtn');
        if (firstPageBtn) {
            console.log('✅ Bouton première page trouvé et connecté');
            firstPageBtn.addEventListener('click', () => {
                console.log('🧭 Navigation vers première page');
                this.goToPage(1);
            });
        } else {
            console.warn('⚠️ Bouton première page non trouvé');
        }

        // Navigation rapide - Dernière page
        const lastPageBtn = document.getElementById('lastPageBtn');
        if (lastPageBtn) {
            console.log('✅ Bouton dernière page trouvé et connecté');
            lastPageBtn.addEventListener('click', () => {
                console.log('🧭 Navigation vers dernière page');
                if (this.pdfDocument) {
                    this.goToPage(this.pdfDocument.numPages);
                }
            });
        } else {
            console.warn('⚠️ Bouton dernière page non trouvé');
        }

        // Contrôles mobiles de navigation
        const mobilePrevPageBtn = document.getElementById('mobilePrevPageBtn');
        if (mobilePrevPageBtn) {
            mobilePrevPageBtn.addEventListener('click', () => {
                this.previousPage();
            });
        }

        const mobileNextPageBtn = document.getElementById('mobileNextPageBtn');
        if (mobileNextPageBtn) {
            mobileNextPageBtn.addEventListener('click', () => {
                this.nextPage();
            });
        }

        const mobileFirstPageBtn = document.getElementById('mobileFirstPageBtn');
        if (mobileFirstPageBtn) {
            mobileFirstPageBtn.addEventListener('click', () => {
                this.goToPage(1);
            });
        }

        const mobileLastPageBtn = document.getElementById('mobileLastPageBtn');
        if (mobileLastPageBtn) {
            mobileLastPageBtn.addEventListener('click', () => {
                if (this.pdfDocument) {
                    this.goToPage(this.pdfDocument.numPages);
                }
            });
        }

        // Soumission du formulaire
        if (this.config.processFormId) {
            const processForm = document.getElementById(this.config.processFormId);
            if (processForm) {
                processForm.addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });
            } else {
                console.warn('⚠️ Formulaire de traitement non trouvé:', this.config.processFormId);
            }
        }
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

        // Support des événements tactiles pour mobile/tablette - PERMETTRE LE SCROLLING
        canvas.addEventListener('touchstart', (e) => {
            // Seulement si on est en mode dessin (signature live)
            if (type === 'signature' && this.isDrawingSignature) {
            e.preventDefault();
            e.stopPropagation();
            isDrawing = true;
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            lastX = (touch.clientX - rect.left) * scaleX;
            lastY = (touch.clientY - rect.top) * scaleY;
            }
        }, { passive: true });

        canvas.addEventListener('touchmove', (e) => {
            if (!isDrawing) return;
            // Seulement si on est en mode dessin (signature live)
            if (type === 'signature' && this.isDrawingSignature) {
            e.preventDefault();
            e.stopPropagation();
            
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const currentX = (touch.clientX - rect.left) * scaleX;
            const currentY = (touch.clientY - rect.top) * scaleY;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.strokeStyle = type === 'signature' ? '#28a745' : '#667eea';
            ctx.lineWidth = type === 'signature' ? 3 : 2;
            ctx.lineCap = 'round';
            ctx.stroke();

            lastX = currentX;
            lastY = currentY;
            }
        }, { passive: true });

        canvas.addEventListener('touchend', (e) => {
            // Seulement si on est en mode dessin (signature live)
            if (type === 'signature' && this.isDrawingSignature) {
            e.preventDefault();
            e.stopPropagation();
            isDrawing = false;
            }
        }, { passive: true });

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
        const cachetConfig = document.getElementById('cachetConfig');
        const liveSignatureArea = document.getElementById('liveSignatureArea');
        const liveParapheArea = document.getElementById('liveParapheArea');
        const liveCachetArea = document.getElementById('liveCachetArea');

        // Masquer toutes les sections
        if (signatureConfig) signatureConfig.style.display = 'none';
        if (parapheConfig) parapheConfig.style.display = 'none';
        if (cachetConfig) cachetConfig.style.display = 'none';
        if (liveSignatureArea) liveSignatureArea.style.display = 'none';
        if (liveParapheArea) liveParapheArea.style.display = 'none';
        if (liveCachetArea) liveCachetArea.style.display = 'none';

        // Afficher selon le type d'action
        switch (this.actionType) {
            case 'sign_only':
                if (signatureConfig) signatureConfig.style.display = 'block';
                break;
            case 'paraphe_only':
                if (parapheConfig) parapheConfig.style.display = 'block';
                break;
            case 'cachet_only':
                if (cachetConfig) cachetConfig.style.display = 'block';
                break;
            case 'both':
            case 'sign_paraphe':
                if (signatureConfig) signatureConfig.style.display = 'block';
                if (parapheConfig) parapheConfig.style.display = 'block';
                break;
            case 'sign_cachet':
                if (signatureConfig) signatureConfig.style.display = 'block';
                if (cachetConfig) cachetConfig.style.display = 'block';
                break;
            case 'paraphe_cachet':
                if (parapheConfig) parapheConfig.style.display = 'block';
                if (cachetConfig) cachetConfig.style.display = 'block';
                break;
            case 'all':
                if (signatureConfig) signatureConfig.style.display = 'block';
                if (parapheConfig) parapheConfig.style.display = 'block';
                if (cachetConfig) cachetConfig.style.display = 'block';
                break;
        }

        // Mettre à jour le bouton de soumission
        const submitBtn = document.getElementById(this.config.submitBtnId);
        if (submitBtn) {
            const submitText = submitBtn.querySelector('span');
            if (submitText) {
        
                switch (this.actionType) {
                    case 'sign_only':
                        submitText.textContent = 'Signer le Document';
                        break;
                    case 'paraphe_only':
                        submitText.textContent = 'Parapher le Document';
                        break;
                    case 'cachet_only':
                        submitText.textContent = 'Cacheter le Document';
                        break;
                    case 'both':
                    case 'sign_paraphe':
                        submitText.textContent = 'Signer & Parapher le Document';
                        break;
                    case 'sign_cachet':
                        submitText.textContent = 'Signer & Cacheter le Document';
                        break;
                    case 'paraphe_cachet':
                        submitText.textContent = 'Parapher & Cacheter le Document';
                        break;
                    case 'all':
                        submitText.textContent = 'Traiter Complètement le Document';
                        break;
                }
            }
        }
    }

    toggleLiveSignatureArea(show) {
        const area = document.getElementById('liveSignatureArea');
        if (area) area.style.display = show ? 'block' : 'none';
    }

    toggleLiveParapheArea(show) {
        const area = document.getElementById('liveParapheArea');
        if (area) area.style.display = show ? 'block' : 'none';
    }

    toggleLiveCachetArea(show) {
        const area = document.getElementById('liveCachetArea');
        if (area) area.style.display = show ? 'block' : 'none';
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
        this.scale = 0.8;
        this.renderPage(this.currentPage);
        this.showStatus('Zoom réinitialisé', 'info');
    }

    async autoFit() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        
        try {
            const page = await this.pdfDoc.getPage(this.currentPage);
            const viewport = page.getViewport({ scale: 1.0 });
            
            // Dimensions A4 standard (210mm x 297mm) en pixels (96 DPI)
            const a4Width = 794;  // 210mm * 96 DPI / 25.4mm
            const a4Height = 1123; // 297mm * 96 DPI / 25.4mm
            
            // Calculer l'échelle pour que le document rentre dans le conteneur comme une page A4
            const scaleWidth = (containerWidth - 40) / a4Width;
            const scaleHeight = (containerHeight - 40) / a4Height;
            
            // Prendre la plus petite échelle pour que la page A4 rentre dans le conteneur
            const optimalScale = Math.min(scaleWidth, scaleHeight);
            
            // Appliquer des limites pour une page A4 complète
            this.scale = Math.max(0.3, Math.min(optimalScale, 1.2)); // Entre 30% et 120%
            
            await this.renderPage(this.currentPage);
            this.showStatus(`Ajustement A4: ${Math.round(this.scale * 100)}%`, 'info');
        } catch (error) {
            console.error('Erreur lors de l\'ajustement automatique:', error);
            // Fallback à une échelle raisonnable pour A4
            this.scale = 0.6;
            await this.renderPage(this.currentPage);
        }
    }

    async forceFit() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        
        try {
            const page = await this.pdfDoc.getPage(this.currentPage);
            const viewport = page.getViewport({ scale: 1.0 });
            
            // Dimensions A4 standard (210mm x 297mm) en pixels (96 DPI)
            const a4Width = 794;  // 210mm * 96 DPI / 25.4mm
            const a4Height = 1123; // 297mm * 96 DPI / 25.4mm
            
            // Calculer l'échelle pour que le document rentre dans le conteneur comme une page A4
            const scaleWidth = (containerWidth - 80) / a4Width;   // 80px de marge
            const scaleHeight = (containerHeight - 80) / a4Height; // 80px de marge
            
            // Prendre la plus petite échelle pour que la page A4 rentre dans le conteneur
            const optimalScale = Math.min(scaleWidth, scaleHeight);
            
            // Appliquer des limites pour une page A4 complète
            this.scale = Math.max(0.2, Math.min(optimalScale, 1.0)); // Entre 20% et 100%
            
            await this.renderPage(this.currentPage);
            this.showStatus(`Ajustement A4 forcé: ${Math.round(this.scale * 100)}%`, 'info');
        } catch (error) {
            console.error('Erreur lors de l\'ajustement forcé:', error);
            // Fallback à une échelle raisonnable pour A4
            this.scale = 0.5;
            await this.renderPage(this.currentPage);
        }
    }

    updatePageInfo() {
        // Mise à jour de l'indicateur principal
        const pageInfo = document.getElementById(this.config.pageInfoId);
        if (pageInfo) {
            pageInfo.textContent = `Page ${this.currentPage} sur ${this.totalPages}`;
        }
        
        // Mise à jour des indicateurs mobiles
        const mobileCurrentPage = document.getElementById('mobileCurrentPage');
        const mobileTotalPages = document.getElementById('mobileTotalPages');
        const currentPageSpan = document.getElementById('currentPage');
        const totalPagesSpan = document.getElementById('totalPages');
        
        if (mobileCurrentPage) {
            mobileCurrentPage.textContent = this.currentPage;
        }
        if (mobileTotalPages) {
            mobileTotalPages.textContent = this.totalPages;
        }
        if (currentPageSpan) {
            currentPageSpan.textContent = this.currentPage;
        }
        if (totalPagesSpan) {
            totalPagesSpan.textContent = this.totalPages;
        }
        
        // Déclencher l'événement pageChanged
        document.dispatchEvent(new CustomEvent('pageChanged', {
            detail: { 
                currentPage: this.currentPage,
                totalPages: this.totalPages 
            }
        }));
    }
    
    // Méthode pour naviguer vers une page spécifique
    async goToPage(pageNumber) {
        if (pageNumber >= 1 && pageNumber <= this.totalPages) {
            this.currentPage = pageNumber;
            await this.renderPage(this.currentPage);
            this.updatePageInfo();
            this.updateNavigationButtons();
        }
    }
    
    // Méthode pour aller à la page précédente
    async goToPreviousPage() {
        if (this.currentPage > 1) {
            await this.goToPage(this.currentPage - 1);
        }
    }
    
    // Méthode pour aller à la page suivante
    async goToNextPage() {
        if (this.currentPage < this.totalPages) {
            await this.goToPage(this.currentPage + 1);
        }
    }
    
    // Méthode pour aller à la première page
    async goToFirstPage() {
        await this.goToPage(1);
    }
    
    // Méthode pour aller à la dernière page
    async goToLastPage() {
        await this.goToPage(this.totalPages);
    }

    // Méthode spécifique pour l'affichage A4
    async fitToA4() {
        const container = document.getElementById(this.config.containerId);
        if (!container) return;

        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        
        try {
            // Dimensions A4 standard (210mm x 297mm) en pixels (96 DPI)
            const a4Width = 794;  // 210mm * 96 DPI / 25.4mm
            const a4Height = 1123; // 297mm * 96 DPI / 25.4mm
            
            // Calculer l'échelle pour que le document rentre dans le conteneur comme une page A4
            const scaleWidth = (containerWidth - 60) / a4Width;
            const scaleHeight = (containerHeight - 60) / a4Height;
            
            // Prendre la plus petite échelle pour que la page A4 rentre dans le conteneur
            const optimalScale = Math.min(scaleWidth, scaleHeight);
            
            // Appliquer des limites pour une page A4 complète
            this.scale = Math.max(0.4, Math.min(optimalScale, 1.5)); // Entre 40% et 150%
            
            await this.renderPage(this.currentPage);
            this.showStatus(`Affichage A4: ${Math.round(this.scale * 100)}%`, 'info');
        } catch (error) {
            console.error('Erreur lors de l\'ajustement A4:', error);
            // Fallback à une échelle raisonnable pour A4
            this.scale = 0.7;
            await this.renderPage(this.currentPage);
        }
    }

    handleFormSubmit(e) {
        console.log('🚀 handleFormSubmit appelé - Prévention du rechargement de page');
        e.preventDefault(); // Empêcher la soumission par défaut
        
        console.log('📊 État actuel:', {
            signatures: this.signatures.length,
            paraphes: this.paraphes.length,
            actionType: this.actionType
        });
        
        // Vérifier qu'il y a au moins une signature ou un paraphe
        if (this.signatures.length === 0 && this.paraphes.length === 0) {
            console.warn('⚠️ Aucune signature ou paraphe à traiter');
            this.showStatus('Veuillez ajouter au moins une signature ou un paraphe', 'error');
            return;
        }
        
        // Remplir les champs cachés avant la soumission
        const actionTypeInput = document.getElementById(this.config.actionTypeInputId);
        if (actionTypeInput) {
            actionTypeInput.value = this.actionType;
        }
        
        // Récupérer les types sélectionnés
        const signatureType = document.querySelector('input[name="signature_type"]:checked')?.value || 'png';
        const parapheType = document.querySelector('input[name="paraphe_type"]:checked')?.value || 'png';
        
        const signatureTypeInput = document.getElementById(this.config.signatureTypeInputId);
        const parapheTypeInput = document.getElementById(this.config.parapheTypeInputId);
        
        if (signatureTypeInput) {
            signatureTypeInput.value = signatureType;
        }
        if (parapheTypeInput) {
            parapheTypeInput.value = parapheType;
        }
        
        // Données live
        const liveSignatureInput = document.getElementById(this.config.liveSignatureDataInputId);
        const liveParapheInput = document.getElementById(this.config.liveParapheDataInputId);
        
        if (liveSignatureInput) {
            liveSignatureInput.value = this.liveSignatureData || '';
        }
        if (liveParapheInput) {
            liveParapheInput.value = this.liveParapheData || '';
        }
        
        // Positions (convertir les coordonnées HTML vers PDF)
        if (this.signatures.length > 0) {
            const firstSignature = this.signatures[0];
            const signatureXInput = document.getElementById(this.config.signatureXInputId);
            const signatureYInput = document.getElementById(this.config.signatureYInputId);
            
            if (signatureXInput) {
                // Conversion des coordonnées HTML vers PDF
                const pdfX = this.convertHtmlToPdfX(firstSignature.x);
                signatureXInput.value = pdfX;
            }
            if (signatureYInput) {
                const pdfY = this.convertHtmlToPdfY(firstSignature.y, 'signature');
                signatureYInput.value = pdfY;
            }
        }
        
        if (this.paraphes.length > 0) {
            const firstParaphe = this.paraphes[0];
            const parapheXInput = document.getElementById(this.config.parapheXInputId);
            const parapheYInput = document.getElementById(this.config.parapheYInputId);
            
            if (parapheXInput) {
                const pdfX = this.convertHtmlToPdfX(firstParaphe.x);
                parapheXInput.value = pdfX;
            }
            if (parapheYInput) {
                const pdfY = this.convertHtmlToPdfY(firstParaphe.y, 'paraphe');
                parapheYInput.value = pdfY;
            }
        }

        this.showStatus('Génération du PDF final...', 'info');
        console.log('📄 Début de la génération du PDF final...');
        
        // Générer le PDF final côté client
        this.generateFinalPdf().then(() => {
            this.showStatus('PDF généré avec succès !', 'success');
            
            // NE PAS soumettre le formulaire pour éviter le rechargement de page
            // Le PDF est déjà envoyé au serveur via uploadPdfToServer
            console.log('✅ PDF généré et envoyé au serveur sans rechargement de page');
            
        }).catch(error => {
            console.error('❌ Erreur lors de la génération du PDF:', error);
            this.showStatus('Erreur lors de la génération du PDF', 'error');
        });
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

    // Méthodes pour gérer les boutons de signature et paraphe
    async submitForm() {
        console.log('📤 Soumission du formulaire...');
        console.log('📊 Signatures avant soumission:', this.signatures);
        console.log('📊 Paraphes avant soumission:', this.paraphes);
        
        // Mettre à jour les données du formulaire
        this.updateFormData();
        
        // Vérifier que les données sont bien dans le formulaire
        const signatureX = document.getElementById(this.config.signatureXInputId)?.value;
        const signatureY = document.getElementById(this.config.signatureYInputId)?.value;
        const actionType = document.getElementById(this.config.actionTypeInputId)?.value;
        
        console.log('🔍 Vérification des données du formulaire:');
        console.log('📍 Signature X:', signatureX);
        console.log('📍 Signature Y:', signatureY);
        console.log('🎯 Type d\'action:', actionType);
        
        // Si des signatures/paraphes sont présents, générer le PDF final
        if (this.signatures.length > 0 || this.paraphes.length > 0) {
            console.log('📄 Génération du PDF final avec signatures...');
            this.showStatus('Génération du PDF final...', 'info');
            
            try {
                await this.generateFinalPdf();
                console.log('✅ PDF final généré avec succès');
                this.showStatus('PDF généré avec succès !', 'success');
                
                // Le PDF est maintenant envoyé au serveur via uploadPdfToServer
                // Pas besoin de soumettre le formulaire
                console.log('✅ Processus terminé - PDF envoyé au serveur');
                return;
            } catch (error) {
                console.error('❌ Erreur lors de la génération du PDF:', error);
                this.showStatus('Erreur lors de la génération du PDF', 'error');
                return;
            }
        }
        
        // Si pas de signatures/paraphes, soumettre le formulaire normalement
        const form = document.getElementById(this.config.processFormId);
        if (form) {
            console.log('📋 Formulaire trouvé, soumission...');
            
            // Ajouter un délai pour s'assurer que les données sont bien mises à jour
            setTimeout(() => {
                console.log('⏰ Soumission du formulaire après délai...');
                form.submit();
            }, 100);
        } else {
            console.error('❌ Formulaire non trouvé');
        }
    }
    
    addSignature() {
        // PROTECTION : Éviter les appels multiples
        if (this.isAddingSignature) {
            console.log('⚠️ addSignature() déjà en cours, ignoré');
            return;
        }
        this.isAddingSignature = true;
        
        console.log('🎯 Méthode addSignature() appelée');
        console.log('🔍 Configuration signature:', {
            signatureUrl: this.config.signatureUrl,
            hasSignatureUrl: !!this.config.signatureUrl
        });
        
        if (!this.config.signatureUrl) {
            console.error('❌ Aucune signature configurée');
            this.showStatus('Aucune signature configurée pour cet utilisateur', 'error');
            this.isAddingSignature = false;
            return;
        }

        // Vérifier si le mode de positionnement est déjà actif
        if (this.isPositioningActive) {
            console.log('⚠️ Mode de positionnement déjà actif, ignoré');
            this.isAddingSignature = false;
            return;
        }

        // Position par défaut au centre
        let x = 100, y = 100;
        
        // Activer le mode de positionnement par clic
        this.enableClickPositioning('signature');

        // L'élément sera créé par enableClickPositioning après le clic
    }


    async addParaphe() {
        // PROTECTION : Éviter les appels multiples
        if (this.isAddingParaphe) {
            console.log('⚠️ addParaphe() déjà en cours, ignoré');
            return;
        }
        this.isAddingParaphe = true;
        
        // Récupérer l'URL du paraphe si elle n'est pas disponible
        let parapheUrl = this.config.parapheUrl;
        
        if (!parapheUrl) {
            try {
                const response = await fetch('/signatures/user-paraphe');
                const data = await response.json();
                
                if (data.success && data.parapheUrl) {
                    parapheUrl = data.parapheUrl;
                    this.config.parapheUrl = parapheUrl; // Mettre en cache
                } else {
                    this.showStatus('Aucun paraphe configuré pour cet utilisateur', 'error');
                    return;
                }
            } catch (error) {
                this.showStatus('Erreur lors de la récupération du paraphe', 'error');
                console.error('Erreur de récupération du paraphe:', error);
                return;
            }
        }

        // Tester si l'URL du paraphe est accessible
        try {
            const response = await fetch(parapheUrl, { method: 'HEAD' });
            if (!response.ok) {
                this.showStatus('Erreur: Le paraphe n\'est pas accessible', 'error');
                return;
            }
        } catch (error) {
            this.showStatus('Erreur: Impossible de charger le paraphe', 'error');
            console.error('Erreur de chargement du paraphe:', error);
            return;
        }

        // Position par défaut au centre
        let x = 100, y = 200;
        
        // Activer le mode de positionnement par clic
        this.enableClickPositioning('paraphe');

        // L'élément sera créé par enableClickPositioning après le clic
        
        // Mettre à jour le type d'action si nécessaire
        if (this.signatures.length > 0) {
            this.actionType = 'both';
        } else {
            this.actionType = 'paraphe_only';
        }
        
        this.updateInterface();
        this.showStatus('Paraphe ajouté - Glissez pour positionner', 'success');
    }

    /**
     * Ajouter un cachet au document
     */
    addCachet() {
        // PROTECTION : Éviter les appels multiples
        if (this.isAddingCachet) {
            console.log('⚠️ addCachet() déjà en cours, ignoré');
            return;
        }
        this.isAddingCachet = true;
        
        console.log('🎯 Méthode addCachet() appelée');
        console.log('🔍 Configuration cachet:', {
            cachetUrl: this.config.cachetUrl,
            hasCachetUrl: !!this.config.cachetUrl,
            userCachetUrl: this.userCachetUrl
        });
        
        // Utiliser userCachetUrl (chargé au démarrage) ou config.cachetUrl
        const cachetUrl = this.userCachetUrl || this.config.cachetUrl;
        
        if (!cachetUrl) {
            console.error('❌ Aucun cachet configuré');
            this.showStatus('Aucun cachet configuré pour cet utilisateur', 'error');
            this.isAddingCachet = false;
            return;
        }

        // Vérifier si le mode de positionnement est déjà actif
        if (this.isPositioningActive) {
            console.log('⚠️ Mode de positionnement déjà actif, ignoré');
            this.isAddingCachet = false;
            return;
        }

        // Position par défaut au centre
        let x = 100, y = 100;
        
        // Activer le mode de positionnement par clic
        this.enableClickPositioning('cachet');

        // L'élément sera créé par enableClickPositioning après le clic
    }

    /**
     * Ajouter signature ET paraphe combinés
     */
    async addSignAndParaphe() {
        if (!this.config.signatureUrl) {
            this.showStatus('Aucune signature configurée pour cet utilisateur', 'error');
            return;
        }

        // Vérifier le paraphe
        let parapheUrl = this.config.parapheUrl;
        if (!parapheUrl || parapheUrl === '/signatures/user-paraphe') {
            try {
                const response = await fetch('/signatures/user-paraphe');
                const data = await response.json();
                
                if (data.success && data.parapheUrl) {
                    parapheUrl = data.parapheUrl;
                    this.config.parapheUrl = parapheUrl;
                } else {
                    this.showStatus('Aucun paraphe configuré pour cet utilisateur', 'error');
                    return;
                }
            } catch (error) {
                this.showStatus('Erreur lors de la récupération du paraphe', 'error');
                return;
            }
        }

        // Ajouter la signature
        this.addSignature();
        
        // Ajouter le paraphe
        await this.addParaphe();
        
        // Définir le type d'action comme combiné
        this.actionType = 'both';
        
        this.updateInterface();
        this.showStatus('Signature et paraphe ajoutés - Glissez pour positionner', 'success');
    }

    /**
     * Afficher les signatures sur le PDF
     */
    renderSignatures(container) {
        // Supprimer les anciennes signatures
        const existingSignatures = container.querySelectorAll('.signature-overlay');
        existingSignatures.forEach(el => el.remove());

        // Afficher les signatures de la page courante
        this.signatures
            .filter(sig => sig.page === this.currentPage)
            .forEach(signature => {
                const signatureElement = this.createSignatureElement(signature);
                container.appendChild(signatureElement);
            });
    }

    /**
     * Afficher les paraphes sur le PDF
     */
    renderParaphes(container) {
        // Supprimer les anciens paraphes
        const existingParaphes = container.querySelectorAll('.paraphe-overlay');
        existingParaphes.forEach(el => el.remove());

        // Afficher les paraphes de la page courante
        this.paraphes
            .filter(paraphe => paraphe.page === this.currentPage)
            .forEach(paraphe => {
                const parapheElement = this.createParapheElement(paraphe);
                container.appendChild(parapheElement);
            });
    }

    /**
     * Créer un élément DOM pour un cachet
     */
    createCachetElement(cachet) {
        const cachetDiv = document.createElement('div');
        cachetDiv.className = 'cachet-overlay';
        cachetDiv.style.position = 'absolute';
        cachetDiv.style.left = cachet.x + 'px';
        cachetDiv.style.top = cachet.y + 'px';
        cachetDiv.style.width = cachet.width + 'px';
        cachetDiv.style.height = cachet.height + 'px';
        cachetDiv.style.border = '2px solid #8B5CF6';
        cachetDiv.style.borderRadius = '4px';
        cachetDiv.style.backgroundColor = 'rgba(139, 92, 246, 0.1)';
        cachetDiv.style.cursor = 'move';
        cachetDiv.style.zIndex = '1000';
        cachetDiv.draggable = true;
        cachetDiv.dataset.cachetId = cachet.id;

        if (cachet.url) {
            const img = document.createElement('img');
            img.src = cachet.url;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            img.style.borderRadius = '2px';
            
            // Gestion des erreurs d'image
            img.onerror = function() {
                console.error('Erreur de chargement de l\'image cachet:', cachet.url);
                // Remplacer par une icône de fallback
                const fallbackIcon = document.createElement('i');
                fallbackIcon.className = 'fas fa-exclamation-triangle';
                fallbackIcon.style.color = '#dc3545';
                fallbackIcon.style.fontSize = '16px';
                fallbackIcon.style.position = 'absolute';
                fallbackIcon.style.top = '50%';
                fallbackIcon.style.left = '50%';
                fallbackIcon.style.transform = 'translate(-50%, -50%)';
                cachetDiv.appendChild(fallbackIcon);
            };
            
            img.onload = function() {
                console.log('Image cachet chargée avec succès:', cachet.url);
            };
            
            cachetDiv.appendChild(img);
        } else {
            const icon = document.createElement('i');
            icon.className = 'fas fa-stamp';
            icon.style.color = '#8B5CF6';
            icon.style.fontSize = '16px';
            icon.style.position = 'absolute';
            icon.style.top = '50%';
            icon.style.left = '50%';
            icon.style.transform = 'translate(-50%, -50%)';
            cachetDiv.appendChild(icon);
        }

        return cachetDiv;
    }

    /**
     * Rendre les cachets sur le conteneur
     */
    renderCachets(container) {
        console.log('🎨 renderCachets appelée:', {
            containerExists: !!container,
            cachetsCount: this.cachets.length,
            currentPage: this.currentPage
        });
        
        // Supprimer les anciens cachets
        const existingCachets = container.querySelectorAll('.cachet-overlay');
        console.log('🗑️ Suppression de', existingCachets.length, 'cachets existants');
        existingCachets.forEach(el => el.remove());

        // Afficher les cachets de la page courante
        const currentPageCachets = this.cachets.filter(cachet => cachet.page === this.currentPage);
        console.log('📄 Cachets pour la page', this.currentPage, ':', currentPageCachets.length);
        
        currentPageCachets.forEach(cachet => {
            const cachetElement = this.createCachetElement(cachet);
            container.appendChild(cachetElement);
            console.log('✅ Cachet ajouté au DOM:', cachet);
        });
    }

    clearAll() {
        this.signatures = [];
        this.paraphes = [];
        this.cachets = [];
        this.renderSignatures(document.getElementById(this.config.containerId));
        this.renderParaphes(document.getElementById(this.config.containerId));
        this.renderCachets(document.getElementById(this.config.containerId));
        this.updateFormData();
        
        // Réinitialiser le type d'action
        this.actionType = this.config.defaultAction || 'sign_only';
        this.updateInterface();
        
        this.showStatus('Toutes les annotations ont été supprimées', 'info');
    }

    disableAllInteractions() {
        // Désactiver tous les boutons d'action
        const actionButtons = [
            this.config.addSignatureBtnId,
            this.config.addParapheBtnId,
            this.config.addCachetBtnId,
            this.config.clearAllBtnId,
            this.config.submitBtnId
        ];

        actionButtons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            }
        });

        // Désactiver les canvas de signature/paraphe
        if (this.signatureCanvas) {
            this.signatureCanvas.style.pointerEvents = 'none';
        }
        if (this.parapheCanvas) {
            this.parapheCanvas.style.pointerEvents = 'none';
        }

        // Désactiver le formulaire
        const form = document.getElementById(this.config.processFormId);
        if (form) {
            form.style.display = 'none';
        }

        this.showStatus('Document en mode lecture seule - Aucune modification possible', 'info');
    }

    updateFormData() {
        // Protection contre les appels multiples
        if (this.isUpdatingForm) {
            console.log('⚠️ updateFormData déjà en cours, ignoré');
            return;
        }
        this.isUpdatingForm = true;
        
        console.log('📝 Mise à jour des données du formulaire...');
        console.log('📊 Signatures:', this.signatures);
        console.log('📊 Paraphes:', this.paraphes);
        console.log('📊 Cachets:', this.cachets);
        
        // Mettre à jour les champs cachés du formulaire avec conversion des coordonnées
        if (this.config.signatureXInputId) {
            const signatureXInput = document.getElementById(this.config.signatureXInputId);
            if (signatureXInput) {
                if (this.signatures.length > 0) {
                    // Conversion des coordonnées HTML vers PDF (même logique que le mode normal)
                    const pdfX = this.convertHtmlToPdfX(this.signatures[0].x);
                    signatureXInput.value = pdfX;
                    console.log('📍 Signature X (HTML):', this.signatures[0].x, '→ (PDF):', pdfX);
                } else {
                    signatureXInput.value = '';
                }
            }
        }
        if (this.config.signatureYInputId) {
            const signatureYInput = document.getElementById(this.config.signatureYInputId);
            if (signatureYInput) {
                if (this.signatures.length > 0) {
                    // Conversion des coordonnées HTML vers PDF (même logique que le mode normal)
                    const pdfY = this.convertHtmlToPdfY(this.signatures[0].y, 'signature');
                    signatureYInput.value = pdfY;
                    console.log('📍 Signature Y (HTML):', this.signatures[0].y, '→ (PDF):', pdfY);
                } else {
                    signatureYInput.value = '';
                }
            }
        }
        if (this.config.parapheXInputId) {
            const parapheXInput = document.getElementById(this.config.parapheXInputId);
            if (parapheXInput) {
                if (this.paraphes.length > 0) {
                    // Conversion des coordonnées HTML vers PDF (même logique que le mode normal)
                    const pdfX = this.convertHtmlToPdfX(this.paraphes[0].x);
                    parapheXInput.value = pdfX;
                    console.log('📍 Paraphe X (HTML):', this.paraphes[0].x, '→ (PDF):', pdfX);
                } else {
                    parapheXInput.value = '';
                }
            }
        }
        if (this.config.parapheYInputId) {
            const parapheYInput = document.getElementById(this.config.parapheYInputId);
            if (parapheYInput) {
                if (this.paraphes.length > 0) {
                    // Conversion des coordonnées HTML vers PDF (même logique que le mode normal)
                    const pdfY = this.convertHtmlToPdfY(this.paraphes[0].y, 'paraphe');
                    parapheYInput.value = pdfY;
                    console.log('📍 Paraphe Y (HTML):', this.paraphes[0].y, '→ (PDF):', pdfY);
                } else {
                    parapheYInput.value = '';
                }
            }
        }
        
        // Mettre à jour les coordonnées des cachets
        if (this.config.cachetXInputId) {
            const cachetXInput = document.getElementById(this.config.cachetXInputId);
            if (cachetXInput) {
                if (this.cachets.length > 0) {
                    // Conversion des coordonnées HTML vers PDF (même logique que le mode normal)
                    const pdfX = this.convertHtmlToPdfX(this.cachets[0].x);
                    cachetXInput.value = pdfX;
                    console.log('📍 Cachet X (HTML):', this.cachets[0].x, '→ (PDF):', pdfX);
                } else {
                    cachetXInput.value = '';
                }
            }
        }
        if (this.config.cachetYInputId) {
            const cachetYInput = document.getElementById(this.config.cachetYInputId);
            if (cachetYInput) {
                if (this.cachets.length > 0) {
                    // Conversion des coordonnées HTML vers PDF (même logique que le mode normal)
                    const pdfY = this.convertHtmlToPdfY(this.cachets[0].y, 'cachet');
                    cachetYInput.value = pdfY;
                    console.log('📍 Cachet Y (HTML):', this.cachets[0].y, '→ (PDF):', pdfY);
                } else {
                    cachetYInput.value = '';
                }
            }
        }
        
        // Mettre à jour le type d'action
        if (this.config.actionTypeInputId) {
            const actionTypeInput = document.getElementById(this.config.actionTypeInputId);
            if (actionTypeInput) {
                if (this.signatures.length > 0 && this.paraphes.length > 0 && this.cachets.length > 0) {
                    actionTypeInput.value = 'all';
                } else if (this.signatures.length > 0 && this.paraphes.length > 0) {
                    actionTypeInput.value = 'both';
                } else if (this.signatures.length > 0 && this.cachets.length > 0) {
                    actionTypeInput.value = 'sign_cachet';
                } else if (this.paraphes.length > 0 && this.cachets.length > 0) {
                    actionTypeInput.value = 'paraphe_cachet';
                } else if (this.signatures.length > 0) {
                    actionTypeInput.value = 'sign_only';
                } else if (this.paraphes.length > 0) {
                    actionTypeInput.value = 'paraphe_only';
                } else if (this.cachets.length > 0) {
                    actionTypeInput.value = 'cachet_only';
                }
                console.log('🎯 Type d\'action:', actionTypeInput.value);
            }
        }
        
        // Ajouter les données de signature live si disponibles
        if (this.config.liveSignatureDataInputId && this.liveSignatureData) {
            const liveSignatureInput = document.getElementById(this.config.liveSignatureDataInputId);
            if (liveSignatureInput) {
                liveSignatureInput.value = this.liveSignatureData;
                console.log('✍️ Données signature live mises à jour');
            }
        }
        
        if (this.config.liveParapheDataInputId && this.liveParapheData) {
            const liveParapheInput = document.getElementById(this.config.liveParapheDataInputId);
            if (liveParapheInput) {
                liveParapheInput.value = this.liveParapheData;
                console.log('✍️ Données paraphe live mises à jour');
            }
        }
        
        if (this.config.liveCachetDataInputId && this.liveCachetData) {
            const liveCachetInput = document.getElementById(this.config.liveCachetDataInputId);
            if (liveCachetInput) {
                liveCachetInput.value = this.liveCachetData;
                console.log('✍️ Données cachet live mises à jour');
            }
        }
        
        console.log('✅ Données du formulaire mises à jour');
        
        // Réinitialiser le flag après un délai
        setTimeout(() => {
            this.isUpdatingForm = false;
        }, 100);
    }

    // Navigation entre les pages
    previousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.renderPage(this.currentPage);
            this.updatePageInfo();
            this.updateNavigationButtons();
            this.showStatus(`Page ${this.currentPage}`, 'info');
        }
    }

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.renderPage(this.currentPage);
            this.updatePageInfo();
            this.updateNavigationButtons();
            this.showStatus(`Page ${this.currentPage}`, 'info');
        }
    }

    updateNavigationButtons() {
        // Boutons principaux
        const prevBtn = document.getElementById(this.config.prevPageBtnId);
        const nextBtn = document.getElementById(this.config.nextPageBtnId);
        const firstBtn = document.getElementById('firstPageBtn');
        const lastBtn = document.getElementById('lastPageBtn');
        
        // Boutons mobiles
        const mobilePrevBtn = document.getElementById('mobilePrevPageBtn');
        const mobileNextBtn = document.getElementById('mobileNextPageBtn');
        const mobileFirstBtn = document.getElementById('mobileFirstPageBtn');
        const mobileLastBtn = document.getElementById('mobileLastPageBtn');
        
        // État des boutons (première page)
        const isFirstPage = this.currentPage <= 1;
        const isLastPage = this.currentPage >= this.totalPages;
        
        // Mise à jour des boutons précédent
        [prevBtn, mobilePrevBtn].forEach(btn => {
            if (btn) {
                btn.disabled = isFirstPage;
                btn.style.opacity = isFirstPage ? '0.5' : '1';
                btn.style.cursor = isFirstPage ? 'not-allowed' : 'pointer';
            }
        });
        
        // Mise à jour des boutons suivant
        [nextBtn, mobileNextBtn].forEach(btn => {
            if (btn) {
                btn.disabled = isLastPage;
                btn.style.opacity = isLastPage ? '0.5' : '1';
                btn.style.cursor = isLastPage ? 'not-allowed' : 'pointer';
            }
        });
        
        // Mise à jour des boutons première page
        [firstBtn, mobileFirstBtn].forEach(btn => {
            if (btn) {
                btn.disabled = isFirstPage;
                btn.style.opacity = isFirstPage ? '0.5' : '1';
                btn.style.cursor = isFirstPage ? 'not-allowed' : 'pointer';
            }
        });
        
        // Mise à jour des boutons dernière page
        [lastBtn, mobileLastBtn].forEach(btn => {
            if (btn) {
                btn.disabled = isLastPage;
                btn.style.opacity = isLastPage ? '0.5' : '1';
                btn.style.cursor = isLastPage ? 'not-allowed' : 'pointer';
            }
        });
    }

    /**
     * Convertir les coordonnées HTML vers PDF
     */
    convertHtmlToPdfX(htmlX) {
        // Obtenir les dimensions du conteneur PDF
        const pdfContainer = document.getElementById(this.config.pdfContainerId);
        if (!pdfContainer) {
            return htmlX;
        }
        
        const containerRect = pdfContainer.getBoundingClientRect();
        const containerWidth = containerRect.width;
        
        // Obtenir les dimensions du PDF affiché
        const pdfCanvas = pdfContainer.querySelector('canvas');
        if (!pdfCanvas) {
            return htmlX;
        }
        
        // Obtenir les dimensions réelles du canvas
        const canvasWidth = pdfCanvas.width;
        const canvasHeight = pdfCanvas.height;
        
        // Obtenir les dimensions réelles de la page PDF (en points)
        let pdfPageWidth = 595; // A4 par défaut
        
        if (this.pdfDoc && this.currentPage) {
            try {
                const page = this.pdfDoc.getPage(this.currentPage);
                const viewport = page.getViewport({ scale: 1.0 });
                pdfPageWidth = viewport.width;
            } catch (error) {
                // Utiliser les dimensions par défaut
            }
        }
        
        // Obtenir les dimensions affichées du canvas
        const canvasDisplayWidth = pdfCanvas.offsetWidth;
        
        // Calculer le facteur d'échelle réel entre le canvas et le conteneur
        const scaleFactor = canvasWidth / canvasDisplayWidth;
        
        // Convertir la position HTML en position canvas
        const canvasX = htmlX * scaleFactor;
        
        // Convertir la position canvas en position PDF
        const pdfX = (canvasX / canvasWidth) * pdfPageWidth;
        
        // Utiliser exactement la même logique que le mode normal (sans ajustements)
        // Log de débogage détaillé pour vérifier les calculs
        console.log(`🔍 DEBUG Conversion X (mode normal):`, {
            htmlX: htmlX,
            containerWidth: containerWidth,
            canvasDisplayWidth: canvasDisplayWidth,
            canvasWidth: canvasWidth,
            scaleFactor: scaleFactor,
            canvasX: canvasX,
            pdfPageWidth: pdfPageWidth,
            pdfX: pdfX,
            finalPdfX: Math.round(pdfX),
            ratio: (htmlX / containerWidth) * 100 + '%'
        });
        
        return Math.round(pdfX);
    }

    convertHtmlToPdfY(htmlY, elementType = 'signature') {
        // Obtenir les dimensions du conteneur PDF
        const pdfContainer = document.getElementById(this.config.pdfContainerId);
        if (!pdfContainer) {
            return htmlY;
        }
        
        const containerRect = pdfContainer.getBoundingClientRect();
        const containerHeight = containerRect.height;
        
        // Obtenir les dimensions du PDF affiché
        const pdfCanvas = pdfContainer.querySelector('canvas');
        if (!pdfCanvas) {
            return htmlY;
        }
        
        // Obtenir les dimensions réelles du canvas
        const canvasWidth = pdfCanvas.width;
        const canvasHeight = pdfCanvas.height;
        
        // Obtenir les dimensions réelles de la page PDF (en points)
        let pdfPageHeight = 842; // A4 par défaut
        
        if (this.pdfDoc && this.currentPage) {
            try {
                const page = this.pdfDoc.getPage(this.currentPage);
                const viewport = page.getViewport({ scale: 1.0 });
                pdfPageHeight = viewport.height;
            } catch (error) {
                // Utiliser les dimensions par défaut
            }
        }
        
        // Obtenir les dimensions affichées du canvas
        const canvasDisplayHeight = pdfCanvas.offsetHeight;
        
        // Calculer le facteur d'échelle réel entre le canvas et le conteneur
        const scaleFactor = canvasHeight / canvasDisplayHeight;
        
        // Convertir la position HTML en position canvas
        const canvasY = htmlY * scaleFactor;
        
        // Pour Y, on doit inverser car HTML a 0,0 en haut et PDF en bas
        // HTML: 0,0 en haut à gauche, PDF: 0,0 en bas à gauche
        const invertedCanvasY = canvasHeight - canvasY;
        
        // Convertir la position canvas en position PDF
        let pdfY = (invertedCanvasY / canvasHeight) * pdfPageHeight;
        
        // CORRECTION : Ajuster pour le décalage vers le haut selon le type d'élément
        // Le système de coordonnées PDF a (0,0) en bas à gauche
        // Nous devons ajuster pour que l'élément apparaisse au bon endroit
        if (elementType === 'cachet') {
            pdfY = pdfY - 10; // Ajustement réduit pour le cachet (correction décalage vers le haut)
        } else if (elementType === 'signature') {
            pdfY = pdfY - 10; // Ajustement augmenté pour la signature (correction décalage mobile)
        } else {
            pdfY = pdfY - 20; // Ajustement normal pour paraphe
        }
        
        // Ajuster pour tenir compte de la hauteur de l'élément
        // L'élément HTML est positionné par son coin supérieur gauche
        // Mais dans le PDF, on veut positionner par le coin supérieur gauche aussi
        let adjustedY = pdfY;
        
        // Calculer la hauteur de l'élément en points PDF pour un ajustement plus précis
        const elementHeight = elementType === 'signature' ? 
            Math.min(80, pdfPageHeight * 0.12) * 0.4 : // Hauteur signature
            elementType === 'cachet' ?
            Math.min(80, pdfPageHeight * 0.12) * 0.8 : // Hauteur cachet (plus carré)
            Math.min(80, pdfPageHeight * 0.12) * 0.4;  // Hauteur paraphe (même que signature)
        
        // Utiliser exactement la même logique que le mode normal (sans ajustements)
        // Log de débogage détaillé pour vérifier les calculs
        console.log(`🔍 DEBUG Conversion Y (mode normal) - ${elementType}:`, {
            htmlY: htmlY,
            containerHeight: containerHeight,
            canvasDisplayHeight: canvasDisplayHeight,
            canvasHeight: canvasHeight,
            scaleFactor: scaleFactor,
            canvasY: canvasY,
            invertedCanvasY: invertedCanvasY,
            pdfPageHeight: pdfPageHeight,
            pdfY: pdfY,
            finalPdfY: Math.round(Math.max(0, pdfY)),
            ratio: (htmlY / containerHeight) * 100 + '%',
            elementType: elementType,
            elementHeight: elementHeight
        });
        
        return Math.round(Math.max(0, pdfY));
    }

    /**
     * Activer le positionnement par clic
     */
    enableClickPositioning(type) {
        console.log('🎯 Activation du mode de positionnement pour:', type);
        
        // Vérifier si le mode de positionnement est déjà actif
        if (this.isPositioningActive) {
            console.log('⚠️ Mode de positionnement déjà actif, ignoré');
            return;
        }
        
        // SOLUTION MOBILE : Détecter si on est sur mobile et éviter le mode signature
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                        ('ontouchstart' in window) || 
                        (navigator.maxTouchPoints > 0);
        
        if (isMobile) {
            console.log('📱 Mode mobile détecté - positionnement par clic/touch sur canvas');
            this.isPositioningActive = false;
            
            // Écouter les clics/touches sur le canvas PDF directement
            const pdfContainer = document.getElementById(this.config.pdfContainerId);
            if (pdfContainer) {
                const canvas = pdfContainer.querySelector('canvas');
                if (canvas) {
                    console.log('🎯 Mode mobile - écoute des clics/touches sur le canvas');
                    
                    // Fonction pour gérer le positionnement
                    const handlePositioning = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Obtenir les coordonnées du clic/touch
                        let clientX, clientY;
                        if (e.touches && e.touches.length > 0) {
                            clientX = e.touches[0].clientX;
                            clientY = e.touches[0].clientY;
                        } else {
                            clientX = e.clientX;
                            clientY = e.clientY;
                        }
                        
                        // Calculer les coordonnées relatives au conteneur
                        const containerRect = pdfContainer.getBoundingClientRect();
                        const x = clientX - containerRect.left;
                        const y = clientY - containerRect.top;
                        
                        // Calculer les coordonnées PDF
                        const pdfX = this.convertHtmlToPdfX(x);
                        const pdfY = this.convertHtmlToPdfY(y, type);
                        
                        console.log('📍 DEBUG Positionnement mobile (clic/touch):', { 
                            x, y, pdfX, pdfY,
                            clientX, clientY,
                            containerRect: containerRect,
                            type: type,
                            ratioX: (x / containerRect.width) * 100 + '%',
                            ratioY: (y / containerRect.height) * 100 + '%',
                            finalPdfX: Math.round(pdfX),
                            finalPdfY: Math.round(pdfY)
                        });
                        
                        // Créer l'élément à la position cliquée
                        if (type === 'signature') {
                            this.createSignatureAtPosition(x, y, pdfX, pdfY);
                        } else if (type === 'paraphe') {
                            this.createParapheAtPosition(x, y, pdfX, pdfY);
                        } else if (type === 'cachet') {
                            this.createCachetAtPosition(x, y, pdfX, pdfY);
                        }
                        
                        // Supprimer les écouteurs après utilisation
                        canvas.removeEventListener('click', handlePositioning);
                        canvas.removeEventListener('touchstart', handlePositioning);
                    };
                    
                    // Ajouter les écouteurs
                    canvas.addEventListener('click', handlePositioning);
                    canvas.addEventListener('touchstart', handlePositioning);
                    
                    // Afficher un message à l'utilisateur
                    this.showStatus('Cliquez/touchez sur le PDF pour positionner l\'élément', 'info');
                } else {
                    console.warn('⚠️ Canvas PDF non trouvé, utilisation du centre du conteneur');
                    const rect = pdfContainer.getBoundingClientRect();
                    const x = rect.width / 2;
                    const y = rect.height / 2;
                    
                    // Calculer les coordonnées PDF pour le fallback
                    const pdfX = this.convertHtmlToPdfX(x);
                    const pdfY = this.convertHtmlToPdfY(y, type);
                    
                    // Créer l'élément directement avec les coordonnées PDF
                    if (type === 'signature') {
                        this.createSignatureAtPosition(x, y, pdfX, pdfY);
                    } else if (type === 'paraphe') {
                        this.createParapheAtPosition(x, y, pdfX, pdfY);
                    } else if (type === 'cachet') {
                        this.createCachetAtPosition(x, y, pdfX, pdfY);
                    }
                }
            }
            return;
        }
        
        // Mode desktop : utiliser l'overlay normal
        this.isPositioningActive = true;
        console.log('🖥️ Mode desktop - overlay activé');
        
        const pdfContainer = document.getElementById(this.config.pdfContainerId);
        if (!pdfContainer) {
            console.error('❌ Conteneur PDF non trouvé:', this.config.pdfContainerId);
            this.isPositioningActive = false;
            return;
        }

        console.log('✅ Conteneur PDF trouvé, ajout de l\'overlay...');
        
        // Ajouter un overlay pour capturer les clics
        const overlay = document.createElement('div');
        overlay.id = 'positioning-overlay';
        overlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 123, 255, 0.1);
            cursor: crosshair;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        `;
        const typeLabels = {
            'signature': 'signature',
            'paraphe': 'paraphe',
            'cachet': 'cachet'
        };
        overlay.textContent = `Cliquez pour positionner le ${typeLabels[type] || type}`;
        
        pdfContainer.style.position = 'relative';
        pdfContainer.appendChild(overlay);
        
        console.log('✅ Overlay ajouté, attente du clic...');

        // Fonction unifiée pour obtenir les coordonnées précises
        const getPreciseCoordinates = (e) => {
            let clientX, clientY;
            
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else if (e.changedTouches && e.changedTouches.length > 0) {
                // Fallback pour touchend
                clientX = e.changedTouches[0].clientX;
                clientY = e.changedTouches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }
            
            // Vérifier que les coordonnées sont valides
            if (isNaN(clientX) || isNaN(clientY) || clientX === undefined || clientY === undefined) {
                console.warn('Coordonnées invalides détectées:', { clientX, clientY, event: e, touches: e.touches, changedTouches: e.changedTouches });
                return { x: 0, y: 0 };
            }
            
            return {
                x: clientX,
                y: clientY
            };
        };

        // Fonction pour positionner l'élément avec précision (même logique que le mode normal)
        const positionElement = (e) => {
            console.log('🖱️ Clic détecté sur l\'overlay');
            const coords = getPreciseCoordinates(e);
            const rect = pdfContainer.getBoundingClientRect();
            
            // Calculer les coordonnées relatives au conteneur
            let x = coords.x - rect.left;
            let y = coords.y - rect.top;
            
            // Vérifier et corriger les coordonnées invalides
            if (isNaN(x) || isNaN(y) || x === 0 || y === 0) {
                console.warn('Coordonnées calculées invalides, utilisation des coordonnées par défaut');
                x = rect.width / 2; // Centre horizontal
                y = rect.height / 2; // Centre vertical
                console.log('📍 Coordonnées par défaut appliquées:', { x, y, rect: rect });
            }
            
            // S'assurer que les coordonnées sont dans les limites du conteneur
            x = Math.max(0, Math.min(x, rect.width));
            y = Math.max(0, Math.min(y, rect.height));
            
            // Utiliser exactement la même logique que le mode normal
            console.log('📍 DEBUG Coordonnées du clic (HTML) - Mode Desktop:', { 
                x, y, rect: rect,
                coords: coords,
                clientX: coords.x,
                clientY: coords.y,
                containerLeft: rect.left,
                containerTop: rect.top
            });
            
            // Convertir les coordonnées exactement comme dans le mode normal
            let pdfX, pdfY;
            
            pdfX = this.convertHtmlToPdfX(x);
            pdfY = this.convertHtmlToPdfY(y, type);
            
            console.log('📍 DEBUG Coordonnées converties (PDF) - Mode Desktop:', { 
                pdfX, pdfY,
                originalX: x,
                originalY: y,
                type: type
            });
            console.log('📍 DEBUG Mode desktop - conversion identique au mode normal:', {
                htmlX: x, htmlY: y,
                pdfX: pdfX, pdfY: pdfY,
                type: type
            });
            
            // Supprimer l'overlay
            overlay.remove();
            console.log('🗑️ Overlay supprimé');
            
            // Réinitialiser le flag de positionnement
            this.isPositioningActive = false;
            
            // Réinitialiser le flag de protection contre les appels multiples
            setTimeout(() => {
                isProcessing = false;
            }, 1000);
            
            // Réinitialiser les flags d'ajout
            setTimeout(() => {
                if (type === 'signature') {
                    this.isAddingSignature = false;
                } else if (type === 'paraphe') {
                    this.isAddingParaphe = false;
                } else if (type === 'cachet') {
                    this.isAddingCachet = false;
                }
            }, 1000);
            
            // Créer l'élément à la position cliquée/touchée
            if (type === 'signature') {
                console.log('✍️ DEBUG Création de la signature à la position:', { 
                    x, y, pdfX, pdfY,
                    hasPdfX: pdfX !== null,
                    hasPdfY: pdfY !== null,
                    finalPdfX: pdfX ? Math.round(pdfX) : 'null',
                    finalPdfY: pdfY ? Math.round(pdfY) : 'null'
                });
                // Créer la signature avec les coordonnées HTML pour l'affichage
                this.createSignatureAtPosition(x, y);
                // Mettre à jour les coordonnées PDF pour la génération finale
                if (this.signatures.length > 0) {
                    this.signatures[this.signatures.length - 1].pdfX = pdfX;
                    this.signatures[this.signatures.length - 1].pdfY = pdfY;
                }
                
                // Déclencher l'événement de fin de signature
                setTimeout(() => {
                    document.dispatchEvent(new CustomEvent('signatureCompleted', {
                        detail: { type: 'signature', x: x, y: y, pdfX: pdfX, pdfY: pdfY }
                    }));
                    console.log('🎉 Événement signatureCompleted déclenché');
                }, 100);
                
            } else if (type === 'paraphe') {
                console.log('✍️ Création du paraphe à la position:', { x, y, pdfX, pdfY });
                // Créer le paraphe avec les coordonnées HTML pour l'affichage
                this.createParapheAtPosition(x, y).then(() => {
                    // Mettre à jour les coordonnées PDF pour la génération finale
                    if (this.paraphes.length > 0) {
                        this.paraphes[this.paraphes.length - 1].pdfX = pdfX;
                        this.paraphes[this.paraphes.length - 1].pdfY = pdfY;
                    }
                    
                    // Déclencher l'événement de fin de paraphe
                    setTimeout(() => {
                        document.dispatchEvent(new CustomEvent('parapheCompleted', {
                            detail: { type: 'paraphe', x: x, y: y, pdfX: pdfX, pdfY: pdfY }
                        }));
                        console.log('🎉 Événement parapheCompleted déclenché');
                    }, 100);
                });
            } else if (type === 'cachet') {
                console.log('🏷️ DEBUG Création du cachet à la position:', { 
                    x, y, pdfX, pdfY,
                    hasPdfX: pdfX !== null,
                    hasPdfY: pdfY !== null,
                    finalPdfX: pdfX ? Math.round(pdfX) : 'null',
                    finalPdfY: pdfY ? Math.round(pdfY) : 'null'
                });
                // Créer le cachet avec les coordonnées HTML pour l'affichage
                this.createCachetAtPosition(x, y).then(() => {
                    // Mettre à jour les coordonnées PDF pour la génération finale
                    if (this.cachets.length > 0) {
                        this.cachets[this.cachets.length - 1].pdfX = pdfX;
                        this.cachets[this.cachets.length - 1].pdfY = pdfY;
                    }
                    
                    // Déclencher l'événement de fin de cachet
                    setTimeout(() => {
                        document.dispatchEvent(new CustomEvent('cachetCompleted', {
                            detail: { type: 'cachet', x: x, y: y, pdfX: pdfX, pdfY: pdfY }
                        }));
                        console.log('🎉 Événement cachetCompleted déclenché');
                    }, 100);
                });
            }
        };

        // SOLUTION PRATIQUE : Désactiver le mode signature en cliquant en dehors
        const handleOutsideClick = (e) => {
            // Vérifier si le clic est en dehors de l'overlay
            if (!overlay.contains(e.target)) {
                console.log('👆 Clic en dehors de l\'overlay - désactivation du mode signature');
                overlay.remove();
                this.isPositioningActive = false;
                this.disableSignatureMode();
            }
        };

        // Capturer le clic
        // PROTECTION : Éviter les appels multiples
        let isProcessing = false;
        
        overlay.addEventListener('click', (e) => {
            if (isProcessing) {
                console.log('⚠️ Événement click ignoré - traitement en cours');
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            isProcessing = true;
            positionElement(e);
        });
        
        // Capturer le touchstart pour une meilleure précision
        overlay.addEventListener('touchstart', (e) => {
            if (isProcessing) {
                console.log('⚠️ Événement touchstart ignoré - traitement en cours');
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            isProcessing = true;
            positionElement(e);
        }, { passive: false });
        
        // Ajouter l'événement de clic en dehors
        document.addEventListener('click', handleOutsideClick);
        
        // Nettoyer l'événement quand l'overlay est supprimé
        const originalRemove = overlay.remove;
        overlay.remove = function() {
            document.removeEventListener('click', handleOutsideClick);
            return originalRemove.call(this);
        };
        
        // Capturer le touchend comme fallback
        overlay.addEventListener('touchend', (e) => {
            e.preventDefault();
            e.stopPropagation();
            // Ne pas appeler positionElement ici pour éviter les doubles appels
        }, { passive: false });
    }

    /**
     * Créer une signature à la position spécifiée
     */
    createSignatureAtPosition(x, y, pdfX = null, pdfY = null) {
        // PROTECTION : Éviter les appels multiples
        if (this.isCreatingSignature) {
            console.log('⚠️ Signature déjà en cours de création, ignoré');
            return;
        }
        this.isCreatingSignature = true;
        
        console.log('✍️ DEBUG Création de la signature à la position:', { 
            x, y, pdfX, pdfY,
            hasPdfX: pdfX !== null,
            hasPdfY: pdfY !== null,
            finalPdfX: pdfX ? Math.round(pdfX) : 'null',
            finalPdfY: pdfY ? Math.round(pdfY) : 'null'
        });
        
        // Calculer les dimensions proportionnelles pour l'affichage (réduites)
        const container = document.getElementById(this.config.pdfContainerId);
        const containerWidth = container ? container.getBoundingClientRect().width : 600;
        const displayWidth = Math.min(80, containerWidth * 0.15);
        const displayHeight = displayWidth * 0.4;
        
        const signature = {
            id: Date.now(),
            page: this.currentPage,
            x: x,
            y: y,
            width: displayWidth,
            height: displayHeight,
            url: this.userSignatureUrl || this.config.signatureUrl,
            // Stocker les coordonnées PDF si fournies (mode mobile)
            pdfX: pdfX,
            pdfY: pdfY
        };

        this.signatures.push(signature);
        console.log('✅ Signature ajoutée:', signature);
        console.log('📊 Total signatures:', this.signatures.length);
        this.renderSignatures(document.getElementById(this.config.containerId));
        this.updateFormData();
        this.showStatus('Signature ajoutée - Glissez pour ajuster la position', 'success');
        
        // Activer le glisser-déposer pour cette signature
        this.enableDragAndDrop(signature.id, 'signature');
        
        // DÉSACTIVER AUTOMATIQUEMENT le mode signature après placement
        // Pour permettre le défilement immédiatement
        this.disableSignatureMode();
        
        // EMPÊCHER la réactivation du mode signature
        this.preventSignatureModeReactivation();
        
        console.log('🎯 Signature placée - Mode signature désactivé automatiquement');
        
        // Réinitialiser le flag de protection
        setTimeout(() => {
            this.isCreatingSignature = false;
        }, 1000);
    }

    /**
     * Créer un paraphe à la position spécifiée
     */
    async createParapheAtPosition(x, y, pdfX = null, pdfY = null) {
        // PROTECTION : Éviter les appels multiples
        if (this.isCreatingParaphe) {
            console.log('⚠️ Paraphe déjà en cours de création, ignoré');
            return;
        }
        this.isCreatingParaphe = true;
        
        console.log('✍️ Création du paraphe à la position:', { x, y, pdfX, pdfY });
        
        // Récupérer l'URL du paraphe si elle n'est pas disponible
        let parapheUrl = this.config.parapheUrl;
        
        if (!parapheUrl || parapheUrl === '/signatures/user-paraphe') {
            try {
                const response = await fetch('/signatures/user-paraphe');
                const data = await response.json();
                
                if (data.success && data.parapheUrl) {
                    parapheUrl = data.parapheUrl;
                    this.config.parapheUrl = parapheUrl; // Mettre en cache
                } else {
                    this.showStatus('Aucun paraphe configuré pour cet utilisateur', 'error');
                    return;
                }
            } catch (error) {
                this.showStatus('Erreur lors de la récupération du paraphe', 'error');
                console.error('Erreur de récupération du paraphe:', error);
                return;
            }
        }

        // Calculer les dimensions proportionnelles pour l'affichage (réduites)
        const container = document.getElementById(this.config.pdfContainerId);
        const containerWidth = container ? container.getBoundingClientRect().width : 600;
        const displayWidth = Math.min(60, containerWidth * 0.12);
        const displayHeight = displayWidth * 0.4;
        
        const paraphe = {
            id: Date.now(),
            page: this.currentPage,
            x: x,
            y: y,
            width: displayWidth,
            height: displayHeight,
            url: parapheUrl,
            // Stocker les coordonnées PDF si fournies (mode mobile)
            pdfX: pdfX,
            pdfY: pdfY
        };

        this.paraphes.push(paraphe);
        this.renderParaphes(document.getElementById(this.config.containerId));
        this.updateFormData();
        this.showStatus('Paraphe ajouté - Glissez pour ajuster la position', 'success');
        
        // Activer le glisser-déposer pour ce paraphe
        this.enableDragAndDrop(paraphe.id, 'paraphe');
        
        // DÉSACTIVER AUTOMATIQUEMENT le mode signature après placement
        // Pour permettre le défilement immédiatement
        this.disableSignatureMode();
        
        // EMPÊCHER la réactivation du mode signature
        this.preventSignatureModeReactivation();
        
        console.log('🎯 Paraphe placé - Mode signature désactivé automatiquement');
        
        // Réinitialiser le flag de protection
        setTimeout(() => {
            this.isCreatingParaphe = false;
        }, 1000);
    }

    /**
     * Créer un cachet à la position spécifiée
     */
    async createCachetAtPosition(x, y, pdfX = null, pdfY = null) {
        // PROTECTION : Éviter les appels multiples
        if (this.isCreatingCachet) {
            console.log('⚠️ Cachet déjà en cours de création, ignoré');
            return;
        }
        this.isCreatingCachet = true;
        
        console.log('🏷️ DEBUG createCachetAtPosition appelée:', { 
            x, y, pdfX, pdfY,
            hasPdfX: pdfX !== null,
            hasPdfY: pdfY !== null,
            finalPdfX: pdfX ? Math.round(pdfX) : 'null',
            finalPdfY: pdfY ? Math.round(pdfY) : 'null'
        });
        
        // Utiliser userCachetUrl (chargé au démarrage) ou config.cachetUrl
        const cachetUrl = this.userCachetUrl || this.config.cachetUrl;
        
        if (!cachetUrl) {
            console.error('❌ Aucun cachet configuré');
            this.showStatus('Vous devez d\'abord ajouter un cachet dans votre profil', 'error');
            return;
        }

        // Calculer les dimensions proportionnelles pour l'affichage
        const container = document.getElementById(this.config.pdfContainerId);
        const containerWidth = container ? container.getBoundingClientRect().width : 600;
        const displayWidth = Math.min(80, containerWidth * 0.15);
        const displayHeight = displayWidth * 0.8; // Les cachets sont généralement carrés ou un peu plus hauts
        
        const cachet = {
            id: Date.now(),
            page: this.currentPage,
            x: x,
            y: y,
            width: displayWidth,
            height: displayHeight,
            url: cachetUrl,
            // Stocker les coordonnées PDF si fournies (mode mobile)
            pdfX: pdfX,
            pdfY: pdfY
        };

        this.cachets.push(cachet);
        console.log('✅ Cachet ajouté:', cachet);
        console.log('📊 Total cachets:', this.cachets.length);
        
        this.renderCachets(document.getElementById(this.config.containerId));
        this.updateFormData();
        this.showStatus('Cachet ajouté - Glissez pour ajuster la position', 'success');
        
        // Activer le glisser-déposer pour ce cachet
        this.enableDragAndDrop(cachet.id, 'cachet');
        
        // DÉSACTIVER AUTOMATIQUEMENT le mode signature après placement
        // Pour permettre le défilement immédiatement
        this.disableSignatureMode();
        
        // EMPÊCHER la réactivation du mode signature
        this.preventSignatureModeReactivation();
        
        console.log('🎯 Cachet placé - Mode signature désactivé automatiquement');
        
        // Réinitialiser le flag de protection
        setTimeout(() => {
            this.isCreatingCachet = false;
        }, 1000);
    }

    /**
     * Désactiver le mode signature pour permettre le défilement
     */
    disableSignatureMode() {
        console.log('🔄 Désactivation du mode signature...');
        
        // Désactiver le flag de positionnement
        this.isPositioningActive = false;
        
        // SOLUTION RADICALE : Forcer la désactivation immédiate
        const pdfContainer = document.getElementById(this.config.pdfContainerId);
        if (pdfContainer) {
            pdfContainer.classList.remove('signature-mode', 'scroll-mode');
            pdfContainer.style.overflow = 'auto';
            pdfContainer.style.touchAction = 'pan-x pan-y pinch-zoom';
            pdfContainer.style.webkitOverflowScrolling = 'touch';
            pdfContainer.style.overscrollBehavior = 'auto';
            
            // Forcer les propriétés sur le canvas
            const canvas = pdfContainer.querySelector('canvas');
            if (canvas) {
                canvas.style.touchAction = 'pan-x pan-y pinch-zoom';
                canvas.style.pointerEvents = 'auto';
                canvas.style.overflow = 'auto';
                canvas.style.webkitOverflowScrolling = 'touch';
                canvas.style.overscrollBehavior = 'auto';
            }
            
            // S'assurer que le body permet le défilement
            document.body.style.overflow = '';
            document.body.style.touchAction = 'pan-x pan-y pinch-zoom';
            
            // DIAGNOSTIC : Vérifier l'état après désactivation
            setTimeout(() => {
                const hasSignatureMode = pdfContainer.classList.contains('signature-mode');
                console.log('🔍 DIAGNOSTIC - Mode signature après désactivation:', hasSignatureMode);
                if (hasSignatureMode) {
                    console.log('⚠️ PROBLÈME: Mode signature réactivé par un autre mécanisme!');
                }
            }, 200);
        }
        
        // Déclencher l'événement de désactivation du mode signature
        document.dispatchEvent(new CustomEvent('signatureModeDisabled', {
            detail: { timestamp: Date.now() }
        }));
        
        console.log('✅ Mode signature désactivé - défilement autorisé');
    }

    /**
     * Empêcher la réactivation du mode signature
     */
    preventSignatureModeReactivation() {
        console.log('🛡️ Protection contre la réactivation du mode signature...');
        
        const pdfContainer = document.getElementById(this.config.pdfContainerId);
        if (!pdfContainer) return;
        
        // Surveiller les changements de classe et les empêcher
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (pdfContainer.classList.contains('signature-mode')) {
                        console.log('🚫 TENTATIVE DE RÉACTIVATION BLOQUÉE');
                        pdfContainer.classList.remove('signature-mode');
                        
                        // Forcer les propriétés de défilement
                        pdfContainer.style.overflow = 'auto';
                        pdfContainer.style.touchAction = 'pan-x pan-y pinch-zoom';
                        pdfContainer.style.webkitOverflowScrolling = 'touch';
                        pdfContainer.style.overscrollBehavior = 'auto';
                    }
                }
            });
        });
        
        observer.observe(pdfContainer, { 
            attributes: true, 
            attributeFilter: ['class'] 
        });
        
        // Nettoyer l'observer après 10 secondes
        setTimeout(() => {
            observer.disconnect();
            console.log('🛡️ Protection contre la réactivation désactivée');
        }, 10000);
    }

    /**
     * Activer le glisser-déposer pour un élément
     */
    enableDragAndDrop(elementId, type) {
        const pdfContainer = document.getElementById(this.config.pdfContainerId);
        if (!pdfContainer) return;

        // Trouver l'élément dans le DOM
        const element = pdfContainer.querySelector(`[data-${type}-id="${elementId}"]`);
        if (!element) return;

        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let initialX = 0;
        let initialY = 0;

        // Styles pour indiquer que l'élément est glissable
        element.style.cursor = 'move';
        element.style.border = '2px solid #007bff';
        element.style.boxShadow = '0 4px 8px rgba(0, 123, 255, 0.3)';
        element.style.transition = 'all 0.2s ease';
        element.style.userSelect = 'none';
        
        // Ajouter un titre pour indiquer que l'élément est glissable
        element.title = 'Glissez pour repositionner';

        // Fonction unifiée pour obtenir les coordonnées précises
        const getPreciseCoordinates = (e) => {
            let clientX, clientY;
            
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else if (e.changedTouches && e.changedTouches.length > 0) {
                // Fallback pour touchend
                clientX = e.changedTouches[0].clientX;
                clientY = e.changedTouches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }
            
            // Vérifier que les coordonnées sont valides
            if (isNaN(clientX) || isNaN(clientY) || clientX === undefined || clientY === undefined) {
                console.warn('Coordonnées invalides détectées:', { clientX, clientY, event: e, touches: e.touches, changedTouches: e.changedTouches });
                return { x: 0, y: 0 };
            }
            
            return {
                x: clientX,
                y: clientY
            };
        };

        // Fonction unifiée pour démarrer le drag
        const startDrag = (e) => {
            // Éviter les conflits entre souris et tactile
            if (e.type === 'mousedown' && e.touches) return;
            if (e.type === 'touchstart' && !e.touches) return;
            
            isDragging = true;
            const coords = getPreciseCoordinates(e);
            startX = coords.x;
            startY = coords.y;
            
            // Obtenir la position actuelle avec précision
            const rect = element.getBoundingClientRect();
            const containerRect = pdfContainer.getBoundingClientRect();
            initialX = rect.left - containerRect.left;
            initialY = rect.top - containerRect.top;
            
            element.style.zIndex = '1001';
            element.style.opacity = '0.8';
            element.style.transform = 'scale(1.05)';
            element.style.boxShadow = '0 8px 16px rgba(0, 123, 255, 0.5)';
            element.style.border = '2px solid #0056b3';
            
            e.preventDefault();
        };

        // Support des événements tactiles pour mobile/tablette
        element.addEventListener('touchstart', startDrag, { passive: false });

        // Fonction unifiée pour le mouvement
        const moveDrag = (e) => {
            if (!isDragging) return;
            
            // Éviter les conflits entre souris et tactile
            if (e.type === 'mousemove' && e.touches) return;
            if (e.type === 'touchmove' && !e.touches) return;
            
            const coords = getPreciseCoordinates(e);
            const deltaX = coords.x - startX;
            const deltaY = coords.y - startY;
            
            const newX = initialX + deltaX;
            const newY = initialY + deltaY;
            
            // Limiter aux limites du conteneur avec précision
            const containerRect = pdfContainer.getBoundingClientRect();
            const maxX = containerRect.width - element.offsetWidth;
            const maxY = containerRect.height - element.offsetHeight;
            
            const constrainedX = Math.max(0, Math.min(newX, maxX));
            const constrainedY = Math.max(0, Math.min(newY, maxY));
            
            element.style.left = constrainedX + 'px';
            element.style.top = constrainedY + 'px';
            
            // Mettre à jour les données avec précision
            if (type === 'signature') {
                const signature = this.signatures.find(s => s.id == elementId);
                if (signature) {
                    signature.x = constrainedX;
                    signature.y = constrainedY;
                    // Recalculer les dimensions si nécessaire (réduites)
                    const container = document.getElementById(this.config.pdfContainerId);
                    if (container) {
                        const containerWidth = container.getBoundingClientRect().width;
                        const displayWidth = Math.min(80, containerWidth * 0.15);
                        const displayHeight = displayWidth * 0.4;
                        signature.width = displayWidth;
                        signature.height = displayHeight;
                    }
                }
            } else if (type === 'paraphe') {
                const paraphe = this.paraphes.find(p => p.id == elementId);
                if (paraphe) {
                    paraphe.x = constrainedX;
                    paraphe.y = constrainedY;
                    // Recalculer les dimensions si nécessaire (réduites)
                    const container = document.getElementById(this.config.pdfContainerId);
                    if (container) {
                        const containerWidth = container.getBoundingClientRect().width;
                        const displayWidth = Math.min(60, containerWidth * 0.12);
                        const displayHeight = displayWidth * 0.4;
                        paraphe.width = displayWidth;
                        paraphe.height = displayHeight;
                    }
                }
            }
            
            // Debounce pour éviter les appels trop fréquents pendant le drag
            if (this.updateFormDataTimeout) {
                clearTimeout(this.updateFormDataTimeout);
            }
            this.updateFormDataTimeout = setTimeout(() => {
            this.updateFormData();
            }, 100); // Délai de 100ms
        };

        document.addEventListener('touchmove', (e) => {
            // Seulement bloquer le scrolling si on est en train de faire du drag
            if (isDragging) {
            e.preventDefault();
            moveDrag(e);
            }
        }, { passive: false });

        // Fonction unifiée pour arrêter le drag
        const stopDrag = (e) => {
            if (!isDragging) return;
            
            // Éviter les conflits entre souris et tactile
            if (e && e.type === 'mouseup' && e.touches) return;
            if (e && e.type === 'touchend' && !e.touches) return;
            
            isDragging = false;
            element.style.zIndex = '1000';
            element.style.opacity = '1';
            element.style.transform = 'scale(1)';
            element.style.boxShadow = '0 4px 8px rgba(0, 123, 255, 0.3)';
            element.style.border = '2px solid #007bff';
            element.style.transition = 'all 0.2s ease';
            
            this.showStatus(`${type === 'signature' ? 'Signature' : 'Paraphe'} repositionné`, 'info');
            
            // Nettoyer le timeout de debounce
            if (this.updateFormDataTimeout) {
                clearTimeout(this.updateFormDataTimeout);
                this.updateFormDataTimeout = null;
            }
            
            // Mise à jour finale des coordonnées après le drag
            this.updateFormData();
        };

        document.addEventListener('touchend', (e) => {
            // Seulement bloquer le scrolling si on est en train de faire du drag
            if (isDragging) {
            e.preventDefault();
            }
            stopDrag(e);
        }, { passive: false });

        // Événements de souris (utilisant les fonctions unifiées)
        element.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', moveDrag);
        document.addEventListener('mouseup', stopDrag);

        // Empêcher la sélection de texte pendant le glissement
        element.addEventListener('selectstart', (e) => {
            e.preventDefault();
        });
        
        // Le mode signature est maintenant désactivé automatiquement après placement
        // Plus besoin de gestionnaire pour clic hors de l'élément
        console.log('🎯 Glissement activé - Mode signature déjà désactivé');
    }


    /**
     * Générer le PDF final côté client
     */
    async generateFinalPdf() {
        try {
            
            // Charger pdf-lib depuis CDN si pas déjà chargé
            if (typeof window.PDFLib === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js';
                document.head.appendChild(script);
                
                await new Promise((resolve, reject) => {
                    script.onload = resolve;
                    script.onerror = reject;
                });
            }

            const { PDFDocument } = window.PDFLib;
            
            // Charger le PDF original
            const existingPdfBytes = await fetch(this.config.pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFDocument.load(existingPdfBytes);
            
            // Obtenir toutes les pages
            const pages = pdfDoc.getPages();
            
            // Ajouter les signatures sur leurs pages respectives
            console.log('🔍 Signatures à traiter:', this.signatures);
            console.log('🔍 Nombre de signatures:', this.signatures.length);
            console.log('🔍 Nombre de pages PDF:', pages.length);
            
            if (this.signatures.length > 0) {
                for (const signature of this.signatures) {
                    console.log('🔍 Traitement signature:', {
                        id: signature.id,
                        url: signature.url,
                        page: signature.page,
                        x: signature.x,
                        y: signature.y,
                        totalPages: pages.length
                    });
                    
                    if (signature.url && signature.page <= pages.length) {
                        try {
                            console.log('📥 Chargement de l\'image de signature...');
                            console.log('🔗 URL de signature:', signature.url);
                            
                            // Vérifier que l'URL est accessible
                            const response = await fetch(signature.url);
                            if (!response.ok) {
                                throw new Error(`Erreur HTTP: ${response.status} - ${response.statusText}`);
                            }
                            
                            // Charger l'image de signature
                            const signatureImageBytes = await response.arrayBuffer();
                            console.log('📊 Taille de l\'image:', signatureImageBytes.byteLength, 'bytes');
                            
                            const signatureImage = await pdfDoc.embedPng(signatureImageBytes);
                            console.log('✅ Image de signature chargée avec succès');
                            
                            // Obtenir la page correspondante (index 0-based)
                            const targetPage = pages[signature.page - 1];
                            
                            // Obtenir les dimensions de la page PDF
                            const pageSize = targetPage.getSize();
                            const pdfPageWidth = pageSize.width;
                            const pdfPageHeight = pageSize.height;
                            
                            // Utiliser les coordonnées PDF stockées si disponibles (mode responsive précis)
                            // Sinon, convertir les coordonnées HTML (mode normal)
                            let pdfX, pdfY;
                            
        if (signature.pdfX !== undefined && signature.pdfY !== undefined) {
            // Mode responsive : utiliser les coordonnées PDF pré-calculées avec ajustements
            pdfX = signature.pdfX - 25; // Ajustement de 25 points vers la gauche (augmenté pour mobile)
            pdfY = signature.pdfY - 15; // Ajustement de 15 points vers le bas (inchangé)
            console.log('📍 DEBUG Mode responsive - coordonnées PDF pré-calculées avec ajustements:', {
                originalPdfX: signature.pdfX,
                originalPdfY: signature.pdfY,
                adjustedPdfX: pdfX,
                adjustedPdfY: pdfY,
                adjustmentX: signature.pdfX - pdfX,
                adjustmentY: signature.pdfY - pdfY,
                finalPdfX: Math.round(pdfX),
                finalPdfY: Math.round(pdfY)
            });
        } else {
                                // Mode normal : conversion pure sans ajustements
                                pdfX = this.convertHtmlToPdfX(signature.x);
                                pdfY = this.convertHtmlToPdfY(signature.y, 'signature');
                                console.log('📍 Mode normal - conversion pure sans ajustements:', { 
                                    htmlX: signature.x, htmlY: signature.y, 
                                    pdfX, pdfY 
                                });
                            }
                            
                            // Calculer les dimensions proportionnelles basées sur la page réelle (réduites)
                            const signatureWidth = Math.min(80, pdfPageWidth * 0.12); // Max 12% de la largeur de page
                            const signatureHeight = signatureWidth * 0.4; // Ratio 2.5:1 pour une signature plus réaliste
                            
                            console.log('📝 Ajout de la signature au PDF (approche module signature):', {
                                originalX: signature.x,
                                originalY: signature.y,
                                pdfX: pdfX,
                                pdfY: pdfY,
                                width: signatureWidth,
                                height: signatureHeight,
                                pageSize: { width: pdfPageWidth, height: pdfPageHeight }
                            });
                            
                            console.log('🎨 Ajout de la signature à la page:', {
                                pageIndex: signature.page - 1,
                                pdfX: pdfX,
                                pdfY: pdfY,
                                width: signatureWidth,
                                height: signatureHeight
                            });
                            
                            targetPage.drawImage(signatureImage, {
                                x: pdfX,
                                y: pdfY,
                                width: signatureWidth,
                                height: signatureHeight,
                                opacity: 0.8
                            });
                            
                            console.log('✅ Signature ajoutée avec succès à la page', signature.page);
                        } catch (error) {
                            console.error('❌ Erreur signature:', error);
                        }
                    } else {
                        console.warn('⚠️ Signature ignorée:', {
                            hasUrl: !!signature.url,
                            pageValid: signature.page <= pages.length,
                            signature: signature
                        });
                    }
                }
            } else {
                console.warn('⚠️ Aucune signature à traiter');
            }
            
            // Ajouter les paraphes sur leurs pages respectives
            if (this.paraphes.length > 0) {
                for (const paraphe of this.paraphes) {
                    if (paraphe.url && paraphe.page <= pages.length) {
                        try {
                            // Charger l'image de paraphe
                            const parapheImageBytes = await fetch(paraphe.url).then(res => res.arrayBuffer());
                            const parapheImage = await pdfDoc.embedPng(parapheImageBytes);
                            
                            // Obtenir la page correspondante (index 0-based)
                            const targetPage = pages[paraphe.page - 1];
                            
                            // Obtenir les dimensions de la page PDF
                            const pageSize = targetPage.getSize();
                            const pdfPageWidth = pageSize.width;
                            const pdfPageHeight = pageSize.height;
                            
                            // Utiliser les coordonnées PDF stockées si disponibles (mode responsive précis)
                            // Sinon, convertir les coordonnées HTML (mode normal)
                            let pdfX, pdfY;
                            
                            if (paraphe.pdfX !== undefined && paraphe.pdfY !== undefined) {
                                // Mode responsive : utiliser les coordonnées PDF pré-calculées avec ajustements
                                pdfX = paraphe.pdfX - 10; // Ajustement de 10 points vers la gauche (même que signature)
                                pdfY = paraphe.pdfY - 10; // Ajustement de 10 points vers le bas (même que signature)
                                console.log('📍 Mode responsive - paraphe coordonnées PDF pré-calculées avec ajustements:', { 
                                    originalPdfX: paraphe.pdfX, 
                                    originalPdfY: paraphe.pdfY,
                                    adjustedPdfX: pdfX,
                                    adjustedPdfY: pdfY
                                });
                            } else {
                                // Mode normal : conversion pure sans ajustements
                                pdfX = this.convertHtmlToPdfX(paraphe.x);
                                pdfY = this.convertHtmlToPdfY(paraphe.y, 'paraphe');
                                console.log('📍 Mode normal - paraphe conversion pure sans ajustements:', { 
                                    htmlX: paraphe.x, htmlY: paraphe.y, 
                                    pdfX, pdfY 
                                });
                            }
                            
                            // Calculer les dimensions proportionnelles basées sur la page réelle (même que signature)
                            const parapheWidth = Math.min(80, pdfPageWidth * 0.12); // Max 12% de la largeur de page (même que signature)
                            const parapheHeight = parapheWidth * 0.4; // Ratio 2.5:1 pour un paraphe plus réaliste
                            
                            console.log('📝 Ajout du paraphe au PDF (approche module signature):', {
                                originalX: paraphe.x,
                                originalY: paraphe.y,
                                pdfX: pdfX,
                                pdfY: pdfY,
                                width: parapheWidth,
                                height: parapheHeight,
                                pageSize: { width: pdfPageWidth, height: pdfPageHeight }
                            });
                            
                            targetPage.drawImage(parapheImage, {
                                x: pdfX,
                                y: pdfY,
                                width: parapheWidth,
                                height: parapheHeight,
                                opacity: 0.8
                            });
                        } catch (error) {
                            console.error('❌ Erreur paraphe:', error);
                        }
                    }
                }
            }
            
            // Ajouter les cachets sur leurs pages respectives
            console.log('🔍 Cachets à traiter:', this.cachets);
            console.log('🔍 Nombre de cachets:', this.cachets.length);
            
            if (this.cachets.length > 0) {
                for (const cachet of this.cachets) {
                    console.log('🔍 Traitement cachet:', {
                        id: cachet.id,
                        url: cachet.url,
                        page: cachet.page,
                        x: cachet.x,
                        y: cachet.y,
                        totalPages: pages.length
                    });
                    
                    if (cachet.url && cachet.page <= pages.length) {
                        try {
                            console.log('📥 Chargement de l\'image de cachet...');
                            console.log('🔗 URL de cachet:', cachet.url);
                            
                            // Vérifier que l'URL est accessible
                            const response = await fetch(cachet.url);
                            if (!response.ok) {
                                throw new Error(`Erreur HTTP: ${response.status} - ${response.statusText}`);
                            }
                            
                            // Charger l'image de cachet
                            const cachetImageBytes = await response.arrayBuffer();
                            console.log('📊 Taille de l\'image cachet:', cachetImageBytes.byteLength, 'bytes');
                            
                            const cachetImage = await pdfDoc.embedPng(cachetImageBytes);
                            console.log('✅ Image de cachet chargée avec succès');
                            
                            // Obtenir la page correspondante (index 0-based)
                            const targetPage = pages[cachet.page - 1];
                            
                            // Obtenir les dimensions de la page PDF
                            const pageSize = targetPage.getSize();
                            const pdfPageWidth = pageSize.width;
                            const pdfPageHeight = pageSize.height;
                            
                            // Utiliser les coordonnées PDF stockées si disponibles (mode responsive précis)
                            // Sinon, convertir les coordonnées HTML (mode normal)
                            let pdfX, pdfY;
                            
                            if (cachet.pdfX !== undefined && cachet.pdfY !== undefined) {
                                // Mode responsive : utiliser les coordonnées PDF pré-calculées avec ajustements
                                pdfX = cachet.pdfX - 25; // Ajustement de 25 points vers la gauche
                                pdfY = cachet.pdfY - 25; // Ajustement de 25 points vers le bas
                                console.log('📍 DEBUG Mode responsive - cachet coordonnées PDF pré-calculées avec ajustements:', { 
                                    originalPdfX: cachet.pdfX, 
                                    originalPdfY: cachet.pdfY,
                                    adjustedPdfX: pdfX,
                                    adjustedPdfY: pdfY,
                                    adjustmentX: cachet.pdfX - pdfX,
                                    adjustmentY: cachet.pdfY - pdfY,
                                    finalPdfX: Math.round(pdfX),
                                    finalPdfY: Math.round(pdfY)
                                });
                            } else {
                                // Mode normal : conversion pure sans ajustements
                                pdfX = this.convertHtmlToPdfX(cachet.x);
                                pdfY = this.convertHtmlToPdfY(cachet.y, 'cachet');
                                console.log('📍 Mode normal - cachet conversion pure sans ajustements:', { 
                                    htmlX: cachet.x, htmlY: cachet.y, 
                                    pdfX, pdfY 
                                });
                            }
                            
                            // Calculer les dimensions proportionnelles basées sur la page réelle (réduites)
                            const cachetWidth = Math.min(80, pdfPageWidth * 0.12); // Max 12% de la largeur de page
                            const cachetHeight = cachetWidth * 0.8; // Ratio 1.25:1 pour un cachet plus carré
                            
                            console.log('📝 Ajout du cachet au PDF (approche module signature):', {
                                originalX: cachet.x,
                                originalY: cachet.y,
                                pdfX: pdfX,
                                pdfY: pdfY,
                                width: cachetWidth,
                                height: cachetHeight,
                                pageSize: { width: pdfPageWidth, height: pdfPageHeight }
                            });
                            
                            console.log('🎨 Ajout du cachet à la page:', {
                                pageIndex: cachet.page - 1,
                                pdfX: pdfX,
                                pdfY: pdfY,
                                width: cachetWidth,
                                height: cachetHeight
                            });
                            
                            targetPage.drawImage(cachetImage, {
                                x: pdfX,
                                y: pdfY,
                                width: cachetWidth,
                                height: cachetHeight,
                                opacity: 0.8
                            });
                            
                            console.log('✅ Cachet ajouté avec succès à la page', cachet.page);
                        } catch (error) {
                            console.error('❌ Erreur cachet:', error);
                        }
                    } else {
                        console.warn('⚠️ Cachet ignoré:', {
                            hasUrl: !!cachet.url,
                            pageValid: cachet.page <= pages.length,
                            cachet: cachet
                        });
                    }
                }
            } else {
                console.warn('⚠️ Aucun cachet à traiter');
            }
            
            // Générer le PDF final
            console.log('📄 Génération du PDF final...');
            const pdfBytes = await pdfDoc.save();
            console.log('✅ PDF généré avec succès, taille:', pdfBytes.byteLength, 'bytes');
            
            // Envoyer le PDF au serveur pour stockage (sans téléchargement automatique)
            await this.uploadPdfToServer(pdfBytes, `document_signe_${Date.now()}.pdf`);
            
        } catch (error) {
            throw error;
        }
    }

    /**
     * Envoyer le PDF généré au serveur pour stockage
     */
    async uploadPdfToServer(pdfBytes, filename) {
        try {
            // Vérifier que documentId est disponible
            if (!this.config.documentId) {
                throw new Error('ID du document non disponible');
            }

            // Créer un FormData pour envoyer le fichier
            const formData = new FormData();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            formData.append('signed_pdf', blob, filename);
            formData.append('document_id', this.config.documentId);
            
            // Récupérer le token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }

            // Envoyer au serveur - utiliser l'URL de configuration ou l'URL par défaut
            const uploadUrl = this.config.uploadUrl || '/documents/upload-signed-pdf';
            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Erreur serveur ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            console.log('📡 Réponse du serveur:', result);
            
            if (result.success) {
                this.showStatus('PDF signé stocké avec succès !', 'success');
                
                // Utiliser la redirection de la réponse ou la configuration
                const redirectUrl = result.redirect || this.config.redirectUrl || `/documents/${this.config.documentId}/process/view`;
                
                console.log('🔄 Redirection vers:', redirectUrl);
                console.log('📋 Configuration redirectUrl:', this.config.redirectUrl);
                console.log('📋 Réponse redirect:', result.redirect);
                
                // Rediriger vers la page appropriée après un court délai
                setTimeout(() => {
                    console.log('🚀 Exécution de la redirection vers:', redirectUrl);
                    window.location.href = redirectUrl;
                }, 1500);
            } else {
                this.showStatus('Erreur lors du stockage: ' + result.message, 'error');
            }
        } catch (error) {
            this.showStatus('Erreur lors de l\'envoi au serveur: ' + error.message, 'error');
        }
    }
}

// Ajouter les styles CSS
const style = document.createElement('style');
style.textContent = `
    .signature-element, .paraphe-element {
        position: absolute;
        border: 2px solid #007bff;
        background: rgba(0, 123, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        color: #007bff;
        cursor: move;
        user-select: none;
        z-index: 1000;
        min-width: 80px;
        min-height: 40px;
    }
    
    .paraphe-element {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        min-width: 40px;
        min-height: 20px;
    }
    
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 300px;
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .toast.success .toast-content {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .toast.error .toast-content {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .toast.warning .toast-content {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .toast.info .toast-content {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
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