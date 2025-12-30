/**
 * Professional Notification System
 * Provides consistent, readable notifications for organizational use
 */

class NotificationSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            this.container.className = 'notification-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('notification-container');
        }
    }

    /**
     * Show a notification
     * @param {string} message - Notification message
     * @param {string} type - Type: 'success', 'error', 'warning', 'info'
     * @param {number} duration - Auto-close duration in ms (0 = no auto-close)
     */
    show(message, type = 'info', duration = 5000) {
        if (!this.container) {
            this.init();
        }

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Icon based on type
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${icons[type] || icons.info}</span>
                <span class="notification-message">${this.escapeHtml(message)}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

        this.container.appendChild(notification);

        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }

        return notification;
    }

    /**
     * Remove notification
     */
    remove(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Success notification
     */
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    /**
     * Error notification
     */
    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    /**
     * Warning notification
     */
    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    /**
     * Info notification
     */
    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
}

// Initialize notification system
const notifications = new NotificationSystem();

// Global functions for backward compatibility
function showSuccess(message) {
    notifications.success(message);
}

function showError(message) {
    notifications.error(message);
}

function showWarning(message) {
    notifications.warning(message);
}

function showInfo(message) {
    notifications.info(message);
}

