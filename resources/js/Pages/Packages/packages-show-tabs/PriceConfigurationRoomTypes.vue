<template>
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                    <select
                        id="season"
                        v-model="selectedSeason"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Select Season</option>
                        <option v-for="season in seasonTypes" :key="season.id" :value="season.id">
                            {{ season.name }}
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
            </div>

            <!-- Search Button -->
            <div class="mt-6 flex space-x-2">
                <button
                    @click="fetchPrices"
                    :disabled="!canSearch || isPriceLoading"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isPriceLoading">Loading...</span>
                    <span v-else>Search Prices</span>
                </button>
                <button
                    @click="resetSearch"
                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Results -->
        <div v-if="searched" class="bg-white shadow rounded-lg p-6">
            <div v-if="isPriceLoading" class="flex justify-center items-center h-full min-h-[400px]">
                <LoadingComponent />
            </div>

            <div v-else-if="!showPriceMatrix" class="text-center py-8 text-gray-500">
                No price configurations found for the selected filters.
            </div>

            <div v-else class="space-y-8">
                <!-- Price Matrix for all Room Types -->
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Price Matrix for All Room Types</h3>
                    <div class="space-x-2">
                        <button
                            v-if="!isEditMode"
                            @click="openPriceForm('edit')"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                        >
                            Edit All Prices
                        </button>
                        <template v-else>
                            <button
                                @click="closePriceForm"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submitPrices"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                                :disabled="priceForm.processing"
                            >
                                {{ isEditMode ? 'Update All Prices' : 'Create Prices' }}
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Display View -->
                <template v-if="!isEditMode">
                    <!-- Base Charge Table -->
                    <div class="overflow-x-auto">
                        <h4 class="text-lg font-bold text-gray-700 mb-2">Base Charge</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</th>
                                    <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ children-1 }} Children
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="config in configurations" :key="config.id">
                                    <template v-for="adults in 4" :key="`${config.id}-${adults}`">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ config.room_type.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ adults }} Adults
                                            </td>
                                            <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <template v-if="isValidOccupancy(adults, children-1, 'adult')">
                                                    <div class="font-medium">Adult: {{ getPrice(config, adults, children-1, 'base_charge', 'adult') }}</div>
                                                </template>
                                                <template v-else>
                                                    <div class="text-gray-400">Adult: -</div>
                                                </template>
                                                <template v-if="isValidOccupancy(adults, children-1, 'child')">
                                                    <div class="font-medium">Child: {{ getPrice(config, adults, children-1, 'base_charge', 'child') }}</div>
                                                </template>
                                                <template v-else>
                                                    <div class="text-gray-400">Child: -</div>
                                                </template>
                                            </td>
                                        </tr>
                                        <tr v-if="adults == 4" class="bg-gray-100">
                                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</td>
                                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</td>
                                            <td v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ children-1 }} Children
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Surcharge Table -->
                    <div v-if="hasSurcharges" class="overflow-x-auto">
                        <h4 class="text-lg font-bold text-gray-700 mb-2">Surcharge</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</th>
                                    <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ children-1 }} Children
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="config in configurations" :key="config.id">
                                    <template v-for="adults in 4" :key="`${config.id}-${adults}`">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ config.room_type.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ adults }} Adults
                                            </td>
                                            <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <template v-if="isValidOccupancy(adults, children-1, 'adult')">
                                                    <div class="font-medium">Adult: {{ getPrice(config, adults, children-1, 'sur_charge', 'adult') }}</div>
                                                </template>
                                                <template v-else>
                                                    <div class="text-gray-400">Adult: -</div>
                                                </template>
                                                <template v-if="isValidOccupancy(adults, children-1, 'child')">
                                                    <div class="font-medium">Child: {{ getPrice(config, adults, children-1, 'sur_charge', 'child') }}</div>
                                                </template>
                                                <template v-else>
                                                    <div class="text-gray-400">Child: -</div>
                                                </template>
                                            </td>
                                        </tr>
                                        <tr v-if="adults == 4" class="bg-gray-100">
                                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</td>
                                            <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</td>
                                            <td v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ children-1 }} Children
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

                <!-- Edit View -->
                <template v-else>
                    <form @submit.prevent="submitPrices">
                        <div class="space-y-8">
                            <!-- Base Charge Table -->
                            <div class="overflow-x-auto">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-bold text-gray-900">Base Charges</h3>
                                    <button 
                                        type="button"
                                        @click="applyBasePricesToAll"
                                        class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 hover:bg-yellow-200 rounded-md transition-colors duration-200 border border-yellow-300 hover:border-yellow-400"
                                    >
                                        Apply 1st Price To All
                                    </button>
                                </div>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</th>
                                            <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ children-1 }} Children
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template v-for="config in configurations" :key="config.id">
                                            <template v-for="adults in 4" :key="`${config.id}-${adults}`">
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ config.room_type.name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ adults }} Adults
                                                    </td>
                                                    <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <template v-if="isValidOccupancy(adults, children-1, 'adult', true)">
                                                            <div class="font-medium">
                                                                Adult: 
                                                                <input
                                                                    type="number"
                                                                    v-model="priceForm.prices.base_charge[getPriceIndex(adults, children-1, 'base_charge')].adult_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-4"
                                                                    placeholder="Adult Price"
                                                                    step="0.01"
                                                                />
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            Adult: 
                                                            <span class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100 mb-4">-</span>
                                                        </template>
                                                        <template v-if="isValidOccupancy(adults, children-1, 'child', true)">
                                                            <div class="font-medium">
                                                                Child: 
                                                                <input
                                                                    type="number"
                                                                    v-model="priceForm.prices.base_charge[getPriceIndex(adults, children-1, 'base_charge')].child_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-4"
                                                                    placeholder="Child Price"
                                                                    step="0.01"
                                                                />
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            Child: 
                                                            <span class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100 mb-4">-</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                                <tr v-if="adults == 4" class="bg-gray-100">
                                                    <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</td>
                                                    <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</td>
                                                    <td v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ children-1 }} Children
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Surcharge Table -->
                            <div class="overflow-x-auto">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-bold text-gray-900">Surcharges</h3>
                                    <button 
                                        type="button"
                                        @click="applySurchargePricesToAll"
                                        class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 hover:bg-yellow-200 rounded-md transition-colors duration-200 border border-yellow-300 hover:border-yellow-400"
                                    >
                                        Apply 1st Price To All
                                    </button>
                                </div>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</th>
                                            <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ children-1 }} Children
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template v-for="config in configurations" :key="config.id">
                                            <template v-for="adults in 4" :key="`${config.id}-${adults}`">
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ config.room_type.name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ adults }} Adults
                                                    </td>
                                                    <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <template v-if="isValidOccupancy(adults, children-1, 'adult', true)">
                                                            <div class="font-medium">
                                                                Adult: 
                                                                <input
                                                                    type="number"
                                                                    v-model="priceForm.prices.sur_charge[getPriceIndex(adults, children-1, 'sur_charge')].adult_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-4"
                                                                    placeholder="Adult Price"
                                                                    step="0.01"
                                                                />
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            Adult: 
                                                            <span class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100 mb-4">-</span>
                                                        </template>
                                                        <template v-if="isValidOccupancy(adults, children-1, 'child', true)">
                                                            <div class="font-medium">
                                                                Child: 
                                                                <input
                                                                    type="number"
                                                                    v-model="priceForm.prices.sur_charge[getPriceIndex(adults, children-1, 'sur_charge')].child_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-4"
                                                                    placeholder="Child Price"
                                                                    step="0.01"
                                                                />
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            Child: 
                                                            <span class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100 mb-4">-</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                                <tr v-if="adults == 4" class="bg-gray-100">
                                                    <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</td>
                                                    <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Adults</td>
                                                    <td v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ children-1 }} Children
                                                    </td>
                                                </tr>
                                            </template>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import LoadingComponent from '@/Components/LoadingComponent.vue';

