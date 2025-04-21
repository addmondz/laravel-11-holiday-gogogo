<template>
    <Head title="Edit Configuration Price" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Configuration Price
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="package_configuration_id" class="block text-sm font-medium text-gray-700">
                                        Configuration
                                    </label>
                                    <select
                                        id="package_configuration_id"
                                        v-model="form.package_configuration_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.package_configuration_id }"
                                    >
                                        <option value="">Select a configuration</option>
                                        <option v-for="config in configurations" :key="config.id" :value="config.id">
                                            {{ config.room_type }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.package_configuration_id" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.package_configuration_id }}
                                    </p>
                                </div>

                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">
                                        Type
                                    </label>
                                    <input
                                        type="text"
                                        id="type"
                                        v-model="form.type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.type }"
                                    />
                                    <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.type }}
                                    </p>
                                </div>

                                <div>
                                    <label for="number_of_adults" class="block text-sm font-medium text-gray-700">
                                        Number of Adults
                                    </label>
                                    <input
                                        type="number"
                                        id="number_of_adults"
                                        v-model="form.number_of_adults"
                                        min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.number_of_adults }"
                                    />
                                    <p v-if="form.errors.number_of_adults" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.number_of_adults }}
                                    </p>
                                </div>

                                <div>
                                    <label for="number_of_children" class="block text-sm font-medium text-gray-700">
                                        Number of Children
                                    </label>
                                    <input
                                        type="number"
                                        id="number_of_children"
                                        v-model="form.number_of_children"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.number_of_children }"
                                    />
                                    <p v-if="form.errors.number_of_children" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.number_of_children }}
                                    </p>
                                </div>

                                <div>
                                    <label for="adult_price" class="block text-sm font-medium text-gray-700">
                                        Adult Price
                                    </label>
                                    <input
                                        type="number"
                                        id="adult_price"
                                        v-model="form.adult_price"
                                        min="0"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.adult_price }"
                                    />
                                    <p v-if="form.errors.adult_price" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.adult_price }}
                                    </p>
                                </div>

                                <div>
                                    <label for="child_price" class="block text-sm font-medium text-gray-700">
                                        Child Price
                                    </label>
                                    <input
                                        type="number"
                                        id="child_price"
                                        v-model="form.child_price"
                                        min="0"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.child_price }"
                                    />
                                    <p v-if="form.errors.child_price" class="mt-2 text-sm text-red-600">
                                        {{ form.errors.child_price }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <Link
                                    :href="route('configuration-prices.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    :disabled="form.processing"
                                >
                                    Update Price
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

const props = defineProps({
    price: Object,
    configurations: Array
});

const form = useForm({
    package_configuration_id: props.price.package_configuration_id,
    type: props.price.type,
    number_of_adults: props.price.number_of_adults,
    number_of_children: props.price.number_of_children,
    adult_price: props.price.adult_price,
    child_price: props.price.child_price
});

const submit = () => {
    form.put(route('configuration-prices.update', props.price.id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'Configuration price updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = route('configuration-prices.index');
                }
            });
        }
    });
};
</script>
