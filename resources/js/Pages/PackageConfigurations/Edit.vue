<template>
    <Head title="Edit Package Configuration" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"></h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="package_id">
                                    Package
                                </label>
                                <select
                                    id="package_id"
                                    v-model="form.package_id"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    :class="{ 'border-red-500': form.errors.package_id }"
                                >
                                    <option value="">Select a package</option>
                                    <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                        {{ pkg.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.package_id" class="text-red-500 text-xs italic">
                                    {{ form.errors.package_id }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="season_id">
                                    Season
                                </label>
                                <select
                                    id="season_id"
                                    v-model="form.season_id"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    :class="{ 'border-red-500': form.errors.season_id }"
                                >
                                    <option value="">Select a season</option>
                                    <option v-for="season in seasons" :key="season.id" :value="season.id">
                                        {{ season.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.season_id" class="text-red-500 text-xs italic">
                                    {{ form.errors.season_id }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="date_type_id">
                                    Date Type
                                </label>
                                <select
                                    id="date_type_id"
                                    v-model="form.date_type_id"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    :class="{ 'border-red-500': form.errors.date_type_id }"
                                >
                                    <option value="">Select a date type</option>
                                    <option v-for="dateType in dateTypes" :key="dateType.id" :value="dateType.id">
                                        {{ dateType.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.date_type_id" class="text-red-500 text-xs italic">
                                    {{ form.errors.date_type_id }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="room_type">
                                    Room Type
                                </label>
                                <input
                                    id="room_type"
                                    v-model="form.room_type"
                                    type="text"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    :class="{ 'border-red-500': form.errors.room_type }"
                                />
                                <p v-if="form.errors.room_type" class="text-red-500 text-xs italic">
                                    {{ form.errors.room_type }}
                                </p>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('package-configurations.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3 text-xs"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    :disabled="form.processing"
                                >
                                    Update Package Configuration
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    configuration: {
        type: Object,
        required: true,
    },
    packages: {
        type: Array,
        required: true,
    },
    seasons: {
        type: Array,
        required: true,
    },
    dateTypes: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    package_id: props.configuration.package_id,
    season_id: props.configuration.season_id,
    date_type_id: props.configuration.date_type_id,
    room_type: props.configuration.room_type,
});

const breadcrumbs = computed(() => [
    { title: 'Package Configurations', link: route('package-configurations.index') },
    { title: 'Edit Package Configuration' }
]);

const submit = () => {
    form.put(route('package-configurations.update', props.configuration.id), {
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Package configuration updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = route('package-configurations.index');
                }
            });
        },
    });
};
</script>
