<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h3 class="text-md font-medium text-gray-900">Date Types Ranges</h3>
                <button
                    v-if="selectedIds.length > 0"
                    @click="bulkDeleteDateTypeRanges"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Delete Selected ({{ selectedIds.length }})</span>
                </button>
            </div>
            <div class="flex space-x-2">
                <button
                    @click="showAddDateTypeRangeModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs hidden"
                >
                    Add Date Type Range
                </button>
                <!-- bulk add season -->
                <button
                    @click="showBulkAddDateTypeRangeModal = true"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                >
                    Add Date Type Ranges
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                :indeterminate="isIndeterminate"
                                @change="toggleSelectAll"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="dateTypeRange in dateTypeRangesData.data" :key="dateTypeRange.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(dateTypeRange.id)"
                                @change="toggleSelect(dateTypeRange.id)"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ dateTypeRange.date_type?.name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(dateTypeRange.start_date).format('DD/MM/YYYY') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ moment(dateTypeRange.end_date).format('DD/MM/YYYY') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                @click="editDateTypeRange(dateTypeRange)"
                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteDateTypeRange(dateTypeRange.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="dateTypeRangesData.data.length === 0">
                        <td colspan="5" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No date type ranges found</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination
                :links="dateTypeRangesPagination.links"
                :from="dateTypeRangesPagination.from"
                :to="dateTypeRangesPagination.to"
                :total="dateTypeRangesPagination.total"
                :component-loading="true"
                @page-change="handlePageChange"
                :key="loadingKey"
            />
        </div>

        <!-- Add Date Type Range Modal -->
        <Modal :show="showAddDateTypeRangeModal" @close="() => {
            showAddDateTypeRangeModal = false;
            dateTypeRangeForm.reset();
            addDateTypeRangeErrors.value = '';
        }">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Date Type Range</h2>
                <div v-if="addDateTypeRangeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addDateTypeRangeErrors }}
                </div>
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
                                <option v-for="dateType in dateTypes" :key="dateType.id" :value="dateType.id">
                                    {{ dateType.name }}
                                </option>
                            </select>
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
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="() => {
                                showAddDateTypeRangeModal = false;
                                dateTypeRangeForm.reset();
                                addDateTypeRangeErrors.value = '';
                            }"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="dateTypeRangeForm.processing"
                        >
                            Create Date Type Range
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Date Type Range Modal -->
        <Modal :show="showEditDateTypeRangeModal" @close="closeEditDateTypeRangeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Date Type Range</h2>
                <div v-if="dateTypeRangeWarning" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ dateTypeRangeWarning }}
                </div>
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
                                <option v-for="dateType in dateTypes" :key="dateType.id" :value="dateType.id">
                                    {{ dateType.name }}
                                </option>
                            </select>
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
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeEditDateTypeRangeModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="editDateTypeRangeForm.processing"
                        >
                            Update Date Type Range
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Bulk Add Date Type Ranges Modal -->
        <Modal :show="showBulkAddDateTypeRangeModal" @close="closeBulkAddDateTypeRangeModal">
            <div class="p-6 flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center mb-4 flex-shrink-0">
                <h2 class="text-lg font-medium text-gray-900">Add Date Type Ranges</h2>
                <button
                    type="button"
                    @click="addBulkDateTypeRange"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-xs"
                >
                    + Add Row
                </button>
            </div>

            <!-- Scrollable content area -->
            <div class="flex-1 overflow-y-auto min-h-0">
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
                                        {{ bulkResults.success.length }} date type ranges created successfully
                                    </span>
                                </div>
                            </div>

                            <div v-if="bulkResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded-md p-3">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-red-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium text-red-800">
                                        {{ bulkResults.errors.length }} date type ranges failed to create
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="bulkAddForm" @submit.prevent="submitBulkDateTypeRanges">
                    <div class="space-y-4">
                        <!-- Date Type Range Rows -->
                        <div v-for="(dateTypeRange, index) in bulkDateTypeRanges" :key="index" class="border rounded-lg p-4" :class="getBulkRowClass(index)">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-md font-medium text-gray-900 invisible">Date Type Range {{ index + 1 }}</h3>
                                <div class="flex space-x-2">
                                    <button
                                        type="button"
                                        @click="removeBulkDateTypeRange(index)"
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 text-sm"
                                        :disabled="bulkDateTypeRanges.length === 1"
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
                                    <label :for="`bulk_date_type_id_${index}`" class="block text-sm font-medium text-gray-700">Date Type</label>
                                    <select
                                        :id="`bulk_date_type_id_${index}`"
                                        v-model="dateTypeRange.date_type_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">Select a date type</option>
                                        <option v-for="dateType in dateTypes" :key="dateType.id" :value="dateType.id">
                                            {{ dateType.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label :for="`bulk_start_date_${index}`" class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        type="date"
                                        :id="`bulk_start_date_${index}`"
                                        v-model="dateTypeRange.start_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label :for="`bulk_end_date_${index}`" class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input
                                        type="date"
                                        :id="`bulk_end_date_${index}`"
                                        v-model="dateTypeRange.end_date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- End scrollable content area -->

                    <div class="mt-6 flex justify-end space-x-3 flex-shrink-0">
                        <button
                            type="button"
                            @click="closeBulkAddDateTypeRangeModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            form="bulkAddForm"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="isBulkSubmitting"
                        >
                            {{ isBulkSubmitting ? 'Creating Date Type Ranges...' : 'Create' }}
                        </button>
                    </div>
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
import moment from 'moment';

const props = defineProps({
    package: {
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
    }
});

const loadingKey = ref(0);
const dateTypeRangesData = ref(props.dateTypeRanges);

// Bulk selection state
const selectedIds = ref([]);

const isAllSelected = computed(() => {
    if (dateTypeRangesData.value.data.length === 0) return false;
    return dateTypeRangesData.value.data.every(range => selectedIds.value.includes(range.id));
});

const isIndeterminate = computed(() => {
    const selectedOnPage = dateTypeRangesData.value.data.filter(range => selectedIds.value.includes(range.id));
    return selectedOnPage.length > 0 && selectedOnPage.length < dateTypeRangesData.value.data.length;
});

const showAddDateTypeRangeModal = ref(false);
const showEditDateTypeRangeModal = ref(false);
const addDateTypeRangeErrors = ref('');
const dateTypeRangeWarning = ref('');

const bulkResults = ref(null);
const isBulkSubmitting = ref(false);
const showBulkAddDateTypeRangeModal = ref(false);
const bulkDateTypeRanges = ref([
    {
        date_type_id: '',
        start_date: '',
        end_date: ''
    }
]);

const dateTypeRangesPagination = computed(() => ({
    links: dateTypeRangesData.value.links,
    from: dateTypeRangesData.value.from,
    to: dateTypeRangesData.value.to,
    total: dateTypeRangesData.value.total
}));

const dateTypeRangeForm = useForm({
    date_type_id: '',
    start_date: '',
    end_date: '',
    package_id: props.package.id,
    return_to_package: true
});

const editDateTypeRangeForm = useForm({
    id: null,
    date_type_id: '',
    start_date: '',
    end_date: '',
    package_id: props.package.id,
    return_to_package: true
});

// Watch for modal closure to reset error
watch(showAddDateTypeRangeModal, (newValue) => {
    if (!newValue) {
        addDateTypeRangeErrors.value = '';
    }
});

watch(showEditDateTypeRangeModal, (newValue) => {
    if (!newValue) {
        dateTypeRangeWarning.value = '';
    }
});

const handlePageChange = async (page) => {
    try {
        const response = await axios.get(route('packages.date-type-ranges', props.package.id), {
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

const submitDateTypeRange = () => {
    if (!dateTypeRangeForm.date_type_id) {
        addDateTypeRangeErrors.value = 'Date type is required';
        return;
    }
    if (!dateTypeRangeForm.start_date) {
        addDateTypeRangeErrors.value = 'Start date is required';
        return;
    }
    if (!dateTypeRangeForm.end_date) {
        addDateTypeRangeErrors.value = 'End date is required';
        return;
    }

    if (moment(dateTypeRangeForm.end_date).isBefore(dateTypeRangeForm.start_date)) {
        addDateTypeRangeErrors.value = 'End date must be after start date';
        return;
    }

    dateTypeRangeForm.post(route('date-type-ranges.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddDateTypeRangeModal.value = false;
            dateTypeRangeForm.reset();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Date type range created successfully'
            });
            handlePageChange(1);
            // router.get(route('packages.show', props.package.id));
        },
        onError: (errors) => {
            if (errors.date_type_id) {
                addDateTypeRangeErrors.value = errors.date_type_id;
            } else if (errors.start_date) {
                addDateTypeRangeErrors.value = errors.start_date;
            } else if (errors.end_date) {
                addDateTypeRangeErrors.value = errors.end_date;
            } else if (errors.package_id) {
                addDateTypeRangeErrors.value = errors.package_id;
            } else {
                addDateTypeRangeErrors.value = 'Failed to create date type range. Please try again.';
            }
        }
    });
};

const editDateTypeRange = (dateTypeRange) => {
    editDateTypeRangeForm.reset();
    editDateTypeRangeForm.id = dateTypeRange.id;
    editDateTypeRangeForm.date_type_id = dateTypeRange.date_type_id;
    editDateTypeRangeForm.start_date = moment(dateTypeRange.start_date).format('YYYY-MM-DD');
    editDateTypeRangeForm.end_date = moment(dateTypeRange.end_date).format('YYYY-MM-DD');
    editDateTypeRangeForm.package_id = parseInt(props.package.id);
    editDateTypeRangeForm.return_to_package = true;
    showEditDateTypeRangeModal.value = true;
};

const closeEditDateTypeRangeModal = () => {
    showEditDateTypeRangeModal.value = false;
    editDateTypeRangeForm.reset();
    dateTypeRangeWarning.value = '';
};

const updateDateTypeRange = () => {
    if (!editDateTypeRangeForm.date_type_id) {
        dateTypeRangeWarning.value = 'Date type is required';
        return;
    }
    if (!editDateTypeRangeForm.start_date) {
        dateTypeRangeWarning.value = 'Start date is required';
        return;
    }
    if (!editDateTypeRangeForm.end_date) {
        dateTypeRangeWarning.value = 'End date is required';
        return;
    }

    if (moment(editDateTypeRangeForm.end_date).isBefore(editDateTypeRangeForm.start_date)) {
        dateTypeRangeWarning.value = 'End date must be after start date';
        return;
    }

    editDateTypeRangeForm.put(route('date-type-ranges.update', editDateTypeRangeForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditDateTypeRangeModal.value = false;
            editDateTypeRangeForm.reset();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Date type range updated successfully'
            });
            handlePageChange(1);
        },
        onError: (errors) => {
            if (errors.date_type_id) {
                dateTypeRangeWarning.value = errors.date_type_id;
            } else if (errors.start_date) {
                dateTypeRangeWarning.value = errors.start_date;
            } else if (errors.end_date) {
                dateTypeRangeWarning.value = errors.end_date;
            } else if (errors.package_id) {
                dateTypeRangeWarning.value = errors.package_id;
            } else {
                dateTypeRangeWarning.value = 'Failed to update date type range. Please try again.';
            }
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
        confirmButtonText: 'Yes, delete it!',
        showConfirmButton: true,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('date-type-ranges.destroy', id), {
                data: { return_to_package: true, package_id: props.package.id },
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Date type range has been deleted.',
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

// Bulk selection functions
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        // Deselect all on current page
        const currentPageIds = dateTypeRangesData.value.data.map(r => r.id);
        selectedIds.value = selectedIds.value.filter(id => !currentPageIds.includes(id));
    } else {
        // Select all on current page
        const currentPageIds = dateTypeRangesData.value.data.map(r => r.id);
        selectedIds.value = [...new Set([...selectedIds.value, ...currentPageIds])];
    }
};

const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(id);
    }
};

const bulkDeleteDateTypeRanges = () => {
    if (selectedIds.value.length === 0) return;

    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete ${selectedIds.value.length} date type range(s). This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!',
        showConfirmButton: true,
        showCloseButton: true
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await axios.delete(route('date-type-ranges.destroy-bulk'), {
                    data: {
                        ids: selectedIds.value,
                        package_id: props.package.id
                    }
                });

                Swal.fire({
                    title: 'Deleted!',
                    text: response.data.message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4F46E5'
                });

                selectedIds.value = [];
                handlePageChange(1);
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'Failed to delete date type ranges',
                    icon: 'error',
                    confirmButtonColor: '#4F46E5'
                });
            }
        }
    });
};

