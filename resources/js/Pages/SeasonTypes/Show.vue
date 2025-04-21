<template>
    <Head title="Season Type Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Season Type Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Season Type Information</h3>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('season-types.edit', seasonType.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Edit Season Type
                                </Link>
                                <Link
                                    :href="route('season-types.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ seasonType.name }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Associated Seasons</h4>
                                <div v-if="seasonType.seasons.length > 0" class="mt-4">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="season in seasonType.seasons" :key="season.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ season.start_date }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ season.end_date }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ season.priority }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="mt-1 text-sm text-gray-500">No seasons associated with this type.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ seasonType.created_at }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ seasonType.updated_at }}</p>
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
defineProps({
    seasonType: Object
});
</script>
