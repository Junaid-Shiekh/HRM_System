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
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import MultiSelect from 'primevue/multiselect';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    leavePolicies: Object,
    leaveTypes: Array,
    departments: Array,
    designations: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    leave_type_id: props.filters.leave_type_id || null
});

const handleSearch = () => {
    router.get(route('leave-policies.index'), {
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
    { name: 'leave_type_id', label: 'Leave Type', type: 'dropdown', options: props.leaveTypes, optionLabel: 'name', optionValue: 'id' }
];

const leavePolicyDialog = ref(false);
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

const openView = (policy) => {
    viewData.value = policy;
    viewDialog.value = true;
};

const form = useForm({
    id: null,
    name: '',
    description: '',
    leave_type_id: null,
    accrual_type: 'once',
    accrual_rate: 0,
    carry_forward_limit: 0,
    min_days: 1,
    max_days: null,
    max_days_per_request: null,
    max_consecutive_days: null,
    notice_period_days: 0,
    allow_carry_forward: false,
    probation_restriction_days: 0,
    allow_half_day: true,
    allow_encashment: false,
    applicability_type: 'all',
    applicability_ids: [],
    requires_approval: true,
    status: 'active'
});

const accrualTypes = [
    { label: 'Once', value: 'once' },
    { label: 'Monthly', value: 'monthly' },
    { label: 'Yearly', value: 'yearly' }
];

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
];

const applicabilityOptions = [
    { label: 'All Employees', value: 'all' },
    { label: 'By Department', value: 'department' },
    { label: 'By Designation', value: 'designation' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    leavePolicyDialog.value = true;
};

const editLeavePolicy = (policy) => {
    form.clearErrors();
    form.id = policy.id;
    form.name = policy.name;
    form.description = policy.description;
    form.leave_type_id = policy.leave_type_id;
    form.accrual_type = policy.accrual_type || 'once';
    form.accrual_rate = policy.accrual_rate;
    form.carry_forward_limit = policy.carry_forward_limit;
    form.min_days = policy.min_days;
    form.max_days = policy.max_days;
    form.max_days_per_request = policy.max_days_per_request;
    form.max_consecutive_days = policy.max_consecutive_days;
    form.notice_period_days = policy.notice_period_days;
    form.allow_carry_forward = !!policy.allow_carry_forward;
    form.probation_restriction_days = policy.probation_restriction_days;
    form.allow_half_day = !!policy.allow_half_day;
    form.allow_encashment = !!policy.allow_encashment;
    form.applicability_type = policy.applicability_type || 'all';
    form.applicability_ids = policy.applicability_ids || [];
    form.requires_approval = !!policy.requires_approval;
    form.status = policy.status;

    isEdit.value = true;
    leavePolicyDialog.value = true;
};

const hideDialog = () => {
    leavePolicyDialog.value = false;
    submitted.value = false;
};

const saveLeavePolicy = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('leave-policies.update', form.id), {
            onSuccess: () => {
                leavePolicyDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Leave policy has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('leave-policies.store'), {
            onSuccess: () => {
                leavePolicyDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Leave policy has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteLeavePolicy = (policy) => {
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

const deleteLeavePolicy = () => {
    form.delete(route('leave-policies.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Leave policy has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteLeavePolicy();
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

    <Head title="Leave Policies" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Leave Policy Management</h2>
            <Button label="Add Leave Policy" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search leave policies..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="leavePolicies.data" removableSort paginator :rows="perPage">
                <Column field="name" header="Policy Name" sortable></Column>
                <Column field="leave_type.name" header="Leave Type" sortable>
                    <template #body="slotProps">
                        <div class="flex items-center gap-2" v-if="slotProps.data.leave_type">
                            <div class="w-3 h-3 rounded-full"
                                :style="{ backgroundColor: '#' + slotProps.data.leave_type.color }"></div>
                            {{ slotProps.data.leave_type.name }}
                        </div>
                    </template>
                </Column>
                <Column header="Approval">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.requires_approval ? 'Required' : 'Not Required'"
                            :severity="slotProps.data.requires_approval ? 'warning' : 'success'" />
                    </template>
                </Column>
                <Column field="status" header="Status">
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
                            <Button @click="editLeavePolicy(slotProps.data)"
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button @click="confirmDeleteLeavePolicy(slotProps.data)"
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
        <Dialog v-model:visible="leavePolicyDialog" :style="{ width: '680px' }"
            :header="isEdit ? 'Edit Leave Policy' : 'Add New Leave Policy'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-5 pt-4">

                <!-- Policy Name -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                        Policy Name <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.name" required autofocus :invalid="submitted && !form.name"
                        placeholder="e.g. Annual Leave Policy 2025..." class="!py-3 !px-4 !text-base !rounded-lg" />
                    <small class="text-red-500 text-xs" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>

                <!-- Description -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Description</label>
                    <Textarea v-model="form.description" rows="2" placeholder="Brief description of this policy..."
                        class="!py-3 !px-4 !text-base !rounded-lg resize-none" />
                </div>

                <!-- Leave Type -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                        Leave Type <span class="text-red-500">*</span>
                    </label>
                    <Dropdown v-model="form.leave_type_id" :options="leaveTypes" optionLabel="name" optionValue="id"
                        placeholder="Select Leave Type" class="!py-1 !rounded-lg" />
                    <small class="text-red-500 text-xs" v-if="form.errors.leave_type_id">{{ form.errors.leave_type_id
                    }}</small>
                </div>

                <!-- Section Divider: Accrual Settings -->
                <div class="flex items-center gap-3">
                    <div class="h-px bg-gray-200 flex-1"></div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-1">Accrual
                        Settings</span>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>

                <!-- Accrual Type & Rate -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Accrual Type <span class="text-red-500">*</span>
                        </label>
                        <Dropdown v-model="form.accrual_type" :options="accrualTypes" optionLabel="label"
                            optionValue="value" class="!py-1 !rounded-lg" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Accrual Rate (Days) <span class="text-red-500">*</span>
                        </label>
                        <InputNumber v-model="form.accrual_rate" :minFractionDigits="1" inputClass="!py-3 !text-base"
                            class="!rounded-lg" />
                    </div>
                </div>

                <!-- Section Divider: Leave Limits -->
                <div class="flex items-center gap-3">
                    <div class="h-px bg-gray-200 flex-1"></div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-1">Leave Limits</span>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>

                <!-- Min / Max Days -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                            Min Days / Application <span class="text-red-500">*</span>
                        </label>
                        <InputNumber v-model="form.min_days" :min="1" inputClass="!py-3 !text-base"
                            class="!rounded-lg" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Max Days /
                            Year (Entitlement)</label>
                        <InputNumber v-model="form.max_days" :min="1" placeholder="Yearly total"
                            inputClass="!py-3 !text-base" class="!rounded-lg" />
                    </div>
                </div>

                <!-- Max Days Per Request & Max Consecutive -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Max Days /
                            Application</label>
                        <InputNumber v-model="form.max_days_per_request" :min="1" placeholder="Per request limit"
                            inputClass="!py-3 !text-base" class="!rounded-lg" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Max Consecutive
                            Days</label>
                        <InputNumber v-model="form.max_consecutive_days" :min="1" placeholder="No limit"
                            inputClass="!py-3 !text-base" class="!rounded-lg" />
                    </div>
                </div>

                <!-- Notice Period & Probation -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Notice Period
                            (Days)</label>
                        <InputNumber v-model="form.notice_period_days" :min="0" inputClass="!py-3 !text-base"
                            class="!rounded-lg" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Probation
                            Restriction
                            (Days)</label>
                        <InputNumber v-model="form.probation_restriction_days" :min="0" inputClass="!py-3 !text-base"
                            class="!rounded-lg" />
                    </div>
                </div>

                <!-- Carry Forward Limit & Applicability Divider -->
                <div class="grid grid-cols-1 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Carry Forward Limit
                            (Days)</label>
                        <InputNumber v-model="form.carry_forward_limit" :min="0" inputClass="!py-3 !text-base"
                            class="!rounded-lg" />
                    </div>
                </div>

                <!-- Section Divider: Applicability -->
                <div class="flex items-center gap-3">
                    <div class="h-px bg-gray-200 flex-1"></div>
                    <span
                        class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-1">Applicability</span>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>

                <!-- Applicability Type -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Apply To</label>
                    <Dropdown v-model="form.applicability_type" :options="applicabilityOptions" optionLabel="label"
                        optionValue="value" class="!py-1 !rounded-lg" @change="form.applicability_ids = []" />
                </div>

                <!-- Departments / Designations MultiSelect -->
                <div v-if="form.applicability_type !== 'all'" class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                        {{ form.applicability_type === 'department' ? 'Select Departments' : 'Select Designations' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <MultiSelect v-model="form.applicability_ids"
                        :options="form.applicability_type === 'department' ? departments : designations"
                        optionLabel="name" optionValue="id"
                        :placeholder="form.applicability_type === 'department' ? 'Choose Departments...' : 'Choose Designations...'"
                        display="chip" class="w-full !rounded-lg" />
                    <small class="text-red-500 text-xs" v-if="form.errors.applicability_ids">{{
                        form.errors.applicability_ids
                    }}</small>
                </div>

                <!-- Section Divider: Permissions -->
                <div class="flex items-center gap-3">
                    <div class="h-px bg-gray-200 flex-1"></div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-1">Permissions &
                        Status</span>
                    <div class="h-px bg-gray-200 flex-1"></div>
                </div>

                <!-- Checkbox Cards Row -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#1C0D82] hover:bg-indigo-50 transition-all duration-200 cursor-pointer select-none"
                        @click="form.allow_carry_forward = !form.allow_carry_forward">
                        <Checkbox v-model="form.allow_carry_forward" :binary="true" inputId="allow_cf" class="mt-0.5" />
                        <div>
                            <label for="allow_cf" class="font-semibold text-gray-800 cursor-pointer block text-sm">Carry
                                Forward</label>
                            <span class="text-xs text-gray-500 leading-snug">Roll over unused days</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#1C0D82] hover:bg-indigo-50 transition-all duration-200 cursor-pointer select-none"
                        @click="form.allow_half_day = !form.allow_half_day">
                        <Checkbox v-model="form.allow_half_day" :binary="true" inputId="allow_half" class="mt-0.5" />
                        <div>
                            <label for="allow_half"
                                class="font-semibold text-gray-800 cursor-pointer block text-sm">Half
                                Day</label>
                            <span class="text-xs text-gray-500 leading-snug">Allow half-day applications</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#1C0D82] hover:bg-indigo-50 transition-all duration-200 cursor-pointer select-none"
                        @click="form.allow_encashment = !form.allow_encashment">
                        <Checkbox v-model="form.allow_encashment" :binary="true" inputId="allow_encash"
                            class="mt-0.5" />
                        <div>
                            <label for="allow_encash"
                                class="font-semibold text-gray-800 cursor-pointer block text-sm">Encashment</label>
                            <span class="text-xs text-gray-500 leading-snug">Allow leave encashment</span>
                        </div>
                    </div>
                </div>

                <!-- Status & Requires Approval -->
                <div class="grid grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Status</label>
                        <Dropdown v-model="form.status" :options="statuses" optionLabel="label" optionValue="value"
                            class="!py-1 !rounded-lg" />
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#1C0D82] hover:bg-indigo-50 transition-all duration-200 cursor-pointer select-none mt-5"
                        @click="form.requires_approval = !form.requires_approval">
                        <Checkbox v-model="form.requires_approval" :binary="true" inputId="requires_approval"
                            class="mt-0.5" />
                        <div>
                            <label for="requires_approval"
                                class="font-semibold text-gray-800 cursor-pointer block text-sm">Requires
                                Approval</label>
                            <span class="text-xs text-gray-500 leading-snug">Manager must approve requests</span>
                        </div>
                    </div>
                </div>

            </div>

            <template #footer>
                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 mt-2">
                    <Button label="Cancel" icon="pi pi-times"
                        class="!bg-white !text-gray-700 !border-gray-300 hover:!bg-gray-50 !px-6 !py-2.5 !font-semibold !rounded-lg"
                        @click="hideDialog" />
                    <Button :label="isEdit ? 'Update Policy' : 'Save Policy'" icon="pi pi-check"
                        @click="saveLeavePolicy" :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] !text-white !px-8 !py-2.5 !font-semibold !rounded-lg" />
                </div>
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '560px' }" header="Leave Policy Details" :modal="true">
            <div class="flex flex-col gap-4 pt-2" v-if="viewData">

                <!-- Name -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Policy Name</label>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-semibold text-gray-800">{{
                        viewData.name
                    }}</div>
                </div>

                <!-- Leave Type -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Leave Type</label>
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200"
                        v-if="viewData.leave_type">
                        <div class="w-4 h-4 rounded-full flex-shrink-0"
                            :style="{ backgroundColor: '#' + viewData.leave_type.color }"></div>
                        <span class="font-semibold text-gray-800">{{ viewData.leave_type.name }}</span>
                    </div>
                </div>

                <!-- Accrual -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Accrual</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-gray-700 capitalize">
                            {{ viewData.accrual_type }} &mdash; {{ viewData.accrual_rate }} days
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Approval</label>
                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <Tag :value="viewData.requires_approval ? 'Required' : 'Not Required'"
                                :severity="viewData.requires_approval ? 'warning' : 'success'" />
                        </div>
                    </div>
                </div>

                <!-- Days Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Min per
                            Request</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-semibold text-gray-800">{{
                            viewData.min_days }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Max per
                            Request</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-semibold text-gray-800">{{
                            viewData.max_days_per_request || 'No limit' }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Yearly
                            Entitlement</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-semibold text-gray-800">{{
                            viewData.max_days || 'Unlimited' }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Carry Fwd
                            Limit</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-semibold text-gray-800">{{
                            viewData.carry_forward_limit }} days</div>
                    </div>
                </div>

                <!-- Periods -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Notice
                            Period</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-gray-700">{{
                            viewData.notice_period_days }} days</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Probation
                            Restriction</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-gray-700">{{
                            viewData.probation_restriction_days }} days</div>
                    </div>
                </div>

                <!-- Flags -->
                <div class="grid grid-cols-4 gap-3">
                    <div class="flex flex-col gap-1 items-start">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Carry Fwd</label>
                        <Tag :value="viewData.allow_carry_forward ? 'Yes' : 'No'"
                            :severity="viewData.allow_carry_forward ? 'success' : 'secondary'" />
                    </div>
                    <div class="flex flex-col gap-1 items-start">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Half Day</label>
                        <Tag :value="viewData.allow_half_day ? 'Yes' : 'No'"
                            :severity="viewData.allow_half_day ? 'success' : 'secondary'" />
                    </div>
                    <div class="flex flex-col gap-1 items-start">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Encashment</label>
                        <Tag :value="viewData.allow_encashment ? 'Yes' : 'No'"
                            :severity="viewData.allow_encashment ? 'success' : 'secondary'" />
                    </div>
                    <div class="flex flex-col gap-1 items-start">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Status</label>
                        <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
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