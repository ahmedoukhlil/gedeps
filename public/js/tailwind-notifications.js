/**
 * Système de notifications natif avec Tailwind CSS
 * Plus léger et intégré avec Tailwind
 */
class TailwindNotifications {
    constructor() {
        this.container = null;
        this.notifications = new Map();
        this.init();
    }

    init() {
        // Créer le conteneur s'il n'existe pas
        if (!document.querySelector('.notification-container')) {
            this.container = document.createElement('div');
            // Responsive: full width avec padding sur mobile, largeur fixe sur desktop
            this.container.className = 'notification-container fixed top-4 left-4 right-4 sm:left-auto sm:right-4 z-50 space-y-2 w-auto sm:max-w-sm';
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.notification-container');
        }
    }

    /**
     * Afficher une notification
     * @param {Object} options - Options de la notification
     * @param {string} options.type - Type de notification (success, error, warning, info)
     * @param {string} options.title - Titre de la notification
     * @param {string} options.message - Message de la notification
     * @param {number} options.duration - Durée d'affichage en ms (défaut: 5000)
     * @param {boolean} options.autoClose - Fermeture automatique (défaut: true)
     */
    show(options) {
        const {
            type = 'info',
            title = 'Notification',
            message = '',
            duration = 5000,
            autoClose = true
        } = options;

        const notificationId = this.generateId();
        const notification = this.createNotification(notificationId, type, title, message);

        this.container.appendChild(notification);
        this.notifications.set(notificationId, notification);

        // Animation d'entrée
        requestAnimationFrame(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
            notification.classList.add('translate-x-0', 'opacity-100');
        });

        // Fermeture automatique
        if (autoClose) {
            setTimeout(() => {
                this.hide(notificationId);
            }, duration);
        }

        return notificationId;
    }

    /**
     * Sanitiser une chaîne de caractères
     */
    sanitize(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Obtenir les classes de couleur selon le type
     */
    getTypeClasses(type) {
        const classes = {
            success: {
                bg: 'bg-green-50',
                border: 'border-green-200',
                icon: 'bg-green-500 text-white',
                title: 'text-green-800',
                message: 'text-green-700',
                close: 'text-green-500 hover:text-green-700',
                iconSymbol: '✓'
            },
            error: {
                bg: 'bg-red-50',
                border: 'border-red-200',
                icon: 'bg-red-500 text-white',
                title: 'text-red-800',
                message: 'text-red-700',
                close: 'text-red-500 hover:text-red-700',
                iconSymbol: '✕'
            },
            warning: {
                bg: 'bg-yellow-50',
                border: 'border-yellow-200',
                icon: 'bg-yellow-500 text-white',
                title: 'text-yellow-800',
                message: 'text-yellow-700',
                close: 'text-yellow-500 hover:text-yellow-700',
                iconSymbol: '⚠'
            },
            info: {
                bg: 'bg-blue-50',
                border: 'border-blue-200',
                icon: 'bg-blue-500 text-white',
                title: 'text-blue-800',
                message: 'text-blue-700',
                close: 'text-blue-500 hover:text-blue-700',
                iconSymbol: 'ℹ'
            }
        };
        return classes[type] || classes.info;
    }

    /**
     * Créer l'élément notification
     */
    createNotification(id, type, title, message) {
        const typeClasses = this.getTypeClasses(type);

        // Conteneur principal - responsive padding et gap
        const notification = document.createElement('div');
        notification.className = `transform transition-all duration-300 ease-out translate-x-full opacity-0 ${typeClasses.bg} ${typeClasses.border} border rounded-lg shadow-lg p-3 sm:p-4 flex items-start gap-2 sm:gap-3 w-full`;
        notification.dataset.notificationId = id;

        // Icône - responsive size
        const iconDiv = document.createElement('div');
        iconDiv.className = `flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 ${typeClasses.icon} rounded-full flex items-center justify-center font-bold text-xs sm:text-sm`;
        iconDiv.textContent = typeClasses.iconSymbol;

        // Contenu - responsive text
        const contentDiv = document.createElement('div');
        contentDiv.className = 'flex-1 min-w-0';

        const titleElement = document.createElement('h4');
        titleElement.className = `font-semibold text-xs sm:text-sm ${typeClasses.title} truncate`;
        titleElement.textContent = title;

        if (message) {
            const messageElement = document.createElement('p');
            messageElement.className = `text-xs sm:text-sm ${typeClasses.message} mt-0.5 sm:mt-1 line-clamp-2`;
            messageElement.textContent = message;
            contentDiv.appendChild(titleElement);
            contentDiv.appendChild(messageElement);
        } else {
            contentDiv.appendChild(titleElement);
        }

        // Bouton fermer - responsive touch target
        const closeButton = document.createElement('button');
        closeButton.className = `flex-shrink-0 ${typeClasses.close} hover:bg-black hover:bg-opacity-5 rounded-full p-1.5 sm:p-1 transition-colors duration-200 touch-manipulation`;
        closeButton.setAttribute('aria-label', 'Fermer');
        closeButton.innerHTML = '<svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
        closeButton.addEventListener('click', () => this.hide(id));

        notification.appendChild(iconDiv);
        notification.appendChild(contentDiv);
        notification.appendChild(closeButton);

        return notification;
    }

    /**
     * Masquer une notification
     */
    hide(notificationId) {
        const notification = this.notifications.get(notificationId);
        if (!notification) return;

        notification.classList.remove('translate-x-0', 'opacity-100');
        notification.classList.add('translate-x-full', 'opacity-0');

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            this.notifications.delete(notificationId);
        }, 300);
    }

    /**
     * Masquer toutes les notifications
     */
    hideAll() {
        this.notifications.forEach((_notification, id) => {
            this.hide(id);
        });
    }

    /**
     * Générer un ID unique
     */
    generateId() {
        return 'notif_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
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
const notifications = new TailwindNotifications();

// Exposer globalement
window.notifications = notifications;

// Méthodes de commodité globales (compatibilité avec l'ancien système)
window.showToast = (type, title, message, options) => {
    return notifications.show({ type, title, message, ...options });
};

window.showSuccess = (title, message, options) => {
    return notifications.success(title, message, options);
};

window.showError = (title, message, options) => {
    return notifications.error(title, message, options);
};

window.showWarning = (title, message, options) => {
    return notifications.warning(title, message, options);
};

window.showInfo = (title, message, options) => {
    return notifications.info(title, message, options);
};

// Gestion des notifications de session Laravel
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier s'il y a des messages de session
    const sessionMessages = document.querySelectorAll('[data-session-message]');
    sessionMessages.forEach(element => {
        const type = element.dataset.sessionType || 'info';
        const title = element.dataset.sessionTitle || 'Notification';
        const message = element.textContent;

        notifications.show({ type, title, message });
        element.remove();
    });
});
