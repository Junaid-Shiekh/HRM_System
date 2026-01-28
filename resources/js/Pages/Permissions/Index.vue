<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    permissions: Object,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    name: props.filters.name || null,
});

const handleSearch = () => {
    router.get(route('permissions.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    { name: 'name', label: 'Permission Name', type: 'text' }
];

// Modal State
const permissionDialog = ref(false);
const viewDialog = ref(false);
const isEditing = ref(false);
const permissionToDelete = ref(null);
const viewData = ref(null);
const submitted = ref(false);

const form = useForm({
    id: null,
    name: ''
});

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
    permissionDialog.value = true;
};

const editPermission = (permission) => {
    form.clearErrors();
    form.id = permission.id;
    form.name = permission.name;
    isEditing.value = true;
    permissionDialog.value = true;
};

const openView = (permission) => {
    viewData.value = permission;
    viewDialog.value = true;
};

const hideDialog = () => {
    permissionDialog.value = false;
    submitted.value = false;
};

const savePermission = () => {
    submitted.value = true;

    if (form.name.trim()) {
        if (isEditing.value) {
            form.put(route('permissions.update', form.id), {
                onSuccess: () => {
                    permissionDialog.value = false;
                    form.reset();
                    showSuccessAlert('Permission updated successfully');
                }
            });
        } else {
            form.post(route('permissions.store'), {
                onSuccess: () => {
                    permissionDialog.value = false;
                    form.reset();
                    showSuccessAlert('Permission created successfully');
                }
            });
        }
    }
};

const confirmDeletePermission = (permission) => {
    permissionToDelete.value = permission;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete this permission? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deletePermission = () => {
    router.delete(route('permissions.destroy', permissionToDelete.value.id), {
        onSuccess: () => {
            permissionToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Permission has been deleted successfully.',
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
        deletePermission();
    }
};
</script>

<template>

    <Head title="Permissions" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Permissions</h2>
            <Button label="Add Permission" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-4 !py-2" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search permissions..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="permissions.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="id" header="ID" sortable style="width: 10%"></Column>
                <Column field="name" header="Name" sortable style="width: 70%"></Column>
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
                                rounded aria-label="Edit" @click="editPermission(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDeletePermission(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="permissionDialog" :style="{ width: '450px' }"
            :header="isEditing ? 'Edit Permission' : 'New Permission'" :modal="true" class="p-fluid">
            <div class="field">
                <label for="name" class="font-bold mb-2 block">Name</label>
                <InputText id="name" v-model.trim="form.name" required="true" autofocus
                    :class="{ 'p-invalid': submitted && !form.name }" class="w-full" />
                <small class="p-error text-red-500" v-if="submitted && !form.name">Name is required.</small>
                <small class="p-error text-red-500 block" v-if="form.errors.name">{{ form.errors.name }}</small>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="savePermission" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Permission Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">ID</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.id }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 font-semibold text-[#1C0D82]">{{
                        viewData.name }}
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Guard Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.guard_name }}</div>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times" text @click="viewDialog = false" />
            </template>
        </Dialog>


        <!-- SweetAlert -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