// Bulk operations
const addBulkDateTypeRange = () => {
    bulkDateTypeRanges.value.push({
        date_type_id: '',
        start_date: '',
        end_date: ''
    });
};

const removeBulkDateTypeRange = (index) => {
    if (bulkDateTypeRanges.value.length > 1) {
        bulkDateTypeRanges.value.splice(index, 1);
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

const closeBulkAddDateTypeRangeModal = () => {
    showBulkAddDateTypeRangeModal.value = false;
    bulkResults.value = null;
    bulkDateTypeRanges.value = [
        {
            date_type_id: '',
            start_date: '',
            end_date: ''
        }
    ];
    handlePageChange(1);
};

const partialSuccess = (successCount, errorCount) => {
    Swal.fire({
        icon: 'warning',
        title: 'Partial Success',
        text: `${successCount} date type ranges created successfully, ${errorCount} failed. Please check the results below.`,
        confirmButtonText: 'OK'
    });
};

const submitBulkDateTypeRanges = async () => {
    isBulkSubmitting.value = true;
    bulkResults.value = null;

    try {
        const response = await axios.post(route('date-type-ranges.store-bulk'), {
            package_id: props.package.id,
            dateTypeRanges: bulkDateTypeRanges.value
        });

        bulkResults.value = response.data;

        // Show summary alert
        const successCount = bulkResults.value.success.length;
        const errorCount = bulkResults.value.errors.length;

        if (errorCount === 0) {
            closeBulkAddDateTypeRangeModal();
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: `${successCount} date type ranges were added successfully!`,
                confirmButtonText: 'OK'
            });
        } else if (successCount === 0) {
            // All failed, keep modal open to show errors
        } else {
            // partialSuccess(successCount, errorCount);
        }

    } catch (error) {
        console.error('Error creating date type ranges:', error);
        
        let errorMessage = 'Failed to create date type ranges';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data?.errors) {
            errorMessage = Object.values(error.response.data.errors).flat().join(', ');
        }

        // showBulkAddDateTypeRangeModal.value = false;
        // Swal.fire({
        //     icon: 'error',
        //     title: 'Error',
        //     text: errorMessage,
        //     confirmButtonText: 'OK'
        // }).then(() => {
        //     showBulkAddDateTypeRangeModal.value = true;
        // });
    } finally {
        isBulkSubmitting.value = false;
    }
};
</script> 