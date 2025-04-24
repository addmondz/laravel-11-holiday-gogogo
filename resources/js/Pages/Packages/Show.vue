<template>
    <Head title="Package Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Package Details
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Package Information</h3>
                            <div class="flex space-x-3">
                                <Link :href="route('packages.edit', pkg.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Edit Package
                                </Link>
                                <Link :href="route('packages.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div class="flex items-start">
                                <!-- <div v-if="pkg.icon_photo" class="flex-shrink-0">
                                    <img :src="`/storage/${pkg.icon_photo}`" :alt="pkg.name" class="h-32 w-32 rounded-full">
                                </div> -->
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ pkg.name }}</p>
                                </div>

                                <div>
                                    <h4 class="mt-4 text-sm font-medium text-gray-500">Location</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ pkg.location }}</p>
                                </div>

                                <div>
                                    <h4 class="mt-4 text-sm font-medium text-gray-500">Status</h4>
                                    <p class="mt-1">
                                        <span :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            pkg.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ pkg.description }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Display Prices</h4>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-900">Adult: ${{ pkg.display_price_adult }}</p>
                                        <p class="text-sm text-gray-900">Child: ${{ pkg.display_price_child }}</p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Duration</h4>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-900">Minimum Days: {{ pkg.package_min_days }}</p>
                                        <p class="text-sm text-gray-900">Maximum Days: {{ pkg.package_max_days }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Terms and Conditions</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ pkg.terms_and_conditions }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.package_start_date).format('DD/MM/YYYY') }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">End Date</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.package_end_date).format('DD/MM/YYYY') || 'No end date' }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.created_at).format('DD/MM/YYYY HH:mm:ss') }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.updated_at).format('DD/MM/YYYY HH:mm:ss') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import moment from 'moment';

defineProps({
    pkg: Object
});
</script>