const props = defineProps({
    packageId: {
        type: Number,
        required: true
    },
    seasonTypes: {
        type: Array,
        required: true
    },
    dateTypes: {
        type: Array,
        required: true
    }
});

// State
const selectedSeason = ref('');
const selectedDateType = ref('');
const searched = ref(false);
const showPriceMatrix = ref(false);
const configurations = ref([]);
const isPriceLoading = ref(false);
const showPriceForm = ref(false);
const isEditMode = ref(false);

// Form
const priceForm = useForm({
    package_id: props.packageId,
    season_type_id: '',
    date_type_id: '',
    prices: {
        base_charge: [],
        sur_charge: []
    }
});

// Computed
const canSearch = computed(() => {
    return selectedSeason.value && selectedDateType.value;
});

const hasSurcharges = computed(() => {
    return configurations.value.some(config => 
        config.prices.some(price => price.type === 'sur_charge')
    );
});

// Methods
const fetchPrices = () => {
    isPriceLoading.value = true;
    axios.post(route('configuration-prices.fetchPricesRoomTypes'), {
        package_id: props.packageId,
        season_type_id: selectedSeason.value,
        date_type_id: selectedDateType.value
    })
    .then(response => {
        if (response.data && response.data.length > 0) {
            configurations.value = response.data;
            searched.value = true;
            showPriceMatrix.value = true;
        } else {
            searched.value = true;
            showPriceMatrix.value = false;
        }
    })
    .catch(error => {
        console.error('Error fetching prices:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to fetch price configurations',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });
    })
    .finally(() => {
        isPriceLoading.value = false;
    });
};

