/**
 * Professional Confirmation Dialog System
 * Provides consistent confirmation dialogs for organizational use
 */

class ConfirmDialog {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create dialog container if it doesn't exist
        if (!document.getElementById('confirm-dialog-container')) {
            this.container = document.createElement('div');
            this.container.id = 'confirm-dialog-container';
            this.container.className = 'confirm-dialog-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('confirm-dialog-container');
        }
    }

    /**
     * Show confirmation dialog
     * @param {string} message - Confirmation message
     * @param {string} title - Dialog title (optional)
     * @returns {Promise<boolean>} - Promise that resolves to true if confirmed, false if cancelled
     */
    show(message, title = 'Confirm Action') {
        return new Promise((resolve) => {
            if (!this.container) {
                this.init();
            }

            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'confirm-dialog-overlay';
            
            // Create dialog
            const dialog = document.createElement('div');
            dialog.className = 'confirm-dialog';
            
            dialog.innerHTML = `
                <div class="confirm-dialog-header">
                    <h3>${this.escapeHtml(title)}</h3>
                </div>
                <div class="confirm-dialog-body">
                    <p>${this.escapeHtml(message)}</p>
                </div>
                <div class="confirm-dialog-footer">
                    <button class="btn btn-secondary confirm-dialog-cancel">Cancel</button>
                    <button class="btn btn-danger confirm-dialog-confirm">Confirm</button>
                </div>
            `;

            overlay.appendChild(dialog);
            this.container.appendChild(overlay);

            // Trigger animation
            setTimeout(() => {
                overlay.classList.add('show');
            }, 10);

            // Handle confirm
            const confirmBtn = dialog.querySelector('.confirm-dialog-confirm');
            confirmBtn.onclick = () => {
                this.remove(overlay);
                resolve(true);
            };

            // Handle cancel
            const cancelBtn = dialog.querySelector('.confirm-dialog-cancel');
            cancelBtn.onclick = () => {
                this.remove(overlay);
                resolve(false);
            };

            // Handle overlay click
            overlay.onclick = (e) => {
                if (e.target === overlay) {
                    this.remove(overlay);
                    resolve(false);
                }
            };

            // Handle Escape key
            const escapeHandler = (e) => {
                if (e.key === 'Escape') {
                    this.remove(overlay);
                    resolve(false);
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        });
    }

    /**
     * Remove dialog
     */
    remove(overlay) {
        overlay.classList.remove('show');
        setTimeout(() => {
            if (overlay.parentElement) {
                overlay.remove();
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
}

// Initialize confirm dialog system
const confirmDialog = new ConfirmDialog();

// Global function for backward compatibility
async function confirmAction(message, title = 'Confirm Action') {
    return await confirmDialog.show(message, title);
}

