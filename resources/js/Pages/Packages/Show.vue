<template>
    <Head title="Package Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Package Details
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-6" />

                        <!-- Tabs -->
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        activeTab === tab.id
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm'
                                    ]"
                                >
                                    {{ tab.name }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="mt-6 border p-4 rounded">
                            <!-- Package Details Tab -->
                            <div v-if="activeTab === 'details'" class="space-y-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-md font-medium text-gray-900">Package Details</h3>
                                        <Link
                                            :href="route('packages.edit', pkg.id)"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                        >
                                            Edit Package
                                        </Link>
                                    </div>
                                    <div class="flex items-start">
                                        <!-- <div v-if="pkg.icon_photo" class="flex-shrink-0">
                                            <img :src="`/storage/${pkg.icon_photo}`" :alt="pkg.name" class="h-32 w-32 rounded-full">
                                        </div> -->
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ pkg.name }}</p>
                                        </div>
                                        <div>
                                            <h4 class="mt-4 text-sm font-medium text-gray-500">Location</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ pkg.location }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ pkg.description }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Display Prices</h4>
                                            <div class="mt-1">
                                                <p class="text-sm text-gray-900">MYR {{ formatNumber(pkg.display_price_adult) }}</p>
                                                <!-- <p class="text-sm text-gray-900">Adult: ${{ pkg.display_price_adult }}</p> -->
                                                <!-- <p class="text-sm text-gray-900">Child: ${{ pkg.display_price_child }}</p> -->
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Duration</h4>
                                            <!-- <div class="mt-1">
                                                <p class="text-sm text-gray-900">Minimum Days: {{ pkg.package_min_days }}</p>
                                                <p class="text-sm text-gray-900">Maximum Days: {{ pkg.package_max_days }}</p>
                                            </div> -->
                                            <p class="mt-1 text-sm text-gray-900">
                                                {{ pkg.package_max_days +1 }} Days {{ pkg.package_max_days }} {{pkg.package_max_days > 1 ? 'Nights' : 'Night'}}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Terms and Conditions</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ pkg.terms_and_conditions }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Package Date</h4>
                                            <p class="mt-1 text-sm text-gray-900">
                                                {{ moment(pkg.package_start_date).format('DD/MM/YYYY') }} - {{ moment(pkg.package_end_date).format('DD/MM/YYYY') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.created_at).format('DD-MM-YYYY HH:mm:ss A')}}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.updated_at).format('DD-MM-YYYY HH:mm:ss A')}}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Link</h4>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <a
                                                :href="route('quotation.with-hash', pkg.uuid)"
                                                target="_blank"
                                                class="text-sm text-indigo-600 hover:text-indigo-900 break-all"
                                            >
                                                {{ route('quotation.with-hash', pkg.uuid) }}
                                            </a>
                                            <button
                                                @click="copyLink(route('quotation.with-hash', pkg.uuid))"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Images</h4>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                            <div class="space-y-1">
                                                <div v-if="pkg.images && pkg.images.length > 0" class="grid grid-cols-2 gap-4 mb-4">
                                                    <div v-for="(image, index) in pkg.images" :key="index">
                                                        <img 
                                                            :src="getImageUrl(image)" 
                                                            class="h-16 w-16 object-cover rounded-lg cursor-pointer" 
                                                            @click="showImageModal(image)"
                                                            alt="Room type image"
                                                        />
                                                    </div>
                                                </div>
                                                <div v-else class="text-gray-400 text-sm text-center">No images</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Types Tab -->
                            <div v-if="activeTab === 'room-types'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Room Types</h3>
                                    <button
                                        @click="showAddRoomTypeModal = true"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Room Type
                                    </button>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Occupancy</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="roomType in roomTypesData.data" :key="roomType.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div v-if="roomType.images && roomType.images.length > 0" class="flex space-x-2">
                                                        <div v-for="(image, index) in roomType.images" :key="index" class="relative">
                                                            <img 
                                                                :src="getImageUrl(image)" 
                                                                class="h-16 w-16 object-cover rounded-lg cursor-pointer" 
                                                                @click="showImageModal(image)"
                                                                alt="Room type image"
                                                            />
                                                        </div>
                                                    </div>
                                                    <span v-else class="text-gray-400 text-sm">No images</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_occupancy }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">{{ roomType.description }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        @click="editRoomType(roomType)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="deleteRoomType(roomType.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="roomTypesData.data.length === 0">
                                                <td colspan="5" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No room types found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4">
                                    <Pagination
                                        :links="roomTypesPagination.links"
                                        :from="roomTypesPagination.from"
                                        :to="roomTypesPagination.to"
                                        :total="roomTypesPagination.total"
                                        :component-loading="true"
                                        @page-change="handlePageChange"
                                        :key="loadingKey"
                                    />
                                </div>
                            </div>

                            <!-- Season Types Tab -->
                            <div v-if="activeTab === 'season-types'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Season Dates</h3>
                                    <button
                                        @click="showAddSeasonModal = true"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Season
                                    </button>
                                </div>

                                <!-- Seasons Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Season Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="season in seasonsData.data" :key="season.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ season.type.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(season.start_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(season.end_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        @click="editSeason(season)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="deleteSeason(season.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="seasonsData.data.length === 0">
                                                <td colspan="4" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No seasons found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Seasons Pagination -->
                                <div class="mt-4">
                                    <Pagination
                                        :links="seasonsPagination.links"
                                        :from="seasonsPagination.from"
                                        :to="seasonsPagination.to"
                                        :total="seasonsPagination.total"
                                        :component-loading="true"
                                        @page-change="handleSeasonPageChange"
                                        :key="loadingKey"
                                    />
                                </div>
                            </div>

                            <!-- Date Types Tab -->
                            <div v-if="activeTab === 'date-types-ranges'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Date Type Ranges</h3>
                                    <div class="space-x-3">
                                        <button
                                            @click="showAddDateTypeRangeModal = true"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                        >
                                            Add Date Range
                                        </button>
                                    </div>
                                </div>

                                <!-- Date Type Ranges Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="range in dateTypeRangesData.data" :key="range.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ range.date_type.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(range.start_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(range.end_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        @click="editDateTypeRange(range)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="deleteDateTypeRange(range.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="dateTypeRangesData.data.length === 0">
                                                <td colspan="4" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No date type ranges found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Date Type Ranges Pagination -->
                                <div class="mt-4">
                                    <Pagination
                                        :links="dateTypeRangesPagination.links"
                                        :from="dateTypeRangesPagination.from"
                                        :to="dateTypeRangesPagination.to"
                                        :total="dateTypeRangesPagination.total"
                                        :component-loading="true"
                                        @page-change="handleDateTypeRangePageChange"
                                        :key="loadingKey"
                                    />
                                </div>
                            </div>

                            <!-- Price Configuration Tab -->
                            <div v-if="activeTab === 'price-configuration'" class="space-y-6">
                                <!-- <div class="flex justify-between items-center" v-if="!showPriceForm && !isEditMode">
                                    <h3 class="text-md font-medium text-gray-900">Price Configuration</h3>
                                </div> -->

                                <!-- Selection Form and Price Matrix -->
                                <div v-if="!showPriceForm">
                                    <!-- Selection Form -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                                        <div>
                                            <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                                            <select
                                                id="season"
                                                v-model="selectedSeason"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">Select Season</option>
                                                <option v-for="season in priceConfigSeasonChoice" :key="season.id" :value="season.id">
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
                                                <option v-for="type in priceConfigDateTypeChoice" :key="type.id" :value="type.id">
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
                                                <option v-for="type in roomTypesData.data" :key="type.id" :value="type.id">
                                                    {{ type.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Search Button -->
                                    <div class="flex mb-8 space-x-2">
                                        <button
                                            @click="fetchPrices"
                                            :disabled="!canSearch"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            Search Prices
                                        </button>
                                        <button
                                            @click="resetSearch"
                                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                                        >
                                            Reset
                                        </button>
                                    </div>

                                    <transition name="fade" v-if="isPriceLoading">
                                        <div class="flex justify-center items-center h-full min-h-[400px]">
                                            <LoadingComponent />
                                        </div>
                                    </transition>
                                    <!-- Price Matrix Tables -->
                                    <div v-else-if="showPriceMatrix" class="space-y-8">
                                        <!-- Base Charge Table -->
                                        <div class="overflow-x-auto">
                                            <h3 class="text-md font-medium text-gray-900 mb-4">Base Charges</h3>
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                        <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ children-1 }} Children
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="adults in 4" :key="adults">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {{ adults }} Adults
                                                        </td>
                                                        <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                            <span v-if="!isValidOccupancy(adults, children-1, 'adult')">Adult: -</span>
                                                            <span v-else>Adult: {{ getPrice(adults, children-1, 'base_charge', 'adult') }}</span>
                                                            <br>
                                                            <span v-if="!isValidOccupancy(adults, children-1, 'child')">Child: -</span>
                                                            <span v-else>Child: {{ getPrice(adults, children-1, 'base_charge', 'child') }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Surcharge Table -->
                                        <div class="overflow-x-auto">
                                            <h3 class="text-md font-medium text-gray-900 mb-4">Surcharges</h3>
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                        <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ children-1 }} Children
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="adults in 4" :key="adults">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {{ adults }} Adults
                                                        </td>
                                                        <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                            <span v-if="!isValidOccupancy(adults, children-1, 'adult')">Adult: -</span>
                                                            <span v-else>Adult: {{ getPrice(adults, children-1, 'sur_charge', 'adult') }}</span>
                                                            <br>
                                                            <span v-if="!isValidOccupancy(adults, children-1, 'child')">Child: -</span>
                                                            <span v-else>Child: {{ getPrice(adults, children-1, 'sur_charge', 'child') }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Edit Button -->
                                        <div class="mt-4 flex justify-end">
                                            <button
                                                @click="openPriceForm('edit')"
                                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                            >
                                                Edit Prices
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else-if="searched" class="text-center py-8 text-gray-600 border b-a border-solid border-gray-600 rounded-md">
                                        <p class="mb-4">No prices found for the selected criteria.</p>
                                        <button
                                            @click="openPriceForm('create')"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm"
                                        >
                                            Create Now
                                        </button>
                                    </div>
                                    <div v-else class="text-center py-8 text-gray-500 bg-gray-50 rounded-md">
                                        Please select all options and click search to view the price matrix.
                                    </div>
                                </div>

                                <!-- Price Configuration Form -->
                                <div v-if="showPriceForm" class="space-y-6">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-lg font-medium text-gray-900">{{ isEditMode ? 'Edit Prices' : 'Create Prices' }}</h3>
                                    </div>
                                    <!-- View only selected season, date type and room type -->
                                    <div v-if="isEditMode">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                            <div>
                                                <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                                                <select
                                                    id="season"
                                                    v-model="selectedSeason"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50"
                                                    disabled
                                                >
                                                    <option value="">Select Season</option>
                                                    <option v-for="season in priceConfigSeasonChoice" :key="season.id" :value="season.id">
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
                                                    disabled
                                                >
                                                    <option value="">Select Date Type</option>
                                                    <option v-for="type in priceConfigDateTypeChoice" :key="type.id" :value="type.id">
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
                                                    disabled
                                                >
                                                    <option value="">Select Room Type</option>
                                                    <option v-for="type in roomTypesData.data" :key="type.id" :value="type.id">
                                                        {{ type.name }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <form @submit.prevent="submitPrices">
                                        <div class="space-y-8">
                                            <!-- Base Charge Table -->
                                            <div class="overflow-x-auto">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h3 class="text-md font-medium text-gray-900">Base Charges</h3>
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
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                            <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                {{ children-1 }} Children
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <tr v-for="adults in 4" :key="adults">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                {{ adults }} Adults
                                                            </td>
                                                            <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                                <span class="text-xs text-gray-500">Adults:</span>
                                                                <span v-if="!isValidOccupancy(adults, children-1, 'adult', true)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100">-</span>
                                                                <input
                                                                    v-else
                                                                    type="number"
                                                                    v-model="priceForm.prices.base_charge[getPriceIndex(adults, children-1, 'base_charge')].adult_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Adult Price"
                                                                    step="0.01"
                                                                />
                                                                <div class="text-xs text-gray-500 mt-2 block">Child:</div>
                                                                <span v-if="!isValidOccupancy(adults, children-1, 'child', true)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100">-</span>
                                                                <input
                                                                    v-else
                                                                    type="number"
                                                                    v-model="priceForm.prices.base_charge[getPriceIndex(adults, children-1, 'base_charge')].child_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    placeholder="Child Price"
                                                                    step="0.01"
                                                                />
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Surcharge Table -->
                                            <div class="overflow-x-auto">
                                                <div class="flex justify-between items-center mb-4">
                                                    <h3 class="text-md font-medium text-gray-900">Surcharges</h3>
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
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                            <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                {{ children-1 }} Children
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <tr v-for="adults in 4" :key="adults">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                {{ adults }} Adults
                                                            </td>
                                                            <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                <div class="text-xs text-gray-500 mt-4 block">Adults:</div>
                                                                <span v-if="!isValidOccupancy(adults, children-1, 'adult', true)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100">-</span>
                                                                <input
                                                                    v-else
                                                                    type="number"
                                                                    v-model="priceForm.prices.sur_charge[getPriceIndex(adults, children-1, 'sur_charge')].adult_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    step="0.01"
                                                                    placeholder="Adult Price"
                                                                />
                                                                <div class="text-xs text-gray-500 mt-2 block">Child:</div>
                                                                <span v-if="!isValidOccupancy(adults, children-1, 'child', true)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border border-solid bg-gray-100">-</span>
                                                                <input
                                                                    v-else
                                                                    type="number"
                                                                    v-model="priceForm.prices.sur_charge[getPriceIndex(adults, children-1, 'sur_charge')].child_price"
                                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                    step="0.01"
                                                                    placeholder="Child Price"
                                                                />
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex justify-end space-x-3">
                                            <button
                                                type="button"
                                                @click="closePriceForm"
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                                :disabled="priceForm.processing"
                                            >
                                                {{ isEditMode ? 'Update Prices' : 'Create Prices' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Price Configuration Room Types Tab -->
                            <div v-if="activeTab === 'price-configuration-room-types'" class="space-y-6">
                                <PriceConfigurationRoomTypes
                                    :package-id="pkg.id"
                                    :season-types="priceConfigSeasonChoice"
                                    :date-types="priceConfigDateTypeChoice"
                                />
                            </div>

                            <!-- Date Blockers Tab -->
                            <div v-if="activeTab === 'date-blockers'" class="space-y-6">
                                <DateBlockers
                                    :package-id="pkg.id"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Room Type Modal -->
        <Modal :show="showAddRoomTypeModal" @close="() => {
            showAddRoomTypeModal = false;
            roomTypeForm.reset();
            addRoomTypeErrors.value = '';
        }">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Room Type</h2>
                <div v-if="addRoomTypeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addRoomTypeErrors }}
                </div>
                <form @submit.prevent="submitRoomType">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="name"
                                v-model="roomTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="max_occupancy" class="block text-sm font-medium text-gray-700">Max Occupancy</label>
                            <input
                                type="number"
                                id="max_occupancy"
                                v-model="roomTypeForm.max_occupancy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="description"
                                v-model="roomTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Images</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload images</span>
                                            <input
                                                id="images"
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageUpload($event, 'roomTypeForm')"
                                            />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB each</p>
                                </div>
                            </div>
                            <!-- Preview uploaded images -->
                            <div v-if="roomTypeForm.images.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <div v-for="(image, index) in roomTypeForm.images" :key="index" class="relative">
                                    <img 
                                        :src="getImagePreviewUrl(image)" 
                                        class="h-24 w-full object-cover rounded-lg" 
                                        alt="Room type image"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index, 'roomTypeForm')"
                                        class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="() => {
                                showAddRoomTypeModal = false;
                                roomTypeForm.reset();
                                addRoomTypeErrors.value = '';
                            }"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="roomTypeForm.processing"
                        >
                            Create Room Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Room Type Modal -->
        <Modal :show="showEditRoomTypeModal" @close="closeEditRoomTypeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Room Type</h2>
                <div v-if="roomTypeWarning" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ roomTypeWarning }}
                </div>
                <form @submit.prevent="updateRoomType">
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="edit_name"
                                v-model="editRoomTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="edit_max_occupancy" class="block text-sm font-medium text-gray-700">Max Occupancy</label>
                            <input
                                type="number"
                                id="edit_max_occupancy"
                                v-model="editRoomTypeForm.max_occupancy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                        </div>

                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="edit_description"
                                v-model="editRoomTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Images</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="edit_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload images</span>
                                            <input
                                                id="edit_images"
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageUpload($event, 'editRoomTypeForm')"
                                            />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB each</p>
                                </div>
                            </div>
                            <!-- Preview uploaded images -->
                            <div v-if="editRoomTypeForm.images.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <div v-for="(image, index) in editRoomTypeForm.images" :key="index" class="relative">
                                    <img 
                                        :src="getImagePreviewUrl(image)" 
                                        class="h-24 w-full object-cover rounded-lg" 
                                        alt="Room type image"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index, 'editRoomTypeForm')"
                                        class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeEditRoomTypeModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="editRoomTypeForm.processing"
                        >
                            Update Room Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Season Modal -->
        <Modal :show="showEditSeasonModal" @close="showEditSeasonModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Season</h2>
                <form @submit.prevent="updateSeason">
                    <div class="space-y-4">
                        <!-- <div>
                            <label for="edit_season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                            <select
                                id="edit_season_type_id"
                                v-model="editSeasonForm.season_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editSeasonForm.errors.season_type_id }"
                                required
                            >
                                <option value="">Select a season type</option>
                                <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="editSeasonForm.errors.season_type_id" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.season_type_id }}
                            </div>
                        </div> -->
                        <div>
                            <label for="edit_season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                            <span class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2 not-allowed">
                                {{ seasonTypes.find(type => type.id === editSeasonForm.season_type_id)?.name || 'N/A' }}
                            </span>
                        </div>

                        <div>
                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="edit_start_date"
                                v-model="editSeasonForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editSeasonForm.errors.start_date }"
                                required
                            />
                            <div v-if="editSeasonForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="edit_end_date"
                                v-model="editSeasonForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editSeasonForm.errors.end_date }"
                                required
                            />
                            <div v-if="editSeasonForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEditSeasonModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="editSeasonForm.processing"
                        >
                            Update Season
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Season Modal -->
        <Modal :show="showAddSeasonModal" @close="() => {
            showAddSeasonModal = false;
            seasonForm.reset();
            seasonForm.clearErrors();
        }">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Season</h2>
                <div v-if="addSeasonErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addSeasonErrors }}
                </div>
                <form @submit.prevent="submitSeason">
                    <div class="space-y-4">
                        <div>
                            <label for="season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                            <select
                                id="season_type_id"
                                v-model="seasonForm.season_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': seasonForm.errors.season_type_id }"
                                required
                            >
                                <option value="">Select a season type</option>
                                <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="seasonForm.errors.season_type_id" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.season_type_id }}
                            </div>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="seasonForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': seasonForm.errors.start_date }"
                                required
                            />
                            <div v-if="seasonForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="seasonForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': seasonForm.errors.end_date }"
                                required
                            />
                            <div v-if="seasonForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="() => {
                                showAddSeasonModal = false;
                                seasonForm.reset();
                                seasonForm.clearErrors();
                            }"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="seasonForm.processing"
                        >
                            Create Season
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Date Type Range Modal -->
        <Modal :show="showAddDateTypeRangeModal" @close="() => {
            showAddDateTypeRangeModal = false;
            dateTypeRangeForm.reset();
            dateTypeRangeForm.clearErrors();
        }">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Date Range</h2>
                <div v-if="addDateTypeRangeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addDateTypeRangeErrors }}
                </div>
                <form @submit.prevent="submitDateTypeRange">
                    <div class="space-y-4">
                        <div>
                            <label for="date_type_id" class="block text-sm font-medium text-gray-700">Date Type</label>
                            <select
                                id="date_type_id"
                                v-model="dateTypeRangeForm.date_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': dateTypeRangeForm.errors.date_type_id }"
                                required
                            >
                                <option value="">Select a date type</option>
                                <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="dateTypeRangeForm.errors.date_type_id" class="mt-1 text-sm text-red-600">
                                {{ dateTypeRangeForm.errors.date_type_id }}
                            </div>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="dateTypeRangeForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': dateTypeRangeForm.errors.start_date }"
                                required
                            />
                            <div v-if="dateTypeRangeForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ dateTypeRangeForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="dateTypeRangeForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': dateTypeRangeForm.errors.end_date }"
                                required
                            />
                            <div v-if="dateTypeRangeForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ dateTypeRangeForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showAddDateTypeRangeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="dateTypeRangeForm.processing"
                        >
                            Create Date Range
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Date Type Range Modal -->
        <Modal :show="showEditDateTypeRangeModal" @close="showEditDateTypeRangeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Date Range</h2>
                <form @submit.prevent="updateDateTypeRange">
                    <div class="space-y-4">
                        <!-- <div>
                            <label for="edit_date_type_id" class="block text-sm font-medium text-gray-700">Date Type</label>
                            <select
                                id="edit_date_type_id"
                                v-model="editDateTypeRangeForm.date_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editDateTypeRangeForm.errors.date_type_id }"
                                required
                            >
                                <option value="">Select a date type</option>
                                <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="editDateTypeRangeForm.errors.date_type_id" class="mt-1 text-sm text-red-600">
                                {{ editDateTypeRangeForm.errors.date_type_id }}
                            </div>
                        </div> -->
                        <div>
                            <label for="edit_date_type_id" class="block text-sm font-medium text-gray-700">Date Type</label>
                            <span class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2 not-allowed">
                                {{ dateTypes.find(type => type.id === editDateTypeRangeForm.date_type_id)?.name || 'N/A' }}
                            </span>
                        </div>

                        <div>
                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="edit_start_date"
                                v-model="editDateTypeRangeForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editDateTypeRangeForm.errors.start_date }"
                                required
                            />
                            <div v-if="editDateTypeRangeForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ editDateTypeRangeForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="edit_end_date"
                                v-model="editDateTypeRangeForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editDateTypeRangeForm.errors.end_date }"
                                required
                            />
                            <div v-if="editDateTypeRangeForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ editDateTypeRangeForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEditDateTypeRangeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="editDateTypeRangeForm.processing"
                        >
                            Update Date Range
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { ref, computed, watch } from 'vue';
import moment from 'moment';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import LoadingComponent from '@/Components/LoadingComponent.vue';
import PriceConfigurationRoomTypes from '@/Pages/Packages/packages-show-tabs/PriceConfigurationRoomTypes.vue';
import DateBlockers from '@/Pages/Packages/packages-show-tabs/DateBlockers.vue';

