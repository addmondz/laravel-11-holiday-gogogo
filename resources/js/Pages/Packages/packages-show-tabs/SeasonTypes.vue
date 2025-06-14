<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-md font-medium text-gray-900">Season Types</h3>
            <button
                @click="showAddSeasonTypeModal = true"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
            >
                Add Season Type
            </button>
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
                        <td class="px-6 py-4 text-sm text-gray-900">{{ moment(season.start_date).format('MM/DD/YYYY') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ moment(season.end_date).format('MM/DD/YYYY') }}</td>
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Season Type</h2>
                <div v-if="addSeasonTypeErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ addSeasonTypeErrors }}
                </div>
                <form @submit.prevent="submitSeasonType">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="name"
                                v-model="seasonTypeForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="description"
                                v-model="seasonTypeForm.description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
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
                            Create Season Type
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
const addSeasonTypeErrors = ref('');
const seasonTypeWarning = ref('');

const seasonsPagination = computed(() => ({
    links: seasonsData.value.links,
    from: seasonsData.value.from,
    to: seasonsData.value.to,
    total: seasonsData.value.total
}));

const seasonTypeForm = useForm({
    name: '',
    description: '',
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
    if (!seasonTypeForm.name?.trim()) {
        addSeasonTypeErrors.value = 'Season type name is required';
        return;
    }

    seasonTypeForm.post(route('season-types.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAddSeasonTypeModal.value = false;
            seasonTypeForm.reset();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Season type created successfully'
            });
            // handlePageChange(1);
            router.get(route('packages.show', props.package.id));
        },
        onError: (errors) => {
            if (errors.name) {
                addSeasonTypeErrors.value = errors.name;
            } else if (errors.description) {
                addSeasonTypeErrors.value = errors.description;
            } else if (errors.package_id) {
                addSeasonTypeErrors.value = errors.package_id;
            } else {
                addSeasonTypeErrors.value = 'Failed to create season type. Please try again.';
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
</script> 