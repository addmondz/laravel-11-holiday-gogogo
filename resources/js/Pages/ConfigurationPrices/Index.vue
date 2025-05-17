<template>
    <Head title="Configuration Prices" />
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
                            <h3 class="text-lg font-medium text-gray-900">
                                All Prices
                            </h3>
                            <Link
                                :href="route('configuration-prices.create')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            >
                                Create New Price
                            </Link>
                        </div>

                        <!-- Selection Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            <div>
                                <label for="package" class="block text-sm font-medium text-gray-700">Package</label>
                                <select
                                    id="package"
                                    v-model="selectedPackage"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Package</option>
                                    <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                        {{ pkg.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                                <select
                                    id="season"
                                    v-model="selectedSeason"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Season</option>
                                    <option v-for="season in seasons" :key="season.id" :value="season.id">
                                        {{ season.type.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="dateType" class="block text-sm font-medium text-gray-700">Date Type</label>
                                <select
                                    id="dateType"
                                    v-model="selectedDateType"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Date Type</option>
                                    <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="roomType" class="block text-sm font-medium text-gray-700">Room Type</label>
                                <select
                                    id="roomType"
                                    v-model="selectedRoomType"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Room Type</option>
                                    <option v-for="type in roomTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="flex mb-8 space-x-4">
                            <button
                                @click="fetchPrices"
                                :disabled="!canSearch"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Search Prices
                            </button>
                            <button
                                @click="resetSearch"
                                class="px-6 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                            >
                                Reset
                            </button>
                        </div>

                        <!-- Price Matrix Tables -->
                        <div v-if="showPriceMatrix" class="space-y-8">
                            <!-- Base Charge Table -->
                            <div class="overflow-x-auto">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Base Charges</h3>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                            <th v-for="children in 6" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ children-1 }} Children
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="adults in 6" :key="adults">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ adults }} Adults
                                            </td>
                                            <td v-for="children in 6" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ getPrice(adults, children-1, 'base_charge') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Surcharge Table -->
                            <div v-if="hasSurcharges" class="overflow-x-auto">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Surcharges</h3>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                            <th v-for="children in 6" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ children-1 }} Children
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="adults in 6" :key="adults">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ adults }} Adults
                                            </td>
                                            <td v-for="children in 6" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ getPrice(adults, children-1, 'sur_charge') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else-if="searched" class="text-center py-8 text-gray-600 border b-a border-solid border-gray-600 rounded-md">
                            No prices found for the selected criteria.
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 bg-gray-50 rounded-md">
                            Please select all options and click search to view the price matrix.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    packages: Array,
    seasons: Array,
    dateTypes: Array,
    roomTypes: Array
});

const selectedPackage = ref('');
const selectedSeason = ref('');
const selectedDateType = ref('');
const selectedRoomType = ref('');
const searched = ref(false);
const showPriceMatrix = ref(false);
const configurationPrices = ref([]);

const canSearch = computed(() => {
    return selectedPackage.value && selectedSeason.value && selectedDateType.value && selectedRoomType.value;
});

const hasSurcharges = computed(() => {
    return configurationPrices.value.some(price => price.type === 'sur_charge');
});

const fetchPrices = () => {
    axios.post(route('configuration-prices.fetchPricesSearchIndex'), {
        package_configuration_id: selectedPackage.value,
        season_id: selectedSeason.value,
        date_type_id: selectedDateType.value,
        room_type: selectedRoomType.value
    })
    .then(response => {
        if (response.data && response.data.length > 0) {
            configurationPrices.value = response.data[0].prices;
            searched.value = true;
            showPriceMatrix.value = true;
        } else {
            searched.value = true;
            showPriceMatrix.value = false;
        }
    })
    .catch(error => {
        console.error('Error fetching prices:', error);
        searched.value = true;
        showPriceMatrix.value = false;
    });
};

const getPrice = (adults, children, type) => {
    const price = configurationPrices.value.find(p =>
        p.number_of_adults === adults &&
        p.number_of_children === children &&
        p.type === type
    );
    return price ? `$${price.adult_price}` : '-';
};

const resetSearch = () => {
    selectedPackage.value = '';
    selectedSeason.value = '';
    selectedDateType.value = '';
    selectedRoomType.value = '';
    searched.value = false;
    showPriceMatrix.value = false;
    configurationPrices.value = [];
};
</script>
