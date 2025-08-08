<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Settings
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- SST Configuration Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            SST (Sales and Service Tax) Configuration
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Configure Sales and Service Tax settings for your application.
                        </p>
                    </div>
                    
                    <div class="p-6">
                        <form @submit.prevent="updateSstConfiguration">
                            <!-- SST Status Toggle -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">
                                            Enable SST
                                        </label>
                                        <p class="text-sm text-gray-500">
                                            Enable or disable Sales and Service Tax calculation
                                        </p>
                                    </div>
                                    <div class="flex items-center">
                                        <button
                                            type="button"
                                            @click="sstForm.status = !sstForm.status"
                                            :class="[
                                                sstForm.status ? 'bg-blue-600' : 'bg-gray-200',
                                                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    sstForm.status ? 'translate-x-6' : 'translate-x-1',
                                                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform'
                                                ]"
                                            />
                                        </button>
                                        <span class="ml-3 text-sm font-medium text-gray-900">
                                            {{ sstForm.status ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- SST Percentage Input -->
                            <div class="mb-6" v-if="sstForm.status">
                                <label for="sst_percent" class="block text-sm font-medium text-gray-700">
                                    SST Percentage (%)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input
                                        type="number"
                                        id="sst_percent"
                                        v-model="sstForm.sst_percent"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="block w-full pr-12 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="6.00"
                                    />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Enter the SST percentage rate (e.g., 6.00 for 6%)
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ loading ? 'Saving...' : 'Save Configuration' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import { Head } from '@inertiajs/vue3';
import moment from 'moment';

// Props
const props = defineProps({
    sstConfiguration: {
        type: Object,
        default: () => ({
            status: 0,
            sst_percent: 6.0
        })
    }
});

// Reactive data
const loading = ref(false);
const sstForm = reactive({
    status: props.sstConfiguration.status === 1,
    sst_percent: props.sstConfiguration.sst_percent || 6.0
});

// Methods
const updateSstConfiguration = async () => {
    loading.value = true;
    
    try {
        const response = await fetch('/settings/sst-configuration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: sstForm.status ? 1 : 0,
                sst_percent: parseFloat(sstForm.sst_percent)
            })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Failed to update SST configuration',
        });
    } finally {
        loading.value = false;
    }
};

// Initialize form on mount
onMounted(() => {
    sstForm.status = props.sstConfiguration.status === 1;
    sstForm.sst_percent = props.sstConfiguration.sst_percent || 6.0;
});
</script>
