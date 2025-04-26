<template>
    <Head title="Season Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('seasons.edit', season.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                >
                                    Edit Season
                                </Link>
                                <Link
                                    :href="route('seasons.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Back to List
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Season Type</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ season.type.name }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ moment(season.start_date).format('DD/MM/YYYY') }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">End Date</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ moment(season.end_date).format('DD/MM/YYYY') }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Priority</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ season.priority }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ moment(season.created_at).format('DD/MM/YYYY HH:mm:ss') }}</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                <p class="mt-1 text-sm text-gray-900">{{ moment(season.updated_at).format('DD/MM/YYYY HH:mm:ss') }}</p>
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
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';

defineProps({
    season: Object
});

const breadcrumbs = computed(() => [
    { title: 'Seasons', link: route('seasons.index') },
    { title: 'Season Details' }
]);
</script>
