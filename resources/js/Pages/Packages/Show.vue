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
                            <div v-if="activeTab === 'room-types'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Room Types</h3>
                                    <button
                                        @click="showAddRoomTypeModal = true"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Room Type
                                    </button>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Occupancy</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="roomType in roomTypesData.data" :key="roomType.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_occupancy }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">{{ roomType.description }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        @click="editRoomType(roomType)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="deleteRoomType(roomType.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="roomTypesData.data.length === 0">
                                                <td colspan="4" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No room types found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4">
                                    <Pagination
                                        :links="roomTypesPagination.links"
                                        :from="roomTypesPagination.from"
                                        :to="roomTypesPagination.to"
                                        :total="roomTypesPagination.total"
                                        :component-loading="true"
                                        @page-change="handlePageChange"
                                        :key="loadingKey"
                                    />
                                </div>
                            </div>

                            <!-- Season Types Tab -->
                            <div v-if="activeTab === 'season-types'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Season Dates</h3>
                                    <button
                                        @click="showAddSeasonModal = true"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    >
                                        Add Season
                                    </button>
                                </div>

                                <!-- Seasons Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Season Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="season in seasonsData.data" :key="season.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ season.type.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(season.start_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(season.end_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ season.priority }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        @click="editSeason(season)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="deleteSeason(season.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="seasonsData.data.length === 0">
                                                <td colspan="5" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No seasons found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Seasons Pagination -->
                                <div class="mt-4">
                                    <Pagination
                                        :links="seasonsPagination.links"
                                        :from="seasonsPagination.from"
                                        :to="seasonsPagination.to"
                                        :total="seasonsPagination.total"
                                        :component-loading="true"
                                        @page-change="handleSeasonPageChange"
                                        :key="loadingKey"
                                    />
                                </div>
                            </div>

                            <!-- Date Types Tab -->
                            <div v-if="activeTab === 'date-types-ranges'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Date Type Ranges</h3>
                                    <div class="space-x-3">
                                        <button
                                            @click="showAddDateTypeRangeModal = true"
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                        >
                                            Add Date Range
                                        </button>
                                    </div>
                                </div>

                                <!-- Date Type Ranges Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="range in dateTypeRangesData.data" :key="range.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ range.date_type.name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(range.start_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(range.end_date).format('DD/MM/YYYY') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        @click="editDateTypeRange(range)"
                                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="deleteDateTypeRange(range.id)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="dateTypeRangesData.data.length === 0">
                                                <td colspan="4" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No date type ranges found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Date Type Ranges Pagination -->
                                <div class="mt-4">
                                    <Pagination
                                        :links="dateTypeRangesPagination.links"
                                        :from="dateTypeRangesPagination.from"
                                        :to="dateTypeRangesPagination.to"
                                        :total="dateTypeRangesPagination.total"
                                        :component-loading="true"
                                        @page-change="handleDateTypeRangePageChange"
                                        :key="loadingKey"
                                    />
                                </div>
                            </div>

                            <!-- Price Configuration Tab -->
                            <div v-if="activeTab === 'price-configuration'" class="space-y-6" style="position: relative;">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-md font-medium text-gray-900">Price Configuration</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Room Type Modal -->
        <Modal :show="showAddRoomTypeModal" @close="showAddRoomTypeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Room Type</h2>
                <form @submit.prevent="submitRoomType">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="name"
                                v-model="roomTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="max_occupancy" class="block text-sm font-medium text-gray-700">Max Occupancy</label>
                            <input
                                type="number"
                                id="max_occupancy"
                                v-model="roomTypeForm.max_occupancy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="description"
                                v-model="roomTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showAddRoomTypeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="roomTypeForm.processing"
                        >
                            Create Room Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Room Type Modal -->
        <Modal :show="showEditRoomTypeModal" @close="showEditRoomTypeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Room Type</h2>
                <form @submit.prevent="updateRoomType">
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="edit_name"
                                v-model="editRoomTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="edit_max_occupancy" class="block text-sm font-medium text-gray-700">Max Occupancy</label>
                            <input
                                type="number"
                                id="edit_max_occupancy"
                                v-model="editRoomTypeForm.max_occupancy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                        </div>

                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="edit_description"
                                v-model="editRoomTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEditRoomTypeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="editRoomTypeForm.processing"
                        >
                            Update Room Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Season Modal -->
        <Modal :show="showEditSeasonModal" @close="showEditSeasonModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Season</h2>
                <form @submit.prevent="updateSeason">
                    <div class="space-y-4">
                        <div>
                            <label for="edit_season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                            <select
                                id="edit_season_type_id"
                                v-model="editSeasonForm.season_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select a season type</option>
                                <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="editSeasonForm.errors.season_type_id" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.season_type_id }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="edit_start_date"
                                v-model="editSeasonForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="editSeasonForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="edit_end_date"
                                v-model="editSeasonForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="editSeasonForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.end_date }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <input
                                type="number"
                                id="edit_priority"
                                v-model="editSeasonForm.priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                            <div v-if="editSeasonForm.errors.priority" class="mt-1 text-sm text-red-600">
                                {{ editSeasonForm.errors.priority }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEditSeasonModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="editSeasonForm.processing"
                        >
                            Update Season
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Season Modal -->
        <Modal :show="showAddSeasonModal" @close="showAddSeasonModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Season</h2>
                <form @submit.prevent="submitSeason">
                    <div class="space-y-4">
                        <div>
                            <label for="season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                            <select
                                id="season_type_id"
                                v-model="seasonForm.season_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select a season type</option>
                                <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="seasonForm.errors.season_type_id" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.season_type_id }}
                            </div>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="seasonForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="seasonForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="seasonForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="seasonForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.end_date }}
                            </div>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <input
                                type="number"
                                id="priority"
                                v-model="seasonForm.priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                            <div v-if="seasonForm.errors.priority" class="mt-1 text-sm text-red-600">
                                {{ seasonForm.errors.priority }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showAddSeasonModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="seasonForm.processing"
                        >
                            Create Season
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Date Type Range Modal -->
        <Modal :show="showAddDateTypeRangeModal" @close="showAddDateTypeRangeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Date Range</h2>
                <form @submit.prevent="submitDateTypeRange">
                    <div class="space-y-4">
                        <div>
                            <label for="date_type_id" class="block text-sm font-medium text-gray-700">Date Type</label>
                            <select
                                id="date_type_id"
                                v-model="dateTypeRangeForm.date_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select a date type</option>
                                <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="dateTypeRangeForm.errors.date_type_id" class="mt-1 text-sm text-red-600">
                                {{ dateTypeRangeForm.errors.date_type_id }}
                            </div>
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="dateTypeRangeForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="dateTypeRangeForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ dateTypeRangeForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="dateTypeRangeForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="dateTypeRangeForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ dateTypeRangeForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showAddDateTypeRangeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="dateTypeRangeForm.processing"
                        >
                            Create Date Range
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Date Type Range Modal -->
        <Modal :show="showEditDateTypeRangeModal" @close="showEditDateTypeRangeModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Date Range</h2>
                <form @submit.prevent="updateDateTypeRange">
                    <div class="space-y-4">
                        <div>
                            <label for="edit_date_type_id" class="block text-sm font-medium text-gray-700">Date Type</label>
                            <select
                                id="edit_date_type_id"
                                v-model="editDateTypeRangeForm.date_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select a date type</option>
                                <option v-for="type in dateTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                            <div v-if="editDateTypeRangeForm.errors.date_type_id" class="mt-1 text-sm text-red-600">
                                {{ editDateTypeRangeForm.errors.date_type_id }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="edit_start_date"
                                v-model="editDateTypeRangeForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="editDateTypeRangeForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ editDateTypeRangeForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="edit_end_date"
                                v-model="editDateTypeRangeForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="editDateTypeRangeForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ editDateTypeRangeForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEditDateTypeRangeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            :disabled="editDateTypeRangeForm.processing"
                        >
                            Update Date Range
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { ref, computed } from 'vue';
import moment from 'moment';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    pkg: Object,
    distinctRoomTypes: Array,
    seasons: Object,
    seasonTypes: Array,
    dateTypeRanges: Object,
    dateTypes: Array
});

