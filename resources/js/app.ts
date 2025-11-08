import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { router } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // Update CSRF token from Inertia props on initial load
        if (props.initialPage.props.csrfToken && window.updateCsrfToken) {
            window.updateCsrfToken(props.initialPage.props.csrfToken);
        }

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});

// Update CSRF token on each successful page visit
router.on('navigate', (event) => {
    // Update token from the new page props
    if (event.detail?.page?.props?.csrfToken && window.updateCsrfToken) {
        window.updateCsrfToken(event.detail.page.props.csrfToken);
    }
});

// Handle Inertia request errors, especially 419 CSRF token mismatch
router.on('error', (event) => {
    if (event.detail?.response?.status === 419) {
        // Check if we're on the login page
        const currentPath = window.location.pathname;
        const isLoginPage = currentPath.includes('/login') || currentPath === '/';
        
        if (isLoginPage) {
            // Stop event propagation to prevent Inertia's default error modal
            if (event.stopPropagation) {
                event.stopPropagation();
            }
            if (event.preventDefault) {
                event.preventDefault();
            }
            
            // Save form data before reload (if not already saved by axios interceptor)
            const form = document.querySelector('form');
            if (form) {
                const emailInput = form.querySelector('input[type="email"]') as HTMLInputElement;
                const rememberInput = form.querySelector('input[type="checkbox"]') as HTMLInputElement;
                if (emailInput && emailInput.value && !sessionStorage.getItem('login_email')) {
                    sessionStorage.setItem('login_email', emailInput.value);
                }
                if (rememberInput && !sessionStorage.getItem('login_remember')) {
                    sessionStorage.setItem('login_remember', String(rememberInput.checked));
                }
            }
            
            // Silently reload to get fresh token
            // The Login component will restore form data on mount
            // Use setTimeout to ensure the error modal doesn't show
            setTimeout(() => {
                window.location.reload();
            }, 0);
        } else {
            // On other pages, reload to get fresh token
            window.location.reload();
        }
    }
});
