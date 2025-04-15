<template>
    <AuthenticatedLayout title="Date Type Range Details">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Date Type Range Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Date Range Information</h3>
                            <div>
                                <Link
                                    :href="route('date-type-ranges.edit', dateTypeRange.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 mr-3"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('date-type-ranges.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Date Type</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ dateTypeRange.date_type.name }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ dateTypeRange.start_date }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">End Date</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ dateTypeRange.end_date }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ dateTypeRange.created_at }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ dateTypeRange.updated_at }}</p>
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

const props = defineProps({
    dateTypeRange: Object
});
</script>