const activeTab = ref('details');
const loadingKey = ref(0);

const tabs = [
    { id: 'details', name: 'Package Details' },
    { id: 'room-types', name: 'Room Types' },
    { id: 'season-types', name: 'Season Dates' },
    { id: 'date-types-ranges', name: 'Date Types Ranges' },
    { id: 'price-configuration', name: 'Price Configuration' }
];

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
    { title: props.pkg.name }
]);

function formatNumber(number) {
  if (number == null || number === '') return '0.00'
  return Number(number).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const showAddRoomTypeModal = ref(false);
const roomTypeForm = useForm({
    name: '',
    description: '',
    max_occupancy: 2,
    package_id: props.pkg.id,
    return_to_package: true
});

const showEditRoomTypeModal = ref(false);
const editRoomTypeForm = useForm({
    id: null,
    name: '',
    description: '',
    max_occupancy: 2,
    package_id: props.pkg.id,
    return_to_package: true
});

const showEditSeasonModal = ref(false);
const editSeasonForm = useForm({
    id: null,
    season_type_id: '',
    start_date: '',
    end_date: '',
    priority: 1,
    return_to_package: true,
    package_id: props.pkg.id
});

const showAddSeasonModal = ref(false);
const seasonForm = useForm({
    season_type_id: '',
    start_date: '',
    end_date: '',
    priority: 1,
    return_to_package: true,
    package_id: props.pkg.id
});

const showAddDateTypeRangeModal = ref(false);
const dateTypeRangeForm = useForm({
    date_type_id: '',
    start_date: '',
    end_date: '',
    return_to_package: true,
    package_id: props.pkg.id
});

const showEditDateTypeRangeModal = ref(false);
const editDateTypeRangeForm = useForm({
    id: null,
    date_type_id: '',
    start_date: '',
    end_date: '',
    return_to_package: true,
    package_id: props.pkg.id
});

const roomTypesData = ref(props.pkg.load_room_types);
const seasonsData = ref(props.seasons);
const dateTypeRangesData = ref(props.dateTypeRanges);

const roomTypesPagination = computed(() => ({
    links: roomTypesData.value.links,
    from: roomTypesData.value.from,
    to: roomTypesData.value.to,
    total: roomTypesData.value.total
}));

const seasonsPagination = computed(() => ({
    links: seasonsData.value.links,
    from: seasonsData.value.from,
    to: seasonsData.value.to,
    total: seasonsData.value.total
}));

const dateTypeRangesPagination = computed(() => ({
    links: dateTypeRangesData.value.links,
    from: dateTypeRangesData.value.from,
    to: dateTypeRangesData.value.to,
    total: dateTypeRangesData.value.total
}));

const handlePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.room-types', props.pkg.id), {
            params: { page }
        });
        roomTypesData.value = {
            ...roomTypesData.value,
            data: response.data.data,
            links: response.data.links,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page
        };
        loadingKey.value++;
    } catch (error) {
        console.error('Error fetching room types:', error);
    }
};

