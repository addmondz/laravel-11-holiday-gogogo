<template>
    <Head title="Create Package" />
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

                                <!-- Images Section -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Package Images</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <!-- Display uploaded images -->
                                            <div v-if="form.images && form.images.length > 0" class="flex gap-4 mb-4">
                                                <div v-for="(image, index) in imagePreviews" :key="index" class="relative group">
                                                    <img :src="image" class="h-24 w-full object-cover rounded-lg" />
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

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label for="display_price_adult" class="block text-sm font-medium text-gray-700"><b class="text-[15px]">Display Price</b> / Adult Base Price</label>
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
                                        <label for="display_price_adult" class="block text-sm font-medium text-gray-700">Child Base Price</label>
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
                                    </div>
                                    <div>
                                        <label for="adult_surcharge" class="block text-sm font-medium text-gray-700">Adult Surcharge</label>
                                        <input
                                            type="number"
                                            id="adult_surcharge"
                                            v-model="form.adult_surcharge"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.adult_surcharge" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.adult_surcharge }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="child_surcharge" class="block text-sm font-medium text-gray-700">Child Surcharge</label>
                                        <input
                                            type="number"
                                            id="child_surcharge"
                                            v-model="form.child_surcharge"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <div v-if="form.errors.child_surcharge" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.child_surcharge }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="package_days" class="block text-sm font-medium text-gray-700">Package Duration (Nights)</label>
                                        <input
                                            type="number"
                                            id="package_days"
                                            v-model="form.package_days"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                        <div v-if="form.errors.package_days" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_days }}
                                        </div>
                                    </div>
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
                                            :max="form.package_end_date ? form.package_end_date : undefined"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.package_start_date || form.errors.package_end_date }"
                                            required
                                            @change="validateDates"
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
                                            :min="form.package_start_date ? new Date(new Date(form.package_start_date).getTime() + 86400000).toISOString().split('T')[0] : undefined"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.package_end_date }"
                                            required
                                        />
                                        <div v-if="form.errors.package_end_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_end_date }}
                                        </div>
                                        <div v-if="dateError" class="mt-1 text-sm text-red-600">
                                            {{ dateError }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Types Section -->
                                <div class="mt-6 border-t border-gray-600 pt-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Room Types</h3>
                                        <button
                                            type="button"
                                            @click="addRoomType"
                                            class="px-4 py-2 text-xs bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200"
                                        >
                                            Add Room Type
                                        </button>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Occupancy</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="(roomType, index) in form.room_types" :key="index">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="text"
                                                            v-model="roomType.name"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            placeholder="Room Type Name"
                                                            required
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="number"
                                                            v-model="roomType.max_occupancy"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            min="1"
                                                            required
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <input
                                                            type="text"
                                                            v-model="roomType.description"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            placeholder="Description"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button
                                                            type="button"
                                                            @click="removeRoomType(index)"
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
                                    Create Package
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

const form = useForm({
    name: '',
    description: '',
    images: [],
    display_price_adult: null,
    display_price_child: null,
    adult_surcharge: null,
    child_surcharge: null,
    package_days: 2,
    terms_and_conditions: '',
    location: '',
    package_start_date: '',
    package_end_date: '',
    room_types: [{
        name: '',
        max_occupancy: 2,
        description: '',
    }]
});

const dateError = ref('');
const imagePreviews = ref([]);

const addRoomType = () => {
    form.room_types.push({
        name: '',
        max_occupancy: 2,
        description: '',
    });
};

const removeRoomType = (index) => {
    form.room_types.splice(index, 1);
};

const handleImagesUpload = (event) => {
    const files = Array.from(event.target.files);
    form.images = [...form.images, ...files];
    files.forEach(file => {
        imagePreviews.value.push(URL.createObjectURL(file));
    });
};

const removeImage = (index) => {
    form.images.splice(index, 1);
    URL.revokeObjectURL(imagePreviews.value[index]);
    imagePreviews.value.splice(index, 1);
};

// Clean up preview URLs when component is unmounted
onUnmounted(() => {
    imagePreviews.value.forEach(url => URL.revokeObjectURL(url));
});

const validateDates = () => {
    dateError.value = '';
    if (form.package_start_date && form.package_end_date) {
        const startDate = new Date(form.package_start_date);
        const endDate = new Date(form.package_end_date);
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (endDate <= startDate) {
            dateError.value = 'End date must be after start date';
        } else if (diffDays < 1) {
            dateError.value = 'End date must be at least one day after start date';
        }
    }
};

const submit = () => {
    // Validate dates before submission
    validateDates();
    if (dateError.value) {
        Swal.fire({
            title: 'Validation Error',
            text: dateError.value,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    form.post(route('packages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Package has been created successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                form.reset();
                dateError.value = '';
            });
        },
        onError: (errors) => {
            if (errors.package_end_date) {
                Swal.fire({
                    title: 'Validation Error',
                    text: errors.package_end_date,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
};

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
	{ title: 'Create Package', },
]);
</script>
