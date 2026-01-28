<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    leaveApplications: Object,
    leaveTypes: Array,
    employees: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    leave_type_id: props.filters.leave_type_id || null,
    employee_id: props.filters.employee_id || null,
});

const handleSearch = () => {
    router.get(route('leave-applications.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    {
        name: 'status', label: 'Status', type: 'dropdown', options: [
            { label: 'Pending', value: 'pending' },
            { label: 'Approved', value: 'approved' },
            { label: 'Rejected', value: 'rejected' }
        ]
    },
    { name: 'leave_type_id', label: 'Leave Type', type: 'dropdown', options: props.leaveTypes, optionLabel: 'name', optionValue: 'id' },
    { name: 'employee_id', label: 'Employee', type: 'dropdown', options: props.employees, optionLabel: 'name', optionValue: 'id' }
];

const applicationDialog = ref(false);
const submitted = ref(false);
const isEdit = ref(false);
const viewDialog = ref(false);
const viewData = ref(null);

// SweetAlert state
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const openView = (app) => {
    viewData.value = app;
    viewDialog.value = true;
};

const form = useForm({
    id: null,
    employee_id: null,
    leave_type_id: null,
    start_date: null,
    end_date: null,
    reason: '',
    attachment: null,
    status: 'pending'
});

const statuses = [
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    applicationDialog.value = true;
};

const editApplication = (app) => {
    form.clearErrors();
    form.id = app.id;
    form.employee_id = app.employee_id;
    form.leave_type_id = app.leave_type_id;
    form.start_date = new Date(app.start_date);
    form.end_date = new Date(app.end_date);
    form.reason = app.reason;
    form.attachment = app.attachment;
    form.status = app.status;

    isEdit.value = true;
    applicationDialog.value = true;
};

const hideDialog = () => {
    applicationDialog.value = false;
    submitted.value = false;
};

const formatDateForBackend = (date) => {
    if (!date) return null;
    if (typeof date === 'string') return date;
    const offset = date.getTimezoneOffset();
    const adjustedDate = new Date(date.getTime() - (offset * 60 * 1000));
    return adjustedDate.toISOString().split('T')[0];
};

const saveApplication = () => {
    submitted.value = true;

    // Format dates for backend
    const formData = {
        ...form.data(),
        start_date: formatDateForBackend(form.start_date),
        end_date: formatDateForBackend(form.end_date)
    };

    if (isEdit.value) {
        router.put(route('leave-applications.update', form.id), formData, {
            onSuccess: () => {
                applicationDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Leave application has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        router.post(route('leave-applications.store'), formData, {
            onSuccess: () => {
                applicationDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Leave application has been submitted successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};


const confirmDeleteApplication = (app) => {
    form.id = app.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Delete this application?`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteApplication = () => {
    router.delete(route('leave-applications.destroy', form.id), {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Application has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteApplication();
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        default: return 'info';
    }
};

const calculateDays = (start, end) => {
    if (!start || !end) return 0;
    const s = new Date(start);
    const e = new Date(end);
    const diffTime = Math.abs(e - s);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays;
};
</script>

<template>

    <Head title="Leave Applications" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Leave Application Management</h2>
            <Button label="Add Leave Application" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search leave applications..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="leaveApplications.data" removableSort paginator :rows="perPage">
                <Column field="employee.name" header="Employee" sortable></Column>
                <Column field="leave_type.name" header="Leave Type" sortable>
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.leave_type?.name"
                            :style="{ backgroundColor: '#' + slotProps.data.leave_type?.color, color: 'white' }"
                            v-if="slotProps.data.leave_type" />
                    </template>
                </Column>
                <Column field="start_date" header="Dates" sortable>
                    <template #body="slotProps">
                        <span class="text-sm">
                            {{ slotProps.data.start_date }} to {{ slotProps.data.end_date }}
                        </span>
                    </template>
                </Column>
                <Column header="Days">
                    <template #body="slotProps">
                        {{ calculateDays(slotProps.data.start_date, slotProps.data.end_date) }}
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button @click="openView(slotProps.data)"
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View">
                                <i class="pi pi-eye"></i>
                            </Button>
                            <Button @click="editApplication(slotProps.data)"
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button @click="confirmDeleteApplication(slotProps.data)"
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="applicationDialog" :style="{ width: '500px' }"
            :header="isEdit ? 'Edit Leave Application' : 'Add New Leave Application'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Employee *</label>
                    <Dropdown v-model="form.employee_id" :options="employees" optionLabel="name" optionValue="id"
                        placeholder="Select Employee" />
                    <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Leave Type *</label>
                    <Dropdown v-model="form.leave_type_id" :options="leaveTypes" optionLabel="name" optionValue="id"
                        placeholder="Select Leave Type" />
                    <small class="text-red-500" v-if="form.errors.leave_type_id">{{ form.errors.leave_type_id }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Start Date *</label>
                        <DatePicker v-model="form.start_date" dateFormat="yy-mm-dd" showIcon />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">End Date *</label>
                        <DatePicker v-model="form.end_date" dateFormat="yy-mm-dd" showIcon :minDate="form.start_date" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Reason *</label>
                    <Textarea v-model="form.reason" rows="3" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Status</label>
                    <Dropdown v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveApplication" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Leave Application Details"
            :modal="true">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Employee</label>
                    <div class="p-2 bg-gray-50 rounded border">{{ viewData.employee?.name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Leave Type</label>
                    <div class="p-2 bg-gray-50 rounded border" v-if="viewData.leave_type">
                        <Tag :value="viewData.leave_type.name"
                            :style="{ backgroundColor: '#' + viewData.leave_type.color, color: 'white' }" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Period</label>
                        <div class="p-2 bg-gray-50 rounded border text-sm">{{ viewData.start_date }} to {{
                            viewData.end_date }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Days</label>
                        <div class="p-2 bg-gray-50 rounded border">{{ calculateDays(viewData.start_date,
                            viewData.end_date) }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Reason</label>
                    <div class="p-2 bg-gray-50 rounded border text-sm overflow-hidden text-ellipsis">{{ viewData.reason
                        || '-'
                    }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Status</label>
                        <div>
                            <Tag :value="viewData.status" :severity="getStatusSeverity(viewData.status)" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1" v-if="viewData.approver">
                        <label class="font-bold text-gray-500">Approved By</label>
                        <div class="p-2 bg-gray-50 rounded border text-sm">{{ viewData.approver.name }}</div>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="viewDialog = false" />
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
