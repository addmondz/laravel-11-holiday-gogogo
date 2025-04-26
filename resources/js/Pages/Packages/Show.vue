<template>
    <Head title="Package Details" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Package Details
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-6" />

                        <!-- Tabs -->
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        activeTab === tab.id
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                    ]"
                                >
                                    {{ tab.name }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="mt-6">
                            <!-- Package Details Tab -->
                            <div v-if="activeTab === 'details'" class="space-y-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="flex justify-between items-center">
                                        <h3 class="text-md font-medium text-gray-900">Package Details</h3>
                                        <Link
                                            :href="route('packages.edit', pkg.id)"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                        >
                                            Edit Package
                                        </Link>
                                    </div>
                                    <div class="flex items-start">
                                        <!-- <div v-if="pkg.icon_photo" class="flex-shrink-0">
                                            <img :src="`/storage/${pkg.icon_photo}`" :alt="pkg.name" class="h-32 w-32 rounded-full">
                                        </div> -->
                                    </div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ pkg.name }}</p>
                                        </div>

                                        <div>
                                            <h4 class="mt-4 text-sm font-medium text-gray-500">Location</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ pkg.location }}</p>
                                        </div>

                                        <div>
                                            <h4 class="mt-4 text-sm font-medium text-gray-500">Status</h4>
                                            <p class="mt-1">
                                                <span :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    pkg.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800'
                                                ]">
                                                    {{ pkg.is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </p>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ pkg.description }}</p>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Display Prices</h4>
                                            <div class="mt-1">
                                                <p class="text-sm text-gray-900">MYR {{ formatNumber(pkg.display_price_adult) }}</p>
                                                <!-- <p class="text-sm text-gray-900">Adult: ${{ pkg.display_price_adult }}</p> -->
                                                <!-- <p class="text-sm text-gray-900">Child: ${{ pkg.display_price_child }}</p> -->
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Duration</h4>
                                            <div class="mt-1">
                                                <p class="text-sm text-gray-900">Minimum Days: {{ pkg.package_min_days }}</p>
                                                <p class="text-sm text-gray-900">Maximum Days: {{ pkg.package_max_days }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Terms and Conditions</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ pkg.terms_and_conditions }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">Start Date</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.package_start_date).format('DD/MM/YYYY') }}</p>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-500">End Date</h4>
                                            <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.package_end_date).format('DD/MM/YYYY') || 'No end date' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Created At</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.created_at).format('DD/MM/YYYY HH:mm:ss') }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500">Updated At</h4>
                                        <p class="mt-1 text-sm text-gray-900">{{ moment(pkg.updated_at).format('DD/MM/YYYY HH:mm:ss') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Types Tab -->
                            <div v-if="activeTab === 'room-types'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Room Types</h3>
                                    <Link
                                        :href="route('room-types.create')"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Room Type
                                    </Link>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Occupancy</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="roomType in pkg.load_room_types" :key="roomType.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_occupancy }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">{{ roomType.description }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        :class="[
                                                            roomType.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                                        ]"
                                                    >
                                                        {{ roomType.is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <Link
                                                        :href="route('room-types.edit', roomType.id)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Season Types Tab -->
                            <div v-if="activeTab === 'season-types'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Season Types</h3>
                                    <Link
                                        :href="route('season-types.create')"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Season Type
                                    </Link>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seasons</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="seasonType in pkg.season_types" :key="seasonType.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ seasonType.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ seasonType.seasons.length }} seasons
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <Link
                                                        :href="route('season-types.edit', seasonType.id)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Date Types Tab -->
                            <div v-if="activeTab === 'date-types'" class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Date Types</h3>
                                    <Link
                                        :href="route('date-types.create')"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Date Type
                                    </Link>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Ranges</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="dateType in pkg.date_types" :key="dateType.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ dateType.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ dateType.ranges.length }} ranges
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <Link
                                                        :href="route('date-types.edit', dateType.id)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { ref, computed } from 'vue';
import moment from 'moment';

const props = defineProps({
    pkg: Object,
    distinctRoomTypes: Array
});

const activeTab = ref('details');

const tabs = [
    { id: 'details', name: 'Package Details' },
    { id: 'room-types', name: 'Room Types' },
    { id: 'season-types', name: 'Season Types' },
    { id: 'date-types', name: 'Date Types' }
];

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
    { title: props.pkg.name }
]);

function formatNumber(number) {
  if (number == null || number === '') return '0.00'
  return Number(number).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>
