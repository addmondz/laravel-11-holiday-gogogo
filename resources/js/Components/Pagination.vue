<template>
    <div v-if="links.length > 3" class="flex items-center justify-between">
        <!-- Record Count -->
        <div class="text-md text-gray-700">
            Showing
            <span class="font-medium">{{ from }}</span>
            to
            <span class="font-medium">{{ to }}</span>
            of
            <span class="font-medium">{{ total }}</span>
            records
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center space-x-2">
            <!-- Previous Button -->
            <Link
                v-if="links[0].url"
                :href="links[0].url"
                :class="[
                    'px-4 py-2 fs-14 border rounded-md flex items-center space-x-1',
                    'hover:bg-white focus:border-indigo-500 focus:text-indigo-500'
                ]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Previous</span>
            </Link>
            <button
                v-else
                disabled
                class="px-4 py-2 fs-14 border rounded-md flex items-center space-x-1 bg-gray-100 text-gray-400 cursor-not-allowed"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Previous</span>
            </button>

            <!-- Next Button -->
            <Link
                v-if="links[links.length - 1].url"
                :href="links[links.length - 1].url"
                :class="[
                    'px-4 py-2 fs-14 border rounded-md flex items-center space-x-1',
                    'bg-indigo-600 text-white hover:bg-indigo-700'
                ]"
            >
                <span>Next</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </Link>
            <button
                v-else
                disabled
                class="px-4 py-2 fs-14 border rounded-md flex items-center space-x-1 bg-gray-100 text-gray-400 cursor-not-allowed"
            >
                <span>Next</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
    from: {
        type: Number,
        required: true,
    },
    to: {
        type: Number,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
});
</script>
