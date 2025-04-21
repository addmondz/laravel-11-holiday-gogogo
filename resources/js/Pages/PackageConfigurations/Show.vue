<template>
    <Head title="Package Configuration Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Package Configuration Details
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Configuration Information
                                </h3>
                                <div class="flex space-x-3">
                                    <Link
                                        :href="route('package-configurations.edit', configuration.id)"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    >
                                        Edit Configuration
                                    </Link>
                                    <Link
                                        :href="route('package-configurations.index')"
                                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                    >
                                        Back to List
                                    </Link>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Package</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ configuration.package.name }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Season</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ configuration.season.name }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Date Type</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ configuration.date_type.name }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Room Type</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ configuration.room_type }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="configuration.prices && configuration.prices.length > 0">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Associated Prices</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Adults
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Children
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Adult Price
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Child Price
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="price in configuration.prices" :key="price.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ price.type }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ price.number_of_adults }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ price.number_of_children }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ price.adult_price }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ price.child_price }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No prices associated with this configuration yet.
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
    configuration: Object
});
</script>