const props = defineProps({
    pkg: Object,
    distinctRoomTypes: Array,
    seasons: Object,
    seasonTypes: Array,
    dateTypeRanges: Object,
    dateTypes: Array,
    priceConfigSeasonChoice: Array,
    priceConfigDateTypeChoice: Array
});

const activeTab = ref('details');
const loadingKey = ref(0);

const tabs = [
    { id: 'details', name: 'Details' },
    { id: 'room-types', name: 'Room Types' },
    { id: 'season-types', name: 'Season Types' },
    { id: 'date-types-ranges', name: 'Date Types' },
    { id: 'price-configuration', name: 'Price Configuration' },
    { id: 'price-configuration-room-types', name: 'Price Configuration Room Types' },
    { id: 'date-blockers', name: 'Date Blockers' }
];

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
    { title: props.pkg.name }
]);

function formatNumber(number) {
  if (number == null || number === '') return '0.00'
  return Number(number).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const showAddRoomTypeModal = ref(false);
const roomTypeForm = useForm({
    name: '',
    description: '',
    max_occupancy: 2,
    package_id: props.pkg.id,
    return_to_package: true,
    images: [],
    delete_images: []
});

// Add error message state
const addRoomTypeErrors = ref('');

// Watch for modal closure to reset error
watch(showAddRoomTypeModal, (newValue) => {
    if (!newValue) {
        addRoomTypeErrors.value = '';
    }
});

const submitRoomType = () => {
    // Validation checks
    if (!roomTypeForm.name?.trim()) {
        addRoomTypeErrors.value = 'Room type name is required';
        return;
    }
    if (!roomTypeForm.max_occupancy || roomTypeForm.max_occupancy < 1) {
        addRoomTypeErrors.value = 'Maximum occupancy must be at least 1';
        return;
    }

    // Create FormData
    const formData = new FormData();
    
    // Add basic fields
    formData.append('name', roomTypeForm.name.trim());
    formData.append('description', roomTypeForm.description?.trim() || '');
    formData.append('max_occupancy', roomTypeForm.max_occupancy);
    formData.append('package_id', props.pkg.id);
    formData.append('return_to_package', 'true');

    // Handle images
    const hasNewImages = roomTypeForm.images.some(image => image instanceof File);

    if (hasNewImages) {
        roomTypeForm.images.forEach((image, index) => {
            if (image instanceof File) {
                formData.append(`images[${index}]`, image);
            }
        });
    }

    // Use axios directly for better control over the request
    axios.post(route('room-types.store'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        roomTypeForm.processing = false;
        showAddRoomTypeModal.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Room type created successfully'
        });
        // Reset form
        roomTypeForm.reset();
        // Refresh the room types list
        handlePageChange(1);
    })
    .catch(error => {
        roomTypeForm.processing = false;
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            // Handle image validation errors
            if (errors.images) {
                // If it's an array of errors, join them
                if (Array.isArray(errors.images)) {
                    addRoomTypeErrors.value = errors.images.join(', ');
                } else {
                    addRoomTypeErrors.value = errors.images;
                }
            } else if (errors.name) {
                addRoomTypeErrors.value = errors.name[0];
            } else if (errors.max_occupancy) {
                addRoomTypeErrors.value = errors.max_occupancy[0];
            } else if (errors.package_id) {
                addRoomTypeErrors.value = errors.package_id[0];
            } else {
                // Handle any other validation errors
                const errorMessages = Object.entries(errors)
                    .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                    .join('\n');
                addRoomTypeErrors.value = errorMessages;
            }
        } else if (error.response?.data?.message) {
            addRoomTypeErrors.value = error.response.data.message;
        } else {
            addRoomTypeErrors.value = 'Failed to create room type. Please try again.';
        }
    })
    .finally(() => {
        roomTypeForm.processing = false;
    });
};

