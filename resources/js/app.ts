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
        // CSRF token mismatch - reload the page to get a fresh token
        window.location.reload();
    }
});
