<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

import { ref, computed, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    employees: Object,
    filters: Object,
    branches: Array,
    departments: Array,
    designations: Array,
    shifts: Array,
    attendancePolicies: Array
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    branch_id: props.filters.branch_id || null,
    department_id: props.filters.department_id || null,
    designation_id: props.filters.designation_id || null,
});

const handleSearch = () => {
    router.get(route('employees.index'), {
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
            { label: 'Terminated', value: 'terminated' },
            { label: 'Resigned', value: 'resigned' }
        ]
    },
    { name: 'branch_id', label: 'Branch', type: 'dropdown', options: props.branches, optionLabel: 'name', optionValue: 'id' },
    { name: 'department_id', label: 'Department', type: 'dropdown', options: props.departments, optionLabel: 'name', optionValue: 'id' },
    { name: 'designation_id', label: 'Designation', type: 'dropdown', options: props.designations, optionLabel: 'name', optionValue: 'id' }
];

// Modal State
const employeeDialog = ref(false);
const viewDialog = ref(false);
const isEditing = ref(false);
const employeeToDelete = ref(null);
const viewData = ref(null);
const submitted = ref(false);

const form = useForm({
    id: null,
    first_name: '',
    last_name: '',
    email: '',
    employee_id: '',
    phone: '',
    branch_id: null,
    department_id: null,
    designation_id: null,
    date_of_birth: null,
    joining_date: null,
    gender: null,
    address: '',
    salary: null,
    status: 'active',
    shift_id: null,
    attendance_policy_id: null
});

const genders = [
    { label: 'Male', value: 'male' },
    { label: 'Female', value: 'female' },
    { label: 'Other', value: 'other' }
];

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Terminated', value: 'terminated' },
    { label: 'Resigned', value: 'resigned' }
];


const filteredDesignations = computed(() => {
    if (!form.department_id) {
        return [];
    }
    return props.designations.filter(d => d.department_id === form.department_id);
});

