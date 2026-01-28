<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    components: Array
});

const componentDialog = ref(false);
const isEditing = ref(false);
const submitted = ref(false);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    id: null,
    name: '',
    type: 'allowance',
    amount_type: 'fixed',
    amount: 0,
    is_active: true
});

const types = [
    { label: 'Allowance', value: 'allowance' },
    { label: 'Deduction', value: 'deduction' }
];

const amountTypes = [
    { label: 'Fixed Amount', value: 'fixed' },
    { label: 'Percentage (%)', value: 'percent' }
];

const openNew = () => {
    form.reset();
    isEditing.value = false;
    componentDialog.value = true;
};

const editComponent = (comp) => {
    form.id = comp.id;
    form.name = comp.name;
    form.type = comp.type;
    form.amount_type = comp.amount_type;
    form.amount = parseFloat(comp.amount);
    form.is_active = comp.is_active;
    isEditing.value = true;
    componentDialog.value = true;
};

const saveComponent = () => {
    submitted.value = true;
    if (isEditing.value) {
        form.put(route('salary-components.update', form.id), {
            onSuccess: () => {
                componentDialog.value = false;
                showSuccess('Component updated successfully');
            }
        });
    } else {
        form.post(route('salary-components.store'), {
            onSuccess: () => {
                componentDialog.value = false;
                showSuccess('Component created successfully');
            }
        });
    }
};

const showSuccess = (msg) => {
    alertConfig.value = { title: 'Success', message: msg, type: 'success' };
    showAlert.value = true;
};
</script>

<template>

    <Head title="Salary Components" />
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Salary Components</h2>
            <Button label="Add Component" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="components" paginator :rows="10">
                <Column field="name" header="Name" sortable></Column>
                <Column field="type" header="Type" sortable>
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.type"
                            :severity="slotProps.data.type === 'allowance' ? 'success' : 'danger'" />
                    </template>
                </Column>
                <Column field="amount_type" header="Amount Type">
                    <template #body="slotProps">
                        {{ slotProps.data.amount_type === 'fixed' ? 'Fixed' : 'Percentage' }}
                    </template>
                </Column>
                <Column field="amount" header="Default Value">
                    <template #body="slotProps">
                        {{ slotProps.data.amount_type === 'fixed' ? '$' : '' }}{{ slotProps.data.amount }}{{
                            slotProps.data.amount_type === 'percent' ? '%' : '' }}
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit" @click="editComponent(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Add/Edit Dialog -->
        <Dialog v-model:visible="componentDialog" :header="isEditing ? 'Edit Component' : 'New Component'" modal
            class="p-fluid" :style="{ width: '450px' }">
            <div class="flex flex-col gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Name</label>
                    <InputText v-model="form.name" required autofocus :class="{ 'p-invalid': form.errors.name }" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Type</label>
                    <Dropdown v-model="form.type" :options="types" optionLabel="label" optionValue="value" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Amount Type</label>
                    <Dropdown v-model="form.amount_type" :options="amountTypes" optionLabel="label"
                        optionValue="value" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Default Amount/Percent</label>
                    <InputNumber v-model="form.amount" mode="decimal" :minFractionDigits="2" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="componentDialog = false" />
                <Button label="Save" icon="pi pi-check" @click="saveComponent" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>
