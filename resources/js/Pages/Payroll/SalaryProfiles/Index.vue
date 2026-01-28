<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    employees: Array,
    components: Array
});

const profileDialog = ref(false);
const activeEmployee = ref(null);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    base_salary: 0,
    salary_type: 'monthly',
    payment_method: 'bank',
    bank_name: '',
    account_name: '',
    account_number: '',
    iban: '',
    components: [] // Array of {id, custom_amount}
});

const salaryTypes = [
    { label: 'Monthly', value: 'monthly' },
    { label: 'Daily', value: 'daily' },
    { label: 'Hourly', value: 'hourly' }
];

const openProfile = (employee) => {
    activeEmployee.value = employee;
    const profile = employee.salary_profile;

    form.base_salary = profile ? parseFloat(profile.base_salary) : parseFloat(employee.salary || 0);
    form.salary_type = profile ? profile.salary_type : 'monthly';
    form.payment_method = profile ? profile.payment_method : 'bank';
    form.bank_name = profile ? profile.bank_name : '';
    form.account_name = profile ? profile.account_name : '';
    form.account_number = profile ? profile.account_number : '';
    form.iban = profile ? profile.iban : '';

    // Sync components
    form.components = props.components.map(comp => {
        const existing = employee.salary_components.find(c => c.id === comp.id);
        return {
            id: comp.id,
            name: comp.name,
            type: comp.type,
            enabled: !!existing,
            amount_type: existing ? (existing.pivot.amount_type || comp.amount_type) : comp.amount_type,
            custom_amount: existing ? parseFloat(existing.pivot.custom_amount || comp.amount) : parseFloat(comp.amount)
        };
    });

    profileDialog.value = true;
};

const saveProfile = () => {
    const selectedComponents = form.components
        .filter(c => c.enabled)
        .map(c => ({
            id: c.id,
            custom_amount: c.custom_amount,
            amount_type: c.amount_type
        }));

    form.transform((data) => ({
        ...data,
        components: selectedComponents
    })).put(route('salary-profiles.update', activeEmployee.value.id), {
        onSuccess: () => {
            profileDialog.value = false;
            showSuccess('Salary profile updated successfully');
        }
    });
};

const showSuccess = (msg) => {
    alertConfig.value = { title: 'Success', message: msg, type: 'success' };
    showAlert.value = true;
};
</script>

<template>

    <Head title="Employee Salary Setup" />
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Payroll Setup</h2>
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="employees" paginator :rows="10">
                <Column header="Employee" sortable>
                    <template #body="slotProps">
                        <div class="font-bold">{{ slotProps.data.first_name }} {{ slotProps.data.last_name }}</div>
                        <div class="text-xs text-gray-500">{{ slotProps.data.department?.name }}</div>
                    </template>
                </Column>
                <Column header="Base Salary">
                    <template #body="slotProps">
                        ${{ slotProps.data.salary_profile?.base_salary || slotProps.data.salary || '0.00' }}
                    </template>
                </Column>
                <Column header="Type">
                    <template #body="slotProps">
                        <span class="capitalize">{{ slotProps.data.salary_profile?.salary_type || 'monthly' }}</span>
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <div v-if="slotProps.data.salary_profile" class="text-green-600 flex items-center gap-1">
                            <i class="pi pi-check-circle"></i> Configured
                        </div>
                        <div v-else class="text-orange-500">Pending Setup</div>
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <Button label="Configure" icon="pi pi-cog" @click="openProfile(slotProps.data)"
                            class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !text-sm !py-2 !px-4" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="profileDialog" header="Salary Profile Setup" modal class="p-fluid"
            :style="{ width: '800px' }">
            <div class="grid grid-cols-2 gap-6 mt-4">
                <div class="col-span-1">
                    <h3 class="font-bold border-b pb-2 mb-4 text-indigo-800">Base Salary Details</h3>
                    <div class="flex flex-col gap-2 mb-4">
                        <label class="font-bold">Base Salary</label>
                        <InputNumber v-model="form.base_salary" mode="decimal" minFractionDigits="2" />
                    </div>
                    <div class="flex flex-col gap-2 mb-4">
                        <label class="font-bold">Salary Type</label>
                        <Dropdown v-model="form.salary_type" :options="salaryTypes" optionLabel="label"
                            optionValue="value" />
                    </div>
                    <div class="flex flex-col gap-2 mb-4">
                        <label class="font-bold">Payment Method</label>
                        <InputText v-model="form.payment_method" placeholder="Bank, Cash, etc." />
                    </div>

                    <h3 class="font-bold border-b pb-2 mb-4 mt-6 text-indigo-800">Bank Information</h3>
                    <div class="flex flex-col gap-1 mb-2">
                        <label class="text-xs font-bold">Bank Name</label>
                        <InputText v-model="form.bank_name" />
                    </div>
                    <div class="flex flex-col gap-1 mb-2">
                        <label class="text-xs font-bold">Account Name</label>
                        <InputText v-model="form.account_name" />
                    </div>
                    <div class="flex flex-col gap-1 mb-2">
                        <label class="text-xs font-bold">Account Number</label>
                        <InputText v-model="form.account_number" />
                    </div>
                </div>

                <div class="col-span-1 border-l pl-6">
                    <h3 class="font-bold border-b pb-2 mb-4 text-indigo-800">Allowances & Deductions</h3>
                    <p class="text-[10px] text-gray-500 mb-4 bg-blue-50 p-2 rounded border border-blue-100 italic">
                        <i class="pi pi-info-circle mr-1"></i>
                        Custom amounts entered here will override default values. Refresh payroll totals after saving.
                    </p>
                    <div class="flex flex-col gap-4 max-h-[400px] overflow-y-auto pr-2">
                        <div v-for="(comp, index) in form.components" :key="comp.id"
                            class="flex flex-col p-3 rounded-lg border-2 transition-all"
                            :class="comp.enabled ? 'border-indigo-100 bg-white' : 'border-gray-50 bg-gray-50 opacity-60'">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <Checkbox v-model="comp.enabled" :binary="true" />
                                    <span class="font-bold text-sm">{{ comp.name }}</span>
                                </div>
                                <span
                                    :class="comp.type === 'allowance' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'"
                                    class="text-[9px] font-bold uppercase px-1.5 rounded">{{ comp.type }}</span>
                            </div>
                            <div v-if="comp.enabled" class="flex flex-col gap-2 p-2 bg-indigo-50/50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <label class="text-[9px] text-indigo-400 font-bold uppercase">Calculation
                                        Basis</label>
                                    <div class="flex gap-1">
                                        <button v-for="bt in [{ l: '$', v: 'fixed' }, { l: '%', v: 'percent' }]"
                                            :key="bt.v" @click="comp.amount_type = bt.v"
                                            class="text-[10px] px-2 py-0.5 rounded font-black transition-all"
                                            :class="comp.amount_type === bt.v ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-indigo-400 border border-indigo-100 hover:bg-indigo-50'">
                                            {{ bt.l }}
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-[9px] text-gray-400 font-bold uppercase">Custom Amount</label>
                                    <InputNumber v-model="comp.custom_amount" mode="decimal" minFractionDigits="2"
                                        class="p-inputtext-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="profileDialog = false" />
                <Button label="Save Configuration" icon="pi pi-check" @click="saveProfile" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" />
    </AuthenticatedLayout>
</template>
