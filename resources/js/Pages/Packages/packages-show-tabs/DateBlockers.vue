<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <h3 class="text-md font-medium text-gray-900">Date Blockers</h3>
                <button
                    v-if="selectedIds.length > 0"
                    @click="bulkDeleteBlockers"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Delete Selected ({{ selectedIds.length }})</span>
                </button>
            </div>
            <button
                @click="showAddModal = true"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
            >
                Add Date Blocker
            </button>
        </div>
        
        <!-- Date Blockers Table -->
        <div class="overflow-x-auto">
            <div v-if="initialLoading" class="flex justify-center items-center h-full min-h-[400px]">
                <LoadingComponent />
            </div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                :indeterminate="isIndeterminate"
                                @change="toggleSelectAll"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Start Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            End Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Room Type
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="blocker in dateBlockers.data" :key="blocker.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(blocker.id)"
                                @change="toggleSelect(blocker.id)"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ moment(blocker.start_date).format('DD/MM/YYYY') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ moment(blocker.end_date).format('DD/MM/YYYY') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ blocker.room_type?.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                @click="editBlocker(blocker)"
                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteBlocker(blocker.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="dateBlockers.data.length === 0">
                        <td colspan="5" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">
                            No date blockers found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination
                v-if="dateBlockers.data.length > 0"
                :links="dateBlockers.links"
                :from="dateBlockers.from"
                :to="dateBlockers.to"
                :total="dateBlockers.total"
                :component-loading="true"
                @page-change="handlePageChange"
                :key="loadingKey"
            />
        </div>

        <!-- Add Date Blocker Modal -->
        <Modal :show="showAddModal" @close="closeAddModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Date Blocker</h2>
                <div v-if="addDateBlockerErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                    <button 
                        @click="addDateBlockerErrors = ''" 
                        class="absolute top-2 right-2 text-red-700 hover:text-red-900"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    {{ addDateBlockerErrors }}
                </div>
                <form @submit.prevent="submitBlocker">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="start_date"
                                v-model="form.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.start_date }"
                                required
                            />
                            <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ form.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="end_date"
                                v-model="form.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.end_date }"
                                required
                            />
                            <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ form.errors.end_date }}
                            </div>
                        </div>

                        <div>
                            <label for="room_type_id" class="block text-sm font-medium text-gray-700">Room Type</label>
                            <select
                                id="room_type_id"
                                v-model="form.room_type_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Room Type</option>
                                <option value="all">All Room Types</option>
                                <option v-for="(roomType, key) in packageUniqueRoomTypes" :value="key" :key="key">
                                    {{ roomType }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeAddModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="form.processing"
                        >
                            Add Date Blocker
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Date Blocker Modal -->
        <Modal :show="showEditModal" @close="closeEditModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Date Blocker</h2>
                <form @submit.prevent="updateBlocker">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                id="edit_start_date"
                                v-model="editForm.start_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editForm.errors.start_date }"
                                required
                            />
                            <div v-if="editForm.errors.start_date" class="mt-1 text-sm text-red-600">
                                {{ editForm.errors.start_date }}
                            </div>
                        </div>

                        <div>
                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                id="edit_end_date"
                                v-model="editForm.end_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': editForm.errors.end_date }"
                                required
                            />
                            <div v-if="editForm.errors.end_date" class="mt-1 text-sm text-red-600">
                                {{ editForm.errors.end_date }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                            :disabled="editForm.processing"
                        >
                            Update Date Blocker
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';
import LoadingComponent from '@/Components/LoadingComponent.vue';

const props = defineProps({
    packageId: {
        type: Number,
        required: true
    },
    package: {
        type: Object,
        required: true
    },
    packageUniqueRoomTypes: {
        type: Object,
        required: true
    }
});

const dateBlockers = ref({
    data: [],
    links: [],
    from: 0,
    to: 0,
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: 50
});

// Bulk selection state
const selectedIds = ref([]);

const isAllSelected = computed(() => {
    if (dateBlockers.value.data.length === 0) return false;
    return dateBlockers.value.data.every(blocker => selectedIds.value.includes(blocker.id));
});

const isIndeterminate = computed(() => {
    const selectedOnPage = dateBlockers.value.data.filter(blocker => selectedIds.value.includes(blocker.id));
    return selectedOnPage.length > 0 && selectedOnPage.length < dateBlockers.value.data.length;
});

const addDateBlockerErrors = ref('');
const loadingKey = ref(0);
const showAddModal = ref(false);
const showEditModal = ref(false);
const initialLoading = ref(true);

const form = useForm({
    package_id: props.packageId,
    start_date: '',
    end_date: '',
    room_type_id: ''
});

const editForm = useForm({
    id: null,
    start_date: '',
    end_date: ''
});

const fetchDateBlockers = async (page = 1) => {
    try {
        const response = await axios.get(route('date-blockers.index'), {
            params: { 
                package_id: props.packageId,
                page: page
            }
        });
        dateBlockers.value = response.data;
        initialLoading.value = false;
    } catch (error) {
        console.error('Error fetching date blockers:', error);
        initialLoading.value = false;
    }
};

const handlePageChange = async (page) => {
    try {
        await fetchDateBlockers(page);
        loadingKey.value++;
    } catch (error) {
        console.error('Error changing page:', error);
    }
};

const submitBlocker = () => {
    form.post(route('date-blockers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddModal();
            fetchDateBlockers(dateBlockers.value.current_page);
            Swal.fire({
                title: 'Success!',
                text: form.recentlySuccessful ? 'Date blocker added successfully.' : 'Operation successful.',
                icon: 'success',
                confirmButtonColor: '#4F46E5'
            });
        },
        onError: (errors) => {
            // Handle all possible error cases
            if (errors.room_type_id) {
                addDateBlockerErrors.value = Array.isArray(errors.room_type_id) 
                    ? errors.room_type_id.join(', ') 
                    : errors.room_type_id;
            } else if (errors.date_range) {
                addDateBlockerErrors.value = Array.isArray(errors.date_range)
                    ? errors.date_range.join(', ')
                    : errors.date_range;
            } else if (errors.start_date) {
                addDateBlockerErrors.value = Array.isArray(errors.start_date)
                    ? errors.start_date.join(', ')
                    : errors.start_date;
            } else if (errors.end_date) {
                addDateBlockerErrors.value = Array.isArray(errors.end_date)
                    ? errors.end_date.join(', ')
                    : errors.end_date;
            } else if (errors.error) {
                addDateBlockerErrors.value = Array.isArray(errors.error)
                    ? errors.error.join(', ')
                    : errors.error;
            } else {
                // Handle any other unexpected errors
                addDateBlockerErrors.value = 'An unexpected error occurred. Please try again.';
            }
        }
    });
};

const updateBlocker = () => {
    editForm.put(route('date-blockers.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            fetchDateBlockers(dateBlockers.value.current_page);
            Swal.fire({
                title: 'Success!',
                text: editForm.recentlySuccessful ? 'Date blocker updated successfully.' : 'Operation successful.',
                icon: 'success',
                confirmButtonColor: '#4F46E5'
            });
        },
        onError: (errors) => {
            if (errors.date_range) {
                Swal.fire({
                    title: 'Error!',
                    text: errors.date_range,
                    icon: 'error',
                    confirmButtonColor: '#4F46E5'
                });
            } else if (errors.error) {
                Swal.fire({
                    title: 'Error!',
                    text: errors.error,
                    icon: 'error',
                    confirmButtonColor: '#4F46E5'
                });
            }
        }
    });
};