const showEditRoomTypeModal = ref(false);
const editRoomTypeForm = useForm({
    id: null,
    name: '',
    description: '',
    max_occupancy: 2,
    package_id: props.pkg.id,
    return_to_package: true,
    images: [],
    delete_images: []
});

const showEditSeasonModal = ref(false);
const editSeasonForm = useForm({
    id: null,
    season_type_id: '',
    start_date: '',
    end_date: '',
    package_id: props.pkg.id
});

const showAddSeasonModal = ref(false);
const seasonForm = useForm({
    season_type_id: '',
    start_date: '',
    end_date: '',
    package_id: props.pkg.id
});

const showAddDateTypeRangeModal = ref(false);
const dateTypeRangeForm = useForm({
    date_type_id: '',
    start_date: '',
    end_date: '',
    return_to_package: true,
    package_id: props.pkg.id
});

const showEditDateTypeRangeModal = ref(false);
const editDateTypeRangeForm = useForm({
    id: null,
    date_type_id: '',
    start_date: '',
    end_date: '',
    return_to_package: true,
    package_id: props.pkg.id
});

const roomTypesData = ref(props.pkg.load_room_types);
const seasonsData = ref(props.seasons);
const dateTypeRangesData = ref(props.dateTypeRanges);
const addSeasonErrors = ref('');
const addDateTypeRangeErrors = ref('');
const roomTypesPagination = computed(() => ({
    links: roomTypesData.value.links,
    from: roomTypesData.value.from,
    to: roomTypesData.value.to,
    total: roomTypesData.value.total
}));

