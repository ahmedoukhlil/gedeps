/**
 * Système de notifications toast sophistiqué
 */
class ToastNotifications {
    constructor() {
        this.container = null;
        this.toasts = new Map();
        this.init();
    }

    init() {
        // Créer le conteneur s'il n'existe pas
        if (!document.querySelector('.toast-container')) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.toast-container');
        }
    }

    /**
     * Afficher une notification toast
     * @param {Object} options - Options de la notification
     * @param {string} options.type - Type de notification (success, error, warning, info)
     * @param {string} options.title - Titre de la notification
     * @param {string} options.message - Message de la notification
     * @param {number} options.duration - Durée d'affichage en ms (défaut: 5000)
     * @param {boolean} options.autoClose - Fermeture automatique (défaut: true)
     * @param {string} options.icon - Icône personnalisée
     */
    show(options) {
        const {
            type = 'info',
            title = 'Notification',
            message = '',
            duration = 5000,
            autoClose = true,
            icon = null
        } = options;

        const toastId = this.generateId();
        const toast = this.createToast(toastId, type, title, message, icon);
        
        this.container.appendChild(toast);
        this.toasts.set(toastId, toast);

        // Animation d'entrée
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Fermeture automatique
        if (autoClose) {
            setTimeout(() => {
                this.hide(toastId);
            }, duration);
        }

        return toastId;
    }

    /**
     * Créer l'élément toast
     */
    createToast(id, type, title, message, icon) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.dataset.toastId = id;

        const defaultIcons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const toastIcon = icon || defaultIcons[type] || 'ℹ';

        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon ${type}">${toastIcon}</div>
                <h4 class="toast-title">${title}</h4>
                <button class="toast-close" onclick="toastNotifications.hide('${id}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
            <div class="toast-progress">
                <div class="toast-progress-bar"></div>
            </div>
        `;

        return toast;
    }

    /**
     * Masquer une notification
     */
    hide(toastId) {
        const toast = this.toasts.get(toastId);
        if (!toast) return;

        toast.classList.remove('show');
        toast.classList.add('hide');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts.delete(toastId);
        }, 300);
    }

    /**
     * Masquer toutes les notifications
     */
    hideAll() {
        this.toasts.forEach((toast, id) => {
            this.hide(id);
        });
    }

    /**
     * Générer un ID unique
     */
    generateId() {
        return 'toast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Méthodes de commodité
     */
    success(title, message, options = {}) {
        return this.show({ type: 'success', title, message, ...options });
    }

    error(title, message, options = {}) {
        return this.show({ type: 'error', title, message, ...options });
    }

    warning(title, message, options = {}) {
        return this.show({ type: 'warning', title, message, ...options });
    }

    info(title, message, options = {}) {
        return this.show({ type: 'info', title, message, ...options });
    }
}

// Initialiser le système de notifications
const toastNotifications = new ToastNotifications();

// Exposer globalement
window.toastNotifications = toastNotifications;

// Méthodes de commodité globales
window.showToast = (type, title, message, options) => {
    return toastNotifications.show({ type, title, message, ...options });
};

window.showSuccess = (title, message, options) => {
    return toastNotifications.success(title, message, options);
};

window.showError = (title, message, options) => {
    return toastNotifications.error(title, message, options);
};

window.showWarning = (title, message, options) => {
    return toastNotifications.warning(title, message, options);
};

window.showInfo = (title, message, options) => {
    return toastNotifications.info(title, message, options);
};

// Gestion des notifications de session Laravel
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier s'il y a des messages de session
    const sessionMessages = document.querySelectorAll('[data-session-message]');
    sessionMessages.forEach(element => {
        const type = element.dataset.sessionType || 'info';
        const title = element.dataset.sessionTitle || 'Notification';
        const message = element.textContent;
        
        toastNotifications.show({ type, title, message });
        element.remove();
    });
});
