import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/fabric-bundle.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // Créer un bundle séparé pour Fabric.js
                manualChunks: {
                    fabric: ['fabric']
                }
            }
        }
    }
});
