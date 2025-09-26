<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-md font-medium text-gray-900">Package Add-ons</h3>
            <button
                @click="showAddModal = true"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
            >
                Add Package Add-on
            </button>
        </div>
        
        <!-- Package Add-ons Table -->
        <div class="overflow-x-auto">
            <div v-if="initialLoading" class="flex justify-center items-center h-full min-h-[400px]">
                <LoadingComponent />
            </div>
            <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Adult Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Child Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Infant Price
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="addOn in packageAddOns.data" :key="addOn.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ addOn.name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" :title="addOn.description">
                                {{ addOn.description || 'No description' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span v-if="addOn.adult_price" class="font-medium">MYR {{ format(addOn.adult_price) }}</span>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span v-if="addOn.child_price" class="font-medium">MYR {{ format(addOn.child_price) }}</span>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span v-if="addOn.infant_price" class="font-medium">MYR {{ format(addOn.infant_price) }}</span>
                            <span v-else class="text-gray-400">-</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                @click="editAddOn(addOn)"
                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteAddOn(addOn.id)"
                                class="text-red-600 hover:text-red-900"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="packageAddOns.data.length === 0">
                        <td colspan="6" class="text-center text-gray-500 py-4 border-t border-b border-gray-300">
                            No package add-ons found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            <Pagination 
            v-if="packageAddOns.data.length > 0"
                :links="packageAddOns.links"
                :from="packageAddOns.from"
                :to="packageAddOns.to"
                :total="packageAddOns.total"
                :component-loading="true"
                @page-change="handlePageChange"
                :key="loadingKey"
            />
        </div>

        <!-- Add Package Add-on Modal -->
        <Modal :show="showAddModal" @close="closeAddModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Package Add-on</h2>
                <div v-if="addAddOnErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                    <button 
                        @click="addAddOnErrors = ''" 
                        class="absolute top-2 right-2 text-red-700 hover:text-red-900"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    {{ addAddOnErrors }}
                </div>
                <form @submit.prevent="submitAddOn">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                            <input
                                type="text"
                                id="name"
                                v-model="addOnForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="description"
                                v-model="addOnForm.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="adult_price" class="block text-sm font-medium text-gray-700">Adult Price (MYR)</label>
                                <input
                                    type="number"
                                    id="adult_price"
                                    v-model.number="addOnForm.adult_price"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label for="child_price" class="block text-sm font-medium text-gray-700">Child Price (MYR)</label>
                                <input
                                    type="number"
                                    id="child_price"
                                    v-model.number="addOnForm.child_price"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label for="infant_price" class="block text-sm font-medium text-gray-700">Infant Price (MYR)</label>
                                <input
                                    type="number"
                                    id="infant_price"
                                    v-model.number="addOnForm.infant_price"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeAddModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Adding...' : 'Add Add-on' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Package Add-on Modal -->
        <Modal :show="showEditModal" @close="closeEditModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Package Add-on</h2>
                <div v-if="editAddOnErrors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                    <button 
                        @click="editAddOnErrors = ''" 
                        class="absolute top-2 right-2 text-red-700 hover:text-red-900"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    {{ editAddOnErrors }}
                </div>
                <form @submit.prevent="submitEditAddOn">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700">Name *</label>
                            <input
                                type="text"
                                id="edit_name"
                                v-model="editAddOnForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="edit_description"
                                v-model="editAddOnForm.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="edit_adult_price" class="block text-sm font-medium text-gray-700">Adult Price (MYR)</label>
                                <input
                                    type="number"
                                    id="edit_adult_price"
                                    v-model.number="editAddOnForm.adult_price"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label for="edit_child_price" class="block text-sm font-medium text-gray-700">Child Price (MYR)</label>
                                <input
                                    type="number"
                                    id="edit_child_price"
                                    v-model.number="editAddOnForm.child_price"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label for="edit_infant_price" class="block text-sm font-medium text-gray-700">Infant Price (MYR)</label>
                                <input
                                    type="number"
                                    id="edit_infant_price"
                                    v-model.number="editAddOnForm.infant_price"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Updating...' : 'Update Add-on' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';
import LoadingComponent from '@/Components/LoadingComponent.vue';
import Pagination from '@/Components/Pagination.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    packageId: {
        type: Number,
        required: true
    }
});

