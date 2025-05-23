<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Simulation</h1>
                <p class="text-gray-600">Booking #{{ booking.id }}</p>
                <div class="mt-4">
                    <p class="text-lg font-semibold text-gray-900">Amount: MYR {{ formatNumber(transaction.amount) }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <button
                    @click="simulatePayment('success')"
                    class="w-full bg-green-600 text-white px-4 py-3 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                    :disabled="isProcessing"
                >
                    {{ isProcessing ? 'Processing...' : 'Simulate Successful Payment' }}
                </button>

                <button
                    @click="simulatePayment('failed')"
                    class="w-full bg-red-600 text-white px-4 py-3 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    :disabled="isProcessing"
                >
                    {{ isProcessing ? 'Processing...' : 'Simulate Failed Payment' }}
                </button>
            </div>

            <div v-if="transaction.status === 'failed'" class="mt-6">
                <button
                    @click="retryPayment"
                    class="w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                >
                    Retry Payment
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    booking: Object,
    transaction: Object
});

const isProcessing = ref(false);

const formatNumber = (number) => {
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const simulatePayment = async (status) => {
    try {
        isProcessing.value = true;
        
        // Update transaction status using the new handlePayment endpoint
        const response = await axios.post(route('api.payment.handle', props.booking.uuid), {
            status: status,
            transaction_id: props.transaction.id,
            is_simulation: true
        });

        if (response.data.success) {
            // Redirect based on status
            if (status === 'success') {
                window.location.href = route('api.payment.success', props.booking.uuid);
            } else {
                window.location.href = route('api.payment.failed', props.booking.uuid);
            }
        }
    } catch (error) {
        console.error('Payment simulation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Payment Failed',
            text: error.response?.data?.message || 'An error occurred while processing the payment',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        isProcessing.value = false;
    }
};

const retryPayment = () => {
    router.visit(route('api.payment.show', props.booking.uuid));
};
</script> 