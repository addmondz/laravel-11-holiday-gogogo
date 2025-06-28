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
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 hover:ring hover:ring-indigo-600"
                            >
                                Create Package
                            </Link>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-white rounded-lg shadow-sm pb-4">
                                <!-- Date Range Filter (spans 2 cols on large screen) -->
                                <div class="md:col-span-1 lg:col-span-2 space-y-2">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                            <input
                                                type="date"
                                                v-model="dateFrom"
                                                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                            <input
                                                type="date"
                                                v-model="dateTo"
                                                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        💡 <strong class="font-semibold text-gray-800">Date Filter:</strong> Find packages available during your travel dates. Leave empty to see all packages.
                                    </p>
                                </div>

                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Packages</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search packages..."
                                        class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    />
                                </div>
                            </div>

                            <div class="mt-4 flex justify-start items-center gap-2">
                                <button
                                    @click="updateFilters"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    Search
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Clear Filters
                                </button>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="packages.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No packages found
                                        </td>
                                    </tr>
                                    <tr v-else v-for="pkg in packages.data" :key="pkg.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ pkg.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ pkg.uuid }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ pkg.location }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">RM {{ formatNumber(pkg.display_price_adult) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(pkg.package_start_date) }} - {{ formatDate(pkg.package_end_date) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ calculateDays(pkg.package_start_date, pkg.package_end_date) }} days
                                            </div>
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
                                                class="text-red-600 hover:text-red-900 mr-3"
                                            >
                                                Delete
                                            </button>
                                            <button
                                                @click="duplicatePackage(pkg.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                Duplicate
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
import moment from 'moment';

const props = defineProps({
    packages: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const sortField = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');
const dateFrom = ref(props.filters.dateFrom || '');
const dateTo = ref(props.filters.dateTo || '');

const updateFilters = () => {
    router.get(
        route('packages.index'),
        {
            search: search.value,
            status: status.value,
            sort: sortField.value,
            direction: sortDirection.value,
            dateFrom: dateFrom.value,
            dateTo: dateTo.value
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

const duplicatePackage = (packageId) => {
    router.visit(route('packages.duplicate-form', packageId));
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

const clearFilters = () => {
    router.get(
        route('packages.index'),
        {},
        { preserveState: false, preserveScroll: false }
    );
};

const formatDate = (date) => {
    return moment(date).format('DD MMM YYYY');
};

const calculateDays = (startDate, endDate) => {
    return moment(endDate).diff(moment(startDate), 'days') + 1;
};
</script>