const getPrice = (config, adults, children, type, personType) => {
    const price = config.prices.find(p =>
        p.number_of_adults === adults &&
        p.number_of_children === children &&
        p.type === type
    );
    return price ? `MYR ${price[personType + '_price']}` : '-';
};

const resetSearch = () => {
    selectedSeason.value = '';
    selectedDateType.value = '';
    searched.value = false;
    showPriceMatrix.value = false;
    showPriceForm.value = false;
    isEditMode.value = false;
    configurations.value = [];
    priceForm.reset();
};

const getPriceIndex = (adults, children, type) => {
    return (adults - 1) * 4 + children;
};

const openPriceForm = (mode) => {
    isEditMode.value = mode === 'edit';
    
    // Reset form
    priceForm.reset();
    priceForm.package_id = props.packageId;
    priceForm.season_type_id = selectedSeason.value;
    priceForm.date_type_id = selectedDateType.value;

    // Initialize price arrays
    const combinations = Array(16).fill().map((_, index) => ({
        number_of_adults: Math.floor(index / 4) + 1,
        number_of_children: index % 4,
        adult_price: '',
        child_price: ''
    }));

    priceForm.prices = {
        base_charge: [...combinations],
        sur_charge: [...combinations]
    };

    // If editing, populate form with first room type's prices
    if (isEditMode.value && configurations.value.length > 0) {
        const firstConfig = configurations.value[0];
        firstConfig.prices.forEach(price => {
            const index = getPriceIndex(price.number_of_adults, price.number_of_children, price.type);
            const type = price.type === 'base_charge' ? 'base_charge' : 'sur_charge';
            priceForm.prices[type][index] = {
                ...priceForm.prices[type][index],
                adult_price: price.adult_price,
                child_price: price.child_price
            };
        });
    }
};

const closePriceForm = () => {
    isEditMode.value = false;
    priceForm.reset();
};

const submitPrices = () => {
    const promises = configurations.value.map(config => {
        const data = {
            package_id: props.packageId,
            season_type_id: selectedSeason.value,
            date_type_id: selectedDateType.value,
            room_type: config.room_type_id,
            prices: priceForm.prices
        };

        return isEditMode.value
            ? axios.put(route('configuration-prices.updatePrices', config.id), data)
            : axios.post(route('configuration-prices.store'), data);
    });

    Promise.all(promises)
        .then(() => {
            closePriceForm();
            fetchPrices();
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `Prices ${isEditMode.value ? 'updated' : 'created'} successfully for all room types`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4F46E5'
            });
        })
        .catch(error => {
            console.error(`Error ${isEditMode.value ? 'updating' : 'creating'} prices:`, error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || `Failed to ${isEditMode.value ? 'update' : 'create'} prices`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4F46E5'
            });
        });
};

const applyBasePricesToAll = () => {
    if (!priceForm.prices.base_charge || priceForm.prices.base_charge.length === 0) return;
    
    const firstPrice = priceForm.prices.base_charge[0];
    if (!firstPrice) return;

    priceForm.prices.base_charge = priceForm.prices.base_charge.map((_, index) => ({
        number_of_adults: Math.floor(index / 4) + 1,
        number_of_children: index % 4,
        adult_price: firstPrice.adult_price,
        child_price: firstPrice.child_price
    }));
};

const applySurchargePricesToAll = () => {
    if (!priceForm.prices.sur_charge || priceForm.prices.sur_charge.length === 0) return;
    
    const firstPrice = priceForm.prices.sur_charge[0];
    if (!firstPrice) return;

    priceForm.prices.sur_charge = priceForm.prices.sur_charge.map((_, index) => ({
        number_of_adults: Math.floor(index / 4) + 1,
        number_of_children: index % 4,
        adult_price: firstPrice.adult_price,
        child_price: firstPrice.child_price
    }));
};

const isValidOccupancy = (adults, children, type, isEditMode = false) => {
    const validCombinations = [
        { adults: 1, children: 0 },
        { adults: 1, children: 1 },
        { adults: 1, children: 2 },
        { adults: 1, children: 3 },
        { adults: 2, children: 0 },
        { adults: 2, children: 1 },
        { adults: 2, children: 2 },
        { adults: 3, children: 0 },
        { adults: 3, children: 1 },
        { adults: 4, children: 0 },
    ];

    const isValidCombo = validCombinations.some(
        combo => combo.adults === adults && combo.children === children
    );

    if (adults == 1 && children == 0 && isEditMode) return true;
    if (!isValidCombo) return false;
    if (type == 'child' && children == 0) return false;

    return true;
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
