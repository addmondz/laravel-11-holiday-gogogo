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

                        <div class="mb-6">
                            <div class="sm:hidden">
                                <select
                                    v-model="currentTab"
                                    class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option v-for="tab in tabs" :key="tab.id" :value="tab.id">
                                        {{ tab.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="hidden sm:block">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        <button
                                            v-for="tab in tabs"
                                            :key="tab.id"
                                            @click="currentTab = tab.id"
                                            :class="[
                                                currentTab === tab.id
                                                    ? 'border-indigo-500 text-indigo-600'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                                'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
                                            ]"
                                        >
                                            {{ tab.name }}
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center min-h-96 flex items-center justify-center" v-if="isTabLoading">
                            <LoadingComponent />
                        </div>
                        <div v-else>
                            <div class="mt-6">
                                <component
                                    :is="currentTabComponent"
                                    :package="pkg"
                                    :room-types="pkg.load_room_types"
                                    :seasons="seasons"
                                    :dateTypeRanges="dateTypeRanges"
                                    :dateTypes="dateTypes"
                                    :packageId="pkg.id"
                                    :seasonTypes="seasonTypes"
                                    :packageUniqueRoomTypes="packageUniqueRoomTypes"
                                    :allSeasonTypes="allSeasonTypes"
                                    :allDateTypes="allDateTypes"
                                    :assignedSeasonTypes="assignedSeasonTypes"
                                    :assignedDateTypes="assignedDateTypes"
                                    :isGlobalSstEnable="isGlobalSstEnable"
                                    :globalSstPercent="globalSstPercent"
                                    :noChildrenAndInfant="pkg.no_children_and_infant"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PackageDetails from './packages-show-tabs/PackageDetails.vue';
import RoomTypes from './packages-show-tabs/RoomTypes.vue';
import SeasonTypes from './packages-show-tabs/SeasonTypes.vue';
import DateTypesRanges from './packages-show-tabs/DateTypesRanges.vue';
import PriceConfigurationRoomTypes from './packages-show-tabs/PriceConfigurationRoomTypes.vue';
import DateBlockers from './packages-show-tabs/DateBlockers.vue';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import LoadingComponent from '@/Components/LoadingComponent.vue';
import PackageAddOns from './packages-show-tabs/PackageAddOns.vue';

const isTabLoading = ref(false);
const props = defineProps({
    pkg: {
        type: Object,
        required: true
    },
    seasons: {
        type: Object,
        required: true
    },
    seasonTypes: {
        type: Object,
        required: true
    },
    dateTypeRanges: {
        type: Object,
        required: true
    },
    dateTypes: {
        type: Array,
        required: true
    },
    priceConfigSeasonChoice: {
        type: Object,
        required: true
    },
    priceConfigDateTypeChoice: {
        type: Object,
        required: true
    },
    packageUniqueRoomTypes: {
        type: Object,
        required: true
    },
    allSeasonTypes: {
        type: Object,
        required: true
    },
    allDateTypes: {
        type: Object,
        required: true
    },
    assignedSeasonTypes: {
        type: Object,
        required: true
    },
    assignedDateTypes: {
        type: Object,
        required: true
    },
    isGlobalSstEnable: {
        type: Boolean,
        required: true
    },
    globalSstPercent: {
        type: Number,
        required: true
    }
});

const tabs = [
    { id: 'details', name: 'Details' },
    { id: 'room-types', name: 'Room Types' },
    { id: 'season-types', name: 'Season Types' },
    { id: 'date-types-ranges', name: 'Date Types Ranges' },
    { id: 'price-configuration', name: 'Price Configuration' },
    { id: 'date-blockers', name: 'Date Blockers' },
    { id: 'package-add-ons', name: 'Package Add Ons' },
];

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
    { title: props.pkg.name }
]);

const currentTabComponent = computed(() => {
    switch (currentTab.value) {
        case 'details':
            return PackageDetails;
        case 'room-types':
            return RoomTypes;
        case 'season-types':
            return SeasonTypes;
        case 'date-types-ranges':
            return DateTypesRanges;
        case 'price-configuration':
            return PriceConfigurationRoomTypes;
        case 'date-blockers':
            return DateBlockers;
        case 'package-add-ons':
            return PackageAddOns;
        default:
            return PackageDetails;
    }
});

const getParamFromUrl = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('tab');
};

// const currentTab = ref('details');
const currentTab = ref(getParamFromUrl() || 'details');

watch(currentTab, (newVal, oldVal) => {
    // force refresh the page when switching to price-configuration
    if (newVal == 'price-configuration' && oldVal != 'price-configuration') {
        isTabLoading.value = true;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', 'price-configuration');
        window.location.href = url.toString();
    }
});
</script>
