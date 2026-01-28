<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SearchFilter from '@/Components/SearchFilter.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    employees: Array,
});

const credentialDialog = ref(false);
const selectedEmployee = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    employee_id: '',
    password: '',
});

const searchTerm = ref('');
const perPage = ref(10);

const filteredEmployees = computed(() => {
    if (!searchTerm.value) return props.employees;
    const term = searchTerm.value.toLowerCase();
    return props.employees.filter(e =>
        e.first_name.toLowerCase().includes(term) ||
        e.last_name.toLowerCase().includes(term) ||
        e.email.toLowerCase().includes(term) ||
        e.employee_id.toString().includes(term)
    );
});

const openCredentialDialog = (employee) => {
    selectedEmployee.value = employee;
    form.employee_id = employee.id;
    form.password = '';
    credentialDialog.value = true;
};

const submitCredentials = () => {
    form.post(route('employee-credentials.store'), {
        onSuccess: () => {
            credentialDialog.value = false;
            alertConfig.value = { title: 'Success!', message: 'Credentials updated successfully.', type: 'success' };
            showAlert.value = true;
        }
    });
};

</script>

<template>

    <Head title="Employee Credentials" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Employee Credentials</h1>
            </div>

            <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search employees..."
                :showFilter="false" />

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <DataTable :value="filteredEmployees" paginator :rows="perPage" responsiveLayout="scroll">
                    <Column field="employee_id" header="Emp ID" sortable></Column>
                    <Column header="Name">
                        <template #body="slotProps">
                            {{ slotProps.data.first_name }} {{ slotProps.data.last_name }}
                        </template>
                    </Column>
                    <Column field="email" header="Email"></Column>
                    <Column header="Status">
                        <template #body="slotProps">
                            <Tag v-if="slotProps.data.user_id" value="Has Login" severity="success" />
                            <Tag v-else value="No Login" severity="warn" />
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button v-if="slotProps.data.user_id" icon="pi pi-key"
                                    class="!bg-purple-100 !text-purple-600 !border-purple-100 hover:!bg-purple-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    rounded v-tooltip="'Reset Password'"
                                    @click="openCredentialDialog(slotProps.data)" />
                                <Button v-else icon="pi pi-user-plus"
                                    class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    rounded v-tooltip="'Create Login'" @click="openCredentialDialog(slotProps.data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <Dialog v-model:visible="credentialDialog"
            :header="selectedEmployee?.user_id ? 'Reset Password' : 'Create Credentials'" :style="{ width: '500px' }"
            :modal="true" class="p-fluid">
            <div class="flex flex-col gap-6 p-6">
                <!-- Employee Info Block -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 flex flex-col gap-1">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Employee Details</span>
                    <div class="font-bold text-gray-800 text-lg">{{ selectedEmployee?.first_name }} {{
                        selectedEmployee?.last_name }}</div>
                    <div class="text-sm text-gray-600">{{ selectedEmployee?.email }}</div>
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">New Password</label>
                    <InputText v-model="form.password" type="password" placeholder="Enter new password"
                        class="!w-full !border !border-gray-300 !rounded-md focus:!border-[#1C0D82] focus:!ring-0"
                        :class="{ 'p-invalid': form.errors.password }" />
                    <small class="text-red-500" v-if="form.errors.password">{{ form.errors.password }}</small>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Cancel" icon="pi pi-times" @click="credentialDialog = false"
                        class="!bg-gray-100 !text-gray-700 !border-gray-100 hover:!bg-gray-200 !px-6 !py-2.5 !rounded-md" />
                    <Button label="Save Changes" icon="pi pi-check" @click="submitCredentials"
                        :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5 !rounded-md" />
                </div>
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>
