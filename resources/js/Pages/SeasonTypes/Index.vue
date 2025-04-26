<template>
    <Head title="Season Types" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Season Types
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">All Season Types</h3>
                            <Link
                                :href="route('season-types.create')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 fs-14"
                            >
                                Create Season Type
                            </Link>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search season types..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @input="debouncedSearch"
                            />
                        </div>

                        <!-- Season Types Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <button
                                                @click="sort('name')"
                                                class="flex items-center space-x-1"
                                            >
                                                <span>Name</span>
                                                <span v-if="sortField === 'name'" class="text-indigo-600">
                                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                                </span>
                                            </button>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seasons</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="seasonType in seasonTypes.data" :key="seasonType.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ seasonType.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ seasonType.seasons.length }} seasons</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('season-types.edit', seasonType.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="deleteSeasonType(seasonType.id)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination
                                :links="seasonTypes.links"
                                :from="seasonTypes.from"
                                :to="seasonTypes.to"
                                :total="seasonTypes.total"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    seasonTypes: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');

const debouncedSearch = debounce(() => {
    updateFilters();
}, 300);

const updateFilters = () => {
    router.get(
        route('season-types.index'),
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDirection.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const sort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    updateFilters();
};

const deleteSeasonType = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('season-types.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Season type has been deleted.',
                        'success'
                    );
                }
            });
        }
    });
};
</script>
