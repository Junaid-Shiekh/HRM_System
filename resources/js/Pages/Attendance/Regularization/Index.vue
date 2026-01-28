<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import SweetAlert from '@/Components/SweetAlert.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    requests: Array,
    employees: Array
});

const searchTerm = ref('');
const perPage = ref(10);
const showAddDialog = ref(false);
const isEdit = ref(false);

// SweetAlert state
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
    date: new Date().toISOString().substr(0, 10),
    requested_clock_in: '09:00',
    requested_clock_out: '18:00',
    reason: ''
});

const handleSearch = () => { };
const handleFilter = () => { };

const openNew = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showAddDialog.value = true;
};

const editRequest = (request) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = request.id;
    form.employee_id = request.employee_id;
    form.date = request.date;
    form.requested_clock_in = request.requested_clock_in.substring(0, 5);
    form.requested_clock_out = request.requested_clock_out.substring(0, 5);
    form.reason = request.reason;
    showAddDialog.value = true;
};

const saveRequest = () => {
    if (isEdit.value) {
        form.put(route('attendance-regularizations.update', form.id), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Updated!',
                    message: 'Regularization request has been updated successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    } else {
        form.post(route('attendance-regularizations.store'), {
            onSuccess: () => {
                showAddDialog.value = false;
                form.reset();
                alertConfig.value = {
                    title: 'Submitted!',
                    message: 'Regularization request has been submitted successfully.',
                    type: 'success',
                    showCancel: false
                };
                showAlert.value = true;
            }
        });
    }
};

const approveRequest = (id) => {
    router.post(route('attendance-regularizations.approve', id), {}, {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Approved!',
                message: 'Request has been approved and record updated.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const rejectRequest = (id) => {
    router.post(route('attendance-regularizations.reject', id), {}, {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Rejected!',
                message: 'Request has been rejected.',
                type: 'info',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const confirmDeleteRequest = (request) => {
    form.id = request.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete this request?`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteRequest = () => {
    form.delete(route('attendance-regularizations.destroy', form.id), {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Request has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteRequest();
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        default: return 'info';
    }
};

const pendingRequests = computed(() => props.requests.filter(r => r.status === 'pending'));
const approvedRequests = computed(() => props.requests.filter(r => r.status === 'approved'));
const rejectedRequests = computed(() => props.requests.filter(r => r.status === 'rejected'));
</script>

<template>

    <Head title="Attendance Regularization Management" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="mb-6 flex justify-between items-center px-4">
                <h2 class="text-2xl font-bold text-gray-800">Attendance Regularization</h2>
                <Button label="Add Request" icon="pi pi-plus" @click="openNew"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <SearchFilter v-model="searchTerm" v-model:perPage="perPage" @search="handleSearch"
                        @filter="handleFilter" />

                    <TabView>
                        <TabPanel header="Pending">
                            <DataTable :value="pendingRequests" responsiveLayout="scroll" class="p-datatable-sm">
                                <Column field="id" header="#" headerStyle="width: 3em"></Column>
                                <Column field="employee.name" header="Employee" sortable>
                                    <template #body="{ data }">
                                        <span class="font-bold text-gray-800">{{ data.employee?.name }}</span>
                                    </template>
                                </Column>
                                <Column field="date" header="Date"></Column>
                                <Column header="Original Times">
                                    <template #body="{ data }">
                                        <div class="text-[10px] text-gray-500">
                                            <div>In: <span class="text-rose-600 font-bold uppercase">{{
                                                data.original_clock_in || '--:--' }}</span></div>
                                            <div>Out: <span class="text-rose-600 font-bold uppercase">{{
                                                data.original_clock_out || '--:--' }}</span></div>
                                        </div>
                                    </template>
                                </Column>
                                <Column header="Requested Times">
                                    <template #body="{ data }">
                                        <div class="text-[10px] text-gray-500">
                                            <div>In: <span class="text-emerald-600 font-bold uppercase">{{
                                                data.requested_clock_in }}</span></div>
                                            <div>Out: <span class="text-emerald-600 font-bold uppercase">{{
                                                data.requested_clock_out }}</span></div>
                                        </div>
                                    </template>
                                </Column>
                                <Column field="reason" header="Reason" class="max-w-[200px]"></Column>
                                <Column field="status" header="Status">
                                    <template #body="{ data }">
                                        <Tag :value="data.status.toUpperCase()"
                                            :severity="getStatusSeverity(data.status)" />
                                    </template>
                                </Column>
                                <Column field="created_at" header="Requested On"></Column>
                                <Column header="Actions">
                                    <template #body="{ data }">
                                        <div class="flex items-center gap-2">
                                            <Button @click="approveRequest(data.id)"
                                                class="!bg-emerald-100 !text-emerald-600 !border-emerald-100 hover:!bg-emerald-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                                rounded aria-label="Approve">
                                                <i class="pi pi-check"></i>
                                            </Button>
                                            <Button @click="rejectRequest(data.id)"
                                                class="!bg-rose-100 !text-rose-600 !border-rose-100 hover:!bg-rose-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                                rounded aria-label="Reject">
                                                <i class="pi pi-times"></i>
                                            </Button>
                                            <Button
                                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                                rounded aria-label="Edit" @click="editRequest(data)">
                                                <i class="pi pi-pencil"></i>
                                            </Button>
                                            <Button
                                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                                rounded aria-label="Delete" @click="confirmDeleteRequest(data)">
                                                <i class="pi pi-trash"></i>
                                            </Button>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Approved">
                            <DataTable :value="approvedRequests" responsiveLayout="scroll" class="p-datatable-sm">
                                <Column field="id" header="#" headerStyle="width: 3em"></Column>
                                <Column field="employee.name" header="Employee">
                                    <template #body="{ data }">
                                        <span class="font-bold text-gray-800">{{ data.employee?.name }}</span>
                                    </template>
                                </Column>
                                <Column field="date" header="Date"></Column>
                                <Column header="Requested Times">
                                    <template #body="{ data }">
                                        <div class="text-[10px]">
                                            <div>In: <span class="text-emerald-600 font-bold uppercase">{{
                                                data.requested_clock_in }}</span></div>
                                            <div>Out: <span class="text-emerald-600 font-bold uppercase">{{
                                                data.requested_clock_out }}</span></div>
                                        </div>
                                    </template>
                                </Column>
                                <Column field="status" header="Status">
                                    <template #body="{ data }">
                                        <Tag :value="data.status.toUpperCase()"
                                            :severity="getStatusSeverity(data.status)" />
                                    </template>
                                </Column>
                                <Column field="approver.name" header="Approved By"></Column>
                                <Column field="updated_at" header="Approved On"></Column>
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Rejected">
                            <DataTable :value="rejectedRequests" responsiveLayout="scroll" class="p-datatable-sm">
                                <Column field="id" header="#" headerStyle="width: 3em"></Column>
                                <Column field="employee.name" header="Employee">
                                    <template #body="{ data }">
                                        <span class="font-bold text-gray-800">{{ data.employee?.name }}</span>
                                    </template>
                                </Column>
                                <Column field="date" header="Date"></Column>
                                <Column field="reason" header="Reason"></Column>
                                <Column field="status" header="Status">
                                    <template #body="{ data }">
                                        <Tag :value="data.status.toUpperCase()"
                                            :severity="getStatusSeverity(data.status)" />
                                    </template>
                                </Column>
                                <Column field="approver.name" header="Rejected By"></Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
        </div>

        <Dialog v-model:visible="showAddDialog"
            :header="isEdit ? 'Edit Regularization Request' : 'Add Regularization Request'" :style="{ width: '500px' }"
            modal class="p-fluid">
            <div class="flex flex-col gap-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Employee</label>
                    <Dropdown v-model="form.employee_id" :options="employees" optionLabel="name" optionValue="id"
                        placeholder="Select Employee" filter class="w-full" />
                    <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Date</label>
                    <InputText v-model="form.date" type="date" />
                    <small class="text-red-500" v-if="form.errors.date">{{ form.errors.date }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Requested Clock In</label>
                        <InputText v-model="form.requested_clock_in" type="time" />
                        <small class="text-red-500" v-if="form.errors.requested_clock_in">{{
                            form.errors.requested_clock_in
                        }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold">Requested Clock Out</label>
                        <InputText v-model="form.requested_clock_out" type="time" />
                        <small class="text-red-500" v-if="form.errors.requested_clock_out">{{
                            form.errors.requested_clock_out
                        }}</small>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Reason</label>
                    <InputText v-model="form.reason" placeholder="e.g. Forgot to punch in..." />
                    <small class="text-red-500" v-if="form.errors.reason">{{ form.errors.reason }}</small>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 mt-4">
                    <Button label="Cancel" icon="pi pi-times" @click="showAddDialog = false"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200" />
                    <Button :label="isEdit ? 'Update Request' : 'Submit Request'" icon="pi pi-check"
                        @click="saveRequest" :loading="form.processing"
                        class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
                </div>
            </template>
        </Dialog>

        <!-- SweetAlert -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" :showCancel="alertConfig.showCancel" :confirmText="alertConfig.confirmText || 'OK'"
            :cancelText="alertConfig.cancelText || 'Cancel'" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>

<style>
.p-tabview-nav-link {
    @apply !font-bold !text-xs !uppercase !tracking-wider;
}
</style>