const deleteSeason = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('seasons.destroy', id), {
                data: {
                    return_to_package: true,
                    package_id: props.pkg.id
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Season has been deleted.',
                        'success'
                    );
                    handleSeasonPageChange(1);
                }
            });
        }
    });
};

const handleSeasonPageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.seasons', props.pkg.id), {
            params: { page }
        });
        seasonsData.value = {
            ...seasonsData.value,
            data: response.data.data,
            links: response.data.links,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page
        };
        loadingKey.value++;
    } catch (error) {
        console.error('Error fetching seasons:', error);
    }
};

const editSeason = (season) => {
    editSeasonForm.id = season.id;
    editSeasonForm.season_type_id = season.season_type_id;
    editSeasonForm.start_date = season.start_date;
    editSeasonForm.end_date = season.end_date;
    editSeasonForm.priority = season.priority;
    showEditSeasonModal.value = true;
};

const updateSeason = () => {
    editSeasonForm.put(route('seasons.update', editSeasonForm.id), {
        onSuccess: () => {
            showEditSeasonModal.value = false;
            editSeasonForm.reset();
            editSeasonForm.return_to_package = true;
            editSeasonForm.package_id = props.pkg.id;
            handleSeasonPageChange(1);
        }
    });
};

const submitSeason = () => {
    seasonForm.post(route('seasons.store'), {
        onSuccess: () => {
            showAddSeasonModal.value = false;
            seasonForm.reset();
            seasonForm.return_to_package = true;
            seasonForm.package_id = props.pkg.id;
            handleSeasonPageChange(1);
        }
    });
};

