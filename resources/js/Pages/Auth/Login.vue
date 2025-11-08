<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    canResetPassword?: boolean;
    status?: string;
    csrfToken?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const isRefreshingToken = ref(false);
const hasTokenError = ref(false);
const showTokenRefreshMessage = ref(false);

// Refresh CSRF token by silently reloading the page to get a fresh token
const refreshCsrfToken = async (): Promise<void> => {
    if (isRefreshingToken.value) return;
    
    return new Promise((resolve) => {
        isRefreshingToken.value = true;
        showTokenRefreshMessage.value = true;
        
        // Silently reload the page to get a fresh CSRF token
        // We'll preserve form data in sessionStorage
        sessionStorage.setItem('login_email', form.email);
        sessionStorage.setItem('login_remember', String(form.remember));
        
        // Reload the page
        window.location.reload();
    });
};

// Refresh token when page becomes visible again (after being away for a while)
let lastTokenRefresh = Date.now();
const TOKEN_REFRESH_INTERVAL = 20 * 60 * 1000; // 20 minutes

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        const timeSinceLastRefresh = Date.now() - lastTokenRefresh;
        // Only refresh if it's been more than 20 minutes
        if (timeSinceLastRefresh > TOKEN_REFRESH_INTERVAL) {
            refreshCsrfToken().then(() => {
                lastTokenRefresh = Date.now();
            });
        }
    }
};

// Refresh token when user focuses on the window (if it's been a while)
const handleWindowFocus = () => {
    const timeSinceLastRefresh = Date.now() - lastTokenRefresh;
    if (timeSinceLastRefresh > TOKEN_REFRESH_INTERVAL) {
        refreshCsrfToken().then(() => {
            lastTokenRefresh = Date.now();
        });
    }
};

// Refresh token when user starts interacting with form (debounced)
let formFocusTimeout: ReturnType<typeof setTimeout> | null = null;
const handleFormFocus = () => {
    // Debounce to avoid multiple refreshes
    if (formFocusTimeout) {
        clearTimeout(formFocusTimeout);
    }
    
    formFocusTimeout = setTimeout(() => {
        const timeSinceLastRefresh = Date.now() - lastTokenRefresh;
        if (timeSinceLastRefresh > 10 * 60 * 1000) { // 10 minutes
            refreshCsrfToken().then(() => {
                lastTokenRefresh = Date.now();
            });
        }
    }, 500);
};

const submit = () => {
    performLogin();
};

let loginRetryCount = 0;
const MAX_RETRIES = 1;

const performLogin = () => {
    form.post(route('login'), {
        onError: (errors) => {
            // Form validation errors are handled by InputError components
            // CSRF errors (419) are handled by the router.on('error') handler in app.ts
        },
        onCancelToken: (cancelToken) => {
            // Store cancel token in case we need to cancel the request
        },
        onFinish: () => {
            // Clear any saved form data
            sessionStorage.removeItem('login_email');
            sessionStorage.removeItem('login_remember');
            loginRetryCount = 0;
            form.reset('password');
            // ✅ Force full page reload to refresh session + CSRF token
            window.location.href = route('dashboard');
        },
    });
};

// Set up event listeners
onMounted(() => {
    // Restore form data from sessionStorage if available (after token refresh)
    const savedEmail = sessionStorage.getItem('login_email');
    const savedRemember = sessionStorage.getItem('login_remember');
    if (savedEmail) {
        form.email = savedEmail;
        sessionStorage.removeItem('login_email');
    }
    if (savedRemember === 'true') {
        form.remember = true;
        sessionStorage.removeItem('login_remember');
    }
    
    // Update CSRF token from props if available
    if (props.csrfToken && window.updateCsrfToken) {
        window.updateCsrfToken(props.csrfToken);
        lastTokenRefresh = Date.now();
    }
    
    // Refresh token when page becomes visible
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Refresh token when window gains focus
    window.addEventListener('focus', handleWindowFocus);
    
    // Refresh token when user focuses on form fields
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    if (emailInput) {
        emailInput.addEventListener('focus', handleFormFocus);
    }
    if (passwordInput) {
        passwordInput.addEventListener('focus', handleFormFocus);
    }
    
    // Also refresh token periodically (every 20 minutes) to prevent expiration
    const tokenRefreshInterval = setInterval(() => {
        refreshCsrfToken().then(() => {
            lastTokenRefresh = Date.now();
        });
    }, 20 * 60 * 1000); // 20 minutes
    
    // Store interval ID for cleanup
    (window as any).__loginTokenRefreshInterval = tokenRefreshInterval;
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('focus', handleWindowFocus);
    
    // Clear interval
    if ((window as any).__loginTokenRefreshInterval) {
        clearInterval((window as any).__loginTokenRefreshInterval);
    }
});
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div v-if="showTokenRefreshMessage" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-700">
            Session expired. Refreshing page...
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block flex justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400"
                        >Remember me</span
                    >
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800 hover:underline"
                >
                    Forgot your password?
                </Link>
            </div>

            <div class="mt-4">
                <PrimaryButton
                    class="bg-indigo-500 hover:bg-indigo-600 w-full flex justify-center active:bg-indigo-700 focus:bg-indigo-700"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
