<template>
    <Head title="Bookings" />
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
                            <h3 class="text-lg font-medium">All Bookings</h3>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-6">
                            <!-- Total Transactions -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 transition-colors duration-200" @click="triggerSearch('all')" :class="{ 'border-indigo-500': filters.status === 'all' }">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.total ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Approval -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 transition-colors duration-200" @click="triggerSearch('0')" :class="{ 'border-indigo-500': filters.status === '0' }">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.pending ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Completed -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 transition-colors duration-200" @click="triggerSearch('1')" :class="{ 'border-indigo-500': filters.status === '1' }">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Approved</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.approved ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rejected -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 transition-colors duration-200" @click="triggerSearch('2')" :class="{ 'border-indigo-500': filters.status === '2' }">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Rejected</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.rejected ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Completed -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 transition-colors duration-200" @click="triggerSearch('3')" :class="{ 'border-indigo-500': filters.status === '3' }">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Payment Completed</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.payment_completed ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Refunded -->
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 cursor-pointer hover:border-indigo-300 transition-colors duration-200" @click="triggerSearch('4')" :class="{ 'border-indigo-500': filters.status === '4' }">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Refunded</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.refunded ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search and Filters -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-white rounded-lg shadow-sm p-6">
                                <!-- Date Range Filter -->
                                <div class="md:col-span-1 lg:col-span-2 space-y-2">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Travel Date From</label>
                                            <input
                                                type="date"
                                                v-model="dateFrom"
                                                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Travel Date To</label>
                                            <input
                                                type="date"
                                                v-model="dateTo"
                                                class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                            />
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        💡 <strong class="font-semibold text-gray-800">Date Filter:</strong> Find bookings for specific travel dates. Leave empty to see all bookings.
                                    </p>
                                </div>

                                <!-- Search -->
                                <div @keyup.enter="updateFilters">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Bookings</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search by name, email, phone, ID..."
                                        class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    />
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select
                                        v-model="filters.status"
                                        class="w-full rounded-md border border-gray-300 shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    >
                                        <option value="all">All Status</option>
                                        <option value="0">Pending Approval</option>
                                        <option value="1">Approved</option>
                                        <option value="2">Rejected</option>
                                        <option value="3">Payment Completed</option>
                                        <option value="4">Refunded</option>
                                    </select>
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

                        <!-- Bookings Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Booking Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Booking ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Package
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dates
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Price
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="bookings.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No bookings found
                                        </td>
                                    </tr>
                                    <tr v-else v-for="booking in bookings.data" :key="booking.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.booking_name }}</div>
                                            <div class="text-sm text-gray-500">{{ booking.phone_number }}</div>
                                            <div class="text-sm text-gray-500">{{ booking.booking_email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ booking.uuid }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ booking.adults }} Adult{{ booking.adults > 1 ? 's' : '' }}
                                                <span v-if="booking.children > 0">
                                                    , {{ booking.children }} Child{{ booking.children > 1 ? 'ren' : '' }}
                                                </span>
                                                <span v-if="booking.infants > 0">
                                                    , {{ booking.infants }} Infant{{ booking.infants > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ booking.package.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ moment(booking.start_date).format('DD MMM YYYY') }} - {{ moment(booking.end_date).format('DD MMM YYYY') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ Math.ceil((new Date(booking.end_date) - new Date(booking.start_date)) / (1000 * 60 * 60 * 24)) }} nights
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                MYR {{ formatNumber(booking.total_price) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="[
                                                    'px-4 py-1 text-xs font-semibold rounded-full block min-w-[50px] text-center',
                                                    booking.status == 0 ? 'bg-yellow-100 text-yellow-800' :
                                                    booking.status == 1 ? 'bg-green-100 text-green-800' :
                                                    booking.status == 2 ? 'bg-red-100 text-red-800' :
                                                    booking.status == 3 ? 'bg-blue-100 text-blue-800' :
                                                    booking.status == 4 ? 'bg-slate-400 text-white' :
                                                    'bg-red-100 text-red-800'   
                                                ]"
                                            >
                                                {{ convertStatus(booking.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <Link
                                                    :href="route('bookings.show', booking.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="route('bookings.edit', booking.id)"
                                                    class="text-blue-600 hover:text-blue-900 ml-4"
                                                >
                                                    Edit
                                                </Link>
                                                <a
                                                    :href="route('quotation.with-hash', booking.package.uuid) + '?booking=' + booking.uuid"
                                                    :class="[
                                                        'ml-4',
                                                        booking.status == 1 ? 'text-green-600 hover:text-green-900' :
                                                        'text-blue-600 hover:text-blue-900'
                                                    ]"
                                                >
                                                    {{ booking.status == 1 ? 'Pay Now' : 'View Booking' }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination 
                                :links="bookings.links"
                                :from="bookings.from"
                                :to="bookings.to"
                                :total="bookings.total" 
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import moment from 'moment';

const props = defineProps({
    bookings: Object,
    filters: Object,
    summary: Object
});

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    dateFrom: props.filters.dateFrom || '',
    dateTo: props.filters.dateTo || '',
});

const search = ref(props.filters.search || '');
const dateFrom = ref(props.filters.dateFrom || '');
const dateTo = ref(props.filters.dateTo || '');

const clearFilters = () => {
    router.get(
        route('bookings.index'),
        {},
        { preserveState: false, preserveScroll: false }
    );
};

const updateFilters = () => {
    router.get(
        route('bookings.index'),
        { 
            search: search.value,
            status: filters.value.status,
            dateFrom: dateFrom.value,
            dateTo: dateTo.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const convertStatus = (status) => {
    switch (status) {
        case 0:
            return 'Pending Approval';
        case 1:
            return 'Approved';
        case 2:
            return 'Rejected';
        case 3: 
            return 'Payment Completed';
        case 4:
            return 'Refunded';
        default:
            return 'Unknown';
    }
};

const triggerSearch = (status) => {
    if (status === 'all') {
        filters.value.status = 'all';
        router.get(
            route('bookings.index'),
            { search: filters.value.search },
            { preserveState: true, preserveScroll: true }
        );
    } else {
        filters.value.status = status;
        router.get(
            route('bookings.index'),
            { 
                search: filters.value.search,
                status: status,
            },
            { preserveState: true, preserveScroll: true }
        );
    }
};
</script>