const seasonsPagination = computed(() => ({
    links: seasonsData.value.links,
    from: seasonsData.value.from,
    to: seasonsData.value.to,
    total: seasonsData.value.total
}));

const dateTypeRangesPagination = computed(() => ({
    links: dateTypeRangesData.value.links,
    from: dateTypeRangesData.value.from,
    to: dateTypeRangesData.value.to,
    total: dateTypeRangesData.value.total
}));

const handlePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.room-types', props.pkg.id), {
            params: { page }
        });
        roomTypesData.value = {
            ...roomTypesData.value,
            data: response.data.data,
            links: response.data.links,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page
        };
        loadingKey.value++;
    } catch (error) {
    }
};

const deleteSeason = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        showConfirmButton: true,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('seasons.destroy', id), {
                data: {
                    return_to_package: true,
                    package_id: props.pkg.id
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Season has been deleted.',
                        icon: 'success',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4F46E5'
                    });
                    handleSeasonPageChange(1);
                }
            });
        }
    });
};

const handleSeasonPageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.seasons', props.pkg.id), {
            params: { page }
        });
        seasonsData.value = {
            ...seasonsData.value,
            data: response.data.data,
            links: response.data.links,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page
        };
        loadingKey.value++;
    } catch (error) {
        // console.error('Error fetching seasons:', error);
    }
};

