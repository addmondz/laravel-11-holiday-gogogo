<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import LoadingComponent from '@/Components/LoadingComponent.vue';
import {
  MenuOutlined,
  AppstoreOutlined,
  CalendarOutlined,
  DollarOutlined,
  UserOutlined,
  TagOutlined,
  ScheduleOutlined,
  SettingOutlined
} from '@ant-design/icons-vue';

const isDarkMode = ref(false);
const isSidebarOpen = ref(true);
const showingNavigationDropdown = ref(false);

// New 👇
const isLoading = ref(false);
const isFakeLoading = ref(true);

// Initialize dark mode based on user's preference
onMounted(() => {
  isDarkMode.value = document.documentElement.classList.contains('dark');

  // Restore sidebar state from localStorage
  const savedSidebarState = localStorage.getItem('sidebarOpen');
  if (savedSidebarState !== null) {
    isSidebarOpen.value = savedSidebarState === 'true';
  }

  // Inertia global loading listeners
  router.on('start', () => {
    isLoading.value = true;
  });

  router.on('finish', () => {
    // Add a small delay to ensure smooth transition
    setTimeout(() => {
      isLoading.value = false;
    }, 300);
  });

  setTimeout(() => {
    isFakeLoading.value = false;
    }, 100);
});

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  document.documentElement.classList.toggle('dark', isDarkMode.value);
};

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
  localStorage.setItem('sidebarOpen', String(isSidebarOpen.value));
};
</script>

<template>
    <div class="min-h-screen bg-indigo-500/5 dark:bg-gray-900">
        <!-- Loading Overlay -->
        <transition name="fade">
            <div v-show="isLoading" class="fixed inset-0 z-50 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center">
                <LoadingComponent />
            </div>
        </transition>
        <transition name="fade">
            <div v-show="isFakeLoading" class="fixed inset-0 z-50 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center">
            </div>
        </transition>

        <!-- Main Content -->
        <transition name="fade" mode="out-in">
            <div :key="$page.url">
                <!-- Header -->
                <header class="fixed top-0 left-0 right-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between px-4 h-16">
                        <div class="flex items-center">
                            <button @click="toggleSidebar" class="p-2 rounded-md text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                                <MenuOutlined class="h-6 w-6" />
                            </button>
                            <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" style="max-width: 100px;" />
                            <!-- <Link :href="route('dashboard')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" style="max-width: 100px;" />
                            </Link> -->
                        </div>

                        <div class="flex items-center">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                                            {{ $page.props.auth.user.name }}
                                            <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <!-- Mobile sidebar backdrop -->
                <div v-if="isSidebarOpen" class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden" @click="toggleSidebar"></div>

                <!-- Sidebar -->
                <aside :class="[
                    'fixed top-16 left-0 z-50 h-[calc(100vh-4rem)] bg-white dark:bg-gray-800 transition-all duration-200 ease-in-out border-r border-gray-200 dark:border-gray-700 overflow-hidden',
                    isSidebarOpen ? 'w-64' : 'w-16',
                    isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]">
                    <nav class="h-full flex flex-col">
                        <div :class="['py-4 border-t border-gray-200 dark:border-gray-700', isSidebarOpen ? 'px-4' : 'px-2']">
                            <h2 v-show="isSidebarOpen" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menus</h2>
                            <div :class="['space-y-1 flex flex-col', isSidebarOpen ? 'mt-3' : 'mt-0']">
                                <NavLink :href="route('packages.index')" :active="route().current('packages.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <AppstoreOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Packages</span>
                                </NavLink>
                                <NavLink :href="route('bookings.index')" :active="route().current('bookings.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <CalendarOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Bookings</span>
                                </NavLink>
                                <NavLink :href="route('payments.index')" :active="route().current('payments.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <DollarOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Payments</span>
                                </NavLink>
                                <NavLink :href="route('users.index')" :active="route().current('users.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <UserOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Users</span>
                                </NavLink>
                            </div>
                        </div>

                        <div :class="['py-4 border-t border-gray-200 dark:border-gray-700', isSidebarOpen ? 'px-4' : 'px-2']">
                            <h2 v-show="isSidebarOpen" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Configurations</h2>
                            <div :class="['space-y-1 flex flex-col', isSidebarOpen ? 'mt-3' : 'mt-0']">
                                <NavLink :href="route('season-types.index')" :active="route().current('season-types.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <TagOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Season Types</span>
                                </NavLink>
                                <NavLink :href="route('date-types.index')" :active="route().current('date-types.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <ScheduleOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Date Types</span>
                                </NavLink>
                                <NavLink :href="route('settings.index')" :active="route().current('settings.*')" class="flex items-center px-2 py-2 text-sm font-medium rounded-md" :class="isSidebarOpen ? '' : 'justify-center'">
                                    <SettingOutlined class="flex-shrink-0 w-5 h-5" />
                                    <span v-show="isSidebarOpen" class="ml-3 whitespace-nowrap">Settings</span>
                                </NavLink>
                            </div>
                        </div>
                    </nav>
                </aside>

                <!-- Main content -->
                <div :class="['pt-16 transition-[padding] duration-200', isSidebarOpen ? 'lg:pl-64' : 'lg:pl-16']">
                    <main class="pb-10">
                        <div class="max-w-9xl">
                            <slot />
                        </div>
                    </main>
                </div>
            </div>
        </transition>
    </div>
</template>

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
