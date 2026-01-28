<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import SweetAlert from '@/Components/SweetAlert.vue';


const props = defineProps({
    applications: Object,
    statuses: Array,
    jobs: Array,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    job_posting_id: props.filters.job_posting_id || null,
});

const handleSearch = () => {
    router.get(route('job-applications.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    { name: 'status', label: 'Status', type: 'dropdown', options: props.statuses, optionLabel: 'label', optionValue: 'value' },
    { name: 'job_posting_id', label: 'Job Posting', type: 'dropdown', options: props.jobs, optionLabel: 'title', optionValue: 'id' }
];

// SweetAlert State
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const statusDialog = ref(false);
const detailDialog = ref(false);
const selectedApplication = ref(null);

const form = useForm({
    id: null,
    status: null
});

const viewDetails = (application) => {
    selectedApplication.value = application;
    detailDialog.value = true;
};

const editStatus = (application) => {
    form.id = application.id;
    form.status = application.status;
    statusDialog.value = true;
};


const saveStatus = () => {
    form.put(route('job-applications.update', form.id), {
        onSuccess: () => {
            statusDialog.value = false;
            showSuccessAlert('Application status updated successfully');
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
    showAlert.value = false;
};

const getSeverity = (status) => {
    switch (status) {
        case 'applied': return 'info';
        case 'screening': return 'warning';
        case 'interview': return 'primary';
        case 'offer': return 'success';
        case 'hired': return 'success';
        case 'rejected': return 'danger';
        default: return 'info';
    }
};

const rowClass = (data) => {
    return {
        'row-rejected': data.status === 'rejected',
        'row-hired': data.status === 'hired',
        'row-screening': data.status === 'screening',
        'row-interview': data.status === 'interview'
    };
};
</script>


<template>

    <Head title="Recruitment - Applications" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Job Applications</h2>
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search by candidate name or email..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="applications.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem"
                :rowClass="rowClass">
                <Column field="candidate.first_name" header="Candidate" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.candidate.first_name }} {{ slotProps.data.candidate.last_name }}
                        <div class="text-xs text-gray-500">{{ slotProps.data.candidate.email }}</div>
                    </template>
                </Column>
                <Column field="job.title" header="Job Applied For" sortable></Column>
                <Column header="Status" style="width: 15%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)"
                            class="capitalize" :class="{ 'cursor-pointer': slotProps.data.status !== 'rejected' }"
                            @click="slotProps.data.status !== 'rejected' && editStatus(slotProps.data)" />
                    </template>
                </Column>
                <Column field="applied_at" header="Applied Date" sortable>
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.applied_at).toLocaleDateString() }}
                    </template>
                </Column>
                <Column header="CV / Resume">
                    <template #body="slotProps">
                        <div v-if="slotProps.data.resume_path">
                            <a :href="'/storage/' + slotProps.data.resume_path" target="_blank"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg font-semibold text-sm hover:bg-blue-100 transition-colors">
                                <i class="pi pi-file-pdf"></i>
                                View CV
                            </a>
                        </div>
                        <span v-else class="text-gray-400 italic">No CV uploaded</span>
                    </template>
                </Column>
                <Column header="Actions" style="width: 10%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View Details" @click="viewDetails(slotProps.data)">
                                <i class="pi pi-eye"></i>
                            </Button>
                            <Button
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit Status" @click="editStatus(slotProps.data)"
                                :disabled="slotProps.data.status === 'rejected'">
                                <i class="pi pi-file-edit"></i>
                            </Button>
                        </div>
                    </template>
                </Column>


            </DataTable>
        </div>

        <!-- Applicant Details Dialog -->
        <Dialog v-model:visible="detailDialog" :style="{ width: '500px' }" header="Applicant Details" :modal="true">
            <div v-if="selectedApplication" class="space-y-6 pt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">First Name</span>
                        <span class="text-gray-900 font-medium">{{ selectedApplication.candidate.first_name }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Name</span>
                        <span class="text-gray-900 font-medium">{{ selectedApplication.candidate.last_name }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</span>
                        <span class="text-gray-900 font-medium truncate">{{ selectedApplication.candidate.email
                        }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</span>
                        <span class="text-gray-900 font-medium">{{ selectedApplication.candidate.phone }}</span>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 rounded-xl space-y-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Job Posting</span>
                        <span class="text-[#1C0D82] font-bold">{{ selectedApplication.job.title }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Status</span>
                        <Tag :value="selectedApplication.status" :severity="getSeverity(selectedApplication.status)"
                            class="w-fit" />
                    </div>
                    <div class="flex flex-col gap-1" v-if="selectedApplication.resume_path">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">CV / Resume</span>
                        <a :href="'/storage/' + selectedApplication.resume_path" target="_blank"
                            class="inline-flex items-center gap-2 text-blue-600 hover:underline font-medium">
                            <i class="pi pi-file-pdf"></i>
                            Download Resume
                        </a>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end pt-2">
                    <Button label="Close" icon="pi pi-times"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                        @click="detailDialog = false" />
                </div>
            </template>
        </Dialog>

        <!-- Update Status Dialog -->

        <Dialog v-model:visible="statusDialog" :style="{ width: '400px' }" header="Update Application Status"
            :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label for="status" class="font-bold">Status</label>
                    <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                        optionValue="value" placeholder="Select Status" class="w-full" />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 mt-4">
                    <Button label="Cancel" icon="pi pi-times"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                        @click="statusDialog = false" />
                    <Button label="Update Status" icon="pi pi-check" @click="saveStatus" :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
                </div>
            </template>
        </Dialog>

        <!-- SweetAlert -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.row-rejected) {
    background-color: #fff1f2 !important;
    /* Very light red */
}

:deep(.row-hired) {
    background-color: #f0fdf4 !important;
    /* Very light success green */
}

:deep(.row-screening) {
    background-color: #fefce8 !important;
    /* Very light yellow */
}

:deep(.row-interview) {
    background-color: #fffaf0 !important;
    /* Very light orange */
}
</style>