watch(() => form.department_id, (newVal) => {
    // Only reset designation if it doesn't belong to the new department
    if (form.designation_id) {
        const currentDesignation = props.designations.find(d => d.id === form.designation_id);
        if (currentDesignation && currentDesignation.department_id !== newVal) {
            form.designation_id = null;
        }
    }
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
    employeeDialog.value = true;
};

const editEmployee = (employee) => {
    form.clearErrors();
    form.id = employee.id;
    form.first_name = employee.first_name;
    form.last_name = employee.last_name;
    form.email = employee.email;
    form.employee_id = employee.employee_id;
    form.phone = employee.phone;
    form.branch_id = employee.branch_id;
    form.department_id = employee.department_id;
    form.designation_id = employee.designation_id;
    form.date_of_birth = employee.date_of_birth ? new Date(employee.date_of_birth) : null;
    form.joining_date = employee.joining_date ? new Date(employee.joining_date) : null;
    form.gender = employee.gender;
    form.address = employee.address;
    form.salary = employee.salary;
    form.status = employee.status;
    form.shift_id = employee.shift_id;
    form.attendance_policy_id = employee.attendance_policy_id;

    isEditing.value = true;
    employeeDialog.value = true;
};

const openView = (employee) => {
    viewData.value = employee;
    viewDialog.value = true;
};

const hideDialog = () => {
    employeeDialog.value = false;
    submitted.value = false;
};

const saveEmployee = () => {
    submitted.value = true;

    if (isEditing.value) {
        form.put(route('employees.update', form.id), {
            onSuccess: () => {
                employeeDialog.value = false;
                form.reset();
                showSuccessAlert('Employee updated successfully');
            },
            onError: () => {
                // Errors handled by Inertia
            }
        });
    } else {
        form.post(route('employees.store'), {
            onSuccess: () => {
                employeeDialog.value = false;
                form.reset();
                showSuccessAlert('Employee created successfully');
            },
            onError: () => {
                // Errors handled by Inertia
            }
        });
    }
};

const confirmDelete = (employee) => {
    employeeToDelete.value = employee;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${employee.first_name} ${employee.last_name}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteEmployee = () => {
    router.delete(route('employees.destroy', employeeToDelete.value.id), {
        onSuccess: () => {
            employeeToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Employee has been deleted successfully.',
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
        deleteEmployee();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success';
        case 'inactive':
            return 'secondary';
        case 'terminated':
            return 'danger';
        default:
            return 'info';
    }
};
</script>

<template>

    <Head title="Aimanova - Employees" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Employees</h2>
            <Button label="Add Employee" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search employees..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="employees.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="employee_id" header="ID" sortable></Column>
                <Column header="Name" sortable field="first_name">
                    <template #body="slotProps">
                        {{ slotProps.data.first_name }} {{ slotProps.data.last_name }}
                    </template>
                </Column>
                <Column field="email" header="Email"></Column>
                <Column field="department.name" header="Department" sortable></Column>
                <Column field="designation.name" header="Designation" sortable></Column>
                <Column header="Status" style="width: 10%">
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
                                rounded aria-label="Edit" @click="editEmployee(slotProps.data)">
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

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="employeeDialog" :style="{ width: '900px' }"
            :header="isEditing ? 'Edit Employee' : 'New Employee'" :modal="true" class="p-fluid">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div class="col-span-full border-b pb-2 mb-2">
                    <h3 class="font-semibold text-gray-600">Basic Information</h3>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="first_name" class="font-bold text-sm">First Name</label>
                    <InputText id="first_name" v-model="form.first_name" :invalid="form.errors.first_name" />
                    <small class="text-red-500" v-if="form.errors.first_name">{{ form.errors.first_name }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="last_name" class="font-bold text-sm">Last Name</label>
                    <InputText id="last_name" v-model="form.last_name" :invalid="form.errors.last_name" />
                    <small class="text-red-500" v-if="form.errors.last_name">{{ form.errors.last_name }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="email" class="font-bold text-sm">Email</label>
                    <InputText id="email" v-model="form.email" :invalid="form.errors.email" />
                    <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="phone" class="font-bold text-sm">Phone</label>
                    <InputText id="phone" v-model="form.phone" :invalid="form.errors.phone" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="dob" class="font-bold text-sm">Date of Birth</label>
                    <DatePicker id="dob" v-model="form.date_of_birth" showIcon fluid />
                    <small class="text-red-500" v-if="form.errors.date_of_birth">{{ form.errors.date_of_birth }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="gender" class="font-bold text-sm">Gender</label>
                    <Dropdown id="gender" v-model="form.gender" :options="genders" optionLabel="label"
                        optionValue="value" placeholder="Select Gender" />
                </div>

                <!-- Work Info -->
                <div class="col-span-full border-b pb-2 mb-2 mt-4">
                    <h3 class="font-semibold text-gray-600">Work Information</h3>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="employee_id" class="font-bold text-sm">Employee ID</label>
                    <InputText id="employee_id" v-model="form.employee_id" :invalid="form.errors.employee_id" />
                    <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="joining_date" class="font-bold text-sm">Joining Date</label>
                    <DatePicker id="joining_date" v-model="form.joining_date" showIcon fluid />
                    <small class="text-red-500" v-if="form.errors.joining_date">{{ form.errors.joining_date }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="branch" class="font-bold text-sm">Branch</label>
                    <Dropdown id="branch" v-model="form.branch_id" :options="branches" optionLabel="name"
                        optionValue="id" placeholder="Select Branch" :invalid="form.errors.branch_id" filter />
                    <small class="text-red-500" v-if="form.errors.branch_id">{{ form.errors.branch_id }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="department" class="font-bold text-sm">Department</label>
                    <Dropdown id="department" v-model="form.department_id" :options="departments" optionLabel="name"
                        optionValue="id" placeholder="Select Department" :invalid="form.errors.department_id" filter />
                    <small class="text-red-500" v-if="form.errors.department_id">{{ form.errors.department_id }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="designation" class="font-bold text-sm">Designation</label>
                    <Dropdown id="designation" v-model="form.designation_id" :options="filteredDesignations"
                        optionLabel="name" optionValue="id" placeholder="Select Designation"
                        :invalid="form.errors.designation_id" filter :disabled="!form.department_id" />
                    <small class="text-red-500" v-if="form.errors.designation_id">{{ form.errors.designation_id
                        }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="shift" class="font-bold text-sm">Shift</label>
                    <Dropdown id="shift" v-model="form.shift_id" :options="shifts" optionLabel="name" optionValue="id"
                        placeholder="Select Shift" :invalid="form.errors.shift_id" filter />
                    <small class="text-red-500" v-if="form.errors.shift_id">{{ form.errors.shift_id }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="attendance_policy" class="font-bold text-sm">Attendance Policy</label>
                    <Dropdown id="attendance_policy" v-model="form.attendance_policy_id" :options="attendancePolicies"
                        optionLabel="name" optionValue="id" placeholder="Select Policy"
                        :invalid="form.errors.attendance_policy_id" filter />
                    <small class="text-red-500" v-if="form.errors.attendance_policy_id">{{
                        form.errors.attendance_policy_id
                        }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="salary" class="font-bold text-sm">Basic Salary</label>
                    <InputText id="salary" v-model="form.salary" type="number" step="0.01" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="status" class="font-bold text-sm">Status</label>
                    <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                        optionValue="value" />
                    <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                </div>

                <div class="col-span-full flex flex-col gap-2">
                    <label for="address" class="font-bold text-sm">Address</label>
                    <Textarea id="address" v-model="form.address" rows="3" />
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveEmployee" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '800px' }" header="Employee Details" :modal="true"
            class="p-fluid">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" v-if="viewData">
                <div class="col-span-full border-b pb-2 mb-2">
                    <h3 class="font-semibold text-gray-600">Basic Information</h3>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Full Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.first_name }} {{
                        viewData.last_name
                        }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Email</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.email }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Phone</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.phone || '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Date of Birth</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.date_of_birth ? new
                        Date(viewData.date_of_birth).toLocaleDateString() : '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Gender</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 capitalize">{{ viewData.gender || '-' }}
                    </div>
                </div>

                <div class="col-span-full border-b pb-2 mb-2 mt-4">
                    <h3 class="font-semibold text-gray-600">Work Information</h3>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Employee ID</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.employee_id }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Joining Date</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ new
                        Date(viewData.joining_date).toLocaleDateString() }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Branch</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.branch ? viewData.branch.name
                        : '-'
                        }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Department</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.department ?
                        viewData.department.name
                        : '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Designation</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.designation ?
                        viewData.designation.name : '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Salary</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.salary }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Shift</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.shift ? viewData.shift.name :
                        '-' }}
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Attendance Policy</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.attendance_policy ?
                        viewData.attendance_policy.name : '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Status</label>
                    <div>
                        <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
                    </div>
                </div>

                <div class="col-span-full flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Address</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.address || '-' }}</div>
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
