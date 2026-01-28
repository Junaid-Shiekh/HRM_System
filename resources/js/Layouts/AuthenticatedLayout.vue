<script setup>
import { ref } from 'vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppTopbar from '@/Components/AppTopbar.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex font-sans text-gray-900 antialiased">
        <!-- Sidebar -->
        <AppSidebar />

        <!-- Main Content -->
        <div class="flex-1 ml-64 flex flex-col min-h-screen transition-all duration-200">
            <AppTopbar />
            
            <main class="flex-1 p-6 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
