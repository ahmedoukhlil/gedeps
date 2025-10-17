 Problèmes Critiques à Résoudre
1. Synchronisation Dimensions Canvas
Problème : Le canvas Fabric.js n'est pas correctement aligné avec le PDF
Fichier : pdf-overlay-unified.js (ligne ~250)

javascript
// CORRECTION REQUISE
syncCanvasDimensions() {
    const container = document.getElementById(this.config.containerId);
    const pdfCanvas = container.querySelector('canvas[data-page-number]');
    
    if (!pdfCanvas || !this.fabricCanvas) return;

    // Attendre le rendu complet
    setTimeout(() => {
        const pdfRect = pdfCanvas.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        
        if (pdfRect.width > 0 && pdfRect.height > 0) {
            // Dimensions exactes
            this.fabricCanvas.setDimensions({
                width: pdfRect.width,
                height: pdfRect.height
            });
            
            // Positionnement absolu précis
            const fabricElement = this.fabricCanvas.getElement();
            const exactTop = pdfRect.top - containerRect.top;
            const exactLeft = pdfRect.left - containerRect.left;
            
            fabricElement.style.top = `${exactTop}px`;
            fabricElement.style.left = `${exactLeft}px`;
            
            this.fabricCanvas.renderAll();
        }
    }, 150);
}
2. Gestion des Événements en Double
Problème : Multiples écouteurs d'événements qui entrent en conflit
Fichier : pdf-overlay-unified.js (ligne ~300)

javascript
// CORRECTION - UNIFIER LES ÉVÉNEMENTS
setupFabricEvents() {
    if (!this.fabricCanvas) return;

    // Nettoyer les anciens écouteurs
    this.fabricCanvas.off();

    // Événement principal pour les clics
    this.fabricCanvas.on('mouse:down', (e) => {
        console.log('🖱️ Clic canvas:', {
            target: e.target,
            isPositioningActive: this.isPositioningActive,
            currentElementType: this.currentElementType
        });

        if (e.target) {
            // Laisser Fabric.js gérer la sélection/déplacement
            return;
        }

        if (this.isPositioningActive && this.currentElementType) {
            const pointer = this.fabricCanvas.getPointer(e.e);
            this.handleCanvasClick(pointer.x, pointer.y);
        }
    });

    // Événement unique pour les modifications
    this.fabricCanvas.on('object:modified', (e) => {
        console.log('✅ Objet modifié:', e.target);
        this.syncFabricObjects();
        this.updateFormData();
    });

    // Événements de déplacement pour le feedback
    this.fabricCanvas.on('object:moving', (e) => {
        e.target.set({ opacity: 0.8 });
    });

    this.fabricCanvas.on('object:modified', (e) => {
        e.target.set({ opacity: 1 });
    });
}
3. Chargement Robust des Images
Problème : Les images échouent silencieusement
Fichier : pdf-overlay-unified.js (ligne ~450)

javascript
// CORRECTION - GESTION ROBUSTE DES IMAGES
createSignatureAtPosition(x, y) {
    return new Promise((resolve, reject) => {
        console.log('🖊️ Création signature promise:', { x, y });
        
        const signatureUrl = this.userSignatureUrl || this.config.signatureUrl;
        
        if (!signatureUrl) {
            reject(new Error('Aucune URL de signature configurée'));
            return;
        }

        // Timeout de sécurité
        const loadTimeout = setTimeout(() => {
            reject(new Error('Timeout chargement signature'));
        }, 5000);

        this.fabric.Image.fromURL(signatureUrl, (img) => {
            clearTimeout(loadTimeout);
            
            if (!img) {
                reject(new Error('Échec création image Fabric'));
                return;
            }

            console.log('✅ Image signature créée:', img);

            // Configuration robuste
            img.set({
                left: x,
                top: y,
                selectable: true,
                evented: true,
                hasControls: true,
                hasBorders: true,
                cornerSize: 12,
                cornerStyle: 'circle',
                borderColor: '#007bff',
                cornerColor: '#007bff',
                transparentCorners: false,
                borderScaleFactor: 2,
                scaleX: 0.3,
                scaleY: 0.3,
                lockMovementX: false,
                lockMovementY: false,
                lockRotation: false,
                lockScalingX: false,
                lockScalingY: false,
                padding: 10,
                objectCaching: true,
                // Métadonnées
                type: 'signature',
                id: `signature-${Date.now()}`,
                page: this.currentPage
            });

            this.fabricCanvas.add(img);
            this.fabricCanvas.setActiveObject(img);
            this.fabricCanvas.renderAll();

            this.syncFabricObjects();
            this.updateFormData();
            
            resolve(img);

        }, { 
            crossOrigin: 'anonymous' 
        });
    });
}
4. Initialisation Robust avec Retry
Problème : L'initialisation échoue si le PDF n'est pas prêt
Fichier : pdf-overlay-unified.js (ligne ~150)

