<template>
    <Head title="Configuration Price Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Configuration Price Details
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Configuration</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ price.configuration.room_type }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Type</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ price.type }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Number of Adults</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ price.number_of_adults }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Number of Children</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ price.number_of_children }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Adult Price</h3>
                                <p class="mt-1 text-sm text-gray-600">${{ price.adult_price.toFixed(2) }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Child Price</h3>
                                <p class="mt-1 text-sm text-gray-600">${{ price.child_price.toFixed(2) }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Created At</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ new Date(price.created_at).toLocaleString() }}</p>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Updated At</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ new Date(price.updated_at).toLocaleString() }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <Link
                                :href="route('configuration-prices.index')"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                            >
                                Back to List
                            </Link>
                            <Link
                                :href="route('configuration-prices.edit', price.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Edit Price
                            </Link>
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
const props = defineProps({
    price: Object
});
</script>
