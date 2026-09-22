/**
 * Confirmation Dialog System (SweetAlert2)
 * Provides consistent confirmation dialogs for organizational use
 */

/**
 * Show confirmation dialog
 * @param {string} message - Confirmation message
 * @param {string} title - Dialog title (optional)
 * @returns {Promise<boolean>} - Promise that resolves to true if confirmed, false if cancelled
 */
async function confirmAction(message, title = 'Confirm Action') {
    if (typeof Swal === 'undefined') {
        return window.confirm(message);
    }

    const result = await Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        confirmButtonColor: '#DC3545',
        cancelButtonColor: '#6C757D',
        allowOutsideClick: true,
        allowEscapeKey: true,
    });

    return result.isConfirmed;
}