javascript
// CORRECTION - INITIALISATION AVEC RETRY
async initializeWithRetry(maxRetries = 5, delay = 500) {
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            console.log(`🔄 Tentative d'initialisation ${attempt}/${maxRetries}`);
            
            await this.loadFabricJs();
            await this.loadPDF();
            
            // Attendre que le PDF soit rendu
            await this.waitForPDFRender();
            
            // Initialiser Fabric.js
            const fabricSuccess = await this.initializeFabricCanvas();
            if (!fabricSuccess) throw new Error('Échec initialisation Fabric');
            
            // Charger les éléments utilisateur
            await Promise.allSettled([
                this.loadUserSignature(),
                this.loadUserParaphe(),
                this.loadUserCachet()
            ]);
            
            this.setupEventListeners();
            this.updateInterface();
            
            console.log('✅ Initialisation réussie');
            this.showStatus('PDF chargé avec succès', 'success');
            return true;
            
        } catch (error) {
            console.error(`❌ Tentative ${attempt} échouée:`, error);
            
            if (attempt === maxRetries) {
                this.showStatus('Erreur critique de chargement', 'error');
                throw error;
            }
            
            // Attendre avant nouvelle tentative
            await new Promise(resolve => setTimeout(resolve, delay * attempt));
        }
    }
}

waitForPDFRender() {
    return new Promise((resolve, reject) => {
        const timeout = setTimeout(() => {
            reject(new Error('Timeout attente rendu PDF'));
        }, 10000);

        const checkPDF = () => {
            const container = document.getElementById(this.config.containerId);
            const pdfCanvas = container?.querySelector('canvas[data-page-number]');
            
            if (pdfCanvas && pdfCanvas.offsetWidth > 100 && pdfCanvas.offsetHeight > 100) {
                clearTimeout(timeout);
                console.log('✅ PDF rendu détecté:', {
                    width: pdfCanvas.offsetWidth,
                    height: pdfCanvas.offsetHeight
                });
                resolve();
            } else {
                setTimeout(checkPDF, 100);
            }
        };
        
        checkPDF();
    });
}
🔧 Correctifs de Performance
5. Gestion Mémoire et Cleanup
javascript
// CORRECTION - PRÉVENTION FUITES MÉMOIRE
cleanup() {
    console.log('🧹 Nettoyage des ressources');
    
    // Supprimer écouteurs Fabric.js
    if (this.fabricCanvas) {
        this.fabricCanvas.off();
        const objects = this.fabricCanvas.getObjects();
        objects.forEach(obj => {
            obj.off(); // Supprimer écouteurs d'objets
        });
    }
    
    // Supprimer écouteurs globaux
    window.removeEventListener('resize', this.handleResize);
    window.removeEventListener('orientationchange', this.handleResize);
    
    // Nettoyer les références
    this.signatures = [];
    this.paraphes = [];
    this.cachets = [];
}

destroy() {
    this.cleanup();
    if (this.fabricCanvas) {
        this.fabricCanvas.dispose();
    }
    console.log('✅ Module PDF Overlay détruit');
}
6. Optimisation Rendu et Interactions
javascript
// CORRECTION - OPTIMISATION RENDU
initializeFabricCanvas() {
    try {
        const container = document.getElementById(this.config.containerId);
        const pdfCanvas = container.querySelector('canvas[data-page-number]');
        
        if (!pdfCanvas) {
            throw new Error('Canvas PDF non trouvé');
        }

        // Créer canvas Fabric.js
        const fabricCanvasElement = document.createElement('canvas');
        fabricCanvasElement.id = 'fabric-overlay-canvas';
        
        // Configuration performance
        this.fabricCanvas = new this.fabric.Canvas(fabricCanvasElement, {
            width: pdfCanvas.offsetWidth,
            height: pdfCanvas.offsetHeight,
            selection: true,
            preserveObjectStacking: true,
            skipTargetFind: false,
            targetFindTolerance: 8, // Plus tolérant sur mobile
            allowTouchScrolling: false,
            enablePointerEvents: true,
            stateful: true,
            renderOnAddRemove: true, // Performance
            stopContextMenu: true
        });

        // Styles optimisés
        const pdfRect = pdfCanvas.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        const exactTop = pdfRect.top - containerRect.top;
        const exactLeft = pdfRect.left - containerRect.left;
        
        fabricCanvasElement.style.cssText = `
            position: absolute;
            top: ${exactTop}px;
            left: ${exactLeft}px;
            width: ${pdfRect.width}px;
            height: ${pdfRect.height}px;
            z-index: 10001;
            cursor: crosshair;
            pointer-events: auto;
            touch-action: none;
            display: block;
        `;

        // Ajouter au DOM
        container.style.position = 'relative';
        pdfCanvas.parentNode.insertBefore(fabricCanvasElement, pdfCanvas.nextSibling);

        // Configurer événements optimisés
        this.setupFabricEvents();
        
        return true;
        
    } catch (error) {
        console.error('❌ Erreur initialisation Fabric:', error);
        return false;
    }
}
🚀 Instructions pour Cursor.ai
Commandes à Exécuter :
Remplacer initializeFabricCanvas() avec la version optimisée

Implémenter initializeWithRetry() pour la robustesse

Ajouter setupFabricEvents() unifié

Corriger createSignatureAtPosition() avec Promises

Implémenter cleanup() et destroy()

Mettre à jour syncCanvasDimensions() avec timeout

Priorité des Correctifs :
✅ CRITIQUE - Synchronisation dimensions canvas

✅ HAUTE - Gestion robuste chargement images

✅ HAUTE - Initialisation avec retry mechanism

✅ MOYENNE - Optimisation performance mémoire

✅ MOYENNE - Unification gestion événements

Tests à Vérifier :
javascript
// Après correctifs, ces tests doivent fonctionner :
window.pdfOverlayModule.initializeWithRetry();
window.pdfOverlayModule.syncCanvasDimensions(); 
window.testSignature(); // Doit ajouter une signature
window.verifyDragDrop(); // Doit montrer objets déplaçables