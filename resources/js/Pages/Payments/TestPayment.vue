<template>
    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Test Payment Form</h1>
                    <p class="text-gray-600">Please fill up the details below in order to test the payment.</p>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-6">
                    <!-- Detail/Description -->
                    <div>
                        <label for="detail" class="block text-sm font-medium text-gray-700 mb-2">
                            Detail
                        </label>
                        <textarea
                            id="detail"
                            v-model="form.detail"
                            rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Description of the transaction"
                            required
                        ></textarea>
                        <p v-if="errors.detail" class="mt-1 text-sm text-red-600">{{ errors.detail }}</p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">MYR</span>
                            </div>
                            <input
                                type="number"
                                id="amount"
                                v-model="form.amount"
                                step="0.01"
                                min="0.01"
                                class="block w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="0.00"
                                required
                            />
                        </div>
                        <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                    </div>

                    <!-- Order ID -->
                    <div>
                        <label for="order_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Order ID
                        </label>
                        <input
                            type="text"
                            id="order_id"
                            v-model="form.order_id"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50"
                            readonly
                        />
                        <p class="mt-1 text-sm text-gray-500">Order ID is defaulted to timestamp</p>
                    </div>

                    <!-- Customer Name -->
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer Name
                        </label>
                        <input
                            type="text"
                            id="customer_name"
                            v-model="form.customer_name"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Name of the customer"
                            required
                        />
                        <p v-if="errors.customer_name" class="mt-1 text-sm text-red-600">{{ errors.customer_name }}</p>
                    </div>

                    <!-- Customer Email -->
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer Email
                        </label>
                        <input
                            type="email"
                            id="customer_email"
                            v-model="form.customer_email"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Email of the customer"
                            required
                        />
                        <p v-if="errors.customer_email" class="mt-1 text-sm text-red-600">{{ errors.customer_email }}</p>
                    </div>

                    <!-- Customer Contact No -->
                    <div>
                        <label for="customer_contact" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer Contact No
                        </label>
                        <input
                            type="tel"
                            id="customer_contact"
                            v-model="form.customer_contact"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Contact number of customer"
                            required
                        />
                        <p v-if="errors.customer_contact" class="mt-1 text-sm text-red-600">{{ errors.customer_contact }}</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white text-base font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isSubmitting ? 'Processing...' : 'Proceed to Payment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({
    detail: '',
    amount: '',
    order_id: '',
    customer_name: '',
    customer_email: '',
    customer_contact: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Generate timestamp-based order ID
const generateOrderId = () => {
    return Date.now().toString();
};

// Initialize form with default order ID and dummy data
onMounted(() => {
    form.value.order_id = generateOrderId();
    
    // Fill with dummy data for testing
    form.value.detail = 'Test payment for holiday package booking - Room reservation for 2 adults and 1 child for 3 nights';
    form.value.amount = '100.01';
    form.value.customer_name = 'John Doe';
    form.value.customer_email = 'john.doe@example.com';
    form.value.customer_contact = '60123456789';
});

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    // Validate detail
    if (!form.value.detail.trim()) {
        errors.value.detail = 'Detail is required';
        isValid = false;
    }

    // Validate amount
    if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
        errors.value.amount = 'Please enter a valid amount greater than 0';
        isValid = false;
    }

    // Validate customer name
    if (!form.value.customer_name.trim()) {
        errors.value.customer_name = 'Customer name is required';
        isValid = false;
    }

    // Validate customer email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!form.value.customer_email.trim()) {
        errors.value.customer_email = 'Customer email is required';
        isValid = false;
    } else if (!emailRegex.test(form.value.customer_email)) {
        errors.value.customer_email = 'Please enter a valid email address';
        isValid = false;
    }

    // Validate customer contact
    if (!form.value.customer_contact.trim()) {
        errors.value.customer_contact = 'Customer contact number is required';
        isValid = false;
    }

    return isValid;
};

const submitPayment = async () => {
    if (!validateForm()) return;

    try {
        isSubmitting.value = true;
        
        // Here you would typically send the data to your payment processing endpoint
        const response = await axios.post('/create-test-payment-transaction', {
            detail: form.value.detail,
            amount: parseFloat(form.value.amount),
            order_id: form.value.order_id,
            customer_name: form.value.customer_name,
            customer_email: form.value.customer_email,
            customer_contact: form.value.customer_contact
        });

        if (response.data.status === 'success') {
            // Handle successful payment initiation
            console.log('Payment initiated:', response.data);
            window.open(response.data.url, '_blank');
            // You might redirect to a payment gateway or show success message
        } else {
            throw new Error(response.data.message || 'Payment initiation failed');
        }
    } catch (error) {
        console.error('Payment error:', error);
        // Handle error - you might want to show an error message to the user
    } finally {
        isSubmitting.value = false;
    }
};
</script> 