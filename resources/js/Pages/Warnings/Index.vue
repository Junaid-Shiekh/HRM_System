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
import Calendar from 'primevue/calendar';
import SearchFilter from '@/Components/SearchFilter.vue';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    warnings: Object,
    filters: Object,
    employees: Array,
    isHR: Boolean
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);
const statusFilter = ref(props.filters.status || null);
const statusOptions = [
    { label: 'All Statuses', value: null },
    { label: 'Pending', value: 'pending' },
    { label: 'Readed', value: 'read' }
];

const warningDialog = ref(false);
const editDialog = ref(false);
const viewDialog = ref(false);
const selectedWarning = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    employee_id: null,
    title: '',
    description: '',
    warning_date: new Date(),
});

const editForm = useForm({
    employee_id: null,
    title: '',
    description: '',
    warning_date: null,
    status: '',
});

const openNewWarning = () => {
    form.reset();
    form.warning_date = new Date();
    warningDialog.value = true;
};

const submitWarning = () => {
    // Format date to YYYY-MM-DD locally
    const d = form.warning_date;
    const formattedDate = d ? `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}` : null;

    form.transform((data) => ({
        ...data,
        warning_date: formattedDate
    })).post(route('warnings.store'), {
        onSuccess: () => {
            warningDialog.value = false;
            alertConfig.value = { title: 'Success!', message: 'Warning issued successfully.', type: 'success' };
            showAlert.value = true;
        }
    });
};

const openEditDialog = (warning) => {
    selectedWarning.value = warning;
    editForm.employee_id = warning.employee_id;
    editForm.title = warning.title;
    editForm.description = warning.description;
    editForm.warning_date = new Date(warning.warning_date);
    editForm.status = warning.status;
    editDialog.value = true;
};

const updateWarning = () => {
    // Format date to YYYY-MM-DD locally
    const d = editForm.warning_date;
    const formattedDate = d ? `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}` : null;

    editForm.transform((data) => ({
        ...data,
        warning_date: formattedDate
    })).put(route('warnings.update', selectedWarning.value.id), {
        onSuccess: () => {
            editDialog.value = false;
            alertConfig.value = { title: 'Updated!', message: 'Warning has been updated.', type: 'success' };
            showAlert.value = true;
        }
    });
};