const editSeason = (season) => {
    editSeasonForm.id = season.id;
    editSeasonForm.season_type_id = season.season_type_id;
    editSeasonForm.start_date = formatDate(season.start_date);
    editSeasonForm.end_date = formatDate(season.end_date);
    showEditSeasonModal.value = true;
};

const formatDate = (isoString) => isoString ? isoString.split('T')[0] : '';
const updateSeason = () => {
    editSeasonForm.put(route('seasons.update', editSeasonForm.id), {
        onSuccess: () => {
            showEditSeasonModal.value = false;
            editSeasonForm.reset();
            editSeasonForm.return_to_package = true;
            editSeasonForm.package_id = props.pkg.id;
            handleSeasonPageChange(1);
        }
    });
};

const submitSeason = () => {
    seasonForm.post(route('seasons.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddSeasonModal.value = false;
            seasonForm.reset();
            seasonForm.return_to_package = true;
            seasonForm.package_id = props.pkg.id;
            handleSeasonPageChange(1);
        },
        onError: () => {
            // Keep the modal open and show errors
            showAddSeasonModal.value = true;
            addSeasonErrors.value = seasonForm.errors.date_range;
        }
    });
};

const editDateTypeRange = (range) => {
    editDateTypeRangeForm.id = range.id;
    editDateTypeRangeForm.date_type_id = range.date_type_id;
    editDateTypeRangeForm.start_date = formatDate(range.start_date);
    editDateTypeRangeForm.end_date = formatDate(range.end_date);
    showEditDateTypeRangeModal.value = true;
};

