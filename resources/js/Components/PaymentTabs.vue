<template>
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
                <button
                    @click="refreshPayments"
                    :disabled="isLoading"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="[
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                        activeTab === tab.key
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    {{ tab.label }}
                    <span
                        :class="[
                            'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium',
                            activeTab === tab.key
                                ? 'bg-indigo-100 text-indigo-600'
                                : 'bg-gray-100 text-gray-900'
                        ]"
                    >
                        {{ getTabCount(tab.key) }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredTransactions.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No payments found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ activeTab === 'all' ? 'No payments have been made yet.' : `No ${activeTab} payments found.` }}
                </p>
            </div>

            <!-- Payment List -->
            <div v-else class="space-y-4">
                <div
                    v-for="transaction in filteredTransactions"
                    :key="transaction.id"
                    class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors cursor-pointer"
                    @click="viewTransactionDetails(transaction)"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Status Icon -->
                            <div class="flex-shrink-0">
                                <div
                                    :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center',
                                        getStatusColor(transaction.status).bg
                                    ]"
                                >
                                    <svg
                                        :class="['w-5 h-5', getStatusColor(transaction.status).icon]"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            v-if="transaction.status === 'completed'"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                        <path
                                            v-else-if="transaction.status === 'failed'"
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
                            </div>

                            <!-- Transaction Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ transaction.booking?.package?.name || 'Package' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Booking #{{ transaction.booking?.id }} • {{ transaction.booking?.booking_name }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900">
                                            MYR {{ formatNumber(transaction.amount) }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(transaction.created_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex-shrink-0 ml-4">
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    getStatusColor(transaction.status).badge
                                ]"
                            >
                                {{ formatStatus(transaction.status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center space-x-4">
                                <span>Transaction ID: {{ transaction.transaction_id || 'N/A' }}</span>
                                <span>Method: {{ formatPaymentMethod(transaction.payment_method) }}</span>
                            </div>
                            <div v-if="transaction.processed_at" class="text-right">
                                Processed: {{ formatDate(transaction.processed_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load More Button -->
            <div v-if="hasMorePayments && !isLoading" class="mt-6 text-center">
                <button
                    @click="loadMorePayments"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Load More Payments
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import moment from 'moment';

const props = defineProps({
    initialLimit: {
        type: Number,
        default: 10
    }
});

const transactions = ref([]);
const isLoading = ref(false);
const activeTab = ref('all');
const currentLimit = ref(props.initialLimit);
const hasMorePayments = ref(true);

const tabs = [
    { key: 'all', label: 'All Payments' },
    { key: 'completed', label: 'Successful' },
    { key: 'failed', label: 'Failed' },
    { key: 'pending', label: 'Pending' }
];

const filteredTransactions = computed(() => {
    if (activeTab.value === 'all') {
        return transactions.value;
    }
    return transactions.value.filter(t => t.status === activeTab.value);
});

const getTabCount = (tabKey) => {
    if (tabKey === 'all') {
        return transactions.value.length;
    }
    return transactions.value.filter(t => t.status === tabKey).length;
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed':
            return {
                bg: 'bg-green-100',
                icon: 'text-green-600',
                badge: 'bg-green-100 text-green-800'
            };
        case 'failed':
            return {
                bg: 'bg-red-100',
                icon: 'text-red-600',
                badge: 'bg-red-100 text-red-800'
            };
        case 'pending':
            return {
                bg: 'bg-yellow-100',
                icon: 'text-yellow-600',
                badge: 'bg-yellow-100 text-yellow-800'
            };
        default:
            return {
                bg: 'bg-gray-100',
                icon: 'text-gray-600',
                badge: 'bg-gray-100 text-gray-800'
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

const fetchPayments = async (limit = props.initialLimit, status = null) => {
    try {
        isLoading.value = true;
        const params = { limit };
        if (status && status !== 'all') {
            params.status = status;
        }
        
        const response = await axios.get(route('api.payment-history'), { params });
        
        if (response.data.success) {
            if (limit === props.initialLimit) {
                transactions.value = response.data.transactions;
            } else {
                transactions.value = [...transactions.value, ...response.data.transactions];
            }
            
            hasMorePayments.value = response.data.transactions.length === limit;
        }
    } catch (error) {
        console.error('Error fetching payments:', error);
    } finally {
        isLoading.value = false;
    }
};

const refreshPayments = async () => {
    currentLimit.value = props.initialLimit;
    await fetchPayments(props.initialLimit, activeTab.value === 'all' ? null : activeTab.value);
};

const loadMorePayments = async () => {
    currentLimit.value += props.initialLimit;
    await fetchPayments(currentLimit.value, activeTab.value === 'all' ? null : activeTab.value);
};

const viewTransactionDetails = (transaction) => {
    // Emit event for parent component to handle
    emit('view-transaction', transaction);
};

const emit = defineEmits(['view-transaction']);

// Watch for tab changes
watch(activeTab, async (newTab) => {
    currentLimit.value = props.initialLimit;
    await fetchPayments(props.initialLimit, newTab === 'all' ? null : newTab);
});

onMounted(() => {
    fetchPayments();
});
</script> 