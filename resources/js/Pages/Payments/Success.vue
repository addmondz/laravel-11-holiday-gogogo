<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                <p class="text-gray-600">Your payment has been processed successfully.</p>
                <p class="text-gray-600">The booking details will be sent to your email.</p>
                
                <!-- Error message for processing failures -->
                <div v-if="error" class="mt-4 p-3 bg-yellow-100 border border-yellow-400 rounded-md">
                    <p class="text-yellow-800 text-sm">{{ error }}</p>
                </div>
            </div>  
            
            <div class="flex justify-center">
                <a 
                    v-if="bookingUuid && packageUuid" 
                    :href="`${route('quotation.with-hash', packageUuid)}?booking=${bookingUuid}`" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-lg font-medium"
                >
                    Back to Booking
                </a>
                <a 
                    v-else 
                    href="/" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-lg font-medium"
                >
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
const goBack = () => {
    window.history.back();
};

const props = defineProps({
    bookingUuid: String,
    packageUuid: String,
    transaction: Object,
    error: String
});
</script> 