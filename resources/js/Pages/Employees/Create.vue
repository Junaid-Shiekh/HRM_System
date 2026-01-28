<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    branches: Array,
    departments: Array,
    designations: Array,
    shifts: Array,
    attendancePolicies: Array
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
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

// SweetAlert state
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const submit = () => {
    form.post(route('employees.store'), {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Success!',
                message: 'Employee has been created successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        },
        onError: () => {
            alertConfig.value = {
                title: 'Error!',
                message: 'There was an error creating the employee. Please check the form.',
                type: 'error',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.type === 'success') {
        router.visit(route('employees.index'));
    }
};
</script>

<template>

    <Head title="Add Employee" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <Link :href="route('employees.index')"
                    class="bg-[#1C0D82] hover:bg-[#150a61] text-white mb-3 inline-flex items-center gap-2 font-medium transition-all duration-200 px-4 py-2 rounded-lg shadow-sm hover:shadow-md">
                    <i class="pi pi-arrow-left"></i>
                    <span>Back to Employees</span>
                </Link>
                <h2 class="text-2xl font-bold text-gray-800 mt-2">Add New Employee</h2>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">

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
                        <small class="text-red-500" v-if="form.errors.date_of_birth">{{ form.errors.date_of_birth
                            }}</small>
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
                        <small class="text-red-500" v-if="form.errors.joining_date">{{ form.errors.joining_date
                            }}</small>
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
                            optionValue="id" placeholder="Select Department" :invalid="form.errors.department_id"
                            filter />
                        <small class="text-red-500" v-if="form.errors.department_id">{{ form.errors.department_id
                            }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="designation" class="font-bold text-sm">Designation</label>
                        <Dropdown id="designation" v-model="form.designation_id" :options="designations"
                            optionLabel="name" optionValue="id" placeholder="Select Designation"
                            :invalid="form.errors.designation_id" filter />
                        <small class="text-red-500" v-if="form.errors.designation_id">{{ form.errors.designation_id
                            }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="shift" class="font-bold text-sm">Shift</label>
                        <Dropdown id="shift" v-model="form.shift_id" :options="shifts" optionLabel="name"
                            optionValue="id" placeholder="Select Shift" :invalid="form.errors.shift_id" filter />
                        <small class="text-red-500" v-if="form.errors.shift_id">{{ form.errors.shift_id }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="attendance_policy" class="font-bold text-sm">Attendance Policy</label>
                        <Dropdown id="attendance_policy" v-model="form.attendance_policy_id"
                            :options="attendancePolicies" optionLabel="name" optionValue="id"
                            placeholder="Select Policy" :invalid="form.errors.attendance_policy_id" filter />
                        <small class="text-red-500" v-if="form.errors.attendance_policy_id">{{
                            form.errors.attendance_policy_id }}</small>
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

                    <!-- Credentials -->
                    <div class="col-span-full border-b pb-2 mb-2 mt-4">
                        <h3 class="font-semibold text-gray-600">Employee Credentials</h3>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password" class="font-bold text-sm">Login Password</label>
                        <InputText id="password" v-model="form.password" type="password"
                            placeholder="Enter password for employee login" :invalid="form.errors.password" />
                        <small class="text-gray-500">Left blank if you don't want to create login credentials
                            yet.</small>
                        <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
                    </div>

                    <!-- Actions -->
                    <div class="col-span-full flex justify-end gap-3 mt-4">
                        <Link :href="route('employees.index')">
                            <Button label="Cancel"
                                class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                        </Link>
                        <Button type="submit" label="Create Employee" :loading="form.processing"
                            class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
                    </div>
                </form>
            </div>
        </div>

        <!-- SweetAlert Component -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
