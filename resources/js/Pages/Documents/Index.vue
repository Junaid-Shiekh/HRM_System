<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import SweetAlert from '@/Components/SweetAlert.vue';


const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    documents: Array,
    employees: Array,
    filters: Object,
});

const dialog = ref(false);
const viewDialog = ref(false);
const submitted = ref(false);
const isEdit = ref(false);
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
    document_type: null,
    title: '',
    expiry_date: null,
    file_path: null,
});

const filterForm = ref({
    employee_id: props.filters.employee_id || null,
});

const documentTypes = [
    'Resume', 'ID Proof', 'Visa', 'Offer Letter', 'Contract', 'Certificate', 'Other'
];

// Watching filters for auto-reload
watch(() => filterForm.value.employee_id, debounce((val) => {
    router.get(route('documents.index'), { employee_id: val }, { preserveState: true, replace: true });
}, 300));

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    dialog.value = true;
};

const editDocument = (doc) => {
    form.clearErrors();
    form.id = doc.id;
    form.employee_id = doc.employee_id;
    form.document_type = doc.document_type;
    form.title = doc.title;
    form.expiry_date = doc.expiry_date ? new Date(doc.expiry_date) : null;
    form.file_path = null; // Don't preload file

    isEdit.value = true;
    dialog.value = true;
};

const openView = (doc) => {
    viewData.value = doc;
    viewDialog.value = true;
};

const hideDialog = () => {
    dialog.value = false;
    submitted.value = false;
};

const saveDocument = () => {
    submitted.value = true;

    if (isEdit.value) {
        // Use post with _method put for file upload overlap
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('documents.update', form.id), {
            onSuccess: () => {
                dialog.value = false;
                form.reset();
                showSuccessAlert('Document updated successfully');
            }
        });
    } else {
        form.post(route('documents.store'), {
            onSuccess: () => {
                dialog.value = false;
                form.reset();
                showSuccessAlert('Document uploaded successfully');
            }
        });
    }
};

const confirmDelete = (doc) => {
    form.id = doc.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${doc.title || 'this document'}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteDocument = () => {
    form.delete(route('documents.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Document has been deleted successfully.',
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
        deleteDocument();
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString();
};

const getSeverity = (expiryDate) => {
    if (!expiryDate) return 'success';
    const now = new Date();
    const expiry = new Date(expiryDate);
    if (expiry < now) return 'danger'; // Expired
    const diffTime = Math.abs(expiry - now);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if (diffDays <= 30) return 'warning'; // Expiring soon
    return 'success';
};
</script>

<template>

    <Head title="Aimanova - Employee Documents" />

    <AuthenticatedLayout>
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Documents</h2>
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <Dropdown v-model="filterForm.employee_id" :options="employees" optionLabel="first_name"
                    optionValue="id" placeholder="Filter by Employee" showClear filter
                    class="w-full md:w-64 !border !border-black">
                    <template #option="slotProps">
                        {{ slotProps.option.first_name }} {{ slotProps.option.last_name }}
                    </template>
                    <template #value="slotProps">
                        <div v-if="slotProps.value">
                            {{employees.find(e => e.id === slotProps.value)?.first_name}} {{employees.find(e => e.id
                                === slotProps.value)?.last_name}}
                        </div>
                        <span v-else>
                            {{ slotProps.placeholder }}
                        </span>
                    </template>
                </Dropdown>
                <Button label="Upload Document" icon="pi pi-upload" @click="openNew"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </div>
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="documents" removableSort paginator :rows="10" tableStyle="min-width: 50rem">
                <template #empty>No documents found.</template>
                <Column header="Employee" sortable sortField="employee.first_name">
                    <template #body="slotProps">
                        {{ slotProps.data.employee.first_name }} {{ slotProps.data.employee.last_name }}
                    </template>
                </Column>
                <Column field="document_type" header="Type" sortable></Column>
                <Column field="title" header="Title" sortable></Column>
                <Column header="Expiry Date" sortable sortField="expiry_date">
                    <template #body="slotProps">
                        <Tag :value="formatDate(slotProps.data.expiry_date)"
                            :severity="getSeverity(slotProps.data.expiry_date)" />
                    </template>
                </Column>
                <Column header="File">
                    <template #body="slotProps">
                        <a :href="'/storage/' + slotProps.data.file_path" target="_blank"
                            class="text-blue-500 underline flex items-center gap-1 hover:text-blue-700">
                            <i class="pi pi-external-link text-xs"></i> View File
                        </a>
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
                                rounded aria-label="Edit" @click="editDocument(slotProps.data)">
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

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="dialog" :style="{ width: '500px' }"
            :header="isEdit ? 'Edit Document' : 'Upload Document'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="employee" class="font-bold">Employee</label>
                    <Dropdown id="employee" v-model="form.employee_id" :options="employees" optionLabel="first_name"
                        optionValue="id" placeholder="Select Employee" filter :disabled="isEdit"
                        :invalid="submitted && !form.employee_id">
                        <template #option="slotProps">
                            {{ slotProps.option.first_name }} {{ slotProps.option.last_name }}
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
                    <label for="document_type" class="font-bold">Document Type</label>
                    <Dropdown id="document_type" v-model="form.document_type" :options="documentTypes"
                        placeholder="Select Type" editable :invalid="submitted && !form.document_type" />
                    <small class="text-red-500" v-if="form.errors.document_type">{{ form.errors.document_type }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="title" class="font-bold">Title (Optional)</label>
                    <InputText id="title" v-model="form.title" placeholder="e.g. Passport Front Page" />
                    <small class="text-red-500" v-if="form.errors.title">{{ form.errors.title }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="expiry_date" class="font-bold">Expiry Date</label>
                    <Calendar id="expiry_date" v-model="form.expiry_date" dateFormat="yy-mm-dd" showIcon
                        :invalid="!!form.errors.expiry_date" />
                    <small class="text-red-500" v-if="form.errors.expiry_date">{{ form.errors.expiry_date }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="file_path" class="font-bold">File</label>
                    <input type="file" @input="form.file_path = $event.target.files[0]" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-violet-50 file:text-violet-700
                        hover:file:bg-violet-100
                    " />
                    <small class="text-red-500" v-if="form.errors.file_path">{{ form.errors.file_path }}</small>
                    <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveDocument" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Document Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Employee</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.employee.first_name }} {{
                        viewData.employee.last_name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Type</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.document_type }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Title</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.title || '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Expiry Date</label>
                    <div>
                        <Tag :value="formatDate(viewData.expiry_date)" :severity="getSeverity(viewData.expiry_date)" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">File</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <a :href="'/storage/' + viewData.file_path" target="_blank"
                            class="text-blue-500 underline flex items-center gap-1 hover:text-blue-700">
                            <i class="pi pi-external-link text-xs"></i> Open File
                        </a>
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
