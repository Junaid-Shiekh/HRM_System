<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    contracts: Array,
    employees: Array,
    contractTypes: Array,
});

const contractDialog = ref(false);
const submitted = ref(false);
const isEdit = ref(false);
const contractToDelete = ref(null);
const viewDialog = ref(false);
const viewData = ref(null);

// SweetAlert State
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const form = useForm({
    id: null,
    employee_id: null,
    contract_type_id: null,
    start_date: null,
    end_date: null,
    salary: null,
    status: 'active',
    document: null,
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Expired', value: 'expired' },
    { label: 'Terminated', value: 'terminated' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    contractDialog.value = true;
};

const editContract = (contract) => {
    form.clearErrors();
    form.id = contract.id;
    form.employee_id = contract.employee_id;
    form.contract_type_id = contract.contract_type_id;
    form.start_date = contract.start_date ? new Date(contract.start_date) : null;
    form.end_date = contract.end_date ? new Date(contract.end_date) : null;
    form.salary = Number(contract.salary);
    form.status = contract.status;
    form.document = null; // Don't preload file

    isEdit.value = true;
    contractDialog.value = true;
};

const hideDialog = () => {
    contractDialog.value = false;
    submitted.value = false;
};

const saveContract = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.post(route('contracts.update', form.id), {
            forceFormData: true,
            onSuccess: () => {
                contractDialog.value = false;
                form.reset();
                showSuccessAlert('Contract updated successfully');
            },
            onError: () => {
                // Handle errors
            }
        });
        // Note: Using post with _method: 'PUT' is handled by Inertia automatically if we used put(), 
        // but for file uploads we often need post with forceFormData. 
        // However, Inertia's update usually handles files correctly if configured. 
        // Let's stick to Inertia's default router.put if NO file, but here we have file.
        // Laravel Inertia file upload for PUT usually requires sending as POST with _method="PUT".
        // Let's adjust form.post to include _method if needed or just use form.post for update with a spoofing field if Inertia doesn't handle it automatically.
        // Actually, Inertia v1.0+ handles files in put? 
        // The common workaround is using post to the update route with _method: 'PUT' in data.
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        }));
    } else {
        form.post(route('contracts.store'), {
            onSuccess: () => {
                contractDialog.value = false;
                form.reset();
                showSuccessAlert('Contract created successfully');
            }
        });
    }
};

const openView = (contract) => {
    viewData.value = contract;
    viewDialog.value = true;
};