// Reactive data
const packageAddOns = ref({ data: [], links: [], from: null, to: null, total: 0 });
const initialLoading = ref(true);
const loadingKey = ref(0);
const showAddModal = ref(false);
const showEditModal = ref(false);
const isSubmitting = ref(false);
const addAddOnErrors = ref('');
const editAddOnErrors = ref('');

// Form data
const addOnForm = ref({
    name: '',
    description: '',
    adult_price: null,
    child_price: null,
    infant_price: null
});

const editAddOnForm = ref({
    id: null,
    name: '',
    description: '',
    adult_price: null,
    child_price: null,
    infant_price: null
});

// Methods
const fetchPackageAddOns = async (page = 1) => {
    try {
        const response = await axios.get(route('package-add-ons.index', props.packageId), {
            params: { 
                page: page
            }
        });
        packageAddOns.value = response.data;
        initialLoading.value = false;
    } catch (error) {
        console.error('Error fetching date blockers:', error);
        initialLoading.value = false;
    }
};

const handlePageChange = (page) => {
    try {
        fetchPackageAddOns(page);
        loadingKey.value++;
    } catch (error) {
        console.error('Error changing page:', error);
    }
};

const format = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toFixed(2);
};

const closeAddModal = () => {
    showAddModal.value = false;
    addOnForm.value = {
        name: '',
        description: '',
        adult_price: null,
        child_price: null,
        infant_price: null
    };
    addAddOnErrors.value = '';
};

const closeEditModal = () => {
    showEditModal.value = false;
    editAddOnForm.value = {
        id: null,
        name: '',
        description: '',
        adult_price: null,
        child_price: null,
        infant_price: null
    };
    editAddOnErrors.value = '';
};

const submitAddOn = async () => {
    isSubmitting.value = true;
    addAddOnErrors.value = '';

    try {
        const response = await fetch(route('package-add-ons.store', props.packageId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(addOnForm.value)
        });

        if (response.ok) {
            closeAddModal();
            await Swal.fire(
                'Success!',
                'Package add-on has been created successfully.',
                'success'
            );
            fetchPackageAddOns();
        } else {
            const errorData = await response.json();
            addAddOnErrors.value = errorData.message || 'An error occurred while creating the add-on.';
        }
    } catch (error) {
        addAddOnErrors.value = 'An error occurred while creating the add-on.';
    } finally {
        isSubmitting.value = false;
    }
};

const editAddOn = (addOn) => {
    editAddOnForm.value = {
        id: addOn.id,
        name: addOn.name,
        description: addOn.description || '',
        adult_price: addOn.adult_price,
        child_price: addOn.child_price,
        infant_price: addOn.infant_price
    };
    showEditModal.value = true;
};

const submitEditAddOn = async () => {
    isSubmitting.value = true;
    editAddOnErrors.value = '';

    try {
        const response = await fetch(route('package-add-ons.update', editAddOnForm.value.id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name: editAddOnForm.value.name,
                description: editAddOnForm.value.description,
                adult_price: editAddOnForm.value.adult_price,
                child_price: editAddOnForm.value.child_price,
                infant_price: editAddOnForm.value.infant_price
            })
        });

        if (response.ok) {
            closeEditModal();
            await Swal.fire(
                'Success!',
                'Package add-on has been updated successfully.',
                'success'
            );
            fetchPackageAddOns();
        } else {
            const errorData = await response.json();
            editAddOnErrors.value = errorData.message || 'An error occurred while updating the add-on.';
        }
    } catch (error) {
        editAddOnErrors.value = 'An error occurred while updating the add-on.';
    } finally {
        isSubmitting.value = false;
    }
};

const deleteAddOn = async (addOnId) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(route('package-add-ons.destroy', addOnId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                await Swal.fire(
                    'Deleted!',
                    'Package add-on has been deleted.',
                    'success'
                );
                fetchPackageAddOns();
            } else {
                await Swal.fire(
                    'Error!',
                    'Failed to delete package add-on.',
                    'error'
                );
            }
        } catch (error) {
            await Swal.fire(
                'Error!',
                'An error occurred while deleting the add-on.',
                'error'
            );
        }
    }
};

// Lifecycle
onMounted(() => {
    fetchPackageAddOns();
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