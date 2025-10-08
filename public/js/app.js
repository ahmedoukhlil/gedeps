// App JS - Version statique
console.log('GEDEPS App loaded');

// Fonctionnalités de base
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants
    initializeComponents();
    
    // Gestion des formulaires
    initializeForms();
    
    // Gestion des notifications
    initializeNotifications();
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

// Export pour utilisation globale
window.GEDEPS = {
    showNotification,
    validateForm
};
