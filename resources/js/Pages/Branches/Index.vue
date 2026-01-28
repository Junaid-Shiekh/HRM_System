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
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    branches: Object,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    name: props.filters.name || null,
    status: props.filters.status || null,
});

const handleSearch = () => {
    router.get(route('branches.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    { name: 'name', label: 'Branch Name', type: 'text' },
    {
        name: 'status', label: 'Status', type: 'dropdown', options: [
            { label: 'Active', value: 'active' },
            { label: 'Inactive', value: 'inactive' }
        ]
    }
];

const branchDialog = ref(false);
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

const openView = (branch) => {
    viewData.value = branch;
    viewDialog.value = true;
};

const form = useForm({
    id: null,
    name: '',
    code: '',
    address: '',
    phone: '',
    email: '',
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
    branchDialog.value = true;
};

const editBranch = (branch) => {
    form.clearErrors();
    form.id = branch.id;
    form.name = branch.name;
    form.code = branch.code;
    form.address = branch.address;
    form.phone = branch.phone;
    form.email = branch.email;
    form.status = branch.status;

    isEdit.value = true;
    branchDialog.value = true;
};

const hideDialog = () => {
    branchDialog.value = false;
    submitted.value = false;
};

const saveBranch = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('branches.update', form.id), {
            onSuccess: () => {
                branchDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Branch has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            },
            onError: () => {
                alertConfig.value = {
                    title: 'Error!',
                    message: 'There was an error updating the branch.',
                    type: 'error',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('branches.store'), {
            onSuccess: () => {
                branchDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'Branch has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            },
            onError: () => {
                alertConfig.value = {
                    title: 'Error!',
                    message: 'There was an error creating the branch.',
                    type: 'error',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteBranch = (branch) => {
    form.id = branch.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${branch.name}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteBranch = () => {
    form.delete(route('branches.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Branch has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        },
        onError: () => {
            alertConfig.value = {
                title: 'Error!',
                message: 'There was an error deleting the branch.',
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
        deleteBranch();
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

    <Head title="Aimanova - Branches" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Branches</h2>
            <Button label="Add Branch" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search branches..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="branches.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="name" header="Name" sortable></Column>
                <Column field="code" header="Code" sortable></Column>
                <Column field="address" header="Address"></Column>
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
                                rounded aria-label="Edit" @click="editBranch(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDeleteBranch(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="branchDialog" :style="{ width: '450px' }"
            :header="isEdit ? 'Edit Branch' : 'New Branch'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
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
                    <label for="address" class="font-bold">Address</label>
                    <Textarea id="address" v-model="form.address" rows="3" cols="20" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="phone" class="font-bold">Phone</label>
                        <InputText id="phone" v-model.trim="form.phone" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="email" class="font-bold">Email</label>
                        <InputText id="email" v-model.trim="form.email" />
                        <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
                    </div>
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
                <Button label="Save" icon="pi pi-check" @click="saveBranch" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Branch Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Code</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.code }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Address</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.address || '-' }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Phone</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.phone || '-' }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Email</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.email || '-' }}</div>
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

        <!-- SweetAlert Component -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
