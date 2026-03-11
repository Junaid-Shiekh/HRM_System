<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import ColorPicker from 'primevue/colorpicker';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    leaveTypes: Object,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    is_paid: props.filters.is_paid !== undefined ? props.filters.is_paid : null
});

const handleSearch = () => {
    router.get(route('leave-types.index'), {
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
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' }
        ]
    },
    {
        name: 'is_paid', label: 'Paid Status', type: 'dropdown', options: [
            { label: 'Paid', value: 1 },
            { label: 'Unpaid', value: 0 }
        ]
    }
];

const leaveTypeDialog = ref(false);
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

const openView = (type) => {
    viewData.value = type;
    viewDialog.value = true;
};

const form = useForm({
    id: null,
    name: '',
    description: '',
    max_days: 0,
    is_paid: true,
    gender: 'all',
    allow_carry_forward: false,
    color: '3498db',
    status: 'active'
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
];

const genderOptions = [
    { label: 'All', value: 'all' },
    { label: 'Male Only', value: 'male' },
    { label: 'Female Only', value: 'female' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    leaveTypeDialog.value = true;
};

const editLeaveType = (type) => {
    form.clearErrors();
    form.id = type.id;
    form.name = type.name;
    form.description = type.description;
    form.max_days = type.max_days;
    form.is_paid = !!type.is_paid;
    form.gender = type.gender || 'all';
    form.allow_carry_forward = !!type.allow_carry_forward;
    form.color = type.color || '3498db';
    form.status = type.status;

    isEdit.value = true;
    leaveTypeDialog.value = true;
};

const hideDialog = () => {
    leaveTypeDialog.value = false;
    submitted.value = false;
};

const saveLeaveType = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('leave-types.update', form.id), {
            onSuccess: () => {
                leaveTypeDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Leave type has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('leave-types.store'), {
            onSuccess: () => {
                leaveTypeDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Leave type has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteLeaveType = (type) => {
    form.id = type.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${type.name}?`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteLeaveType = () => {
    form.delete(route('leave-types.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Leave type has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteLeaveType();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'secondary';
        default: return 'info';
    }
};
</script>

<template>

    <Head title="Leave Types" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Leave Type Management</h2>
            <Button label="Add Leave Type" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search leave types..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="leaveTypes.data" removableSort paginator :rows="perPage">
                <Column field="name" header="Name" sortable>
                    <template #body="slotProps">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: '#' + slotProps.data.color }">
                            </div>
                            {{ slotProps.data.name }}
                        </div>
                    </template>
                </Column>
                <Column field="description" header="Description"></Column>
                <Column field="max_days" header="Max Days/Year" sortable></Column>
                <Column header="Type">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.is_paid ? 'Paid' : 'Unpaid'"
                            :severity="slotProps.data.is_paid ? 'success' : 'danger'" />
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)" />
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
                            <Button @click="editLeaveType(slotProps.data)"
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button @click="confirmDeleteLeaveType(slotProps.data)"
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Add / Edit Dialog -->
        <Dialog v-model:visible="leaveTypeDialog" :style="{ width: '620px' }"
            :header="isEdit ? 'Edit Leave Type' : 'Add New Leave Type'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-5 pt-4">

                <!-- Leave Type Name -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                        Leave Type Name <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.name" required autofocus :invalid="submitted && !form.name"
                        placeholder="e.g. Annual Leave, Sick Leave..." class="!py-3 !px-4 !text-base !rounded-lg" />
                    <small class="text-red-500 text-xs" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>

                <!-- Description -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Description</label>
                    <Textarea v-model="form.description" rows="3" placeholder="Brief description of this leave type..."
                        class="!py-3 !px-4 !text-base !rounded-lg resize-none" />
                </div>

                <!-- Max Days & Applicability -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Max Days / Year <span class="text-red-500">*</span>
                        </label>
                        <InputNumber v-model="form.max_days" showButtons :min="0" inputClass="!py-3 !text-base"
                            class="!rounded-lg" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Applicability</label>
                        <Dropdown v-model="form.gender" :options="genderOptions" optionLabel="label" optionValue="value"
                            placeholder="Select Applicability" class="!py-1 !rounded-lg" />
                    </div>
                </div>

                <!-- Checkbox Cards -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#1C0D82] hover:bg-indigo-50 transition-all duration-200 cursor-pointer select-none"
                        @click="form.is_paid = !form.is_paid">
                        <Checkbox v-model="form.is_paid" :binary="true" inputId="is_paid" class="mt-0.5" />
                        <div>
                            <label for="is_paid" class="font-semibold text-gray-800 cursor-pointer block text-sm">Is
                                Paid</label>
                            <span class="text-xs text-gray-500 leading-snug">Employees receive pay during this
                                leave</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#1C0D82] hover:bg-indigo-50 transition-all duration-200 cursor-pointer select-none"
                        @click="form.allow_carry_forward = !form.allow_carry_forward">
                        <Checkbox v-model="form.allow_carry_forward" :binary="true" inputId="carry_forward"
                            class="mt-0.5" />
                        <div>
                            <label for="carry_forward"
                                class="font-semibold text-gray-800 cursor-pointer block text-sm">Allow
                                Carry Forward</label>
                            <span class="text-xs text-gray-500 leading-snug">Unused days roll over to next year</span>
                        </div>
                    </div>
                </div>

                <!-- Color & Status -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Color <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center gap-3 px-3 py-2.5 border border-gray-300 rounded-lg bg-white hover:border-[#1C0D82] transition-colors cursor-pointer">
                            <ColorPicker v-model="form.color" format="hex" />
                            <div class="w-7 h-7 rounded-md shadow-sm border border-gray-200 flex-shrink-0"
                                :style="{ backgroundColor: '#' + form.color }"></div>
                            <span class="text-sm font-mono font-bold text-gray-700">#{{ form.color?.toUpperCase()
                                }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Status</label>
                        <Dropdown v-model="form.status" :options="statuses" optionLabel="label" optionValue="value"
                            class="!py-1 !rounded-lg" />
                    </div>
                </div>

            </div>

            <template #footer>
                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 mt-2">
                    <Button label="Cancel" icon="pi pi-times"
                        class="!bg-white !text-gray-700 !border-gray-300 hover:!bg-gray-50 !px-6 !py-2.5 !font-semibold !rounded-lg"
                        @click="hideDialog" />
                    <Button :label="isEdit ? 'Update Leave Type' : 'Save Leave Type'" icon="pi pi-check"
                        @click="saveLeaveType" :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] !text-white !px-8 !py-2.5 !font-semibold !rounded-lg" />
                </div>
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Leave Type Details" :modal="true">
            <div class="flex flex-col gap-4 pt-2" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Name</label>
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="w-4 h-4 rounded-full flex-shrink-0"
                            :style="{ backgroundColor: '#' + viewData.color }">
                        </div>
                        <span class="font-semibold text-gray-800">{{ viewData.name }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Description</label>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-gray-700">{{ viewData.description
                        || '—'
                        }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Max Days /
                            Year</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-semibold text-gray-800">{{
                            viewData.max_days }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label
                            class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Applicability</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 capitalize text-gray-800">{{
                            viewData.gender }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Type</label>
                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <Tag :value="viewData.is_paid ? 'Paid' : 'Unpaid'"
                                :severity="viewData.is_paid ? 'success' : 'danger'" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Carry
                            Forward</label>
                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <Tag :value="viewData.allow_carry_forward ? 'Yes' : 'No'"
                                :severity="viewData.allow_carry_forward ? 'success' : 'secondary'" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Status</label>
                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end pt-3 border-t border-gray-100 mt-2">
                    <Button label="Close" icon="pi pi-times"
                        class="!bg-white !text-gray-700 !border-gray-300 hover:!bg-gray-50 !px-6 !py-2.5 !rounded-lg"
                        @click="viewDialog = false" />
                </div>
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>