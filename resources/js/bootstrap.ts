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
            // Check if we're on the login page
            const currentPath = window.location.pathname;
            const isLoginPage = currentPath.includes('/login') || currentPath === '/';
            
            if (isLoginPage) {
                // On login page, save form data and reload silently
                const form = document.querySelector('form');
                if (form) {
                    const emailInput = form.querySelector('input[type="email"]') as HTMLInputElement;
                    const rememberInput = form.querySelector('input[type="checkbox"]') as HTMLInputElement;
                    if (emailInput && emailInput.value) {
                        sessionStorage.setItem('login_email', emailInput.value);
                    }
                    if (rememberInput) {
                        sessionStorage.setItem('login_remember', String(rememberInput.checked));
                    }
                }
                // Silently reload - no error window will show
                window.location.reload();
                return Promise.reject(error); // Still reject to prevent further processing
            } else {
                // On other pages, reload to get fresh token
                window.location.reload();
            }
        }
        return Promise.reject(error);
    }
);
