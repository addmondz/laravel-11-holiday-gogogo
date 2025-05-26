<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-md font-medium text-gray-900">Date Blockers</h3>
            <button
                @click="showAddModal = true"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
            >
                Add Date Blocker
            </button>
        </div>

        <!-- Date Blockers Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Start Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            End Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="blocker in dateBlockers.data" :key="blocker.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ moment(blocker.start_date).format('DD/MM/YYYY') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ moment(blocker.end_date).format('DD/MM/YYYY') }}
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
                        <td colspan="3" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">
                            No date blockers found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination
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
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import moment from 'moment';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    packageId: {
        type: Number,
        required: true
    }
});

const dateBlockers = ref({
    data: [],
    links: [],
    from: null,
    to: null,
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: 10
});
const loadingKey = ref(0);
const showAddModal = ref(false);
const showEditModal = ref(false);

const form = useForm({
    package_id: props.packageId,
    start_date: '',
    end_date: ''
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
    } catch (error) {
        console.error('Error fetching date blockers:', error);
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
        confirmButtonColor: '#4F46E5',
        cancelButtonColor: '#EF4444',
        confirmButtonText: 'Yes, delete it!'
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
