<template>
    <Head title="Edit Package" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <!-- <label for="display_price_adult" class="block text-sm font-medium text-gray-700">Display Price (Adult)</label> -->
                                        <label for="display_price_adult" class="block text-sm font-medium text-gray-700">Display Price</label>
                                        <input
                                            type="number"
                                            id="display_price_adult"
                                            v-model="form.display_price_adult"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.display_price_adult" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.display_price_adult }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="package_max_days" class="block text-sm font-medium text-gray-700">Package Duration (Nights)</label>
                                        <input
                                            type="number"
                                            id="package_max_days"
                                            v-model="form.package_max_days"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                        <div v-if="form.errors.package_max_days" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_max_days }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Weekend Days</label>
                                        <div class="grid grid-cols-7 gap-2">
                                            <div v-for="(day, index) in weekDays" :key="index" class="flex items-center">
                                                <input
                                                    type="checkbox"
                                                    :id="'day-' + index"
                                                    :value="index"
                                                    v-model.number="form.weekend_days"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                />
                                                <label :for="'day-' + index" class="ml-2 text-sm text-gray-700">
                                                    {{ day }}
                                                </label>
                                            </div>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">Select which days are considered weekends for pricing purposes.</p>
                                        <div v-if="form.errors.weekend_days" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.weekend_days }}
                                        </div>
                                    </div>
            
                                    <!-- <div>
                                        <label for="display_price_child" class="block text-sm font-medium text-gray-700">Display Price (Child)</label>
                                        <input
                                            type="number"
                                            id="display_price_child"
                                            v-model="form.display_price_child"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.display_price_child" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.display_price_child }}
                                        </div>
                                    </div> -->
                                </div>

                                <div>
                                    <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700">Terms and Conditions</label>
                                    <textarea
                                        id="terms_and_conditions"
                                        v-model="form.terms_and_conditions"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                    <div v-if="form.errors.terms_and_conditions" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.terms_and_conditions }}
                                    </div>
                                </div>

                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                    <input
                                        type="text"
                                        id="location"
                                        v-model="form.location"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <div v-if="form.errors.location" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.location }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label for="package_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input
                                            type="date"
                                            id="package_start_date"
                                            v-model="form.package_start_date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                        <div v-if="form.errors.package_start_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_start_date }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="package_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input
                                            type="date"
                                            id="package_end_date"
                                            v-model="form.package_end_date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.package_end_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_end_date }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Images Section -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Package Images</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <!-- Display existing images -->
                                            <div v-if="form.images && form.images.length > 0" class="flex gap-4 mb-4">
                                                <div v-for="(image, index) in form.images" :key="index" class="relative group">
                                                    <img 
                                                        :src="getImagePreviewUrl(image)" 
                                                        class="h-24 w-full object-cover rounded-lg" 
                                                        alt="Room type image"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeImage(index)"
                                                        class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload Images</span>
                                                    <input
                                                        type="file"
                                                        @change="handleImagesUpload"
                                                        accept="image/*"
                                                        multiple
                                                        class="sr-only"
                                                    />
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.images" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.images }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('packages.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3 text-xs"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    :disabled="form.processing"
                                >
                                    Update Package
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed, ref, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    package: Object
});

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
    { title: 'Edit Package', },
]);

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const form = useForm({
    name: props.package.name,
    description: props.package.description,
    images: props.package.images || [],
    display_price_adult: props.package.display_price_adult,
    display_price_child: props.package.display_price_child,
    package_min_days: props.package.package_max_days,
    package_max_days: props.package.package_max_days,
    terms_and_conditions: props.package.terms_and_conditions,
    location: props.package.location,
    package_start_date: props.package.package_start_date,
    package_end_date: props.package.package_end_date,
    weekend_days: props.package.weekend_days || [0, 6], // Default to Saturday and Sunday
});

const handleImagesUpload = (event) => {
    const files = Array.from(event.target.files);
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const errors = [];

    files.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            errors.push(`${file.name} is not a valid image file. Only JPG, PNG, and GIF are allowed.`);
        }
        if (file.size > maxSize) {
            errors.push(`${file.name} is too large. Maximum file size is 10MB.`);
        }
    });

    if (errors.length > 0) {
        Swal.fire({
            title: 'Invalid Images',
            html: errors.join('<br>'),
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    form.images = [...form.images, ...files];
};

const removeImage = (index) => {
    const image = form.images[index];
    
    // If it's an existing image (string path), add to delete_images
    if (typeof image === 'string') {
        if (!form.delete_images) {
            form.delete_images = [];
        }
        form.delete_images.push(image);
    }
    
    form.images.splice(index, 1);
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


// Clean up preview URLs when component is unmounted
onUnmounted(() => {
    form.images.forEach(image => {
        if (image instanceof File) {
            URL.revokeObjectURL(URL.createObjectURL(image));
        }
    });
});

const submit = () => {
    // Create FormData for file upload
    const formData = new FormData();
    
    // Add all form fields to FormData
    Object.keys(form).forEach(key => {
        if (key === 'images') {
            // Handle images separately
            form.images.forEach((image, index) => {
                if (image instanceof File) {
                    formData.append(`images[${index}]`, image);
                } else if (typeof image === 'string') {
                    // For existing images, just pass the path
                    formData.append(`existing_images[${index}]`, image);
                }
            });
        } else if (key === 'delete_images') {
            // Handle deleted images
            if (form.delete_images && form.delete_images.length > 0) {
                form.delete_images.forEach((image, index) => {
                    formData.append(`delete_images[${index}]`, image);
                });
            }
        } else {
            // JSON-encode arrays like weekend_days
            if (Array.isArray(form[key])) {
                formData.append(key, JSON.stringify(form[key]));
            } else {
                formData.append(key, form[key]);
            }
        }
    });

    // Use axios for better control over the request
    axios.post(route('packages.update', props.package.id), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT' // This is needed because Laravel expects PUT for updates
        }
    })
    .then(response => {
        Swal.fire({
            title: 'Success!',
            text: 'Package has been updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = route('packages.show', props.package.id);
        });
    })
    .catch(error => {
        console.error('Error updating package:', error);
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            const errorMessages = [];
            
            // Format validation errors
            Object.entries(errors).forEach(([field, messages]) => {
                if (Array.isArray(messages)) {
                    errorMessages.push(`${field}: ${messages.join(', ')}`);
                } else {
                    errorMessages.push(`${field}: ${messages}`);
                }
            });

            Swal.fire({
                title: 'Validation Error',
                html: errorMessages.join('<br>'),
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: error.response?.data?.message || 'Failed to update package. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
};
</script>
