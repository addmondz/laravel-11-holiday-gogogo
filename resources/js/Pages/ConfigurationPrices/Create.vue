<template>
    <Head title="Create Configuration Prices" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Configuration Prices
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <!-- Selection Form -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                <div>
                                    <label for="package" class="block text-sm font-medium text-gray-700">Package</label>
                                    <select
                                        id="package"
                                        v-model="form.package_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Package</option>
                                        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                            {{ pkg.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                                    <select
                                        id="season"
                                        v-model="form.season_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Season</option>
                                        <option v-for="season in seasons" :key="season.id" :value="season.id">
                                            {{ season.type.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="dateType" class="block text-sm font-medium text-gray-700">Date Type</label>
                                    <select
                                        id="dateType"
                                        v-model="form.date_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Date Type</option>
                                        <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="roomType" class="block text-sm font-medium text-gray-700">Room Type</label>
                                    <select
                                        id="roomType"
                                        v-model="form.room_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select Room Type</option>
                                        <option v-for="type in roomTypes" :key="type" :value="type">
                                            {{ type }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Price Type Selection -->
                            <div class="mb-8">
                                <label for="type" class="block text-sm font-medium text-gray-700">Price Type</label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Price Type</option>
                                    <option value="base_charge">Base Charge</option>
                                    <option value="sur_charge">Surcharge</option>
                                    <option value="ext_charge">Extra Charge</option>
                                </select>
                            </div>

                            <!-- Price Matrix -->
                            <div class="space-y-8">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                                <th v-for="children in 4" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ children-1 }} Children
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="adults in 4" :key="adults">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ adults }} Adults
                                                </td>
                                                <td v-for="children in 4" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <input
                                                        type="number"
                                                        v-model="form.prices[getPriceIndex(adults, children-1)].adult_price"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        placeholder="Adult Price"
                                                    />
                                                    <input
                                                        type="number"
                                                        v-model="form.prices[getPriceIndex(adults, children-1)].child_price"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        placeholder="Child Price"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6 flex justify-end space-x-3">
                                <Link
                                    :href="route('packages.show', form.package_id)"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    :disabled="form.processing"
                                >
                                    Create Prices
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
import { ref, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    packages: Array,
    seasons: Array,
    dateTypes: Array,
    roomTypes: Array,
    prefilled: Object
});

const form = useForm({
    package_id: props.prefilled?.package_id || '',
    season_id: props.prefilled?.season_id || '',
    date_type_id: props.prefilled?.date_type_id || '',
    room_type: props.prefilled?.room_type || '',
    type: '',
    prices: Array(16).fill().map((_, index) => ({
        number_of_adults: Math.floor(index / 4) + 1,
        number_of_children: index % 4,
        adult_price: '',
        child_price: ''
    }))
});

const getPriceIndex = (adults, children) => {
    return (adults - 1) * 4 + children;
};

const submit = () => {
    form.post(route('configuration-prices.store'), {
        onSuccess: () => {
            window.location.href = route('packages.show', form.package_id);
        }
    });
};
</script>
