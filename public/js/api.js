/**
 * API Utility - Handles all AJAX requests
 * This file contains reusable functions for making API calls
 */

// Base URL for API endpoints
const API_BASE_URL = window.location.origin;

// Helper to detect if we're in MVC mode (routes) or API mode
// In MVC mode, we use routes like /items, /recipes, etc.
// In API mode, we use the same routes but they return JSON
const IS_MVC_MODE = !window.location.pathname.includes('.php') && !window.location.pathname.includes('/api/');

/**
 * Generic function to make API requests
 * @param {string} url - API endpoint
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE, etc.)
 * @param {object} data - Request body data (optional)
 * @param {object} options - Additional options (headers, etc.)
 * @returns {Promise} - Promise that resolves with response data
 */
async function apiRequest(url, method = 'GET', data = null, options = {}) {
    try {
        // Prepare request configuration
        const config = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        };

        // Add request body for POST, PUT, PATCH requests
        if (data && ['POST', 'PUT', 'PATCH'].includes(method)) {
            config.body = JSON.stringify(data);
        }

        // Add AJAX header to identify AJAX requests
        config.headers['X-Requested-With'] = 'XMLHttpRequest';
        
        // Make the fetch request
        const response = await fetch(`${API_BASE_URL}${url}`, config);

        // Parse JSON response
        const result = await response.json();

        // Check if request was successful
        if (!response.ok) {
            // Handle error responses
            throw new Error(result.message || 'Request failed');
        }

        return result;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

/**
 * GET request - Fetch data from server
 * @param {string} url - API endpoint
 * @param {object} params - Query parameters (optional)
 * @returns {Promise} - Promise with response data
 */
async function apiGet(url, params = {}) {
    // Build query string from params
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;
    
    return apiRequest(fullUrl, 'GET');
}

/**
 * POST request - Create new resource
 * @param {string} url - API endpoint
 * @param {object} data - Data to send
 * @returns {Promise} - Promise with response data
 */
async function apiPost(url, data) {
    return apiRequest(url, 'POST', data);
}

/**
 * PUT request - Update existing resource
 * @param {string} url - API endpoint
 * @param {object} data - Data to send
 * @returns {Promise} - Promise with response data
 */
async function apiPut(url, data) {
    return apiRequest(url, 'PUT', data);
}

/**
 * PATCH request - Partially update resource
 * @param {string} url - API endpoint
 * @param {object} data - Data to send
 * @returns {Promise} - Promise with response data
 */
async function apiPatch(url, data) {
    return apiRequest(url, 'PATCH', data);
}

/**
 * DELETE request - Delete resource
 * @param {string} url - API endpoint
 * @returns {Promise} - Promise with response data
 */
async function apiDelete(url) {
    return apiRequest(url, 'DELETE');
}

/**
 * File upload request - For multipart/form-data
 * @param {string} url - API endpoint
 * @param {FormData} formData - FormData object with files
 * @returns {Promise} - Promise with response data
 */
async function apiUpload(url, formData) {
    try {
        const response = await fetch(`${API_BASE_URL}${url}`, {
            method: 'POST',
            body: formData
            // Don't set Content-Type header - browser will set it with boundary
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Upload failed');
        }

        return result;
    } catch (error) {
        console.error('Upload Error:', error);
        throw error;
    }
}

/**
 * Show error message to user
 * @param {string} message - Error message
 */
function showError(message) {
    alert('Error: ' + message);
}

/**
 * Show success message to user
 * @param {string} message - Success message
 */
function showSuccess(message) {
    alert('Success: ' + message);
}

