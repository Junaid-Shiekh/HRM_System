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
import MultiSelect from 'primevue/multiselect';
import Dropdown from 'primevue/dropdown';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    role: props.filters.role || null
});

const handleSearch = () => {
    router.get(route('users.index'), {
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
            { label: 'Inactive', value: 'inactive' },
            { label: 'Banned', value: 'banned' }
        ]
    },
    { name: 'role', label: 'Role', type: 'dropdown', options: props.roles, optionLabel: 'name', optionValue: 'name' }
];

const userDialog = ref(false);
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

const openView = (user) => {
    viewData.value = user;
    viewDialog.value = true;
};

const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    status: 'active',
    roles: [],
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Banned', value: 'banned' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    userDialog.value = true;
};

const editUser = (user) => {
    form.clearErrors();
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.status = user.status;
    form.roles = user.roles ? user.roles.map(r => r.name) : [];

    isEdit.value = true;
    userDialog.value = true;
};

const hideDialog = () => {
    userDialog.value = false;
    submitted.value = false;
};

const saveUser = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('users.update', form.id), {
            onSuccess: () => {
                userDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'User has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            },
            onError: () => {
                alertConfig.value = {
                    title: 'Error!',
                    message: 'There was an error updating the user.',
                    type: 'error',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => {
                userDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Success!',
                    message: 'User has been created successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            },
            onError: () => {
                alertConfig.value = {
                    title: 'Error!',
                    message: 'There was an error creating the user.',
                    type: 'error',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const confirmDeleteUser = (user) => {
    form.id = user.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${user.name}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteUser = () => {
    form.delete(route('users.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'User has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        },
        onError: () => {
            alertConfig.value = {
                title: 'Error!',
                message: 'There was an error deleting the user.',
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
        deleteUser();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success';
        case 'inactive':
            return 'secondary';
        case 'banned':
            return 'danger';
        default:
            return 'info';
    }
};
</script>

<template>

    <Head title="Staff - Users" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Users</h2>
            <Button label="Add User" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search users..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="users.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="name" header="Name" sortable style="width: 25%"></Column>
                <Column field="email" header="Email" sortable style="width: 25%"></Column>
                <Column header="Status" style="width: 15%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column field="created_at" header="Joined" sortable style="width: 15%">
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.created_at).toLocaleDateString() }}
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
                                rounded aria-label="Edit" @click="editUser(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDeleteUser(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="userDialog" :style="{ width: '450px' }" :header="isEdit ? 'Edit User' : 'New User'"
            :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name" class="font-bold">Name</label>
                    <InputText id="name" v-model.trim="form.name" required="true" autofocus
                        :invalid="submitted && !form.name" />
                    <small class="text-red-500" v-if="form.errors.name">{{ form.errors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="email" class="font-bold">Email</label>
                    <InputText id="email" v-model.trim="form.email" required="true"
                        :invalid="submitted && !form.email" />
                    <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="password" class="font-bold">Password</label>
                    <InputText id="password" v-model="form.password" type="password" :required="!isEdit"
                        :placeholder="isEdit ? 'Leave blank to keep current' : ''" />
                    <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="password_confirmation" class="font-bold">Confirm Password</label>
                    <InputText id="password_confirmation" v-model="form.password_confirmation" type="password"
                        :required="!isEdit || form.password"
                        :placeholder="isEdit ? 'Leave blank to keep current' : ''" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="status" class="font-bold">Status</label>
                    <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                        optionValue="value" placeholder="Select a Status" />
                    <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="roles" class="font-bold">Roles</label>
                    <MultiSelect id="roles" v-model="form.roles" :options="roles" optionLabel="name" optionValue="name"
                        placeholder="Select Roles" filter display="chip"
                        class="w-full !border !border-gray-300 !rounded-md" />
                    <small class="text-red-500" v-if="form.errors.roles">{{ form.errors.roles }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveUser" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="User Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Email</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.email }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Status</label>
                    <div>
                        <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Joined</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ new
                        Date(viewData.created_at).toLocaleDateString() }}</div>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times" text @click="viewDialog = false" />
            </template>
        </Dialog>

        <!-- SweetAlert Component -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