const deleteBlocker = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(route('date-blockers.destroy', id));
            // If we're on the last page and it's the only item, go to previous page
            if (dateBlockers.value.data.length === 1 && dateBlockers.value.current_page > 1) {
                await fetchDateBlockers(dateBlockers.value.current_page - 1);
            } else {
                await fetchDateBlockers(dateBlockers.value.current_page);
            }
            Swal.fire({
                title: 'Deleted!',
                text: 'Date blocker has been deleted.',
                icon: 'success',
                confirmButtonColor: '#4F46E5'
            });
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Error deleting date blocker';
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#4F46E5'
            });
        }
    }
};

// Bulk selection functions
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        // Deselect all on current page
        const currentPageIds = dateBlockers.value.data.map(b => b.id);
        selectedIds.value = selectedIds.value.filter(id => !currentPageIds.includes(id));
    } else {
        // Select all on current page
        const currentPageIds = dateBlockers.value.data.map(b => b.id);
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

const bulkDeleteBlockers = async () => {
    if (selectedIds.value.length === 0) return;

    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete ${selectedIds.value.length} date blocker(s). This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete them!',
        showConfirmButton: true,
        showCloseButton: true
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.delete(route('date-blockers.destroy-bulk'), {
                data: {
                    ids: selectedIds.value,
                    package_id: props.packageId
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
            await fetchDateBlockers(1);
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || 'Failed to delete date blockers',
                icon: 'error',
                confirmButtonColor: '#4F46E5'
            });
        }
    }
};

const editBlocker = (blocker) => {
    editForm.id = blocker.id;
    editForm.start_date = moment(blocker.start_date).format('YYYY-MM-DD');
    editForm.end_date = moment(blocker.end_date).format('YYYY-MM-DD');
    showEditModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    form.reset();
    form.clearErrors();
    addDateBlockerErrors.value = ''; // Clear the error message when closing modal
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    editForm.clearErrors();
};

onMounted(() => {
    fetchDateBlockers();
});
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
