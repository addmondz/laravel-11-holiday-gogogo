<template>
    <div class="space-y-6" style="position: relative;">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Occupancy</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Adults</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Children</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Infants</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bed Desc</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="roomTypesData?.data?.length > 0" v-for="roomType in roomTypesData.data" :key="roomType.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div v-if="roomType.images && roomType.images.length > 0" class="flex space-x-2">
                                <div v-for="(image, index) in roomType.images" :key="index" class="relative group">
                                    <img 
                                        :src="getImageUrl(image)" 
                                        class="h-16 w-16 object-cover rounded-lg" 
                                        alt="Room type image"
                                    />
                                    <!-- Enlarge Icon Overlay -->
                                    <div 
                                        class="absolute inset-0 flex items-center justify-center bg-black/0 sm:group-hover:bg-black/20 transition-all duration-300 rounded-lg pointer-events-none"
                                    >
                                        <button 
                                            class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300 bg-white/90 rounded-full p-1.5 shadow-lg cursor-pointer pointer-events-auto"
                                            @click="showImageModal(image)"
                                            aria-label="Enlarge image"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <span v-else class="text-gray-400 text-sm">No images</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_occupancy }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_adults }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_children }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ roomType.max_infants }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ roomType.bed_desc }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ roomType.description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                @click="editRoomType(roomType)"
                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                            >
                                Edit
                            </button>
                            <button
                                @click="duplicateRoomType(roomType)"
                                class="text-blue-600 hover:text-blue-900 mr-3"
                            >
                                Duplicate
                            </button>
                            <button
                                @click="deleteRoomType(roomType.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="6" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No room types found</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="roomTypesData?.links?.length > 0" class="mt-4">
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

        <!-- Add Room Type Modal -->
        <Modal :show="showAddRoomTypeModal" @close="() => {
            showAddRoomTypeModal = false;
            roomTypeForm.reset();
            addRoomTypeErrors.value = '';
        }">
            <div class="p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Room Type</h2>
                <div v-if="addRoomTypeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addRoomTypeErrors }}
                </div>
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

                        <div class="flex justify-between">
                            <div>
                                <label for="max_adults" class="block text-sm font-medium text-gray-700">Max Adults</label>
                                <input
                                    type="number"
                                    id="max_adults"
                                    v-model="roomTypeForm.max_adults"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                            <div>
                                <label for="max_children" class="block text-sm font-medium text-gray-700">Max Children</label>
                                <input
                                    type="number"
                                    id="max_children"
                                    v-model="roomTypeForm.max_children"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                            <div>
                                <label for="max_infants" class="block text-sm font-medium text-gray-700">Max Infants</label>
                                <input
                                    type="number"
                                    id="max_infants"
                                    v-model="roomTypeForm.max_infants"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="bed_desc" class="block text-sm font-medium text-gray-700">Bed Desc</label>
                            <textarea
                                id="bed_desc"
                                v-model="roomTypeForm.bed_desc"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="2"
                            ></textarea>
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Images</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload images</span>
                                            <input
                                                id="images"
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageUpload($event, 'roomTypeForm')"
                                            />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB each</p>
                                </div>
                            </div>
                            <!-- Preview uploaded images -->
                            <div v-if="roomTypeForm.images.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <div v-for="(image, index) in roomTypeForm.images" :key="index" class="relative">
                                    <img 
                                        :src="getImagePreviewUrl(image)" 
                                        class="h-24 w-full object-cover rounded-lg" 
                                        alt="Room type image"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index, 'roomTypeForm')"
                                        class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="() => {
                                showAddRoomTypeModal = false;
                                roomTypeForm.reset();
                                addRoomTypeErrors.value = '';
                            }"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="roomTypeForm.processing"
                        >
                            Create Room Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Duplicate Room Type Modal -->
        <Modal :show="showDuplicateRoomTypeModal" @close="closeDuplicateRoomTypeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Duplicate Room Type</h2>
                <div v-if="duplicateRoomTypeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ duplicateRoomTypeErrors }}
                </div>
                <form @submit.prevent="submitDuplicateRoomType">
                    <div class="space-y-4">
                        <div>
                            <label for="duplicate_name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="duplicate_name"
                                v-model="duplicateRoomTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="duplicate_max_occupancy" class="block text-sm font-medium text-gray-700">Max Occupancy</label>
                            <input
                                type="number"
                                id="duplicate_max_occupancy"
                                v-model="duplicateRoomTypeForm.max_occupancy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                min="1"
                                required
                            />
                        </div>

                        <div class="flex justify-between">
                            <div>
                                <label for="duplicate_max_adults" class="block text-sm font-medium text-gray-700">Max Adults</label>
                                <input
                                    type="number"
                                    id="duplicate_max_adults"
                                    v-model="duplicateRoomTypeForm.max_adults"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                            <div>
                                <label for="duplicate_max_children" class="block text-sm font-medium text-gray-700">Max Children</label>
                                <input
                                    type="number"
                                    id="duplicate_max_children"
                                    v-model="duplicateRoomTypeForm.max_children"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                            <div>
                                <label for="duplicate_max_infants" class="block text-sm font-medium text-gray-700">Max Infants</label>
                                <input
                                    type="number"
                                    id="duplicate_max_infants"
                                    v-model="duplicateRoomTypeForm.max_infants"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="duplicate_bed_desc" class="block text-sm font-medium text-gray-700">Bed Desc</label>
                            <textarea
                                id="duplicate_bed_desc"
                                v-model="duplicateRoomTypeForm.bed_desc"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="2"
                            ></textarea>
                        </div>

                        <div>
                            <label for="duplicate_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="duplicate_description"
                                v-model="duplicateRoomTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Images</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="duplicate_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload images</span>
                                            <input
                                                id="duplicate_images"
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageUpload($event, 'duplicateRoomTypeForm')"
                                            />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB each</p>
                                </div>
                            </div>
                            <!-- Preview existing and uploaded images -->
                            <div v-if="duplicateRoomTypeForm.images.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <div v-for="(image, index) in duplicateRoomTypeForm.images" :key="index" class="relative">
                                    <img 
                                        :src="getImagePreviewUrl(image)" 
                                        class="h-24 w-full object-cover rounded-lg" 
                                        alt="Room type image"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index, 'duplicateRoomTypeForm')"
                                        class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeDuplicateRoomTypeModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="duplicateRoomTypeForm.processing"
                        >
                            Create Duplicate
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Room Type Modal -->
        <Modal :show="showEditRoomTypeModal" @close="closeEditRoomTypeModal">
            <div class="p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Room Type</h2>
                <div v-if="roomTypeWarning" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ roomTypeWarning }}
                </div>
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

                        <div class="flex justify-between">
                            <div>
                                <label for="max_adults" class="block text-sm font-medium text-gray-700">Max Adults</label>
                                <input
                                    type="number"
                                    id="max_adults"
                                    v-model="editRoomTypeForm.max_adults"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                            <div>
                                <label for="max_children" class="block text-sm font-medium text-gray-700">Max Children</label>
                                <input
                                    type="number"
                                    id="max_children"
                                    v-model="editRoomTypeForm.max_children"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                            <div>
                                <label for="max_infants" class="block text-sm font-medium text-gray-700">Max Infants</label>
                                <input
                                    type="number"
                                    id="max_infants"
                                    v-model="editRoomTypeForm.max_infants"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    min="1"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="edit_bed_desc" class="block text-sm font-medium text-gray-700">Bed Desc</label>
                            <textarea
                                id="edit_bed_desc"
                                v-model="editRoomTypeForm.bed_desc"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="2"
                            ></textarea>
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Images</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="edit_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload images</span>
                                            <input
                                                id="edit_images"
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageUpload($event, 'editRoomTypeForm')"
                                            />
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB each</p>
                                </div>
                            </div>
                            <!-- Preview uploaded images -->
                            <div v-if="editRoomTypeForm.images.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <div v-for="(image, index) in editRoomTypeForm.images" :key="index" class="relative">
                                    <img 
                                        :src="getImagePreviewUrl(image)" 
                                        class="h-24 w-full object-cover rounded-lg" 
                                        alt="Room type image"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index, 'editRoomTypeForm')"
                                        class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeEditRoomTypeModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="editRoomTypeForm.processing"
                        >
                            Update Room Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    package: {
        type: Object,
        required: true
    },
    roomTypes: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            links: [],
            from: 0,
            to: 0,
            total: 0,
            current_page: 1,
            last_page: 1,
            per_page: 10
        })
    }
});

