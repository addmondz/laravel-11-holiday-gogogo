<template>
    <Head title="Packages" />
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
                            <h3 class="text-lg font-medium">All Packages</h3>
                            <Link
                                :href="route('packages.create')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 fs-14"
                            >
                                Create Package
                            </Link>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6">
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search packages..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @input="debouncedSearch"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Packages Table -->
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="packages.data.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            No packages found
                                        </td>
                                    </tr>
                                    <tr v-else v-for="pkg in packages.data" :key="pkg.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ pkg.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ pkg.location }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">RM {{ formatNumber(pkg.display_price_adult) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('packages.show', pkg.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="route('packages.edit', pkg.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="deletePackage(pkg.id)"
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
                                :links="packages.links"
                                :from="packages.from"
                                :to="packages.to"
                                :total="packages.total"
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
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    packages: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');

const debouncedSearch = debounce(() => {
    updateFilters();
}, 1000);

const updateFilters = () => {
    router.get(
        route('packages.index'),
        {
            search: search.value,
            status: status.value,
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

const deletePackage = (id) => {
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
            router.delete(route('packages.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Package has been deleted.',
                        'success'
                    );
                }
            });
        }
    });
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ['packages']
    });
};

const formatNumber = (number) => {
    const num = parseFloat(number);
    if (isNaN(num)) return '0.00';
    return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};
</script>