const confirmDelete = (contract) => {
    contractToDelete.value = contract;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete this contract? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteContract = () => {
    router.delete(route('contracts.destroy', contractToDelete.value.id), {
        onSuccess: () => {
            contractToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Contract has been deleted successfully.',
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
        deleteContract();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'active':
            return 'success';
        case 'terminated':
            return 'danger';
        case 'expired':
            return 'warning';
        default:
            return 'info';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>

    <Head title="Aimanova - Contracts" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Contracts</h2>
            <Button label="New Contract" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-4 !py-2" />
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="contracts" removableSort paginator :rows="10" tableStyle="min-width: 50rem">
                <Column header="Employee" sortable sortField="employee.first_name">
                    <template #body="slotProps">
                        {{ slotProps.data.employee.first_name }} {{ slotProps.data.employee.last_name }}
                    </template>
                </Column>
                <Column field="contract_type.name" header="Type" sortable></Column>
                <Column header="Start Date" sortable sortField="start_date">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.start_date) }}
                    </template>
                </Column>
                <Column header="End Date" sortable sortField="end_date">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.end_date) }}
                    </template>
                </Column>
                <Column field="salary" header="Salary" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.salary }}
                    </template>
                </Column>
                <Column header="Status" style="width: 10%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Document">
                    <template #body="slotProps">
                        <a v-if="slotProps.data.document" :href="'/storage/' + slotProps.data.document" target="_blank"
                            class="text-blue-500 underline">View</a>
                        <span v-else>-</span>
                    </template>
                </Column>
                <Column header="Actions" style="width: 15%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View" @click="openView(slotProps.data)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-eye">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </Button>
                            <Button
                                class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit" @click="editContract(slotProps.data)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-edit">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDelete(slotProps.data)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-trash-2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path
                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                    </path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="contractDialog" :style="{ width: '800px' }"
            :header="isEdit ? 'Edit Contract' : 'New Contract'" :modal="true" class="p-fluid">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label for="employee" class="font-bold">Employee</label>
                    <Dropdown id="employee" v-model="form.employee_id" :options="employees" optionLabel="first_name"
                        optionValue="id" placeholder="Select Employee" filter class="w-full"
                        :invalid="!!form.errors.employee_id">
                        <template #option="slotProps">
                            {{ slotProps.option.first_name }} {{ slotProps.option.last_name }} ({{
                                slotProps.option.employee_id }})
                        </template>
                        <template #value="slotProps">
                            <div v-if="slotProps.value">
                                {{employees.find(e => e.id === slotProps.value)?.first_name}} {{employees.find(e =>
                                    e.id === slotProps.value)?.last_name}}
                            </div>
                            <span v-else>
                                {{ slotProps.placeholder }}
                            </span>
                        </template>
                    </Dropdown>
                    <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="contract_type" class="font-bold">Contract Type</label>
                    <Dropdown id="contract_type" v-model="form.contract_type_id" :options="contractTypes"
                        optionLabel="name" optionValue="id" placeholder="Select Type" class="w-full"
                        :invalid="!!form.errors.contract_type_id" />
                    <small class="text-red-500" v-if="form.errors.contract_type_id">{{ form.errors.contract_type_id
                        }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="start_date" class="font-bold">Start Date</label>
                    <Calendar id="start_date" v-model="form.start_date" dateFormat="yy-mm-dd" showIcon
                        :invalid="!!form.errors.start_date" />
                    <small class="text-red-500" v-if="form.errors.start_date">{{ form.errors.start_date }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="end_date" class="font-bold">End Date</label>
                    <Calendar id="end_date" v-model="form.end_date" dateFormat="yy-mm-dd" showIcon
                        :invalid="!!form.errors.end_date" />
                    <small class="text-red-500" v-if="form.errors.end_date">{{ form.errors.end_date }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="salary" class="font-bold">Salary</label>
                    <InputNumber id="salary" v-model="form.salary" mode="currency" currency="PKR" locale="en-PK"
                        class="w-full" :invalid="!!form.errors.salary" />
                    <small class="text-red-500" v-if="form.errors.salary">{{ form.errors.salary }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="status" class="font-bold">Status</label>
                    <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                        optionValue="value" placeholder="Select Status" class="w-full"
                        :invalid="!!form.errors.status" />
                    <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                </div>

                <div class="col-span-full flex flex-col gap-2">
                    <label for="document" class="font-bold">Document (PDF/DOC/Image)</label>
                    <input type="file" @input="form.document = $event.target.files[0]" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-violet-50 file:text-violet-700
                        hover:file:bg-violet-100
                    " />
                    <small class="text-red-500" v-if="form.errors.document">{{ form.errors.document }}</small>
                    <small class="text-gray-500 text-xs">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG. Max:
                        2MB</small>
                    <div v-if="form.errors.document === 'The document field must be a file of type: pdf, doc, docx.'"
                        class="text-red-500 text-sm mt-1">
                        The document must be a file of type: pdf, doc, docx, jpg, jpeg, png.
                    </div>
                    <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Cancel" icon="pi pi-times" text @click="hideDialog"
                        class="!text-gray-600 hover:!text-gray-800 !px-6 !py-3" />
                    <Button label="Save" icon="pi pi-check" @click="saveContract" :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-3" />
                </div>
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '450px' }" header="Contract Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Employee</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.employee.first_name }} {{
                        viewData.employee.last_name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Type</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.contract_type.name }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Start Date</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ formatDate(viewData.start_date) }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">End Date</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ formatDate(viewData.end_date) }}
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Salary</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.salary }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Status</label>
                    <div>
                        <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
                    </div>
                </div>
                <div class="flex flex-col gap-1" v-if="viewData.document">
                    <label class="font-bold text-gray-500">Document</label>
                    <div>
                        <a :href="'/storage/' + viewData.document" target="_blank"
                            class="text-blue-500 underline flex items-center gap-1">
                            <i class="pi pi-external-link text-xs"></i> View Document
                        </a>
                    </div>
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
