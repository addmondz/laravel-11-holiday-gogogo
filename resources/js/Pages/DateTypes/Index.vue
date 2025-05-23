<template>
    <Head title="Date Types" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Date Types
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">All Date Types</h3>
                            <Link
                                :href="route('date-types.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Create Date Type
                            </Link>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search date types..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @input="debouncedSearch"
                            />
                        </div>

                        <!-- Date Types Table -->
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Ranges</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="dateType in dateTypes.data" :key="dateType.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ dateType.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ dateType.ranges.length }} ranges</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('date-types.edit', $page.props.package_id ? {
                                                    dateType: dateType.id,
                                                    package_id: $page.props.package_id,
                                                    return_to_package: true
                                                } : dateType.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="deleteDateType(dateType.id)"
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
                                :links="dateTypes.links"
                                :from="dateTypes.from"
                                :to="dateTypes.to"
                                :total="dateTypes.total"
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
    dateTypes: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');

const debouncedSearch = debounce(() => {
    updateFilters();
}, 1000);

const updateFilters = () => {
    router.get(
        route('date-types.index'),
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

const deleteDateType = (id) => {
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
            router.delete(route('date-types.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Date type has been deleted.',
                        'success'
                    );
                }
            });
        }
    });
};
</script>
