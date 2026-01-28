<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const loginType = ref('hr');

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    login_type: loginType.value,
});

import { watch } from 'vue';

watch(loginType, (newType) => {
    form.login_type = newType;
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div class="mb-10">
            <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
            <p class="mt-2 text-sm text-indigo-200/60 font-light">
                {{ loginType === 'hr' ? 'Management Workspace' : 'Employee Portal' }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-8">
            <button type="button" @click="loginType = 'hr'" :class="[
                'py-3 rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2',
                loginType === 'hr'
                    ? 'bg-indigo-600/20 border border-indigo-500 text-indigo-100 shadow-[0_0_20px_rgba(79,70,229,0.3)]'
                    : 'bg-[#232442] border border-transparent text-indigo-300/40 hover:bg-[#2A2B4E]'
            ]">
                <i class="pi pi-user-plus text-xs"></i>
                Login as HR
            </button>
            <button type="button" @click="loginType = 'employee'" :class="[
                'py-3 rounded-xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2',
                loginType === 'employee'
                    ? 'bg-violet-600/20 border border-violet-500 text-violet-100 shadow-[0_0_20px_rgba(139,92,246,0.3)]'
                    : 'bg-[#232442] border border-transparent text-violet-300/40 hover:bg-[#2A2B4E]'
            ]">
                <i class="pi pi-users text-xs"></i>
                Login as Employee
            </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="text-xs font-semibold uppercase tracking-wider text-indigo-200/50">Workspace
                    Email</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300/40 group-focus-within:text-indigo-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input id="email" type="email"
                        class="block w-full pl-11 pr-4 py-3.5 bg-[#232442] border border-transparent rounded-xl text-indigo-100 placeholder-indigo-400/30 focus:border-indigo-500/50 focus:bg-[#2A2B4E] focus:ring-0 transition-all duration-200 text-sm"
                        v-model="form.email" required autofocus autocomplete="username"
                        placeholder="name@company.com" />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="password"
                        class="text-xs font-semibold uppercase tracking-wider text-indigo-200/50">Password</label>
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-xs font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                        Forgot?
                    </Link>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-indigo-300/40 group-focus-within:text-indigo-400 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" type="password"
                        class="block w-full pl-11 pr-4 py-3.5 bg-[#232442] border border-transparent rounded-xl text-indigo-100 placeholder-indigo-400/30 focus:border-indigo-500/50 focus:bg-[#2A2B4E] focus:ring-0 transition-all duration-200 text-sm"
                        v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <label class="flex items-center cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" v-model="form.remember" class="sr-only" />
                        <div
                            class="block bg-[#232442] w-5 h-5 rounded border border-indigo-500/30 group-hover:border-indigo-500/60 transition-colors">
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center text-indigo-400 opacity-0 transition-opacity"
                            :class="{ 'opacity-100': form.remember }">
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
                            </svg>
                        </div>
                    </div>
                    <span
                        class="ml-3 text-sm text-indigo-200/60 group-hover:text-indigo-200/80 transition-colors">Remember
                        this device for 30 days</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-[#1A1B3A] transition-all duration-200 transform active:scale-[0.98]"
                :class="{ 'opacity-75 cursor-not-allowed': form.processing }" :disabled="form.processing">
                {{ loginType === 'hr' ? 'Sign in to HRM' : 'Access Employee Portal' }}
                <svg class="ml-2 -mr-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </button>

            <!-- Register Link -->
            <div v-if="loginType === 'hr'" class="text-center pt-4">
                <p class="text-sm text-indigo-200/40">
                    First time here?
                    <Link :href="route('register')"
                        class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors underline-offset-4 hover:underline">
                        Request access
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
