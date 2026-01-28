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
    color: '3498db',
    status: 'active'
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
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

        <Dialog v-model:visible="leaveTypeDialog" :style="{ width: '450px' }"
            :header="isEdit ? 'Edit Leave Type' : 'Add New Leave Type'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Leave Type Name *</label>
                    <InputText v-model="form.name" required autofocus :invalid="submitted && !form.name" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Description</label>
                    <Textarea v-model="form.description" rows="3" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Max Days / Year *</label>
                        <InputNumber v-model="form.max_days" showButtons :min="0" />
                    </div>
                    <div class="flex flex-col gap-2 flex justify-center mt-6">
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.is_paid" :binary="true" inputId="is_paid" />
                            <label for="is_paid" class="font-bold cursor-pointer">Is Paid</label>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Color *</label>
                        <div class="flex items-center gap-2 p-2 border rounded-md">
                            <ColorPicker v-model="form.color" format="hex" />
                            <span class="text-sm font-bold text-gray-600">#{{ form.color }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Status</label>
                        <Dropdown v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveLeaveType" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Leave Type Details" :modal="true">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="flex items-center gap-2 p-2 bg-gray-50 rounded border">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: '#' + viewData.color }"></div>
                        {{ viewData.name }}
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Description</label>
                    <div class="p-2 bg-gray-50 rounded border">{{ viewData.description || '-' }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Max Days</label>
                        <div class="p-2 bg-gray-50 rounded border">{{ viewData.max_days }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Type</label>
                        <div class="p-2 bg-gray-50 rounded border">
                            <Tag :value="viewData.is_paid ? 'Paid' : 'Unpaid'"
                                :severity="viewData.is_paid ? 'success' : 'danger'" />
                        </div>
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
