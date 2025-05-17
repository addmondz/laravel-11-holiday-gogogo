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
                        <!-- Booking Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        <label class="block text-sm font-medium text-gray-700">Room Type</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.room_type.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Dates</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ new Date(booking.start_date).toLocaleDateString() }} - {{ new Date(booking.end_date).toLocaleDateString() }}
                                            ({{ Math.ceil((new Date(booking.end_date) - new Date(booking.start_date)) / (1000 * 60 * 60 * 24)) }} nights)
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Guests</label>
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
</script>
