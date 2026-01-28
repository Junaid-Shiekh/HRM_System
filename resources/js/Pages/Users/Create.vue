<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Card from 'primevue/card';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    roles: []
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Banned', value: 'banned' }
];

const submit = () => {
    form.post(route('users.store'));
};
</script>

<template>
    <Head title="Add User" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Add New User</h2>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-6">
                    <!-- Name -->
                    <div class="flex flex-col gap-2">
                        <label for="name" class="font-medium text-gray-700">Name</label>
                        <InputText id="name" v-model="form.name" :invalid="form.errors.name" />
                        <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-2">
                        <label for="email" class="font-medium text-gray-700">Email</label>
                        <InputText id="email" v-model="form.email" :invalid="form.errors.email" />
                         <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col gap-2">
                        <label for="password" class="font-medium text-gray-700">Password</label>
                        <Password id="password" v-model="form.password" toggleMask :invalid="form.errors.password" />
                         <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="flex flex-col gap-2">
                        <label for="password_confirmation" class="font-medium text-gray-700">Confirm Password</label>
                        <Password id="password_confirmation" v-model="form.password_confirmation" toggleMask :invalid="form.errors.password_confirmation" :feedback="false" />
                         <small class="text-red-500" v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</small>
                    </div>

                    <!-- Status -->
                    <div class="flex flex-col gap-2">
                        <label for="status" class="font-medium text-gray-700">Status</label>
                        <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" placeholder="Select a Status" :invalid="form.errors.status" />
                         <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 mt-4">
                        <Button label="Cancel" severity="secondary" text @click="route('users.index')" />
                        <Button type="submit" label="Create User" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
