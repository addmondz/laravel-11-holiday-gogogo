<template>
    <div>
        <!-- INVALID STATE -->
        <div
            v-if="!isValid"
            class="p-4 text-center text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg"
        >
            <p>Invalid props passed to RoomPaxCombinationComponent.</p>
            <p class="mt-1 text-xs text-gray-600">
                Please provide <strong>maxPax</strong> or resolve it via <strong>roomTypeId</strong>
            </p>
        </div>

        <!-- VALID STATE -->
        <div v-else>
            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <div
                        @click="switchTab('tab1')"
                        :class="tabClass('tab1')"
                    >
                        {{ tab1Label || 'Tab 1' }}
                    </div>

                    <div
                        @click="switchTab('tab2')"
                        :class="tabClass('tab2')"
                    >
                        Disabled
                        <span v-if="selectedCombinations.length">
                            ( {{ selectedCombinations.length }} / {{ paxCombinations.length }} )
                        </span>
                        Combinations
                    </div>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="max-h-[60vh] overflow-y-auto">
                <!-- TAB 1 -->
                <div v-if="activeTab === 'tab1'" class="p-4">
                    <slot name="tab1" />
                </div>

                <!-- TAB 2 -->
                <div v-if="activeTab === 'tab2'" class="p-4 space-y-2">
                    <div
                        v-for="(combination, index) in paxCombinations"
                        :key="`${combination}-${index}`"
                        class="flex items-center justify-between p-3 rounded-lg border transition-colors"
                        :class="rowClass(combination)"
                    >
                        <span class="text-sm text-gray-900">
                            {{ formatPaxCombination(combination) }}
                        </span>

                        <button
                            type="button"
                            @click="toggleCombination(combination)"
                            :class="buttonClass(combination)"
                        >
                            <span v-if="isSelected(combination)">Undo</span>
                            <span v-else>
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </span>
                        </button>
                    </div>

                    <div
                        v-if="!paxCombinations.length"
                        class="p-4 text-center text-sm text-gray-500 bg-gray-50 rounded-lg"
                    >
                        No PAX combinations available
                    </div>

                    <!-- Summary -->
                    <div
                        v-if="selectedCombinations.length"
                        class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"
                    >
                        <p class="text-sm text-yellow-800">
                            <strong>{{ selectedCombinations.length }}</strong>
                            combination(s) disabled
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { generatePaxCombinations, formatPaxCombination } from '@/helpers/pax';

/**
 * PROPS
 */
const props = defineProps<{
    maxPax?: number | null;
    disabledPaxCombinations?: string[];
    maxAdults?: number | null;
    maxChildren?: number | null;
    maxInfants?: number | null;
    roomTypeId?: number | null;
    tab1Label?: string;
}>();

/**
 * EMITS
 */
const emit = defineEmits<{
    (e: 'update:selectedCombinations', value: string[]): void;
}>();

/**
 * STATE
 */
const activeTab = ref<'tab1' | 'tab2'>('tab1');
const selectedCombinations = ref<string[]>([]);

/**
 * VALIDATION
 */
const isValid = computed(() => {
    return props.maxPax !== null && props.maxPax !== undefined;
});

/**
 * PAX COMBINATIONS
 */
const paxCombinations = computed(() => {
    if (!isValid.value) return [];

    return generatePaxCombinations(
        props.maxPax!,
        props.maxAdults ?? null,
        props.maxChildren ?? null,
        props.maxInfants ?? null
    );
});

/**
 * UI HELPERS
 */
const isSelected = (combo: string) =>
    selectedCombinations.value.includes(combo);

const tabClass = (tab: 'tab1' | 'tab2') => [
    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium cursor-pointer transition-colors',
    activeTab.value === tab
        ? 'border-indigo-500 text-indigo-600'
        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
];

const rowClass = (combo: string) =>
    isSelected(combo)
        ? 'bg-red-100 border-red-300'
        : 'bg-white border-gray-200 hover:bg-gray-50';

const buttonClass = (combo: string) =>
    isSelected(combo)
        ? 'px-3 py-1.5 text-xs rounded-md bg-red-600 text-white hover:bg-red-700'
        : 'px-3 py-1.5 text-xs rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200';

/**
 * ACTIONS
 */
const toggleCombination = (combo: string) => {
    if (isSelected(combo)) {
        selectedCombinations.value =
            selectedCombinations.value.filter(c => c !== combo);
    } else {
        selectedCombinations.value.push(combo);
    }

    emit('update:selectedCombinations', [...selectedCombinations.value]);
};

const switchTab = (tab: 'tab1' | 'tab2') => {
    activeTab.value = tab;
};

/**
 * 🔥 CRITICAL FIX
 * Sync disabled combos → selected combos on LOAD & CHANGE
 */
watch(
    [() => props.disabledPaxCombinations, paxCombinations],
    ([disabled = [], available]) => {
        if (!Array.isArray(disabled)) {
            selectedCombinations.value = [];
            return;
        }

        // 🔒 Preserve UI order (paxCombinations)
        selectedCombinations.value = available.filter(combo =>
            disabled.includes(combo)
        );
    },
    { immediate: true }
);

/**
 * EXPOSE API
 */
defineExpose({
    selectedCombinations,
    clearSelection: () => {
        selectedCombinations.value = [];
        emit('update:selectedCombinations', []);
    }
});
</script>
