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
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    departments: Object,
    branches: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    branch_id: props.filters.branch_id || null,
    name: props.filters.name || null,
    status: props.filters.status || null,
});

const handleSearch = () => {
    router.get(route('departments.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    { name: 'branch_id', label: 'Branch', type: 'dropdown', options: props.branches, optionLabel: 'name', optionValue: 'id' },
    { name: 'name', label: 'Department Name', type: 'text' },
    {
        name: 'status', label: 'Status', type: 'dropdown', options: [
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' }
        ]
    }
];

const departmentDialog = ref(false);
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

const openView = (department) => {
    viewData.value = department;
    viewDialog.value = true;
};

const form = useForm({
    id: null,
    branch_id: null,
    name: '',
    code: '',
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
    departmentDialog.value = true;
};

const editDepartment = (department) => {
    form.clearErrors();
    form.id = department.id;
    form.branch_id = department.branch_id;
    form.name = department.name;
    form.code = department.code;
    form.status = department.status;

    isEdit.value = true;
    departmentDialog.value = true;
};

const hideDialog = () => {
    departmentDialog.value = false;
    submitted.value = false;
};

const saveDepartment = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('departments.update', form.id), {
            onSuccess: () => {
                departmentDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Department has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            },
            onError: () => {
                alertConfig.value = {
                    title: 'Error!',
                    message: 'There was an error updating the department.',
                    type: 'error',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('departments.store'), {
            onSuccess: () => {
                departmentDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Department has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            },
            onError: () => {
                alertConfig.value = {
                    title: 'Error!',
                    message: 'There was an error creating the department.',
                    type: 'error',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteDepartment = (department) => {
    form.id = department.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${department.name}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteDepartment = () => {
    form.delete(route('departments.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Department has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        },
        onError: () => {
            alertConfig.value = {
                title: 'Error!',
                message: 'There was an error deleting the department.',
                type: 'error',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    // If it's a delete confirmation dialog
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteDepartment();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success';
        case 'inactive':
            return 'secondary';
        default:
            return 'info';
    }
};
</script>

<template>

    <Head title="Aimanova - Departments" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Departments</h2>
            <Button label="Add Department" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search departments..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="departments.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="name" header="Name" sortable></Column>
                <Column field="code" header="Code" sortable></Column>
                <Column field="branch.name" header="Branch" sortable></Column>
                <Column header="Status" style="width: 15%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions" style="width: 15%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View" @click="openView(slotProps.data)">
                                <i class="pi pi-eye"></i>
                            </Button>
                            <Button
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit" @click="editDepartment(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDeleteDepartment(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="departmentDialog" :style="{ width: '450px' }"
            :header="isEdit ? 'Edit Department' : 'New Department'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="branch" class="font-bold">Branch</label>
                    <Dropdown id="branch" v-model="form.branch_id" :options="branches" optionLabel="name"
                        optionValue="id" placeholder="Select a Branch" :invalid="submitted && !form.branch_id" />
                    <small class="text-red-500" v-if="form.errors.branch_id">{{ form.errors.branch_id }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="name" class="font-bold">Name</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="submitted && !form.name" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="code" class="font-bold">Code</label>
                    <InputText id="code" v-model.trim="form.code" />
                    <small class="text-red-500" v-if="form.errors.code">{{ form.errors.code }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="status" class="font-bold">Status</label>
                    <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                        optionValue="value" placeholder="Select a Status" />
                    <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveDepartment" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Department Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Branch</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.branch ? viewData.branch.name
                        : '-'
                        }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Code</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.code || '-' }}</div>
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

        <!-- SweetAlert Component -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
