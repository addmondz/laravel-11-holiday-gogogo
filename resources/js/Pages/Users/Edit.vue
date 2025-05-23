<template>
    <Head title="Edit User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password" value="New Password (leave blank to keep current)" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('users.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3 text-xs"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    :disabled="form.processing"
                                >
                                    Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    user: Object
});

const breadcrumbs = computed(() => [
    { title: 'Users', link: route('users.index') },
	{ title: 'Edit User', },
]);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

const passwordRequirements = computed(() => ({
    minLength: form.password.length >= 8,
    hasNumber: /\d/.test(form.password),
    hasUpperCase: /[A-Z]/.test(form.password),
    hasLowerCase: /[a-z]/.test(form.password),
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(form.password),
    matches: form.password === form.password_confirmation && form.password !== '',
}));

const isPasswordValid = computed(() => {
    // If password is empty, it's valid (optional in edit)
    if (!form.password && !form.password_confirmation) {
        return true;
    }
    // If password is being changed, validate all requirements
    return Object.values(passwordRequirements.value).every(Boolean);
});

const submit = () => {
    if (!isPasswordValid.value) {
        return;
    }
    
    form.put(route('users.update', props.user.id), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>
