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
        // Use existing container from layout or create one
        this.container = document.getElementById('notification-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            document.body.appendChild(this.container);
        }
    }

    /**
     * Show a notification
     * @param {string|object} message - Notification message or error object
     * @param {string} type - Type: 'success', 'error', 'warning', 'info'
     * @param {number} duration - Auto-close duration in ms (0 = no auto-close)
     */
    show(message, type = 'info', duration = 5000) {
        if (!this.container) {
            this.init();
        }

        // Handle error objects - extract message and details
        let displayMessage = message;
        if (typeof message === 'object' && message !== null) {
            if (message.message) {
                displayMessage = message.message;
                if (message.error || message.errors) {
                    const details = message.error || message.errors;
                    if (typeof details === 'object') {
                        const detailText = Object.entries(details)
                            .map(([key, value]) => `${key}: ${Array.isArray(value) ? value.join(', ') : value}`)
                            .join('\n');
                        displayMessage += '\n' + detailText;
                    } else {
                        displayMessage += '\n' + details;
                    }
                }
            } else {
                displayMessage = JSON.stringify(message);
            }
        }

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Icon based on type - using Material Symbols
        const icons = {
            success: '<span class="material-symbols-outlined" style="font-size: 1rem;">check_circle</span>',
            error: '<span class="material-symbols-outlined" style="font-size: 1rem;">error</span>',
            warning: '<span class="material-symbols-outlined" style="font-size: 1rem;">warning</span>',
            info: '<span class="material-symbols-outlined" style="font-size: 1rem;">info</span>'
        };

        // Escape and format message (preserve line breaks)
        const escapedMessage = this.escapeHtml(String(displayMessage)).replace(/\n/g, '<br>');

        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${icons[type] || icons.info}</span>
                <span class="notification-message">${escapedMessage}</span>
                <button class="notification-close" onclick="notifications.remove(this.closest('.notification'))" title="Close">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">close</span>
                </button>
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
        if (!notification) return;
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification && notification.parentElement) {
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

