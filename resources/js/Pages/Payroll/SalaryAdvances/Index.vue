<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    advances: Array,
    employees: Array
});

const advanceDialog = ref(false);
const rejectDialog = ref(false);
const selectedItem = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    employee_id: null,
    amount: null,
    reason: '',
    repayment_date: ''
});

const rejectForm = useForm({
    rejection_reason: ''
});

const openNew = () => {
    form.reset();
    advanceDialog.value = true;
};

const openReject = (advance) => {
    selectedItem.value = advance;
    rejectForm.reset();
    rejectDialog.value = true;
};

const handleStore = () => {
    form.post(route('salary-advances.store'), {
        onSuccess: () => {
            advanceDialog.value = false;
            showSuccess('Salary advance request created successfully');
        }
    });
};

const approveAdvance = (id) => {
    router.post(route('salary-advances.approve', id), {}, {
        onSuccess: () => showSuccess('Salary advance approved successfully')
    });
};

const handleReject = () => {
    rejectForm.post(route('salary-advances.reject', selectedItem.value.id), {
        onSuccess: () => {
            rejectDialog.value = false;
            showSuccess('Salary advance rejected');
        }
    });
};

const showSuccess = (msg) => {
    alertConfig.value = { title: 'Success', message: msg, type: 'success' };
    showAlert.value = true;
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'info';
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};
</script>

<template>

    <Head title="Salary Advances" />
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Salary Advances</h2>
            <Button label="New Advance Request" icon="pi pi-plus" @click="openNew"
                class="!bg-fuchsia-600 !border-fuchsia-600 hover:!bg-fuchsia-700 text-white !px-6 !py-2.5" />
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="advances" paginator :rows="10">
                <Column header="Employee" sortable>
                    <template #body="slotProps">
                        <div class="font-bold text-gray-800">{{ slotProps.data.employee.name }}</div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{
                            slotProps.data.employee.employee_id }}
                        </div>
                    </template>
                </Column>
                <Column field="amount" header="Amount" sortable>
                    <template #body="slotProps">
                        <span class="font-bold text-gray-700">{{ formatCurrency(slotProps.data.amount) }}</span>
                    </template>
                </Column>
                <Column field="repayment_date" header="Repayment Month" sortable></Column>
                <Column field="status" header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status.toUpperCase()"
                            :severity="getStatusSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button v-if="slotProps.data.status === 'pending'"
                                class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                v-tooltip="'Approve'" @click="approveAdvance(slotProps.data.id)">
                                <i class="pi pi-check"></i>
                            </Button>
                            <Button v-if="slotProps.data.status === 'pending'"
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                v-tooltip="'Reject'" @click="openReject(slotProps.data)">
                                <i class="pi pi-times"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- New Request Modal -->
        <Dialog v-model:visible="advanceDialog" header="New Salary Advance Request" modal :style="{ width: '450px' }"
            class="p-fluid">
            <div class="flex flex-col gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Select Employee</label>
                    <Dropdown v-model="form.employee_id" :options="employees" optionLabel="name" optionValue="id"
                        filter />
                    <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Amount</label>
                    <InputNumber v-model="form.amount" mode="currency" currency="USD" locale="en-US" />
                    <small class="text-red-500" v-if="form.errors.amount">{{ form.errors.amount }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Repayment Month</label>
                    <input type="date" v-model="form.repayment_date"
                        class="w-full px-4 py-2 rounded-lg border border-gray-100 bg-gray-50 font-bold" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Reason</label>
                    <Textarea v-model="form.reason" rows="3" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="advanceDialog = false" />
                <Button label="Submit Request" icon="pi pi-send" @click="handleStore" :loading="form.processing"
                    class="!bg-fuchsia-600 !border-fuchsia-600 text-white" />
            </template>
        </Dialog>

        <!-- Reject Modal -->
        <Dialog v-model:visible="rejectDialog" header="Reject Request" modal :style="{ width: '400px' }"
            class="p-fluid">
            <div class="flex flex-col gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-red-600">Rejection Reason</label>
                    <Textarea v-model="rejectForm.rejection_reason" rows="4"
                        placeholder="Explain why this request is rejected..." />
                    <small class="text-red-500" v-if="rejectForm.errors.rejection_reason">{{
                        rejectForm.errors.rejection_reason
                        }}</small>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="rejectDialog = false" />
                <Button label="Confirm Rejection" icon="pi pi-check" @click="handleReject"
                    :loading="rejectForm.processing" class="!bg-red-600 !border-red-600 text-white" />
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>
