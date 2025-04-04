<template>
    <AuthenticatedLayout title="Package Add-on Details">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Package Add-on Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Add-on Information</h3>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('package-add-ons.edit', addOn.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Edit Add-on
                                </Link>
                                <Link
                                    :href="route('package-add-ons.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ addOn.name }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Package</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ addOn.package.name }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ addOn.description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Adult Price</h4>
                                    <p class="mt-1 text-sm text-gray-900">${{ addOn.adult_price }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Child Price</h4>
                                    <p class="mt-1 text-sm text-gray-900">${{ addOn.child_price }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ addOn.created_at }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ addOn.updated_at }}</p>
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

defineProps({
    addOn: Object
});
</script>
