<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-md font-medium text-gray-900">Season Types</h3>
            <div class="flex space-x-2">
                <!-- single add season -->
                <button
                    @click="showAddSeasonTypeModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs hidden"
                >
                    Add Season
                </button>
                <!-- bulk add season -->
                <button
                    @click="showBulkAddSeasonModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                >
                    Add Seasons
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="seasons.data.length > 0" v-for="season in seasons.data" :key="season.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ season.type.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ moment(season.start_date).format('DD/MM/YYYY') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ moment(season.end_date).format('DD/MM/YYYY') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                @click="editSeasonType(season)"
                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteSeasonType(season.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="3" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No season types found</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="seasons?.links?.length > 0" class="mt-4">
            <Pagination
                :links="seasonsPagination.links"
                :from="seasonsPagination.from"
                :to="seasonsPagination.to"
                :total="seasonsPagination.total"
                :component-loading="true"
                @page-change="handlePageChange"
                :key="loadingKey"
            />
        </div>

        <!-- Add Season Type Modal -->
        <Modal :show="showAddSeasonTypeModal" @close="() => {
            showAddSeasonTypeModal = false;
            seasonTypeForm.reset();
            addSeasonTypeErrors.value = '';
        }">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Season</h2>
                <div v-if="addSeasonTypeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addSeasonTypeErrors }}
                </div>
                <form @submit.prevent="submitSeasonType">
                    <div class="space-y-4">
                        <!-- Season Type Selection -->
                        <div>
                            <label for="season_type_id" class="block text-sm font-medium text-gray-700">Season Type</label>
                            <select
                                id="season_type_id"
                                v-model="seasonTypeForm.season_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Select a season type</option>
                                <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Date Range Picker -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="seasonTypeForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="seasonTypeForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="() => {
                                showAddSeasonTypeModal = false;
                                seasonTypeForm.reset();
                                addSeasonTypeErrors.value = '';
                            }"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="seasonTypeForm.processing"
                        >
                            Create Season
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Season Type Modal -->
        <Modal :show="showEditSeasonTypeModal" @close="closeEditSeasonTypeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Season Type</h2>
                <div v-if="seasonTypeWarning" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ seasonTypeWarning }}
                </div>
                <form @submit.prevent="updateSeasonType">
                    <div class="space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="edit_name"
                                v-model="editSeasonTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="edit_description"
                                v-model="editSeasonTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeEditSeasonTypeModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="editSeasonTypeForm.processing"
                        >
                            Update Season Type
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Bulk Add Seasons Modal -->
        <Modal :show="showBulkAddSeasonModal" @close="closeBulkAddSeasonModal">
            <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Add Seasons</h2>
                <button
                    type="button"
                    @click="addBulkSeason"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-xs"
                >
                    + Add Row
                </button>
            </div>
                
                <!-- Results Summary -->
                <div v-if="bulkResults" class="mb-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-md font-medium text-gray-900 mb-3">Results Summary</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="bulkResults.success.length > 0" class="bg-green-50 border border-green-200 rounded-md p-3">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-green-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium text-green-800">
                                        {{ bulkResults.success.length }} seasons created successfully
                                    </span>
                                </div>
                            </div>
                            
                            <div v-if="bulkResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded-md p-3">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-red-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium text-red-800">
                                        {{ bulkResults.errors.length }} seasons failed to create
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitBulkSeasons">
                    <div class="space-y-4">
                        <!-- Season Rows -->
                        <div v-for="(season, index) in bulkSeasons" :key="index" class="border rounded-lg p-4" :class="getBulkRowClass(index)">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-md font-medium text-gray-900 invisible">Season {{ index + 1 }}</h3>
                                <div class="flex space-x-2">
                                    <button
                                        type="button"
                                        @click="removeBulkSeason(index)"
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 text-sm"
                                        :disabled="bulkSeasons.length === 1"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Error Display -->
                            <div v-if="getBulkRowError(index)" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-800">{{ getBulkRowError(index) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Success Display -->
                            <div v-if="getBulkRowSuccess(index)" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-800">{{ getBulkRowSuccess(index) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label :for="`bulk_season_type_id_${index}`" class="block text-sm font-medium text-gray-700">Season Type</label>
                                    <select
                                        :id="`bulk_season_type_id_${index}`"
                                        v-model="season.season_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select a season type</option>
                                        <option v-for="type in seasonTypes" :key="type.id" :value="type.id">
                                            {{ type.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label :for="`bulk_start_date_${index}`" class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        type="date"
                                        :id="`bulk_start_date_${index}`"
                                        v-model="season.start_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label :for="`bulk_end_date_${index}`" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        type="date"
                                        :id="`bulk_end_date_${index}`"
                                        v-model="season.end_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeBulkAddSeasonModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="isBulkSubmitting"
                        >
                            {{ isBulkSubmitting ? 'Creating Seasons...' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import moment from 'moment';

const props = defineProps({
    package: {
        type: Object,
        required: true
    },
    seasons: {
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
    },
    seasonTypes: {
        type: Array,
        required: true,
        default: () => []
    }
});

const loadingKey = ref(0);
const seasonsData = ref(props.seasons || {
    data: [],
    links: [],
    from: 0,
    to: 0,
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: 10
});
const showAddSeasonTypeModal = ref(false);
const showEditSeasonTypeModal = ref(false);
const showBulkAddSeasonModal = ref(false);
const addSeasonTypeErrors = ref('');
const seasonTypeWarning = ref('');
const isBulkSubmitting = ref(false);
const bulkResults = ref(null);

// Initialize bulk seasons with one empty entry
const bulkSeasons = ref([
    {
        season_type_id: '',
        start_date: '',
        end_date: ''
    }
]);

const seasonsPagination = computed(() => ({
    links: seasonsData.value.links,
    from: seasonsData.value.from,
    to: seasonsData.value.to,
    total: seasonsData.value.total
}));

const seasonTypeForm = useForm({
    season_type_id: '',
    start_date: '',
    end_date: '',
    package_id: props.package.id,
    return_to_package: true
});

const editSeasonTypeForm = useForm({
    id: null,
    name: '',
    description: '',
    package_id: props.package.id,
    return_to_package: true
});

// Watch for modal closure to reset error
watch(showAddSeasonTypeModal, (newValue) => {
    if (!newValue) {
        addSeasonTypeErrors.value = '';
    }
});

watch(showEditSeasonTypeModal, (newValue) => {
    if (!newValue) {
        seasonTypeWarning.value = '';
    }
});

const handlePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.seasons', props.package.id), {
            params: { page }
        });

        const res = response.data;

        seasonsData.value.data = res.data;
        seasonsData.value.links = res.links;
        seasonsData.value.from = res.from;
        seasonsData.value.to = res.to;
        seasonsData.value.total = res.total;
        seasonsData.value.current_page = res.current_page;
        seasonsData.value.last_page = res.last_page;
        seasonsData.value.per_page = res.per_page;

        loadingKey.value++;
    } catch (error) {
        console.error('Error fetching season types:', error);
    }
};

