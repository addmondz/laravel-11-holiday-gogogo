<template>
    <div class="min-h-screen bg-gray-100">
    <Head :title="packageData?.name ?? 'Travel Package'" />
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
        <div v-else class="max-w-7xl mx-auto py-12 px-0 sm:px-4 sm:px-6 lg:px-8 pb-32 sm:pb-12 quoataion-extra-upper-padding">
            <!-- Package Header with Images and Details -->
            <div class="bg-white rounded-none sm:rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="flex flex-col lg:flex-row">
                    <!-- Left Side - Images -->
                    <div class="lg:w-1/3 p-0 sm:p-6 md:p-4">
                        <div class="relative">
                            <template v-if="packageData.images && packageData.images.length > 0">
                                <!-- Main Image - Enlarge icon opens lightbox (desktop only) -->
                                <div 
                                    class="relative mb-4 group touch-none package-image-wrapper" 
                                    style="height: 300px;"
                                >
                                    <img
                                        v-for="(image, index) in packageData.images"
                                        :key="'package-image-' + index"
                                        :src="getImageUrl(image)"
                                        :class="[
                                            'absolute inset-0 w-full h-full object-cover transition-opacity duration-500 rounded-none sm:rounded-lg',
                                            currentImageIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'
                                        ]"
                                        :alt="packageData?.name || 'Package Image'"
                                    />
                                    <!-- Enlarge Icon Overlay - Clickable to open lightbox (desktop only) -->
                                    <div 
                                        class="hidden sm:flex absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300 items-center justify-center z-30 pointer-events-none"
                                    >
                                        <button 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/90 rounded-full p-3 shadow-lg cursor-pointer pointer-events-auto"
                                            @click="openPackageImageLightbox(currentImageIndex)"
                                            aria-label="Enlarge image"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Navigation Buttons (Mobile only - hidden on desktop since image opens modal) -->
                                    <button
                                        v-if="packageData.images.length > 1"
                                        @click.stop="previousImage"
                                        class="sm:hidden absolute left-2 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full p-2 z-20 shadow-lg"
                                        aria-label="Previous image"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button
                                        v-if="packageData.images.length > 1"
                                        @click.stop="nextImage"
                                        class="sm:hidden absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-white/80 hover:bg-white rounded-full p-2 shadow-lg"
                                        aria-label="Next image"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Thumbnail Navigation -->
                                <div v-if="packageData.images.length > 1" class="flex space-x-2 overflow-x-auto pb-2">
                                    <button
                                        v-for="(image, index) in packageData.images"
                                        :key="index"
                                        @click.stop="currentImageIndex = index"
                                        @touchend.stop="currentImageIndex = index"
                                        @dblclick="handlePackageThumbnailDoubleClick(index)"
                                        @touchstart.prevent
                                        :class="[
                                            'flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-lg overflow-hidden border-2 transition-all duration-200 cursor-pointer touch-none',
                                            currentImageIndex === index ? 'border-indigo-500 ring-2 ring-indigo-300' : 'border-gray-200 hover:border-indigo-300'
                                        ]"
                                        :aria-label="`Go to image ${index + 1}`"
                                    >
                                        <img
                                            :src="getImageUrl(image)"
                                            :alt="`Thumbnail ${index + 1}`"
                                            class="w-full h-full object-cover"
                                        />
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="h-64 flex items-center justify-center bg-gray-100 rounded-lg">
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

                    <!-- Right Side - Package Details -->
                    <div class="lg:w-2/3 p-4 sm:p-6">
                        <div class="h-full flex flex-col">
                            <!-- Package Title and Promo Badge -->
                            <div class="mb-4">
                                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2 flex flex-col sm:flex-row sm:items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 text-white mb-2 sm:mb-0 sm:mr-3">
                                        {{ computedPromoPeriod }} Promo
                                    </span>
                                    {{ packageData.name }}
                                </h1>
                                <p class="text-gray-600 text-sm sm:text-md">{{ packageData.description }}</p>
                            </div>

                            <!-- Package Information Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Location
                                    </h2>
                                    <p class="text-gray-700">{{ packageData.location }}</p>
                                </div>
                                <div v-if="packageData.wordpress_link" class="bg-gray-50 rounded-lg p-4">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Package Details
                                    </h2>
                                    <a :href="packageData.wordpress_link" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-900 underline break-all text-sm cursor-pointer no-underline">
                                        <!-- {{ packageData.wordpress_link }} -->
                                        Click to view package details
                                    </a>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Duration
                                    </h2>
                                    <p class="text-gray-700">{{ packageData.package_max_days +1 }} Days {{ packageData.package_max_days }} {{packageData.package_max_days > 1 ? 'Nights' : 'Night'}}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Travel Period
                                    </h2>
                                    <p class="text-gray-700">{{ moment(packageData.package_start_date).format('DD MMM YYYY') }} - {{ moment(packageData.package_end_date).format('DD MMM YYYY') }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                        Package Price From
                                    </h2>
                                    <p class="text-2xl font-bold text-indigo-600">MYR {{ packageData?.display_price_adult ? formatNumber(packageData.display_price_adult) : '0.00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6" id="booking-form">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6">{{ currentStep === 4 ? 'Your Booking' : 'Book Your Stay' }}</h2>
                
                <!-- Step Indicators -->
                <div class="mb-8">
                    <!-- Mobile: Show only numbers with tooltips -->
                    <div class="flex items-center justify-between md:hidden">
                        <!-- Step 1 -->
                        <div class="flex items-center flex-1 justify-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs', currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']" :title="currentStep >= 1 ? 'Select Room and Date' : ''">1</div>
                        </div>
                        <div class="flex-1 mx-2 h-0.5" :class="isStepActive(2) ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        <!-- Step 2: Add-ons (conditionally shown) -->
                        <template v-if="hasAddOns">
                            <div class="flex items-center flex-1 justify-center">
                                <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs', currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']" :title="currentStep >= 2 ? 'Choose Add-ons' : ''">2</div>
                            </div>
                            <div class="flex-1 mx-2 h-0.5" :class="currentStep >= 3 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        </template>
                        <!-- Step 3: Price Summary -->
                        <div class="flex items-center flex-1 justify-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs', isStepActive(3) ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']" :title="currentStep >= 3 ? 'Price Summary' : ''">{{ hasAddOns ? '3' : '2' }}</div>
                        </div>
                        <div class="flex-1 mx-2 h-0.5" :class="currentStep >= 4 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        <!-- Step 4: Booking Details -->
                        <div class="flex items-center flex-1 justify-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs', currentStep >= 4 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']" :title="currentStep >= 4 ? 'Booking Details' : ''">{{ hasAddOns ? '4' : '3' }}</div>
                        </div>
                    </div>
                    <!-- Desktop: Show full labels -->
                    <div class="hidden md:flex items-center justify-between">
                        <!-- Step 1 -->
                        <div class="flex items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">1</div>
                            <div class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-indigo-600' : 'text-gray-500'">Select Room and Date</div>
                        </div>
                        <div class="flex-1 mx-4 h-0.5" :class="isStepActive(2) ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        <!-- Step 2: Add-ons (conditionally shown) -->
                        <template v-if="hasAddOns">
                            <div class="flex items-center">
                                <div :class="['w-8 h-8 rounded-full flex items-center justify-center', currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">2</div>
                                <div class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-indigo-600' : 'text-gray-500'">Choose Add-ons</div>
                            </div>
                            <div class="flex-1 mx-4 h-0.5" :class="currentStep >= 3 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        </template>
                        <!-- Step 3: Price Summary -->
                        <div class="flex items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', isStepActive(3) ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">{{ hasAddOns ? '3' : '2' }}</div>
                            <div class="ml-2 text-sm font-medium" :class="isStepActive(3) ? 'text-indigo-600' : 'text-gray-500'">Price Summary</div>
                        </div>
                        <div class="flex-1 mx-4 h-0.5" :class="currentStep >= 4 ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                        <!-- Step 4: Booking Details -->
                        <div class="flex items-center">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', currentStep >= 4 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">{{ hasAddOns ? '4' : '3' }}</div>
                            <div class="ml-2 text-sm font-medium" :class="currentStep >= 4 ? 'text-indigo-600' : 'text-gray-500'">Booking Details</div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Room Selection -->
                <div v-if="currentStep === 1">
                    <form @submit.prevent="handleStep1Submit" class="space-y-6">
                        <!-- Room Management -->
                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                <h3 class="text-lg font-medium text-gray-900">Select Rooms</h3>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                                    <span class="text-sm text-gray-600 whitespace-nowrap">
                                        Total Guests: {{ totalGuests }}
                                    </span>
                                </div>
                            </div>

                            <!-- Room Cards -->
                            <div class="space-y-4">
                                <div v-for="(room, index) in form.rooms" 
                                     :key="index"
                                     class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden" style="margin-left: -15px; margin-right: -15px;">
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

                                                            <!-- Room Type Image - Enlarge icon opens lightbox -->
                                                            <div 
                                                                class="aspect-w-16 aspect-h-9 rounded-t-lg overflow-hidden bg-gray-100 group relative"
                                                                @touchstart="(e) => { roomTypeTapStartTime = Date.now(); roomTypeTapStartX = e.touches[0].clientX; roomTypeTapStartY = e.touches[0].clientY; }"
                                                                @touchend="handleRoomTypeImageTap(roomType, $event)"
                                                            >
                                                                <Swiper
                                                                    :modules="[Pagination]"
                                                                    :slides-per-view="1"
                                                                    :space-between="0"
                                                                    :pagination="{ clickable: false }"
                                                                    :loop="true"
                                                                    :autoplay="{
                                                                        delay: 3000,
                                                                        disableOnInteraction: false
                                                                    }"
                                                                    :allow-touch-move="false"
                                                                    class="room-image-swiper"
                                                                >
                                                                    <SwiperSlide v-for="(image, imgIndex) in roomType.images" 
                                                                                 :key="imgIndex"
                                                                                 @touchend="handleRoomTypeImageTap(roomType, $event)">
                                                                        <img
                                                                            :src="getImageUrl(image)"
                                                                            :alt="`${roomType.name} - Image ${imgIndex + 1}`"
                                                                            class="w-full h-full object-cover pointer-events-none"
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
                                                                <!-- Enlarge Icon Overlay - Clickable to open lightbox -->
                                                                <div 
                                                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300 flex items-center justify-center z-10 pointer-events-none"
                                                                >
                                                                    <button 
                                                                        class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/90 rounded-full p-2 shadow-lg cursor-pointer pointer-events-auto"
                                                                        @click="openRoomTypeImageLightbox(roomType, 0)"
                                                                        aria-label="Enlarge image"
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Room Type Info -->
                                                            <div class="p-3 space-y-1">
                                                                <div class="flex items-start justify-between">
                                                                    <h5 class="font-medium text-gray-900 text-sm">{{ roomType.name }}</h5>
                                                                    <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded whitespace-nowrap">
                                                                        Max {{ roomType.max_occupancy }} pax
                                                                    </span>
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
                                        <div v-if="room.room_type_id" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label :for="'adults-' + index" 
                                                       class="block text-sm font-medium text-gray-700 mb-1">
                                                    Number of Adults
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        :id="'adults-' + index"
                                                        type="number"
                                                        v-model.number="room.adults"
                                                        min="1"
                                                        :max="getRoomMaxAdults(room)"
                                                        :class="[
                                                            'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                                            validationErrors.rooms?.[index]?.adults ? 'border-red-500' : 'border-gray-300'
                                                        ]"
                                                        @change="handleAdultsChange(room, index)"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">/ {{ getRoomMaxAdults(room) }}</span>
                                                    </div>
                                                </div>
                                                <p v-if="validationErrors.rooms?.[index]?.adults" 
                                                   class="mt-1 text-sm text-red-600">
                                                    {{ validationErrors.rooms[index].adults }}
                                                </p>
                                                <p v-if="helperMessages[index]?.adults" 
                                                   class="mt-1 text-sm text-indigo-600 animate-fade-in">
                                                    {{ helperMessages[index].adults }}
                                                </p>
                                            </div>
                                            <div v-if="!packageData?.no_children_and_infant">
                                                <label :for="'children-' + index" 
                                                       class="block text-sm font-medium text-gray-700 mb-1">
                                                    Number of Children 
                                                    <span class="text-xs text-gray-500" v-if="packageData.child_max_age_desc">
                                                        ({{ packageData.child_max_age_desc }})
                                                    </span>
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        :id="'children-' + index"
                                                        type="number"
                                                        v-model.number="room.children"
                                                        min="0"
                                                        :max="getRoomMaxChildren(room)"
                                                        :class="[
                                                            'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                                            validationErrors.rooms?.[index]?.children ? 'border-red-500' : 'border-gray-300'
                                                        ]"
                                                        @change="handleChildrenChange(room, index)"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">/ {{ getRoomMaxChildren(room) }}</span>
                                                    </div>
                                                </div>
                                                <p v-if="validationErrors.rooms?.[index]?.children" 
                                                   class="mt-1 text-sm text-red-600">
                                                    {{ validationErrors.rooms[index].children }}
                                                </p>
                                                <p v-if="helperMessages[index]?.children" 
                                                   class="mt-1 text-sm text-indigo-600 animate-fade-in">
                                                    {{ helperMessages[index].children }}
                                                </p>
                                            </div>
                                            <div v-if="!packageData?.no_children_and_infant">
                                                <label :for="'infants-' + index" 
                                                       class="block text-sm font-medium text-gray-700 mb-1">
                                                    Number of Infants 
                                                    <span class="text-xs text-gray-500" v-if="packageData.infant_max_age_desc">
                                                        ({{ packageData.infant_max_age_desc }})
                                                    </span>
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        :id="'infants-' + index"
                                                        type="number"
                                                        v-model.number="room.infants"
                                                        min="0"
                                                        :max="getRoomMaxInfants(room)"
                                                        :class="[
                                                            'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                                            validationErrors.rooms?.[index]?.infants ? 'border-red-500' : 'border-gray-300'
                                                        ]"
                                                        @change="handleInfantsChange(room, index)"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">/ {{ getRoomMaxInfants(room) }}</span>
                                                    </div>
                                                </div>
                                                <p v-if="validationErrors.rooms?.[index]?.infants" 
                                                   class="mt-1 text-sm text-red-600">
                                                    {{ validationErrors.rooms[index].infants }}
                                                </p>
                                                <p v-if="helperMessages[index]?.infants" 
                                                   class="mt-1 text-sm text-indigo-600 animate-fade-in">
                                                    {{ helperMessages[index].infants }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Room Summary -->
                                        <div v-if="room.room_type_id" class="bg-gray-50 rounded-md p-3">
                                            <p class="text-sm text-gray-600">
                                                Room {{ index + 1 }}: {{ (room.adults || 0) + (room.children || 0) + (room.infants || 0) }} guests
                                                ({{ room.adults }} adults<span v-if="!packageData?.no_children_and_infant">, {{ room.children }} children, {{ room.infants }} infants</span>)
                                            </p>
                                        </div>

                                        <!-- Room Type Not Selected Message -->
                                        <div v-if="!room.room_type_id" class="bg-blue-50 rounded-md p-3 border border-blue-200">
                                            <p class="text-sm text-blue-700">
                                                Please select a room type above to configure guest details
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

                        <!-- Action Buttons -->
                        <div class="flex flex-row justify-between gap-3">
                            <button
                                type="button"
                                @click="addRoom"
                                :disabled="!canAddRoom"
                                :class="[
                                    'inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 text-sm sm:text-base font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 flex-1 sm:flex-initial',
                                    canAddRoom
                                        ? 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                            >
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="truncate">Add Room</span>
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-4 py-2.5 sm:px-6 sm:py-3 bg-indigo-600 text-white text-sm sm:text-base font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex-1 sm:flex-initial"
                                :disabled="form.processing"
                            >
                                Calculate Price
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Choose Add-ons -->
                <div v-if="currentStep === 2 && hasAddOns">
                    <div class="space-y-2">
                        <div class="mb-2">
                            <h3 class="text-base font-semibold text-gray-900">Choose Your Add-ons</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Select add-ons and enter the number of guests for each.</p>
                        </div>

                        <!-- Add-ons Table -->
                        <div v-if="packageAddOns.length > 0" class="bg-white border border-gray-200 rounded-md overflow-visible">
                            <!-- Desktop Table Header -->
                            <div class="hidden sm:grid grid-cols-12 gap-2 bg-gray-50 border-b border-gray-200 px-2 py-1.5 text-xs font-medium text-gray-700">
                                <div class="col-span-3 flex items-center gap-1.5">
                                    <span>Add-on</span>
                                </div>
                                <div class="col-span-2 text-center">Adult Price</div>
                                <div v-if="!packageData?.no_children_and_infant" class="col-span-2 text-center">Child Price</div>
                                <div v-if="!packageData?.no_children_and_infant" class="col-span-2 text-center">Infant Price</div>
                                <div :class="packageData?.no_children_and_infant ? 'col-span-6' : 'col-span-2'" class="text-right">Subtotal</div>
                                <div v-if="!packageData?.no_children_and_infant" class="col-span-1"></div>
                            </div>
                            
                            <!-- Desktop Table Rows -->
                            <div class="divide-y divide-gray-100">
                                <div v-for="addOn in packageAddOns" :key="addOn.id" 
                                     class="grid grid-cols-12 gap-2 px-2 py-1.5 transition-colors"
                                     :class="selectedAddOns.includes(addOn.id) ? 'bg-indigo-50 hover:bg-indigo-100' : 'hover:bg-gray-50'">
                                    <!-- Checkbox and Name with Info -->
                                    <div class="col-span-12 sm:col-span-3 flex items-center gap-1.5 min-w-0 mb-2 sm:mb-0">
                                        <!-- <input type="checkbox" 
                                               :value="addOn.id" 
                                               v-model="selectedAddOns"
                                               class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer flex-shrink-0"> -->
                                        <div class="min-w-0 flex-1 flex items-center gap-1">
                                            <h4 class="text-xs font-medium text-gray-900 truncate">{{ addOn.name }}</h4>
                                            <div class="relative group flex-shrink-0 addon-info-button">
                                                <button 
                                                    @click.stop="toggleAddOnDescription(addOn.id)"
                                                    @mouseenter="showAddOnTooltip = addOn.id"
                                                    @mouseleave="showAddOnTooltip = null"
                                                    class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-colors relative z-10"
                                                    type="button">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                                <!-- Tooltip/Popover -->
                                                <div v-if="showAddOnTooltip === addOn.id || openAddOnDescriptions[addOn.id]" 
                                                     class="absolute left-0 top-full mt-1 z-[9999] w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg pointer-events-auto"
                                                     :class="{'pointer-events-auto': openAddOnDescriptions[addOn.id]}"
                                                     @click.stop
                                                     @mouseenter="handleTooltipMouseEnter(addOn.id)"
                                                     @mouseleave="handleTooltipMouseLeave(addOn.id)">
                                                    <div class="font-medium mb-1">{{ addOn.name }}</div>
                                                    <div class="text-gray-300 whitespace-normal">{{ addOn.description || 'No description available' }}</div>
                                                    <div v-if="openAddOnDescriptions[addOn.id]" class="mt-2 pt-2 border-t border-gray-700">
                                                        <button @click.stop="toggleAddOnDescription(addOn.id)" class="text-xs text-indigo-300 hover:text-indigo-200">
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Mobile Layout: Stacked Cards -->
                                    <div class="col-span-12 sm:hidden space-y-2 pb-2">
                                        <!-- Adult -->
                                        <div v-if="addOn.adult_price" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-700">Adult:</span>
                                                <span class="text-xs text-gray-900">MYR {{ formatNumber(addOn.adult_price) }}</span>
                                            </div>
                                            <input type="number" 
                                                   :min="0" 
                                                   :max="totalGuests"
                                                   v-model="getAddOnPax(addOn.id).adults"
                                                   @input="updateAddOnSelection(addOn.id)"
                                                   placeholder="0"
                                                   class="w-16 px-2 py-1 text-xs border border-gray-300 rounded text-center focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <!-- Child -->
                                        <div v-if="!packageData?.no_children_and_infant && addOn.child_price" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-700">Child:</span>
                                                <span class="text-xs text-gray-900">MYR {{ formatNumber(addOn.child_price) }}</span>
                                            </div>
                                            <input type="number" 
                                                   :min="0" 
                                                   :max="totalGuests"
                                                   v-model="getAddOnPax(addOn.id).children"
                                                   @input="updateAddOnSelection(addOn.id)"
                                                   placeholder="0"
                                                   class="w-16 px-2 py-1 text-xs border border-gray-300 rounded text-center focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <!-- Infant -->
                                        <div v-if="!packageData?.no_children_and_infant && addOn.infant_price" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-700">Infant:</span>
                                                <span class="text-xs text-gray-900">MYR {{ formatNumber(addOn.infant_price) }}</span>
                                            </div>
                                            <input type="number" 
                                                   :min="0" 
                                                   :max="totalGuests"
                                                   v-model="getAddOnPax(addOn.id).infants"
                                                   @input="updateAddOnSelection(addOn.id)"
                                                   placeholder="0"
                                                   class="w-16 px-2 py-1 text-xs border border-gray-300 rounded text-center focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                        <!-- Subtotal Mobile -->
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                            <span class="text-xs font-medium text-gray-600">Subtotal:</span>
                                            <span class="text-xs font-semibold text-indigo-600">
                                                MYR {{ formatNumber(calculateAddOnSubtotal(addOn)) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Desktop Layout: Table Columns -->
                                    <!-- Adult Price and Input -->
                                    <div class="hidden sm:flex sm:col-span-2 flex-col items-center gap-1">
                                        <div v-if="addOn.adult_price" class="text-xs font-medium text-gray-900">
                                            MYR {{ formatNumber(addOn.adult_price) }}
                                        </div>
                                        <div v-else class="text-xs text-gray-400">-</div>
                                        <div v-if="addOn.adult_price" class="w-full">
                                            <input type="number" 
                                                   :min="0" 
                                                   :max="totalGuests"
                                                   v-model="getAddOnPax(addOn.id).adults"
                                                   @input="updateAddOnSelection(addOn.id)"
                                                   placeholder="0"
                                                   class="w-full px-1 py-0.5 text-xs border border-gray-300 rounded text-center focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    
                                    <!-- Child Price and Input -->
                                    <div v-if="!packageData?.no_children_and_infant" class="hidden sm:flex sm:col-span-2 flex-col items-center gap-1">
                                        <div v-if="addOn.child_price" class="text-xs font-medium text-gray-900">
                                            MYR {{ formatNumber(addOn.child_price) }}
                                        </div>
                                        <div v-else class="text-xs text-gray-400">-</div>
                                        <div v-if="addOn.child_price" class="w-full">
                                            <input type="number" 
                                                   :min="0" 
                                                   :max="totalGuests"
                                                   v-model="getAddOnPax(addOn.id).children"
                                                   @input="updateAddOnSelection(addOn.id)"
                                                   placeholder="0"
                                                   class="w-full px-1 py-0.5 text-xs border border-gray-300 rounded text-center focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    
                                    <!-- Infant Price and Input -->
                                    <div v-if="!packageData?.no_children_and_infant" class="hidden sm:flex sm:col-span-2 flex-col items-center gap-1">
                                        <div v-if="addOn.infant_price" class="text-xs font-medium text-gray-900">
                                            MYR {{ formatNumber(addOn.infant_price) }}
                                        </div>
                                        <div v-else class="text-xs text-gray-400">-</div>
                                        <div v-if="addOn.infant_price" class="w-full">
                                            <input type="number" 
                                                   :min="0" 
                                                   :max="totalGuests"
                                                   v-model="getAddOnPax(addOn.id).infants"
                                                   @input="updateAddOnSelection(addOn.id)"
                                                   placeholder="0"
                                                   class="w-full px-1 py-0.5 text-xs border border-gray-300 rounded text-center focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    
                                    <!-- Subtotal Desktop -->
                                    <div :class="packageData?.no_children_and_infant ? 'hidden sm:flex sm:col-span-6' : 'hidden sm:flex sm:col-span-2'" class="items-center justify-end">
                                        <span class="text-xs font-semibold text-indigo-600">
                                            MYR {{ formatNumber(calculateAddOnSubtotal(addOn)) }}
                                        </span>
                                    </div>
                                    <!-- Empty spacer -->
                                    <div class="hidden sm:block sm:col-span-1"></div>
                                </div>
                            </div>
                        </div>

                        <!-- No Add-ons Available -->
                        <div v-else class="text-center py-6">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                            </svg>
                            <h3 class="mt-1.5 text-xs font-medium text-gray-900">No Add-ons Available</h3>
                            <p class="mt-0.5 text-xs text-gray-500">This package doesn't have any add-ons available.</p>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between mt-4">
                            <button
                                @click="currentStep = 1"
                                class="px-8 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                Back
                            </button>
                            <button
                                @click="handleStep2Submit"
                                class="px-8 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Price Summary -->
                <div v-if="currentStep === 3">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <!-- Booking Summary -->
                        <div v-if="bookingSummary && priceBreakdown?.summary" class="mb-6 border-b pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Summary</h3>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Total Guests</p>
                                    <p class="font-medium">
                                        {{ priceBreakdown.summary.total_adults || 0 }} Adults<span v-if="!packageData?.no_children_and_infant">, 
                                        {{ priceBreakdown.summary.total_children || 0 }} Children,
                                        {{ priceBreakdown.summary.total_infants || 0 }} Infants</span>
                                        ({{ (priceBreakdown.summary.total_adults || 0) + (priceBreakdown.summary.total_children || 0) + (priceBreakdown.summary.total_infants || 0) }} Total)
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Duration</p>
                                    <p class="font-medium">{{ bookingSummary.duration }} nights</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Check-in / Check-out</p>
                                    <p class="font-medium">{{ moment(bookingSummary.startDate).format('DD MMM YYYY') }} / {{ moment(bookingSummary.endDate).format('DD MMM YYYY') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Rooms</p>
                                    <p class="font-medium">{{ bookingSummary.rooms.length }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Nightly Breakdown -->
                        <div v-if="priceBreakdown?.nights" class="mt-6">
                            <!-- <div class="nightly-breakdown"> Extra HIDDEN SUMMARY DETAILS -->
                            <div class="nightly-breakdown hidden"> <!-- Extra HIDDEN SUMMARY DETAILS -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Price Breakdown by Rooms</h3>
                                <!-- Room Breakdown -->
                                <div v-for="(room, roomIndex) in priceBreakdown.rooms" :key="roomIndex" class="mb-6">
                                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                        <!-- Room Header -->
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                Room {{ roomIndex + 1 }}: {{ room.room_type_name }}
                                                <span class="text-gray-500 ml-2">
                                                    ({{ room.adults ?? 0 }} Adults, {{ room.children ?? 0 }} Children, {{ room.infants ?? 0 }} Infants)
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
                                                    <tr v-for="(night, nightIndex) in room.nights" :key="nightIndex">
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
                                                                Child: {{ formatNumber(night.base_charge.child.total) }}<br>
                                                                Infant: {{ formatNumber(night.base_charge.infant.total) }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                            <div class="text-gray-900">{{ formatNumber(night.surcharge.total) }}</div>
                                                            <div class="text-xs text-gray-500">
                                                                Adult: {{ formatNumber(night.surcharge.adult.total) }}<br>
                                                                Child: {{ formatNumber(night.surcharge.child.total) }}<br>
                                                                Infant: {{ formatNumber(night.surcharge.infant.total) }}
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
                                                                Child: {{ formatNumber(room.summary.base_charges.child.total) }}<br>
                                                                Infant: {{ formatNumber(room.summary.base_charges.infant.total) }}
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                                            <div class="text-gray-900">MYR {{ formatNumber(room.summary.surcharges.total) }}</div>
                                                            <div class="text-xs text-gray-500">
                                                                Adult: {{ formatNumber(room.summary.surcharges.adult.total) }}<br>
                                                                Child: {{ formatNumber(room.summary.surcharges.child.total) }}<br>
                                                                Infant: {{ formatNumber(room.summary.base_charges.infant.total) }}
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
                            </div>

                            <!-- Guest Breakdown -->
                            <div v-if="priceBreakdown?.guest_breakdown" class="mt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Guest Breakdown</h3>
                                
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <!-- Guest Breakdown Table -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nights</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Total</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Surcharge Total</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="(guest, guestKey) in priceBreakdown.guest_breakdown" :key="guestKey" class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        Room {{ guest.room_number }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                        {{ guest.guest_type.charAt(0).toUpperCase() + guest.guest_type.slice(1) }} {{ guest.guest_number }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                            {{ guest.guest_type.charAt(0).toUpperCase() + guest.guest_type.slice(1) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                        {{ guest.room_type_name }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                        {{ guest.nights }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                        MYR {{ formatNumber(guest.base_charge.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                        MYR {{ formatNumber(guest.surcharge.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-600 text-right">
                                                        MYR {{ formatNumber(guest.total) }}
                                                    </td>
                                                </tr>
                                                
                                                <!-- Guest Type Summary Rows -->
                                                <tr v-if="priceBreakdown.summary.total_adults > 0" class="bg-indigo-50">
                                                    <td colspan="4" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900">
                                                        Total Adults ({{ priceBreakdown.summary.total_adults }})
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.base_charges.adult.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-900 text-right">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.surcharges.adult.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.base_charges.adult.total + priceBreakdown.summary.surcharges.adult.total) }}
                                                    </td>
                                                </tr>
                                                
                                                <tr v-if="priceBreakdown.summary.total_children > 0" class="bg-indigo-50">
                                                    <td colspan="5" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900">
                                                        Total Children ({{ priceBreakdown.summary.total_children }})
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-900 text-right">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.base_charges.child.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-900 text-right">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.surcharges.child.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.base_charges.child.total + priceBreakdown.summary.surcharges.child.total) }}
                                                    </td>
                                                </tr>
                                                
                                                <tr v-if="priceBreakdown.summary.total_infants > 0" class="bg-indigo-50">
                                                    <td colspan="5" class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900">
                                                        Total Infants ({{ priceBreakdown.summary.total_infants }})
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-900 text-right">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.base_charges.infant.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-900 text-right">
                                                        -
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.surcharges.infant.total) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-indigo-900 text-right">
                                                        MYR {{ formatNumber(priceBreakdown.summary.base_charges.infant.total + priceBreakdown.summary.surcharges.infant.total) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Overall Summary -->
                            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                                <!-- <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="hidden">
                                        <p class="text-sm text-gray-500">Total Base Charge</p>
                                        <div class="text-lg font-medium text-gray-900">
                                            MYR {{ formatNumber(priceBreakdown.summary.base_charges.total) }}
                                            <div class="text-sm text-gray-500">
                                                Adult: {{ formatNumber(priceBreakdown.summary.base_charges.adult.total) }}<br>
                                                Child: {{ formatNumber(priceBreakdown.summary.base_charges.child.total) }}<br>
                                                Infant: {{ formatNumber(priceBreakdown.summary.base_charges.infant.total) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hidden">
                                        <p class="text-sm text-gray-500">Total Surcharge</p>
                                        <div class="text-lg font-medium text-gray-900">
                                            MYR {{ formatNumber(priceBreakdown.summary.surcharges.total) }}
                                            <div class="text-sm text-gray-500">
                                                Adult: {{ formatNumber(priceBreakdown.summary.surcharges.adult.total) }}<br>
                                                Child: {{ formatNumber(priceBreakdown.summary.surcharges.child.total) }}<br>
                                                Infant: {{ formatNumber(priceBreakdown.summary.surcharges.infant.total) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hidden">
                                        <p class="text-sm text-gray-500">Total Nights</p>
                                        <p class="text-lg font-medium text-gray-900">{{ bookingSummary.duration }}</p>
                                    </div>
                                </div> -->
                                <div class="bg-gray-50 p-4 rounded-md shadow-sm w-full max-w-xs ml-auto">
                                    <!-- Package Total -->
                                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                                        <span>Package Total</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.total_without_sst - (priceBreakdown.add_ons_total || 0)) }}</span>
                                    </div>
                                    
                                    <!-- Add-ons Total -->
                                    <div v-if="priceBreakdown.add_ons_total && priceBreakdown.add_ons_total > 0" class="flex justify-between text-sm text-gray-600 mb-2">
                                        <span>Add-ons</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.add_ons_total) }}</span>
                                    </div>
                                    
                                    <!-- Grand Total -->
                                    <div class="flex justify-between text-base font-semibold text-indigo-700 mt-3 pt-2 border-t-2">
                                        <span>Grand Total</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.total, false) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between mt-6">
                            <button
                                @click="goToPreviousStep"
                                class="px-8 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                Back
                            </button>
                            <button
                                @click="currentStep = 4"
                                class="px-8 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Booking Details -->
                <div v-if="currentStep === 4">
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

                            <!-- Booking Email -->
                            <div>
                                <label for="booking_email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    type="email"
                                    id="booking_email"
                                    v-model="bookingForm.booking_email"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        bookingValidationErrors.booking_email ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    placeholder="e.g., john.doe@example.com"
                                    required
                                />
                                <p v-if="bookingValidationErrors.booking_email" class="mt-1 text-sm text-red-600">
                                    {{ bookingValidationErrors.booking_email }}
                                </p>
                            </div>

                            <!-- Phone Number with Country Code -->
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <!-- Country Code Dropdown with Search -->
                                    <div class="relative country-dropdown">
                                        <button
                                            type="button"
                                            @click="toggleCountryDropdown"
                                            class="h-full rounded-l-md border-r-0 border-gray-300 bg-gray-50 py-2 pl-3 pr-8 text-sm focus:border-indigo-500 focus:ring-indigo-500 flex items-center justify-between min-w-[140px]"
                                        >
                                            <span>{{ getSelectedCountryDisplay() }}</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div v-if="showCountryDropdown" class="absolute z-50 mt-1 w-80 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-hidden">
                                            <!-- Search Input -->
                                            <div class="p-3 border-b border-gray-200">
                                                <input
                                                    type="text"
                                                    v-model="countrySearch"
                                                    placeholder="Search countries..."
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    @focus="countrySearch = ''"
                                                />
                                            </div>
                                            
                                            <!-- Country List -->
                                            <div class="max-h-48 overflow-y-auto">
                                                <button
                                                    v-for="country in filteredCountries"
                                                    :key="country.code"
                                                    type="button"
                                                    @click="selectCountry(country.code)"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 focus:bg-gray-100 focus:outline-none flex items-center"
                                                >
                                                    <span class="mr-2">{{ country.flag }}</span>
                                                    <span class="flex-1">{{ country.name }}</span>
                                                    <span class="text-gray-500">{{ country.code }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Phone Number Input -->
                                    <input
                                        type="tel"
                                        id="phone_number"
                                        v-model="bookingForm.phone_number"
                                        :class="[
                                            'flex-1 rounded-r-md border-gray-300 py-2 px-3 text-sm focus:border-indigo-500 focus:ring-indigo-500',
                                            bookingValidationErrors.phone_number ? 'border-red-500' : 'border-gray-300'
                                        ]"
                                        placeholder="e.g., 123456789"
                                        required
                                    />
                                </div>
                                <p v-if="bookingValidationErrors.phone_number" class="mt-1 text-sm text-red-600">
                                    {{ bookingValidationErrors.phone_number }}
                                </p>
                            </div>

                            <!-- Tin Number -->
                            <div>
                                <label for="booking_ic" class="block text-sm font-medium text-gray-700">Tin Number (if einvoice is required)</label>
                                <input
                                    type="text"
                                    id="booking_ic"
                                    v-model="bookingForm.booking_ic"
                                    :class="[
                                        'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                        bookingValidationErrors.booking_ic ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    placeholder="e.g., 123456-78-9012 or A12345678"
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
                                    @click="goToPreviousStep"
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
                        <div v-if="bookingSuccess.status == 0" class="relative px-6 py-8">
                           <div class="text-center mb-6 bg-indigo-50 rounded-lg p-4">
                                <!-- show a loading icon -->
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white backdrop-blur-sm mb-4">
                                    <svg class="w-10 h-10 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">Booking Created!</h3>
                                <p class="text-gray-600">Your booking has been submitted.</p>
                                <p class="text-gray-600">Once the booking is approved, you will receive an email with the booking details.</p>
                            </div>
                        </div>
                        <div v-else-if="bookingSuccess.status == 1" class="relative bg-green-400 bg-[length:200%] bg-[position:0%_50%] animate-gradient-x px-6 py-8 rounded-2xl shadow-xl transform transition-transform duration-100 text-white mx-8 mt-5">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative text-center">
                                <!-- show success icon -->
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-3xl font-bold text-white mb-2">Booking Approved!</h2>
                                    <p class="text-white/90 text-lg">Your booking has been approved.</p>
                                    <p class="text-white/80 text-md font-bold">Please proceed to make payment to secure your reservation.</p>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="bookingSuccess.status == 2" class="relative bg-red-500 px-6 py-8 rounded-2xl shadow-xl transform transition-transform duration-100 text-white mx-8 mt-5">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative text-center">
                                <!-- show rejected svg -->
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-3xl font-bold text-white mb-2">Booking Rejected!</h2>
                                    <p class="text-white/90 text-lg">Your booking has been rejected.</p>
                                    <p class="text-white/80 text-sm">Please contact us if you have any questions.</p>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="bookingSuccess.status == 3" class="relative bg-green-400 bg-[length:200%] bg-[position:0%_50%] animate-gradient-x px-6 py-8 rounded-2xl shadow-xl transform transition-transform duration-100 text-white mx-8 mt-5">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative text-center">
                                <!-- show success icon -->
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-3xl font-bold text-white mb-2">Payment Completed!</h2>
                                    <p class="text-white/90 text-lg">Your payment has been completed.</p>
                                    <p class="text-white/80 text-sm">Your booking is now confirmed and ready.</p>
                                </div>
                            </div>
                        </div>
                        <!-- change the background color to refunded color -->
                        <div v-else-if="bookingSuccess.status == 4" class="relative bg-sky-500 px-6 py-8 rounded-2xl shadow-xl transform transition-transform duration-100 text-white mx-8 mt-5">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mb-4">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h2 class="text-3xl font-bold text-white mb-2">Booking Refunded!</h2>
                                    <p class="text-white/90 text-lg">Your booking has been refunded.</p>
                                    <p class="text-white/80 text-sm">Please contact us if you have any questions.</p>
                                </div>
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
                                        <p class="text-sm text-gray-600">Email: {{bookingSuccess.booking_email}}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">ROOMS</h4>
                                        <div class="space-y-2">
                                            <template v-for="(room, roomIndex) in bookingSuccess.rooms" :key="roomIndex">
                                                <p class="text-sm text-gray-600 font-medium">
                                                    Room {{ roomIndex + 1 }}:&nbsp; {{ room.room_type.name }} ({{ room.adults }} Adults, {{ room.children }} Children, {{ room.infants }} Infants)    
                                                </p>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">GUESTS</h4>
                                        <p class="text-lg text-gray-900">{{ bookingSuccess.adults }} Adults, {{ bookingSuccess.children }} Children, {{ bookingSuccess.infants }} Infants</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">DURATION</h4>
                                        <p class="text-lg text-gray-900">{{ moment(bookingSuccess.end_date).diff(moment(bookingSuccess.start_date), 'days') + 1 }} Days, {{ moment(bookingSuccess.end_date).diff(moment(bookingSuccess.start_date), 'days') }} Nights</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 shadow-sm">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Check-in -->
                                            <div class="bg-white rounded-lg p-4 border border-blue-200 shadow-sm">
                                                <div class="flex items-center mb-2">
                                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                        </svg>
                                                    </div>
                                                    <span>Check-in</span>
                                                </div>
                                                <div class="ml-11">
                                                    <p class="text-lg font-bold text-gray-900">{{ moment(bookingSuccess.start_date).format('DD') }} {{ moment(bookingSuccess.start_date).format('MMM YYYY') }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ moment(bookingSuccess.start_date).format('dddd') }}</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Check-out -->
                                            <div class="bg-white rounded-lg p-4 border border-blue-200 shadow-sm">
                                                <div class="flex items-center mb-2">
                                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                                                        </svg>
                                                    </div>
                                                    <span>Check-out</span>
                                                </div>
                                                <div class="ml-11">
                                                    <p class="text-lg font-bold text-gray-900">{{ moment(bookingSuccess.end_date).format('DD') }} {{ moment(bookingSuccess.end_date).format('MMM YYYY') }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ moment(bookingSuccess.end_date).format('dddd') }}</p>
                                                </div>
                                            </div>
                                        </div>
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
                            <div v-if="bookingSuccess.status == 1" class="flex justify-center pt-4">
                                <div class="text-center">
                                    <!-- Payment Failed Status -->
                                    <div v-if="paymentStatus === 'failed'" class="mb-6">
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md mx-auto">
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-lg font-semibold text-red-800 mb-2">Payment Failed</h3>
                                            <p class="text-red-700 mb-6">{{ paymentError || 'Your payment could not be processed. Please try again.' }}</p>
                                            <button
                                                @click="proceedToPayment"
                                                :disabled="isProcessingPayment"
                                                class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 font-medium"
                                            >
                                                <span v-if="isProcessingPayment" class="flex items-center justify-center">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Processing...
                                                </span>
                                                <span v-else class="flex items-center justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Retry Payment
                                                </span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Payment Success Status -->
                                    <div v-else-if="paymentStatus === 'success'" class="mb-6">
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 max-w-md mx-auto">
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-lg font-semibold text-green-800 mb-2">Payment Successful!</h3>
                                            <p class="text-green-700">Your payment has been processed successfully. Your booking is now confirmed.</p>
                                        </div>
                                    </div>

                                    <!-- Regular Pay Now Button (when no payment status) -->
                                    <div v-else>
                                        <button
                                            @click="proceedToPayment"
                                            class="w-full md:w-auto px-8 py-3 bg-indigo-600 text-white rounded-md text-lg font-semibold shadow-md hover:bg-indigo-700 hover:shadow-lg hover:scale-[1.02] transition-transform duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="isProcessingPayment"
                                        >
                                            <span v-if="isProcessingPayment" class="flex items-center justify-center">
                                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0..." />
                                                </svg>
                                                Initializing Payment...
                                            </span>
                                            <span v-else class="flex items-center justify-center">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                Pay Now
                                            </span>
                                        </button>
                                        <p class="text-sm text-gray-500 mt-2">
                                            You will be redirected to secure payment gateway
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mt-6 mb-8 sm:mb-0">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <!-- <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg> -->
                    Terms and Conditions
                </h2>
                <div class="text-gray-600 text-sm" v-html="formatTermsAndConditions(packageData.terms_and_conditions)"></div>
            </div>
        </div>

        <!-- Package Images Lightbox Modal -->
        <Modal :show="showPackageImageLightbox" @close="closePackageImageLightbox" :max-width="'7xl'">
            <div class="relative bg-white lightbox-container">
                <button
                    @click="closePackageImageLightbox"
                    @touchend.prevent="closePackageImageLightbox"
                    class="absolute top-2 right-2 sm:top-4 sm:right-4 z-50 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-full p-2 shadow-md transition-all lightbox-close-button touch-none"
                    aria-label="Close lightbox"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <div 
                    v-if="packageData?.images && packageData.images.length > 0" 
                    class="relative w-full" 
                    @touchstart="handleTouchStart"
                    @touchend="handleTouchEnd"
                    @touchmove="handleTouchMove"
                >
                    <!-- Main Image Display -->
                    <div 
                        class="flex items-center justify-center w-full px-2 py-12 sm:px-4 sm:py-8 md:px-8 md:py-12 h-[70vh] sm:h-[80vh]"
                        @touchstart="handleTouchStart"
                        @touchend="handleTouchEnd"
                        @touchmove="handleTouchMove"
                    >
                        <img
                            v-for="(image, index) in packageData.images"
                            :key="'lightbox-package-' + index"
                            :src="getImageUrl(image)"
                            :class="[
                                'lightbox-image max-w-full max-h-full w-auto h-auto object-contain transition-opacity duration-500',
                                lightboxPackageImageIndex === index ? 'opacity-100' : 'opacity-0 absolute'
                            ]"
                            :alt="`${packageData.name} - Image ${index + 1}`"
                        />
                    </div>

                    <!-- Navigation Buttons -->
                    <button
                        v-if="packageData.images.length > 1"
                        @click="previousLightboxPackageImage"
                        @touchend.prevent="previousLightboxPackageImage"
                        class="absolute left-1 sm:left-2 md:left-4 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-800 rounded-full p-1.5 sm:p-2 md:p-3 shadow-lg border border-gray-200 z-40 transition-all lightbox-nav-button touch-none"
                        aria-label="Previous image"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        v-if="packageData.images.length > 1"
                        @click="nextLightboxPackageImage"
                        @touchend.prevent="nextLightboxPackageImage"
                        class="absolute right-1 sm:right-2 md:right-4 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-800 rounded-full p-1.5 sm:p-2 md:p-3 shadow-lg border border-gray-200 z-40 transition-all lightbox-nav-button touch-none"
                        aria-label="Next image"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Image Counter -->
                    <div class="absolute bottom-12 sm:bottom-16 md:bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-800/80 text-white px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs sm:text-sm z-40 font-medium">
                        {{ lightboxPackageImageIndex + 1 }} / {{ packageData.images.length }}
                    </div>

                    <!-- Thumbnail Strip (Desktop) -->
                    <div v-if="packageData.images.length > 1" class="hidden md:flex absolute bottom-0 left-0 right-0 bg-gray-50 border-t border-gray-200 p-2 md:p-3 space-x-1.5 md:space-x-2 overflow-x-auto justify-center z-30 lightbox-thumbnails">
                        <button
                            v-for="(image, index) in packageData.images"
                            :key="'thumb-' + index"
                            @click="lightboxPackageImageIndex = index"
                            :class="[
                                'flex-shrink-0 w-12 h-12 md:w-14 md:h-14 rounded-lg overflow-hidden border-2 transition-all duration-200',
                                lightboxPackageImageIndex === index ? 'border-indigo-600 ring-2 ring-indigo-300' : 'border-gray-300 hover:border-indigo-400'
                            ]"
                        >
                            <img
                                :src="getImageUrl(image)"
                                :alt="`Thumbnail ${index + 1}`"
                                class="w-full h-full object-cover"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Room Type Images Lightbox Modal -->
        <Modal :show="showRoomTypeImageLightbox" @close="closeRoomTypeImageLightbox" :max-width="'7xl'">
            <div class="relative bg-white lightbox-container h-screen sm:h-auto rounded-none sm:rounded-lg -m-6 sm:m-0">
                <!-- Close Button (Desktop only - mobile is in top bar) -->
                <button
                    @click="closeRoomTypeImageLightbox"
                    @touchend.prevent="closeRoomTypeImageLightbox"
                    class="hidden sm:flex absolute top-2 right-2 sm:top-4 sm:right-4 z-50 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-full p-2 shadow-md transition-all lightbox-close-button touch-none"
                    aria-label="Close lightbox"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div 
                    v-if="lightboxRoomTypeData" 
                    class="relative w-full" 
                    @touchstart="handleTouchStart"
                    @touchend="handleTouchEnd"
                    @touchmove="handleTouchMove"
                >
                    <!-- Fixed Top Bar (Mobile) -->
                    <div v-if="availableRoomTypes.length > 1" class="fixed top-0 left-0 right-0 z-50 sm:hidden bg-white border-b border-gray-200 shadow-sm">
                        <div class="flex items-center h-14 px-2 gap-2">
                            <!-- Left Button (Previous Room Type) -->
                            <button
                                @click="previousRoomType"
                                @touchend.prevent="previousRoomType"
                                class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors touch-none"
                                aria-label="Previous room type"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            
                            <!-- Room Type Name and Counter (Scrollable) -->
                            <div class="flex-1 min-w-0 flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">
                                    {{ lightboxRoomTypeData.name }}
                                </h3>
                                <span class="flex-shrink-0 text-xs text-gray-500 whitespace-nowrap">
                                    {{ availableRoomTypes.findIndex(rt => rt.id === lightboxRoomTypeData.id) + 1 }}/{{ availableRoomTypes.length }}
                                </span>
                            </div>
                            
                            <!-- Right Button (Next Room Type) -->
                            <button
                                @click="nextRoomType"
                                @touchend.prevent="nextRoomType"
                                class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors touch-none"
                                aria-label="Next room type"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            
                            <!-- Close Button -->
                            <button
                                @click="closeRoomTypeImageLightbox"
                                @touchend.prevent="closeRoomTypeImageLightbox"
                                class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors touch-none ml-1"
                                aria-label="Close lightbox"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Single Room Type Header (Mobile - when only one room type) -->
                    <div v-if="availableRoomTypes.length === 1" class="fixed top-0 left-0 right-0 z-50 sm:hidden bg-white border-b border-gray-200 shadow-sm">
                        <div class="flex items-center h-14 px-2 gap-2">
                            <h3 class="flex-1 text-sm font-semibold text-gray-900 truncate">
                                {{ lightboxRoomTypeData.name }}
                            </h3>
                            <button
                                @click="closeRoomTypeImageLightbox"
                                @touchend.prevent="closeRoomTypeImageLightbox"
                                class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors touch-none"
                                aria-label="Close lightbox"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Room Type Title and Navigation (Desktop) -->
                    <div class="absolute top-2 left-2 sm:top-4 sm:left-4 z-40 bg-white border border-gray-200 shadow-md text-gray-800 px-2 py-1.5 sm:px-3 sm:py-2 rounded-lg flex items-center gap-2 hidden sm:flex">
                        <!-- Previous Room Type Button (Desktop) -->
                        <button
                            v-if="availableRoomTypes.length > 1"
                            @click="previousRoomType"
                            @touchend.prevent="previousRoomType"
                            class="flex items-center justify-center w-6 h-6 rounded-full hover:bg-gray-100 transition-colors touch-none"
                            aria-label="Previous room type"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h3 class="text-sm sm:text-base md:text-lg font-semibold">{{ lightboxRoomTypeData.name }}</h3>
                        <!-- Next Room Type Button (Desktop) -->
                        <button
                            v-if="availableRoomTypes.length > 1"
                            @click="nextRoomType"
                            @touchend.prevent="nextRoomType"
                            class="flex items-center justify-center w-6 h-6 rounded-full hover:bg-gray-100 transition-colors touch-none"
                            aria-label="Next room type"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <!-- Room Type Counter (Desktop) -->
                        <span v-if="availableRoomTypes.length > 1" class="text-xs text-gray-500 ml-1">
                            ({{ availableRoomTypes.findIndex(rt => rt.id === lightboxRoomTypeData.id) + 1 }}/{{ availableRoomTypes.length }})
                        </span>
                    </div>

                    <!-- Main Image Display -->
                    <div 
                        class="flex items-center justify-center w-full px-2 h-[calc(100vh-3.5rem)] pt-14 pb-0 sm:h-auto sm:min-h-0 sm:pt-4 sm:px-4 sm:py-8 md:px-8 md:py-12"
                        @touchstart="handleTouchStart"
                        @touchend="handleTouchEnd"
                        @touchmove="handleTouchMove"
                    >
                        <img
                            v-for="(image, index) in lightboxRoomTypeData.images"
                            :key="'lightbox-room-' + index"
                            :src="getImageUrl(image)"
                            :class="[
                                'lightbox-image w-full h-auto max-h-[75vh] sm:max-h-[80vh] object-contain transition-opacity duration-500',
                                lightboxRoomTypeImageIndex === index ? 'opacity-100' : 'opacity-0 absolute'
                            ]"
                            :alt="`${lightboxRoomTypeData.name} - Image ${index + 1}`"
                        />
                    </div>

                    <!-- Navigation Buttons for Images -->
                    <button
                        v-if="lightboxRoomTypeData.images && lightboxRoomTypeData.images.length > 1"
                        @click="previousLightboxRoomTypeImage"
                        @touchend.prevent="previousLightboxRoomTypeImage"
                        class="absolute left-1 sm:left-2 md:left-4 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-800 rounded-full p-1.5 sm:p-2 md:p-3 shadow-lg border border-gray-200 z-40 transition-all lightbox-nav-button touch-none"
                        aria-label="Previous image"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        v-if="lightboxRoomTypeData.images && lightboxRoomTypeData.images.length > 1"
                        @click="nextLightboxRoomTypeImage"
                        @touchend.prevent="nextLightboxRoomTypeImage"
                        class="absolute right-1 sm:right-2 md:right-4 top-1/2 transform -translate-y-1/2 bg-white hover:bg-gray-50 text-gray-800 rounded-full p-1.5 sm:p-2 md:p-3 shadow-lg border border-gray-200 z-40 transition-all lightbox-nav-button touch-none"
                        aria-label="Next image"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    
                    <!-- Room Type Navigation Buttons (Mobile - when at edges) -->
                    <button
                        v-if="availableRoomTypes.length > 1 && lightboxRoomTypeImageIndex === 0 && lightboxRoomTypeData.images.length === 1"
                        @click="previousRoomType"
                        @touchend.prevent="previousRoomType"
                        class="absolute left-1 sm:left-2 md:left-4 top-1/2 transform -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-1.5 sm:p-2 md:p-3 shadow-lg border border-indigo-700 z-40 transition-all lightbox-nav-button touch-none"
                        aria-label="Previous room type"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        v-if="availableRoomTypes.length > 1 && lightboxRoomTypeImageIndex === (lightboxRoomTypeData.images.length - 1) && lightboxRoomTypeData.images.length === 1"
                        @click="nextRoomType"
                        @touchend.prevent="nextRoomType"
                        class="absolute right-1 sm:right-2 md:right-4 top-1/2 transform -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-1.5 sm:p-2 md:p-3 shadow-lg border border-indigo-700 z-40 transition-all lightbox-nav-button touch-none"
                        aria-label="Next room type"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Image Counter -->
                    <div v-if="lightboxRoomTypeData.images && lightboxRoomTypeData.images.length > 1" class="absolute bottom-12 sm:bottom-16 md:bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-900/90 text-white px-3 py-1.5 sm:px-3 sm:py-1.5 rounded-full text-xs sm:text-sm z-40 font-medium shadow-lg">
                        {{ lightboxRoomTypeImageIndex + 1 }} / {{ lightboxRoomTypeData.images.length }}
                    </div>
                    

                    <!-- Thumbnail Strip (Desktop) -->
                    <div v-if="lightboxRoomTypeData.images && lightboxRoomTypeData.images.length > 1" class="hidden md:flex absolute bottom-0 left-0 right-0 bg-gray-50 border-t border-gray-200 p-2 md:p-3 space-x-1.5 md:space-x-2 overflow-x-auto justify-center z-30 lightbox-thumbnails">
                        <button
                            v-for="(image, index) in lightboxRoomTypeData.images"
                            :key="'thumb-room-' + index"
                            @click="lightboxRoomTypeImageIndex = index"
                            :class="[
                                'flex-shrink-0 w-12 h-12 md:w-14 md:h-14 rounded-lg overflow-hidden border-2 transition-all duration-200',
                                lightboxRoomTypeImageIndex === index ? 'border-indigo-600 ring-2 ring-indigo-300' : 'border-gray-300 hover:border-indigo-400'
                            ]"
                        >
                            <img
                                :src="getImageUrl(image)"
                                :alt="`Thumbnail ${index + 1}`"
                                class="w-full h-full object-cover"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style>
.quoataion-extra-upper-padding {
    padding-top: 100px;
}
@media (max-width: 640px) {
    .quoataion-extra-upper-padding {
        padding-top: 80px !important;
    }
}
</style>

<script setup>
import { Head } from '@inertiajs/vue3';
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
        currentStep.value = 4;
        setTimeout(() => {
            const bookingForm = document.getElementById('booking-form');
            if (bookingForm) {
                bookingForm.scrollIntoView({ behavior: 'smooth' });
            }
        }, 100);
    }
    
    // Check if user is returning from payment (URL has payment parameters)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('payment_status') || urlParams.has('transaction_id')) {
        paymentStatus.value = urlParams.get('payment_status');
        transactionId.value = urlParams.get('transaction_id');
        paymentError.value = urlParams.get('error');
        
        // Refresh booking data to get updated payment status
        refreshBookingData();
    }
});

const linkify = (text) => {
  if (!text) return "";
  const urlPattern = /(https?:\/\/[^\s]+)/g;
  return text.replace(urlPattern, '<a href="$1" target="_blank" class="text-blue-600 underline">$1</a>');
}

// Format terms and conditions as bullet points
const formatTermsAndConditions = (text) => {
  if (!text) return "";
  
  // Split by newlines and filter out empty lines
  const lines = text.split(/\r?\n/).filter(line => line.trim().length > 0);
  
  if (lines.length === 0) return "";
  
  // If only one line or no newlines, return as paragraph with linkify
  if (lines.length === 1) {
    return `<p>${linkify(lines[0])}</p>`;
  }
  
  // Convert to bullet points
  const bulletPoints = lines.map(line => {
    const linkedLine = linkify(line.trim());
    return `<li class="mb-1">${linkedLine}</li>`;
  }).join('');
  
  return `<ul class="list-disc list-inside space-y-1">${bulletPoints}</ul>`;
}

// Function to retry payment
const retryPayment = async () => {
    if (!bookingSuccess.value?.uuid) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Booking information not found. Please start over.',
            confirmButtonColor: '#EF4444'
        });
        return;
    }

    isProcessingPayment.value = true;
    try {
        const r = await axios.post(route('payment.initiate', bookingSuccess.value.uuid));
        console.log(r.data);
        return;
        if (r.data.success && r.data.payment_data) {
            const f = Object.entries(r.data.payment_data).reduce((form, [k, v]) => {
            form.innerHTML += `<input type="hidden" name="${k}" value="${v}">`;
            return form;
        }, 
        document.createElement('form'));
        f.method = 'post';
        f.action = `https://sandbox.senangpay.my/payment/839174991356979`;
        document.body.appendChild(f);
        f.submit();
        } else throw new Error('Failed to initiate payment');
    } catch (e) {
        console.error(e);
        Swal.fire({ icon: 'error', title: 'Payment Error', text: 'Unable to retry payment. Please contact support.', confirmButtonColor: '#EF4444' });
    } finally {
        isProcessingPayment.value = false;
    }
};

// Add function to refresh booking data
const refreshBookingData = async () => {
    if (!bookingSuccess.value?.uuid) return;
    
    try {
        const response = await axios.post(route('api.fetch-package-by-uuid'), {
            uuid: props.uuid,
            booking_uuid: bookingSuccess.value.uuid,
        });

        if (response.data.success && response.data.booking) {
            bookingSuccess.value = response.data.booking;
        }
    } catch (error) {
        console.error('Error refreshing booking data:', error);
    }
};

const packageData = ref(null);
const currentImageIndex = ref(0);
const calculatedPrice = ref(null);
const globalSst = ref(null);

// Lightbox states
const showPackageImageLightbox = ref(false);
const lightboxPackageImageIndex = ref(0);
const showRoomTypeImageLightbox = ref(false);
const lightboxRoomTypeData = ref(null);
const lightboxRoomTypeImageIndex = ref(0);
const currentRoomTypeIndex = ref(0); // Track which room type is currently shown

// Touch/swipe support for mobile
const touchStartX = ref(0);
const touchStartY = ref(0);
const touchEndX = ref(0);
const touchEndY = ref(0);
const priceBreakdown = ref(null);
const nightlyBreakdown = ref([]);
const roomTypes = ref([]);
const selectedRoomType = ref(null);
const isLoading = ref(true);
const packageAddOns = ref([]);
const selectedAddOns = ref([]); // Array of add-on IDs that are selected (checkbox state)
const addOnPaxData = ref([]); // Array of objects with pax data for each add-on
const showAddOnTooltip = ref(null); // Track which add-on tooltip is shown on hover
const openAddOnDescriptions = ref({}); // Track which add-on descriptions are open on click
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
    country_code: '+60', // Default to Malaysia
    booking_ic: '',
    special_remarks: '',
    booking_email: ''
});

