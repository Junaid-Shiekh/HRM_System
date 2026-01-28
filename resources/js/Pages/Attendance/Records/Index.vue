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
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import SweetAlert from '@/Components/SweetAlert.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    records: Array,
    employees: Array,
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
    employee_id: null,
    shift_id: null,
    date: new Date().toISOString().substr(0, 10),
    clock_in: '09:00',
    clock_out: '18:00',
    status: 'present',
    note: ''
});

const statusOptions = [
    { label: 'Present', value: 'present' },
    { label: 'Absent', value: 'absent' },
    { label: 'Late', value: 'late' },
    { label: 'Early Leave', value: 'early_leave' },
    { label: 'On Leave', value: 'on_leave' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    isEdit.value = false;
    showAddDialog.value = true;
};

const editRecord = (record) => {
    form.clearErrors();
    form.id = record.id;
    form.employee_id = record.employee_id;
    form.shift_id = record.shift_id;
    form.date = record.date;
    form.clock_in = record.clock_in;
    form.clock_out = record.clock_out;
    form.status = record.status;
    form.note = record.note;

    isEdit.value = true;
    showAddDialog.value = true;
};

const saveRecord = () => {
    if (isEdit.value) {
        form.put(route('attendance-records.update', form.id), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Attendance record has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('attendance-records.store'), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Attendance record has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteRecord = (record) => {
    form.id = record.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete this record?`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteRecord = () => {
    form.delete(route('attendance-records.destroy', form.id), {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Attendance record has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteRecord();
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'present': return 'success';
        case 'absent': return 'danger';
        case 'late': return 'warning';
        case 'early_leave': return 'info';
        case 'on_leave': return 'primary';
        default: return 'secondary';
    }
};

const formatStatus = (status) => {
    return status.replace('_', ' ').toUpperCase();
};
</script>

<template>

    <Head title="Attendance Record Management" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="mb-6 flex justify-between items-center px-4">
                <h2 class="text-2xl font-bold text-gray-800">Attendance Records</h2>
                <Button label="Add Record" icon="pi pi-plus" @click="openNew"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <SearchFilter v-model="searchTerm" v-model:perPage="perPage" @search="handleSearch"
                        @filter="handleFilter" />

                    <DataTable :value="records" responsiveLayout="scroll" class="p-datatable-sm" :paginator="true"
                        :rows="perPage">
                        <Column field="id" header="#" headerStyle="width: 3em"></Column>
                        <Column field="employee.name" header="Employee" sortable>
                            <template #body="{ data }">
                                <span class="font-bold text-gray-800">{{ data.employee?.name }}</span>
                            </template>
                        </Column>
                        <Column field="date" header="Date" sortable>
                            <template #body="{ data }">
                                <span class="text-gray-600">{{ data.date }}</span>
                            </template>
                        </Column>
                        <Column field="shift.name" header="Shift">
                            <template #body="{ data }">
                                {{ data.shift?.name || '-' }}
                            </template>
                        </Column>
                        <Column field="clock_in" header="Clock In">
                            <template #body="{ data }">
                                <span v-if="data.clock_in" class="text-emerald-600 font-bold">{{ data.clock_in }}</span>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column field="clock_out" header="Clock Out">
                            <template #body="{ data }">
                                <span v-if="data.clock_out" class="text-rose-600 font-bold">{{ data.clock_out }}</span>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column field="total_hours" header="Total Hours">
                            <template #body="{ data }">
                                <span class="font-bold">{{ data.total_hours }}h</span>
                            </template>
                        </Column>
                        <Column field="overtime_hours" header="Overtime">
                            <template #body="{ data }">
                                <span class="text-blue-600 font-bold">{{ data.overtime_hours }}h</span>
                            </template>
                        </Column>
                        <Column field="status" header="Status">
                            <template #body="{ data }">
                                <div class="flex flex-wrap gap-1">
                                    <Tag :value="formatStatus(data.status)"
                                        :severity="getStatusSeverity(data.status)" />
                                    <Tag v-if="data.status === 'early_leave'" value="EARLY" severity="warning" />
                                </div>
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
                                        rounded aria-label="Edit" @click="editRecord(data)">
                                        <i class="pi pi-pencil"></i>
                                    </Button>
                                    <Button
                                        class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        rounded aria-label="Delete" @click="confirmDeleteRecord(data)">
                                        <i class="pi pi-trash"></i>
                                    </Button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Create/Edit Record Dialog -->
        <Dialog v-model:visible="showAddDialog"
            :header="isEdit ? 'Edit Attendance Record' : 'Add Manual Attendance Record'" :style="{ width: '500px' }"
            modal class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Employee</label>
                    <Dropdown v-model="form.employee_id" :options="employees" optionLabel="name" optionValue="id"
                        placeholder="Select Employee" filter class="w-full" />
                    <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Date</label>
                        <InputText v-model="form.date" type="date" />
                        <small class="text-red-500" v-if="form.errors.date">{{ form.errors.date }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Shift</label>
                        <Dropdown v-model="form.shift_id" :options="shifts" optionLabel="name" optionValue="id"
                            placeholder="Select Shift" />
                        <small class="text-red-500" v-if="form.errors.shift_id">{{ form.errors.shift_id }}</small>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Clock In</label>
                        <InputText v-model="form.clock_in" type="time" />
                        <small class="text-red-500" v-if="form.errors.clock_in">{{ form.errors.clock_in }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Clock Out</label>
                        <InputText v-model="form.clock_out" type="time" />
                        <small class="text-red-500" v-if="form.errors.clock_out">{{ form.errors.clock_out }}</small>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Status</label>
                    <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" />
                    <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Note (Optional)</label>
                    <InputText v-model="form.note" placeholder="Add any notes..." />
                    <small class="text-red-500" v-if="form.errors.note">{{ form.errors.note }}</small>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 mt-4">
                    <Button label="Cancel" icon="pi pi-times" @click="showAddDialog = false"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                    <Button label="Save Record" icon="pi pi-check" @click="saveRecord" :loading="form.processing"
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
