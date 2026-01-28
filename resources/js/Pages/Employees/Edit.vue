<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';

const props = defineProps({
    employee: Object,
    branches: Array,
    departments: Array,
    designations: Array,
    shifts: Array,
    attendancePolicies: Array
});

const form = useForm({
    first_name: props.employee.first_name,
    last_name: props.employee.last_name,
    email: props.employee.email,
    employee_id: props.employee.employee_id,
    phone: props.employee.phone,
    branch_id: props.employee.branch_id,
    department_id: props.employee.department_id,
    designation_id: props.employee.designation_id,
    date_of_birth: props.employee.date_of_birth ? new Date(props.employee.date_of_birth) : null,
    joining_date: props.employee.joining_date ? new Date(props.employee.joining_date) : null,
    gender: props.employee.gender,
    address: props.employee.address,
    salary: props.employee.salary,
    status: props.employee.status,
    shift_id: props.employee.shift_id,
    attendance_policy_id: props.employee.attendance_policy_id
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

const submit = () => {
    form.put(route('employees.update', props.employee.id));
};
</script>

<template>

    <Head title="Edit Employee" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <Link :href="route('employees.index')" class="text-gray-500 hover:text-gray-700 mb-2 inline-block">
                    <i class="pi pi-arrow-left mr-1"></i> Back to Employees
                </Link>
                <h2 class="text-2xl font-bold text-gray-800">Edit Employee</h2>
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

                    <!-- Actions -->
                    <div class="col-span-full flex justify-end gap-3 mt-4">
                        <Link :href="route('employees.index')">
                            <Button label="Cancel" severity="secondary" text />
                        </Link>
                        <Button type="submit" label="Save Changes" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
