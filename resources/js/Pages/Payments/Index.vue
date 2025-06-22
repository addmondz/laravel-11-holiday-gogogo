<template>
    <Head title="Payments" />
    
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Payments
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Payment History</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Total Transactions</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.total }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Completed</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.completed }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
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
                                            <p class="text-sm font-medium text-gray-500">Failed</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.failed }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                                <div class="p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Pending</p>
                                            <p class="text-lg font-semibold text-gray-900">{{ summary.pending }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Filters -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <input
                                        type="text"
                                        v-model="filters.search"
                                        placeholder="Search payments..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @input="debouncedSearch"
                                    />
                                </div>
                                <div>
                                    <select
                                        v-model="filters.status"
                                        @change="applyFilters"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="all">All Status</option>
                                        <option value="completed">Completed</option>
                                        <option value="failed">Failed</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div>
                                    <button
                                        @click="clearFilters"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Transaction ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Package
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment Method
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="transactions.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No transactions found
                                        </td>
                                    </tr>
                                    <tr v-else v-for="transaction in transactions.data" :key="transaction.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ transaction.transaction_id || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Order: {{ transaction.order_id || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ transaction.booking?.booking_name || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ transaction.booking?.phone_number || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ transaction.booking?.package?.name || 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Booking #{{ transaction.booking?.id || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatPaymentMethod(transaction.payment_method) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                MYR {{ formatNumber(transaction.amount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="[
                                                    'px-3 py-1 text-xs font-semibold rounded-full',
                                                    transaction.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                    transaction.status === 'failed' ? 'bg-red-100 text-red-800' :
                                                    transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-gray-100 text-gray-800'
                                                ]"
                                            >
                                                {{ formatStatus(transaction.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(transaction.created_at) }}
                                            </div>
                                            <div v-if="transaction.processed_at" class="text-sm text-gray-500">
                                                Processed: {{ formatDate(transaction.processed_at) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <button
                                                    @click="viewTransactionDetails(transaction)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </button>
                                                <a
                                                    v-if="transaction.booking"
                                                    :href="route('bookings.show', transaction.booking.id)"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    Booking
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
                                :links="transactions.links"
                                :from="transactions.from"
                                :to="transactions.to"
                                :total="transactions.total" 
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details Modal -->
        <Modal :show="showTransactionModal" @close="showTransactionModal = false">
            <div v-if="selectedTransaction" class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Transaction Details</h3>
                    <button
                        @click="showTransactionModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Transaction Status -->
                    <div class="flex items-center space-x-3">
                        <div
                            :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center',
                                getStatusColor(selectedTransaction.status).bg
                            ]"
                        >
                            <svg
                                :class="['w-6 h-6', getStatusColor(selectedTransaction.status).icon]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    v-if="selectedTransaction.status === 'completed'"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                                <path
                                    v-else-if="selectedTransaction.status === 'failed'"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">
                                {{ formatStatus(selectedTransaction.status) }}
                            </h4>
                            <p class="text-sm text-gray-500">
                                Transaction ID: {{ selectedTransaction.transaction_id || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <!-- Transaction Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Booking Information</h5>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Booking ID:</span>
                                    <span class="ml-2 font-medium">{{ selectedTransaction.booking?.id || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Customer:</span>
                                    <span class="ml-2 font-medium">{{ selectedTransaction.booking?.booking_name || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Package:</span>
                                    <span class="ml-2 font-medium">{{ selectedTransaction.booking?.package?.name || 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Phone:</span>
                                    <span class="ml-2 font-medium">{{ selectedTransaction.booking?.phone_number || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Payment Information</h5>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Amount:</span>
                                    <span class="ml-2 font-medium text-lg text-gray-900">
                                        MYR {{ formatNumber(selectedTransaction.amount) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Method:</span>
                                    <span class="ml-2 font-medium">{{ formatPaymentMethod(selectedTransaction.payment_method) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="ml-2 font-medium">{{ formatDate(selectedTransaction.created_at) }}</span>
                                </div>
                                <div v-if="selectedTransaction.processed_at">
                                    <span class="text-gray-500">Processed:</span>
                                    <span class="ml-2 font-medium">{{ formatDate(selectedTransaction.processed_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div v-if="selectedTransaction.message">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Message</h5>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-md">
                            {{ selectedTransaction.message }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            @click="showTransactionModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Close
                        </button>
                        <a
                            v-if="selectedTransaction.booking"
                            :href="route('quotation.with-hash', selectedTransaction.booking.package?.uuid) + '?booking=' + selectedTransaction.booking.uuid"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            View Booking
                        </a>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import debounce from 'lodash/debounce';
import moment from 'moment';

const props = defineProps({
    transactions: Object,
    summary: Object,
    filters: Object
});

const showTransactionModal = ref(false);
const selectedTransaction = ref(null);

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
});

const debouncedSearch = debounce((value) => {
    router.get(
        route('payments.index'),
        { 
            search: value.target.value,
            status: filters.value.status,
            date_from: filters.value.date_from,
            date_to: filters.value.date_to
        },
        { preserveState: true, preserveScroll: true }
    );
}, 500);

const applyFilters = () => {
    router.get(
        route('payments.index'),
        { 
            search: filters.value.search,
            status: filters.value.status,
            date_from: filters.value.date_from,
            date_to: filters.value.date_to
        },
        { preserveState: true, preserveScroll: true }
    );
};

const clearFilters = () => {
    filters.value = {
        search: '',
        status: 'all',
        date_from: '',
        date_to: ''
    };
    router.get(
        route('payments.index'),
        {},
        { preserveState: true, preserveScroll: true }
    );
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed':
            return {
                bg: 'bg-green-100',
                icon: 'text-green-600'
            };
        case 'failed':
            return {
                bg: 'bg-red-100',
                icon: 'text-red-600'
            };
        case 'pending':
            return {
                bg: 'bg-yellow-100',
                icon: 'text-yellow-600'
            };
        default:
            return {
                bg: 'bg-gray-100',
                icon: 'text-gray-600'
            };
    }
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatPaymentMethod = (method) => {
    if (!method) return 'N/A';
    return method.charAt(0).toUpperCase() + method.slice(1);
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const formatDate = (date) => {
    return moment(date).format('DD MMM YYYY, HH:mm');
};

const viewTransactionDetails = (transaction) => {
    selectedTransaction.value = transaction;
    showTransactionModal.value = true;
};
</script> 