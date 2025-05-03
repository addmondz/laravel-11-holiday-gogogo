<template>
    <div class="min-h-screen bg-gray-100">
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
        <div v-else class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8" style="padding-bottom: 50px;">
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
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ packageData.name }}</h1>
                <p class="text-gray-600 mb-6">{{ packageData.description }}</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Location</h2>
                        <p class="text-gray-600">{{ packageData.location }}</p>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Duration</h2>
                        <p class="text-gray-600">{{ packageData.package_min_days }} - {{ packageData.package_max_days }} days</p>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Display Prices</h2>
                        <p class="text-gray-600">Adult: MYR {{ formatNumber(packageData.display_price_adult) }}</p>
                        <p class="text-gray-600">Child: MYR {{ formatNumber(packageData.display_price_child) }}</p>
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
                <form @submit.prevent="calculatePrice" class="space-y-6">
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
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-indigo-600">
                                            MYR {{ formatNumber(roomType.price_per_night) }}
                                        </p>
                                        <p class="text-sm text-gray-500">per night</p>
                                    </div>
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
                                :min="new Date().toISOString().split('T')[0]"
                                :class="[
                                    'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                    validationErrors.start_date ? 'border-red-500' : 'border-gray-300'
                                ]"
                                required
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
                                :class="[
                                    'mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
                                    validationErrors.end_date ? 'border-red-500' : 'border-gray-300'
                                ]"
                                required
                            />
                            <p v-if="validationErrors.end_date" class="mt-1 text-sm text-red-600">{{ validationErrors.end_date }}</p>
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

                    <!-- Price Display -->
                    <div v-if="calculatedPrice !== null" class="bg-gray-50 rounded-lg p-4">
                        <!-- Booking Summary -->
                        <div v-if="bookingSummary" class="mb-6 border-b pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Summary</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Season</p>
                                    <p class="font-medium text-gray-900">
                                        {{ bookingSummary.seasonType }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Date Type</p>
                                    <p class="font-medium text-gray-900">
                                        {{ bookingSummary.dateType.charAt(0).toUpperCase() + bookingSummary.dateType.slice(1) }}
                                        <span v-if="bookingSummary.isWeekend" class="ml-2 text-sm text-indigo-600">(Weekend Rate)</span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Room Type</p>
                                    <p class="font-medium text-gray-900">{{ bookingSummary.roomType.name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Check-in</p>
                                    <p class="font-medium text-gray-900">{{ new Date(bookingSummary.startDate).toLocaleDateString() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Check-out</p>
                                    <p class="font-medium text-gray-900">{{ new Date(bookingSummary.endDate).toLocaleDateString() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Duration</p>
                                    <p class="font-medium text-gray-900">{{ bookingSummary.nights }} nights</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Guests</p>
                                    <p class="font-medium text-gray-900">
                                        {{ bookingSummary.adults }} Adult{{ bookingSummary.adults > 1 ? 's' : '' }}
                                        <span v-if="bookingSummary.children > 0">
                                            , {{ bookingSummary.children }} Child{{ bookingSummary.children > 1 ? 'ren' : '' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Estimated Price</h3>
                        <p class="text-2xl font-bold text-indigo-600">MYR {{ formatNumber(calculatedPrice) }}</p>

                        <!-- Price Breakdown -->
                        <div v-if="priceBreakdown" class="mt-4 space-y-4">
                            <!-- Base Charge -->
                            <div class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-2">Base Charge (per night)</h4>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Adults ({{ priceBreakdown.base_charge.adult_qty }})</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.base_charge.per_adult) }} × {{ bookingSummary.nights }} nights</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Children ({{ priceBreakdown.base_charge.child_qty }})</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.base_charge.per_child) }} × {{ bookingSummary.nights }} nights</span>
                                    </div>
                                    <div class="flex justify-between font-medium text-gray-900">
                                        <span>Subtotal</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.base_charge.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Surcharge -->
                            <div v-if="priceBreakdown.surcharge.total !== '0.00'" class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-2">Surcharge (per night)</h4>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Adults ({{ priceBreakdown.surcharge.adult_qty }})</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.surcharge.per_adult) }} × {{ bookingSummary.nights }} nights</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Children ({{ priceBreakdown.surcharge.child_qty }})</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.surcharge.per_child) }} × {{ bookingSummary.nights }} nights</span>
                                    </div>
                                    <div class="flex justify-between font-medium text-gray-900">
                                        <span>Subtotal</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.surcharge.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Extra Charges -->
                            <div v-if="priceBreakdown.extra_charges.total !== '0.00'" class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-2">Extra Charges (per night)</h4>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Adults ({{ priceBreakdown.extra_charges.adult_qty }})</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.extra_charges.per_adult) }} × {{ bookingSummary.nights }} nights</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Children ({{ priceBreakdown.extra_charges.child_qty }})</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.extra_charges.per_child) }} × {{ bookingSummary.nights }} nights</span>
                                    </div>
                                    <div class="flex justify-between font-medium text-gray-900">
                                        <span>Subtotal</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.extra_charges.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Add-ons -->
                            <div v-if="priceBreakdown.add_ons.items.length > 0" class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-2">Add-ons (per night)</h4>
                                <div class="space-y-3">
                                    <div v-for="(item, index) in priceBreakdown.add_ons.items" :key="index" class="text-sm text-gray-600">
                                        <div class="font-medium text-gray-900">{{ item.name }}</div>
                                        <div class="space-y-1 mt-1">
                                            <div class="flex justify-between">
                                                <span>Adults ({{ item.adult_qty }})</span>
                                                <span>MYR {{ formatNumber(item.adult_price) }} × {{ bookingSummary.nights }} nights</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Children ({{ item.child_qty }})</span>
                                                <span>MYR {{ formatNumber(item.child_price) }} × {{ bookingSummary.nights }} nights</span>
                                            </div>
                                            <div class="flex justify-between font-medium text-gray-900">
                                                <span>Subtotal</span>
                                                <span>MYR {{ formatNumber(item.total) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between font-medium text-gray-900 border-t pt-2">
                                        <span>Add-ons Total</span>
                                        <span>MYR {{ formatNumber(priceBreakdown.add_ons.total) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t pt-4">
                                <div class="flex justify-between text-lg font-bold text-gray-900">
                                    <span>Total</span>
                                    <span>MYR {{ formatNumber(calculatedPrice) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :disabled="form.processing"
                        >
                            Calculate Price
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import LoadingComponent from '@/Components/LoadingComponent.vue';

const props = defineProps({
    uuid: String
});

const packageData = ref(null);
const currentImageIndex = ref(0);
const calculatedPrice = ref(null);
const priceBreakdown = ref(null);
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

        isLoading.value = false;
    } catch (error) {
        console.error('Error fetching package:', error);
        packageData.value = null;
        isLoading.value = false;
    }

    // Start the carousel auto-rotation
    startAutoRotation();
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
    }
    if (!form.end_date) {
        validationErrors.value.end_date = 'Please select an end date';
        isValid = false;
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
    }

    // Validate adults
    if (!form.adults || form.adults < 1) {
        validationErrors.value.adults = 'Please select at least 1 adult';
        isValid = false;
    }

    return isValid;
};

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
            calculatedPrice.value = parseFloat(response.data.total);
            priceBreakdown.value = response.data.breakdown;
            bookingSummary.value = {
                roomType: roomTypes.value.find(rt => rt.id === form.room_type_id),
                startDate: form.start_date,
                endDate: form.end_date,
                adults: form.adults,
                children: form.children,
                nights: Math.ceil((new Date(form.end_date) - new Date(form.start_date)) / (1000 * 60 * 60 * 24)),
                season: response.data.season,
                seasonType: response.data.season_type,
                dateType: response.data.date_type,
                isWeekend: response.data.is_weekend
            };
        }
    } catch (error) {
        console.error('Error calculating price:', error);
        calculatedPrice.value = null;
        priceBreakdown.value = null;
        bookingSummary.value = null;
    }
};

// Add watchers for date changes
watch([() => form.start_date, () => form.end_date], () => {
    validateForm();
});

// Add auto-rotation functionality
const startAutoRotation = () => {
    const interval = setInterval(() => {
        nextImage();
    }, 5000); // Change image every 5 seconds

    // Clear interval when component is unmounted
    onUnmounted(() => {
        clearInterval(interval);
    });
};

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % mockImages.length;
};

const previousImage = () => {
    currentImageIndex.value = (currentImageIndex.value - 1 + mockImages.length) % mockImages.length;
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
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
