/**
 * CORRECTIONS DE TAILLES D'ICÔNES
 * Script pour ajuster automatiquement les tailles d'icônes
 */

(function() {
    'use strict';

    // Configuration des tailles d'icônes
    const ICON_SIZES = {
        xs: '0.75rem',    // 12px
        sm: '0.875rem',   // 14px
        md: '1rem',       // 16px
        lg: '1.125rem',   // 18px
        xl: '1.25rem',    // 20px
        '2xl': '1.5rem',  // 24px
        '3xl': '1.875rem' // 30px
    };

    // Tailles par contexte
    const CONTEXT_SIZES = {
        button: '1rem',
        badge: '0.875rem',
        card: '1.125rem',
        cardHeader: '1.5rem',
        nav: '1rem',
        menu: '1.125rem',
        stat: '1.5rem',
        info: '1rem',
        status: '1.25rem'
    };

    /**
     * Détermine la taille appropriée pour une icône selon son contexte
     */
    function getIconSize(icon, context = 'default') {
        const parent = icon.parentElement;
        const parentClasses = parent ? parent.className : '';
        const iconClasses = icon.className;
        
        // Vérifier les classes de taille explicites
        if (iconClasses.includes('text-xs')) return ICON_SIZES.xs;
        if (iconClasses.includes('text-sm')) return ICON_SIZES.sm;
        if (iconClasses.includes('text-lg')) return ICON_SIZES.lg;
        if (iconClasses.includes('text-xl')) return ICON_SIZES.xl;
        if (iconClasses.includes('text-2xl')) return ICON_SIZES['2xl'];
        if (iconClasses.includes('text-3xl')) return ICON_SIZES['3xl'];
        
        // Déterminer le contexte
        if (parentClasses.includes('btn') || parentClasses.includes('button')) {
            return CONTEXT_SIZES.button;
        }
        if (parentClasses.includes('badge') || parentClasses.includes('status')) {
            return CONTEXT_SIZES.badge;
        }
        if (parentClasses.includes('card') || parentClasses.includes('document-card')) {
            return CONTEXT_SIZES.card;
        }
        if (parentClasses.includes('nav') || parentClasses.includes('navbar')) {
            return CONTEXT_SIZES.nav;
        }
        if (parentClasses.includes('menu')) {
            return CONTEXT_SIZES.menu;
        }
        if (parentClasses.includes('stat')) {
            return CONTEXT_SIZES.stat;
        }
        if (parentClasses.includes('info')) {
            return CONTEXT_SIZES.info;
        }
        if (parentClasses.includes('status-section')) {
            return CONTEXT_SIZES.status;
        }
        
        // Vérifier les conteneurs avec dimensions fixes
        if (parentClasses.includes('w-8') && parentClasses.includes('h-8')) {
            return '1rem';
        }
        if (parentClasses.includes('w-10') && parentClasses.includes('h-10')) {
            return '1.25rem';
        }
        if (parentClasses.includes('w-12') && parentClasses.includes('h-12')) {
            return '1.5rem';
        }
        if (parentClasses.includes('w-14') && parentClasses.includes('h-14')) {
            return '1.75rem';
        }
        if (parentClasses.includes('w-16') && parentClasses.includes('h-16')) {
            return '2rem';
        }
        
        // Taille par défaut
        return ICON_SIZES.md;
    }

    /**
     * Applique la taille appropriée à une icône
     */
    function fixIconSize(icon) {
        const size = getIconSize(icon);
        
        icon.style.fontSize = size;
        icon.style.width = size;
        icon.style.height = size;
        icon.style.lineHeight = '1';
        icon.style.display = 'inline-flex';
        icon.style.alignItems = 'center';
        icon.style.justifyContent = 'center';
        icon.style.verticalAlign = 'middle';
    }

    /**
     * Corrige toutes les icônes de la page
     */
    function fixAllIcons() {
        const icons = document.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
        
        icons.forEach(icon => {
            fixIconSize(icon);
        });
        
        console.log(`🎯 ${icons.length} icônes ajustées`);
    }

    /**
     * Corrige les icônes dans un conteneur spécifique
     */
    function fixIconsInContainer(container) {
        const icons = container.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
        
        icons.forEach(icon => {
            fixIconSize(icon);
        });
    }

    /**
     * Corrige les icônes dans les cartes de documents
     */
    function fixDocumentCardIcons() {
        const cards = document.querySelectorAll('.document-card, .card, .sophisticated-card');
        
        cards.forEach(card => {
            // Icônes dans l'en-tête de la carte
            const headerIcons = card.querySelectorAll('.w-16 i, .w-14 i, .w-12 i');
            headerIcons.forEach(icon => {
                icon.style.fontSize = '2rem';
                icon.style.width = '2rem';
                icon.style.height = '2rem';
            });
            
            // Icônes dans les sections d'information
            const infoIcons = card.querySelectorAll('.info-section i, [class*="info"] i');
            infoIcons.forEach(icon => {
                icon.style.fontSize = '1rem';
                icon.style.width = '1rem';
                icon.style.height = '1rem';
            });
            
            // Icônes dans les sections de statut
            const statusIcons = card.querySelectorAll('.status-section i, [class*="status"] i');
            statusIcons.forEach(icon => {
                icon.style.fontSize = '1.25rem';
                icon.style.width = '1.25rem';
                icon.style.height = '1.25rem';
            });
        });
    }

    /**
     * Corrige les icônes dans les boutons
     */
    function fixButtonIcons() {
        const buttons = document.querySelectorAll('button, .btn, a[class*="btn"]');
        
        buttons.forEach(button => {
            const icons = button.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
            icons.forEach(icon => {
                icon.style.fontSize = '1rem';
                icon.style.width = '1rem';
                icon.style.height = '1rem';
            });
        });
    }

    /**
     * Corrige les icônes dans la navigation
     */
    function fixNavigationIcons() {
        const navItems = document.querySelectorAll('.nav, .navbar, .sophisticated-nav');
        
        navItems.forEach(nav => {
            const icons = nav.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
            icons.forEach(icon => {
                icon.style.fontSize = '1rem';
                icon.style.width = '1rem';
                icon.style.height = '1rem';
            });
        });
    }

    /**
     * Corrige les icônes dans les badges et statuts
     */
    function fixBadgeIcons() {
        const badges = document.querySelectorAll('.badge, .status, [class*="badge"]');
        
        badges.forEach(badge => {
            const icons = badge.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
            icons.forEach(icon => {
                icon.style.fontSize = '0.875rem';
                icon.style.width = '0.875rem';
                icon.style.height = '0.875rem';
            });
        });
    }

    /**
     * Corrige les icônes dans les statistiques
     */
    function fixStatIcons() {
        const stats = document.querySelectorAll('.stat, [class*="stat"]');
        
        stats.forEach(stat => {
            const icons = stat.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
            icons.forEach(icon => {
                icon.style.fontSize = '1.5rem';
                icon.style.width = '1.5rem';
                icon.style.height = '1.5rem';
            });
        });
    }

    /**
     * Corrige les icônes dans les conteneurs avec dimensions fixes
     */
    function fixFixedSizeContainerIcons() {
        const containers = document.querySelectorAll('[class*="w-8"][class*="h-8"], [class*="w-10"][class*="h-10"], [class*="w-12"][class*="h-12"], [class*="w-14"][class*="h-14"], [class*="w-16"][class*="h-16"]');
        
        containers.forEach(container => {
            const icons = container.querySelectorAll('i, .fas, .far, .fab, .fal, .fad, .fa');
            const classes = container.className;
            
            icons.forEach(icon => {
                if (classes.includes('w-8') && classes.includes('h-8')) {
                    icon.style.fontSize = '1rem';
                    icon.style.width = '1rem';
                    icon.style.height = '1rem';
                } else if (classes.includes('w-10') && classes.includes('h-10')) {
                    icon.style.fontSize = '1.25rem';
                    icon.style.width = '1.25rem';
                    icon.style.height = '1.25rem';
                } else if (classes.includes('w-12') && classes.includes('h-12')) {
                    icon.style.fontSize = '1.5rem';
                    icon.style.width = '1.5rem';
                    icon.style.height = '1.5rem';
                } else if (classes.includes('w-14') && classes.includes('h-14')) {
                    icon.style.fontSize = '1.75rem';
                    icon.style.width = '1.75rem';
                    icon.style.height = '1.75rem';
                } else if (classes.includes('w-16') && classes.includes('h-16')) {
                    icon.style.fontSize = '2rem';
                    icon.style.width = '2rem';
                    icon.style.height = '2rem';
                }
            });
        });
    }

    /**
     * Fonction principale d'initialisation
     */
    function initIconSizes() {
        console.log('🎯 Initialisation des corrections de tailles d\'icônes...');
        
        // Appliquer les corrections
        fixAllIcons();
        fixDocumentCardIcons();
        fixButtonIcons();
        fixNavigationIcons();
        fixBadgeIcons();
        fixStatIcons();
        fixFixedSizeContainerIcons();
        
        console.log('✅ Corrections de tailles d\'icônes appliquées avec succès');
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
                        fixAllIcons();
                        fixDocumentCardIcons();
                        fixButtonIcons();
                        fixNavigationIcons();
                        fixBadgeIcons();
                        fixStatIcons();
                        fixFixedSizeContainerIcons();
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
            initIconSizes();
            setupMutationObserver();
        });
    } else {
        initIconSizes();
        setupMutationObserver();
    }

    /**
     * Réapplication périodique pour les éléments dynamiques
     */
    setInterval(function() {
        fixAllIcons();
        fixDocumentCardIcons();
        fixButtonIcons();
    }, 3000);

    // Export pour utilisation externe
    window.IconSizes = {
        init: initIconSizes,
        fixAll: fixAllIcons,
        fixDocumentCards: fixDocumentCardIcons,
        fixButtons: fixButtonIcons,
        fixNavigation: fixNavigationIcons,
        fixBadges: fixBadgeIcons,
        fixStats: fixStatIcons,
        fixFixedContainers: fixFixedSizeContainerIcons
    };

})();
