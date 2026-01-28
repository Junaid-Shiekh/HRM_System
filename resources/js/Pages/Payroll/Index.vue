<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    runs: Object,
    branches: Array,
    months: Array
});

const generateDialog = ref(false);
const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    branch_id: null
});

const years = Array.from({ length: 3 }, (_, i) => ({ label: (new Date().getFullYear() - 1 + i).toString(), value: new Date().getFullYear() - 1 + i }));

const openGenerate = () => {
    generateDialog.value = true;
};

const handleGenerate = () => {
    form.post(route('payrolls.generate'), {
        onSuccess: () => {
            generateDialog.value = false;
        }
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'draft': return 'secondary';
        case 'pending_approval': return 'info';
        case 'approved': return 'info';
        case 'submitted': return 'warning';
        case 'paid': return 'success';
        default: return 'info';
    }
};
</script>

<template>

    <Head title="Payroll Processing" />
    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Payroll Management</h2>
            <Button label="Process New Payroll" icon="pi pi-plus" @click="openGenerate"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-lg overflow-hidden relative">
                <div class="absolute -right-4 -bottom-4 opacity-10 text-8xl"><i class="pi pi-money-bill"></i></div>
                <div class="text-sm font-bold uppercase opacity-60 mb-2 tracking-widest">Total Payroll Runs</div>
                <div class="text-4xl font-black">{{ runs.total }}</div>
            </div>
            <!-- More stats can go here -->
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="runs.data" tableStyle="min-width: 50rem">
                <Column header="Period">
                    <template #body="slotProps">
                        <div class="font-bold">
                            {{months.find(m => m.id === slotProps.data.month)?.name}} {{ slotProps.data.year }}
                        </div>
                        <div class="text-xs text-gray-400">{{ slotProps.data.branch?.name || 'All Branches' }}</div>
                    </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getStatusSeverity(slotProps.data.status)"
                            class="uppercase" />
                    </template>
                </Column>
                <Column header="Processed By">
                    <template #body="slotProps">
                        <div v-if="slotProps.data.processor" class="flex items-center gap-2">
                            <i class="pi pi-user text-xs"></i>
                            {{ slotProps.data.processor.name }}
                        </div>
                        <span v-else class="text-gray-400 italic">In Progress</span>
                    </template>
                </Column>
                <Column header="Created" sortable field="created_at">
                    <template #body="slotProps">
                        {{ new Date(slotProps.data.created_at).toLocaleDateString() }}
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Link :href="route('payrolls.show', slotProps.data.id)">
                                <Button
                                    class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    rounded aria-label="View">
                                    <i class="pi pi-eye"></i>
                                </Button>
                            </Link>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="generateDialog" header="Process Monthly Salary" modal :style="{ width: '400px' }"
            class="p-fluid">
            <div class="flex flex-col gap-4 mt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Select Month</label>
                    <Dropdown v-model="form.month" :options="months" optionLabel="name" optionValue="id" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Select Year</label>
                    <Dropdown v-model="form.year" :options="years" optionLabel="label" optionValue="value" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-bold">Branch (Optional)</label>
                    <Dropdown v-model="form.branch_id" :options="branches" optionLabel="name" optionValue="id"
                        placeholder="Select Branch" showClear />
                </div>
                <div
                    class="bg-blue-50 p-4 rounded-lg flex gap-3 items-start border border-blue-100 text-blue-800 text-xs">
                    <i class="pi pi-info-circle mt-0.5"></i>
                    <p>Generating payroll will automatically calculate base salaries, allowances, deductions, and
                        outstanding
                        loan installments for all active employees.</p>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="generateDialog = false" />
                <Button label="Generate Draft" icon="pi pi-play" @click="handleGenerate" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
