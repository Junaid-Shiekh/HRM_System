<script setup>
import { ref, watch } from 'vue';
import Sidebar from 'primevue/sidebar';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';

const props = defineProps({
    visible: Boolean,
    config: {
        type: Array,
        required: true,
        // config: [{ name: 'status', label: 'Status', type: 'dropdown', options: [], placeholder: '' }]
    },
    modelValue: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:visible', 'update:modelValue', 'apply', 'reset']);

const localFilters = ref({ ...props.modelValue });

watch(() => props.modelValue, (newVal) => {
    localFilters.value = { ...newVal };
}, { deep: true });

const applyFilters = () => {
    emit('update:modelValue', localFilters.value);
    emit('apply', localFilters.value);
    emit('update:visible', false);
};

const resetFilters = () => {
    Object.keys(localFilters.value).forEach(key => {
        localFilters.value[key] = null;
    });
    emit('update:modelValue', localFilters.value);
    emit('reset');
    emit('update:visible', false);
};
</script>

<template>
    <Sidebar :visible="visible" @update:visible="$emit('update:visible', $event)" position="right" class="!w-[350px]">
        <template #header>
            <div class="flex items-center gap-2">
                <i class="pi pi-filter text-xl text-[#1C0D82]"></i>
                <span class="font-bold text-xl">Advanced Filters</span>
            </div>
        </template>

        <div class="flex flex-col gap-6 py-4">
            <div v-for="field in config" :key="field.name" class="flex flex-col gap-2">
                <label :for="field.name" class="font-semibold text-gray-700">{{ field.label }}</label>

                <template v-if="field.type === 'dropdown'">
                    <Dropdown v-model="localFilters[field.name]" :options="field.options"
                        :optionLabel="field.optionLabel || 'label'" :optionValue="field.optionValue || 'value'"
                        :placeholder="field.placeholder || 'Select ' + field.label"
                        class="w-full !rounded-lg !border-gray-200" showClear />
                </template>

                <template v-else-if="field.type === 'text'">
                    <InputText v-model="localFilters[field.name]"
                        :placeholder="field.placeholder || 'Enter ' + field.label"
                        class="w-full !rounded-lg !border-gray-200 !py-2.5" />
                </template>
            </div>
        </div>

        <template #footer>
            <div class="flex gap-3 pt-4 border-t">
                <Button label="Reset" icon="pi pi-refresh" @click="resetFilters"
                    class="flex-1 bg-gray-100 text-gray-700 hover:bg-gray-200 !border-none !py-2.5 !rounded-lg" />
                <Button label="Apply" icon="pi pi-check" @click="applyFilters"
                    class="flex-1 !bg-[#1C0D82] hover:!bg-[#150a61] text-white !border-none !py-2.5 !rounded-lg" />
            </div>
        </template>
    </Sidebar>
</template>

<style>
.p-sidebar-header {
    padding: 1.5rem !important;
}

.p-sidebar-content {
    padding: 0 1.5rem 1.5rem 1.5rem !important;
}

.p-sidebar-footer {
    padding: 1.5rem !important;
}
</style>
