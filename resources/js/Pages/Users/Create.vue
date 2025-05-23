<template>
    <Head title="Create User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create User
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    :class="{ 'border-red-500': form.password && !isPasswordValid }"
                                />
                                <div class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">Password must contain:</p>
                                    <ul class="text-sm space-y-1">
                                        <li :class="{ 'text-green-600': passwordRequirements.minLength, 'text-red-600': !passwordRequirements.minLength }">
                                            • At least 8 characters
                                        </li>
                                        <li :class="{ 'text-green-600': passwordRequirements.hasNumber, 'text-red-600': !passwordRequirements.hasNumber }">
                                            • At least one number
                                        </li>
                                        <li :class="{ 'text-green-600': passwordRequirements.hasUpperCase, 'text-red-600': !passwordRequirements.hasUpperCase }">
                                            • At least one uppercase letter
                                        </li>
                                        <li :class="{ 'text-green-600': passwordRequirements.hasLowerCase, 'text-red-600': !passwordRequirements.hasLowerCase }">
                                            • At least one lowercase letter
                                        </li>
                                        <li :class="{ 'text-green-600': passwordRequirements.hasSpecialChar, 'text-red-600': !passwordRequirements.hasSpecialChar }">
                                            • At least one special character
                                        </li>
                                    </ul>
                                </div>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    :class="{ 'border-red-500': form.password_confirmation && !passwordRequirements.matches }"
                                />
                                <div v-if="form.password_confirmation" class="mt-2">
                                    <p :class="{ 'text-green-600': passwordRequirements.matches, 'text-red-600': !passwordRequirements.matches }" class="text-sm">
                                        {{ passwordRequirements.matches ? 'Passwords match' : 'Passwords do not match' }}
                                    </p>
                                </div>
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';
import Swal from 'sweetalert2';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const breadcrumbs = computed(() => [
    { title: 'Users', link: route('users.index') },
    { title: 'Create User' }
]);

const passwordRequirements = computed(() => ({
    minLength: form.password.length >= 8,
    hasNumber: /\d/.test(form.password),
    hasUpperCase: /[A-Z]/.test(form.password),
    hasLowerCase: /[a-z]/.test(form.password),
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(form.password),
    matches: form.password === form.password_confirmation && form.password !== '',
}));

const isPasswordValid = computed(() => {
    return Object.values(passwordRequirements.value).every(Boolean);
});

const submit = () => {
    if (!isPasswordValid.value) {
        const missingRequirements = Object.entries(passwordRequirements.value)
            .filter(([_, value]) => !value)
            .map(([key]) => {
                switch(key) {
                    case 'minLength': return 'At least 8 characters';
                    case 'hasNumber': return 'At least one number';
                    case 'hasUpperCase': return 'At least one uppercase letter';
                    case 'hasLowerCase': return 'At least one lowercase letter';
                    case 'hasSpecialChar': return 'At least one special character';
                    case 'matches': return 'Passwords do not match';
                    default: return '';
                }
            })
            .filter(Boolean);

        Swal.fire({
            title: 'Password Requirements Not Met',
            html: `
                <div class="text-left">
                    <p class="mb-2">Please ensure your password meets the following requirements:</p>
                    <ul class="list-disc pl-5 text-left">
                        ${missingRequirements.map(req => `<li class="text-red-600">${req}</li>`).join('')}
                    </ul>
                </div>
            `,
            icon: 'error',
            confirmButtonColor: '#3085d6',
        });
        return;
    }
    
    form.post(route('users.store'), {
        onSuccess: () => {
            Swal.fire({
                title: 'Success!',
                text: 'User has been created successfully.',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                form.reset();
            });
        },
        onError: (errors) => {
            console.log(errors);
            const errorMessages = Object.values(errors).join('<br>');
            Swal.fire({
                title: 'Error!',
                html: errorMessages,
                icon: 'error',
                confirmButtonColor: '#3085d6',
            });
        }
    });
};
</script>
