// App JS - Version statique améliorée
console.log('🚀 GEDEPS App loaded');

// Fonctionnalités de base
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants
    initializeComponents();
    
    // Gestion des formulaires
    initializeForms();
    
    // Gestion des notifications
    initializeNotifications();
    
    // Améliorations UX
    initializeUXEnhancements();
    
    // Animations
    initializeAnimations();
});

function initializeComponents() {
    console.log('Initializing components...');
    
    // Initialiser les tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', showTooltip);
        tooltip.addEventListener('mouseleave', hideTooltip);
    });
}

function initializeForms() {
    console.log('Initializing forms...');
    
    // Validation des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', validateForm);
    });
}

function initializeNotifications() {
    console.log('Initializing notifications...');
    
    // Auto-hide des alertes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

function showTooltip(event) {
    const tooltip = event.target.getAttribute('data-tooltip');
    if (tooltip) {
        // Créer et afficher le tooltip
        const tooltipElement = document.createElement('div');
        tooltipElement.className = 'tooltip';
        tooltipElement.textContent = tooltip;
        tooltipElement.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
        `;
        document.body.appendChild(tooltipElement);
        
        const rect = event.target.getBoundingClientRect();
        tooltipElement.style.left = rect.left + 'px';
        tooltipElement.style.top = (rect.top - 30) + 'px';
    }
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

function validateForm(event) {
    const form = event.target;
    const requiredFields = form.querySelectorAll('[required]');
    
    let isValid = true;
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        event.preventDefault();
        showNotification('Veuillez remplir tous les champs obligatoires', 'error');
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} fixed top-4 right-4 z-50`;
    notification.textContent = message;
    notification.style.cssText = `
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    `;
    
    if (type === 'error') {
        notification.style.backgroundColor = '#ef4444';
    } else if (type === 'success') {
        notification.style.backgroundColor = '#10b981';
    } else {
        notification.style.backgroundColor = '#3b82f6';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Améliorations UX
function initializeUXEnhancements() {
    console.log('🎨 Initializing UX enhancements...');
    
    // Améliorer les interactions
    enhanceInteractions();
    
    // Améliorer la navigation
    enhanceNavigation();
    
    // Améliorer les formulaires
    enhanceForms();
}

function initializeAnimations() {
    console.log('✨ Initializing animations...');
    
    // Animation d'entrée pour les éléments
    const elements = document.querySelectorAll('.card, .btn-primary, .btn-secondary');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

function enhanceInteractions() {
    // Améliorer les boutons
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Améliorer les cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        });
    });
}

function enhanceNavigation() {
    // Améliorer les liens de navigation
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Animation de clic
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
    
    // Gestion du menu mobile
    initializeMobileMenu();
}

function initializeMobileMenu() {
    // DÉSACTIVÉ - Le menu mobile est géré dans app.blade.php pour éviter les conflits
    console.log('⚠️ Menu mobile géré par app.blade.php');
    return;

    /* ANCIEN CODE DÉSACTIVÉ POUR ÉVITER LES CONFLITS
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('navbarNavMobile');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

    if (mobileMenuToggle && mobileMenu) {
        // Ouvrir le menu mobile
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('mobile-menu', 'active');
            document.body.style.overflow = 'hidden';
        });

        // Fermer le menu mobile
        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            setTimeout(() => {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('mobile-menu');
            }, 300);
            document.body.style.overflow = '';
        }

        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', closeMobileMenu);
        }

        // Fermer avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });

        // Fermer en cliquant sur un lien
        const mobileMenuLinks = mobileMenu.querySelectorAll('.mobile-menu-link');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(closeMobileMenu, 100);
            });
        });
    }
    */
}

function enhanceForms() {
    // Améliorer les inputs
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// Export pour utilisation globale
window.GEDEPS = {
    showNotification,
    validateForm,
    initializeUXEnhancements,
    initializeAnimations
};
