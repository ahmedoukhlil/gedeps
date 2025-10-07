/**
 * Système de notifications pour l'accessibilité et le feedback utilisateur
 */
class NotificationSystem {
    constructor() {
        this.container = null;
        this.notifications = new Map();
        this.init();
    }

    init() {
        this.createContainer();
        this.setupEventListeners();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.className = 'notification-system';
        this.container.setAttribute('aria-live', 'polite');
        this.container.setAttribute('aria-label', 'Notifications système');
        document.body.appendChild(this.container);
    }

    setupEventListeners() {
        // Gestion des événements clavier pour les notifications
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAll();
            }
        });

        // Gestion des événements de focus pour l'accessibilité
        document.addEventListener('focusin', (e) => {
            if (e.target.closest('.notification')) {
                this.announceNotification(e.target);
            }
        });
    }

    show(message, type = 'info', options = {}) {
        const id = this.generateId();
        const notification = this.createNotification(id, message, type, options);
        
        this.container.appendChild(notification);
        this.notifications.set(id, notification);

        // Animation d'entrée
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });

        // Auto-close si spécifié
        if (options.autoClose !== false) {
            const duration = options.duration || 5000;
            setTimeout(() => {
                this.close(id);
            }, duration);
        }

        // Annonce pour les lecteurs d'écran
        this.announceToScreenReader(message, type);

        return id;
    }

    createNotification(id, message, type, options) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.setAttribute('role', type === 'error' ? 'alert' : 'status');
        notification.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
        notification.setAttribute('data-id', id);

        const icon = this.getIcon(type);
        const title = options.title || this.getDefaultTitle(type);

        notification.innerHTML = `
            <div class="notification-icon" aria-hidden="true">
                <i class="${icon}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close" aria-label="Fermer la notification">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        `;

        // Gestion du clic sur le bouton fermer
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.close(id);
        });

        // Gestion du clic sur la notification
        if (options.onClick) {
            notification.addEventListener('click', options.onClick);
            notification.style.cursor = 'pointer';
        }

        return notification;
    }

    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    getDefaultTitle(type) {
        const titles = {
            success: 'Succès',
            error: 'Erreur',
            warning: 'Attention',
            info: 'Information'
        };
        return titles[type] || titles.info;
    }

    close(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        notification.classList.add('hide');
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            this.notifications.delete(id);
        }, 300);
    }

    closeAll() {
        this.notifications.forEach((notification, id) => {
            this.close(id);
        });
    }

    announceToScreenReader(message, type) {
        const announcement = document.createElement('div');
        announcement.className = 'sr-only';
        announcement.setAttribute('aria-live', 'polite');
        announcement.textContent = `${this.getDefaultTitle(type)}: ${message}`;
        
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }

    announceNotification(element) {
        const title = element.querySelector('.notification-title')?.textContent;
        const message = element.querySelector('.notification-message')?.textContent;
        
        if (title && message) {
            this.announceToScreenReader(message, 'info');
        }
    }

    generateId() {
        return 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    }

    // Méthodes de convenance
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    info(message, options = {}) {
        return this.show(message, 'info', options);
    }
}

// Instance globale
window.NotificationSystem = new NotificationSystem();

// Export pour les modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationSystem;
}