const submitSeasonType = () => {
    if (!seasonTypeForm.season_type_id) {
        addSeasonTypeErrors.value = 'Season type is required';
        return;
    }
    if (!seasonTypeForm.start_date) {
        addSeasonTypeErrors.value = 'Start date is required';
        return;
    }
    if (!seasonTypeForm.end_date) {
        addSeasonTypeErrors.value = 'End date is required';
        return;
    }

    seasonTypeForm.post(route('seasons.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddSeasonTypeModal.value = false;
            seasonTypeForm.reset();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Season created successfully'
            });
            handlePageChange(1);
        },
        onError: (errors) => {
            if (errors.season_type_id) {
                addSeasonTypeErrors.value = errors.season_type_id;
            } else if (errors.start_date) {
                addSeasonTypeErrors.value = errors.start_date;
            } else if (errors.end_date) {
                addSeasonTypeErrors.value = errors.end_date;
            } else if (errors.date_range) {
                addSeasonTypeErrors.value = errors.date_range;
            } else {
                addSeasonTypeErrors.value = 'Failed to create season. Please try again.';
            }
        }
    });
};

const editSeasonType = (seasonType) => {
    editSeasonTypeForm.reset();
    editSeasonTypeForm.id = seasonType.id;
    editSeasonTypeForm.name = seasonType.name || '';
    editSeasonTypeForm.description = seasonType.description || '';
    editSeasonTypeForm.package_id = parseInt(props.package.id);
    editSeasonTypeForm.return_to_package = true;
    showEditSeasonTypeModal.value = true;
};

