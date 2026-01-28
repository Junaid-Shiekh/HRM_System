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
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    loans: Array,
    employees: Array
});

const loanDialog = ref(false);
const repayDialog = ref(false);
const rejectDialog = ref(false);
const selectedLoan = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    employee_id: null,
    amount: 0,
    installments: 12,
    reason: ''
});

const repayForm = useForm({
    amount: 0,
    repayment_date: new Date().toISOString().split('T')[0],
    notes: ''
});

const rejectForm = useForm({
    rejection_reason: ''
});

const openNew = () => {
    form.reset();
    loanDialog.value = true;
};

const openRepay = (loan) => {
    selectedLoan.value = loan;
    repayForm.reset();
    repayForm.amount = loan.remaining_balance;
    repayDialog.value = true;
};

const openReject = (loan) => {
    selectedLoan.value = loan;
    rejectForm.reset();
    rejectDialog.value = true;
};

const handleReject = () => {
    rejectForm.post(route('loans.reject', selectedLoan.value.id), {
        onSuccess: () => {
            rejectDialog.value = false;
            showSuccess('Loan request rejected');
        }
    });
};

const handleStore = () => {
    form.post(route('loans.store'), {
        onSuccess: () => {
            loanDialog.value = false;
            showSuccess('Loan request created successfully');
        }
    });
};

const handleRepay = () => {
    repayForm.post(route('loans.repay', selectedLoan.value.id), {
        onSuccess: () => {
            repayDialog.value = false;
            showSuccess('Repayment processed successfully');
        }
    });
};

const approveLoan = (id) => {
    router.post(route('loans.approve', id), {}, {
        onSuccess: () => showSuccess('Loan approved successfully')
    });
};

const showSuccess = (msg) => {
    alertConfig.value = { title: 'Success', message: msg, type: 'success' };
    showAlert.value = true;
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'pending': return 'warning';
        case 'approved': return 'info';
        case 'paid': return 'success';
        case 'rejected': return 'danger';
        default: return 'info';
    }
};
</script>

<template>

    <Head title="Loan & Advances" />
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Loans & Salary Advances</h2>
            <!-- <Button label="New Loan Request" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" /> -->
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="loans" paginator :rows="10">
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
                        <span class="font-bold text-gray-700">${{ slotProps.data.amount }}</span>
                    </template>
                </Column>
                <Column field="installments" header="Inst." sortable></Column>
                <Column field="monthly_installment" header="Monthly Deduction">
                    <template #body="slotProps">
                        <span class="font-mono text-xs font-bold text-indigo-600">${{ slotProps.data.monthly_installment
                            }}</span>
                    </template>
                </Column>
                <Column field="remaining_balance" header="Remaining Balance">
                    <template #body="slotProps">
                        <span class="font-black"
                            :class="slotProps.data.remaining_balance > 0 ? 'text-red-500' : 'text-green-600'">
                            ${{ slotProps.data.remaining_balance }}
                        </span>
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)"
                            class="uppercase" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button v-if="slotProps.data.status === 'pending'"
                                class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Approve" @click="approveLoan(slotProps.data.id)">
                                <i class="pi pi-check"></i>
                            </Button>

                            <Button v-if="slotProps.data.status === 'pending'"
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Reject" @click="openReject(slotProps.data)">
                                <i class="pi pi-times"></i>
                            </Button>

                            <Button v-if="slotProps.data.status === 'approved' && slotProps.data.remaining_balance > 0"
                                class="!bg-indigo-100 !text-indigo-600 !border-indigo-100 hover:!bg-indigo-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Return Loan" v-tooltip.top="'Return Loan'"
                                @click="openRepay(slotProps.data)">
                                <i class="pi pi-undo text-xs"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Reject Modal -->
        <Dialog v-model:visible="rejectDialog" header="Reject Loan Request" modal :style="{ width: '400px' }"
            class="p-fluid">
            <div class="flex flex-col gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold text-red-600">Rejection Reason</label>
                    <textarea v-model="rejectForm.rejection_reason" rows="4"
                        class="w-full px-4 py-2 rounded-xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-indigo-500 transition-all"
                        placeholder="Explain why this request is rejected..."></textarea>
                    <small class="text-red-500" v-if="rejectForm.errors.rejection_reason">{{
                        rejectForm.errors.rejection_reason
                    }}</small>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="rejectDialog = false" />
                <Button label="Confirm Rejection" icon="pi pi-check" @click="handleReject"
                    :loading="rejectForm.processing" class="!bg-red-600 !border-red-600 text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <Dialog v-model:visible="loanDialog" header="New Loan Request" modal :style="{ width: '400px' }"
            class="p-fluid">
            <div class="flex flex-col gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Select Employee</label>
                    <Dropdown v-model="form.employee_id" :options="employees" optionLabel="name" optionValue="id"
                        filter />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Loan Amount</label>
                    <InputNumber v-model="form.amount" mode="decimal" minFractionDigits="2" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Total Installments</label>
                    <InputNumber v-model="form.installments" showButtons :min="1" />
                </div>
                <div v-if="form.amount > 0" class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Estimated Monthly
                        Deduction</div>
                    <div class="text-xl font-black text-indigo-900">${{ (form.amount / (form.installments ||
                        1)).toFixed(2) }}
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="loanDialog = false" />
                <Button label="Submit Request" icon="pi pi-send" @click="handleStore" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <Dialog v-model:visible="repayDialog" header="Return Loan Amount" modal :style="{ width: '400px' }"
            class="p-fluid">
            <div class="flex flex-col gap-4 mt-4" v-if="selectedLoan">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Loan</span>
                        <span class="font-bold text-gray-700">${{ selectedLoan.amount }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Outstanding</span>
                        <span class="font-black text-red-600">${{ selectedLoan.remaining_balance }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm">Amount to Return</label>
                    <InputNumber v-model="repayForm.amount" mode="decimal" minFractionDigits="2"
                        :max="selectedLoan.remaining_balance" />
                    <small v-if="repayForm.errors.amount" class="text-red-500 font-bold">{{ repayForm.errors.amount
                    }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm">Return Date</label>
                    <input type="date" v-model="repayForm.repayment_date"
                        class="w-full px-4 py-2 rounded-xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-indigo-500 transition-all font-mono font-bold" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-bold text-sm">Notes (Optional)</label>
                    <textarea v-model="repayForm.notes" rows="2"
                        class="w-full px-4 py-2 rounded-xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-indigo-500 transition-all"
                        placeholder="e.g. Returned in cash"></textarea>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="repayDialog = false" />
                <Button label="Process Return" icon="pi pi-check" @click="handleRepay" :loading="repayForm.processing"
                    class="!bg-green-600 !border-green-600 hover:!bg-green-700 text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>
