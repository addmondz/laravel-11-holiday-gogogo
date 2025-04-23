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
                        <!-- Selection Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            <div>
                                <label for="package" class="block text-sm font-medium text-gray-700">Package</label>
                                <select
                                    id="package"
                                    v-model="form.package_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.package_id }"
                                >
                                    <option value="">Select Package</option>
                                    <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">
                                        {{ pkg.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.package_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.package_id }}
                                </p>
                            </div>

                            <div>
                                <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
                                <select
                                    id="season"
                                    v-model="form.season_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.season_id }"
                                >
                                    <option value="">Select Season</option>
                                    <option v-for="season in seasons" :key="season.id" :value="season.id">
                                        {{ season.type.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.season_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.season_id }}
                                </p>
                            </div>

                            <div>
                                <label for="dateType" class="block text-sm font-medium text-gray-700">Date Type</label>
                                <select
                                    id="dateType"
                                    v-model="form.date_type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.date_type_id }"
                                >
                                    <option value="">Select Date Type</option>
                                    <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.date_type_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.date_type_id }}
                                </p>
                            </div>

                            <div>
                                <label for="roomType" class="block text-sm font-medium text-gray-700">Room Type</label>
                                <select
                                    id="roomType"
                                    v-model="form.room_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.room_type }"
                                >
                                    <option value="">Select Room Type</option>
                                    <option v-for="type in roomTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                                <p v-if="form.errors.room_type" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.room_type }}
                                </p>
                            </div>
                        </div>

                        <!-- Price Type Selection -->
                        <div class="mb-8">
                            <label for="priceType" class="block text-sm font-medium text-gray-700">Price Type</label>
                            <select
                                id="priceType"
                                v-model="form.type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.type }"
                            >
                                <option value="">Select Price Type</option>
                                <option value="base_charge">Base Charge</option>
                                <option value="sur_charge">Surcharge</option>
                                <option value="ext_charge">Extra Charge</option>
                            </select>
                            <p v-if="form.errors.type" class="mt-2 text-sm text-red-600">
                                {{ form.errors.type }}
                            </p>
                        </div>

                        <!-- Price Matrix Table -->
                        <div class="overflow-x-auto mb-8">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                        <th v-for="children in 6" :key="children-1" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ children-1 }} Children
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="adults in 6" :key="adults">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ adults }} Adults
                                        </td>
                                        <td v-for="children in 6" :key="children-1" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex flex-col space-y-2">
                                                <input
                                                    type="number"
                                                    v-model="form.prices[`${adults}-${children-1}`].adult_price"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Adult Price"
                                                    step="0.01"
                                                    min="0"
                                                />
                                                <input
                                                    type="number"
                                                    v-model="form.prices[`${adults}-${children-1}`].child_price"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Child Price"
                                                    step="0.01"
                                                    min="0"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <Link
                                :href="route('configuration-prices.index')"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                            >
                                Cancel
                            </Link>
                            <button
                                @click="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                :disabled="form.processing"
                            >
                                Update Prices
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    price: Object,
    packages: Array,
    seasons: Array,
    dateTypes: Array,
    roomTypes: Array
});

const form = useForm({
    package_id: props.price.package_id,
    season_id: props.price.season_id,
    date_type_id: props.price.date_type_id,
    room_type: props.price.room_type,
    type: props.price.type,
    prices: {}
});

// Initialize prices object with empty values for all combinations
for (let adults = 1; adults <= 6; adults++) {
    for (let children = 0; children <= 5; children++) {
        form.prices[`${adults}-${children}`] = {
            adult_price: '',
            child_price: ''
        };
    }
}

// Populate prices with existing values
onMounted(() => {
    props.price.prices.forEach(price => {
        const key = `${price.number_of_adults}-${price.number_of_children}`;
        form.prices[key] = {
            adult_price: price.adult_price,
            child_price: price.child_price
        };
    });
});

const submit = () => {
    // Transform prices object into array format
    const pricesArray = Object.entries(form.prices).map(([key, value]) => {
        const [adults, children] = key.split('-');
        return {
            number_of_adults: parseInt(adults),
            number_of_children: parseInt(children),
            adult_price: value.adult_price,
            child_price: value.child_price
        };
    });

    form.put(route('configuration-prices.update', props.price.id), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.href = route('configuration-prices.index');
        }
    });
};
</script>
