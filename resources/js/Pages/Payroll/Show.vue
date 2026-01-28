<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    run: Object
});

const viewDialog = ref(false);
const selectedItem = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });
const approvePayroll = () => {
    router.post(route('payrolls.approve', props.run.id), {}, {
        onSuccess: () => showSuccessAlert('Payroll approved and locked for payment.')
    });
};

const printPayslip = () => {
    window.print();
};

const downloadPDF = (item) => {
    window.location.href = route('payrolls.items.download', {
        payroll: props.run.id,
        item: item.id
    });
};

const markPaid = (item) => {
    router.post(route('payrolls.items.mark-paid', {
        payroll: props.run.id,
        item: item.id
    }), {}, {
        onSuccess: () => {
            showSuccessAlert('Payment confirmed and payslip sent to ' + item.employee.first_name);
        }
    });
};

const openDetails = (item) => {
    selectedItem.value = item;
    viewDialog.value = true;
};

const formatCurrency = (value) => {
    return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const showSuccessAlert = (message) => {
    alertConfig.value = { title: 'Success', message: message, type: 'success' };
    showAlert.value = true;
};

const totals = computed(() => {
    return props.run.items.reduce((acc, item) => {
        acc.gross += parseFloat(item.base_salary) + parseFloat(item.total_allowances);
        acc.deductions += parseFloat(item.total_deductions) + parseFloat(item.loan_deduction);
        acc.net += parseFloat(item.net_salary);
        return acc;
    }, { gross: 0, deductions: 0, net: 0 });
});

const getSalaryRowClass = (data) => {
    const net = parseFloat(data.net_salary);
    if (net > 100000) return 'salary-high';
    if (net >= 60000) return 'salary-mid';
    return 'salary-low';
};
</script>

<template>

    <Head title="Payroll Breakdown" />
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-end no-print">
            <div>
                <nav class="flex text-xs font-bold text-gray-400 gap-2 mb-2 uppercase tracking-widest">
                    <Link :href="route('payrolls.index')">Payrolls</Link>
                    <span>/</span>
                    <span class="text-gray-600">Breakdown</span>
                </nav>
                <h2 class="text-3xl font-black text-gray-900 capitalize">
                    Breakdown: {{ run.month }}/{{ run.year }}
                </h2>
            </div>

            <div class="flex gap-4">
                <Button v-if="run.status === 'draft' || run.status === 'pending_approval'" label="Approve Payroll" icon="pi pi-check-circle" @click="approvePayroll"
                    class="!bg-green-600 !border-green-600 hover:!bg-green-700 text-white !px-6 !py-2.5" />
                
                <div v-if="run.status === 'approved' || run.status === 'paid'" class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-xl border border-green-100">
                    <i class="pi pi-verified text-green-600"></i>
                    <span class="text-xs font-black text-green-700 uppercase tracking-widest">Payroll Approved</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-6 mb-8 no-print">
            <div class="bg-white border p-6 rounded-2xl shadow-sm">
                <div class="text-xs font-bold uppercase text-gray-400 mb-1 tracking-wider">Status</div>
                <Tag :value="run.status.replace('_', ' ')" :severity="run.status === 'draft' ? 'secondary' : (run.status === 'paid' ? 'success' : 'info')"
                    class="uppercase !font-black !px-3 !py-1" />
            </div>
            <div class="bg-white border p-6 rounded-2xl shadow-sm">
                <div class="text-xs font-bold uppercase text-gray-400 mb-1 tracking-wider">Total Gross</div>
                <div class="text-2xl font-black text-gray-800">${{ totals.gross.toFixed(2) }}</div>
            </div>
            <div class="bg-white border p-6 rounded-2xl shadow-sm">
                <div class="text-xs font-bold uppercase text-red-400 mb-1 tracking-wider">Total Deductions</div>
                <div class="text-2xl font-black text-red-600">${{ totals.deductions.toFixed(2) }}</div>
            </div>
            <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-2xl shadow-sm">
                <div class="text-xs font-bold uppercase text-indigo-400 mb-1 tracking-wider">Net Disbursement</div>
                <div class="text-2xl font-black text-indigo-900">${{ totals.net.toFixed(2) }}</div>
            </div>
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100 overflow-hidden no-print">
            <DataTable :value="run.items" :rowClass="getSalaryRowClass" removableSort paginator :rows="10"
                tableStyle="min-width: 50rem" class="p-datatable-sm custom-payroll-table">
                <Column field="employee.first_name" header="Employee" sortable>
                    <template #body="slotProps">
                        <div class="flex flex-col">
                            <span class="font-black text-gray-800">{{ slotProps.data.employee.first_name }} {{
                                slotProps.data.employee.last_name }}</span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{
                                slotProps.data.employee.department?.name }}</span>
                        </div>
                    </template>
                </Column>
                <Column field="base_salary" header="Base" sortable>
                    <template #body="slotProps">
                        <span class="font-mono font-bold text-gray-500">${{ formatCurrency(slotProps.data.base_salary)
                        }}</span>
                    </template>
                </Column>
                <Column field="total_allowances" header="Allowances" class="text-green-600">
                    <template #body="slotProps">
                        <span class="text-xs font-black">+${{ formatCurrency(slotProps.data.total_allowances) }}</span>
                    </template>
                </Column>
                <Column field="total_deductions" header="Deductions" class="text-red-500">
                    <template #body="slotProps">
                        <span class="text-xs font-black">-${{ formatCurrency(parseFloat(slotProps.data.total_deductions)
                            +
                            parseFloat(slotProps.data.loan_deduction)) }}</span>
                    </template>
                </Column>
                <Column field="net_salary" header="Net Payable" sortable>
                    <template #body="slotProps">
                        <span class="font-mono font-black text-indigo-700">${{ formatCurrency(slotProps.data.net_salary)
                            }}</span>
                    </template>
                </Column>
                <Column field="status" header="Status" sortable>
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status === 'paid' ? 'PAID' : 'PENDING'"
                            :severity="slotProps.data.status === 'paid' ? 'success' : 'warn'"
                            class="!text-[9px] !font-black !px-2 !py-0.5 !rounded-full shadow-sm" />
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button v-if="run.status !== 'draft'"
                                class="!bg-indigo-100 !text-indigo-600 !border-indigo-100 hover:!bg-indigo-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View Details" @click="openDetails(slotProps.data)">
                                <i class="pi pi-file-pdf"></i>
                            </Button>

                            <template v-if="run.status === 'approved' || run.status === 'paid'">
                                <Button v-if="slotProps.data.status !== 'paid'"
                                    class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-8 !h-8 !p-0 !min-w-[32px] flex items-center justify-center p-button-icon-only shadow-sm"
                                    rounded aria-label="Mark as Paid" @click="markPaid(slotProps.data)">
                                    <i class="pi pi-check text-xs"></i>
                                </Button>

                                <div v-else
                                    class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-md animate-in fade-in zoom-in duration-300"
                                    title="Paid">
                                    <i class="pi pi-check-circle text-xs"></i>
                                </div>
                            </template>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>


        <!-- Payslip Modal -->
        <Dialog v-model:visible="viewDialog" header="Employee Payslip Preview" modal :style="{ width: '700px' }">
            <div id="payslip-content" class="p-8 bg-white rounded-xl" v-if="selectedItem">
                <div class="flex justify-between items-start mb-10 border-b-2 pb-8 border-slate-100">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-10 h-10 bg-[#1C0D82] rounded-lg flex items-center justify-center">
                                <i class="pi pi-bolt text-white"></i>
                            </div>
                            <span class="font-black text-xl tracking-tighter">GORITMI <span
                                    class="text-[#1C0D82]">HRMS</span></span>
                        </div>
                        <h2 class="text-3xl font-black text-[#1C0D82] mt-4">PAYSLIP</h2>
                        <div class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em]">{{ run.month }}/{{
                            run.year }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold uppercase text-[10px] text-slate-400 tracking-widest mb-1">Employee
                            Details</div>
                        <div class="font-black text-xl text-gray-800">{{ selectedItem.employee.first_name }} {{
                            selectedItem.employee.last_name }}</div>
                        <div class="text-xs text-indigo-600 font-black uppercase mb-1">{{
                            selectedItem.employee.employee_id }}
                        </div>
                        <div class="text-[10px] text-gray-400 font-bold capitalize">{{
                            selectedItem.employee.department?.name }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-12 mb-10">
                    <div>
                        <h4
                            class="font-black text-xs uppercase mb-4 text-green-600 tracking-widest border-b pb-2 flex justify-between">
                            <span>Earnings</span>
                            <span>Amount</span>
                        </h4>
                        <div class="flex justify-between text-sm py-2 border-b border-slate-50">
                            <span class="text-gray-500 font-medium">Basic Salary</span>
                            <span class="font-mono font-bold text-gray-800">${{ formatCurrency(selectedItem.base_salary)
                                }}</span>
                        </div>
                        <div v-for="allow in selectedItem.calculation_snapshot.allowances" :key="allow.name"
                            class="flex justify-between text-sm py-2 border-b border-slate-50">
                            <span class="text-gray-600 font-medium">{{ allow.name }}</span>
                            <span class="font-mono font-bold text-green-600">+${{ formatCurrency(allow.amount) }}</span>
                        </div>
                    </div>
                    <div>
                        <h4
                            class="font-black text-xs uppercase mb-4 text-red-500 tracking-widest border-b pb-2 flex justify-between">
                            <span>Deductions</span>
                            <span>Amount</span>
                        </h4>
                        <div v-for="dd in selectedItem.calculation_snapshot.deductions" :key="dd.name"
                            class="flex justify-between text-sm py-2 border-b border-slate-50">
                            <span class="text-gray-600 font-medium">{{ dd.name }}</span>
                            <span class="font-mono font-bold text-red-600">-${{ formatCurrency(dd.amount) }}</span>
                        </div>
                        <div v-for="loan in selectedItem.calculation_snapshot.loans" :key="loan.id"
                            class="flex justify-between text-sm py-2 border-b border-slate-50">
                            <span class="text-gray-600 font-medium">Loan Installment</span>
                            <span class="font-mono font-bold text-red-600">-${{ formatCurrency(loan.amount) }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8 mb-10">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Payment
                            Destination
                        </div>
                        <div v-if="selectedItem.employee.salary_profile?.payment_method === 'bank'">
                            <div class="flex justify-between mb-1">
                                <span class="text-[10px] text-slate-500 uppercase font-bold">Bank</span>
                                <span class="text-xs font-black">{{ selectedItem.employee.salary_profile.bank_name
                                    }}</span>
                            </div>
                            <div class="flex justify-between mb-1">
                                <span class="text-[10px] text-slate-500 uppercase font-bold">A/C Name</span>
                                <span class="text-xs font-black">{{ selectedItem.employee.salary_profile.account_name
                                    }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[10px] text-slate-500 uppercase font-bold">A/C No</span>
                                <span class="text-xs font-black font-mono">{{
                                    selectedItem.employee.salary_profile.account_number }}</span>
                            </div>
                        </div>
                        <div v-else class="text-xs font-black text-slate-600 uppercase italic">
                            {{ selectedItem.employee.salary_profile?.payment_method || 'CASH PAYMENT' }}
                        </div>
                    </div>
                    <div
                        class="bg-[#1C0D82] text-white p-6 rounded-2xl flex flex-col justify-center items-end shadow-xl relative overflow-hidden">
                        <div class="absolute -left-4 top-0 opacity-10 text-6xl">
                            <i class="pi pi-wallet"></i>
                        </div>
                        <span class="font-bold text-[10px] uppercase tracking-[0.3em] mb-1 opacity-60">Net
                            Disbursed</span>
                        <span class="text-3xl font-black">${{ formatCurrency(selectedItem.net_salary) }}</span>
                    </div>
                </div>

                <div class="mt-10 flex justify-center gap-4 no-print">
                    <Button label="Direct PDF Download" icon="pi pi-download" @click="downloadPDF(selectedItem)"
                        class="!bg-green-600 hover:!bg-green-700 !border-green-600 text-white !rounded-full !px-8 !py-2.5" />
                    <Button label="Print View / PDF" icon="pi pi-print" @click="printPayslip"
                        class="!bg-indigo-600 hover:!bg-indigo-700 !border-indigo-600 text-white !rounded-full !px-8 !py-2.5" />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between w-full items-center no-print">
                    <span class="text-xs text-gray-400 font-bold italic">* Use Direct Download for generated PDF</span>
                    <div class="flex gap-2">
                        <Button label="Close" icon="pi pi-times"
                            class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                            @click="viewDialog = false" />
                    </div>
                </div>
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .p-dialog {
        position: static !important;
        width: 100% !important;
        height: auto !important;
        box-shadow: none !important;
        border: none !important;
        display: block !important;
    }

    .p-dialog-header,
    .p-dialog-footer {
        display: none !important;
    }

    .p-dialog-content {
        padding: 0 !important;
    }

    .p-dialog-mask {
        background: transparent !important;
    }
}

.paid-row {
    background-color: rgba(34, 197, 94, 0.05) !important;
}

.salary-high {
    background-color: rgba(34, 197, 94, 0.05) !important;
}

.salary-mid {
    background-color: rgba(245, 158, 11, 0.05) !important;
}

.salary-low {
    background-color: rgba(239, 68, 68, 0.05) !important;
}

.custom-payroll-table .p-datatable-tbody>tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.02);
}

.custom-payroll-table .p-datatable-tbody>tr:hover {
    background: rgba(28, 13, 130, 0.02) !important;
}

.custom-payroll-table .p-column-title {
    font-size: 11px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.1em !important;
    font-weight: 800 !important;
}
</style>
