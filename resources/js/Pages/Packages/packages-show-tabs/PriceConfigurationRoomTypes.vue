<template>
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                    <select
                        id="season"
                        v-model="selectedSeason"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Select Season</option>
                        <option v-for="season in assignedSeasonTypes" :key="season.id" :value="season.id">
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
                        <option v-for="type in assignedDateTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="roomType" class="block text-sm font-medium text-gray-700">Room Type (Optional)</label>
                    <select
                        id="roomType"
                        v-model="selectedRoomType"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">All Room Types</option>
                        <option v-for="(name, id) in packageUniqueRoomTypes" :key="id" :value="id">
                            {{ name }}
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

            <div v-else-if="!showPriceMatrix" class="text-center text-gray-500">
                No price configurations found for the selected filters.
                
                <!-- show a button to add a new price configuration -->
                <button
                    @click="createPriceConfigurationWithApi"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm block text-center mt-4 mx-auto"
                >
                    Create Configuration
                </button>
            </div>

            <div v-else class="space-y-8">
                <!-- Price Matrix for all Room Types -->

                <!-- Display View -->
                <template v-if="!isEditMode">
                    <!-- Base Charge Table -->
                    <div class="overflow-x-auto">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-bold text-gray-700 mb-2">Base Charge</h4>
                                <small class="text-gray-500 mb-4 block">
                                    Note: Base Charges are lump sum (not per night). Season and Date Type follow the first night’s rate.
                                </small>
                            </div>
                            <div class="space-x-2 mb-4">
                                <button
                                    v-if="!isEditMode"
                                    @click="openPriceForm('edit')"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                                >
                                    Edit All Prices
                                </button>
                            </div>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="config in configurations" :key="config.id">
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adult Price</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Child Price</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infant Price</th>
                                    </tr>
                                    <template v-for="combination in VALID_OCCUPANCY_COMBINATIONS" :key="`${config.id}-${combination.adults}-${combination.children}-${combination.infants}`">
                                        <tr v-if="isValidOccupancy(combination.adults, combination.children, combination.infants, 'adult')" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ config.room_type.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ combination.adults }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ combination.children }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ combination.infants }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ getPrice(config, combination.adults, combination.children, 'base_charge', 'adult', combination.infants) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <span v-if="combination.children === 0"> - </span>
                                                <span v-else>
                                                    {{ getPrice(config, combination.adults, combination.children, 'base_charge', 'child', combination.infants) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <span v-if="combination.infants === 0"> - </span>
                                                <span v-else>
                                                    {{ getPrice(config, combination.adults, combination.children, 'base_charge', 'infant', combination.infants) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Surcharge Table -->
                    <div class="overflow-x-auto mt-8">
                        <h4 class="text-lg font-bold text-gray-700 mb-2">Surcharge</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="config in configurations" :key="config.id">
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adult Price</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Child Price</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infant Price</th>
                                    </tr>
                                    <template v-for="combination in VALID_OCCUPANCY_COMBINATIONS" :key="`${config.id}-${combination.adults}-${combination.children}-${combination.infants}`">
                                        <tr v-if="isValidOccupancy(combination.adults, combination.children, combination.infants, 'adult')" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ config.room_type.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ combination.adults }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ combination.children }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ combination.infants }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ getPrice(config, combination.adults, combination.children, 'sur_charge', 'adult', combination.infants) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <span v-if="combination.children === 0"> - </span>
                                                <span v-else>
                                                    {{ getPrice(config, combination.adults, combination.children, 'sur_charge', 'child', combination.infants) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <span v-if="combination.infants === 0"> - </span>
                                                <span v-else>
                                                    {{ getPrice(config, combination.adults, combination.children, 'sur_charge', 'infant', combination.infants) }}
                                                </span>
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
                        <!-- Save/Cancel Buttons -->
                        <div class="flex justify-end space-x-2 mb-6">
                            <button
                                type="button"
                                @click="togglePriceConfigByPaxForm"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm"
                            >
                                Edit Price By Pax
                            </button>
                            <button
                                type="button"
                                @click="closePriceForm"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                                :disabled="priceForm.processing"
                            >
                                {{ priceForm.processing ? 'Saving...' : 'Save All Prices' }}
                            </button>
                        </div>
                        
                        <div class="space-y-8">
                            <!-- Base Charge Table -->
                            <div class="overflow-x-auto">
                                <div class="flex justify-between items-center mb-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">Base Charges</h3>
                                        <small class="text-gray-500 block mt-2 mb-2">
                                            Note: Base Charges are lump sum (not per night). Season and Date Type follow the first night's rate.
                                        </small>
                                    </div>
                                    <div class="flex flex-1 gap-2 items-center justify-end mr-4">
                                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-40" placeholder="Adult Price"  v-model="applyAllBasePrice.adult_price" />
                                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-40" placeholder="Child Price"  v-model="applyAllBasePrice.child_price" /> 
                                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-40" placeholder="Infant Price"  v-model="applyAllBasePrice.infant_price" />
                                    </div>
                                    <button  
                                        type="button"
                                        @click="applyBasePricesToAll"
                                        class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 hover:bg-yellow-200 rounded-md transition-colors duration-200 border border-yellow-300 hover:border-yellow-400"
                                    >
                                        Apply Prices To All
                                    </button>
                                </div>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template v-for="config in configurations" :key="config.id">
                                            <tr class="bg-gray-100">
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adult Price</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Child Price</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infant Price</th>
                                            </tr>
                                            <template v-for="combination in VALID_OCCUPANCY_COMBINATIONS" :key="`${config.id}-${combination.adults}-${combination.children}-${combination.infants}`">
                                                <tr v-if="isValidOccupancy(combination.adults, combination.children, combination.infants, 'adult', true)">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ config.room_type.name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ combination.adults }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ combination.children }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ combination.infants }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <input
                                                            type="number"
                                                            v-model="priceForm.prices[config.room_type_id].base_charge[getPriceIndex(combination.adults, combination.children, combination.infants, 'base_charge')].adult_price"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="Adult Price"
                                                            step="0.01"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      
                                                        <span v-if="combination.children === 0"> - </span>
                                                        <input
                                                            v-else
                                                            type="number"
                                                            v-model="priceForm.prices[config.room_type_id].base_charge[getPriceIndex(combination.adults, combination.children, combination.infants, 'base_charge')].child_price"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="Child Price"
                                                            step="0.01"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <span v-if="combination.infants === 0"> - </span>
                                                        <input
                                                            v-else
                                                            type="number"
                                                            v-model="priceForm.prices[config.room_type_id].base_charge[getPriceIndex(combination.adults, combination.children, combination.infants, 'base_charge')].infant_price"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="Infant Price"
                                                            step="0.01"
                                                        />
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
                                    <div class="flex flex-1 gap-2 items-center justify-end mr-4">
                                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-40" placeholder="Adult Price"  v-model="applyAllSurcharge.adult_price" />
                                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-40" placeholder="Child Price"  v-model="applyAllSurcharge.child_price" /> 
                                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm max-w-40" placeholder="Infant Price"  v-model="applyAllSurcharge.infant_price" />
                                    </div>
                                    <button 
                                        type="button"
                                        @click="applySurchargePricesToAll"
                                        class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 hover:bg-yellow-200 rounded-md transition-colors duration-200 border border-yellow-300 hover:border-yellow-400"
                                    >
                                        Apply Prices To All
                                    </button>
                                </div>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template v-for="config in configurations" :key="config.id">
                                            <tr class="bg-gray-100">
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adult Price</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Child Price</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Infant Price</th>
                                            </tr>
                                            <template v-for="combination in VALID_OCCUPANCY_COMBINATIONS" :key="`${config.id}-${combination.adults}-${combination.children}-${combination.infants}`">
                                                <tr v-if="isValidOccupancy(combination.adults, combination.children, combination.infants, 'adult', true)">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ config.room_type.name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ combination.adults }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ combination.children }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ combination.infants }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <input
                                                            type="number"
                                                            v-model="priceForm.prices[config.room_type_id].sur_charge[getPriceIndex(combination.adults, combination.children, combination.infants, 'sur_charge')].adult_price"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="Adult Price"
                                                            step="0.01"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <span v-if="combination.children === 0"> - </span>
                                                        <input
                                                            v-else
                                                            type="number"
                                                            v-model="priceForm.prices[config.room_type_id].sur_charge[getPriceIndex(combination.adults, combination.children, combination.infants, 'sur_charge')].child_price"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="Child Price"
                                                            step="0.01"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <span v-if="combination.infants === 0"> - </span>
                                                        <input
                                                            type="number"
                                                            v-model="priceForm.prices[config.room_type_id].sur_charge[getPriceIndex(combination.adults, combination.children, combination.infants, 'sur_charge')].infant_price"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                            placeholder="Infant Price"
                                                            step="0.01"
                                                        />
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

        <Modal :show="showPriceConfigByPaxForm" @close="showPriceConfigByPaxForm = false">
            <div class="max-w-2xl bg-white space-y-8 p-4">
                <h3 class="text-md font-medium text-gray-900">Update Price Config by Pax</h3>

                <div class="space-y-6">
                    <!-- Selector to choose pax type -->
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Select Pax Type</label>
                        <select v-model="selectedPaxType" class="w-full rounded-lg border-gray-300 px-3 py-2">
                            <option value="adult">Adult</option>
                            <option value="child">Child</option>
                            <option value="infant">Infant</option>
                        </select>
                    </div>

                    <!-- Select pax count -->
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Number of Pax</label>
                        <select v-model="paxPriceForm[selectedPaxType]" class="w-full rounded-lg border-gray-300 px-3 py-2">
                            <option v-for="n in paxRange[selectedPaxType]" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <!-- Amounts -->
                    <div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Base Charge (RM)</label>
                            <input
                                v-model.number="paxPriceForm[selectedPaxType + '_base_charge']"
                                type="number"
                                class="w-full rounded-lg border-gray-300 px-3 py-2"
                                placeholder="e.g. 120"
                            />
                        </div>
                    </div>

                    <div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Surcharge (RM)</label>
                            <input
                                v-model.number="paxPriceForm[selectedPaxType + '_surcharge']"
                                type="number"
                                class="w-full rounded-lg border-gray-300 px-3 py-2"
                                placeholder="e.g. 50"
                            />
                        </div>
                    </div>
                </div>


                <div class="flex justify-end space-x-2">
                    <button @click="togglePriceConfigByPaxForm" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs">
                        Cancel
                    </button>
                    <button @click="submitPriceConfigByPax" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs">
                        Update
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, watch, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import LoadingComponent from '@/Components/LoadingComponent.vue';
import Modal from '@/Components/Modal.vue';

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
    },
    packageUniqueRoomTypes: {
        type: Object,
        required: true
    },
    allSeasonTypes: {
        type: Array,
        required: true
    },
    allDateTypes: {
        type: Array,
        required: true
    },
    assignedSeasonTypes: {
        type: Array,
        required: true
    },
    assignedDateTypes: {
        type: Array,
        required: true
    }
});

