<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    issuances: Array,
    employees: Array,
    warehouses: Array,
    items: Array,
});

const showIssueModal = ref(false);
const showReturnModal = ref(false);
const selectedIssuance = ref(null);

const issueForm = useForm({
    inventory_item_id: '',
    warehouse_id: '',
    employee_id: '',
    quantity: 1,
    issued_date: new Date().toISOString().substr(0, 10),
    notes: '',
});

const returnForm = useForm({
    returned_date: new Date().toISOString().substr(0, 10),
    status: 'Returned',
    notes: '',
});

const submitIssuance = () => {
    issueForm.post(route('inventory.asset-issuances.store'), {
        onSuccess: () => {
            showIssueModal.value = false;
            issueForm.reset();
        }
    });
};

const openReturn = (issuance) => {
    selectedIssuance.value = issuance;
    showReturnModal.value = true;
};

const submitReturn = () => {
    returnForm.post(route('inventory.asset-issuances.return', selectedIssuance.value.id), {
        onSuccess: () => {
            showReturnModal.value = false;
            returnForm.reset();
        }
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Issued': return 'bg-blue-100 text-blue-700';
        case 'Returned': return 'bg-green-100 text-green-700';
        case 'Lost': return 'bg-red-100 text-red-700';
        case 'Damaged': return 'bg-orange-100 text-orange-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head title="Asset Issuance" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Employee Asset Issuance</h2>
                    <p class="text-sm text-gray-500 font-medium">Track operational assets assigned to staff members.</p>
                </div>
                <button @click="showIssueModal = true" class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95 uppercase tracking-widest text-xs">
                    <i class="pi pi-user-plus mr-2"></i>
                    Issue Asset
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Asset Details</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Employee</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Qty</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Issuance Date</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="iss in issuances" :key="iss.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-gray-900 leading-none">{{ iss.item.name }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase mt-1">From: {{ iss.warehouse.name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                         <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-[#1C0D82] border border-indigo-100">
                                            <i class="pi pi-user text-[10px]"></i>
                                         </div>
                                         <div>
                                            <p class="text-xs font-black text-gray-900 leading-none">{{ iss.employee.first_name }} {{ iss.employee.last_name }}</p>
                                            <p class="text-[9px] text-[#EAB308] font-black uppercase mt-1">ID: {{ iss.employee.employee_id }}</p>
                                         </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-black text-gray-900">{{ iss.quantity }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-medium text-gray-600">{{ iss.issued_date }}</p>
                                    <p v-if="iss.returned_date" class="text-[9px] text-green-600 font-black uppercase mt-0.5">Ret: {{ iss.returned_date }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight', getStatusClass(iss.status)]">
                                        {{ iss.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button v-if="iss.status === 'Issued'" @click="openReturn(iss)" class="text-xs font-black text-[#EAB308] hover:underline uppercase tracking-widest mr-3">Return Asset</button>
                                    <button class="text-xs font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest">History</button>
                                </td>
                            </tr>
                            <tr v-if="issuances.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No asset issuances recorded.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Issue Modal -->
        <div v-if="showIssueModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden" @click.stop>
                 <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">Issue Operational Asset</h3>
                    <button @click="showIssueModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submitIssuance" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Select Asset (Item)</label>
                        <select v-model="issueForm.inventory_item_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all bg-white">
                            <option value="" disabled>Search item...</option>
                            <option v-for="itm in items" :key="itm.id" :value="itm.id">[{{ itm.item_code }}] {{ itm.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">From Warehouse</label>
                            <select v-model="issueForm.warehouse_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all bg-white">
                                <option value="" disabled>Select storage...</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Quantity</label>
                            <input v-model="issueForm.quantity" type="number" step="0.01" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all font-black text-[#1C0D82]">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Target Employee</label>
                        <select v-model="issueForm.employee_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all bg-white">
                            <option value="" disabled>Search employee...</option>
                            <option v-for="emp in employees" :key="emp.id" :value="emp.id">[{{ emp.employee_id }}] {{ emp.first_name }} {{ emp.last_name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Issuance Date</label>
                        <input v-model="issueForm.issued_date" type="date" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all">
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="issueForm.processing" class="w-full py-3 rounded-xl bg-[#1C0D82] text-white text-sm font-black hover:bg-blue-900 transition-all uppercase tracking-widest shadow-lg active:scale-95 disabled:opacity-70">
                            Confirm Issuance
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Return Modal -->
        <div v-if="showReturnModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden" @click.stop>
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">Process Return: {{ selectedIssuance?.item.name }}</h3>
                    <button @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submitReturn" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Return Date</label>
                        <input v-model="returnForm.returned_date" type="date" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Return Condition / Status</label>
                        <select v-model="returnForm.status" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all bg-white">
                            <option value="Returned">Returned (Serviceable)</option>
                            <option value="Damaged">Damaged (Requires Repair)</option>
                            <option value="Lost">Lost / Missing</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Return Remarks</label>
                        <textarea v-model="returnForm.notes" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="returnForm.processing" class="w-full py-3 rounded-xl bg-[#EAB308] text-white text-sm font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg active:scale-95 disabled:opacity-70">
                            Process Asset Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
