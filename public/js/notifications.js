/**
 * Notification System (Toaster-Ui)
 * Provides consistent, readable toasts for organizational use
 */

const toaster = new ToasterUi();

/**
 * Normalize a message (string or error object) into plain text
 */
function formatMessage(message) {
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

    return String(displayMessage);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show a toast
 * @param {string|object} message - Toast message or error object
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Auto-close duration in ms
 */
function showToast(message, type = 'info', duration = 5000) {
    const safeHtml = escapeHtml(formatMessage(message)).replace(/\n/g, '<br>');

    return toaster.addToast(safeHtml, type, {
        allowHtml: true,
        duration: duration,
    });
}

/**
 * Notification system (backward-compatible interface)
 */
const notifications = {
    show(message, type = 'info', duration = 5000) {
        return showToast(message, type, duration);
    },

    success(message, duration = 5000) {
        return showToast(message, 'success', duration);
    },

    error(message, duration = 7000) {
        return showToast(message, 'error', duration);
    },

    warning(message, duration = 6000) {
        return showToast(message, 'warning', duration);
    },

    info(message, duration = 5000) {
        return showToast(message, 'info', duration);
    },
};

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