const submitDateTypeRange = () => {
    dateTypeRangeForm.post(route('date-type-ranges.store'), {
        onSuccess: () => {
            showAddDateTypeRangeModal.value = false;
            dateTypeRangeForm.reset();
            dateTypeRangeForm.return_to_package = true;
            dateTypeRangeForm.package_id = props.pkg.id;
            handleDateTypeRangePageChange(1);
        },
        onError: (errors) => {
            showAddDateTypeRangeModal.value = true;
            addDateTypeRangeErrors.value = dateTypeRangeForm.errors.date_range;
        }
    });
};

const updateDateTypeRange = () => {
    editDateTypeRangeForm.put(route('date-type-ranges.update', editDateTypeRangeForm.id), {
        onSuccess: () => {
            showEditDateTypeRangeModal.value = false;
            editDateTypeRangeForm.reset();
            editDateTypeRangeForm.return_to_package = true;
            editDateTypeRangeForm.package_id = props.pkg.id;
            handleDateTypeRangePageChange(1);
        },
        onError: (errors) => {
            showEditDateTypeRangeModal.value = true;
            addDateTypeRangeErrors.value = dateTypeRangeForm.errors.date_range;
        }
    });
};

const deleteDateTypeRange = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        showConfirmButton: true,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('date-type-ranges.destroy', id), {
                data: {
                    return_to_package: true,
                    package_id: props.pkg.id
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Date range has been deleted.',
                        icon: 'success',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4F46E5'
                    });
                    handleDateTypeRangePageChange(1);
                }
            });
        }
    });
};

const handleDateTypeRangePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.date-type-ranges', props.pkg.id), {
            params: { page }
        });
        dateTypeRangesData.value = {
            ...dateTypeRangesData.value,
            data: response.data.data,
            links: response.data.links,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page
        };
        loadingKey.value++;
    } catch (error) {
        console.error('Error fetching date type ranges:', error);
    }
};

const editRoomType = (roomType) => {
    // Reset the form first
    editRoomTypeForm.reset();
    
    // Set all required fields with explicit values
    editRoomTypeForm.id = roomType.id;
    editRoomTypeForm.name = roomType.name || '';
    editRoomTypeForm.description = roomType.description || '';
    editRoomTypeForm.max_occupancy = parseInt(roomType.max_occupancy) || 2;
    editRoomTypeForm.package_id = parseInt(props.pkg.id); // Ensure package_id is a number
    editRoomTypeForm.images = Array.isArray(roomType.images) ? [...roomType.images] : [];
    editRoomTypeForm.delete_images = [];
    editRoomTypeForm.return_to_package = true;
    showEditRoomTypeModal.value = true;
};


// Add warning message state
const roomTypeWarning = ref('');

// Watch for modal closure to reset warning
watch(showEditRoomTypeModal, (newValue) => {
    if (!newValue) {
        roomTypeWarning.value = '';
    }
});

const closeEditRoomTypeModal = () => {
    showEditRoomTypeModal.value = false;
    editRoomTypeForm.reset();
    roomTypeWarning.value = ''; // Reset warning when closing modal
};

