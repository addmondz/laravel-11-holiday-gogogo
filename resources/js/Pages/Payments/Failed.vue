<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
                <p class="text-gray-600">We were unable to process your payment.</p>
            </div>

            <div class="space-y-4">
                <button
                    @click="goBack"
                    class="block w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                >
                    Try Again
                </button>

                <button
                    @click="goBack"
                    class="block w-full bg-gray-200 text-gray-700 px-4 py-3 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                >
                    Back
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
const goBack = () => {
    window.history.back();
};
</script> 