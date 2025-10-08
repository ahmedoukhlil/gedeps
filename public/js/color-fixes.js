/**
 * CORRECTIONS DE COULEURS DYNAMIQUES
 * Script pour corriger automatiquement les problèmes de couleurs
 */

(function() {
    'use strict';

    // Configuration des couleurs
    const COLOR_CONFIG = {
        primary: '#2563eb',
        primaryDark: '#1d4ed8',
        success: '#059669',
        successDark: '#047857',
        warning: '#d97706',
        warningDark: '#b45309',
        error: '#dc2626',
        errorDark: '#b91c1c',
        info: '#0891b2',
        infoDark: '#0e7490',
        gray: '#4b5563',
        grayDark: '#374151',
        white: '#ffffff',
        black: '#111827'
    };

    /**
     * Corrige les couleurs des boutons
     */
    function fixButtonColors() {
        const buttons = document.querySelectorAll('button, .btn, a[class*="btn"]');
        
        buttons.forEach(button => {
            const classes = button.className;
            
            // Correction des boutons bleus
            if (classes.includes('bg-blue-600') || classes.includes('bg-blue-500') || classes.includes('btn-primary')) {
                button.style.backgroundColor = COLOR_CONFIG.primary;
                button.style.color = COLOR_CONFIG.white;
                button.style.borderColor = COLOR_CONFIG.primary;
            }
            
            // Correction des boutons verts
            if (classes.includes('bg-green-600') || classes.includes('bg-emerald-600') || classes.includes('btn-success')) {
                button.style.backgroundColor = COLOR_CONFIG.success;
                button.style.color = COLOR_CONFIG.white;
                button.style.borderColor = COLOR_CONFIG.success;
            }
            
            // Correction des boutons orange/jaune
            if (classes.includes('bg-yellow-600') || classes.includes('bg-orange-600') || classes.includes('btn-warning')) {
                button.style.backgroundColor = COLOR_CONFIG.warning;
                button.style.color = COLOR_CONFIG.white;
                button.style.borderColor = COLOR_CONFIG.warning;
            }
            
            // Correction des boutons rouges
            if (classes.includes('bg-red-600') || classes.includes('btn-danger')) {
                button.style.backgroundColor = COLOR_CONFIG.error;
                button.style.color = COLOR_CONFIG.white;
                button.style.borderColor = COLOR_CONFIG.error;
            }
            
            // Correction des boutons gris
            if (classes.includes('bg-gray-600') || classes.includes('bg-gray-500') || classes.includes('btn-secondary')) {
                button.style.backgroundColor = COLOR_CONFIG.gray;
                button.style.color = COLOR_CONFIG.white;
                button.style.borderColor = COLOR_CONFIG.gray;
            }
        });
    }

    /**
     * Corrige les couleurs des icônes
     */
    function fixIconColors() {
        const icons = document.querySelectorAll('i, .fas, .far, .fab, .fal, .fad');
        
        icons.forEach(icon => {
            const parent = icon.parentElement;
            const parentClasses = parent ? parent.className : '';
            
            // Si l'icône est dans un bouton coloré, elle doit être blanche
            if (parentClasses.includes('bg-blue-600') || 
                parentClasses.includes('bg-green-600') || 
                parentClasses.includes('bg-emerald-600') ||
                parentClasses.includes('bg-red-600') ||
                parentClasses.includes('bg-yellow-600') ||
                parentClasses.includes('bg-orange-600') ||
                parentClasses.includes('bg-gray-600')) {
                icon.style.color = COLOR_CONFIG.white;
            }
            
            // Si l'icône est dans un badge coloré, elle doit hériter de la couleur du parent
            if (parentClasses.includes('bg-blue-100') || 
                parentClasses.includes('bg-green-100') || 
                parentClasses.includes('bg-emerald-100') ||
                parentClasses.includes('bg-red-100') ||
                parentClasses.includes('bg-yellow-100') ||
                parentClasses.includes('bg-orange-100')) {
                icon.style.color = 'inherit';
            }
        });
    }

    /**
     * Corrige les couleurs des badges et statuts
     */
    function fixBadgeColors() {
        const badges = document.querySelectorAll('.badge, .status, [class*="bg-"][class*="-100"]');
        
        badges.forEach(badge => {
            const classes = badge.className;
            
            // Correction des badges verts
            if (classes.includes('bg-green-100') || classes.includes('bg-emerald-100')) {
                badge.style.backgroundColor = '#d1fae5';
                badge.style.color = COLOR_CONFIG.successDark;
                badge.style.borderColor = '#a7f3d0';
            }
            
            // Correction des badges bleus
            if (classes.includes('bg-blue-100')) {
                badge.style.backgroundColor = '#dbeafe';
                badge.style.color = COLOR_CONFIG.primaryDark;
                badge.style.borderColor = '#bfdbfe';
            }
            
            // Correction des badges rouges
            if (classes.includes('bg-red-100')) {
                badge.style.backgroundColor = '#fee2e2';
                badge.style.color = COLOR_CONFIG.errorDark;
                badge.style.borderColor = '#fecaca';
            }
            
            // Correction des badges orange/jaune
            if (classes.includes('bg-yellow-100') || classes.includes('bg-orange-100')) {
                badge.style.backgroundColor = '#fef3c7';
                badge.style.color = COLOR_CONFIG.warningDark;
                badge.style.borderColor = '#fde68a';
            }
        });
    }

    /**
     * Corrige les couleurs des cartes
     */
    function fixCardColors() {
        const cards = document.querySelectorAll('.card, .document-card, .sophisticated-card');
        
        cards.forEach(card => {
            card.style.borderColor = '#e5e7eb';
            card.style.backgroundColor = COLOR_CONFIG.white;
            
            // Amélioration du hover
            card.addEventListener('mouseenter', function() {
                this.style.borderColor = '#93c5fd';
                this.style.boxShadow = '0 8px 25px rgba(37, 99, 235, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.borderColor = '#e5e7eb';
                this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
            });
        });
    }

    /**
     * Corrige les couleurs des éléments de navigation
     */
    function fixNavigationColors() {
        const navLinks = document.querySelectorAll('.nav-link, .sophisticated-nav-link');
        
        navLinks.forEach(link => {
            link.style.color = COLOR_CONFIG.white;
            
            link.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                this.style.color = COLOR_CONFIG.white;
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
                this.style.color = COLOR_CONFIG.white;
            });
        });
    }

    /**
     * Corrige les couleurs des formulaires
     */
    function fixFormColors() {
        const inputs = document.querySelectorAll('input, select, textarea, .form-control');
        
        inputs.forEach(input => {
            input.style.borderColor = '#e5e7eb';
            input.style.backgroundColor = COLOR_CONFIG.white;
            input.style.color = COLOR_CONFIG.black;
            
            input.addEventListener('focus', function() {
                this.style.borderColor = COLOR_CONFIG.primary;
                this.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.1)';
            });
            
            input.addEventListener('blur', function() {
                this.style.borderColor = '#e5e7eb';
                this.style.boxShadow = 'none';
            });
        });
    }

    /**
     * Corrige les couleurs des tables
     */
    function fixTableColors() {
        const tableHeaders = document.querySelectorAll('table thead th');
        
        tableHeaders.forEach(header => {
            header.style.backgroundColor = COLOR_CONFIG.primary;
            header.style.color = COLOR_CONFIG.white;
            header.style.borderColor = COLOR_CONFIG.primaryDark;
        });
        
        const tableRows = document.querySelectorAll('table tbody tr');
        
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#eff6ff';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'transparent';
            });
        });
    }

    /**
     * Corrige les couleurs des alertes
     */
    function fixAlertColors() {
        const alerts = document.querySelectorAll('.alert, .notification');
        
        alerts.forEach(alert => {
            const classes = alert.className;
            
            if (classes.includes('alert-success')) {
                alert.style.backgroundColor = '#ecfdf5';
                alert.style.color = COLOR_CONFIG.successDark;
                alert.style.borderColor = '#a7f3d0';
            } else if (classes.includes('alert-warning')) {
                alert.style.backgroundColor = '#fffbeb';
                alert.style.color = COLOR_CONFIG.warningDark;
                alert.style.borderColor = '#fde68a';
            } else if (classes.includes('alert-danger')) {
                alert.style.backgroundColor = '#fef2f2';
                alert.style.color = COLOR_CONFIG.errorDark;
                alert.style.borderColor = '#fecaca';
            } else if (classes.includes('alert-info')) {
                alert.style.backgroundColor = '#f0fdfa';
                alert.style.color = COLOR_CONFIG.infoDark;
                alert.style.borderColor = '#a7f3d0';
            }
        });
    }

    /**
     * Supprime les dégradés problématiques
     */
    function removeProblematicGradients() {
        const gradientElements = document.querySelectorAll('[class*="bg-gradient"]');
        
        gradientElements.forEach(element => {
            const classes = element.className;
            
            if (classes.includes('bg-gradient-to-r') || 
                classes.includes('bg-gradient-to-br') ||
                classes.includes('bg-gradient-to-l') ||
                classes.includes('bg-gradient-to-t') ||
                classes.includes('bg-gradient-to-b')) {
                
                // Remplacer par une couleur solide appropriée
                if (classes.includes('from-blue-500') || classes.includes('to-blue-600')) {
                    element.style.background = COLOR_CONFIG.primary;
                } else if (classes.includes('from-green-500') || classes.includes('to-green-600')) {
                    element.style.background = COLOR_CONFIG.success;
                } else if (classes.includes('from-red-500') || classes.includes('to-red-600')) {
                    element.style.background = COLOR_CONFIG.error;
                } else if (classes.includes('from-yellow-500') || classes.includes('to-yellow-600')) {
                    element.style.background = COLOR_CONFIG.warning;
                } else {
                    element.style.background = COLOR_CONFIG.primary;
                }
            }
        });
    }

    /**
     * Améliore la visibilité des éléments critiques
     */
    function improveCriticalElementsVisibility() {
        const criticalElements = document.querySelectorAll('.critical-element, .important-element');
        
        criticalElements.forEach(element => {
            element.style.color = COLOR_CONFIG.black;
            element.style.backgroundColor = COLOR_CONFIG.white;
            element.style.border = '2px solid #d1d5db';
            element.style.opacity = '1';
            element.style.visibility = 'visible';
        });
    }

    /**
     * Fonction principale d'initialisation
     */
    function initColorFixes() {
        console.log('🎨 Initialisation des corrections de couleurs...');
        
        // Appliquer les corrections
        fixButtonColors();
        fixIconColors();
        fixBadgeColors();
        fixCardColors();
        fixNavigationColors();
        fixFormColors();
        fixTableColors();
        fixAlertColors();
        removeProblematicGradients();
        improveCriticalElementsVisibility();
        
        console.log('✅ Corrections de couleurs appliquées avec succès');
    }

    /**
     * Observer pour les nouveaux éléments ajoutés dynamiquement
     */
    function setupMutationObserver() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Réappliquer les corrections aux nouveaux éléments
                    setTimeout(() => {
                        fixButtonColors();
                        fixIconColors();
                        fixBadgeColors();
                        fixCardColors();
                        fixNavigationColors();
                        fixFormColors();
                        fixTableColors();
                        fixAlertColors();
                        removeProblematicGradients();
                    }, 100);
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Initialisation au chargement de la page
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initColorFixes();
            setupMutationObserver();
        });
    } else {
        initColorFixes();
        setupMutationObserver();
    }

    /**
     * Réapplication périodique pour les éléments dynamiques
     */
    setInterval(function() {
        fixButtonColors();
        fixIconColors();
        fixBadgeColors();
    }, 2000);

    // Export pour utilisation externe
    window.ColorFixes = {
        init: initColorFixes,
        fixButtons: fixButtonColors,
        fixIcons: fixIconColors,
        fixBadges: fixBadgeColors,
        fixCards: fixCardColors,
        fixNavigation: fixNavigationColors,
        fixForms: fixFormColors,
        fixTables: fixTableColors,
        fixAlerts: fixAlertColors,
        removeGradients: removeProblematicGradients
    };

})();
