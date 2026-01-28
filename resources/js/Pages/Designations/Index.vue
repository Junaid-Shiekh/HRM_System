<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    designations: Object,
    departments: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    department_id: props.filters.department_id || null,
    name: props.filters.name || null,
    status: props.filters.status || null,
});

const handleSearch = () => {
    router.get(route('designations.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    { name: 'department_id', label: 'Department', type: 'dropdown', options: props.departments, optionLabel: 'name', optionValue: 'id' },
    { name: 'name', label: 'Designation Name', type: 'text' },
    {
        name: 'status', label: 'Status', type: 'dropdown', options: [
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' }
        ]
    }
];

// Modal State
const designationDialog = ref(false);
const viewDialog = ref(false);
const submitted = ref(false);
const isEditing = ref(false);
const designationToDelete = ref(null);
const viewData = ref(null);

const form = useForm({
    id: null,
    name: '',
    department_id: null,
    description: '',
    status: 'active'
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
];

// SweetAlert State
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEditing.value = false;
    designationDialog.value = true;
};

const editDesignation = (designation) => {
    form.clearErrors();
    form.id = designation.id;
    form.name = designation.name;
    form.department_id = designation.department_id;
    form.description = designation.description;
    form.status = designation.status;

    isEditing.value = true;
    designationDialog.value = true;
};

const openView = (designation) => {
    viewData.value = designation;
    viewDialog.value = true;
};

const hideDialog = () => {
    designationDialog.value = false;
    submitted.value = false;
};

const saveDesignation = () => {
    submitted.value = true;

    if (isEditing.value) {
        form.put(route('designations.update', form.id), {
            onSuccess: () => {
                designationDialog.value = false;
                form.reset();
                showSuccessAlert('Designation updated successfully');
            },
            onError: () => {
                // Handling by Inertia
            }
        });
    } else {
        form.post(route('designations.store'), {
            onSuccess: () => {
                designationDialog.value = false;
                form.reset();
                showSuccessAlert('Designation created successfully');
            },
            onError: () => {
                // Handling by Inertia
            }
        });
    }
};

const confirmDelete = (designation) => {
    designationToDelete.value = designation;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${designation.name}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteDesignation = () => {
    router.delete(route('designations.destroy', designationToDelete.value.id), {
        onSuccess: () => {
            designationToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Designation has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const showSuccessAlert = (message) => {
    alertConfig.value = {
        title: 'Success!',
        message: message,
        type: 'success',
        showCancel: false
    };
    showAlert.value = true;
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteDesignation();
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

    <Head title="Aimanova - Designations" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Designations</h2>
            <Button label="Add Designation" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search designations..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="designations.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="name" header="Name" sortable></Column>
                <Column field="department.name" header="Department" sortable></Column>
                <Column header="Status" style="width: 20%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions" style="width: 20%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View" @click="openView(slotProps.data)">
                                <i class="pi pi-eye"></i>
                            </Button>
                            <Button
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit" @click="editDesignation(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDelete(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="designationDialog" :style="{ width: '450px' }"
            :header="isEditing ? 'Edit Designation' : 'New Designation'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name" class="font-bold">Name</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="submitted && !form.name" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="department" class="font-bold">Department</label>
                    <Dropdown id="department" v-model="form.department_id" :options="departments" optionLabel="name"
                        optionValue="id" placeholder="Select Department" :invalid="form.errors.department_id" filter />
                    <small class="text-red-500" v-if="form.errors.department_id">{{ form.errors.department_id }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="description" class="font-bold">Description</label>
                    <Textarea id="description" v-model="form.description" rows="3" />
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
                <Button label="Save" icon="pi pi-check" @click="saveDesignation" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Designation Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Department</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.department ?
                        viewData.department.name
                        : '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Description</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.description || '-' }}</div>
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


        <!-- SweetAlert -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
