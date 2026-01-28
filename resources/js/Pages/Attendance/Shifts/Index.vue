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
    shifts: Array
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
    start_time: '09:00',
    end_time: '18:00',
    break_duration: 60,
    grace_period: 15,
    type: 'day',
    status: 'active'
});

const shiftTypes = [
    { label: 'Day', value: 'day' },
    { label: 'Night', value: 'night' }
];

const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
];

const handleSearch = () => {
    // Logic for search
};

const handleFilter = () => {
    // Logic for filter
};

const openNew = () => {
    form.reset();
    form.clearErrors();
    isEdit.value = false;
    showAddDialog.value = true;
};

const editShift = (shift) => {
    form.clearErrors();
    form.id = shift.id;
    form.name = shift.name;
    form.start_time = shift.start_time;
    form.end_time = shift.end_time;
    form.break_duration = shift.break_duration;
    form.grace_period = shift.grace_period;
    form.type = shift.type;
    form.status = shift.status;

    isEdit.value = true;
    showAddDialog.value = true;
};

const saveShift = () => {
    if (isEdit.value) {
        form.put(route('attendance-shifts.update', form.id), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Shift has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('attendance-shifts.store'), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Shift has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteShift = (shift) => {
    form.id = shift.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${shift.name}?`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteShift = () => {
    form.delete(route('attendance-shifts.destroy', form.id), {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Shift has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteShift();
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'danger';
        default: return 'info';
    }
};

const getTypeSeverity = (type) => {
    switch (type) {
        case 'day': return 'info';
        case 'night': return 'warning';
        default: return 'secondary';
    }
};
</script>

<template>

    <Head title="Shift Management" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center px-4 py-2">
                <h2 class="text-2xl font-bold text-gray-800">Shifts</h2>
                <Button label="Add Shift" icon="pi pi-plus" @click="openNew"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <SearchFilter v-model="searchTerm" v-model:perPage="perPage" @search="handleSearch"
                        @filter="handleFilter" />

                    <DataTable :value="shifts" responsiveLayout="scroll" class="p-datatable-sm" :paginator="true"
                        :rows="perPage">
                        <Column field="id" header="#" headerStyle="width: 3em"></Column>
                        <Column field="name" header="Shift Name" sortable>
                            <template #body="{ data }">
                                <span class="font-bold text-gray-800">{{ data.name }}</span>
                            </template>
                        </Column>
                        <Column field="start_time" header="Start Time" sortable></Column>
                        <Column field="end_time" header="End Time" sortable></Column>
                        <Column field="break_duration" header="Break (mins)" sortable></Column>
                        <Column header="Working Hours">
                            <template #body="{ data }">
                                <span class="text-emerald-600 font-bold">8.0h</span>
                            </template>
                        </Column>
                        <Column field="grace_period" header="Grace (mins)"></Column>
                        <Column field="type" header="Type">
                            <template #body="{ data }">
                                <Tag :value="data.type.toUpperCase()" :severity="getTypeSeverity(data.type)" />
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
                                        rounded aria-label="Edit" @click="editShift(data)">
                                        <i class="pi pi-pencil"></i>
                                    </Button>
                                    <Button
                                        class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        rounded aria-label="Delete" @click="confirmDeleteShift(data)">
                                        <i class="pi pi-trash"></i>
                                    </Button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Create/Edit Shift Dialog -->
        <Dialog v-model:visible="showAddDialog" :header="isEdit ? 'Edit Shift' : 'Add New Shift'"
            :style="{ width: '500px' }" modal class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Shift Name</label>
                    <InputText v-model="form.name" placeholder="e.g. Morning Shift" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Start Time</label>
                        <InputText v-model="form.start_time" type="time" />
                        <small class="text-red-500" v-if="form.errors.start_time">{{ form.errors.start_time }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">End Time</label>
                        <InputText v-model="form.end_time" type="time" />
                        <small class="text-red-500" v-if="form.errors.end_time">{{ form.errors.end_time }}</small>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Break (mins)</label>
                        <InputText v-model="form.break_duration" type="number" />
                        <small class="text-red-500" v-if="form.errors.break_duration">{{ form.errors.break_duration
                            }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Grace (mins)</label>
                        <InputText v-model="form.grace_period" type="number" />
                        <small class="text-red-500" v-if="form.errors.grace_period">{{ form.errors.grace_period
                            }}</small>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Shift Type</label>
                        <Dropdown v-model="form.type" :options="shiftTypes" optionLabel="label" optionValue="value" />
                        <small class="text-red-500" v-if="form.errors.type">{{ form.errors.type }}</small>
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
                    <Button label="Save Shift" icon="pi pi-check" @click="saveShift" :loading="form.processing"
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

.p-datatable .p-datatable-tbody>tr>td {
    padding: 1rem;
    border-bottom: 1px solid #f9fafb;
    font-size: 13px;
}
</style>
