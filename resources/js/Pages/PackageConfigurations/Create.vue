<template>
    <Head title="Create Package Configuration" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                                <!-- Package Selection -->
                                <div>
                                    <label for="package_id" class="block text-sm font-medium text-gray-700">
                                        Package
                                    </label>
                                    <select
                                        id="package_id"
                                        v-model="form.package_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.package_id }"
                                    >
                                        <option value="">Select a package</option>
                                        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                            {{ pkg.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.package_id" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.package_id }}
                                    </p>
                                </div>

                                <!-- Season Selection -->
                                <div>
                                    <label for="season_id" class="block text-sm font-medium text-gray-700">
                                        Season
                                    </label>
                                    <select
                                        id="season_id"
                                        v-model="form.season_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.season_id }"
                                    >
                                        <option value="">Select a season</option>
                                        <option v-for="season in seasons" :key="season.id" :value="season.id">
                                            {{ season.type.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.season_id" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.season_id }}
                                    </p>
                                </div>

                                <!-- Date Type Selection -->
                                <div>
                                    <label for="date_type_id" class="block text-sm font-medium text-gray-700">
                                        Date Type
                                    </label>
                                    <select
                                        id="date_type_id"
                                        v-model="form.date_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.date_type_id }"
                                    >
                                        <option value="">Select a date type</option>
                                        <option v-for="dateType in dateTypes" :key="dateType.id" :value="dateType.id">
                                            {{ dateType.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.date_type_id" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.date_type_id }}
                                    </p>
                                </div>

                                <!-- Room Type -->
                                <div>
                                    <label for="room_type" class="block text-sm font-medium text-gray-700">
                                        Room Type
                                    </label>
                                    <input
                                        type="text"
                                        id="room_type"
                                        v-model="form.room_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.room_type }"
                                    />
                                    <p v-if="form.errors.room_type" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.room_type }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <Link
                                    :href="route('package-configurations.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    :disabled="form.processing"
                                >
                                    Create Configuration
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
import { computed } from 'vue';

const props = defineProps({
    packages: Array,
    seasons: Array,
    dateTypes: Array
});

const breadcrumbs = computed(() => [
    { title: 'Package Configurations', link: route('package-configurations.index') },
    { title: 'Create Package Configuration' }
]);

const form = useForm({
    package_id: '',
    season_id: '',
    date_type_id: '',
    room_type: ''
});

const submit = () => {
    form.post(route('package-configurations.store'), {
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Package configuration created successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = route('package-configurations.index');
                }
            });
        }
    });
};
</script>