// Countries data for phone number dropdown
const countries = [
    { code: '+60', name: 'Malaysia', flag: '🇲🇾' },
    { code: '+65', name: 'Singapore', flag: '🇸🇬' },
    { code: '+66', name: 'Thailand', flag: '🇹🇭' },
    { code: '+62', name: 'Indonesia', flag: '🇮🇩' },
    { code: '+63', name: 'Philippines', flag: '🇵🇭' },
    { code: '+84', name: 'Vietnam', flag: '🇻🇳' },
    { code: '+855', name: 'Cambodia', flag: '🇰🇭' },
    { code: '+856', name: 'Laos', flag: '🇱🇦' },
    { code: '+95', name: 'Myanmar', flag: '🇲🇲' },
    { code: '+673', name: 'Brunei', flag: '🇧🇳' },
    { code: '+1', name: 'USA/Canada', flag: '🇺🇸' },
    { code: '+44', name: 'United Kingdom', flag: '🇬🇧' },
    { code: '+61', name: 'Australia', flag: '🇦🇺' },
    { code: '+86', name: 'China', flag: '🇨🇳' },
    { code: '+81', name: 'Japan', flag: '🇯🇵' },
    { code: '+82', name: 'South Korea', flag: '🇰🇷' },
    { code: '+91', name: 'India', flag: '🇮🇳' },
    { code: '+971', name: 'UAE', flag: '🇦🇪' },
    { code: '+966', name: 'Saudi Arabia', flag: '🇸🇦' },
    { code: '+852', name: 'Hong Kong', flag: '🇭🇰' },
    { code: '+886', name: 'Taiwan', flag: '🇹🇼' },
    { code: '+33', name: 'France', flag: '🇫🇷' },
    { code: '+49', name: 'Germany', flag: '🇩🇪' },
    { code: '+39', name: 'Italy', flag: '🇮🇹' },
    { code: '+34', name: 'Spain', flag: '🇪🇸' },
    { code: '+31', name: 'Netherlands', flag: '🇳🇱' },
    { code: '+46', name: 'Sweden', flag: '🇸🇪' },
    { code: '+47', name: 'Norway', flag: '🇳🇴' },
    { code: '+45', name: 'Denmark', flag: '🇩🇰' },
    { code: '+358', name: 'Finland', flag: '🇫🇮' },
    { code: '+41', name: 'Switzerland', flag: '🇨🇭' },
    { code: '+43', name: 'Austria', flag: '🇦🇹' },
    { code: '+32', name: 'Belgium', flag: '🇧🇪' },
    { code: '+351', name: 'Portugal', flag: '🇵🇹' },
    { code: '+353', name: 'Ireland', flag: '🇮🇪' },
    { code: '+48', name: 'Poland', flag: '🇵🇱' },
    { code: '+420', name: 'Czech Republic', flag: '🇨🇿' },
    { code: '+36', name: 'Hungary', flag: '🇭🇺' },
    { code: '+30', name: 'Greece', flag: '🇬🇷' },
    { code: '+90', name: 'Turkey', flag: '🇹🇷' },
    { code: '+7', name: 'Russia', flag: '🇷🇺' },
    { code: '+380', name: 'Ukraine', flag: '🇺🇦' },
    { code: '+55', name: 'Brazil', flag: '🇧🇷' },
    { code: '+54', name: 'Argentina', flag: '🇦🇷' },
    { code: '+56', name: 'Chile', flag: '🇨🇱' },
    { code: '+57', name: 'Colombia', flag: '🇨🇴' },
    { code: '+52', name: 'Mexico', flag: '🇲🇽' },
    { code: '+51', name: 'Peru', flag: '🇵🇪' },
    { code: '+58', name: 'Venezuela', flag: '🇻🇪' },
    { code: '+593', name: 'Ecuador', flag: '🇪🇨' },
    { code: '+595', name: 'Paraguay', flag: '🇵🇾' },
    { code: '+598', name: 'Uruguay', flag: '🇺🇾' },
    { code: '+591', name: 'Bolivia', flag: '🇧🇴' },
    { code: '+27', name: 'South Africa', flag: '🇿🇦' },
    { code: '+234', name: 'Nigeria', flag: '🇳🇬' },
    { code: '+254', name: 'Kenya', flag: '🇰🇪' },
    { code: '+20', name: 'Egypt', flag: '🇪🇬' },
    { code: '+212', name: 'Morocco', flag: '🇲🇦' },
    { code: '+216', name: 'Tunisia', flag: '🇹🇳' },
    { code: '+213', name: 'Algeria', flag: '🇩🇿' },
    { code: '+233', name: 'Ghana', flag: '🇬🇭' },
    { code: '+225', name: 'Ivory Coast', flag: '🇨🇮' },
    { code: '+221', name: 'Senegal', flag: '🇸🇳' },
    { code: '+237', name: 'Cameroon', flag: '🇨🇲' },
    { code: '+236', name: 'Central African Republic', flag: '🇨🇫' },
    { code: '+235', name: 'Chad', flag: '🇹🇩' },
    { code: '+242', name: 'Congo', flag: '🇨🇬' },
    { code: '+243', name: 'DR Congo', flag: '🇨🇩' },
    { code: '+241', name: 'Gabon', flag: '🇬🇦' },
    { code: '+240', name: 'Equatorial Guinea', flag: '🇬🇶' },
    { code: '+239', name: 'São Tomé and Príncipe', flag: '🇸🇹' },
    { code: '+238', name: 'Cape Verde', flag: '🇨🇻' },
    { code: '+245', name: 'Guinea-Bissau', flag: '🇬🇼' },
    { code: '+246', name: 'British Indian Ocean Territory', flag: '🇮🇴' },
    { code: '+247', name: 'Ascension Island', flag: '🇦🇨' },
    { code: '+248', name: 'Seychelles', flag: '🇸🇨' },
    { code: '+249', name: 'Sudan', flag: '🇸🇩' },
    { code: '+250', name: 'Rwanda', flag: '🇷🇼' },
    { code: '+251', name: 'Ethiopia', flag: '🇪🇹' },
    { code: '+252', name: 'Somalia', flag: '🇸🇴' },
    { code: '+253', name: 'Djibouti', flag: '🇩🇯' },
    { code: '+255', name: 'Tanzania', flag: '🇹🇿' },
    { code: '+256', name: 'Uganda', flag: '🇺🇬' },
    { code: '+257', name: 'Burundi', flag: '🇧🇮' },
    { code: '+258', name: 'Mozambique', flag: '🇲🇿' },
    { code: '+260', name: 'Zambia', flag: '🇿🇲' },
    { code: '+261', name: 'Madagascar', flag: '🇲🇬' },
    { code: '+262', name: 'Réunion', flag: '🇷🇪' },
    { code: '+263', name: 'Zimbabwe', flag: '🇿🇼' },
    { code: '+264', name: 'Namibia', flag: '🇳🇦' },
    { code: '+265', name: 'Malawi', flag: '🇲🇼' },
    { code: '+266', name: 'Lesotho', flag: '🇱🇸' },
    { code: '+267', name: 'Botswana', flag: '🇧🇼' },
    { code: '+268', name: 'Eswatini', flag: '🇸🇿' },
    { code: '+269', name: 'Comoros', flag: '🇰🇲' },
    { code: '+290', name: 'Saint Helena', flag: '🇸🇭' },
    { code: '+291', name: 'Eritrea', flag: '🇪🇷' },
    { code: '+297', name: 'Aruba', flag: '🇦🇼' },
    { code: '+298', name: 'Faroe Islands', flag: '🇫🇴' },
    { code: '+299', name: 'Greenland', flag: '🇬🇱' },
    { code: '+350', name: 'Gibraltar', flag: '🇬🇮' },
    { code: '+352', name: 'Luxembourg', flag: '🇱🇺' },
    { code: '+354', name: 'Iceland', flag: '🇮🇸' },
    { code: '+355', name: 'Albania', flag: '🇦🇱' },
    { code: '+356', name: 'Malta', flag: '🇲🇹' },
    { code: '+357', name: 'Cyprus', flag: '🇨🇾' },
    { code: '+359', name: 'Bulgaria', flag: '🇧🇬' },
    { code: '+370', name: 'Lithuania', flag: '🇱🇹' },
    { code: '+371', name: 'Latvia', flag: '🇱🇻' },
    { code: '+372', name: 'Estonia', flag: '🇪🇪' },
    { code: '+373', name: 'Moldova', flag: '🇲🇩' },
    { code: '+374', name: 'Armenia', flag: '🇦🇲' },
    { code: '+375', name: 'Belarus', flag: '🇧🇾' },
    { code: '+376', name: 'Andorra', flag: '🇦🇩' },
    { code: '+377', name: 'Monaco', flag: '🇲🇨' },
    { code: '+378', name: 'San Marino', flag: '🇸🇲' },
    { code: '+379', name: 'Vatican City', flag: '🇻🇦' },
    { code: '+381', name: 'Serbia', flag: '🇷🇸' },
    { code: '+382', name: 'Montenegro', flag: '🇲🇪' },
    { code: '+383', name: 'Kosovo', flag: '🇽🇰' },
    { code: '+385', name: 'Croatia', flag: '🇭🇷' },
    { code: '+386', name: 'Slovenia', flag: '🇸🇮' },
    { code: '+387', name: 'Bosnia and Herzegovina', flag: '🇧🇦' },
    { code: '+389', name: 'North Macedonia', flag: '🇲🇰' },
    { code: '+421', name: 'Slovakia', flag: '🇸🇰' },
    { code: '+423', name: 'Liechtenstein', flag: '🇱🇮' },
    { code: '+500', name: 'Falkland Islands', flag: '🇫🇰' },
    { code: '+501', name: 'Belize', flag: '🇧🇿' },
    { code: '+502', name: 'Guatemala', flag: '🇬🇹' },
    { code: '+503', name: 'El Salvador', flag: '🇸🇻' },
    { code: '+504', name: 'Honduras', flag: '🇭🇳' },
    { code: '+505', name: 'Nicaragua', flag: '🇳🇮' },
    { code: '+506', name: 'Costa Rica', flag: '🇨🇷' },
    { code: '+507', name: 'Panama', flag: '🇵🇦' },
    { code: '+508', name: 'Saint Pierre and Miquelon', flag: '🇵🇲' },
    { code: '+509', name: 'Haiti', flag: '🇭🇹' },
    { code: '+590', name: 'Guadeloupe', flag: '🇬🇵' },
    { code: '+592', name: 'Guyana', flag: '🇬🇾' },
    { code: '+594', name: 'French Guiana', flag: '🇬🇫' },
    { code: '+596', name: 'Martinique', flag: '🇲🇶' },
    { code: '+597', name: 'Suriname', flag: '🇸🇷' },
    { code: '+599', name: 'Netherlands Antilles', flag: '🇧🇶' },
    { code: '+670', name: 'East Timor', flag: '🇹🇱' },
    { code: '+672', name: 'Antarctica', flag: '🇦🇶' },
    { code: '+674', name: 'Nauru', flag: '🇳🇷' },
    { code: '+675', name: 'Papua New Guinea', flag: '🇵🇬' },
    { code: '+676', name: 'Tonga', flag: '🇹🇴' },
    { code: '+677', name: 'Solomon Islands', flag: '🇸🇧' },
    { code: '+678', name: 'Vanuatu', flag: '🇻🇺' },
    { code: '+679', name: 'Fiji', flag: '🇫🇯' },
    { code: '+680', name: 'Palau', flag: '🇵🇼' },
    { code: '+681', name: 'Wallis and Futuna', flag: '🇼🇫' },
    { code: '+682', name: 'Cook Islands', flag: '🇨🇰' },
    { code: '+683', name: 'Niue', flag: '🇳🇺' },
    { code: '+685', name: 'Samoa', flag: '🇼🇸' },
    { code: '+686', name: 'Kiribati', flag: '🇰🇮' },
    { code: '+687', name: 'New Caledonia', flag: '🇳🇨' },
    { code: '+688', name: 'Tuvalu', flag: '🇹🇻' },
    { code: '+689', name: 'French Polynesia', flag: '🇵🇫' },
    { code: '+690', name: 'Tokelau', flag: '🇹🇰' },
    { code: '+691', name: 'Micronesia', flag: '🇫🇲' },
    { code: '+692', name: 'Marshall Islands', flag: '🇲🇭' },
    { code: '+850', name: 'North Korea', flag: '🇰🇵' },
    { code: '+853', name: 'Macau', flag: '🇲🇴' },
    { code: '+880', name: 'Bangladesh', flag: '🇧🇩' },
    { code: '+960', name: 'Maldives', flag: '🇲🇻' },
    { code: '+961', name: 'Lebanon', flag: '🇱🇧' },
    { code: '+962', name: 'Jordan', flag: '🇯🇴' },
    { code: '+963', name: 'Syria', flag: '🇸🇾' },
    { code: '+964', name: 'Iraq', flag: '🇮🇶' },
    { code: '+965', name: 'Kuwait', flag: '🇰🇼' },
    { code: '+967', name: 'Yemen', flag: '🇾🇪' },
    { code: '+968', name: 'Oman', flag: '🇴🇲' },
    { code: '+970', name: 'Palestine', flag: '🇵🇸' },
    { code: '+972', name: 'Israel', flag: '🇮🇱' },
    { code: '+973', name: 'Bahrain', flag: '🇧🇭' },
    { code: '+974', name: 'Qatar', flag: '🇶🇦' },
    { code: '+975', name: 'Bhutan', flag: '🇧🇹' },
    { code: '+976', name: 'Mongolia', flag: '🇲🇳' },
    { code: '+977', name: 'Nepal', flag: '🇳🇵' },
    { code: '+992', name: 'Tajikistan', flag: '🇹🇯' },
    { code: '+993', name: 'Turkmenistan', flag: '🇹🇲' },
    { code: '+994', name: 'Azerbaijan', flag: '🇦🇿' },
    { code: '+995', name: 'Georgia', flag: '🇬🇪' },
    { code: '+996', name: 'Kyrgyzstan', flag: '🇰🇬' },
    { code: '+998', name: 'Uzbekistan', flag: '🇺🇿' }
];