const loadingKey = ref(0);
const roomTypesData = ref(props.roomTypes || {
    data: [],
    links: [],
    from: 0,
    to: 0,
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: 10
});
const showAddRoomTypeModal = ref(false);
const showEditRoomTypeModal = ref(false);
const showDuplicateRoomTypeModal = ref(false);
const addRoomTypeErrors = ref('');
const roomTypeWarning = ref('');
const duplicateRoomTypeErrors = ref('');

const roomTypesPagination = computed(() => ({
    links: roomTypesData.value.links,
    from: roomTypesData.value.from,
    to: roomTypesData.value.to,
    total: roomTypesData.value.total
}));

const roomTypeForm = useForm({
    name: '',
    bed_desc: '',
    description: '',
    max_occupancy: 4,
    max_adults: null,
    max_children: null,
    max_infants: null,
    package_id: props.package.id,
    return_to_package: true,
    images: [],
    delete_images: []
});

const editRoomTypeForm = useForm({
    id: null,
    name: '',
    bed_desc: '',
    description: '',
    max_occupancy: 4,
    max_adults: null,
    max_children: null,
    max_infants: null,
    package_id: props.package.id,
    return_to_package: true,
    images: [],
    delete_images: []
});

const duplicateRoomTypeForm = useForm({
    name: '',
    bed_desc: '',
    description: '',
    max_occupancy: 4,
    max_adults: null,
    max_children: null,
    max_infants: null,
    package_id: props.package.id,
    return_to_package: true,
    images: [],
    existing_images: [],
    originalRoomTypeId: null
});

