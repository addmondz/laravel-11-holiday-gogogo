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

                        <!-- Search and Filters -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search bookings..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        @input="debouncedSearch"
                                    />
                                </div>
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
                                            Package
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dates
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Guests
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Price
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment Status
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
                                            <div class="text-sm text-gray-900">
                                                {{ booking.adults }} Adult{{ booking.adults > 1 ? 's' : '' }}
                                                <span v-if="booking.children > 0">
                                                    , {{ booking.children }} Child{{ booking.children > 1 ? 'ren' : '' }}
                                                </span>
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
                                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                                    booking.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                                    booking.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                ]"
                                            >
                                                {{ booking.payment_status ? booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1) : 'Unpaid' }}
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
                                                    class="text-green-600 hover:text-green-900 ml-4"
                                                >
                                                    {{ booking.payment_status === 'paid' ? 'View Payment' : 'Pay Now' }}
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
import debounce from 'lodash/debounce';
import moment from 'moment';

const props = defineProps({
    bookings: Object,
    filters: Object
});

const search = ref(props.filters.search);

const debouncedSearch = debounce((value) => {
    router.get(
        route('bookings.index'),
        { search: value.target.value },
        { preserveState: true, preserveScroll: true }
    );
}, 1000);

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};
</script>