// Dropdown state
const showCountryDropdown = ref(false);
const countrySearch = ref('');

// Computed property for filtered countries
const filteredCountries = computed(() => {
    if (!countrySearch.value) {
        return countries;
    }
    const search = countrySearch.value.toLowerCase();
    return countries.filter(country => 
        country.name.toLowerCase().includes(search) ||
        country.code.includes(search) ||
        country.flag.includes(search)
    );
});

// Methods for dropdown functionality
const toggleCountryDropdown = () => {
    showCountryDropdown.value = !showCountryDropdown.value;
    if (showCountryDropdown.value) {
        countrySearch.value = '';
    }
};

const selectCountry = (code) => {
    bookingForm.value.country_code = code;
    showCountryDropdown.value = false;
    countrySearch.value = '';
};

const getSelectedCountryDisplay = () => {
    const selectedCountry = countries.find(country => country.code === bookingForm.value.country_code);
    return selectedCountry ? `${selectedCountry.flag} ${selectedCountry.code}` : '+60';
};

// Close dropdown when clicking outside
const closeDropdownOnOutsideClick = (event) => {
    if (showCountryDropdown.value && !event.target.closest('.country-dropdown')) {
        showCountryDropdown.value = false;
        countrySearch.value = '';
    }
    
    // Close add-on descriptions when clicking outside
    if (Object.keys(openAddOnDescriptions.value).length > 0 && !event.target.closest('.addon-info-button')) {
        openAddOnDescriptions.value = {};
    }
};

