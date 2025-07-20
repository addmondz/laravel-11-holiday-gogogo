<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6">
            <div class="flex justify-between items-center">
                <h3 class="text-md font-medium text-gray-900">Package Details</h3>
                <Link
                    :href="route('packages.edit', package.id)"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                >
                    Edit Package
                </Link>
            </div>
            <div class="flex items-start">
                <!-- Image placeholder if needed -->
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Name</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ package.name }}</p>
                </div>
                <div>
                    <h4 class="mt-4 text-sm font-medium text-gray-500">Location</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ package.location }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Description</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ package.description }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Display Prices</h4>
                    <div class="mt-1">
                        <p class="text-sm text-gray-900">MYR {{ formatNumber(package.display_price_adult) }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Duration</h4>
                    <p class="mt-1 text-sm text-gray-900">
                        {{ package.package_max_days + 1 }} Days {{ package.package_max_days }} {{ package.package_max_days > 1 ? 'Nights' : 'Night' }}
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Weekend Days</h4>
                    <p class="mt-1 text-sm text-gray-900">
                        <span v-if="package.weekend_days && package.weekend_days.length > 0">
                            {{ formatWeekendDays(package.weekend_days) }}
                        </span>
                        <span v-else class="text-gray-400">Not configured</span>
                    </p>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Terms and Conditions</h4>
                <p class="mt-1 text-sm text-gray-900">{{ package.terms_and_conditions }}</p>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Package Date</h4>
                    <p class="mt-1 text-sm text-gray-900">
                        {{ moment(package.package_start_date).format('DD/MM/YYYY') }} - {{ moment(package.package_end_date).format('DD/MM/YYYY') }}
                    </p>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                <p class="mt-1 text-sm text-gray-900">{{ moment(package.created_at).format('DD-MM-YYYY HH:mm:ss A') }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                <p class="mt-1 text-sm text-gray-900">{{ moment(package.updated_at).format('DD-MM-YYYY HH:mm:ss A') }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Link</h4>
                <div class="mt-1 flex items-center space-x-2">
                    <a
                        :href="route('quotation.with-hash', package.uuid)"
                        target="_blank"
                        class="text-sm text-indigo-600 hover:text-indigo-900 break-all"
                    >
                        {{ route('quotation.with-hash', package.uuid) }}
                    </a>
                    <button
                        @click="copyLink(route('quotation.with-hash', package.uuid))"
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
                        <div v-if="package.images && package.images.length > 0" class="grid grid-cols-2 gap-4 mb-4">
                            <div v-for="(image, index) in package.images" :key="index">
                                <img 
                                    :src="getImageUrl(image)" 
                                    class="h-16 w-16 object-cover rounded-lg cursor-pointer" 
                                    @click="showImageModal(image)"
                                    alt="Package image"
                                />
                            </div>
                        </div>
                        <div v-else class="text-gray-400 text-sm text-center">No images</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import moment from 'moment';
import Swal from 'sweetalert2';

const props = defineProps({
    package: {
        type: Object,
        required: true
    }
});

const formatNumber = (number) => {
    if (!number) return '0.00';
    return parseFloat(number).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const formatWeekendDays = (weekendDays) => {
    if (!weekendDays || weekendDays.length === 0) return 'Not configured';
    
    const dayNames = {
        0: 'Sunday',
        1: 'Monday', 
        2: 'Tuesday',
        3: 'Wednesday',
        4: 'Thursday',
        5: 'Friday',
        6: 'Saturday'
    };
    
    return weekendDays.map(day => dayNames[day]).join(', ');
};

const getImageUrl = (imagePath) => {
    if (imagePath.startsWith('images/')) {
        return `/${imagePath}`;
    }
    if (imagePath.startsWith('packages/')) {
        return `/images/${imagePath}`;
    }
    return `/images/${imagePath}`;
};

const showImageModal = (image) => {
    Swal.fire({
        imageUrl: getImageUrl(image),
        imageAlt: 'Package image',
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
</script>

<style scoped>
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