// Watch for modal closure to reset error
watch(showAddRoomTypeModal, (newValue) => {
    if (!newValue) {
        addRoomTypeErrors.value = '';
    }
});

watch(showEditRoomTypeModal, (newValue) => {
    if (!newValue) {
        roomTypeWarning.value = '';
    }
});

watch(showDuplicateRoomTypeModal, (newValue) => {
    if (!newValue) {
        duplicateRoomTypeErrors.value = '';
    }
});

const handlePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.room-types', props.package.id), {
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

const submitRoomType = () => {
    // Validation checks
    if (!roomTypeForm.name?.trim()) {
        addRoomTypeErrors.value = 'Room type name is required';
        return;
    }
    if (!roomTypeForm.max_occupancy || roomTypeForm.max_occupancy < 1) {
        addRoomTypeErrors.value = 'Maximum occupancy must be at least 1';
        return;
    }

    // Create FormData
    const formData = new FormData();
    
    // Add basic fields
    formData.append('name', roomTypeForm.name.trim());
    formData.append('bed_desc', roomTypeForm.bed_desc?.trim() || '');
    formData.append('description', roomTypeForm.description?.trim() || '');
    formData.append('max_occupancy', roomTypeForm.max_occupancy);
    formData.append('max_adults', roomTypeForm.max_adults || '');
    formData.append('max_children', roomTypeForm.max_children || '');
    formData.append('max_infants', roomTypeForm.max_infants || '');
    formData.append('package_id', props.package.id);
    formData.append('return_to_package', 'true');

    // Handle images
    const hasNewImages = roomTypeForm.images.some(image => image instanceof File);

    if (hasNewImages) {
        roomTypeForm.images.forEach((image, index) => {
            if (image instanceof File) {
                formData.append(`images[${index}]`, image);
            }
        });
    }

    // Use axios directly for better control over the request
    axios.post(route('room-types.store'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        roomTypeForm.processing = false;
        showAddRoomTypeModal.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Room type created successfully'
        });
        // Reset form
        roomTypeForm.reset();
        // Refresh the room types list
        // handlePageChange(1);
        router.get(route('packages.show', props.package.id));
    })
    .catch(error => {
        roomTypeForm.processing = false;
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            if (errors.images) {
                addRoomTypeErrors.value = Array.isArray(errors.images) ? errors.images.join(', ') : errors.images;
            } else if (errors.name) {
                addRoomTypeErrors.value = errors.name[0];
            } else if (errors.max_occupancy) {
                addRoomTypeErrors.value = errors.max_occupancy[0];
            } else if (errors.package_id) {
                addRoomTypeErrors.value = errors.package_id[0];
            } else {
                const errorMessages = Object.entries(errors)
                    .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                    .join('\n');
                addRoomTypeErrors.value = errorMessages;
            }
        } else if (error.response?.data?.message) {
            addRoomTypeErrors.value = error.response.data.message;
        } else {
            addRoomTypeErrors.value = 'Failed to create room type. Please try again.';
        }
    })
    .finally(() => {
        roomTypeForm.processing = false;
    });
};

