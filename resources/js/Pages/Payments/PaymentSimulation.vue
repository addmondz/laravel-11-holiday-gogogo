<template>
    <Head title="Payment Simulation" />
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Payment Processing</h2>
                        <p class="text-gray-600 mb-8">
                            Please complete your payment for booking #{{ transaction.booking_id }}
                        </p>

                        <!-- Payment Details -->
                        <div class="max-w-2xl mx-auto bg-gray-50 rounded-lg p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Details</h3>
                            <div class="grid grid-cols-2 gap-4 text-left">
                                <div>
                                    <p class="text-sm text-gray-600">Booking Reference</p>
                                    <p class="font-medium">{{ transaction.booking_id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Amount</p>
                                    <p class="font-medium">MYR {{ formatNumber(transaction.amount) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status</p>
                                    <p class="font-medium">
                                        <span 
                                            :class="[
                                                'px-2 py-1 text-sm font-semibold rounded-full',
                                                transaction.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="error" class="mb-6 p-4 bg-red-50 rounded-md">
                            <p class="text-sm text-red-600">{{ error }}</p>
                        </div>

                        <!-- Payment Actions -->
                        <div v-if="transaction.status === 'failed'" class="mb-8">
                            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                                <p class="text-red-800">
                                    Your last payment attempt was unsuccessful. Please try again.
                                </p>
                            </div>
                        </div>

                        <!-- Simulation Buttons -->
                        <div class="flex justify-center space-x-4">
                            <button
                                @click="simulatePayment('completed')"
                                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                :disabled="isProcessing"
                            >
                                {{ isProcessing ? 'Processing...' : 'Complete Payment' }}
                            </button>
                            <button
                                @click="simulatePayment('failed')"
                                class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                :disabled="isProcessing"
                            >
                                {{ isProcessing ? 'Processing...' : 'Simulate Failed Payment' }}
                            </button>
                        </div>

                        <!-- Cancel Payment -->
                        <div class="mt-6">
                            <button
                                @click="cancelPayment"
                                class="text-gray-600 hover:text-gray-900"
                                :disabled="isProcessing"
                            >
                                Cancel and return to booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    transaction: {
        type: Object,
        required: true
    }
});

const isProcessing = ref(false);
const error = ref(null);

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const simulatePayment = async (status) => {
    isProcessing.value = true;
    error.value = null;

    try {
        const response = await axios.post(`/api/transactions/${props.transaction.id}/update-status`, {
            status: status
        });

        if (response.data.success) {
            if (status === 'completed') {
                await Swal.fire({
                    title: 'Payment Successful!',
                    text: 'Your payment has been processed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#10B981'
                });
                window.location.href = route('bookings.payment.success', props.transaction.booking_id);
            } else {
                await Swal.fire({
                    title: 'Payment Failed',
                    text: 'Your payment could not be processed. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
                window.location.reload();
            }
        } else {
            throw new Error('Failed to update transaction status');
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to process payment simulation';
        Swal.fire({
            title: 'Error',
            text: error.value,
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        isProcessing.value = false;
    }
};

const cancelPayment = () => {
    Swal.fire({
        title: 'Cancel Payment?',
        text: 'Are you sure you want to cancel this payment?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel payment',
        cancelButtonText: 'No, continue payment',
        confirmButtonColor: '#EF4444'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = route('bookings.show', props.transaction.booking_id);
        }
    });
};
</script> 