const updateRoomType = () => {
    // Validation checks
    if (!editRoomTypeForm.name?.trim()) {
        roomTypeWarning.value = 'Room type name is required';
        return;
    }
    if (!editRoomTypeForm.max_occupancy || editRoomTypeForm.max_occupancy < 1) {
        roomTypeWarning.value = 'Maximum occupancy must be at least 1';
        return;
    }

    // Create FormData
    const formData = new FormData();
    
    // Add basic fields
    formData.append('_method', 'PUT'); // Required for Laravel to recognize this as a PUT request
    formData.append('name', editRoomTypeForm.name.trim());
    formData.append('description', editRoomTypeForm.description?.trim() || '');
    formData.append('max_occupancy', editRoomTypeForm.max_occupancy);
    formData.append('package_id', props.pkg.id);
    formData.append('return_to_package', 'true');

    // Handle images
    const hasNewImages = editRoomTypeForm.images.some(image => image instanceof File);
    const hasDeletedImages = editRoomTypeForm.delete_images.length > 0;

    if (hasNewImages) {
        editRoomTypeForm.images.forEach((image, index) => {
            if (image instanceof File) {
                formData.append(`images[${index}]`, image);
            }
        });
    }

    if (hasDeletedImages) {
        editRoomTypeForm.delete_images.forEach((imagePath, index) => {
            formData.append(`delete_images[${index}]`, imagePath);
        });
    }

    // Use axios directly for better control over the request
    axios.post(route('room-types.update', editRoomTypeForm.id), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        editRoomTypeForm.processing = false;
        showEditRoomTypeModal.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Room type updated successfully'
        });
        // Reset form
        editRoomTypeForm.reset();
        // Refresh the room types list
        handlePageChange(1);
    })
    .catch(error => {
        editRoomTypeForm.processing = false;
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            // Handle image validation errors
            if (errors.images) {
                // If it's an array of errors, join them
                if (Array.isArray(errors.images)) {
                    roomTypeWarning.value = errors.images.join(', ');
                } else {
                    roomTypeWarning.value = errors.images;
                }
            } else if (errors.name) {
                roomTypeWarning.value = errors.name[0];
            } else if (errors.max_occupancy) {
                roomTypeWarning.value = errors.max_occupancy[0];
            } else if (errors.package_id) {
                roomTypeWarning.value = errors.package_id[0];
            } else {
                // Handle any other validation errors
                const errorMessages = Object.entries(errors)
                    .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                    .join('\n');
                roomTypeWarning.value = errorMessages;
            }
        } else if (error.response?.data?.message) {
            roomTypeWarning.value = error.response.data.message;
        } else {
            roomTypeWarning.value = 'Failed to update room type. Please try again.';
        }
    })
    .finally(() => {
        editRoomTypeForm.processing = false;
    });
};

const deleteRoomType = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        showConfirmButton: true,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('room-types.destroy', id), {
                data: { return_to_package: true },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Room type has been deleted.',
                        icon: 'success',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4F46E5'
                    });
                    handlePageChange(1);
                }
            });
        }
    });
};

// Add new refs for price configuration
const selectedSeason = ref('');
const selectedDateType = ref('');
const selectedRoomType = ref('');
const searched = ref(false);
const showPriceMatrix = ref(false);
const configurationPrices = ref([]);
const pricesFromApi = ref([]);
const isPriceLoading = ref(false);

const canSearch = computed(() => {
    return selectedSeason.value && selectedDateType.value && selectedRoomType.value;
});

const hasSurcharges = computed(() => {
    return configurationPrices.value.some(price => price.type === 'sur_charge');
});

const fetchPrices = () => {
    isPriceLoading.value = true;
    axios.post(route('configuration-prices.fetchPricesSearchIndex'), {
        package_id: props.pkg.id,
        season_type_id: selectedSeason.value,
        date_type_id: selectedDateType.value,
        room_type_id: selectedRoomType.value
    })
    .then(response => {
        if (response.data && response.data.length > 0) {
            // Store the configuration ID for later use
            const configurationId = response.data[0].id;

            // Reset the price form arrays
            Object.keys(priceForm.prices).forEach(type => {
                priceForm.prices[type] = [];
            });

            // Map prices directly from API response
            response.data[0].prices.forEach(price => {
                priceForm.prices[price.type].push({
                    id: price.id,
                    package_configuration_id: price.package_configuration_id,
                    type: price.type,
                    number_of_adults: price.number_of_adults,
                    number_of_children: price.number_of_children,
                    adult_price: price.adult_price,
                    child_price: price.child_price
                });
            });

            configurationPrices.value = response.data[0].prices;
            searched.value = true;
            showPriceMatrix.value = true;
            showPriceForm.value = false;
            isPriceLoading.value = false;
        } else {
            searched.value = true;
            showPriceMatrix.value = false;
            showPriceForm.value = false;
            isPriceLoading.value = false;
        }
    })
    .catch(error => {
        searched.value = true;
        showPriceMatrix.value = false;
        showPriceForm.value = false;
        isPriceLoading.value = false;
    });
};

const getPrice = (adults, children, type, AdultOrChild) => {
    const price = configurationPrices.value.find(p =>
        p.number_of_adults === adults &&
        p.number_of_children === children &&
        p.type === type
    );
    return price ? `MYR ${price[AdultOrChild + '_price']}` : '-';
};

const resetSearch = () => {
    selectedSeason.value = '';
    selectedDateType.value = '';
    selectedRoomType.value = '';
    searched.value = false;
    showPriceMatrix.value = false;
    showPriceForm.value = false;
    isEditMode.value = false;
    configurationPrices.value = [];
    priceForm.reset();
};

const showPriceForm = ref(false);
const isEditMode = ref(false);
const priceForm = useForm({
    package_id: props.pkg.id,
    season_id: selectedSeason.value,
    date_type_id: selectedDateType.value,
    room_type: selectedRoomType.value,
    prices: {
        base_charge: [],
        sur_charge: [],
    }
});

const getPriceIndex = (adults, children, type) => {
    return (adults - 1) * 4 + children;
};

const openPriceForm = (mode) => {
    isEditMode.value = mode === 'edit';
    if (isEditMode.value && configurationPrices.value.length > 0) {
        // Reset the price form arrays
        Object.keys(priceForm.prices).forEach(type => {
            priceForm.prices[type] = Array(16).fill().map((_, index) => ({
                number_of_adults: Math.floor(index / 4) + 1,
                number_of_children: index % 4,
                adult_price: '',
                child_price: ''
            }));
        });

        // Map prices directly from configuration prices
        configurationPrices.value.forEach(price => {
            const index = getPriceIndex(price.number_of_adults, price.number_of_children, price.type);
            priceForm.prices[price.type][index] = {
                id: price.id,
                package_configuration_id: price.package_configuration_id,
                type: price.type,
                number_of_adults: price.number_of_adults,
                number_of_children: price.number_of_children,
                adult_price: price.adult_price,
                child_price: price.child_price
            };
        });
    } else {
        // Reset form for create mode
        Object.keys(priceForm.prices).forEach(type => {
            priceForm.prices[type] = Array(16).fill().map((_, index) => ({
                number_of_adults: Math.floor(index / 4) + 1,
                number_of_children: index % 4,
                adult_price: '',
                child_price: ''
            }));
        });
    }
    showPriceForm.value = true;
};