// Add booking form validation errors
const bookingValidationErrors = ref({
    booking_name: '',
    phone_number: '',
    booking_ic: '',
    booking_email: ''
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
    
    // Add click outside listener for country dropdown
    document.addEventListener('click', closeDropdownOnOutsideClick);
    
    // Add keyboard navigation for lightboxes
    document.addEventListener('keydown', handleKeyPress);
});

// Clean up interval on component unmount
onUnmounted(() => {
    if (autoRotationInterval) {
        clearInterval(autoRotationInterval);
    }
    
    // Remove click outside listener
    document.removeEventListener('click', closeDropdownOnOutsideClick);
    
    // Remove keyboard navigation listener
    document.removeEventListener('keydown', handleKeyPress);
    
    // Reset body overflow
    document.body.style.overflow = '';
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

// Package Image Lightbox Functions
const openPackageImageLightbox = (index = 0) => {
    // Disable on mobile - only allow on desktop (sm breakpoint and above)
    if (window.innerWidth < 640) {
        return;
    }
    lightboxPackageImageIndex.value = index;
    showPackageImageLightbox.value = true;
    document.body.style.overflow = 'hidden';
};

// Handle thumbnail double-click (desktop only)
const handlePackageThumbnailDoubleClick = (index = 0) => {
    // Disable on mobile - only allow on desktop (sm breakpoint and above)
    if (window.innerWidth < 640) {
        return;
    }
    openPackageImageLightbox(index);
};

const closePackageImageLightbox = () => {
    showPackageImageLightbox.value = false;
    document.body.style.overflow = '';
};

const nextLightboxPackageImage = () => {
    if (packageData.value?.images?.length > 0) {
        lightboxPackageImageIndex.value = (lightboxPackageImageIndex.value + 1) % packageData.value.images.length;
    }
};

const previousLightboxPackageImage = () => {
    if (packageData.value?.images?.length > 0) {
        lightboxPackageImageIndex.value = (lightboxPackageImageIndex.value - 1 + packageData.value.images.length) % packageData.value.images.length;
    }
};

// Room Type Image Lightbox Functions
const openRoomTypeImageLightbox = (roomType, index = 0) => {
    if (!roomType || !roomType.images || roomType.images.length === 0) return;
    
    // Find the index of this room type in the roomTypes array
    const roomTypeIndex = roomTypes.value.findIndex(rt => rt.id === roomType.id);
    if (roomTypeIndex !== -1) {
        currentRoomTypeIndex.value = roomTypeIndex;
    }
    
    lightboxRoomTypeData.value = roomType;
    lightboxRoomTypeImageIndex.value = index;
    showRoomTypeImageLightbox.value = true;
    document.body.style.overflow = 'hidden';
};

// Navigate to next room type
const nextRoomType = () => {
    const availableRoomTypes = roomTypes.value.filter(rt => rt.images && rt.images.length > 0);
    if (availableRoomTypes.length <= 1) return;
    
    const currentIndex = availableRoomTypes.findIndex(rt => rt.id === lightboxRoomTypeData.value?.id);
    const nextIndex = (currentIndex + 1) % availableRoomTypes.length;
    const nextRoomType = availableRoomTypes[nextIndex];
    
    lightboxRoomTypeData.value = nextRoomType;
    lightboxRoomTypeImageIndex.value = 0;
    currentRoomTypeIndex.value = roomTypes.value.findIndex(rt => rt.id === nextRoomType.id);
};

// Navigate to previous room type
const previousRoomType = () => {
    const availableRoomTypes = roomTypes.value.filter(rt => rt.images && rt.images.length > 0);
    if (availableRoomTypes.length <= 1) return;
    
    const currentIndex = availableRoomTypes.findIndex(rt => rt.id === lightboxRoomTypeData.value?.id);
    const prevIndex = (currentIndex - 1 + availableRoomTypes.length) % availableRoomTypes.length;
    const prevRoomType = availableRoomTypes[prevIndex];
    
    lightboxRoomTypeData.value = prevRoomType;
    lightboxRoomTypeImageIndex.value = 0;
    currentRoomTypeIndex.value = roomTypes.value.findIndex(rt => rt.id === prevRoomType.id);
};

// Switch to a specific room type
const switchToRoomType = (roomType) => {
    if (!roomType || !roomType.images || roomType.images.length === 0) return;
    lightboxRoomTypeData.value = roomType;
    lightboxRoomTypeImageIndex.value = 0;
    currentRoomTypeIndex.value = roomTypes.value.findIndex(rt => rt.id === roomType.id);
};

// Get available room types with images
const availableRoomTypes = computed(() => {
    return roomTypes.value.filter(rt => rt.images && rt.images.length > 0);
});

// Handle tap on room type images (for mobile)
let roomTypeTapStartTime = 0;
let roomTypeTapStartX = 0;
let roomTypeTapStartY = 0;

const handleRoomTypeImageTap = (roomType, event) => {
    // Check if this was a tap (not a swipe)
    const touch = event.changedTouches ? event.changedTouches[0] : null;
    if (!touch) return;
    
    const deltaX = Math.abs(touch.clientX - roomTypeTapStartX);
    const deltaY = Math.abs(touch.clientY - roomTypeTapStartY);
    const deltaTime = Date.now() - roomTypeTapStartTime;
    
    // If it's a quick tap (not a swipe), open lightbox
    if (deltaTime < 300 && deltaX < 10 && deltaY < 10) {
        event.preventDefault();
        event.stopPropagation();
        openRoomTypeImageLightbox(roomType, 0);
    }
};

const closeRoomTypeImageLightbox = () => {
    showRoomTypeImageLightbox.value = false;
    lightboxRoomTypeData.value = null;
    document.body.style.overflow = '';
};

const nextLightboxRoomTypeImage = () => {
    if (lightboxRoomTypeData.value?.images?.length > 0) {
        lightboxRoomTypeImageIndex.value = (lightboxRoomTypeImageIndex.value + 1) % lightboxRoomTypeData.value.images.length;
    }
};

const previousLightboxRoomTypeImage = () => {
    if (lightboxRoomTypeData.value?.images?.length > 0) {
        lightboxRoomTypeImageIndex.value = (lightboxRoomTypeImageIndex.value - 1 + lightboxRoomTypeData.value.images.length) % lightboxRoomTypeData.value.images.length;
    }
};

function updateAddOnSelection(addOnId) {
    console.log('updateAddOnSelection', addOnId);
  const pax = getAddOnPax(addOnId);
  const total = (pax.adults || 0) + (pax.children || 0) + (pax.infants || 0);

  if (total > 0 && !selectedAddOns.value.includes(addOnId)) {
    selectedAddOns.value.push(addOnId);
  } else if (total === 0 && selectedAddOns.value.includes(addOnId)) {
    selectedAddOns.value.splice(selectedAddOns.value.indexOf(addOnId), 1);
  }
}

// Keyboard navigation for lightboxes
const handleKeyPress = (event) => {
    if (showPackageImageLightbox.value) {
        if (event.key === 'ArrowLeft') {
            previousLightboxPackageImage();
        } else if (event.key === 'ArrowRight') {
            nextLightboxPackageImage();
        } else if (event.key === 'Escape') {
            closePackageImageLightbox();
        }
    } else if (showRoomTypeImageLightbox.value) {
        if (event.key === 'ArrowLeft') {
            previousLightboxRoomTypeImage();
        } else if (event.key === 'ArrowRight') {
            nextLightboxRoomTypeImage();
        } else if (event.key === 'Escape') {
            closeRoomTypeImageLightbox();
        }
    }
};

// Touch/swipe handlers for mobile
let isSwiping = false;

const handleTouchStart = (event) => {
    // Only handle if we're in a lightbox
    if (!showPackageImageLightbox.value && !showRoomTypeImageLightbox.value) {
        return;
    }
    isSwiping = false;
    touchStartX.value = event.touches[0].clientX;
    touchStartY.value = event.touches[0].clientY;
};

const handleTouchMove = (event) => {
    // Only handle if we're in a lightbox
    if (!showPackageImageLightbox.value && !showRoomTypeImageLightbox.value) {
        return;
    }
    
    if (event.touches.length > 0) {
        const currentX = event.touches[0].clientX;
        const currentY = event.touches[0].clientY;
        const deltaX = Math.abs(currentX - touchStartX.value);
        const deltaY = Math.abs(currentY - touchStartY.value);
        
        // If horizontal movement is greater than vertical, prevent default scrolling
        if (deltaX > deltaY && deltaX > 10) {
            isSwiping = true;
            event.preventDefault();
        }
    }
};

const handleTouchEnd = (event) => {
    // Only handle if we're in a lightbox
    if (!showPackageImageLightbox.value && !showRoomTypeImageLightbox.value) {
        return;
    }
    
    if (isSwiping) {
        event.preventDefault();
    }
    
    touchEndX.value = event.changedTouches[0].clientX;
    touchEndY.value = event.changedTouches[0].clientY;
    handleSwipe();
};

const handleSwipe = () => {
    const deltaX = touchStartX.value - touchEndX.value;
    const deltaY = touchStartY.value - touchEndY.value;
    
    // Only handle horizontal swipes (ignore vertical scrolling)
    if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
        if (showPackageImageLightbox.value) {
            if (deltaX > 0) {
                // Swipe left - next image
                nextLightboxPackageImage();
            } else {
                // Swipe right - previous image
                previousLightboxPackageImage();
            }
        } else if (showRoomTypeImageLightbox.value) {
            // For room type lightbox, check if we're at the edge to switch room types
            const isFirstImage = lightboxRoomTypeImageIndex.value === 0;
            const isLastImage = lightboxRoomTypeImageIndex.value === (lightboxRoomTypeData.value?.images?.length - 1 || 0);
            const hasMultipleRoomTypes = availableRoomTypes.value.length > 1;
            
            if (deltaX > 0) {
                // Swipe left
                if (isLastImage && hasMultipleRoomTypes) {
                    // At last image, swipe to next room type
                    nextRoomType();
                } else {
                    // Navigate to next image
                    nextLightboxRoomTypeImage();
                }
            } else {
                // Swipe right
                if (isFirstImage && hasMultipleRoomTypes) {
                    // At first image, swipe to previous room type
                    previousRoomType();
                } else {
                    // Navigate to previous image
                    previousLightboxRoomTypeImage();
                }
            }
        }
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
            packageAddOns.value = response.data.add_ons || [];
            globalSst.value = response.data.global_sst || null;
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
        adults: null,
        children: null,
        infants: null
    }]
});

