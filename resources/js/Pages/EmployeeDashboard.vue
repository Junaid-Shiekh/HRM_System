<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import SweetAlert from '@/Components/SweetAlert.vue';

const canClockIn = computed(() => {
    if (props.todayAttendance) return false;
    if (!props.employee?.shift) return false;

    const now = new Date();
    const [startH, startM] = props.employee.shift.start_time.split(':').map(Number);
    const [endH, endM] = props.employee.shift.end_time.split(':').map(Number);

    // Create date objects for shift start and end today
    const shiftStart = new Date();
    shiftStart.setHours(startH, startM, 0, 0);

    const shiftEnd = new Date();
    shiftEnd.setHours(endH, endM, 0, 0);

    // Handle shift crossing midnight (e.g., 22:00 to 06:00)
    if (shiftEnd < shiftStart) {
        // If current time is early morning (before end time), the shift started "yesterday"
        if (now.getHours() < endH || (now.getHours() === endH && now.getMinutes() < endM)) {
            shiftStart.setDate(shiftStart.getDate() - 1);
        } else {
            // Shift starts later today
            shiftEnd.setDate(shiftEnd.getDate() + 1);
        }
    }

    // Allow clock in starting 2 hours before shift start
    const windowStart = new Date(shiftStart.getTime() - 2 * 60 * 60 * 1000);
    // Allow clock in until shift ends
    const windowEnd = shiftEnd;

    return now >= windowStart && now <= windowEnd;
});

const props = defineProps({
    meetings: Array,
    complaints: Array,
    warnings: Array,
    loans: Array,
    advances: Array,
    warningCount: Number,
    employee: Object,
    todayAttendance: Object
});

const complaintDialog = ref(false);
const loanDialog = ref(false);
const advanceDialog = ref(false);
const viewMeetingDialog = ref(false);
const viewComplaintDialog = ref(false);
const viewWarningDialog = ref(false);
const viewLoanDialog = ref(false);
const viewAdvanceDialog = ref(false);
const selectedItem = ref(null);
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success'
});

const form = useForm({
    title: '',
    description: ''
});

const loanForm = useForm({
    amount: null,
    installments: 12,
    reason: ''
});

const advanceForm = useForm({
    amount: null,
    reason: '',
    repayment_date: ''
});

const openNewComplaint = () => {
    form.reset();
    complaintDialog.value = true;
};

const openNewLoan = () => {
    loanForm.reset();
    loanDialog.value = true;
};

const openNewAdvance = () => {
    advanceForm.reset();
    advanceDialog.value = true;
};

const submitComplaint = () => {
    form.post(route('complaints.store'), {
        onSuccess: () => {
            complaintDialog.value = false;
            alertConfig.value = {
                title: 'Submitted!',
                message: 'Your complaint has been submitted to HR.',
                type: 'success'
            };
            showAlert.value = true;
        }
    });
};

const submitLoanRequest = () => {
    loanForm.post(route('loans.store'), {
        onSuccess: () => {
            loanDialog.value = false;
            alertConfig.value = {
                title: 'Requested!',
                message: 'Your loan request has been submitted to HR.',
                type: 'success'
            };
            showAlert.value = true;
        }
    });
};

const submitAdvanceRequest = () => {
    advanceForm.post(route('salary-advances.store'), {
        onSuccess: () => {
            advanceDialog.value = false;
            alertConfig.value = {
                title: 'Requested!',
                message: 'Your salary advance request has been submitted to HR.',
                type: 'success'
            };
            showAlert.value = true;
        }
    });
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'done': return 'success';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        case 'progress': return 'warn';
        case 'pending': return 'info';
        case 'scheduled': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
};

const openViewMeeting = (meeting) => {
    selectedItem.value = meeting;
    viewMeetingDialog.value = true;
};

const openViewComplaint = (complaint) => {
    selectedItem.value = complaint;
    viewComplaintDialog.value = true;
};

const openViewWarning = (warning) => {
    selectedItem.value = warning;
    viewWarningDialog.value = true;
};

const openViewLoan = (loan) => {
    selectedItem.value = loan;
    viewLoanDialog.value = true;
};

