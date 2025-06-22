<template>
    <Head title="Booking Details" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Booking Details</h2>
                <Link
                    :href="route('bookings.edit', booking.id)"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Edit Booking
                </Link>
            </div>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-6" />

                        <!-- Payment Status -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div 
                                        :class="[
                                            'w-12 h-12 rounded-full flex items-center justify-center',
                                            getPaymentStatusColor(booking.payment_status).bg
                                        ]"
                                    >
                                        <svg 
                                            :class="['w-6 h-6', getPaymentStatusColor(booking.payment_status).icon]"
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                v-if="booking.payment_status === 'paid'"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                            <path
                                                v-else-if="booking.payment_status === 'failed'"
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
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Payment Status</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ formatPaymentStatus(booking.payment_status) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Information</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Booking Name</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.booking_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.phone_number }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">IC/Passport Number</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.booking_ic }}</p>
                                    </div>
                                    <div v-if="booking.special_remarks">
                                        <label class="block text-sm font-medium text-gray-700">Special Remarks</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.special_remarks }}</p>
                                    </div>
                                    <div v-if="booking.special_remarks">
                                        <label class="block text-sm font-medium text-gray-700">Booking Reference</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.uuid }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Package Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Package</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.package.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Dates</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ new Date(booking.start_date).toLocaleDateString() }} - {{ new Date(booking.end_date).toLocaleDateString() }}
                                            ({{ Math.ceil((new Date(booking.end_date) - new Date(booking.start_date)) / (1000 * 60 * 60 * 24)) }} nights)
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Guests</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ booking.adults }} Adult{{ booking.adults > 1 ? 's' : '' }}
                                            <span v-if="booking.children > 0">
                                                , {{ booking.children }} Child{{ booking.children > 1 ? 'ren' : '' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Price</label>
                                        <p class="mt-1 text-sm font-medium text-gray-900">MYR {{ formatNumber(booking.total_price) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                    <div class="p-6 text-gray-900">
                        <!-- Room Details -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Room Details</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Guests</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="room in booking.rooms" :key="room.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ room.room_type.name }} 
                                                <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded whitespace-nowrap">
                                                    Max {{ room.room_type.max_occupancy }} pax
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.adults }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.infants }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.adults + room.children + room.infants }}</div>
                                        </td>
                                    </tr>
                                    <!-- Summary Row -->
                                    <!-- <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Total</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.adults }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.adults + booking.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">-</div>
                                        </td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                    <div class="p-6 text-gray-900">
                        <!-- Transaction History -->
                        <div v-if="booking.transactions && booking.transactions.length > 0">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="transaction in booking.transactions" :key="transaction.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ new Date(transaction.created_at).toLocaleDateString() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ transaction.transaction_id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ transaction.payment_method }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                MYR {{ formatNumber(transaction.amount) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span 
                                                    :class="[
                                                        'px-2 py-1 text-sm font-semibold rounded-full',
                                                        transaction.status === 'paid' ? 'bg-green-100 text-green-800' :
                                                        transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-red-100 text-red-800'
                                                    ]"
                                                >
                                                    {{ transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';

const props = defineProps({
    booking: Object
});

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const breadcrumbs = computed(() => [
    { title: 'Bookings', link: route('bookings.index') },
    { title: 'Booking Details' }
]);

const getPaymentStatusColor = (status) => {
    switch (status) {
        case 'paid':
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

const formatPaymentStatus = (status) => {
    switch (status) {
        case 'paid':
            return 'Paid';
        case 'pending':
            return 'Pending Payment';
        case 'failed':
            return 'Payment Failed';
        default:
            return 'Unpaid';
    }
};
</script>
