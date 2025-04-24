<template>
    <Head title="Edit Date Type Range" />
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
                                    <label for="date_type_id" class="block text-sm font-medium text-gray-700">Date Type</label>
                                    <select
                                        id="date_type_id"
                                        v-model="form.date_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Date Type</option>
                                        <option v-for="dateType in dateTypes" :key="dateType.id" :value="dateType.id">
                                            {{ dateType.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.date_type_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.date_type_id }}
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

                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('date-type-ranges.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    :disabled="form.processing"
                                >
                                    Update Date Range
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
    dateTypeRange: Object,
    dateTypes: Array
});

const breadcrumbs = computed(() => [
    { title: 'Date Type Ranges', link: route('date-type-ranges.index') },
    { title: 'Edit Date Type Range' }
]);

const form = useForm({
    date_type_id: props.dateTypeRange.date_type_id,
    start_date: props.dateTypeRange.start_date,
    end_date: props.dateTypeRange.end_date,
});

const submit = () => {
    form.put(route('date-type-ranges.update', props.dateTypeRange.id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Date type range updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
    });
};
</script>
