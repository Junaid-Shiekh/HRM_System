<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: 'Search...'
    },
    perPage: {
        type: Number,
        default: 10
    },
    showFilter: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'update:perPage', 'search', 'filter']);

const perPageOptions = [
    { label: '10', value: 10 },
    { label: '25', value: 25 },
    { label: '50', value: 50 },
    { label: '100', value: 100 }
];

const viewType = ref('list');

const handleSearch = () => {
    emit('search');
};

import { watch } from 'vue';
watch(() => props.modelValue, (newVal) => {
    if (!newVal || newVal.trim() === '') {
        handleSearch();
    }
});
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 flex-1 min-w-[300px]">
            <div class="relative flex-1 max-w-md">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <InputText :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
                    :placeholder="placeholder"
                    class="w-full !pl-10 !py-2.5 !rounded-lg !border-gray-200 focus:!ring-primary-500"
                    @keyup.enter="handleSearch" />
            </div>
            <Button label="Search" icon="pi pi-search" @click="handleSearch"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5 !rounded-lg flex items-center gap-2" />
            <Button v-if="showFilter" label="Filters" icon="pi pi-filter" @click="$emit('filter')"
                class="!bg-white !text-gray-700 !border-gray-200 hover:!bg-gray-50 !px-4 !py-2.5 !rounded-lg flex items-center gap-2 border" />
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center p-1 bg-gray-100 rounded-lg">
                <button @click="viewType = 'list'" class="p-2 rounded-md transition-all"
                    :class="viewType === 'list' ? 'bg-white text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                    <i class="pi pi-bars" />
                </button>
                <button @click="viewType = 'grid'" class="p-2 rounded-md transition-all"
                    :class="viewType === 'grid' ? 'bg-white text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                    <i class="pi pi-th-large" />
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 whitespace-nowrap">Per Page:</span>
                <Dropdown :modelValue="perPage" @update:modelValue="$emit('update:perPage', $event)"
                    :options="perPageOptions" optionLabel="label" optionValue="value"
                    class="!bg-white !border-gray-200 !rounded-lg !w-24 text-sm" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-primary {
    color: #1C0D82;
}
</style>
