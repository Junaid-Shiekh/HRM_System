<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    roles: Object,
    permissions: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    name: props.filters.name || null,
});

const handleSearch = () => {
    router.get(route('roles.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    { name: 'name', label: 'Role Name', type: 'text' }
];

// Modal State
const roleDialog = ref(false);
const viewDialog = ref(false); // Added View Dialog
const isEditing = ref(false);
const roleToDelete = ref(null);
const viewData = ref(null); // Added View Data
const submitted = ref(false);

const form = useForm({
    id: null,
    name: '',
    permissions: []
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
    roleDialog.value = true;
};

const editRole = (role) => {
    form.clearErrors();
    form.id = role.id;
    form.name = role.name;
    form.permissions = role.permissions ? role.permissions.map(p => p.name) : [];
    isEditing.value = true;
    roleDialog.value = true;
};

const formatPermissionName = (name) => {
    if (!name) return '';

    const parts = name.split('.');
    if (parts.length !== 2) return name.charAt(0).toUpperCase() + name.slice(1);

    const resource = parts[0];
    const action = parts[1];

    const actionMap = {
        'index': 'Browse',
        'create': 'Create',
        'store': 'Save',
        'show': 'View',
        'edit': 'Edit',
        'update': 'Update',
        'destroy': 'Delete'
    };

    const friendlyAction = actionMap[action] || action.charAt(0).toUpperCase() + action.slice(1);
    const friendlyResource = resource.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

    // Pluralize resource name for Browse
    const finalResource = friendlyAction === 'Browse' ? friendlyResource : friendlyResource.replace(/s$/, '');

    return `${friendlyAction} ${friendlyResource}`;
};

const openView = (role) => {
    viewData.value = role;
    viewDialog.value = true;
};

const hideDialog = () => {
    roleDialog.value = false;
    submitted.value = false;
};

const saveRole = () => {
    submitted.value = true;

    if (form.name.trim()) {
        if (isEditing.value) {
            form.put(route('roles.update', form.id), {
                onSuccess: () => {
                    roleDialog.value = false;
                    form.reset();
                    showSuccessAlert('Role updated successfully');
                }
            });
        } else {
            form.post(route('roles.store'), {
                onSuccess: () => {
                    roleDialog.value = false;
                    form.reset();
                    showSuccessAlert('Role created successfully');
                }
            });
        }
    }
};

const confirmDeleteRole = (role) => {
    roleToDelete.value = role;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete this role? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteRole = () => {
    router.delete(route('roles.destroy', roleToDelete.value.id), {
        onSuccess: () => {
            roleToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Role has been deleted successfully.',
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
        deleteRole();
    }
};

const selectAll = computed({
    get: () => {
        return props.permissions.length > 0 && form.permissions.length === props.permissions.length;
    },
    set: (value) => {
        if (value) {
            form.permissions = props.permissions.map(p => p.name);
        } else {
            form.permissions = [];
        }
    }
});
</script>

<template>

    <Head title="Roles" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Roles</h2>
            <Button label="Add Role" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-4 !py-2" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search roles..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="roles.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
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
                                rounded aria-label="Edit" @click="editRole(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDeleteRole(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="roleDialog" :style="{ width: '500px' }" :header="isEditing ? 'Edit Role' : 'New Role'"
            :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name" class="font-bold">Name</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="submitted && !form.name" />
                    <small class="text-red-500" v-if="submitted && !form.name">Name is required.</small>
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center mb-1">
                        <label class="font-bold">Permissions</label>
                        <div class="flex items-center gap-2 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                            <Checkbox id="select_all" v-model="selectAll" :binary="true" />
                            <label for="select_all"
                                class="text-[10px] font-black uppercase cursor-pointer text-indigo-700">Select
                                All</label>
                        </div>
                    </div>
                    <div class="p-3 border border-gray-200 rounded-lg max-h-60 overflow-y-auto bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div v-for="permission in permissions" :key="permission.id"
                                class="flex items-center gap-2 bg-white px-2 py-1.5 rounded border border-gray-100 shadow-sm hover:border-indigo-200 transition-colors">
                                <Checkbox :inputId="'perm_' + permission.id" name="permissions" :value="permission.name"
                                    v-model="form.permissions" />
                                <label :for="'perm_' + permission.id"
                                    class="text-sm text-gray-700 cursor-pointer truncate font-medium"
                                    :title="permission.name">
                                    {{ formatPermissionName(permission.name) }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <small class="text-red-500" v-if="form.errors.permissions">{{ form.errors.permissions }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveRole" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Role Details" :modal="true"
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
                    <label class="font-bold text-gray-500">Permissions</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 text-gray-500 italic">
                        <div v-if="viewData.permissions && viewData.permissions.length > 0"
                            class="flex flex-wrap gap-2">
                            <Tag v-for="perm in viewData.permissions" :key="perm.id" :value="perm.name"
                                severity="info" />
                        </div>
                        <span v-else class="text-gray-500 italic">No permissions assigned.</span>
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