const closePriceForm = () => {
    showPriceForm.value = false;
    isEditMode.value = false;
    priceForm.reset();
};

const submitPrices = () => {
    if (isEditMode.value) {
        // Update prices using the configuration price ID
        const data = {
            package_id: props.pkg.id,
            season_type_id: selectedSeason.value,
            date_type_id: selectedDateType.value,
            room_type: selectedRoomType.value,
            prices: priceForm.prices
        };

        axios.put(route('configuration-prices.updatePrices', 1), data)
            .then(() => {
                closePriceForm();
                fetchPrices();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Prices updated successfully',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4F46E5'
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Failed to update prices',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4F46E5'
                });
            });
    } else {
        // Create prices
        const data = {
            package_id: props.pkg.id,
            season_type_id: selectedSeason.value,
            date_type_id: selectedDateType.value,
            room_type: selectedRoomType.value,
            prices: priceForm.prices
        };

        axios.post(route('configuration-prices.store'), data)
            .then(() => {
                closePriceForm();
                fetchPrices();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Prices created successfully',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4F46E5'
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Failed to create prices',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4F46E5'
                });
            });
    }
};

// Watch for changes in selected values to update form data
watch([selectedSeason, selectedDateType, selectedRoomType], ([newSeason, newDateType, newRoomType]) => {
    priceForm.season_id = newSeason;
    priceForm.date_type_id = newDateType;
    priceForm.room_type = newRoomType;
});

watch(showAddSeasonModal, (newVal) => {
  if (!newVal) {
    setTimeout(() => {
      seasonForm.reset();
      seasonForm.clearErrors();
      addSeasonErrors.value = '';
    }, 500);
  }
});

watch(showAddDateTypeRangeModal, (newVal) => {
  if (!newVal) {
    setTimeout(() => {
      dateTypeRangeForm.reset();
      dateTypeRangeForm.clearErrors();
      addDateTypeRangeErrors.value = '';
    }, 500);
  }
});


const copyLink = (link) => {
    navigator.clipboard.writeText(link).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Link has been copied to clipboard',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });
    }).catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to copy link',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });
    });
};

const applyBasePricesToAll = () => {
    if (!priceForm.prices.base_charge || priceForm.prices.base_charge.length === 0) return;
    
    // Get the first price combination (1 adult, 0 children)
    const firstPrice = priceForm.prices.base_charge[0];
    if (!firstPrice) return;

    // Apply the first price to all combinations
    priceForm.prices.base_charge = priceForm.prices.base_charge.map((_, index) => ({
        number_of_adults: Math.floor(index / 4) + 1,
        number_of_children: index % 4,
        adult_price: firstPrice.adult_price,
        child_price: firstPrice.child_price
    }));
};

const applySurchargePricesToAll = () => {
    if (!priceForm.prices.sur_charge || priceForm.prices.sur_charge.length === 0) return;
    
    // Get the first price combination (1 adult, 0 children)
    const firstPrice = priceForm.prices.sur_charge[0];
    if (!firstPrice) return;

    // Apply the first price to all combinations
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

const handleImageUpload = (event, formName) => {
    const files = Array.from(event.target.files);
    const form = formName === 'roomTypeForm' ? roomTypeForm : editRoomTypeForm;
    
    // Validate each file
    const validFiles = files.filter(file => {
        // Check file type
        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: `${file.name} is not an image file.`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4F46E5'
            });
            return false;
        }
        
        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: `${file.name} is larger than 2MB.`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4F46E5'
            });
            return false;
        }
        
        return true;
    });
    
    // Add valid files to the images array
    form.images = [...form.images, ...validFiles];
};

const removeImage = (index, formName) => {
    const form = formName === 'roomTypeForm' ? roomTypeForm : editRoomTypeForm;
    const image = form.images[index];
    
    // If it's an existing image (string path), add to delete_images
    if (typeof image === 'string') {
        form.delete_images.push(image);
    }
    
    // Remove from images array
    form.images.splice(index, 1);
};

const getImageUrl = (imagePath) => {
    // If the path already includes 'images/', return as is
    if (imagePath.startsWith('images/')) {
        return `/${imagePath}`;
    }
    // If the path starts with 'room-types/', add 'images/' prefix
    if (imagePath.startsWith('room-types/')) {
        return `/images/${imagePath}`;
    }
    // For any other case, assume it's a relative path and add 'images/' prefix
    return `/images/${imagePath}`;
};

// Add back the getImagePreviewUrl function for image previews
const getImagePreviewUrl = (image) => {
    if (typeof image === 'string') {
        return getImageUrl(image);
    }
    
    // Check if URL API is available
    if (typeof window !== 'undefined' && window.URL && window.URL.createObjectURL) {
        return URL.createObjectURL(image);
    }
    
    // Fallback for when URL API is not available
    return '';
};

const showImageModal = (image) => {
    Swal.fire({
        imageUrl: getImageUrl(image),
        imageAlt: 'Room image',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            container: 'image-modal',
            popup: 'max-w-4xl',
            closeButton: 'text-gray-500 hover:text-gray-700',
            image: 'max-h-[80vh] object-contain'
        },
        background: '#f8f9fa',
        padding: '1rem',
        width: 'auto',
        backdrop: 'rgba(0,0,0,0.8)'
    });
};

// Make sure to expose the functions to the template
defineExpose({
    getImageUrl,
    getImagePreviewUrl,
    showImageModal
});

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

/* Modal styles */
:deep(.image-modal) {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

:deep(.image-modal::-webkit-scrollbar) {
    display: none !important;
}

:deep(.swal2-popup) {
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
}

:deep(.swal2-html-container) {
    margin: 0 !important;
    padding: 0 !important;
}

:deep(.swal2-image) {
    margin: 0 !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

:deep(.swal2-close) {
    color: white !important;
    font-size: 2rem !important;
    right: 1rem !important;
    top: 1rem !important;
}

:deep(.swal2-close:hover) {
    color: #e5e7eb !important;
}
</style>
