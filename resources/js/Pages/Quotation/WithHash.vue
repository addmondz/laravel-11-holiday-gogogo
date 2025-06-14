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
                        <div @click="openWhatsApp" class="bg-sky-600 text-white px-6 py-2 hover:bg-sky-700 flex items-center gap-4 transition-all duration-300 cursor-pointer" style="border-radius: 50px 50px 50px 50px; height: 40px;">
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
                        <div class="relative mt-10" style="height: 500px;">
                            <template v-if="packageData.images && packageData.images.length > 0">
                                <img
                                    v-for="(image, index) in packageData.images"
                                    :key="'package-image-' + index"
                                    :src="getImageUrl(image)"
                                    :class="[
                                        'absolute inset-0 w-full h-full object-cover transition-opacity duration-500 h-[500px]',
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
                                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white rounded-full p-2 shadow-lg"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <!-- Image Indicators -->
                                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
                                    <button
                                        v-for="(image, index) in packageData.images"
                                        :key="index"
                                        @click="currentImageIndex = index"
                                        :class="[
                                            'w-3 h-3 rounded-full transition-colors duration-200',
                                            currentImageIndex === index ? 'bg-white' : 'bg-white/50 hover:bg-white/75'
                                        ]"
                                        :aria-label="`Go to image ${index + 1}`"
                                    />
                                </div>
                            </template>
                            <template v-else>
                                <div class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded-lg">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Images Available</h3>
                                        <p class="mt-1 text-sm text-gray-500">This package does not have any images.</p>
                                    </div>
                                </div>
                            </template>
                        </div>
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
            <div class="bg-white rounded-lg shadow-lg p-6" id="booking-form">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ currentStep === 3 ? 'Your Booking' : 'Book Your Stay' }}</h2>
                
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

                <!-- Step 1: Room Selection -->
                <div v-if="currentStep === 1">
                    <form @submit.prevent="handleStep1Submit" class="space-y-6">
                        <!-- Room Management -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Select Rooms</h3>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-600">
                                        Total Guests: {{ totalGuests }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="addRoom"
                                        :disabled="!canAddRoom"
                                        :class="[
                                            'inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                            canAddRoom
                                                ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        ]"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Room
                                    </button>
                                </div>
                            </div>

                            <!-- Room Cards -->
                            <div class="space-y-4">
                                <div v-for="(room, index) in form.rooms" 
                                     :key="index"
                                     class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                                    <!-- Room Header -->
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                        <h4 class="text-lg font-medium text-gray-900">Room {{ index + 1 }}</h4>
                                        <button
                                            v-if="form.rooms.length > 1"
                                            type="button"
                                            @click="removeRoom(index)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Room Content -->
                                    <div class="p-4 space-y-4">
                                        <!-- Room Type Selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                                            <div class="relative">
                                                <Swiper
                                                    :modules="[Navigation, Pagination]"
                                                    :slides-per-view="1.2"
                                                    :space-between="16"
                                                    :breakpoints="{
                                                        '640': {
                                                            slidesPerView: 2.2,
                                                            spaceBetween: 16
                                                        },
                                                        '1024': {
                                                            slidesPerView: 3.2,
                                                            spaceBetween: 16
                                                        }
                                                    }"
                                                    :navigation="true"
                                                    :pagination="{ clickable: true }"
                                                    class="room-type-swiper"
                                                >
                                                    <SwiperSlide v-for="roomType in roomTypes"
                                                                 :key="roomType.id">
                                                        <div :class="[
                                                            'h-full border rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md',
                                                            room.room_type_id === roomType.id
                                                                ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600'
                                                                : 'border-gray-200 hover:border-indigo-400'
                                                        ]"
                                                        @click="room.room_type_id = roomType.id">
                                                            <!-- Selected Indicator -->
                                                            <div v-if="room.room_type_id === roomType.id" 
                                                                 class="absolute -top-2 -right-2 bg-indigo-600 text-white rounded-full p-1 z-10">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>

                                                            <!-- Room Type Image -->
                                                            <div class="aspect-w-16 aspect-h-9 rounded-t-lg overflow-hidden bg-gray-100">
                                                                <Swiper
                                                                    :modules="[Pagination]"
                                                                    :slides-per-view="1"
                                                                    :space-between="0"
                                                                    :pagination="{ clickable: true }"
                                                                    :loop="true"
                                                                    :autoplay="{
                                                                        delay: 3000,
                                                                        disableOnInteraction: false
                                                                    }"
                                                                    class="room-image-swiper"
                                                                >
                                                                    <SwiperSlide v-for="(image, imgIndex) in roomType.images" 
                                                                                 :key="imgIndex">
                                                                        <img
                                                                            :src="getImageUrl(image)"
                                                                            :alt="`${roomType.name} - Image ${imgIndex + 1}`"
                                                                            class="w-full h-full object-cover"
                                                                            @error="e => e.target.src = '/images/placeholder.jpg'"
                                                                        />
                                                                    </SwiperSlide>
                                                                    <SwiperSlide v-if="!roomType.images?.length">
                                                                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                        </div>
                                                                    </SwiperSlide>
                                                                </Swiper>
                                                            </div>

                                                            <!-- Room Type Info -->
                                                            <div class="p-3 space-y-1">
                                                                <div class="flex items-start justify-between">
                                                                    <h5 class="font-medium text-gray-900 text-sm">{{ roomType.name }}</h5>
                                                                    <!-- <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded whitespace-nowrap">
                                                                        Max {{ roomType.max_occupancy }} pax
                                                                    </span> -->
                                                                </div>
                                                                <p class="text-xs text-gray-600 line-clamp-2">{{ roomType.description }}</p>
                                                            </div>
                                                        </div>
                                                    </SwiperSlide>
                                                </Swiper>
                                            </div>
                                            <p v-if="validationErrors.rooms?.[index]?.room_type_id" 
                                               class="mt-1 text-sm text-red-600">
                                                {{ validationErrors.rooms[index].room_type_id }}
                                            </p>
                                        </div>

                                        <!-- Guest Selection -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label :for="'adults-' + index" 
                                                       class="block text-sm font-medium text-gray-700 mb-1">
                                                    Number of Adults
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        :id="'adults-' + index"
                                                        type="number"
                                                        v-model="room.adults"
                                                        min="1"
                                                        max="4"
                                                        :class="[
                                                            'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                                            validationErrors.rooms?.[index]?.adults ? 'border-red-500' : 'border-gray-300'
                                                        ]"
                                                        @input="room.children = Math.min(room.children, getRoomMaxChildren(room))"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">/ 4</span>
                                                    </div>
                                                </div>
                                                <p v-if="validationErrors.rooms?.[index]?.adults" 
                                                   class="mt-1 text-sm text-red-600">
                                                    {{ validationErrors.rooms[index].adults }}
                                                </p>
                                            </div>
                                            <div>
                                                <label :for="'children-' + index" 
                                                       class="block text-sm font-medium text-gray-700 mb-1">
                                                    Number of Children
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        :id="'children-' + index"
                                                        type="number"
                                                        v-model="room.children"
                                                        min="0"
                                                        :max="getRoomMaxChildren(room)"
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">/ {{ getRoomMaxChildren(room) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Room Summary -->
                                        <div class="bg-gray-50 rounded-md p-3">
                                            <p class="text-sm text-gray-600">
                                                Room {{ index + 1 }}: {{ room.adults + room.children }} guests
                                                ({{ room.adults }} adults, {{ room.children }} children)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Guests Summary -->
                            <div class="bg-indigo-50 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-medium text-indigo-900">
                                        Total Guests: {{ totalGuests }}
                                    </p>
                                    <p class="text-sm text-indigo-600">
                                        {{ form.rooms.length }} {{ form.rooms.length === 1 ? 'Room' : 'Rooms' }}
                                    </p>
                                </div>
                            </div>
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

                        <!-- Calculate Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white text-base font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Total Guests</p>
                                    <p class="font-medium">
                                        {{ priceBreakdown.summary.total_adults || 0 }} Adults, 
                                        {{ priceBreakdown.summary.total_children || 0 }} Children
                                        ({{ (priceBreakdown.summary.total_adults || 0) + (priceBreakdown.summary.total_children || 0) }} Total)
                                    </p>
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
                        <div v-if="priceBreakdown?.nights" class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Price Breakdown</h3>
                            
                            <!-- Room Breakdown -->
                            <div v-for="(room, roomIndex) in priceBreakdown.rooms" :key="roomIndex" class="mb-6">
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <!-- Room Header -->
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <h4 class="text-sm font-medium text-gray-900">
                                            Room {{ roomIndex + 1 }}: {{ room.room_type_name }}
                                            <span class="text-gray-500 ml-2">
                                                ({{ room.adults }} Adults, {{ room.children }} Children)
                                            </span>
                                        </h4>
                                    </div>

                                    <!-- Nightly Breakdown Table -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Rate</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Surcharge</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="(night, nightIndex) in priceBreakdown.nights" :key="nightIndex">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                        {{ moment(night.date).format('DD MMM YYYY') }}
                                                        <span class="text-gray-500 ml-1">
                                                            ({{ night.date_type }})
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                        <span :class="[
                                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                                            night.season_type === 'peak' ? 'bg-red-100 text-red-800' :
                                                            night.season_type === 'high' ? 'bg-yellow-100 text-yellow-800' :
                                                            'bg-green-100 text-green-800'
                                                        ]">
                                                            {{ night.season_type.charAt(0).toUpperCase() + night.season_type.slice(1) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                        <div class="text-gray-900">{{ formatNumber(night.base_charge.total) }}</div>
                                                        <div class="text-xs text-gray-500">
                                                            Adult: {{ formatNumber(night.base_charge.adult.total) }}<br>
                                                            Child: {{ formatNumber(night.base_charge.child.total) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                        <div class="text-gray-900">{{ formatNumber(night.surcharge.total) }}</div>
                                                        <div class="text-xs text-gray-500">
                                                            Adult: {{ formatNumber(night.surcharge.adult.total) }}<br>
                                                            Child: {{ formatNumber(night.surcharge.child.total) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                        MYR {{ formatNumber(night.total) }}
                                                    </td>
                                                </tr>
                                                <!-- Room Summary Row -->
                                                <tr class="bg-gray-50">
                                                    <td colspan="2" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        Room {{ roomIndex + 1 }} Total
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                        <div class="text-gray-900">MYR {{ formatNumber(room.summary.base_charges.total) }}</div>
                                                        <div class="text-xs text-gray-500">
                                                            Adult: {{ formatNumber(room.summary.base_charges.adult.total) }}<br>
                                                            Child: {{ formatNumber(room.summary.base_charges.child.total) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                        <div class="text-gray-900">MYR {{ formatNumber(room.summary.surcharges.total) }}</div>
                                                        <div class="text-xs text-gray-500">
                                                            Adult: {{ formatNumber(room.summary.surcharges.adult.total) }}<br>
                                                            Child: {{ formatNumber(room.summary.surcharges.child.total) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                        MYR {{ formatNumber(room.summary.total) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Overall Summary -->
                            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Total Base Charge</p>
                                        <div class="text-lg font-medium text-gray-900">
                                            MYR {{ formatNumber(priceBreakdown.summary.base_charges.total) }}
                                            <div class="text-sm text-gray-500">
                                                Adult: {{ formatNumber(priceBreakdown.summary.base_charges.adult.total) }}<br>
                                                Child: {{ formatNumber(priceBreakdown.summary.base_charges.child.total) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Total Surcharge</p>
                                        <div class="text-lg font-medium text-gray-900">
                                            MYR {{ formatNumber(priceBreakdown.summary.surcharges.total) }}
                                            <div class="text-sm text-gray-500">
                                                Adult: {{ formatNumber(priceBreakdown.summary.surcharges.adult.total) }}<br>
                                                Child: {{ formatNumber(priceBreakdown.summary.surcharges.child.total) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Total Nights</p>
                                        <p class="text-lg font-medium text-gray-900">{{ bookingSummary.duration }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Grand Total</p>
                                        <p class="text-lg font-medium text-indigo-600">MYR {{ formatNumber(priceBreakdown.total) }}</p>
                                    </div>
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
                                    placeholder="e.g. John Doe"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        bookingValidationErrors.booking_name ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    required
                                />
                                <p v-if="bookingValidationErrors.booking_name" class="mt-1 text-sm text-red-600">
                                    {{ bookingValidationErrors.booking_name }}
                                </p>
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input
                                    type="tel"
                                    id="phone_number"
                                    v-model="bookingForm.phone_number"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        bookingValidationErrors.phone_number ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    placeholder="e.g., 60123456789"
                                    required
                                />
                                <p v-if="bookingValidationErrors.phone_number" class="mt-1 text-sm text-red-600">
                                    {{ bookingValidationErrors.phone_number }}
                                </p>
                            </div>

                            <!-- Booking IC -->
                            <div>
                                <label for="booking_ic" class="block text-sm font-medium text-gray-700">IC/Passport Number</label>
                                <input
                                    type="text"
                                    id="booking_ic"
                                    v-model="bookingForm.booking_ic"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        bookingValidationErrors.booking_ic ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    placeholder="e.g., 123456-78-9012 or A12345678"
                                    required
                                />
                                <p v-if="bookingValidationErrors.booking_ic" class="mt-1 text-sm text-red-600">
                                    {{ bookingValidationErrors.booking_ic }}
                                </p>
                            </div>

                            <!-- Special Remarks -->
                            <div>
                                <label for="special_remarks" class="block text-sm font-medium text-gray-700">Special Remarks (if any)</label>
                                <textarea
                                    id="special_remarks"
                                    v-model="bookingForm.special_remarks"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Please specify any special requests or requirements"
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
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="isSubmitting"
                                >
                                    {{ isSubmitting ? 'Submitting...' : 'Submit Booking' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Booking Success State -->
                    <div v-else class="bg-white rounded-xl border overflow-hidden">
                        <!-- Success Header -->
                        <div v-if="bookingSuccess.payment_status === 'paid'" 
                         class="relative bg-gradient-to-r slower from-green-400 via-green-500 to-green-600 bg-[length:200%] bg-[position:0%_50%] animate-gradient-x px-6 py-8 rounded-2xl shadow-xl hover:shadow-[0_0_25px_#34d399] transform transition-transform duration-100 hover:scale-105 text-white mx-8 mt-5">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-3xl font-bold text-white mb-2">Payment Completed</h2>
                                    <p class="text-white/90 text-lg">Your booking details will be sent to your email.</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="relative px-6 py-8">
                           <div class="text-center mb-6 bg-indigo-50 rounded-lg p-4">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white mb-4">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Booking Successful!</h3>
                                <p class="text-gray-600">Your booking has been submitted successfully.</p>
                                <p class="text-gray-600">Please proceed to make payment.</p>
                            </div>
                        </div>

                        <!-- Booking Summary -->
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">BOOKING REFERENCE</h4>
                                        <p class="text-lg text-gray-900">{{ bookingSuccess.uuid }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm text-gray-500 mb-3">BOOKING NAME</h4>
                                        <p class="text-lg text-gray-900">{{ bookingSuccess.booking_name }}</p>
                                            <p class="text-sm text-gray-600">Phone: {{bookingSuccess.phone_number}}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">ROOM TYPE</h4>
                                        <div class="space-y-2">
                                            <template v-for="(room, roomIndex) in bookingSuccess.rooms" :key="roomIndex">
                                                <p class="text-sm text-gray-600 font-medium">
                                                    Room {{ roomIndex + 1 }}:&nbsp; {{ room.room_type.name }} ({{ room.adults }} Adults, {{ room.children }} Children)    
                                                </p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">DURATION</h4>
                                        <p class="text-lg text-gray-900">{{ moment(bookingSuccess.end_date).diff(moment(bookingSuccess.start_date), 'days') +1 }} Days {{ moment(bookingSuccess.end_date).diff(moment(bookingSuccess.start_date), 'days') }} Nights</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">CHECK-IN / CHECK-OUT</h4>
                                        <div class="space-y-1">
                                            <p class="text-sm text-gray-600 font-medium">Check-in: {{ moment(bookingSuccess.start_date).format('DD MMM YYYY') }}</p>
                                            <p class="text-sm text-gray-600 font-medium">Check-out: {{ moment(bookingSuccess.end_date).format('DD MMM YYYY') }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">GUESTS</h4>
                                        <p class="text-lg text-gray-900">{{ bookingSuccess.adults }} Adults, {{ bookingSuccess.children }} Children</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Amount -->
                            <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-100">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-lg font-semibold text-indigo-900">Total Amount</h4>
                                    <p class="text-2xl font-bold text-indigo-600">MYR {{ formatNumber(bookingSuccess.total_price) }}</p>
                                </div>
                            </div>

                            <!-- Pay Now Button -->
                            <div v-if="bookingSuccess.payment_status !== 'paid'" class="flex justify-center pt-4">
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
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

const props = defineProps({
    uuid: String,
    booking: Object
});

onMounted(() => {
    if (props.booking) {
        bookingSuccess.value = props.booking;
        currentStep.value = 3;
        setTimeout(() => {
            const bookingForm = document.getElementById('booking-form');
            if (bookingForm) {
                bookingForm.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);
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
    rooms: [],
    start_date: '',
    end_date: ''
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

// Add booking form validation errors
const bookingValidationErrors = ref({
    booking_name: '',
    phone_number: '',
    booking_ic: ''
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
    if (packageData.value?.images?.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % packageData.value.images.length;
    }
};

const previousImage = () => {
    if (packageData.value?.images?.length > 0) {
        currentImageIndex.value = (currentImageIndex.value - 1 + packageData.value.images.length) % packageData.value.images.length;
    }
};

onMounted(async () => {
    try {
        const response = await axios.post(route('api.fetch-package-by-uuid'), {
            uuid: props.uuid,
            booking_uuid: props.booking ? props.booking.uuid : null,
        });

        if (response.data.success && response.data.package) {
            packageData.value = response.data.package;
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

const form = useForm({
    start_date: '',
    end_date: '',
    rooms: [{
        room_type_id: null,
        adults: 1,
        children: 0
    }]
});

// Add computed properties for room management
const totalGuests = computed(() => {
    return form.rooms.reduce((total, room) => total + room.adults + room.children, 0);
});

const canAddRoom = computed(() => {
    return form.rooms.length < 5; // Maximum 5 rooms
});

const addRoom = () => {
    if (canAddRoom.value) {
        form.rooms.push({
            room_type_id: null,
            adults: 1,
            children: 0
        });
    }
};

const removeRoom = (index) => {
    if (form.rooms.length > 1) {
        form.rooms.splice(index, 1);
    }
};

const getRoomMaxChildren = (room) => {
    return Math.max(0, 4 - room.adults);
};

// Update validation logic
const validateForm = () => {
    let isValid = true;
    validationErrors.value = {
        rooms: [],
        start_date: '',
        end_date: ''
    };

    // Validate each room
    form.rooms.forEach((room, index) => {
        const roomErrors = {
            room_type_id: '',
            adults: '',
            children: ''
        };

        if (!room.room_type_id) {
            roomErrors.room_type_id = 'Please select a room type';
            isValid = false;
        }

        if (!room.adults || room.adults < 1) {
            roomErrors.adults = 'Please select at least 1 adult';
            isValid = false;
        }

        if (room.adults + room.children > 4) {
            roomErrors.adults = 'Maximum 4 guests per room';
            isValid = false;
        }

        validationErrors.value.rooms[index] = roomErrors;
    });

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
            rooms: form.rooms.map(room => ({
                room_type: room.room_type_id,
                adults: room.adults,
                children: room.children
            })),
            start_date: form.start_date,
            end_date: form.end_date
        });

        if (response.data.success) {
            calculatedPrice.value = parseFloat(response.data.total) || 0;
            priceBreakdown.value = response.data;
            
            // Calculate total guests
            const totalAdults = form.rooms.reduce((sum, room) => sum + room.adults, 0);
            const totalChildren = form.rooms.reduce((sum, room) => sum + room.children, 0);
            
            // Update booking summary with total guests
            bookingSummary.value = {
                rooms: form.rooms.map(room => {
                    const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
                    return {
                        roomType: roomType?.name || '',
                        adults: room.adults,
                        children: room.children
                    };
                }),
                startDate: form.start_date,
                endDate: form.end_date,
                duration: Math.ceil((new Date(form.end_date) - new Date(form.start_date)) / (1000 * 60 * 60 * 24)),
                seasonType: response.data.season_type || '',
                dateType: response.data.date_type || '',
                isWeekend: response.data.is_weekend || false,
                totalAdults,
                totalChildren
            };

            // Add total guests to price breakdown summary
            priceBreakdown.value.summary = {
                ...priceBreakdown.value.summary,
                total_adults: totalAdults,
                total_children: totalChildren
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

const validateBookingForm = () => {
    let isValid = true;
    bookingValidationErrors.value = {
        booking_name: '',
        phone_number: '',
        booking_ic: ''
    };

    // Validate booking name
    if (!bookingForm.value.booking_name.trim()) {
        bookingValidationErrors.value.booking_name = 'Booking name is required';
        isValid = false;
    }

    // Validate phone number (Malaysian format)
    // const phoneRegex = /^(?:\+?60|0)[1-9]\d{8,9}$/;
    // if (!bookingForm.value.phone_number.trim()) {
    //     bookingValidationErrors.value.phone_number = 'Phone number is required';
    //     isValid = false;
    // } else if (!phoneRegex.test(bookingForm.value.phone_number.trim())) {
    //     bookingValidationErrors.value.phone_number = 'Please enter a valid Malaysian phone number';
    //     isValid = false;
    // }

    // // Validate IC/Passport
    // const icRegex = /^[A-Z0-9]{6,12}$/;
    // if (!bookingForm.value.booking_ic.trim()) {
    //     bookingValidationErrors.value.booking_ic = 'IC/Passport number is required';
    //     isValid = false;
    // } else if (!icRegex.test(bookingForm.value.booking_ic.trim().toUpperCase())) {
    //     bookingValidationErrors.value.booking_ic = 'Please enter a valid IC/Passport number';
    //     isValid = false;
    // }

    return isValid;
};

const submitBooking = async () => {
    if (!validateBookingForm()) return;

    try {
        isSubmitting.value = true;
        const response = await axios.post(route('api.bookings.store'), {
            package_id: packageData.value.id,
            rooms: form.rooms.map(room => ({
                room_type_id: room.room_type_id,
                adults: room.adults,
                children: room.children
            })),
            booking_name: bookingForm.value.booking_name,
            phone_number: bookingForm.value.phone_number,
            booking_ic: bookingForm.value.booking_ic,
            start_date: form.start_date,
            end_date: form.end_date,
            total_price: priceBreakdown.value.total,
            special_remarks: bookingForm.value.special_remarks.trim()
        });

        if (response.data.success) {
            bookingSuccess.value = response.data.booking;
            const url = new URL(window.location);
            url.searchParams.set('booking', response.data.booking.uuid);
            window.history.replaceState({}, '', url);
        } else {
            throw new Error(response.data.message || 'Failed to create booking');
        }
    } catch (error) {
        console.error('Booking error:', error);
        
        // Handle validation errors from backend
        if (error.response?.data?.errors) {
            const backendErrors = error.response.data.errors;
            Object.keys(backendErrors).forEach(key => {
                if (bookingValidationErrors.value.hasOwnProperty(key)) {
                    bookingValidationErrors.value[key] = backendErrors[key][0];
                }
            });
        } else {
            // Show general error message
            await Swal.fire({
                icon: 'error',
                title: 'Booking Failed',
                text: error.response?.data?.message || 'Failed to create booking. Please try again.',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#EF4444'
            });
        }
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
            window.location.href = route('api.payment.show', bookingSuccess.value.uuid);
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

// Add a computed property for max start date
const maxStartDate = computed(() => {
    if (!packageData.value) return '';
    const packageEndDate = new Date(packageData.value.package_end_date);
    const maxDate = new Date(packageEndDate);
    maxDate.setDate(packageEndDate.getDate() - packageData.value.package_max_days);
    return maxDate.toISOString().split('T')[0];
});

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith('http')) return imagePath;
    return `/images/${imagePath}`;
};

const getRoomTypeCounts = (rooms) => {
    const counts = {};
    rooms.forEach(room => {
        const roomTypeName = room.room_type.name;
        counts[roomTypeName] = (counts[roomTypeName] || 0) + 1;
    });
    return counts;
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

/* Add styles for the promo badge */
.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradient 0.5s ease infinite;
}

/* Define the slow gradient animation */
.bg-gradient-to-r.slower {
    animation: gradient 2s ease infinite;
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

.swiper-button-next,
.swiper-button-prev {
    color: #4F46E5 !important;
    background: rgba(255, 255, 255, 0.8);
    padding: 1.5rem;
    border-radius: 50%;
    width: 2rem !important;
    height: 2rem !important;
}

.swiper-button-next:after,
.swiper-button-prev:after {
    font-size: 1rem !important;
}

.swiper-pagination-bullet-active {
    background: #4F46E5 !important;
}

/* Add styles for aspect ratio */
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
}

.aspect-w-16 > * {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

/* Add line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom styles for room type swiper */
.room-type-swiper {
    padding: 0.5rem 0;
    margin: -0.5rem -1rem;
    padding-left: 1rem;
    padding-right: 1rem;
}

.room-type-swiper :deep(.swiper-button-next),
.room-type-swiper :deep(.swiper-button-prev) {
    color: #4F46E5;
    background: white;
    width: 2rem !important;
    height: 2rem !important;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.room-type-swiper :deep(.swiper-button-next:after),
.room-type-swiper :deep(.swiper-button-prev:after) {
    font-size: 1rem !important;
}

.room-type-swiper :deep(.swiper-button-disabled) {
    opacity: 0.35;
    cursor: auto;
    pointer-events: none;
}

.room-type-swiper :deep(.swiper-pagination) {
    bottom: -1.5rem;
}

.room-type-swiper :deep(.swiper-pagination-bullet) {
    background: #4F46E5;
    opacity: 0.3;
}

.room-type-swiper :deep(.swiper-pagination-bullet-active) {
    opacity: 1;
}

/* Ensure slides have proper height */
.room-type-swiper :deep(.swiper-slide) {
    height: auto;
}

/* Simplified styles for room image swiper */
.room-image-swiper {
    height: 100%;
}

.room-image-swiper :deep(.swiper-pagination) {
    bottom: 0.5rem;
}

.room-image-swiper :deep(.swiper-pagination-bullet) {
    background: white;
    opacity: 0.5;
    width: 0.5rem;
    height: 0.5rem;
}

.room-image-swiper :deep(.swiper-pagination-bullet-active) {
    opacity: 1;
}

/* Remove navigation button styles since we're not using them anymore */
</style>
