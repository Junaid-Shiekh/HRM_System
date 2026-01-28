<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-30">
        <!-- Left: Breadcrumb / Title -->
        <div class="flex items-center">
            <!-- Mobile Menu Toggle could go here -->
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">Dashboard</h2>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center space-x-4">
             <!-- Language -->
             <button class="flex items-center text-gray-500 hover:text-gray-700">
                <i class="pi pi-globe mr-2"></i>
                <span class="text-sm font-medium">English</span>
             </button>

             <!-- Profile -->
             <div class="relative ml-3">
                 <div class="flex items-center cursor-pointer">
                     <div class="text-right mr-3 hidden sm:block">
                         <div class="text-sm font-medium text-gray-800">{{ user.name }}</div>
                         <div class="text-xs text-gray-500">Company Admin</div>
                     </div>
                     <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold">
                         {{ user.name ? user.name.charAt(0) : 'U' }}
                     </div>
                 </div>
             </div>
        </div>
    </header>
</template>
