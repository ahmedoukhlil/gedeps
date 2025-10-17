Je veux intégrer Fabric.js dans mon module PDF pour résoudre définitivement mon problème de conversion de coordonnées HTML vers PDF.

CONTEXTE :
- J'ai un fichier `public/js/pdf-overlay-unified-module.js` qui gère les signatures/paraphes/cachets sur PDF
- J'utilise actuellement PDF.js pour l'affichage et pdf-lib pour la génération
- Problème : Les coordonnées HTML ne se convertissent pas correctement vers les coordonnées PDF
- Les signatures "sautent" vers des positions incorrectes dans le PDF final

OBJECTIF :
Intégrer Fabric.js pour gérer automatiquement :
1. Le positionnement précis des éléments (signatures, paraphes, cachets)
2. Le glisser-déposer fluide
3. La conversion automatique des coordonnées HTML → PDF
4. Le support mobile/tablette avec événements tactiles

CONTRAINTES :
- Garder la structure actuelle de ma classe `PDFOverlayUnifiedModule`
- Conserver les méthodes existantes : `addSignature()`, `addParaphe()`, `addCachet()`
- Maintenir la compatibilité avec mon backend Laravel
- Le canvas Fabric.js doit se superposer au canvas PDF.js

FICHIER À MODIFIER :
`public/js/pdf-overlay-unified-module.js`

ACTIONS REQUISES :

1. **Installer Fabric.js**
   - Ajouter la dépendance dans package.json
   - Ou utiliser le CDN : https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js

2. **Initialiser Fabric.js dans la méthode `init()`**
   - Créer un canvas Fabric.js superposé au conteneur PDF
   - Synchroniser les dimensions avec le canvas PDF.js
   - Gérer le redimensionnement responsive

3. **Modifier la méthode `enableClickPositioning(type)`**
   - Utiliser Fabric.js pour capturer les clics
   - Créer les objets Fabric.js (Image) au lieu des div HTML

4. **Modifier `createSignatureAtPosition(x, y)`**
   - Utiliser `fabric.Image.fromURL()` pour charger la signature
   - Rendre l'objet draggable avec Fabric.js
   - Gérer les événements `object:modified` pour mettre à jour les coordonnées

5. **Modifier `generateFinalPdf()`**
   - Récupérer les objets Fabric.js avec `canvas.getObjects()`
   - Utiliser les coordonnées Fabric.js qui sont déjà précises
   - Convertir vers PDF avec la formule :
```javascript
     pdfX = obj.left * (pdfPageWidth / fabricCanvas.width)
     pdfY = pdfPageHeight - (obj.top * (pdfPageHeight / fabricCanvas.height))
```

6. **Améliorer le support mobile**
   - Utiliser les événements Fabric.js qui gèrent tactile automatiquement
   - Configurer `selection: true` et `hasControls: true`

7. **Ajouter des logs de debug**
   - Logger les coordonnées Fabric.js vs coordonnées PDF
   - Afficher un message si les coordonnées sortent des limites

EXEMPLE DE STRUCTURE ATTENDUE :
```javascript
class PDFOverlayUnifiedModule {
    constructor(config) {
        // ... code existant ...
        this.fabricCanvas = null; // NOUVEAU
    }

    async init() {
        // ... code existant ...
        await this.initializeFabricCanvas(); // NOUVEAU
    }

    async initializeFabricCanvas() {
        // NOUVEAU : Créer le canvas Fabric.js
        const container = document.getElementById(this.config.containerId);
        
        // Créer un canvas overlay
        const canvasElement = document.createElement('canvas');
        canvasElement.id = 'fabric-overlay';
        canvasElement.style.position = 'absolute';
        canvasElement.style.top = '0';
        canvasElement.style.left = '0';
        canvasElement.style.zIndex = '1000';
        container.style.position = 'relative';
        container.appendChild(canvasElement);

        // Initialiser Fabric.js
        this.fabricCanvas = new fabric.Canvas('fabric-overlay', {
            selection: false,
            backgroundColor: 'transparent'
        });

        // Synchroniser les dimensions
        this.syncCanvasDimensions();
    }

    syncCanvasDimensions() {
        // NOUVEAU : Synchroniser avec le canvas PDF.js
        const pdfCanvas = document.querySelector('canvas[data-page-number]');
        if (pdfCanvas) {
            const rect = pdfCanvas.getBoundingClientRect();
            this.fabricCanvas.setWidth(rect.width);
            this.fabricCanvas.setHeight(rect.height);
        }
    }

    createSignatureAtPosition(x, y) {
        // MODIFIÉ : Utiliser Fabric.js
        fabric.Image.fromURL(this.userSignatureUrl, img => {
            img.set({
                left: x,
                top: y,
                selectable: true,
                hasControls: true,
                hasBorders: true,
                cornerSize: 10,
                transparentCorners: false,
                scaleX: 0.3,
                scaleY: 0.3
            });

            // Ajouter au canvas Fabric
            this.fabricCanvas.add(img);
            this.fabricCanvas.renderAll();

            // Stocker dans le tableau signatures
            this.signatures.push({
                id: Date.now(),
                fabricObject: img, // NOUVEAU : Référence à l'objet Fabric
                page: this.currentPage
            });

            // Gérer les modifications
            img.on('modified', () => {
                this.updateFormData();
            });
        });
    }

    async generateFinalPdf() {
        // MODIFIÉ : Utiliser les objets Fabric.js
        const fabricObjects = this.fabricCanvas.getObjects();
        
        for (const obj of fabricObjects) {
            // Les coordonnées Fabric.js sont PRÉCISES
            const pdfX = obj.left * (pdfPageWidth / this.fabricCanvas.width);
            const pdfY = pdfPageHeight - (obj.top * (pdfPageHeight / this.fabricCanvas.height));

            // Plus de problème de conversion ! ✅
            console.log('Coordonnées Fabric → PDF:', {
                fabricX: obj.left,
                fabricY: obj.top,
                pdfX: Math.round(pdfX),
                pdfY: Math.round(pdfY)
            });

            // Dessiner sur le PDF
            targetPage.drawImage(signatureImage, {
                x: pdfX,
                y: pdfY - imageHeight,
                width: imageWidth,
                height: imageHeight
            });
        }
    }
}
```

POINTS D'ATTENTION :
- Préserver tous les logs de debug existants
- Ne pas casser les méthodes existantes pour les paraphes et cachets
- Gérer le changement de page (vider/restaurer le canvas Fabric)
- Tester sur mobile ET desktop

RÉSULTAT ATTENDU :
Un code fonctionnel où :
- Les signatures/paraphes/cachets se positionnent avec Fabric.js
- La conversion HTML → PDF est toujours correcte
- Le glisser-déposer est fluide
- Les coordonnées finales dans le PDF sont précises à ±2 pixels

Peux-tu modifier mon fichier `public/js/pdf-overlay-unified-module.js` en suivant ces instructions ?