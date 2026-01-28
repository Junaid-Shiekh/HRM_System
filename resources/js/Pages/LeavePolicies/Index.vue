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
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    leavePolicies: Object,
    leaveTypes: Array,
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

        <Dialog v-model:visible="leavePolicyDialog" :style="{ width: '500px' }"
            :header="isEdit ? 'Edit Leave Policy' : 'Add New Leave Policy'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Policy Name *</label>
                    <InputText v-model="form.name" required autofocus :invalid="submitted && !form.name" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Description</label>
                    <Textarea v-model="form.description" rows="2" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Leave Type *</label>
                    <Dropdown v-model="form.leave_type_id" :options="leaveTypes" optionLabel="name" optionValue="id"
                        placeholder="Select Leave Type" />
                    <small class="text-red-500" v-if="form.errors.leave_type_id">{{ form.errors.leave_type_id }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Accrual Type *</label>
                        <Dropdown v-model="form.accrual_type" :options="accrualTypes" optionLabel="label"
                            optionValue="value" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Accrual Rate (Days) *</label>
                        <InputNumber v-model="form.accrual_rate" :minFractionDigits="1" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Min Days / Application *</label>
                        <InputNumber v-model="form.min_days" :min="1" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Max Days / Application</label>
                        <InputNumber v-model="form.max_days" :min="1" placeholder="Empty for no limit" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Carry Forward Limit *</label>
                        <InputNumber v-model="form.carry_forward_limit" :min="0" />
                    </div>
                    <div class="flex flex-col gap-2 flex justify-center mt-6">
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.requires_approval" :binary="true" inputId="requires_approval" />
                            <label for="requires_approval" class="font-bold cursor-pointer">Requires Approval</label>
                        </div>
                    </div>
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
                <Button label="Save" icon="pi pi-check" @click="saveLeavePolicy" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Leave Policy Details" :modal="true">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Policy Name</label>
                    <div class="p-2 bg-gray-50 rounded border">{{ viewData.name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Leave Type</label>
                    <div class="flex items-center gap-2 p-2 bg-gray-50 rounded border" v-if="viewData.leave_type">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: '#' + viewData.leave_type.color }">
                        </div>
                        {{ viewData.leave_type.name }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Accrual</label>
                        <div class="p-2 bg-gray-50 rounded border">{{ viewData.accrual_type }} ({{ viewData.accrual_rate
                        }}
                            days)</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Approval</label>
                        <div class="p-2 bg-gray-50 rounded border">
                            <Tag :value="viewData.requires_approval ? 'Required' : 'Not Required'"
                                :severity="viewData.requires_approval ? 'warning' : 'success'" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <div class="p-2 bg-gray-50 rounded border">{{ viewData.min_days }} / {{ viewData.max_days ||
                            "Unlimited"
                            }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Carry Forward Limit</label>
                        <div class="p-2 bg-gray-50 rounded border">{{ viewData.carry_forward_limit }} days</div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Status</label>
                    <div>
                        <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
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
