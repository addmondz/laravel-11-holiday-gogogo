<template>
    <AuthenticatedLayout title="Create Package">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Package
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
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

                                <div class="hidden">
                                    <label for="icon_photo" class="block text-sm font-medium text-gray-700">Icon Photo</label>
                                    <input
                                        type="file"
                                        id="icon_photo"
                                        @change="handleFileUpload"
                                        accept="image/*"
                                        class="mt-1 block w-full"
                                    />
                                    <div v-if="form.errors.icon_photo" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.icon_photo }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label for="display_price_adult" class="block text-sm font-medium text-gray-700">Display Price (Adult)</label>
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
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label for="package_min_days" class="block text-sm font-medium text-gray-700">Minimum Days</label>
                                        <input
                                            type="number"
                                            id="package_min_days"
                                            v-model="form.package_min_days"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                        <div v-if="form.errors.package_min_days" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_min_days }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="package_max_days" class="block text-sm font-medium text-gray-700">Maximum Days</label>
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

                                <div>
                                    <label for="is_active" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            id="is_active"
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('packages.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
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

const form = useForm({
    name: '',
    description: '',
    icon_photo: null,
    display_price_adult: null,
    display_price_child: null,
    package_min_days: 1,
    package_max_days: 1,
    terms_and_conditions: '',
    location: '',
    package_start_date: '',
    package_end_date: '',
    is_active: true
});

const handleFileUpload = (event) => {
    form.icon_photo = event.target.files[0];
};

const submit = () => {
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
            });
        }
    });
};
</script>
