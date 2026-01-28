<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import SearchFilter from '@/Components/SearchFilter.vue';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    complaints: Object,
    filters: Object,
    isHR: Boolean
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);
const statusFilter = ref(props.filters.status || null);
const statusOptions = [
    { label: 'All Statuses', value: null },
    { label: 'Pending', value: 'pending' },
    { label: 'In Progress', value: 'progress' },
    { label: 'Done', value: 'done' }
];

const complaintDialog = ref(false);
const statusDialog = ref(false);
const viewDialog = ref(false);
const selectedComplaint = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    title: '',
    description: ''
});

const statusForm = useForm({
    status: '',
    resolution_note: ''
});

const openNewComplaint = () => {
    form.reset();
    complaintDialog.value = true;
};

const submitComplaint = () => {
    form.post(route('complaints.store'), {
        onSuccess: () => {
            complaintDialog.value = false;
            alertConfig.value = { title: 'Success!', message: 'Complaint submitted successfully.', type: 'success' };
            showAlert.value = true;
        }
    });
};

const openStatusDialog = (complaint) => {
    selectedComplaint.value = complaint;
    statusForm.status = complaint.status;
    statusForm.resolution_note = complaint.resolution_note || '';
    statusDialog.value = true;
};

const updateStatus = () => {
    statusForm.put(route('complaints.update', selectedComplaint.value.id), {
        onSuccess: () => {
            statusDialog.value = false;
            alertConfig.value = { title: 'Updated!', message: 'Complaint status has been updated.', type: 'success' };
            showAlert.value = true;
        }
    });
};

const quickUpdateStatus = (complaint, newStatus) => {
    router.put(route('complaints.update', complaint.id), {
        status: newStatus,
        resolution_note: complaint.resolution_note
    }, {
        onSuccess: () => {
            alertConfig.value = { title: 'Updated!', message: `Status changed to ${newStatus}.`, type: 'success' };
            showAlert.value = true;
        }
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'done': return 'success';
        case 'progress': return 'warn';
        case 'pending': return 'info';
        default: return null;
    }
};

const openViewComplaint = (complaint) => {
    selectedComplaint.value = complaint;
    viewDialog.value = true;
};

const handleSearch = () => {
    router.get(route('complaints.index'), {
        search: searchTerm.value,
        status: statusFilter.value,
        perPage: perPage.value
    }, { preserveState: true, replace: true });
};

watch([perPage, statusFilter], handleSearch);

watch(searchTerm, (newVal) => {
    if (!newVal || newVal.trim() === '') {
        handleSearch();
    }
});

</script>

<template>

    <Head title="Complaints Management" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Complaints Management</h1>
                <Button v-if="!isHR" label="New Complaint" icon="pi pi-plus"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5"
                    @click="openNewComplaint" />
            </div>

            <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search complaints..."
                @search="handleSearch" :showFilter="true" @filter="() => { }" />

            <div v-if="statusFilter" class="mb-4 flex gap-2">
                <Tag v-if="statusFilter" :value="'Status: ' + statusFilter.toUpperCase()" closable
                    @close="statusFilter = null" />
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <DataTable :value="complaints.data" responsiveLayout="scroll">
                    <Column v-if="isHR" header="Employee">
                        <template #body="slotProps">
                            {{ slotProps.data.employee?.first_name }} {{ slotProps.data.employee?.last_name }}
                        </template>
                    </Column>
                    <Column field="title" header="Subject" class="font-bold"></Column>
                    <Column field="description" header="Description">
                        <template #body="slotProps">
                            <span class="text-gray-500 whitespace-nowrap overflow-hidden text-ellipsis max-w-xs block">
                                {{ slotProps.data.description }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Status">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.status.toUpperCase()"
                                :severity="getStatusSeverity(slotProps.data.status)" />
                        </template>
                    </Column>
                    <Column header="Date">
                        <template #body="slotProps">
                            {{ new Date(slotProps.data.created_at).toLocaleDateString() }}
                        </template>
                    </Column>
                    <Column header="Actions" alignFrozen="right" frozen style="width: 15%">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <template v-if="isHR">
                                    <Button v-if="slotProps.data.status === 'pending'"
                                        class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        v-tooltip="'Mark as In Progress'"
                                        @click="quickUpdateStatus(slotProps.data, 'progress')">
                                        <i class="pi pi-spinner text-lg"></i>
                                    </Button>
                                    <Button v-if="slotProps.data.status !== 'done'"
                                        class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        v-tooltip="'Mark as Done'" @click="quickUpdateStatus(slotProps.data, 'done')">
                                        <i class="pi pi-check-circle text-lg"></i>
                                    </Button>
                                    <Button
                                        class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        v-tooltip="'Edit Details/Status'" @click="openStatusDialog(slotProps.data)">
                                        <i class="pi pi-cog text-lg"></i>
                                    </Button>
                                </template>
                                <Button
                                    class="!bg-gray-100 !text-gray-600 !border-gray-100 hover:!bg-gray-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    v-tooltip="'View Details'" @click="openViewComplaint(slotProps.data)">
                                    <i class="pi pi-eye text-lg"></i>
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- New Complaint Dialog -->
        <Dialog v-model:visible="complaintDialog" header="Submit a New Complaint" :style="{ width: '500px' }"
            :modal="true" class="p-fluid">
            <div class="flex flex-col gap-6 p-1">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Subject</label>
                    <InputText v-model="form.title" placeholder="Summary of the issue"
                        class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Description</label>
                    <Textarea v-model="form.description" rows="5" placeholder="Detailed explanation"
                        class="!w-full !border !border-gray-300 !rounded-md !resize-none" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" @click="complaintDialog = false"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                <Button label="Submit" icon="pi pi-check" @click="submitComplaint" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- Status Update Dialog (HR only) -->
        <Dialog v-model:visible="statusDialog" header="Update Complaint Status" :style="{ width: '500px' }"
            :modal="true" class="p-fluid">
            <div class="flex flex-col gap-6 p-1">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Status</label>
                    <Dropdown v-model="statusForm.status" :options="statusOptions.filter(o => o.value !== null)"
                        optionLabel="label" optionValue="value" class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Resolution Note</label>
                    <Textarea v-model="statusForm.resolution_note" rows="3"
                        placeholder="Notes on progress or resolution"
                        class="!w-full !border !border-gray-300 !rounded-md !resize-none" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" @click="statusDialog = false"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                <Button label="Update Status" icon="pi pi-check" @click="updateStatus" :loading="statusForm.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Complaint Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Complaint Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="selectedComplaint">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Subject</label>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-lg font-bold text-[#1C0D82]">
                        {{ selectedComplaint.title }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Submitted On</label>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 font-medium">
                            {{ new Date(selectedComplaint.created_at).toLocaleDateString() }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Status</label>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <Tag :value="selectedComplaint.status.toUpperCase()"
                                :severity="getStatusSeverity(selectedComplaint.status)" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Description</label>
                    <div
                        class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-gray-600 text-sm whitespace-pre-wrap min-h-[100px]">
                        {{ selectedComplaint.description }}
                    </div>
                </div>

                <div class="flex flex-col gap-1" v-if="selectedComplaint.resolution_note">
                    <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">HR Resolution Note</label>
                    <div
                        class="p-3 bg-green-50 rounded-xl border border-green-100 text-green-800 text-sm whitespace-pre-wrap">
                        {{ selectedComplaint.resolution_note }}
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times" @click="viewDialog = false"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>