const editRoomType = (roomType) => {
    editRoomTypeForm.reset();
    editRoomTypeForm.id = roomType.id;
    editRoomTypeForm.name = roomType.name || '';
    editRoomTypeForm.bed_desc = roomType.bed_desc || '';
    editRoomTypeForm.description = roomType.description || '';
    editRoomTypeForm.max_occupancy = parseInt(roomType.max_occupancy) || 2;
    editRoomTypeForm.max_adults = parseInt(roomType.max_adults) || null;
    editRoomTypeForm.max_children = parseInt(roomType.max_children) || null;
    editRoomTypeForm.max_infants = parseInt(roomType.max_infants) || null;
    editRoomTypeForm.package_id = parseInt(props.package.id);
    editRoomTypeForm.images = Array.isArray(roomType.images) ? [...roomType.images] : [];
    editRoomTypeForm.delete_images = [];
    editRoomTypeForm.return_to_package = true;
    showEditRoomTypeModal.value = true;
};

const closeEditRoomTypeModal = () => {
    showEditRoomTypeModal.value = false;
    editRoomTypeForm.reset();
    roomTypeWarning.value = '';
};

const updateRoomType = () => {
    if (!editRoomTypeForm.name?.trim()) {
        roomTypeWarning.value = 'Room type name is required';
        return;
    }
    if (!editRoomTypeForm.max_occupancy || editRoomTypeForm.max_occupancy < 1) {
        roomTypeWarning.value = 'Maximum occupancy must be at least 1';
        return;
    }

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', editRoomTypeForm.name.trim());
    formData.append('bed_desc', editRoomTypeForm.bed_desc?.trim() || '');
    formData.append('description', editRoomTypeForm.description?.trim() || '');
    formData.append('max_occupancy', editRoomTypeForm.max_occupancy);
    formData.append('max_adults', editRoomTypeForm.max_adults || '');
    formData.append('max_children', editRoomTypeForm.max_children || '');
    formData.append('max_infants', editRoomTypeForm.max_infants || '');
    formData.append('package_id', props.package.id);
    formData.append('return_to_package', 'true');

    const hasNewImages = editRoomTypeForm.images.some(image => image instanceof File);
    const hasDeletedImages = editRoomTypeForm.delete_images.length > 0;

    if (hasNewImages) {
        editRoomTypeForm.images.forEach((image, index) => {
            if (image instanceof File) {
                formData.append(`images[${index}]`, image);
            }
        });
    }

    if (hasDeletedImages) {
        editRoomTypeForm.delete_images.forEach((imagePath, index) => {
            formData.append(`delete_images[${index}]`, imagePath);
        });
    }

    axios.post(route('room-types.update', editRoomTypeForm.id), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        editRoomTypeForm.processing = false;
        showEditRoomTypeModal.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Room type updated successfully'
        });
        editRoomTypeForm.reset();
        handlePageChange(1);
    })
    .catch(error => {
        editRoomTypeForm.processing = false;
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            if (errors.images) {
                roomTypeWarning.value = Array.isArray(errors.images) ? errors.images.join(', ') : errors.images;
            } else if (errors.name) {
                roomTypeWarning.value = errors.name[0];
            } else if (errors.max_occupancy) {
                roomTypeWarning.value = errors.max_occupancy[0];
            } else if (errors.package_id) {
                roomTypeWarning.value = errors.package_id[0];
            } else {
                const errorMessages = Object.entries(errors)
                    .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                    .join('\n');
                roomTypeWarning.value = errorMessages;
            }
        } else if (error.response?.data?.message) {
            roomTypeWarning.value = error.response.data.message;
        } else {
            roomTypeWarning.value = 'Failed to update room type. Please try again.';
        }
    })
    .finally(() => {
        editRoomTypeForm.processing = false;
    });
};

