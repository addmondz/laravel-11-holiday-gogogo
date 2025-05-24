<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200" style="height: 80px;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" style="max-width: 100px;" />
                    </div>
                    <div class="flex items-center">
                        <div @click="openWhatsApp" class="bg-sky-600 text-white px-6 py-2 hover:bg-sky-700 flex items-center gap-4 transition-all duration-300" style="border-radius: 50px 50px 50px 50px; height: 40px;">
                            <WhatsAppOutlined />
                            Enquiry
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Not Found State -->
        <transition name="fade" v-if="isLoading">
            <div class="flex justify-center items-center h-full min-h-screen">
                <LoadingComponent />
            </div>
        </transition>
        <div v-else-if="!packageData" class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <div class="text-6xl mb-4">🔍</div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Package Not Found</h1>
                <p class="text-gray-600 mb-8">We couldn't find the package you're looking for, please check the URL or contact us for assistance.</p>
                <!-- <Link :href="route('home')" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Return Home
                </Link> -->
            </div>
        </div>

        <!-- Package Found State -->
        <div v-else class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" style="padding-top: 80px; padding-bottom: 50px;">
            <!-- Image Carousel -->
            <div class="mb-8">
                <div class="relative">
                    <div class="overflow-hidden rounded-lg shadow-lg">
                        <!-- <div class="relative min-h-[500px]"> -->
                        <div class="relative mt-10" style="height: 500px;">
                            <img
                                v-for="(image, index) in mockImages"
                                :key="index"
                                :src="image"
                                :class="[
                                    'absolute inset-0 w-full h-full object-cover transition-opacity duration-500  h-[500px]',
                                    currentImageIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'
                                ]"
                                :alt="packageData?.name || 'Package Image'"
                            />
                            <!-- Navigation Buttons -->
                            <button
                                @click="previousImage"
                                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full p-2 z-20"
                                aria-label="Previous image"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button
                                @click="nextImage"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full p-2 z-20"
                                aria-label="Next image"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <!-- Image Indicators -->
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
                        <button
                            v-for="(image, index) in mockImages"
                            :key="index"
                            @click="currentImageIndex = index"
                            :class="[
                                'w-3 h-3 rounded-full transition-colors duration-200',
                                currentImageIndex === index ? 'bg-white' : 'bg-white/50 hover:bg-white/75'
                            ]"
                            :aria-label="`Go to image ${index + 1}`"
                        />
                    </div>
                </div>
            </div>

            <!-- Package Details -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="inline-flex items-center px-4 py-2 rounded-md text-sm font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 text-white mr-2 mb-1 sm:mb-0">
                        {{ computedPromoPeriod }} Promo
                    </span>
                    {{ packageData.name }}
                </h1>
                <p class="text-gray-600 mb-6">{{ packageData.description }}</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Location</h2>
                        <p class="text-gray-600">{{ packageData.location }}</p>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Duration</h2>
                        <p class="text-gray-600">{{ packageData.package_max_days +1 }} Days {{ packageData.package_max_days }} {{packageData.package_max_days > 1 ? 'Nights' : 'Night'}}</p>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Package Prices</h2>
                        <p class="text-gray-600">MYR {{ formatNumber(packageData.display_price_adult) }}</p>
                        <!-- <p class="text-gray-600">Adult: MYR {{ formatNumber(packageData.display_price_adult) }}</p> -->
                        <!-- <p class="text-gray-600">Child: MYR {{ formatNumber(packageData.display_price_child) }}</p> -->
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Package Start Date</h2>
                        <p class="text-gray-600">{{ moment(packageData.package_start_date).format('DD MMM YYYY') }}</p>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Package End Date</h2>
                        <p class="text-gray-600">{{ moment(packageData.package_end_date).format('DD MMM YYYY') }}</p>
                    </div>
                </div>
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Terms and Conditions</h2>
                    <p class="text-gray-600">{{ packageData.terms_and_conditions }}</p>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Book Your Stay</h2>
                
                <!-- Step Indicators -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">1</div>
                            <div class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-indigo-600' : 'text-gray-500'">Quotation Details</div>
                        </div>
                        <div class="flex-1 mx-4 h-0.5" :class="currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        <div class="flex items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">2</div>
                            <div class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-indigo-600' : 'text-gray-500'">Price Summary</div>
                        </div>
                        <div class="flex-1 mx-4 h-0.5" :class="currentStep >= 3 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        <div class="flex items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">3</div>
                            <div class="ml-2 text-sm font-medium" :class="currentStep >= 3 ? 'text-indigo-600' : 'text-gray-500'">Booking Details</div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Quotation Details -->
                <div v-if="currentStep === 1">
                    <form @submit.prevent="handleStep1Submit" class="space-y-6">
                        <!-- Room Type Selection -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Room Type</label>
                            <div v-if="roomTypes.length === 0" class="text-center py-4">
                                <p class="text-gray-600">No room types available for this package.</p>
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="roomType in roomTypes"
                                    :key="roomType.id"
                                    :class="[
                                        'border rounded-lg p-4 cursor-pointer transition-all duration-200',
                                        selectedRoomType === roomType.id
                                            ? 'border-indigo-600 bg-indigo-50'
                                            : 'border-gray-200 hover:border-indigo-400'
                                    ]"
                                    @click="selectedRoomType = roomType.id; form.room_type_id = roomType.id"
                                >
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ roomType.name }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ roomType.description }}</p>
                                        </div>
                                        <!-- <div class="text-right">
                                            <p class="text-lg font-bold text-indigo-600">
                                                MYR {{ formatNumber(roomType.price_per_night) }}
                                            </p>
                                            <p class="text-sm text-gray-500">per night</p>
                                        </div> -->
                                    </div>
                                    <!-- <div class="mt-4 text-sm text-gray-600"> -->
                                    <div class="mt-4 text-sm text-indigo-600">
                                        <p>Max Occupancy: {{ roomType.max_occupancy }} persons</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="validationErrors.room_type" class="mt-1 text-sm text-red-600">{{ validationErrors.room_type }}</p>
                        </div>

                        <!-- Date Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input
                                    type="date"
                                    id="start_date"
                                    v-model="form.start_date"
                                    :min="packageData?.package_start_date"
                                    :max="maxStartDate"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        validationErrors.start_date ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    required
                                    @change="validateDates"
                                />
                                <p v-if="validationErrors.start_date" class="mt-1 text-sm text-red-600">{{ validationErrors.start_date }}</p>
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input
                                    type="date"
                                    id="end_date"
                                    v-model="form.end_date"
                                    :min="form.start_date ? new Date(new Date(form.start_date).getTime() + 86400000).toISOString().split('T')[0] : new Date().toISOString().split('T')[0]"
                                    class="mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-100 border-gray-300"
                                    required
                                    readonly
                                />
                                <!-- <p v-if="validationErrors.end_date" class="mt-1 text-sm text-red-600">{{ validationErrors.end_date }}</p> -->
                            </div>
                        </div>

                        <!-- Guest Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="adults" class="block text-sm font-medium text-gray-700">Number of Adults</label>
                                <input
                                    type="number"
                                    id="adults"
                                    v-model="form.adults"
                                    min="1"
                                    max="4"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        validationErrors.adults ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    required
                                />
                                <p v-if="validationErrors.adults" class="mt-1 text-sm text-red-600">{{ validationErrors.adults }}</p>
                            </div>
                            <div>
                                <label for="children" class="block text-sm font-medium text-gray-700">Number of Children</label>
                                <input
                                    type="number"
                                    id="children"
                                    v-model="form.children"
                                    min="0"
                                    max="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                :disabled="form.processing"
                            >
                                Calculate Price
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Price Summary -->
                <div v-if="currentStep === 2">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <!-- Booking Summary -->
                        <div v-if="bookingSummary && priceBreakdown?.summary" class="mb-6 border-b pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Summary</h3>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <p class="text-sm text-gray-600">Room Type</p>
                                    <p class="font-medium">{{ bookingSummary.roomType }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Duration</p>
                                    <p class="font-medium">{{ bookingSummary.duration }} nights</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Check-in</p>
                                    <p class="font-medium">{{ moment(bookingSummary.startDate).format('DD MMM YYYY') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Check-out</p>
                                    <p class="font-medium">{{ moment(bookingSummary.endDate).format('DD MMM YYYY') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Nightly Breakdown -->
                        <div v-if="priceBreakdown?.nights?.length" class="mb-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Nightly Breakdown</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border-solid rounded-md border">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Night</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Season</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Charge</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Surcharge</th>
                                            <!-- <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Add-ons</th> -->
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template v-for="(night, index) in priceBreakdown.nights" :key="index">
                                            <!-- Main row -->
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    Night {{ index + 1 }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ moment(night.date).format('DD MMM YYYY') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ night.season_type }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ night.date_type }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                    <div class="text-gray-900">{{ formatNumber(night.base_charge.total) }} MYR</div>
                                                    <div class="text-xs text-gray-500">
                                                        Adult: {{ formatNumber(night.base_charge.adult.total) }} MYR<br>
                                                        Child: {{ formatNumber(night.base_charge.child.total) }} MYR
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                    <div class="text-gray-900">{{ formatNumber(night.surcharge.total) }} MYR</div>
                                                    <div class="text-xs text-gray-500">
                                                        Adult: {{ formatNumber(night.surcharge.adult.total) }} MYR<br>
                                                        Child: {{ formatNumber(night.surcharge.child.total) }} MYR
                                                    </div>
                                                </td>
                                                <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                    <div v-if="night.add_ons && night.add_ons.length > 0">
                                                        <div v-for="(addon, addonIndex) in night.add_ons" :key="addonIndex" class="text-xs text-gray-500">
                                                            {{ addon.name }}: {{ formatNumber(addon.total) }} MYR
                                                        </div>
                                                    </div>
                                                    <div v-else class="text-gray-500">-</div>
                                                </td> -->
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right text-gray-900">
                                                    {{ formatNumber(night.total) }} MYR
                                                </td>
                                            </tr>
                                            <!-- Add-ons details row (if any) -->
                                            <tr v-if="night.add_ons && night.add_ons.length > 0" class="bg-gray-50">
                                                <td colspan="8" class="px-6 py-3">
                                                    <div class="text-xs text-gray-500">
                                                        <div v-for="(addon, addonIndex) in night.add_ons" :key="addonIndex" class="mb-1">
                                                            <span class="font-medium">{{ addon.name }}:</span>
                                                            <span class="ml-2">
                                                                Adults ({{ addon.adult_qty }} × {{ formatNumber(addon.adult_price) }} MYR) = {{ formatNumber(addon.adult_total) }} MYR
                                                                <span v-if="addon.child_qty > 0" class="ml-2">
                                                                    | Children ({{ addon.child_qty }} × {{ formatNumber(addon.child_price) }} MYR) = {{ formatNumber(addon.child_total) }} MYR
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Overall Summary -->
                        <div v-if="priceBreakdown?.summary" class="border-t border-gray-200 pt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Overall Summary</h4>
                            <div class="space-y-2">
                                <!-- Base Charges Summary -->
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">Base Charges</p>
                                        <div class="text-sm text-gray-600 ml-4">
                                            <p>Adults: {{ formatNumber(priceBreakdown?.summary?.base_charges?.adult?.total || 0) }} MYR</p>
                                            <p>Children: {{ formatNumber(priceBreakdown?.summary?.base_charges?.child?.total || 0) }} MYR</p>
                                        </div>
                                    </div>
                                    <p class="font-medium">{{ formatNumber(priceBreakdown?.summary?.base_charges?.total || 0) }} MYR</p>
                                </div>

                                <!-- Surcharges Summary -->
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">Surcharges</p>
                                        <div class="text-sm text-gray-600 ml-4">
                                            <p>Adults: {{ formatNumber(priceBreakdown?.summary?.surcharges?.adult?.total || 0) }} MYR</p>
                                            <p>Children: {{ formatNumber(priceBreakdown?.summary?.surcharges?.child?.total || 0) }} MYR</p>
                                        </div>
                                    </div>
                                    <p class="font-medium">{{ formatNumber(priceBreakdown?.summary?.surcharges?.total || 0) }} MYR</p>
                                </div>

                                <!-- Add-ons Summary -->
                                <div v-if="priceBreakdown?.summary?.add_ons?.total > 0" class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">Add-ons</p>
                                        <div class="text-sm text-gray-600 ml-4">
                                            <p>Adults: {{ formatNumber(priceBreakdown?.summary?.add_ons?.adult?.total || 0) }} MYR</p>
                                            <p>Children: {{ formatNumber(priceBreakdown?.summary?.add_ons?.child?.total || 0) }} MYR</p>
                                        </div>
                                    </div>
                                    <p class="font-medium">{{ formatNumber(priceBreakdown?.summary?.add_ons?.total || 0) }} MYR</p>
                                </div>

                                <!-- Grand Total -->
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                    <p class="text-lg font-semibold">Grand Total</p>
                                    <p class="text-lg font-semibold">{{ formatNumber(priceBreakdown?.total || 0) }} MYR</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between mt-6">
                            <button
                                @click="currentStep = 1"
                                class="px-8 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                Back
                            </button>
                            <button
                                @click="currentStep = 3"
                                class="px-8 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Booking Details -->
                <div v-if="currentStep === 3">
                    <div v-if="!bookingSuccess" class="space-y-6">
                        <form @submit.prevent="submitBooking" class="space-y-6">
                            <!-- Booking Name -->
                            <div>
                                <label for="booking_name" class="block text-sm font-medium text-gray-700">Booking Name</label>
                                <input
                                    type="text"
                                    id="booking_name"
                                    v-model="bookingForm.booking_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input
                                    type="tel"
                                    id="phone_number"
                                    v-model="bookingForm.phone_number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <!-- Booking IC -->
                            <div>
                                <label for="booking_ic" class="block text-sm font-medium text-gray-700">IC/Passport Number</label>
                                <input
                                    type="text"
                                    id="booking_ic"
                                    v-model="bookingForm.booking_ic"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <!-- Special Remarks -->
                            <div>
                                <label for="special_remarks" class="block text-sm font-medium text-gray-700">Special Remarks (if any)</label>
                                <textarea
                                    id="special_remarks"
                                    v-model="bookingForm.special_remarks"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <div class="flex justify-between">
                                <button
                                    type="button"
                                    @click="currentStep = 2"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Back
                                </button>
                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    :disabled="isSubmitting"
                                >
                                    {{ isSubmitting ? 'Submitting...' : 'Submit Booking' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Booking Success State -->
                    <div v-else class="bg-white rounded-lg p-6">
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Booking Successful!</h3>
                            <p class="text-gray-600">Your booking has been submitted successfully.</p>
                        </div>

                        <!-- Booking Summary -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Booking Summary</h4>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Booking Reference</p>
                                        <p class="font-medium">{{ bookingSuccess.uuid }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Booking Name</p>
                                        <p class="font-medium">{{ bookingSuccess.booking_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Room Type</p>
                                        <p class="font-medium">{{ bookingSuccess.room_type?.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Duration</p>
                                        <p class="font-medium">{{ bookingSuccess.duration }} nights</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Check-in</p>
                                        <p class="font-medium">{{ moment(bookingSuccess.start_date).format('DD MMM YYYY') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Check-out</p>
                                        <p class="font-medium">{{ moment(bookingSuccess.end_date).format('DD MMM YYYY') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Guests</p>
                                        <p class="font-medium">{{ bookingSuccess.adults }} Adults, {{ bookingSuccess.children }} Children</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Amount</p>
                                        <p class="font-medium">MYR {{ formatNumber(bookingSuccess.total_price) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pay Now Button -->
                        <div class="text-center">
                            <button
                                @click="proceedToPayment"
                                class="px-8 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 text-lg font-medium"
                                :disabled="isProcessingPayment"
                            >
                                {{ isProcessingPayment ? 'Processing...' : 'Pay Now' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import LoadingComponent from '@/Components/LoadingComponent.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { WhatsAppOutlined } from '@ant-design/icons-vue';
import Modal from '@/Components/Modal.vue';
import Swal from 'sweetalert2';
import moment from 'moment';

const props = defineProps({
    uuid: String,
    booking: Object
});

onMounted(() => {
    if (props.booking) {
        bookingSuccess.value = props.booking;
        currentStep.value = 3;
    }
});

const packageData = ref(null);
const currentImageIndex = ref(0);
const calculatedPrice = ref(null);
const priceBreakdown = ref(null);
const nightlyBreakdown = ref([]);
const roomTypes = ref([]);
const selectedRoomType = ref(null);
const isLoading = ref(true);
const bookingSummary = ref(null);
const dateError = ref('');
const validationErrors = ref({
    room_type: '',
    start_date: '',
    end_date: '',
    adults: ''
});
const phone = '60102956786';
function openWhatsApp() {
  const currentUrl = window.location.href;
  const message = `Hi, I have an enquiry regarding this package: ${currentUrl}`;
  const whatsappLink = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
  window.open(whatsappLink, '_blank');
}

const computedPromoPeriod = computed(() => {
  if (!packageData.value) return '';

  const startYear = new Date(packageData.value.package_start_date).getFullYear();
  const endYear = new Date(packageData.value.package_end_date).getFullYear();

  if (startYear === endYear && startYear === 2025) {
    return '2025';
  }

  // Build year range like 2025/26/27
  const years = [];
  for (let y = startYear; y <= endYear; y++) {
    if (y === startYear) {
      years.push(y.toString());
    } else {
      years.push((y % 100).toString().padStart(2, '0'));
    }
  }

  return years.join('/');
});

const bookingForm = ref({
    booking_name: '',
    phone_number: '',
    booking_ic: '',
    special_remarks: ''
});

const isSubmitting = ref(false);
const currentStep = ref(1);
const showBookingModal = ref(false);
let autoRotationInterval = null;

// Move interval setup to setup function
onMounted(() => {
    // Start auto-rotation
    autoRotationInterval = setInterval(() => {
        nextImage();
    }, 5000);
});

// Clean up interval on component unmount
onUnmounted(() => {
    if (autoRotationInterval) {
        clearInterval(autoRotationInterval);
    }
});

// Remove the startAutoRotation function and its call
const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % mockImages.length;
};

const previousImage = () => {
    currentImageIndex.value = (currentImageIndex.value - 1 + mockImages.length) % mockImages.length;
};

onMounted(async () => {
    try {
        const response = await axios.post(route('api.fetch-package-by-uuid'), {
            uuid: props.uuid
        });

        if (response.data.success && response.data.package) {
            packageData.value = {
                ...response.data.package,
                images: mockImages
            };
            roomTypes.value = response.data.room_types;
        } else {
            packageData.value = null;
        }
    } catch (error) {
        console.error('Error fetching package:', error);
        packageData.value = null;
    } finally {
        isLoading.value = false;
    }
});

// Update the mock images array with proper URLs
const mockImages = [
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'
];

const form = useForm({
    start_date: '',
    end_date: '',
    adults: 1,
    children: 0,
    room_type_id: null
});

const validateForm = () => {
    let isValid = true;
    validationErrors.value = {
        room_type: '',
        start_date: '',
        end_date: '',
        adults: ''
    };

    // Validate room type
    if (!form.room_type_id) {
        validationErrors.value.room_type = 'Please select a room type';
        isValid = false;
    }

    // Validate dates
    if (!form.start_date) {
        validationErrors.value.start_date = 'Please select a start date';
        isValid = false;
    } else {
        const startDate = new Date(form.start_date);
        const packageStartDate = new Date(packageData.value.package_start_date);
        const packageEndDate = new Date(packageData.value.package_end_date);
        const maxStartDate = new Date(packageEndDate);
        maxStartDate.setDate(packageEndDate.getDate() - packageData.value.package_max_days);

        if (startDate < packageStartDate) {
            validationErrors.value.start_date = `Start date cannot be before ${moment(packageStartDate).format('DD MMM YYYY')}`;
            isValid = false;
        } else if (startDate > maxStartDate) {
            validationErrors.value.start_date = `Start date cannot be after ${moment(maxStartDate).format('DD MMM YYYY')} to ensure ${packageData.value.package_max_days} days stay within package end date`;
            isValid = false;
        }
    }

    if (!form.end_date) {
        validationErrors.value.end_date = 'Please select an end date';
        isValid = false;
    } else {
        const endDate = new Date(form.end_date);
        const packageEndDate = new Date(packageData.value.package_end_date);

        if (endDate > packageEndDate) {
            validationErrors.value.end_date = `End date cannot be after ${moment(packageEndDate).format('DD MMM YYYY')}`;
            isValid = false;
        }
    }

    if (form.start_date && form.end_date) {
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays < 1) {
            validationErrors.value.end_date = 'End date must be at least 1 day after start date';
            isValid = false;
        }

        if (diffDays > packageData.value.package_max_days) {
            validationErrors.value.end_date = `Duration cannot exceed ${packageData.value.package_max_days} days`;
            isValid = false;
        }
    }

    // Validate adults
    if (!form.adults || form.adults < 1) {
        validationErrors.value.adults = 'Please select at least 1 adult';
        isValid = false;
    }

    return isValid;
};

const calculateEndDate = () => {
    if (form.start_date && packageData.value.package_max_days) {
        const startDate = new Date(form.start_date);
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + parseInt(packageData.value.package_max_days));
        form.end_date = endDate.toISOString().split('T')[0];
    }
};

// Watch for changes in package_days to recalculate end date
watch(() => form.start_date, () => {
    if (form.start_date) {
        calculateEndDate();
    }
});

const calculatePrice = async () => {
    if (!validateForm()) return;

    try {
        const response = await axios.post(route('api.package-calculate-price'), {
            package_id: packageData.value.id,
            room_type: form.room_type_id,
            start_date: form.start_date,
            end_date: form.end_date,
            adults: form.adults,
            children: form.children
        });

        if (response.data.success) {
            // Update the state with the complete response data
            calculatedPrice.value = parseFloat(response.data.total) || 0;
            priceBreakdown.value = response.data;
            
            // Update booking summary
            const selectedRoomType = roomTypes.value.find(rt => rt.id === form.room_type_id);
            bookingSummary.value = {
                roomType: selectedRoomType?.name || '',
                startDate: form.start_date,
                endDate: form.end_date,
                adults: form.adults,
                children: form.children,
                duration: Math.ceil((new Date(form.end_date) - new Date(form.start_date)) / (1000 * 60 * 60 * 24)),
                seasonType: response.data.season_type || '',
                dateType: response.data.date_type || '',
                isWeekend: response.data.is_weekend || false
            };

            currentStep.value = 2;
        } else {
            throw new Error(response.data.message || 'Failed to calculate price');
        }
    } catch (error) {
        console.error('Error calculating price:', error);
        calculatedPrice.value = null;
        priceBreakdown.value = null;
        bookingSummary.value = null;
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || error.message || 'Failed to calculate price',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4F46E5'
        });
    }
};

// Add watchers for date changes
watch([() => form.start_date, () => form.end_date], () => {
    validateForm();
});

const formatNumber = (number) => {
    const num = parseFloat(number);
    if (isNaN(num)) return '0.00';
    return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

// Add new methods for step handling
const handleStep1Submit = async () => {
    if (!validateForm()) return;
    await calculatePrice();
    if (calculatedPrice.value !== null) {
        currentStep.value = 2;
    }
};

// Add new refs for booking success state
const bookingSuccess = ref(null);
const isProcessingPayment = ref(false);

const submitBooking = async () => {
    if (!validateBookingForm()) return;

    try {
        isSubmitting.value = true;
        const response = await axios.post(route('api.bookings.store'), {
            package_id: packageData.value.id,
            room_type_id: form.room_type_id,
            booking_name: bookingForm.value.booking_name,
            phone_number: bookingForm.value.phone_number,
            booking_ic: bookingForm.value.booking_ic,
            start_date: form.start_date,
            end_date: form.end_date,
            adults: form.adults,
            children: form.children,
            total_price: priceBreakdown.value.total,
            special_remarks: bookingForm.value.special_remarks
        });

        if (response.data.success) {
            bookingSuccess.value = response.data.booking;
        }
    } catch (error) {
        console.error('Booking error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Booking Failed',
            text: error.response?.data?.message || 'Failed to create booking. Please try again.',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        isSubmitting.value = false;
    }
};

const proceedToPayment = async () => {
    if (!bookingSuccess.value) return;

    try {
        isProcessingPayment.value = true;
        const transactionResponse = await axios.post('/calculator/api/transactions', {
            booking_id: bookingSuccess.value.id,
            amount: bookingSuccess.value.total_price,
            status: 'pending'
        });

        if (transactionResponse.data.success) {
            window.open(route('api.payment.show', bookingSuccess.value.uuid), '_blank');
        } else {
            throw new Error(transactionResponse.data.message || 'Failed to create transaction');
        }
    } catch (error) {
        console.error('Transaction creation error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Payment Initialization Failed',
            text: error.response?.data?.message || error.message || 'Failed to initialize payment. Please try again.',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        isProcessingPayment.value = false;
    }
};

const validateBookingForm = () => {
    // Add your booking form validation logic here
    return true;
};

// Add a computed property for max start date
const maxStartDate = computed(() => {
    if (!packageData.value) return '';
    const packageEndDate = new Date(packageData.value.package_end_date);
    const maxDate = new Date(packageEndDate);
    maxDate.setDate(packageEndDate.getDate() - packageData.value.package_max_days);
    return maxDate.toISOString().split('T')[0];
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

/* Add styles for the promo badge */
.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradient 0.5s ease infinite;
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Make the promo badge responsive */
@media (max-width: 640px) {
    h1 {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .inline-flex {
        margin-bottom: 0.5rem;
    }
}
</style>
