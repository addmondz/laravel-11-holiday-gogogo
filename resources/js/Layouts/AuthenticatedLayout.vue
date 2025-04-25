<script setup lang="ts">
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { BgColorsOutlined, MenuOutlined, CloseOutlined } from '@ant-design/icons-vue';

const isDarkMode = ref(false);
const isSidebarOpen = ref(false);

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  document.documentElement.classList.toggle('dark', isDarkMode.value);
};

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Initialize based on user's preference
onMounted(() => {
  isDarkMode.value = document.documentElement.classList.contains('dark');
});

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-indigo-500/5 dark:bg-gray-900">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-4 h-16">
                <div class="flex items-center">
                    <button
                        @click="toggleSidebar"
                        class="p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300 lg:hidden"
                    >
                        <MenuOutlined class="h-6 w-6" />
                    </button>
                    <Link :href="route('dashboard')" class="ml-4">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" style="max-width: 100px;" />
                    </Link>
                </div>

                <div class="flex items-center">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                >
                                    {{ $page.props.auth.user.name }}
                                    <svg
                                        class="-me-0.5 ms-2 h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </header>

        <!-- Mobile sidebar backdrop -->
        <div
            v-if="isSidebarOpen"
            class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden"
            @click="toggleSidebar"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-16 left-0 z-30 w-64 h-[calc(100vh-4rem)] transform bg-white dark:bg-gray-800 transition-transform duration-200 ease-in-out lg:translate-x-0',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <nav class="mt-6 px-2">
                <div class="space-y-1 flex flex-col">
                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Dashboard
                    </NavLink>
                    <NavLink :href="route('packages.index')" :active="route().current('packages.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Packages
                    </NavLink>
                    <NavLink :href="route('season-types.index')" :active="route().current('season-types.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Season Types
                    </NavLink>
                    <NavLink :href="route('seasons.index')" :active="route().current('seasons.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Seasons
                    </NavLink>
                    <NavLink :href="route('date-types.index')" :active="route().current('date-types.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Date Types
                    </NavLink>
                    <NavLink :href="route('date-type-ranges.index')" :active="route().current('date-type-ranges.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Date Type Ranges
                    </NavLink>
                    <NavLink :href="route('package-configurations.index')" :active="route().current('package-configurations.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Package Configurations
                    </NavLink>
                    <NavLink :href="route('configuration-prices.index')" :active="route().current('configuration-prices.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md">
                        Configuration Prices
                    </NavLink>
                </div>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64 pt-16">

            <!-- Page Content -->
            <main class="pb-10">
                <div class="max-w-9xl">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
