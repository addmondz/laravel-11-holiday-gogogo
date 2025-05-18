<template>
    <Head title="Create Season" />
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
                                    <label for="season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                                    <select
                                        id="season_type_id"
                                        v-model="form.season_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select a season type</option>
                                        <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.season_type_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.season_type_id }}
                                    </div>
                                </div>

                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        type="date"
                                        id="start_date"
                                        v-model="form.start_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.start_date }}
                                    </div>
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        type="date"
                                        id="end_date"
                                        v-model="form.end_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.end_date }}
                                    </div>
                                </div>

                                <div>
                                    <label for="package_id" class="block text-sm font-medium text-gray-700">Package</label>
                                    <select
                                        id="package_id"
                                        v-model="form.package_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select a package</option>
                                        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                            {{ pkg.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.package_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.package_id }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('seasons.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3 text-xs"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    :disabled="form.processing"
                                >
                                    Create Season
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
import { useRouter } from 'vue-router';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';

const props = defineProps({
    seasonTypes: Array,
    packages: Array
});

const breadcrumbs = computed(() => [
    { title: 'Seasons', link: route('seasons.index') },
    { title: 'Create Season' }
]);

const form = useForm({
    season_type_id: '',
    start_date: '',
    end_date: '',
    package_id: ''
});

const router = useRouter();

const submit = () => {
    form.post(route('seasons.store'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Season created successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    router.visit(route('seasons.index'));
                }
            });
        }
    });
};
</script>