// State
const selectedSeason = ref('');
const selectedDateType = ref('');
const selectedRoomType = ref('');
const searched = ref(false);
const showPriceMatrix = ref(false);
const configurations = ref([]);
const isPriceLoading = ref(false);
const showPriceForm = ref(false);
const isEditMode = ref(false);
const applyAllBasePrice = ref({
    adult_price: '',
    child_price: '',
    infant_price: ''
});
const applyAllSurcharge = ref({
    adult_price: '',
    child_price: '',
    infant_price: ''
});
const showPriceConfigByPaxForm = ref(false);

// Form
const priceForm = useForm({
    package_id: props.packageId,
    season_type_id: '',
    date_type_id: '',
    prices: {} // Will store as { room_type_id: { base_charge: [], sur_charge: [] } }
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
        date_type_id: selectedDateType.value,
        room_type_id: selectedRoomType.value || undefined
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

const getPrice = (config, adults, children, type, personType, infants = 0) => {
    const price = config.prices.find(p =>
        p.number_of_adults === adults &&
        p.number_of_children === children &&
        p.number_of_infants === infants &&
        p.type === type
    );
    return price ? `MYR ${price[personType + '_price']}` : '-';
};

const resetSearch = () => {
    selectedSeason.value = '';
    selectedDateType.value = '';
    selectedRoomType.value = '';
    searched.value = false;
    showPriceMatrix.value = false;
    showPriceForm.value = false;
    isEditMode.value = false;
    configurations.value = [];
    priceForm.reset();
};

const VALID_OCCUPANCY_COMBINATIONS = [
    { adults: 1, children: 0, infants: 0 },
    { adults: 1, children: 0, infants: 1 },
    { adults: 1, children: 0, infants: 2 },
    { adults: 1, children: 0, infants: 3 },
    { adults: 1, children: 1, infants: 0 },
    { adults: 1, children: 1, infants: 1 },
    { adults: 1, children: 1, infants: 2 },
    { adults: 1, children: 2, infants: 0 },
    { adults: 1, children: 2, infants: 1 },
    { adults: 1, children: 3, infants: 0 },
    { adults: 2, children: 0, infants: 0 },
    { adults: 2, children: 0, infants: 1 },
    { adults: 2, children: 0, infants: 2 },
    { adults: 2, children: 1, infants: 0 },
    { adults: 2, children: 1, infants: 1 },
    { adults: 2, children: 2, infants: 0 },
    { adults: 2, children: 0, infants: 2 },
    { adults: 3, children: 0, infants: 0 },
    { adults: 3, children: 0, infants: 1 },
    { adults: 3, children: 1, infants: 0 },
    { adults: 4, children: 0, infants: 0 }
];

const adultOptions = computed(() => {
  const unique = new Set(VALID_OCCUPANCY_COMBINATIONS.map(c => c.adults))
  return [...unique].sort((a, b) => a - b)
})
const childOptions = ref([])
const infantOptions = ref([])

const isValidOccupancy = (adults, children, infants, type, isEditMode = false) => {
    // Check if total occupancy exceeds 4
    if (adults + children + infants > 4) return false;

    const isValidCombo = VALID_OCCUPANCY_COMBINATIONS.some(
        combo => combo.adults === adults && combo.children === children && combo.infants === infants
    );

    if (adults === 1 && children === 0 && infants === 0 && isEditMode) return true;
    if (!isValidCombo) return false;
    if (type === 'child' && children === 0) return false;
    if (type === 'infant' && infants === 0) return false;

    return true;
};

const getPriceIndex = (adults, children, infants, type) => {
    return VALID_OCCUPANCY_COMBINATIONS.findIndex(
        combo => combo.adults === adults && combo.children === children && combo.infants === infants
    );
};

const openPriceForm = (mode) => {
    isEditMode.value = mode === 'edit';
    
    // Reset form
    priceForm.reset();
    priceForm.package_id = props.packageId;
    priceForm.season_type_id = selectedSeason.value;
    priceForm.date_type_id = selectedDateType.value;

    // Initialize price structure for each room type
    configurations.value.forEach(config => {
        const roomTypeId = config.room_type_id;
        
        // Initialize the room type structure with all valid combinations
        priceForm.prices[roomTypeId] = {
            base_charge: VALID_OCCUPANCY_COMBINATIONS.map(combo => ({
                number_of_adults: combo.adults,
                number_of_children: combo.children,
                number_of_infants: combo.infants,
                adult_price: '',
                child_price: '',
                infant_price: ''
            })),
            sur_charge: VALID_OCCUPANCY_COMBINATIONS.map(combo => ({
                number_of_adults: combo.adults,
                number_of_children: combo.children,
                number_of_infants: combo.infants,
                adult_price: '',
                child_price: '',
                infant_price: ''
            }))
        };

        // If editing, populate form with existing prices for this room type
        if (isEditMode.value) {
            config.prices.forEach(price => {
                const index = getPriceIndex(price.number_of_adults, price.number_of_children, price.number_of_infants, price.type);
                if (index !== -1) {
                    const type = price.type === 'base_charge' ? 'base_charge' : 'sur_charge';
                    priceForm.prices[roomTypeId][type][index] = {
                        number_of_adults: price.number_of_adults,
                        number_of_children: price.number_of_children,
                        number_of_infants: price.number_of_infants,
                        adult_price: price.adult_price,
                        child_price: price.child_price,
                        infant_price: price.infant_price || '' // Handle cases where infant_price might be null
                    };
                }
            });
        }
    });
};

const closePriceForm = () => {
    isEditMode.value = false;
    priceForm.reset();
};

const submitPrices = () => {
    // Prepare data for all room types in a single object
    const allPrices = {};
    configurations.value.forEach(config => {
        const roomTypeId = config.room_type_id;
        allPrices[roomTypeId] = {
            base_charge: priceForm.prices[roomTypeId].base_charge.map(price => ({
                number_of_adults: price.number_of_adults,
                number_of_children: price.number_of_children,
                number_of_infants: price.number_of_infants,
                adult_price: price.adult_price,
                child_price: price.child_price,
                infant_price: price.infant_price || 0
            })),
            sur_charge: priceForm.prices[roomTypeId].sur_charge.map(price => ({
                number_of_adults: price.number_of_adults,
                number_of_children: price.number_of_children,
                number_of_infants: price.number_of_infants,
                adult_price: price.adult_price,
                child_price: price.child_price,
                infant_price: price.infant_price || 0
            }))
        };
    });

    const data = {
        package_id: props.packageId,
        season_type_id: selectedSeason.value,
        date_type_id: selectedDateType.value,
        prices: allPrices
    };

    // Make a single API call with all room types' data
    const request = isEditMode.value
        ? axios.put(route('configuration-prices.updateRoomTypePrices', configurations.value[0].id), data)
        : axios.post(route('configuration-prices.store'), data);

    request
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
    if (!configurations.value.length) return;
    
    const firstPrice = applyAllBasePrice.value;
    if (!firstPrice) return;

    configurations.value.forEach(config => {
        const roomTypeId = config.room_type_id;
        priceForm.prices[roomTypeId].base_charge = VALID_OCCUPANCY_COMBINATIONS.map(combo => ({
            number_of_adults: combo.adults,
            number_of_children: combo.children,
            number_of_infants: combo.infants,
            adult_price: firstPrice.adult_price,
            child_price: firstPrice.child_price,
            infant_price: firstPrice.infant_price
        }));
    });
};

const applySurchargePricesToAll = () => {
    if (!configurations.value.length) return;
    
    const firstPrice = applyAllSurcharge.value;
    if (!firstPrice) return;

    configurations.value.forEach(config => {
        const roomTypeId = config.room_type_id;
        priceForm.prices[roomTypeId].sur_charge = VALID_OCCUPANCY_COMBINATIONS.map(combo => ({
            number_of_adults: combo.adults,
            number_of_children: combo.children,
            number_of_infants: combo.infants,
            adult_price: firstPrice.adult_price,
            child_price: firstPrice.child_price,
            infant_price: firstPrice.infant_price
        }));
    });
};

const createPriceConfigurationWithApi = () => {
    axios.post(route('configuration-prices.createPriceConfiguration'), {
        package_id: props.packageId,
        season_type_id: selectedSeason.value,
        date_type_id: selectedDateType.value,
        room_type_id: selectedRoomType.value
    })
    .then(response => {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Price configuration created successfully',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });

        fetchPrices();
    })
    .catch(error => {
        console.error('Error creating price configuration:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to create price configuration',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });
    });
};

const togglePriceConfigByPaxForm = () => {
    showPriceConfigByPaxForm.value = !showPriceConfigByPaxForm.value;
}

const selectedPaxType = ref('adult');
const paxPriceForm = reactive({
  adult: 1,
  child: 1,
  infant: 1,
  adult_base_charge: null,
  child_base_charge: null,
  infant_base_charge: null,
  adult_surcharge: null,
  child_surcharge: null,
  infant_surcharge: null,
});
const paxRange = {
  adult: 4,
  child: 3,
  infant: 3,
};

const submitPriceConfigByPax = () => {
    const {
        adult,
        child,
        infant,
        adult_base_charge,
        child_base_charge,
        infant_base_charge,
        adult_surcharge,
        child_surcharge,
        infant_surcharge
    } = paxPriceForm;

    const paxCount = paxPriceForm[selectedPaxType.value]; // number of pax
    const basePrice = paxPriceForm[`${selectedPaxType.value}_base_charge`];
    const surPrice = paxPriceForm[`${selectedPaxType.value}_surcharge`];

    configurations.value.forEach(config => {
        const roomTypeId = config.room_type_id;

        if (basePrice !== null) {
            priceForm.prices[roomTypeId].base_charge.forEach(item => {
                console.log(selectedPaxType.value, paxCount);
                if (selectedPaxType.value === 'adult' && item.number_of_adults === paxCount) {
                    item.adult_price = basePrice
                } else if (selectedPaxType.value === 'child' && item.number_of_children === paxCount) {
                    item.child_price = basePrice;
                } else if (selectedPaxType.value === 'infant' && item.number_of_infants === paxCount) {
                    item.infant_price = basePrice;
                }
            });
        }

        if (surPrice !== null) {
            priceForm.prices[roomTypeId].sur_charge.forEach(item => {
                if (selectedPaxType.value === 'adult' && item.number_of_adults === paxCount) {
                    item.adult_price = surPrice;
                } else if (selectedPaxType.value === 'child' && item.number_of_children === paxCount) {
                    item.child_price = surPrice;
                } else if (selectedPaxType.value === 'infant' && item.number_of_infants === paxCount) {
                    item.infant_price = infant_surcharge;
                }
            });
        }
    });

    showPriceConfigByPaxForm.value = false;
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