// Add computed properties for room management
const totalGuests = computed(() => {
    return form.rooms.reduce((total, room) => {
        const adults = room.adults || 0;
        const children = room.children || 0;
        const infants = room.infants || 0;
        return total + adults + children + infants;
    }, 0);
});

const canAddRoom = computed(() => {
    return form.rooms.length < 5; // Maximum 5 rooms
});

const addRoom = () => {
    if (canAddRoom.value) {
        form.rooms.push({
            room_type_id: null,
            adults: null,
            children: null,
            infants: null
        });
    }
};

const removeRoom = (index) => {
    if (form.rooms.length > 1) {
        form.rooms.splice(index, 1);
    }
};

const getRoomMaxChildren = (room) => {
    const maxOccupancy = roomTypes.value.find(rt => rt.id === room.room_type_id)?.max_occupancy || 4;
    return Math.max(0, maxOccupancy - room.adults - room.infants);
};

const getRoomMaxInfants = (room) => {
    const maxOccupancy = roomTypes.value.find(rt => rt.id === room.room_type_id)?.max_occupancy || 4;
    return Math.max(0, maxOccupancy - room.adults - room.children);
};

const getRoomMaxAdults = (room) => {
    const maxOccupancy = roomTypes.value.find(rt => rt.id === room.room_type_id)?.max_occupancy || 4;
    return Math.max(1, maxOccupancy - room.children - room.infants);
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
            children: '',
            infants: ''
        };

        if (!room.room_type_id) {
            roomErrors.room_type_id = 'Please select a room type';
            isValid = false;
        }

        const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
        const maxOccupancy = roomType?.max_occupancy || 4;
        const totalOccupants = (room.adults || 0) + (room.children || 0) + (room.infants || 0);

        if (!room.adults || room.adults < 1) {
            roomErrors.adults = 'Please select at least 1 adult';
            isValid = false;
        }

        if (totalOccupants > maxOccupancy) {
            roomErrors.adults = `Maximum ${maxOccupancy} guests per room (including infants)`;
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

// Watch for room type changes to set default guest values
watch(() => form.rooms, (newRooms) => {
    newRooms.forEach((room, index) => {
        if (room.room_type_id && (room.adults === null || room.children === null || room.infants === null)) {
            // Set default values when room type is selected
            room.adults = 1;
            room.children = 0;
            room.infants = 0;
        }
    });
}, { deep: true });

// Watch for changes in selectedAddOns to clear pax data when add-on is deselected
watch(selectedAddOns, (newSelected, oldSelected) => {
    // Find add-ons that were deselected
    const deselected = oldSelected.filter(id => !newSelected.includes(id));
    
    // Clear pax data for deselected add-ons
    deselected.forEach(addOnId => {
        const index = addOnPaxData.value.findIndex(item => item.id === addOnId);
        if (index !== -1) {
            addOnPaxData.value.splice(index, 1);
        }
    });
}, { deep: true });

// Add-ons methods
const getAddOnPax = (addOnId) => {
    let addOnPax = addOnPaxData.value.find(item => item.id === addOnId);
    if (!addOnPax) {
        addOnPax = { id: addOnId, adults: 0, children: 0, infants: 0 };
        addOnPaxData.value.push(addOnPax);
    }
    return addOnPax;
};

const getSelectedAddOnsForAPI = () => {
    return selectedAddOns.value
        .filter(addOnId => {
            // Only include add-ons that are actually selected (checkbox checked)
            // and have at least one guest selected
            const pax = getAddOnPax(addOnId);
            return pax.adults > 0 || pax.children > 0 || pax.infants > 0;
        })
        .map(addOnId => ({
            id: addOnId,
            adults: getAddOnPax(addOnId).adults,
            children: getAddOnPax(addOnId).children,
            infants: getAddOnPax(addOnId).infants
        }));
};

// Calculate individual add-on subtotal
const calculateAddOnSubtotal = (addOn) => {
    const pax = getAddOnPax(addOn.id);
    const adultTotal = (pax.adults || 0) * (addOn.adult_price || 0);
    const childTotal = (pax.children || 0) * (addOn.child_price || 0);
    const infantTotal = (pax.infants || 0) * (addOn.infant_price || 0);
    return adultTotal + childTotal + infantTotal;
};

// Toggle add-on description on click
const toggleAddOnDescription = (addOnId) => {
    if (openAddOnDescriptions.value[addOnId]) {
        delete openAddOnDescriptions.value[addOnId];
    } else {
        openAddOnDescriptions.value[addOnId] = true;
    }
};

// Handle tooltip mouse enter
const handleTooltipMouseEnter = (addOnId) => {
    if (!openAddOnDescriptions.value[addOnId]) {
        showAddOnTooltip.value = addOnId;
    }
};

// Handle tooltip mouse leave
const handleTooltipMouseLeave = (addOnId) => {
    if (!openAddOnDescriptions.value[addOnId]) {
        showAddOnTooltip.value = null;
    }
};

const calculatePrice = async () => {
    if (!validateForm()) return;

    try {
        const response = await axios.post(route('api.package-calculate-price'), {
            package_id: packageData.value.id,
            rooms: form.rooms.map(room => ({
                room_type: room.room_type_id,
                adults: room.adults,
                children: room.children,
                infants: room.infants
            })),
            start_date: form.start_date,
            end_date: form.end_date,
            add_ons: getSelectedAddOnsForAPI()
        });

        if (response.data.success) {
            calculatedPrice.value = parseFloat(response.data.total) || 0;
            priceBreakdown.value = response.data;
            
            // Calculate total guests
            const totalAdults = form.rooms.reduce((sum, room) => sum + (room.adults || 0), 0);
            const totalChildren = form.rooms.reduce((sum, room) => sum + (room.children || 0), 0);
            const totalInfants = form.rooms.reduce((sum, room) => sum + (room.infants || 0), 0);
            
            // Update booking summary with total guests
            bookingSummary.value = {
                rooms: form.rooms.map(room => {
                    const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
                    return {
                        roomType: roomType?.name || '',
                        adults: room.adults || 0,
                        children: room.children || 0,
                        infants: room.infants || 0
                    };
                }),
                startDate: form.start_date,
                endDate: form.end_date,
                duration: Math.ceil((new Date(form.end_date) - new Date(form.start_date)) / (1000 * 60 * 60 * 24)),
                seasonType: response.data.season_type || '',
                dateType: response.data.date_type || '',
                isWeekend: response.data.is_weekend || false,
                totalAdults,
                totalChildren,
                totalInfants
            };

            // Add total guests to price breakdown summary
            priceBreakdown.value.summary = {
                ...priceBreakdown.value.summary,
                total_adults: totalAdults,
                total_children: totalChildren,
                total_infants: totalInfants
            };

            currentStep.value = 3;
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

const formatNumber = (number, withSst = true) => {
    if (withSst) {
        number = calculatePackagePriceWithSst(number)
    }
    
    const num = parseFloat(number);
    if (isNaN(num)) return '0.00';
    return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

// Computed property to check if there are add-ons
const hasAddOns = computed(() => {
    return packageAddOns.value && packageAddOns.value.length > 0;
});

// Helper function to get the displayed step number (adjusting for hidden add-ons step)
const getDisplayStepNumber = (step) => {
    if (!hasAddOns.value) {
        // If no add-ons, step 2 is hidden, so adjust:
        // Step 1 -> 1, Step 2 -> hidden, Step 3 -> 2, Step 4 -> 3
        if (step === 1) return 1;
        if (step === 2) return null; // Hidden
        if (step === 3) return 2;
        if (step === 4) return 3;
    }
    return step;
};

// Helper function to check if a step should be active based on current step
const isStepActive = (step) => {
    if (!hasAddOns.value) {
        // If no add-ons, step 2 is skipped
        if (step === 2) return false;
        if (step === 3) return currentStep.value >= 2; // Step 3 becomes step 2 visually
        if (step === 4) return currentStep.value >= 3; // Step 4 becomes step 3 visually
    }
    return currentStep.value >= step;
};

// Helper function to go back to the previous step
const goToPreviousStep = () => {
    if (currentStep.value === 4) {
        // From step 4 (Booking Details), always go back to step 3 (Price Summary)
        currentStep.value = 3;
    } else if (currentStep.value === 3) {
        // From step 3 (Price Summary), go back to step 2 (Add-ons) if has add-ons, otherwise step 1
        currentStep.value = hasAddOns.value ? 2 : 1;
    } else if (currentStep.value === 2) {
        // From step 2 (Add-ons), always go back to step 1
        currentStep.value = 1;
    }
};

// Add new methods for step handling
const handleStep1Submit = async () => {
    if (!validateForm()) return;
    await calculatePrice();
    if (calculatedPrice.value !== null) {
        // Skip step 2 (add-ons) if there are no add-ons
        currentStep.value = hasAddOns.value ? 2 : 3;
    }
};

const handleStep2Submit = async () => {
    // Calculate price with current add-ons selection
    await calculatePrice();
    if (calculatedPrice.value !== null) {
        currentStep.value = 3;
    }
};

const package_sst_percentage = computed(() => {
    // Safety check: return 0 if data is not loaded yet
    if (!packageData.value || !globalSst.value) {
        return 0;
    }

    // Check if package SST is enabled
    const packageSstEnabled = packageData.value.sst_enable === true;

    // Check if global SST is enabled
    const globalSstEnabled = globalSst.value.enabled === true;

    // SST should be applied only when BOTH package SST and global SST are enabled
    // Packages don't have their own percentage - they use the global SST percentage
    if (packageSstEnabled && globalSstEnabled) {
        return globalSst.value.percent || 0;
    }

    // If either is disabled, return 0
    return 0;
});

const calculatePackagePriceWithSst = (amount) => {
    if (!amount || amount === 0) return 0;
    if (package_sst_percentage.value === 0) return amount;

    return amount * (1 + package_sst_percentage.value / 100)
};

// Add new refs for booking success state
const bookingSuccess = ref(null);
const paymentStatus = ref(null);
const paymentError = ref(null);
const transactionId = ref(null);
const isProcessingPayment = ref(false);

const validateBookingForm = () => {
    let isValid = true;
    bookingValidationErrors.value = {
        booking_name: '',
        phone_number: '',
        booking_ic: '',
        booking_email: ''
    };

    // Validate booking name
    if (!bookingForm.value.booking_name.trim()) {
        bookingValidationErrors.value.booking_name = 'Booking name is required';
        isValid = false;
    }

    // Validate phone number
    if (!bookingForm.value.phone_number.trim()) {
        bookingValidationErrors.value.phone_number = 'Phone number is required';
        isValid = false;
    } else if (bookingForm.value.phone_number.trim().length < 7) {
        bookingValidationErrors.value.phone_number = 'Please enter a valid phone number (at least 7 digits)';
        isValid = false;
    }

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
                adults: room.adults || 0,
                children: room.children || 0,
                infants: room.infants || 0
            })),
            booking_name: bookingForm.value.booking_name,
            phone_number: bookingForm.value.country_code + bookingForm.value.phone_number,
            booking_ic: bookingForm.value.booking_ic,
            booking_email: bookingForm.value.booking_email,
            start_date: form.start_date,
            end_date: form.end_date,
            total_price: priceBreakdown.value.total,
            special_remarks: bookingForm.value.special_remarks.trim(),
            add_ons: getSelectedAddOnsForAPI()
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
        const r = await axios.post(route('payment.initiate', bookingSuccess.value.uuid));
        if (r.data.success && r.data.payment_data) {
            const f = Object.entries(r.data.payment_data).reduce((form, [k, v]) => {
            form.innerHTML += `<input type="hidden" name="${k}" value="${v}">`;
            return form;
        }, 
        document.createElement('form'));
        f.method = 'post';
        f.action = r.data.payment_url;
        document.body.appendChild(f);
        console.log(f.outerHTML);
        f.submit();
        } else throw new Error('Failed to initiate payment');
    } catch (e) {
        console.error(e);
        Swal.fire({ icon: 'error', title: 'Payment Error', text: 'Unable to create payment. Please contact support.', confirmButtonColor: '#EF4444' });
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

const validateRoomOccupancy = (room, index) => {
    const roomErrors = {
        room_type_id: '',
        adults: '',
        children: '',
        infants: ''
    };

    // Get room type and max occupancy
    const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
    const maxOccupancy = roomType?.max_occupancy || 4;

    // Validate adults
    if (!room.adults || room.adults < 1) {
        roomErrors.adults = 'At least 1 adult is required';
        return roomErrors;
    }

    // Validate total occupancy
    const totalOccupants = (room.adults || 0) + (room.children || 0) + (room.infants || 0);
    if (totalOccupants > maxOccupancy) {
        const excess = totalOccupants - maxOccupancy;
        if ((room.infants || 0) > 0) {
            roomErrors.infants = `Too many guests. Please reduce by ${excess} guest(s)`;
        } else if ((room.children || 0) > 0) {
            roomErrors.children = `Too many guests. Please reduce by ${excess} guest(s)`;
        } else {
            roomErrors.adults = `Maximum ${maxOccupancy} guests per room`;
        }
        return roomErrors;
    }

    // Validate children
    const maxChildren = getRoomMaxChildren(room);
    if ((room.children || 0) > maxChildren) {
        roomErrors.children = `Maximum ${maxChildren} children allowed for this room`;
        return roomErrors;
    }

    // Validate infants
    const maxInfants = getRoomMaxInfants(room);
    if ((room.infants || 0) > maxInfants) {
        roomErrors.infants = `Maximum ${maxInfants} infants allowed for this room`;
        return roomErrors;
    }

    return roomErrors;
};

// Add a new ref for helper messages
const helperMessages = ref({});

const resetHelperMessage = (roomIndex, field) => {
    if (helperMessages.value[roomIndex]) {
        helperMessages.value[roomIndex][field] = '';
    }
};

const showHelperMessage = (roomIndex, field, message, duration = 10000) => {
    if (!helperMessages.value[roomIndex]) {
        helperMessages.value[roomIndex] = {};
    }
    helperMessages.value[roomIndex][field] = message;
    
    // Clear the message after duration
    setTimeout(() => {
        if (helperMessages.value[roomIndex]) {
            helperMessages.value[roomIndex][field] = '';
        }
    }, duration);
};

const handleAdultsChange = (room, index) => {
    resetHelperMessage(index, 'adults');
    const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
    const maxOccupancy = roomType?.max_occupancy || 4;
    const oldAdults = room.adults;
    
    // Ensure adults is at least 1 and not more than max capacity
    room.adults = Math.max(1, Math.min(getRoomMaxAdults(room), parseInt(room.adults) || 1));
    
    // Show helper message if value was adjusted
    if (oldAdults <= 1) {
        showHelperMessage(index, 'adults', `Minimum 1 adult is required per room.`);
    }
    else if (oldAdults == maxOccupancy) {
        console.log('ok');
    }
    else if (oldAdults !== room.adults) {
        showHelperMessage(index, 'adults', `Maximum ${maxOccupancy} guests per room. Adults adjusted to ${room.adults}.`);
    }
    
    // Adjust children and infants if needed
    const maxChildren = getRoomMaxChildren(room);
    const maxInfants = getRoomMaxInfants(room);
    
    if ((room.children || 0) > maxChildren) {
        const oldChildren = room.children;
        room.children = maxChildren;
        showHelperMessage(index, 'children', `Children reduced to ${maxChildren} to maintain maximum occupancy of ${maxOccupancy} guests`);
    }
    
    if ((room.infants || 0) > maxInfants) {
        const oldInfants = room.infants;
        room.infants = maxInfants;
        showHelperMessage(index, 'infants', `Infants reduced to ${maxInfants} to maintain maximum occupancy of ${maxOccupancy} guests`);
    }
    
    // Validate and update errors
    validationErrors.value.rooms[index] = validateRoomOccupancy(room, index);
};

const handleChildrenChange = (room, index) => {
    resetHelperMessage(index, 'children');
    const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
    const maxOccupancy = roomType?.max_occupancy || 4;
    const oldChildren = room.children;
    
    // Ensure children is not negative
    room.children = Math.max(0, Math.min(getRoomMaxChildren(room), parseInt(room.children) || 0));
    
    // Show helper message if value was adjusted
    if (oldChildren !== room.children) {
        showHelperMessage(index, 'children', `Maximum ${maxOccupancy} guests per room. Children adjusted to ${room.children}`);
    }
    
    // Adjust infants if needed
    const maxInfants = getRoomMaxInfants(room);
    if (room.infants > maxInfants) {
        const oldInfants = room.infants;
        room.infants = maxInfants;
        showHelperMessage(index, 'infants', `Infants reduced to ${maxInfants} to maintain maximum occupancy of ${maxOccupancy} guests`);
    }
    
    // Validate and update errors
    validationErrors.value.rooms[index] = validateRoomOccupancy(room, index);
};

const handleInfantsChange = (room, index) => {
    resetHelperMessage(index, 'infants');
    const roomType = roomTypes.value.find(rt => rt.id === room.room_type_id);
    const maxOccupancy = roomType?.max_occupancy || 4;
    const oldInfants = room.infants;
    
    // Ensure infants is not negative
    room.infants = Math.max(0, Math.min(getRoomMaxInfants(room), parseInt(room.infants) || 0));
    
    // Show helper message if value was adjusted
    if (oldInfants !== room.infants) {
        showHelperMessage(index, 'infants', `Maximum ${maxOccupancy} guests per room. Infants adjusted to ${room.infants}`);
    }
    
    // Validate and update errors
    validationErrors.value.rooms[index] = validateRoomOccupancy(room, index);
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
    
    /* Ensure proper scrolling on mobile */
    body {
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Add extra bottom padding for mobile safe area */
    .min-h-screen {
        padding-bottom: env(safe-area-inset-bottom, 2rem);
    }
    
    /* Add-on tooltip responsive positioning */
    .addon-info-button .absolute {
        left: auto !important;
        right: 0 !important;
        bottom: auto !important;
        top: 100% !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0 !important;
        width: calc(100vw - 2rem) !important;
        max-width: 280px !important;
        z-index: 9999 !important;
    }
    
    /* Ensure table container allows overflow for tooltips */
    .bg-white.border.rounded-md {
        overflow: visible !important;
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
    /* Disable touch swiping but allow tap to open lightbox */
    touch-action: manipulation;
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

/* Make swiper slides clickable on mobile */
.room-image-swiper :deep(.swiper-slide) {
    cursor: pointer;
    touch-action: manipulation;
}

/* Remove navigation button styles since we're not using them anymore */

/* Add animation for helper messages */
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Lightbox Modal Styles */
:deep(dialog[open]) {
    padding: 0 !important;
}

/* Make lightbox images responsive */
.lightbox-image {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    /* Prevent image drag on mobile */
    -webkit-user-drag: none;
    user-select: none;
    /* Allow pointer events for touch interactions */
    pointer-events: auto;
    /* Enable touch interactions for swiping */
    touch-action: pan-x;
}

/* Package image - ensure no rounded corners on mobile */
@media (max-width: 639px) {
    /* Target the package image wrapper and image on mobile */
    .package-image-wrapper {
        border-radius: 0 !important;
        -webkit-border-radius: 0 !important;
        -moz-border-radius: 0 !important;
    }
    
    .package-image-wrapper img {
        border-radius: 0 !important;
        -webkit-border-radius: 0 !important;
        -moz-border-radius: 0 !important;
    }
}

/* Lightbox container spacing */
.lightbox-container {
    padding: 0;
    margin: 0;
    width: 100%;
    /* Enable touch interactions */
    touch-action: pan-y pinch-zoom;
}

/* Touch-friendly classes */
.touch-none {
    touch-action: none;
    -webkit-tap-highlight-color: transparent;
}

.touch-pan-y {
    touch-action: pan-y;
}

/* Improve mobile touch targets */
@media (max-width: 640px) {
    /* Compact spacing on mobile */
    .lightbox-container {
        min-height: auto !important;
    }
    
    /* Navigation buttons on mobile */
    .lightbox-nav-button {
        padding: 0.75rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
        touch-action: manipulation;
    }
    
    /* Close button on mobile */
    .lightbox-close-button {
        padding: 0.75rem !important;
        min-width: 3rem !important;
        min-height: 3rem !important;
        touch-action: manipulation;
        /* Position above room type selector on mobile */
        top: 0.5rem !important;
        right: 0.5rem !important;
    }
    
    /* Better image display on mobile */
    .lightbox-image {
        max-height: 75vh !important;
        width: 100%;
    }
    
    /* Prevent text selection on mobile */
    .lightbox-container * {
        -webkit-user-select: none;
        user-select: none;
    }
    
    /* Room type selector styling */
    .lightbox-container .bg-white\/95 {
        max-width: calc(100% - 4rem);
    }
    
    /* Full screen modal on mobile */
    :deep(.lightbox-container) {
        height: 100vh !important;
        margin-bottom: 0 !important;
    }
    
    /* Override Modal component margin on mobile for room type lightbox */
    :deep(div[class*="mb-6"]) {
        margin-bottom: 0 !important;
    }
}

/* Tablet and Desktop improvements */
@media (min-width: 768px) {
    :deep(.sm\\:max-w-7xl) {
        max-width: 90vw !important;
    }
}

/* Hide scrollbar for room type selector */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