const markAsRead = (warning) => {
    router.patch(route('warnings.mark-as-read', warning.id), {}, {
        onSuccess: () => {
            alertConfig.value = { title: 'Read!', message: 'Warning marked as read.', type: 'success' };
            showAlert.value = true;
        }
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'read': return 'success';
        case 'pending': return 'info';
        default: return null;
    }
};

const openViewWarning = (warning) => {
    selectedWarning.value = warning;
    viewDialog.value = true;
};

const handleSearch = () => {
    router.get(route('warnings.index'), {
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

    <Head title="Employee Warnings" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Employee Warnings</h1>
                <Button v-if="isHR" label="New Warning" icon="pi pi-plus"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5"
                    @click="openNewWarning" />
            </div>

            <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search warnings..."
                @search="handleSearch" :showFilter="true" @filter="() => { }" />

            <div v-if="statusFilter" class="mb-4 flex gap-2">
                <Tag v-if="statusFilter" :value="'Status: ' + statusFilter.toUpperCase()" closable
                    @close="statusFilter = null" />
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <DataTable :value="warnings.data" responsiveLayout="scroll">
                    <Column v-if="isHR" header="Employee">
                        <template #body="slotProps">
                            {{ slotProps.data.employee?.first_name }} {{ slotProps.data.employee?.last_name }}
                        </template>
                    </Column>
                    <Column field="title" header="Title" class="font-bold"></Column>
                    <Column field="warning_date" header="Date">
                        <template #body="slotProps">
                            {{ slotProps.data.warning_date }}
                        </template>
                    </Column>
                    <Column header="Status">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.status === 'read' ? 'READED' : 'PENDING'"
                                :severity="getStatusSeverity(slotProps.data.status)" />
                        </template>
                    </Column>
                    <Column header="Actions" alignFrozen="right" frozen style="width: 15%">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <template v-if="isHR">
                                    <Button
                                        class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        v-tooltip="'Edit Warning'" @click="openEditDialog(slotProps.data)">
                                        <i class="pi pi-cog text-lg"></i>
                                    </Button>
                                </template>
                                <template v-if="!isHR && slotProps.data.status === 'pending'">
                                    <Button
                                        class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                        v-tooltip="'Mark as Done'" @click="markAsRead(slotProps.data)">
                                        <i class="pi pi-check text-lg"></i>
                                    </Button>
                                </template>
                                <Button
                                    class="!bg-gray-100 !text-gray-600 !border-gray-100 hover:!bg-gray-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    v-tooltip="'View Details'" @click="openViewWarning(slotProps.data)">
                                    <i class="pi pi-eye text-lg"></i>
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- New Warning Dialog -->
        <Dialog v-model:visible="warningDialog" header="Issue New Warning" :style="{ width: '500px' }" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-6 p-1">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Select Employee</label>
                    <Dropdown v-model="form.employee_id" :options="employees"
                        :optionLabel="(e) => e.first_name + ' ' + e.last_name" optionValue="id"
                        placeholder="Choose employee" filter class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Warning Title</label>
                    <InputText v-model="form.title" placeholder="Summary of the warning"
                        class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Warning Date</label>
                    <Calendar v-model="form.warning_date" dateFormat="yy-mm-dd" showIcon
                        class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Description</label>
                    <Textarea v-model="form.description" rows="5" placeholder="Detailed explanation"
                        class="!w-full !border !border-gray-300 !rounded-md !resize-none" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" @click="warningDialog = false"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                <Button label="Issue Warning" icon="pi pi-check" @click="submitWarning" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- Edit Warning Dialog (HR only) -->
        <Dialog v-model:visible="editDialog" header="Update Warning Details" :style="{ width: '500px' }" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-6 p-1">
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Employee</label>
                    <Dropdown v-model="editForm.employee_id" :options="employees"
                        :optionLabel="(e) => e.first_name + ' ' + e.last_name" optionValue="id"
                        placeholder="Choose employee" filter class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Title</label>
                    <InputText v-model="editForm.title" placeholder="Summary of the warning"
                        class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Warning Date</label>
                    <Calendar v-model="editForm.warning_date" dateFormat="yy-mm-dd" showIcon
                        class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Status</label>
                    <Dropdown v-model="editForm.status" :options="statusOptions.filter(o => o.value !== null)"
                        optionLabel="label" optionValue="value" class="!w-full !border !border-gray-300 !rounded-md" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-gray-700">Description</label>
                    <Textarea v-model="editForm.description" rows="5" placeholder="Detailed explanation"
                        class="!w-full !border !border-gray-300 !rounded-md !resize-none" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" @click="editDialog = false"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                <Button label="Update Warning" icon="pi pi-check" @click="updateWarning" :loading="editForm.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Warning Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Warning Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="selectedWarning">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Title</label>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-lg font-bold text-[#1C0D82]">
                        {{ selectedWarning.title }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Warning Date</label>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 font-medium">
                            {{ selectedWarning.warning_date }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Status</label>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <Tag :value="selectedWarning.status === 'read' ? 'READED' : 'PENDING'"
                                :severity="getStatusSeverity(selectedWarning.status)" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Description</label>
                    <div
                        class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-gray-600 text-sm whitespace-pre-wrap min-h-[100px]">
                        {{ selectedWarning.description }}
                    </div>
                </div>

                <div v-if="isHR" class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500 text-xs uppercase tracking-wider">Assigned Employee</label>
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 font-medium text-gray-700">
                        {{ selectedWarning.employee?.first_name }} {{ selectedWarning.employee?.last_name }}
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