const closeEditSeasonTypeModal = () => {
    showEditSeasonTypeModal.value = false;
    editSeasonTypeForm.reset();
    seasonTypeWarning.value = '';
};

const updateSeasonType = () => {
    if (!editSeasonTypeForm.name?.trim()) {
        seasonTypeWarning.value = 'Season type name is required';
        return;
    }

    editSeasonTypeForm.put(route('season-types.update', editSeasonTypeForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditSeasonTypeModal.value = false;
            editSeasonTypeForm.reset();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Season type updated successfully'
            });
            handlePageChange(1);
        },
        onError: (errors) => {
            if (errors.name) {
                seasonTypeWarning.value = errors.name;
            } else if (errors.description) {
                seasonTypeWarning.value = errors.description;
            } else if (errors.package_id) {
                seasonTypeWarning.value = errors.package_id;
            } else {
                seasonTypeWarning.value = 'Failed to update season type. Please try again.';
            }
        }
    });
};

const deleteSeasonType = (id) => {
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
            router.delete(route('season-types.destroy', id), {
                data: { return_to_package: true },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Season type has been deleted.',
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

// Bulk operations
const addBulkSeason = () => {
    bulkSeasons.value.push({
        season_type_id: '',
        start_date: '',
        end_date: ''
    });
};

const removeBulkSeason = (index) => {
    if (bulkSeasons.value.length > 1) {
        bulkSeasons.value.splice(index, 1);
    }
};

const getBulkRowClass = (index) => {
    if (bulkResults.value) {
        const hasError = bulkResults.value.errors.some(error => error.index === index);
        const hasSuccess = bulkResults.value.success.some(success => success.index === index);
        
        if (hasError) return 'border-red-200 bg-red-50';
        if (hasSuccess) return 'border-green-200 bg-green-50';
    }
    return 'border-gray-200';
};

const getBulkRowError = (index) => {
    if (!bulkResults.value) return null;
    
    // Check for validation errors first
    const validationError = bulkResults.value.validation_errors?.find(error => error.index === index);
    if (validationError) {
        return validationError.errors.join(', ');
    }
    
    // Check for other errors
    const error = bulkResults.value.errors.find(error => error.index === index);
    if (error) {
        return error.message || error;
    }
    
    return null;
};

const getBulkRowSuccess = (index) => {
    if (!bulkResults.value) return null;
    const success = bulkResults.value.success.find(success => success.index === index);
    return success ? success.message : null;
};

const closeBulkAddSeasonModal = () => {
    showBulkAddSeasonModal.value = false;
    bulkResults.value = null;
    bulkSeasons.value = [
        {
            season_type_id: '',
            start_date: '',
            end_date: ''
        }
    ];
};

const submitBulkSeasons = async () => {
    isBulkSubmitting.value = true;
    bulkResults.value = null;

    // how to remove all the success on the bulkSeasons.value
    bulkSeasons.value = bulkSeasons.value.filter(season => !season.success);

    try {
        const response = await axios.post(route('seasons.store-bulk'), {
            package_id: props.package.id,
            seasons: bulkSeasons.value
        });

        bulkResults.value = response.data;

        // Show summary alert
        const successCount = bulkResults.value.success.length;
        const errorCount = bulkResults.value.errors.length;

        if (errorCount === 0) {
            closeBulkAddSeasonModal();
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `${successCount} seasons were added successfully!`,
                confirmButtonText: 'OK'
            });
            handlePageChange(1);
        } else if (successCount === 0) {

        } else {
            partialSuccess(successCount, errorCount);
        }

    } catch (error) {
        console.error('Error creating seasons:', error);
        
        let errorMessage = 'Failed to create seasons';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            errorMessage = Object.values(error.response.data.errors).flat().join(', ');
        }

        // showBulkAddSeasonModal.value = false;
        // Swal.fire({
        //     icon: 'error',
        //     title: 'Error',
        //     text: errorMessage,
        //     confirmButtonText: 'OK'
        // }).then(() => {
        //     showBulkAddSeasonModal.value = true;
        // });
    } finally {
        isBulkSubmitting.value = false;
    }
};
</script> 