const duplicateRoomType = (roomType) => {
    // Generate unique name for duplicate
    const baseName = roomType.name.replace(/\s\(Copy(?:\s\d+)?\)$/, '');
    let newName = baseName + ' (Copy)';
    
    // Check if name already exists in current package room types
    const existingNames = roomTypesData.value.data.map(rt => rt.name);
    if (existingNames.includes(newName)) {
        let copyNum = 1;
        while (existingNames.includes(baseName + ' (Copy ' + (copyNum + 1) + ')')) {
            copyNum++;
        }
        newName = baseName + ' (Copy ' + (copyNum + 1) + ')';
    }
    
    // Reset and populate duplicate form
    duplicateRoomTypeForm.reset();
    duplicateRoomTypeForm.name = newName;
    duplicateRoomTypeForm.bed_desc = roomType.bed_desc || '';
    duplicateRoomTypeForm.description = roomType.description || '';
    duplicateRoomTypeForm.max_occupancy = parseInt(roomType.max_occupancy) || 4;
    duplicateRoomTypeForm.max_adults = parseInt(roomType.max_adults) || null;
    duplicateRoomTypeForm.max_children = parseInt(roomType.max_children) || null;
    duplicateRoomTypeForm.max_infants = parseInt(roomType.max_infants) || null;
    duplicateRoomTypeForm.package_id = parseInt(props.package.id);
    duplicateRoomTypeForm.originalRoomTypeId = roomType.id;
    duplicateRoomTypeForm.images = Array.isArray(roomType.images) ? [...roomType.images] : [];
    duplicateRoomTypeForm.existing_images = Array.isArray(roomType.images) ? [...roomType.images] : [];
    duplicateRoomTypeForm.return_to_package = true;
    duplicateRoomTypeErrors.value = '';
    
    showDuplicateRoomTypeModal.value = true;
};

const closeDuplicateRoomTypeModal = () => {
    showDuplicateRoomTypeModal.value = false;
    duplicateRoomTypeForm.reset();
    duplicateRoomTypeErrors.value = '';
};

const submitDuplicateRoomType = () => {
    // Validation checks
    if (!duplicateRoomTypeForm.name?.trim()) {
        duplicateRoomTypeErrors.value = 'Room type name is required';
        return;
    }
    if (!duplicateRoomTypeForm.max_occupancy || duplicateRoomTypeForm.max_occupancy < 1) {
        duplicateRoomTypeErrors.value = 'Maximum occupancy must be at least 1';
        return;
    }

    duplicateRoomTypeForm.processing = true;
    duplicateRoomTypeErrors.value = '';

    // Create FormData
    const formData = new FormData();
    
    // Add basic fields
    formData.append('name', duplicateRoomTypeForm.name.trim());
    formData.append('bed_desc', duplicateRoomTypeForm.bed_desc?.trim() || '');
    formData.append('description', duplicateRoomTypeForm.description?.trim() || '');
    formData.append('max_occupancy', duplicateRoomTypeForm.max_occupancy);
    formData.append('max_adults', duplicateRoomTypeForm.max_adults || '');
    formData.append('max_children', duplicateRoomTypeForm.max_children || '');
    formData.append('max_infants', duplicateRoomTypeForm.max_infants || '');
    formData.append('package_id', props.package.id);
    formData.append('return_to_package', 'true');
    
    // Separate existing images (strings) from new uploads (File objects)
    const existingImages = duplicateRoomTypeForm.images.filter(img => typeof img === 'string');
    const newImages = duplicateRoomTypeForm.images.filter(img => img instanceof File);
    
    // Add existing images to copy
    existingImages.forEach((imagePath, index) => {
        formData.append(`existing_images[${index}]`, imagePath);
    });
    
    // Add new image uploads
    if (newImages.length > 0) {
        newImages.forEach((image, index) => {
            formData.append(`images[${index}]`, image);
        });
    }

    // Use axios directly for better control over the request
    axios.post(route('room-types.duplicate', duplicateRoomTypeForm.originalRoomTypeId), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        duplicateRoomTypeForm.processing = false;
        showDuplicateRoomTypeModal.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Room type duplicated successfully'
        });
        // Reset form
        duplicateRoomTypeForm.reset();
        // Refresh the room types list
        router.get(route('packages.show', props.package.id));
    })
    .catch(error => {
        duplicateRoomTypeForm.processing = false;
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            if (errors.images) {
                duplicateRoomTypeErrors.value = Array.isArray(errors.images) ? errors.images.join(', ') : errors.images;
            } else if (errors.name) {
                duplicateRoomTypeErrors.value = errors.name[0];
            } else if (errors.max_occupancy) {
                duplicateRoomTypeErrors.value = errors.max_occupancy[0];
            } else if (errors.package_id) {
                duplicateRoomTypeErrors.value = errors.package_id[0];
            } else {
                const errorMessages = Object.entries(errors)
                    .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
                    .join('\n');
                duplicateRoomTypeErrors.value = errorMessages;
            }
        } else if (error.response?.data?.message) {
            duplicateRoomTypeErrors.value = error.response.data.message;
        } else {
            duplicateRoomTypeErrors.value = 'Failed to duplicate room type. Please try again.';
        }
    })
    .finally(() => {
        duplicateRoomTypeForm.processing = false;
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
        confirmButtonText: 'Yes, delete it!',
        showConfirmButton: true,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('room-types.destroy', id), {
                data: { return_to_package: true },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Room type has been deleted.',
                        icon: 'success',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4F46E5'
                    });
                    handlePageChange(1);
                }
            });
        }
    });
};

