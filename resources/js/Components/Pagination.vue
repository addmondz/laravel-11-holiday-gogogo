<template>
    <div v-if="links.length > 3" class="flex items-center justify-between">
        <transition name="fade">
            <div v-show="loading" class="absolute inset-0 bg-white/80 flex items-center justify-center transition-opacity duration-300 ease-in-out">
                <LoadingComponent :loading="loading" />
            </div>
        </transition>

        <!-- Record Count -->
        <div class="text-md text-gray-700 select-none">
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
            <button
                v-if="links[0].url"
                @click="handlePageChange(links[0].url)"
                :class="[
                    'px-4 py-2 fs-14 border rounded-md flex items-center space-x-1 select-none',
                    'hover:bg-white focus:border-indigo-500 focus:text-indigo-500'
                ]"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Previous</span>
            </button>
            <button
                v-else
                disabled
                class="px-4 py-2 fs-14 border rounded-md flex items-center space-x-1 bg-gray-100 text-gray-400 cursor-not-allowed select-none"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Previous</span>
            </button>

            <!-- Next Button -->
            <button
                v-if="links[links.length - 1].url"
                @click="handlePageChange(links[links.length - 1].url)"
                :class="[
                    'px-4 py-2 fs-14 border rounded-md flex items-center space-x-1 select-none',
                    'bg-indigo-600 text-white hover:bg-indigo-700'
                ]"
            >
                <span>Next</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <button
                v-else
                disabled
                class="px-4 py-2 fs-14 border rounded-md flex items-center space-x-1 bg-gray-100 text-gray-400 cursor-not-allowed select-none"
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
import { router } from '@inertiajs/vue3';
import LoadingComponent from './LoadingComponent.vue';
import { ref, onMounted } from 'vue';

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
    componentLoading: {
        type: Boolean,
        default: false,
    },
});

const loading = ref(false);
const fakeLoading = ref(true);
const emit = defineEmits(['page-change']);

const handlePageChange = (url) => {

    if (props.componentLoading) {
        loading.value = true;
        const page = typeof url === 'string' ? new URL(url).searchParams.get('page') : url;
        emit('page-change', parseInt(page));
    } else {
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
            // onFinish: () => {
            //     loading.value = false;
            // }
        });
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
