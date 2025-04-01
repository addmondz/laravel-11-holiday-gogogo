<template>
    <Head title="Package" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Package
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="w-full p-4">
                        <!-- Tab Navigation -->
                        <nav class="flex gap-4 border-b mb-4">
                            <button
                                v-for="tab in tabs"
                                :key="tab"
                                :class="[
                                    'px-4 py-2',
                                    activeTab === tab
                                        ? 'border-b-2 border-blue-500 font-bold'
                                        : 'text-gray-500'
                                ]"
                                @click="activeTab = tab"
                            >
                                {{ tab }}
                            </button>
                        </nav>

                        <!-- Packages Tab -->
                        <div v-if="activeTab === 'Packages'">
                            <h2 class="text-xl font-semibold mb-4">Packages Module</h2>
                            <ul>
                                <li
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    class="p-4 border rounded mb-2 bg-gray-50 dark:bg-gray-700"
                                >
                                    <div class="font-bold text-lg">{{ pkg.name }}</div>
                                    <div class="text-sm mb-1">{{ pkg.description }}</div>
                                    <div class="text-gray-600 dark:text-gray-300">
                                        Adults: RM{{ pkg.display_price_adult }} |
                                        Children: RM{{ pkg.display_price_child }}
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Add-ons Tab -->
                        <div v-if="activeTab === 'Add-ons'">
                            <h2 class="text-xl font-semibold mb-4">Add-ons Module</h2>
                            <ul>
                                <li
                                    v-for="addon in addons"
                                    :key="addon.id"
                                    class="p-4 border rounded mb-2 bg-gray-50 dark:bg-gray-700"
                                >
                                    <div class="font-bold">{{ addon.name }}</div>
                                    <div class="text-sm mb-1">{{ addon.description }}</div>
                                    <div class="text-gray-600 dark:text-gray-300">
                                        Adult: RM{{ addon.adult_price }} |
                                        Child: RM{{ addon.child_price }}
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Seasons Tab -->
                        <div v-if="activeTab === 'Seasons'">
                            <h2 class="text-xl font-semibold mb-4">Seasons Module</h2>
                            <ul>
                                <li
                                    v-for="season in seasons"
                                    :key="season.id"
                                    class="p-4 border rounded mb-2 bg-gray-50 dark:bg-gray-700"
                                >
                                    <div class="font-bold">
                                        {{ season.type?.name || 'Unknown Type' }}
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-300">
                                        {{ season.start_date }} → {{ season.end_date }}
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

// Props from Inertia
const props = defineProps({
    packages: Array,
    addons: Array,
    seasons: Array
})

// Tabs logic
const tabs = ['Packages', 'Add-ons', 'Seasons']
const activeTab = ref('Packages') // Default

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const module = urlParams.get('module')
    if (module && tabs.includes(module)) {
        activeTab.value = module
    }
})
</script>
