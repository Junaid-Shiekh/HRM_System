<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import SweetAlert from '@/Components/SweetAlert.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    policies: Array
});

const searchTerm = ref('');
const perPage = ref(10);
const showAddDialog = ref(false);
const isEdit = ref(false);

// SweetAlert state
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const form = useForm({
    id: null,
    name: '',
    late_grace_period: 15,
    early_leave_grace_period: 15,
    overtime_rate: 150.00,
    status: 'active'
});

const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
];

const handleSearch = () => { };
const handleFilter = () => { };

const openNew = () => {
    form.reset();
    form.clearErrors();
    isEdit.value = false;
    showAddDialog.value = true;
};

const editPolicy = (policy) => {
    form.clearErrors();
    form.id = policy.id;
    form.name = policy.name;
    form.late_grace_period = policy.late_grace_period;
    form.early_leave_grace_period = policy.early_leave_grace_period;
    form.overtime_rate = policy.overtime_rate;
    form.status = policy.status;

    isEdit.value = true;
    showAddDialog.value = true;
};

const savePolicy = () => {
    if (isEdit.value) {
        form.put(route('attendance-policies.update', form.id), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Policy has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('attendance-policies.store'), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Policy has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeletePolicy = (policy) => {
    form.id = policy.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${policy.name}?`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deletePolicy = () => {
    form.delete(route('attendance-policies.destroy', form.id), {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Policy has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deletePolicy();
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'danger';
        default: return 'info';
    }
};
</script>

<template>

    <Head title="Attendance Policy Management" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="mb-6 flex justify-between items-center px-4">
                <h2 class="text-2xl font-bold text-gray-800">Attendance Policies</h2>
                <Button label="Add Attendance Policy" icon="pi pi-plus" @click="openNew"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <SearchFilter v-model="searchTerm" v-model:perPage="perPage" @search="handleSearch"
                        @filter="handleFilter" />

                    <DataTable :value="policies" responsiveLayout="scroll" class="p-datatable-sm" :paginator="true"
                        :rows="perPage">
                        <Column field="id" header="#" headerStyle="width: 3em"></Column>
                        <Column field="name" header="Policy Name" sortable>
                            <template #body="{ data }">
                                <span class="font-bold text-gray-800">{{ data.name }}</span>
                            </template>
                        </Column>
                        <Column field="late_grace_period" header="Late Grace (mins)" sortable>
                            <template #body="{ data }">
                                <span class="text-orange-600 font-bold">{{ data.late_grace_period }}</span>
                            </template>
                        </Column>
                        <Column field="early_leave_grace_period" header="Early Grace (mins)" sortable>
                            <template #body="{ data }">
                                <span class="text-blue-600 font-bold">{{ data.early_leave_grace_period }}</span>
                            </template>
                        </Column>
                        <Column field="overtime_rate" header="Overtime Rate" sortable>
                            <template #body="{ data }">
                                <span class="text-emerald-600 font-bold">${{ data.overtime_rate }}/hr</span>
                            </template>
                        </Column>
                        <Column field="status" header="Status">
                            <template #body="{ data }">
                                <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
                            </template>
                        </Column>
                        <Column header="Actions" alignFrozen="right" frozen>
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <Button
                                        class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        rounded aria-label="View">
                                        <i class="pi pi-eye"></i>
                                    </Button>
                                    <Button
                                        class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        rounded aria-label="Edit" @click="editPolicy(data)">
                                        <i class="pi pi-pencil"></i>
                                    </Button>
                                    <Button
                                        class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        rounded aria-label="Delete" @click="confirmDeletePolicy(data)">
                                        <i class="pi pi-trash"></i>
                                    </Button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Create/Edit Policy Dialog -->
        <Dialog v-model:visible="showAddDialog"
            :header="isEdit ? 'Edit Attendance Policy' : 'Add New Attendance Policy'" :style="{ width: '500px' }" modal
            class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Policy Name</label>
                    <InputText v-model="form.name" placeholder="e.g. Standard Attendance Policy" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Late Grace (mins)</label>
                        <InputText v-model="form.late_grace_period" type="number" />
                        <small class="text-red-500" v-if="form.errors.late_grace_period">{{
                            form.errors.late_grace_period
                            }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Early Grace (mins)</label>
                        <InputText v-model="form.early_leave_grace_period" type="number" />
                        <small class="text-red-500" v-if="form.errors.early_leave_grace_period">{{
                            form.errors.early_leave_grace_period }}</small>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Overtime Rate ($/hr)</label>
                        <InputText v-model="form.overtime_rate" type="number" step="0.01" />
                        <small class="text-red-500" v-if="form.errors.overtime_rate">{{ form.errors.overtime_rate
                            }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Status</label>
                        <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label"
                            optionValue="value" />
                        <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 mt-4">
                    <Button label="Cancel" icon="pi pi-times" @click="showAddDialog = false"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                    <Button label="Save Policy" icon="pi pi-check" @click="savePolicy" :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
                </div>
            </template>
        </Dialog>

        <!-- SweetAlert -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>

<style>
.p-datatable .p-datatable-thead>tr>th {
    background: #f9fafb;
    color: #374151;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}
</style>