const handleImageUpload = (event, formName) => {
    const files = Array.from(event.target.files);
    let form;
    if (formName === 'roomTypeForm') {
        form = roomTypeForm;
    } else if (formName === 'editRoomTypeForm') {
        form = editRoomTypeForm;
    } else if (formName === 'duplicateRoomTypeForm') {
        form = duplicateRoomTypeForm;
    }
    
    const validFiles = files.filter(file => {
        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: `${file.name} is not an image file.`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4F46E5'
            });
            return false;
        }
        
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: `${file.name} is larger than 2MB.`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#4F46E5'
            });
            return false;
        }
        
        return true;
    });
    
    form.images = [...form.images, ...validFiles];
};

const removeImage = (index, formName) => {
    let form;
    if (formName === 'roomTypeForm') {
        form = roomTypeForm;
    } else if (formName === 'editRoomTypeForm') {
        form = editRoomTypeForm;
    } else if (formName === 'duplicateRoomTypeForm') {
        form = duplicateRoomTypeForm;
    }
    
    const image = form.images[index];
    
    if (typeof image === 'string') {
        if (formName === 'editRoomTypeForm') {
            form.delete_images.push(image);
        } else if (formName === 'duplicateRoomTypeForm') {
            // For duplicate, just remove from existing_images array
            const existingIndex = form.existing_images.indexOf(image);
            if (existingIndex > -1) {
                form.existing_images.splice(existingIndex, 1);
            }
        }
    }
    
    form.images.splice(index, 1);
};

const getImageUrl = (imagePath) => {
    if (imagePath.startsWith('images/')) {
        return `/${imagePath}`;
    }
    if (imagePath.startsWith('room-types/')) {
        return `/images/${imagePath}`;
    }
    return `/images/${imagePath}`;
};

const getImagePreviewUrl = (image) => {
    if (typeof image === 'string') {
        return getImageUrl(image);
    }
    
    if (typeof window !== 'undefined' && window.URL && window.URL.createObjectURL) {
        return URL.createObjectURL(image);
    }
    
    return '';
};

const showImageModal = (image) => {
    Swal.fire({
        imageUrl: getImageUrl(image),
        imageAlt: 'Room image',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: {
            container: 'image-modal',
            popup: 'max-w-4xl',
            closeButton: 'text-gray-500 hover:text-gray-700',
            image: 'max-h-[80vh] object-contain'
        },
        background: '#f8f9fa',
        padding: '1rem',
        width: 'auto',
        backdrop: 'rgba(0,0,0,0.8)'
    });
};
</script>

<style scoped>
:deep(.image-modal) {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

:deep(.image-modal::-webkit-scrollbar) {
    display: none !important;
}

:deep(.swal2-popup) {
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
}

:deep(.swal2-html-container) {
    margin: 0 !important;
    padding: 0 !important;
}

:deep(.swal2-image) {
    margin: 0 !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

:deep(.swal2-close) {
    color: white !important;
    font-size: 2rem !important;
    right: 1rem !important;
    top: 1rem !important;
}

:deep(.swal2-close:hover) {
    color: #e5e7eb !important;
}
</style> 