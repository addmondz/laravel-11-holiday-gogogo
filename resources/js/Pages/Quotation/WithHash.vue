<template>
    <div class="min-h-screen bg-gray-100">
        <!-- WordPress Content -->
        <!-- <InjectedContent /> -->

        <!-- Not Found State -->
        <div v-if="!packageData" class="min-h-screen flex items-center justify-center">
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
                    </div>

                    <!-- Date Selection -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="form.start_date"
                                :min="packageData.package_start_date"
                                :max="packageData.package_end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="form.end_date"
                                :min="form.start_date"
                                :max="packageData.package_end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Estimated Price</h3>
                        <p class="text-2xl font-bold text-indigo-600">MYR {{ formatNumber(calculatedPrice) }}</p>
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
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import InjectedContent from '@/Components/InjectedContent.vue';

const props = defineProps({
    uuid: String
});

const packageData = ref(null);
const currentImageIndex = ref(0);
const calculatedPrice = ref(null);
const roomTypes = ref([]);
const selectedRoomType = ref(null);

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

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const nextImage = () => {
    currentImageIndex.value = (currentImageIndex.value + 1) % mockImages.length;
};

const previousImage = () => {
    currentImageIndex.value = (currentImageIndex.value - 1 + mockImages.length) % mockImages.length;
};

const calculatePrice = () => {
    // For now, just return 0 as requested
    calculatedPrice.value = 0;
};

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
</script>

<style scoped>
/* Your component styles here */
</style>
