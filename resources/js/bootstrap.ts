import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// Helper function to update CSRF token
window.updateCsrfToken = (token: string) => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        metaTag.setAttribute('content', token);
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
};

// Handle 419 CSRF token mismatch errors
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            // CSRF token mismatch - reload the page to get a fresh token
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