const editDateTypeRange = (range) => {
    editDateTypeRangeForm.id = range.id;
    editDateTypeRangeForm.date_type_id = range.date_type_id;
    editDateTypeRangeForm.start_date = range.start_date;
    editDateTypeRangeForm.end_date = range.end_date;
    showEditDateTypeRangeModal.value = true;
};

const submitDateTypeRange = () => {
    dateTypeRangeForm.post(route('date-type-ranges.store'), {
        onSuccess: () => {
            showAddDateTypeRangeModal.value = false;
            dateTypeRangeForm.reset();
            dateTypeRangeForm.return_to_package = true;
            dateTypeRangeForm.package_id = props.pkg.id;
            handleDateTypeRangePageChange(1);
        }
    });
};

const updateDateTypeRange = () => {
    editDateTypeRangeForm.put(route('date-type-ranges.update', editDateTypeRangeForm.id), {
        onSuccess: () => {
            showEditDateTypeRangeModal.value = false;
            editDateTypeRangeForm.reset();
            editDateTypeRangeForm.return_to_package = true;
            editDateTypeRangeForm.package_id = props.pkg.id;
            handleDateTypeRangePageChange(1);
        }
    });
};

const deleteDateTypeRange = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('date-type-ranges.destroy', id), {
                data: {
                    return_to_package: true,
                    package_id: props.pkg.id
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Date range has been deleted.',
                        'success'
                    );
                    handleDateTypeRangePageChange(1);
                }
            });
        }
    });
};

const handleDateTypeRangePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.date-type-ranges', props.pkg.id), {
            params: { page }
        });
        dateTypeRangesData.value = {
            ...dateTypeRangesData.value,
            data: response.data.data,
            links: response.data.links,
            from: response.data.from,
            to: response.data.to,
            total: response.data.total,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page
        };
        loadingKey.value++;
    } catch (error) {
        console.error('Error fetching date type ranges:', error);
    }
};

const editRoomType = (roomType) => {
    editRoomTypeForm.id = roomType.id;
    editRoomTypeForm.name = roomType.name;
    editRoomTypeForm.description = roomType.description;
    editRoomTypeForm.max_occupancy = roomType.max_occupancy;
    editRoomTypeForm.package_id = roomType.package_id;
    showEditRoomTypeModal.value = true;
};

const submitRoomType = () => {
    roomTypeForm.post(route('room-types.store'), {
        onSuccess: () => {
            showAddRoomTypeModal.value = false;
            roomTypeForm.reset();
            roomTypeForm.return_to_package = true;
            handlePageChange(1);
        }
    });
};

const updateRoomType = () => {
    editRoomTypeForm.put(route('room-types.update', editRoomTypeForm.id), {
        onSuccess: () => {
            showEditRoomTypeModal.value = false;
            editRoomTypeForm.reset();
            editRoomTypeForm.return_to_package = true;
            handlePageChange(1);
        }
    });
};

const deleteRoomType = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('room-types.destroy', id), {
                data: { return_to_package: true },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Room type has been deleted.',
                        'success'
                    );
                    handlePageChange(1);
                }
            });
        }
    });
};
</script>