const openViewAdvance = (advance) => {
    selectedItem.value = advance;
    viewAdvanceDialog.value = true;
};
const markAsRead = (warning) => {
    router.patch(route('warnings.mark-as-read', warning.id), {}, {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Read!',
                message: 'Warning marked as read.',
                type: 'success'
            };
            showAlert.value = true;
        }
    });
};

const handleClockIn = () => {
    router.post(route('employee.clock-in'), {}, {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Clocked In!',
                message: 'Have a productive day!',
                type: 'success'
            };
            showAlert.value = true;
        }
    });
};

const handleClockOut = () => {
    router.post(route('employee.clock-out'), {}, {
        onSuccess: () => {
            alertConfig.value = {
                title: 'Clocked Out!',
                message: 'Good work today! See you tomorrow.',
                type: 'success'
            };
            showAlert.value = true;
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const formatDateTime = (dateString, timeOnly = false) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (timeOnly) {
        return date.toLocaleString(undefined, {
            timeStyle: 'short'
        });
    }
    return date.toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};
</script>

<template>

    <Head title="Employee Dashboard" />

    <AuthenticatedLayout>
        <div class="dashboard-container space-y-10 pb-16 pt-2 px-4 md:px-0">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 animate-fade-in">
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight leading-none mb-3">
                        Hello, <span
                            class="bg-gradient-to-r from-indigo-600 to-fuchsia-600 bg-clip-text text-transparent">{{
                                employee?.first_name || 'Employee' }}</span>! <span class="wave">👋</span>
                    </h1>
                    <p class="text-gray-500 font-medium text-lg">Your workspace is ready. What would you like to do
                        today?</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Button label="Request Loan" icon="pi pi-money-bill"
                        class="glass-button !bg-indigo-600/10 !border-indigo-600/20 !text-indigo-700 hover:!bg-indigo-600 hover:!text-white !px-6 !py-3 font-bold transition-all"
                        @click="openNewLoan" />
                    <Button label="Request Advance" icon="pi pi-wallet"
                        class="glass-button !bg-fuchsia-600/10 !border-fuchsia-600/20 !text-fuchsia-700 hover:!bg-fuchsia-600 hover:!text-white !px-6 !py-3 font-bold transition-all"
                        @click="openNewAdvance" />
                    <Button label="New Complaint" icon="pi pi-plus"
                        class="glass-button !bg-blue-900/10 !border-blue-900/20 !text-blue-900 hover:!bg-blue-900 hover:!text-white !px-6 !py-3 font-bold transition-all"
                        @click="openNewComplaint" />
                </div>
            </div>

            <!-- Main Grid: Top Row -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Attendance Card (Left - Compact) -->
                <div v-if="employee?.shift" class="lg:col-span-5 glass-card overflow-hidden">
                    <div class="p-6 border-b border-white/20 bg-white/10 flex justify-between items-center">
                        <h2 class="text-xs font-black text-gray-800 uppercase tracking-[0.2em] flex items-center gap-2">
                            <i class="pi pi-clock text-emerald-500"></i>
                            Attendance
                        </h2>
                        <span
                            class="text-[10px] font-bold px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full">ACTIVE
                            SESSION</span>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Shift Info -->
                        <div
                            class="flex items-center justify-between p-4 bg-white/40 rounded-2xl border border-white/60">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{
                                    employee.shift.name }}</p>
                                <p class="text-sm font-black text-gray-700">{{ employee.shift.start_time.substring(0, 5)
                                    }} - {{ employee.shift.end_time.substring(0, 5) }}</p>
                            </div>
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <i class="pi pi-sun"></i>
                            </div>
                        </div>

                        <!-- Clocking Buttons -->
                        <div class="grid grid-cols-2 gap-4">
                            <Button :disabled="!canClockIn" @click="handleClockIn"
                                class="!w-full !py-4 !rounded-2xl !transition-all !duration-500 shadow-sm"
                                :class="!canClockIn ? 'glass-disabled' : 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-200/50 shadow-lg'">
                                <div class="flex flex-col items-center gap-1">
                                    <i class="pi pi-sign-in text-lg"></i>
                                    <span class="text-xs font-bold uppercase tracking-tight">Clock In</span>
                                </div>
                            </Button>

                            <Button :disabled="!todayAttendance || todayAttendance.clock_out" @click="handleClockOut"
                                class="!w-full !py-4 !rounded-2xl !transition-all !duration-500 shadow-sm"
                                :class="(!todayAttendance || todayAttendance.clock_out) ? 'glass-disabled' : 'bg-rose-500 hover:bg-rose-600 text-white shadow-rose-200/50 shadow-lg'">
                                <div class="flex flex-col items-center gap-1">
                                    <i class="pi pi-sign-out text-lg"></i>
                                    <span class="text-xs font-bold uppercase tracking-tight">Clock Out</span>
                                </div>
                            </Button>
                        </div>

                        <!-- Simple Times -->
                        <div class="flex gap-4">
                            <div class="flex-1 p-3 bg-white/20 rounded-xl border border-white/40 text-center">
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Clock In</p>
                                <p class="text-sm font-black"
                                    :class="todayAttendance ? 'text-emerald-600' : 'text-gray-300'">
                                    {{ todayAttendance ? todayAttendance.clock_in.substring(0, 5) : '--:--' }}
                                </p>
                            </div>
                            <div class="flex-1 p-3 bg-white/20 rounded-xl border border-white/40 text-center">
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Clock Out</p>
                                <p class="text-sm font-black"
                                    :class="todayAttendance?.clock_out ? 'text-rose-600' : 'text-gray-300'">
                                    {{ todayAttendance?.clock_out ? todayAttendance.clock_out.substring(0, 5) : '--:--'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards (Right - Grid) -->
                <div class="lg:col-span-7 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-4">
                    <div v-for="(kpi, index) in [
                        { label: 'Meetings', count: meetings.filter(m => m.status === 'scheduled').length, icon: 'pi-video', color: 'indigo' },
                        { label: 'Complaints', count: complaints.filter(c => c.status !== 'done').length, icon: 'pi-exclamation-circle', color: 'orange' },
                        { label: 'Warnings', count: warningCount, icon: 'pi-bell', color: 'rose' },
                        { label: 'Active Loans', count: loans.filter(l => l.status === 'approved' && l.remaining_balance > 0).length, icon: 'pi-money-bill', color: 'emerald' },
                        { label: 'Advances', count: advances.filter(a => a.status === 'pending').length, icon: 'pi-wallet', color: 'fuchsia' }
                    ]" :key="index" class="glass-kpi group"
                        :class="['kpi-' + kpi.color, index === 4 ? 'col-span-2 md:col-span-1' : '']">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                            <div class="kpi-icon-container">
                                <i :class="['pi', kpi.icon]"></i>
                            </div>
                            <div class="text-center sm:text-left">
                                <h3 class="kpi-count">{{ kpi.count }}</h3>
                                <p class="kpi-label">{{ kpi.label }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Sections Grid (Two per row) -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

                <!-- Meetings Section -->
                <div class="section-card glass-card">
                    <div class="section-header">
                        <h2 class="text-xl font-black text-gray-800 flex items-center gap-3">
                            <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                            My Meetings
                        </h2>
                    </div>
                    <div class="p-4">
                        <DataTable :value="meetings" responsiveLayout="scroll" :rows="3" class="custom-table">
                            <Column field="title" header="Meeting" class="font-bold"></Column>
                            <Column header="Status" class="w-24">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.status.toUpperCase()"
                                        :severity="getStatusSeverity(slotProps.data.status)"
                                        class="!text-[9px] !font-black" />
                                </template>
                            </Column>
                            <Column header="Actions" class="w-16">
                                <template #body="slotProps">
                                    <Button icon="pi pi-chevron-right" text rounded
                                        @click="openViewMeeting(slotProps.data)" class="!p-0 !w-8 !h-8" />
                                </template>
                            </Column>
                        </DataTable>
                        <div v-if="!meetings.length" class="empty-state">
                            <i class="pi pi-calendar-times text-2xl mb-2 text-gray-300"></i>
                            <p>No meetings today</p>
                        </div>
                    </div>
                </div>

                <!-- Complaints Section -->
                <div class="section-card glass-card">
                    <div class="section-header flex justify-between items-center">
                        <h2 class="text-xl font-black text-gray-800 flex items-center gap-3">
                            <span class="w-2 h-8 bg-orange-500 rounded-full"></span>
                            My Complaints
                        </h2>
                        <Link :href="route('complaints.index')" class="view-all-link">View All</Link>
                    </div>
                    <div class="p-4">
                        <DataTable :value="complaints" responsiveLayout="scroll" :rows="3" class="custom-table">
                            <Column field="title" header="Subject" class="font-bold"></Column>
                            <Column header="Status" class="w-24">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.status.toUpperCase()"
                                        :severity="getStatusSeverity(slotProps.data.status)"
                                        class="!text-[9px] !font-black" />
                                </template>
                            </Column>
                            <Column header="Actions" class="w-16">
                                <template #body="slotProps">
                                    <Button icon="pi pi-chevron-right" text rounded
                                        @click="openViewComplaint(slotProps.data)" class="!p-0 !w-8 !h-8" />
                                </template>
                            </Column>
                        </DataTable>
                        <div v-if="!complaints.length" class="empty-state">
                            <i class="pi pi-check-circle text-2xl mb-2 text-gray-300"></i>
                            <p>All set! No complaints.</p>
                        </div>
                    </div>
                </div>

                <!-- Loans Section -->
                <div class="section-card glass-card">
                    <div class="section-header">
                        <h2 class="text-xl font-black text-gray-800 flex items-center gap-3">
                            <span class="w-2 h-8 bg-emerald-500 rounded-full"></span>
                            Finance & Loans
                        </h2>
                    </div>
                    <div class="p-4">
                        <DataTable :value="loans" responsiveLayout="scroll" :rows="3" class="custom-table">
                            <Column header="Amount" class="font-bold">
                                <template #body="slotProps">
                                    {{ formatCurrency(slotProps.data.amount) }}
                                </template>
                            </Column>
                            <Column header="Status" class="w-24">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.status.toUpperCase()"
                                        :severity="getStatusSeverity(slotProps.data.status)"
                                        class="!text-[9px] !font-black" />
                                </template>
                            </Column>
                            <Column header="Actions" class="w-16">
                                <template #body="slotProps">
                                    <Button icon="pi pi-chevron-right" text rounded
                                        @click="openViewLoan(slotProps.data)" class="!p-0 !w-8 !h-8" />
                                </template>
                            </Column>
                        </DataTable>
                        <div v-if="!loans.length" class="empty-state">
                            <i class="pi pi-money-bill text-2xl mb-2 text-gray-300"></i>
                            <p>No active loans</p>
                        </div>
                    </div>
                </div>

                <!-- Advances Section -->
                <div class="section-card glass-card">
                    <div class="section-header">
                        <h2 class="text-xl font-black text-gray-800 flex items-center gap-3">
                            <span class="w-2 h-8 bg-purple-500 rounded-full"></span>
                            Salary Advances
                        </h2>
                    </div>
                    <div class="p-4">
                        <DataTable :value="advances" responsiveLayout="scroll" :rows="3" class="custom-table">
                            <Column header="Amount" class="font-bold">
                                <template #body="slotProps">
                                    {{ formatCurrency(slotProps.data.amount) }}
                                </template>
                            </Column>
                            <Column header="Status" class="w-24">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.status.toUpperCase()"
                                        :severity="getStatusSeverity(slotProps.data.status)"
                                        class="!text-[9px] !font-black" />
                                </template>
                            </Column>
                            <Column header="Actions" class="w-16">
                                <template #body="slotProps">
                                    <Button icon="pi pi-chevron-right" text rounded
                                        @click="openViewAdvance(slotProps.data)" class="!p-0 !w-8 !h-8" />
                                </template>
                            </Column>
                        </DataTable>
                        <div v-if="!advances.length" class="empty-state">
                            <i class="pi pi-wallet text-2xl mb-2 text-gray-300"></i>
                            <p>No pending advances</p>
                        </div>
                    </div>
                </div>

                <!-- Warnings Section (Full Width) -->
                <div class="section-card glass-card xl:col-span-2">
                    <div class="section-header flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                            <span class="w-2 h-8 bg-rose-500 rounded-full"></span>
                            Important Notifications & Warnings
                        </h2>
                        <Link :href="route('warnings.index')" class="view-all-link">History</Link>
                    </div>
                    <div class="p-4">
                        <DataTable :value="warnings" responsiveLayout="scroll" :rows="5" class="custom-table-large">
                            <Column field="title" header="Notice" class="font-bold"></Column>
                            <Column field="warning_date" header="Received" class="w-32"></Column>
                            <Column header="Status" class="w-28">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.status === 'read' ? 'READ' : 'UNREAD'"
                                        :severity="slotProps.data.status === 'read' ? 'success' : 'danger'"
                                        class="!text-[9px] !font-black" />
                                </template>
                            </Column>
                            <Column header="Actions" class="w-32 text-right">
                                <template #body="slotProps">
                                    <div class="flex justify-end gap-1">
                                        <Button v-if="slotProps.data.status === 'pending'" icon="pi pi-check"
                                            class="!bg-emerald-500/10 !text-emerald-600 !border-0 !w-8 !h-8"
                                            @click="markAsRead(slotProps.data)" />
                                        <Button icon="pi pi-eye"
                                            class="!bg-blue-500/10 !text-blue-600 !border-0 !w-8 !h-8"
                                            @click="openViewWarning(slotProps.data)" />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                        <div v-if="!warnings.length" class="text-center py-8 text-gray-400 font-medium italic">
                            No warnings found. Keep up the good work!
                        </div>
                    </div>
                </div>
            </div>


            <!-- Dialogs (Moved inside container) -->
            <Dialog v-model:visible="complaintDialog" header="Submit a New Complaint" :style="{ width: '500px' }"
                :modal="true" class="p-fluid glass-modal">
                <div class="flex flex-col gap-6 p-1">
                    <div class="flex flex-col gap-2">
                        <label for="title" class="font-semibold text-gray-700">Subject</label>
                        <InputText id="title" v-model="form.title" placeholder="Brief summary of the issue..."
                            class="!w-full !border !border-gray-200 !rounded-xl" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="description" class="font-semibold text-gray-700">Description</label>
                        <Textarea id="description" v-model="form.description" rows="5"
                            placeholder="Detailed explanation..."
                            class="!w-full !border !border-gray-200 !rounded-xl !resize-none" />
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-3 p-2">
                        <Button label="Cancel" text @click="complaintDialog = false" 
                            class="!text-gray-500 !font-bold !px-6 hover:!bg-gray-50 !rounded-xl transition-all" />
                        <Button label="Submit Complaint" icon="pi pi-send" @click="submitComplaint" :loading="form.processing"
                            class="!bg-indigo-600 !border-indigo-600 !px-8 !py-3 !rounded-xl !font-bold hover:!bg-indigo-700 transition-all shadow-lg shadow-indigo-200" />
                    </div>
                </template>
            </Dialog>

            <!-- Other Dialogs... (Simplified for brevity but kept functional) -->
            <Dialog v-model:visible="loanDialog" header="Request a Loan" :style="{ width: '500px' }" :modal="true"
                class="p-fluid glass-modal">
                <div class="flex flex-col gap-4 p-1">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold text-sm">Amount Required</label>
                        <InputText v-model="loanForm.amount" type="number" class="!rounded-xl" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold text-sm">Reason</label>
                        <Textarea v-model="loanForm.reason" rows="3" class="!rounded-xl !resize-none" />
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-3 p-2">
                        <Button label="Cancel" text @click="loanDialog = false"
                            class="!text-gray-500 !font-bold !px-6 hover:!bg-gray-50 !rounded-xl transition-all" />
                        <Button label="Submit Request" icon="pi pi-check" @click="submitLoanRequest" :loading="loanForm.processing"
                            class="!bg-indigo-600 !border-indigo-600 !px-8 !py-3 !rounded-xl !font-bold hover:!bg-indigo-700 transition-all shadow-lg shadow-indigo-200" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="advanceDialog" header="Request Salary Advance" :style="{ width: '500px' }"
                :modal="true" class="p-fluid glass-modal">
                <div class="flex flex-col gap-4 p-1">
                    <div class="flex flex-col gap-2">
                        <label class="font-bold text-sm">Amount</label>
                        <InputText v-model="advanceForm.amount" type="number" class="!rounded-xl" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-bold text-sm">Reason</label>
                        <Textarea v-model="advanceForm.reason" rows="3" class="!rounded-xl !resize-none" />
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-3 p-2">
                        <Button label="Cancel" text @click="advanceDialog = false"
                            class="!text-gray-500 !font-bold !px-6 hover:!bg-gray-50 !rounded-xl transition-all" />
                        <Button label="Submit Request" icon="pi pi-check" @click="submitAdvanceRequest" :loading="advanceForm.processing"
                            class="!bg-fuchsia-600 !border-fuchsia-600 !px-8 !py-3 !rounded-xl !font-bold hover:!bg-fuchsia-700 transition-all shadow-lg shadow-fuchsia-200" />
                    </div>
                </template>
            </Dialog>

            <!-- View Dialogs (Meetings, Warnings, etc.) -->
            <Dialog v-model:visible="viewMeetingDialog" :style="{ width: '500px' }" header="Meeting Details"
                :modal="true" class="glass-modal">
                <div v-if="selectedItem" class="space-y-4">
                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                        <h3 class="text-xl font-black text-indigo-900">{{ selectedItem.title }}</h3>
                        <p class="text-sm text-indigo-600 font-bold mt-1">{{ selectedItem.date }} | {{
                            selectedItem.start_time_fmt
                            }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-gray-600">
                        {{ selectedItem.description || 'No description provided.' }}
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end p-2">
                        <Button label="Close" text @click="viewMeetingDialog = false" class="!font-bold !text-indigo-600" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="viewComplaintDialog" :style="{ width: '500px' }" header="Complaint Details"
                :modal="true" class="glass-modal">
                <div v-if="selectedItem" class="space-y-4">
                    <div class="p-4 bg-orange-50/50 rounded-2xl border border-orange-100">
                        <h3 class="text-xl font-black text-orange-900">{{ selectedItem.title }}</h3>
                        <div class="flex gap-2 mt-2">
                            <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" />
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-gray-600">
                        <p class="font-bold text-xs uppercase tracking-widest text-gray-400 mb-2">Description</p>
                        {{ selectedItem.description }}
                    </div>
                    <div v-if="selectedItem.resolution_note" class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-emerald-700">
                        <p class="font-bold text-xs uppercase tracking-widest text-emerald-500 mb-2">HR Resolution</p>
                        {{ selectedItem.resolution_note }}
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end p-2">
                        <Button label="Close" text @click="viewComplaintDialog = false" class="!font-bold !text-orange-600" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="viewWarningDialog" :style="{ width: '500px' }" header="Warning Details"
                :modal="true" class="glass-modal">
                <div v-if="selectedItem" class="space-y-4">
                    <div class="p-4 bg-rose-50/50 rounded-2xl border border-rose-100">
                        <h3 class="text-xl font-black text-rose-900">{{ selectedItem.title }}</h3>
                        <p class="text-sm text-rose-600 font-bold mt-1">Received: {{ selectedItem.warning_date }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-gray-600">
                        {{ selectedItem.description }}
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end p-2">
                        <Button label="Close" text @click="viewWarningDialog = false" class="!font-bold !text-rose-600" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="viewLoanDialog" :style="{ width: '500px' }" header="Loan Details"
                :modal="true" class="glass-modal">
                <div v-if="selectedItem" class="space-y-4">
                    <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-2xl font-black text-indigo-900">{{ formatCurrency(selectedItem.amount) }}</h3>
                            <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Installments</p>
                            <p class="font-black text-gray-700">{{ selectedItem.installments }} Months</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Monthly</p>
                            <p class="font-black text-gray-700">{{ formatCurrency(selectedItem.monthly_installment) }}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-gray-600">
                        <p class="font-bold text-xs uppercase tracking-widest text-gray-400 mb-2">Reason</p>
                        {{ selectedItem.reason }}
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end p-2">
                        <Button label="Close" text @click="viewLoanDialog = false" class="!font-bold !text-indigo-600" />
                    </div>
                </template>
            </Dialog>

            <Dialog v-model:visible="viewAdvanceDialog" :style="{ width: '500px' }" header="Salary Advance Details"
                :modal="true" class="glass-modal">
                <div v-if="selectedItem" class="space-y-4">
                    <div class="p-4 bg-fuchsia-50/50 rounded-2xl border border-fuchsia-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-2xl font-black text-fuchsia-900">{{ formatCurrency(selectedItem.amount) }}</h3>
                            <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" />
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-gray-600">
                        <p class="font-bold text-xs uppercase tracking-widest text-gray-400 mb-2">Reason</p>
                        {{ selectedItem.reason }}
                    </div>
                    <div v-if="selectedItem.repayment_date" class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Expected Repayment</p>
                        <p class="font-black text-gray-700">{{ selectedItem.repayment_date }}</p>
                    </div>
                </div>
                <template #footer>
                    <div class="flex justify-end p-2">
                        <Button label="Close" text @click="viewAdvanceDialog = false" class="!font-bold !text-fuchsia-600" />
                    </div>
                </template>
            </Dialog>

            <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
                :type="alertConfig.type" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-container {
    background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.03), transparent),
        radial-gradient(circle at bottom left, rgba(217, 70, 239, 0.03), transparent);
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
    border-radius: 28px;
    transition: transform 0.3s ease;
}

.glass-button {
    backdrop-filter: blur(4px);
    border-radius: 14px !important;
}

.glass-disabled {
    background: rgba(243, 244, 246, 0.4) !important;
    border-color: rgba(229, 231, 235, 0.2) !important;
    color: #cbd5e1 !important;
    filter: grayscale(1);
    cursor: not-allowed;
}

.glass-kpi {
    padding: 1.5rem;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.glass-kpi:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
}

.kpi-icon-container {
    width: 3.8rem;
    height: 3.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    font-size: 1.6rem;
    transition: all 0.4s ease;
}

.kpi-count {
    font-size: 1.8rem;
    font-weight: 900;
    color: #111827;
    line-height: 1;
    margin-bottom: 0.2rem;
}

.kpi-label {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #94a3b8;
}

.kpi-indigo .kpi-icon-container {
    background: #e0e7ff;
    color: #4338ca;
}

.kpi-indigo:hover .kpi-icon-container {
    background: #4338ca;
    color: white;
    box-shadow: 0 10px 20px rgba(67, 56, 202, 0.2);
}

.kpi-orange .kpi-icon-container {
    background: #ffedd5;
    color: #ea580c;
}

.kpi-orange:hover .kpi-icon-container {
    background: #ea580c;
    color: white;
    box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2);
}

.kpi-rose .kpi-icon-container {
    background: #ffe4e6;
    color: #e11d48;
}

.kpi-rose:hover .kpi-icon-container {
    background: #e11d48;
    color: white;
    box-shadow: 0 10px 20px rgba(225, 29, 72, 0.2);
}

.kpi-emerald .kpi-icon-container {
    background: #dcfce7;
    color: #059669;
}

.kpi-emerald:hover .kpi-icon-container {
    background: #059669;
    color: white;
    box-shadow: 0 10px 20px rgba(5, 150, 105, 0.2);
}

.kpi-fuchsia .kpi-icon-container {
    background: #fae8ff;
    color: #c026d3;
}

.kpi-fuchsia:hover .kpi-icon-container {
    background: #c026d3;
    color: white;
    box-shadow: 0 10px 20px rgba(192, 38, 211, 0.2);
}

.section-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
}

.view-all-link {
    font-size: 0.7rem;
    font-weight: 900;
    color: #6366f1;
    padding: 0.4rem 0.8rem;
    background: rgba(99, 102, 241, 0.08);
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.3s;
}

.view-all-link:hover {
    background: #6366f1;
    color: white;
}

.custom-table :deep(.p-datatable-thead > tr > th) {
    background: transparent !important;
    font-size: 0.65rem;
    color: #94a3b8;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.custom-table :deep(.p-datatable-tbody > tr) {
    background: transparent !important;
    transition: background 0.2s;
}

.custom-table :deep(.p-datatable-tbody > tr:hover) {
    background: rgba(255, 255, 255, 0.5) !important;
}

.empty-state {
    padding: 3.5rem 1rem;
    text-align: center;
}

.wave {
    display: inline-block;
    animation: wave-animation 2.5s infinite;
    transform-origin: 70% 70%;
}

@keyframes wave-animation {
    0% {
        transform: rotate(0.0deg)
    }

    10% {
        transform: rotate(14.0deg)
    }

    20% {
        transform: rotate(-8.0deg)
    }

    30% {
        transform: rotate(14.0deg)
    }

    40% {
        transform: rotate(-4.0deg)
    }

    50% {
        transform: rotate(10.0deg)
    }

    60% {
        transform: rotate(0.0deg)
    }

    100% {
        transform: rotate(0.0deg)
    }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.glass-modal :deep(.p-dialog-content) {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}
</style>
