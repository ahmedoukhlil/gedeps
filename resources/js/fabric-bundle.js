// Import Fabric.js (import par défaut)
import * as fabric from 'fabric';

// Exposer Fabric.js globalement pour compatibilité
window.fabric = fabric;

// Debug
console.log('🎨 Fabric.js bundle chargé:', fabric.version);

// Export pour utilisation dans d'autres modules
export { fabric };
