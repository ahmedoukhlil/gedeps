/**
 * Améliorations Responsive pour les Signatures Séquentielles
 * Gestion dynamique de l'interface selon la taille d'écran
 */

document.addEventListener('DOMContentLoaded', function() {
    // Configuration responsive
    const RESPONSIVE_CONFIG = {
        breakpoints: {
            mobile: 640,
            tablet: 768,
            desktop: 1024
        },
        animations: {
            fadeInDuration: 300,
            slideInDuration: 200
        }
    };

    // Détection de la taille d'écran
    function getScreenSize() {
        const width = window.innerWidth;
        if (width < RESPONSIVE_CONFIG.breakpoints.mobile) return 'mobile';
        if (width < RESPONSIVE_CONFIG.breakpoints.tablet) return 'tablet';
        return 'desktop';
    }

    // Gestion des cartes de documents
    function initDocumentCards() {
        const cards = document.querySelectorAll('.document-card-responsive');
        
        cards.forEach(card => {
            // Animation d'apparition
            card.classList.add('fade-in-up');
            
            // Gestion du hover sur mobile
            if (getScreenSize() === 'mobile') {
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                card.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            }
        });
    }

    // Gestion des statistiques
    function initStatsCards() {
        const statsCards = document.querySelectorAll('.signature-stats');
        
        statsCards.forEach((card, index) => {
            // Animation d'apparition décalée
            setTimeout(() => {
                card.classList.add('fade-in-up');
            }, index * 100);
            
            // Gestion des interactions
            card.addEventListener('mouseenter', function() {
                if (getScreenSize() !== 'mobile') {
                    this.style.transform = 'translateY(-4px)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    // Gestion des boutons d'action
    function initActionButtons() {
        const actionButtons = document.querySelectorAll('.action-button');
        
        actionButtons.forEach(button => {
            // Gestion des états de chargement
            button.addEventListener('click', function(e) {
                if (this.classList.contains('loading')) {
                    e.preventDefault();
                    return;
                }
                
                // Ajouter un état de chargement
                this.classList.add('loading');
                this.style.opacity = '0.7';
                
                // Simuler le chargement (à adapter selon les besoins)
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.style.opacity = '';
                }, 1000);
            });
        });
    }

    // Gestion des grilles de signataires
    function initSignersGrid() {
        const signersGrids = document.querySelectorAll('.signers-grid');
        
        signersGrids.forEach(grid => {
            const signerItems = grid.querySelectorAll('.signer-item');
            
            signerItems.forEach((item, index) => {
                // Animation d'apparition décalée
                setTimeout(() => {
                    item.classList.add('fade-in-up');
                }, index * 50);
                
                // Gestion des interactions
                item.addEventListener('mouseenter', function() {
                    if (getScreenSize() !== 'mobile') {
                        this.style.transform = 'scale(1.02)';
                    }
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        });
    }

    // Gestion des sections d'en-tête
    function initSectionHeaders() {
        const sectionHeaders = document.querySelectorAll('.section-header');
        
        sectionHeaders.forEach(header => {
            // Animation d'apparition
            header.classList.add('fade-in-up');
            
            // Gestion responsive des badges
            const badges = header.querySelectorAll('.status-badge');
            badges.forEach(badge => {
                if (getScreenSize() === 'mobile') {
                    badge.style.fontSize = '0.625rem';
                    badge.style.padding = '0.125rem 0.5rem';
                }
            });
        });
    }

    // Gestion des barres de progression
    function initProgressBars() {
        const progressBars = document.querySelectorAll('.progress-fill');
        
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            
            // Animation de la barre de progression
            setTimeout(() => {
                bar.style.transition = 'width 0.8s ease-out';
                bar.style.width = width;
            }, 200);
        });
    }

    // Gestion du redimensionnement de la fenêtre
    function handleResize() {
        const currentSize = getScreenSize();
        
        // Mise à jour des classes responsive
        document.body.className = document.body.className.replace(/screen-\w+/g, '');
        document.body.classList.add(`screen-${currentSize}`);
        
        // Gestion des grilles
        const grids = document.querySelectorAll('.signers-grid');
        grids.forEach(grid => {
            if (currentSize === 'mobile') {
                grid.style.gridTemplateColumns = '1fr';
            } else if (currentSize === 'tablet') {
                grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else {
                grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
            }
        });
        
        // Gestion des statistiques
        const statsGrids = document.querySelectorAll('.stats-grid');
        statsGrids.forEach(grid => {
            if (currentSize === 'mobile') {
                grid.style.gridTemplateColumns = '1fr';
            } else if (currentSize === 'tablet') {
                grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else {
                grid.style.gridTemplateColumns = 'repeat(4, 1fr)';
            }
        });
    }

    // Gestion des tooltips sur desktop
    function initTooltips() {
        if (getScreenSize() === 'mobile') return;
        
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltiptext';
            tooltip.textContent = element.getAttribute('data-tooltip');
            element.classList.add('tooltip');
            element.appendChild(tooltip);
        });
    }

    // Gestion des animations d'apparition
    function initAnimations() {
        const animatedElements = document.querySelectorAll('.fade-in-up');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        animatedElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            observer.observe(element);
        });
    }

    // Gestion des messages d'état
    function initStatusMessages() {
        const statusMessages = document.querySelectorAll('.status-message');
        
        statusMessages.forEach(message => {
            // Animation d'apparition
            message.classList.add('fade-in-up');
            
            // Gestion des couleurs selon le statut
            if (message.classList.contains('to-sign')) {
                message.style.backgroundColor = '#fef2f2';
                message.style.borderColor = '#fecaca';
                message.style.color = '#dc2626';
            } else if (message.classList.contains('waiting')) {
                message.style.backgroundColor = '#eef2ff';
                message.style.borderColor = '#c7d2fe';
                message.style.color = '#3730a3';
            } else if (message.classList.contains('completed')) {
                message.style.backgroundColor = '#ecfdf5';
                message.style.borderColor = '#bbf7d0';
                message.style.color = '#166534';
            }
        });
    }

    // Gestion des espacements responsive
    function initSpacing() {
        const spacingElements = document.querySelectorAll('.spacing-responsive');
        
        spacingElements.forEach(element => {
            if (getScreenSize() === 'mobile') {
                element.style.marginBottom = '0.75rem';
            } else {
                element.style.marginBottom = '1.5rem';
            }
        });
    }

    // Gestion des textes responsive
    function initTextResponsive() {
        const textElements = document.querySelectorAll('.text-responsive');
        
        textElements.forEach(element => {
            if (getScreenSize() === 'mobile') {
                element.style.fontSize = '0.875rem';
                element.style.lineHeight = '1.25rem';
            } else {
                element.style.fontSize = '1rem';
                element.style.lineHeight = '1.5rem';
            }
        });
    }

    // Gestion des icônes responsive
    function initIconsResponsive() {
        const iconElements = document.querySelectorAll('.icon-responsive');
        
        iconElements.forEach(element => {
            if (getScreenSize() === 'mobile') {
                element.style.width = '1rem';
                element.style.height = '1rem';
            } else {
                element.style.width = '1.25rem';
                element.style.height = '1.25rem';
            }
        });
    }

    // Initialisation de toutes les fonctionnalités
    function initAll() {
        initDocumentCards();
        initStatsCards();
        initActionButtons();
        initSignersGrid();
        initSectionHeaders();
        initProgressBars();
        initTooltips();
        initAnimations();
        initStatusMessages();
        initSpacing();
        initTextResponsive();
        initIconsResponsive();
    }

    // Gestion du redimensionnement
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            handleResize();
            initSpacing();
            initTextResponsive();
            initIconsResponsive();
        }, 250);
    });

    // Initialisation
    initAll();
    handleResize();

    // Gestion des erreurs
    window.addEventListener('error', function(e) {
        console.error('Erreur dans signatures-responsive.js:', e.error);
    });

    // Gestion des performances
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            // Initialisation différée pour les éléments non critiques
            initAnimations();
        });
    } else {
        setTimeout(() => {
            initAnimations();
        }, 100);
    }
});

// Export pour utilisation externe
window.SignaturesResponsive = {
    getScreenSize: function() {
        const width = window.innerWidth;
        if (width < 640) return 'mobile';
        if (width < 768) return 'tablet';
        return 'desktop';
    },
    
    isMobile: function() {
        return this.getScreenSize() === 'mobile';
    },
    
    isTablet: function() {
        return this.getScreenSize() === 'tablet';
    },
    
    isDesktop: function() {
        return this.getScreenSize() === 'desktop';
    }
};
