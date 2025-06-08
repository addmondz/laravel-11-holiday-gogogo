<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-md font-medium text-gray-900">Date Types Ranges</h3>
            <button
                @click="showAddDateTypeRangeModal = true"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
            >
                Add Date Type Range
            </button>
        </div>

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
                    <tr v-for="dateTypeRange in dateTypeRangesData.data" :key="dateTypeRange.id">
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
                        <td colspan="4" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">No date type ranges found</td>
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
const showAddDateTypeRangeModal = ref(false);
const showEditDateTypeRangeModal = ref(false);
const addDateTypeRangeErrors = ref('');
const dateTypeRangeWarning = ref('');

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
                data: { return_to_package: true },
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
</script> 