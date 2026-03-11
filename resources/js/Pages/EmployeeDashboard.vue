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
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import SweetAlert from '@/Components/SweetAlert.vue';

const canClockIn = computed(() => {
    if (props.todayAttendance) return false;
    if (!props.employee?.shift) return false;
    const now = new Date();
    const [startH, startM] = props.employee.shift.start_time.split(':').map(Number);
    const [endH, endM] = props.employee.shift.end_time.split(':').map(Number);
    const shiftStart = new Date(); shiftStart.setHours(startH, startM, 0, 0);
    const shiftEnd = new Date(); shiftEnd.setHours(endH, endM, 0, 0);
    if (shiftEnd < shiftStart) {
        if (now.getHours() < endH || (now.getHours() === endH && now.getMinutes() < endM)) shiftStart.setDate(shiftStart.getDate() - 1);
        else shiftEnd.setDate(shiftEnd.getDate() + 1);
    }
    const windowStart = new Date(shiftStart.getTime() - 2 * 60 * 60 * 1000);
    return now >= windowStart && now <= shiftEnd;
});

const props = defineProps({
    meetings: Array, complaints: Array, warnings: Array, loans: Array, advances: Array,
    warningCount: Number, employee: Object, todayAttendance: Object,
    leaveBalances: Array, leaveApplications: Array, leaveTypes: Array
});

const complaintDialog = ref(false); const loanDialog = ref(false);
const advanceDialog = ref(false); const viewMeetingDialog = ref(false);
const viewComplaintDialog = ref(false); const viewWarningDialog = ref(false);
const viewLoanDialog = ref(false); const viewAdvanceDialog = ref(false);
const leaveDialog = ref(false); const viewLeaveDialog = ref(false);
const selectedItem = ref(null); const showAlert = ref(false);
const alertConfig = ref({ title: '', message: '', type: 'success' });

const form = useForm({ title: '', description: '' });
const loanForm = useForm({ amount: null, installments: 12, reason: '' });
const advanceForm = useForm({ amount: null, reason: '', repayment_date: '' });
const leaveForm = useForm({ leave_type_id: null, start_date: null, end_date: null, reason: '', status: 'pending' });

const openNewComplaint = () => { form.reset(); complaintDialog.value = true; };
const openNewLoan = () => { loanForm.reset(); loanDialog.value = true; };
const openNewAdvance = () => { advanceForm.reset(); advanceDialog.value = true; };
const openNewLeave = () => { leaveForm.reset(); leaveDialog.value = true; };

const submitComplaint = () => {
    form.post(route('complaints.store'), { onSuccess: () => { complaintDialog.value = false; alertConfig.value = { title: 'Submitted!', message: 'Your complaint has been submitted to HR.', type: 'success' }; showAlert.value = true; } });
};
const submitLoanRequest = () => {
    loanForm.post(route('loans.store'), { onSuccess: () => { loanDialog.value = false; alertConfig.value = { title: 'Requested!', message: 'Your loan request has been submitted to HR.', type: 'success' }; showAlert.value = true; } });
};
const submitAdvanceRequest = () => {
    advanceForm.post(route('salary-advances.store'), { onSuccess: () => { advanceDialog.value = false; alertConfig.value = { title: 'Requested!', message: 'Your salary advance request has been submitted to HR.', type: 'success' }; showAlert.value = true; } });
};
const submitLeaveRequest = () => {
    const formData = { ...leaveForm.data(), employee_id: props.employee.id, start_date: leaveForm.start_date ? new Date(leaveForm.start_date.getTime() - (leaveForm.start_date.getTimezoneOffset() * 60000)).toISOString().split('T')[0] : null, end_date: leaveForm.end_date ? new Date(leaveForm.end_date.getTime() - (leaveForm.end_date.getTimezoneOffset() * 60000)).toISOString().split('T')[0] : null };
    router.post(route('leave-applications.store'), formData, { onSuccess: () => { leaveDialog.value = false; alertConfig.value = { title: 'Submitted!', message: 'Your leave application has been submitted for approval.', type: 'success' }; showAlert.value = true; } });
};

const getStatusSeverity = (status) => {
    const map = { done: 'success', approved: 'success', rejected: 'danger', progress: 'warn', pending: 'info', scheduled: 'info', completed: 'success', cancelled: 'danger' };
    return map[status] || 'secondary';
};

const openViewMeeting = (item) => { selectedItem.value = item; viewMeetingDialog.value = true; };
const openViewComplaint = (item) => { selectedItem.value = item; viewComplaintDialog.value = true; };
const openViewWarning = (item) => { selectedItem.value = item; viewWarningDialog.value = true; };
const openViewLoan = (item) => { selectedItem.value = item; viewLoanDialog.value = true; };
const openViewAdvance = (item) => { selectedItem.value = item; viewAdvanceDialog.value = true; };
const openViewLeave = (item) => { selectedItem.value = item; viewLeaveDialog.value = true; };

const markAsRead = (warning) => {
    router.patch(route('warnings.mark-as-read', warning.id), {}, { onSuccess: () => { alertConfig.value = { title: 'Marked Read', message: 'Warning marked as read.', type: 'success' }; showAlert.value = true; } });
};
const handleClockIn = () => {
    router.post(route('employee.clock-in'), {}, { onSuccess: () => { alertConfig.value = { title: 'Clocked In!', message: 'Have a productive day!', type: 'success' }; showAlert.value = true; } });
};
const handleClockOut = () => {
    router.post(route('employee.clock-out'), {}, { onSuccess: () => { alertConfig.value = { title: 'Clocked Out!', message: 'Good work today!', type: 'success' }; showAlert.value = true; } });
};

const calculateDays = (start, end) => { if (!start || !end) return 0; return Math.ceil(Math.abs(new Date(end) - new Date(start)) / (1000 * 60 * 60 * 24)) + 1; };
const formatCurrency = (v) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(v);
</script>

<template>
    <Head title="Employee Dashboard" />
    <AuthenticatedLayout>
        <div class="dash">

            <!-- Top Bar -->
            <div class="top-bar">
                <div>
                    <h1 class="greeting">Hello, <span class="name-highlight">{{ employee?.first_name || 'Employee' }}</span>! <span class="wave">👋</span></h1>
                    <p class="greeting-sub">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }) }}</p>
                </div>
                <div class="action-row">
                    <button class="action-btn btn-indigo" @click="openNewLoan">
                        <i class="pi pi-money-bill"></i> Request Loan
                    </button>
                    <button class="action-btn btn-violet" @click="openNewAdvance">
                        <i class="pi pi-wallet"></i> Request Advance
                    </button>
                    <button class="action-btn btn-slate" @click="openNewComplaint">
                        <i class="pi pi-plus"></i> New Complaint
                    </button>
                    <button class="action-btn btn-emerald" @click="openNewLeave">
                        <i class="pi pi-calendar-plus"></i> Request Leave
                    </button>
                </div>
            </div>

            <!-- Row 1: Attendance + KPIs -->
            <div class="row-grid">

                <!-- Attendance -->
                <div v-if="employee?.shift" class="card attendance-card">
                    <div class="card-head">
                        <span class="card-title"><i class="pi pi-clock"></i> Attendance</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="shift-info">
                        <div>
                            <div class="micro-label">{{ employee.shift.name }}</div>
                            <div class="shift-time">{{ employee.shift.start_time.substring(0,5) }} – {{ employee.shift.end_time.substring(0,5) }}</div>
                        </div>
                        <i class="pi pi-sun shift-icon"></i>
                    </div>
                    <div class="clock-grid">
                        <button :disabled="!canClockIn" @click="handleClockIn" class="clock-btn clock-in" :class="{ 'clock-disabled': !canClockIn }">
                            <i class="pi pi-sign-in"></i> Clock In
                        </button>
                        <button :disabled="!todayAttendance || todayAttendance.clock_out" @click="handleClockOut" class="clock-btn clock-out" :class="{ 'clock-disabled': !todayAttendance || todayAttendance.clock_out }">
                            <i class="pi pi-sign-out"></i> Clock Out
                        </button>
                    </div>
                    <div class="time-row">
                        <div class="time-cell">
                            <span class="micro-label">Clock In</span>
                            <span class="time-val in">{{ todayAttendance ? todayAttendance.clock_in.substring(0,5) : '--:--' }}</span>
                        </div>
                        <div class="time-divider"></div>
                        <div class="time-cell">
                            <span class="micro-label">Clock Out</span>
                            <span class="time-val out">{{ todayAttendance?.clock_out ? todayAttendance.clock_out.substring(0,5) : '--:--' }}</span>
                        </div>
                    </div>
                </div>

                <!-- KPIs + Leave Balances -->
                <div class="right-col">
                    <div class="kpi-grid">
                        <div v-for="(kpi, i) in [
                            { label: 'Meetings', count: meetings.filter(m => m.status === 'scheduled').length, icon: 'pi-video', color: 'indigo' },
                            { label: 'Complaints', count: complaints.filter(c => c.status !== 'done').length, icon: 'pi-exclamation-circle', color: 'amber' },
                            { label: 'Warnings', count: warningCount, icon: 'pi-bell', color: 'rose' },
                            { label: 'Active Loans', count: loans.filter(l => l.status === 'approved' && l.remaining_balance > 0).length, icon: 'pi-money-bill', color: 'teal' },
                            { label: 'Advances', count: advances.filter(a => a.status === 'pending').length, icon: 'pi-wallet', color: 'violet' },
                        ]" :key="i" class="kpi-card" :class="'kpi-' + kpi.color">
                            <div class="kpi-icon"><i :class="['pi', kpi.icon]"></i></div>
                            <div class="kpi-num">{{ kpi.count }}</div>
                            <div class="kpi-lbl">{{ kpi.label }}</div>
                        </div>
                    </div>

                    <!-- Leave Balances -->
                    <div class="card leave-bal-card">
                        <div class="card-head">
                            <span class="card-title"><i class="pi pi-briefcase"></i> Leave Balances</span>
                        </div>
                        <div class="leave-bal-grid">
                            <div v-for="b in leaveBalances" :key="b.leave_type_id" class="leave-bal-item">
                                <div class="leave-bal-bar" :style="{ backgroundColor: '#' + b.color }"></div>
                                <div class="leave-bal-info">
                                    <div class="leave-bal-name">{{ b.leave_type_name }}</div>
                                    <div class="leave-bal-count">{{ b.remaining }}<span class="leave-bal-unit"> days</span></div>
                                    <div class="leave-bal-used">{{ b.used }}/{{ b.entitlement }} used</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Meetings + Complaints -->
            <div class="two-col">
                <div class="card section-card">
                    <div class="card-head">
                        <span class="card-title"><span class="accent-bar blue"></span> My Meetings</span>
                    </div>
                    <DataTable :value="meetings" :rows="4" class="dt">
                        <Column field="title" header="Meeting"></Column>
                        <Column header="Status" style="width:90px">
                            <template #body="{ data }"><Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="dt-tag" /></template>
                        </Column>
                        <Column header="" style="width:44px">
                            <template #body="{ data }"><button class="icon-btn" @click="openViewMeeting(data)"><i class="pi pi-angle-right"></i></button></template>
                        </Column>
                    </DataTable>
                    <div v-if="!meetings.length" class="empty"><i class="pi pi-calendar-times"></i><span>No meetings today</span></div>
                </div>

                <div class="card section-card">
                    <div class="card-head">
                        <span class="card-title"><span class="accent-bar amber"></span> My Complaints</span>
                        <Link :href="route('complaints.index')" class="view-all">View All</Link>
                    </div>
                    <DataTable :value="complaints" :rows="4" class="dt">
                        <Column field="title" header="Subject"></Column>
                        <Column header="Status" style="width:90px">
                            <template #body="{ data }"><Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="dt-tag" /></template>
                        </Column>
                        <Column header="" style="width:44px">
                            <template #body="{ data }"><button class="icon-btn" @click="openViewComplaint(data)"><i class="pi pi-angle-right"></i></button></template>
                        </Column>
                    </DataTable>
                    <div v-if="!complaints.length" class="empty"><i class="pi pi-check-circle"></i><span>All set! No complaints.</span></div>
                </div>
            </div>

            <!-- Row 3: Loans + Advances -->
            <div class="two-col">
                <div class="card section-card">
                    <div class="card-head">
                        <span class="card-title"><span class="accent-bar teal"></span> Finance & Loans</span>
                    </div>
                    <DataTable :value="loans" :rows="4" class="dt">
                        <Column header="Amount"><template #body="{ data }">{{ formatCurrency(data.amount) }}</template></Column>
                        <Column header="Status" style="width:90px">
                            <template #body="{ data }"><Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="dt-tag" /></template>
                        </Column>
                        <Column header="" style="width:44px">
                            <template #body="{ data }"><button class="icon-btn" @click="openViewLoan(data)"><i class="pi pi-angle-right"></i></button></template>
                        </Column>
                    </DataTable>
                    <div v-if="!loans.length" class="empty"><i class="pi pi-money-bill"></i><span>No active loans</span></div>
                </div>

                <div class="card section-card">
                    <div class="card-head">
                        <span class="card-title"><span class="accent-bar violet"></span> Salary Advances</span>
                    </div>
                    <DataTable :value="advances" :rows="4" class="dt">
                        <Column header="Amount"><template #body="{ data }">{{ formatCurrency(data.amount) }}</template></Column>
                        <Column header="Status" style="width:90px">
                            <template #body="{ data }"><Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="dt-tag" /></template>
                        </Column>
                        <Column header="" style="width:44px">
                            <template #body="{ data }"><button class="icon-btn" @click="openViewAdvance(data)"><i class="pi pi-angle-right"></i></button></template>
                        </Column>
                    </DataTable>
                    <div v-if="!advances.length" class="empty"><i class="pi pi-wallet"></i><span>No pending advances</span></div>
                </div>
            </div>

            <!-- Row 4: Leaves + Warnings -->
            <div class="two-col">
                <div class="card section-card">
                    <div class="card-head">
                        <span class="card-title"><span class="accent-bar teal"></span> My Leaves</span>
                    </div>
                    <DataTable :value="leaveApplications" :rows="4" class="dt">
                        <Column header="Type">
                            <template #body="{ data }">
                                <div class="leave-type-cell">
                                    <span class="leave-dot" :style="{ background: '#' + data.leave_type?.color }"></span>
                                    {{ data.leave_type?.name }}
                                </div>
                            </template>
                        </Column>
                        <Column header="Status" style="width:90px">
                            <template #body="{ data }"><Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" class="dt-tag" /></template>
                        </Column>
                        <Column header="" style="width:44px">
                            <template #body="{ data }"><button class="icon-btn" @click="openViewLeave(data)"><i class="pi pi-angle-right"></i></button></template>
                        </Column>
                    </DataTable>
                    <div v-if="!leaveApplications.length" class="empty"><i class="pi pi-calendar"></i><span>No leave applications</span></div>
                </div>

                <div class="card section-card">
                    <div class="card-head">
                        <span class="card-title"><span class="accent-bar rose"></span> Notifications & Warnings</span>
                        <Link :href="route('warnings.index')" class="view-all">History</Link>
                    </div>
                    <DataTable :value="warnings" :rows="4" class="dt">
                        <Column field="title" header="Notice"></Column>
                        <Column field="warning_date" header="Date" style="width:100px"></Column>
                        <Column header="Status" style="width:80px">
                            <template #body="{ data }">
                                <Tag :value="data.status === 'read' ? 'READ' : 'UNREAD'" :severity="data.status === 'read' ? 'success' : 'danger'" class="dt-tag" />
                            </template>
                        </Column>
                        <Column header="" style="width:64px">
                            <template #body="{ data }">
                                <div class="flex gap-1">
                                    <button v-if="data.status === 'pending'" class="icon-btn icon-btn-check" @click="markAsRead(data)"><i class="pi pi-check"></i></button>
                                    <button class="icon-btn" @click="openViewWarning(data)"><i class="pi pi-angle-right"></i></button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                    <div v-if="!warnings.length" class="empty italic"><span>No warnings. Keep up the good work!</span></div>
                </div>
            </div>

            <!-- ── DIALOGS ── -->

            <!-- Complaint -->
            <Dialog v-model:visible="complaintDialog" header="New Complaint" :style="{ width: '440px' }" modal class="dlg">
                <div class="dlg-body">
                    <div class="field"><label>Subject</label><InputText v-model="form.title" placeholder="Brief summary..." /></div>
                    <div class="field"><label>Description</label><Textarea v-model="form.description" rows="4" placeholder="Detailed explanation..." class="!resize-none" /></div>
                </div>
                <template #footer>
                    <div class="dlg-footer">
                        <button class="dlg-cancel" @click="complaintDialog = false">Cancel</button>
                        <button class="dlg-submit bg-indigo" @click="submitComplaint" :disabled="form.processing">{{ form.processing ? 'Submitting…' : 'Submit' }}</button>
                    </div>
                </template>
            </Dialog>

            <!-- Loan -->
            <Dialog v-model:visible="loanDialog" header="Request a Loan" :style="{ width: '440px' }" modal class="dlg">
                <div class="dlg-body">
                    <div class="field"><label>Amount (USD)</label><InputText v-model="loanForm.amount" type="number" placeholder="0.00" /></div>
                    <div class="field"><label>Installments (months)</label><InputText v-model="loanForm.installments" type="number" /></div>
                    <div v-if="loanForm.amount > 0" class="estimate-box">
                        <span class="estimate-lbl">Est. monthly deduction</span>
                        <span class="estimate-val">${{ (loanForm.amount / (loanForm.installments || 1)).toFixed(2) }}</span>
                    </div>
                    <div class="field"><label>Reason</label><Textarea v-model="loanForm.reason" rows="3" class="!resize-none" /></div>
                </div>
                <template #footer>
                    <div class="dlg-footer">
                        <button class="dlg-cancel" @click="loanDialog = false">Cancel</button>
                        <button class="dlg-submit bg-indigo" @click="submitLoanRequest" :disabled="loanForm.processing">{{ loanForm.processing ? 'Submitting…' : 'Submit' }}</button>
                    </div>
                </template>
            </Dialog>

            <!-- Advance -->
            <Dialog v-model:visible="advanceDialog" header="Request Salary Advance" :style="{ width: '440px' }" modal class="dlg">
                <div class="dlg-body">
                    <div class="field"><label>Amount (USD)</label><InputText v-model="advanceForm.amount" type="number" /></div>
                    <div class="field"><label>Reason</label><Textarea v-model="advanceForm.reason" rows="3" class="!resize-none" /></div>
                </div>
                <template #footer>
                    <div class="dlg-footer">
                        <button class="dlg-cancel" @click="advanceDialog = false">Cancel</button>
                        <button class="dlg-submit bg-violet" @click="submitAdvanceRequest" :disabled="advanceForm.processing">{{ advanceForm.processing ? 'Submitting…' : 'Submit' }}</button>
                    </div>
                </template>
            </Dialog>

            <!-- Leave -->
            <Dialog v-model:visible="leaveDialog" header="Request Leave" :style="{ width: '440px' }" modal class="dlg p-fluid">
                <div class="dlg-body">
                    <div class="field"><label>Leave Type</label><Dropdown v-model="leaveForm.leave_type_id" :options="leaveTypes" optionLabel="name" optionValue="id" placeholder="Select type" /></div>
                    <div class="two-fields">
                        <div class="field"><label>Start Date</label><DatePicker v-model="leaveForm.start_date" dateFormat="yy-mm-dd" showIcon /></div>
                        <div class="field"><label>End Date</label><DatePicker v-model="leaveForm.end_date" dateFormat="yy-mm-dd" showIcon :minDate="leaveForm.start_date" /></div>
                    </div>
                    <div class="field"><label>Reason</label><Textarea v-model="leaveForm.reason" rows="3" class="!resize-none" /></div>
                </div>
                <template #footer>
                    <div class="dlg-footer">
                        <button class="dlg-cancel" @click="leaveDialog = false">Cancel</button>
                        <button class="dlg-submit bg-emerald" @click="submitLeaveRequest" :disabled="leaveForm.processing">{{ leaveForm.processing ? 'Submitting…' : 'Submit' }}</button>
                    </div>
                </template>
            </Dialog>

            <!-- View: Meeting -->
            <Dialog v-model:visible="viewMeetingDialog" header="Meeting Details" :style="{ width: '420px' }" modal class="dlg">
                <div v-if="selectedItem" class="dlg-body">
                    <div class="view-hero blue-hero">
                        <div class="view-hero-title">{{ selectedItem.title }}</div>
                        <div class="view-hero-sub">{{ selectedItem.date }} · {{ selectedItem.start_time_fmt }}</div>
                    </div>
                    <p class="view-desc">{{ selectedItem.description || 'No description provided.' }}</p>
                </div>
                <template #footer><div class="dlg-footer"><button class="dlg-cancel" @click="viewMeetingDialog = false">Close</button></div></template>
            </Dialog>

            <!-- View: Complaint -->
            <Dialog v-model:visible="viewComplaintDialog" header="Complaint Details" :style="{ width: '420px' }" modal class="dlg">
                <div v-if="selectedItem" class="dlg-body">
                    <div class="view-hero amber-hero">
                        <div class="view-hero-title">{{ selectedItem.title }}</div>
                        <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" class="mt-2" />
                    </div>
                    <p class="view-desc">{{ selectedItem.description }}</p>
                    <div v-if="selectedItem.resolution_note" class="resolution-box">
                        <span class="resolution-lbl">HR Resolution</span>
                        <p>{{ selectedItem.resolution_note }}</p>
                    </div>
                </div>
                <template #footer><div class="dlg-footer"><button class="dlg-cancel" @click="viewComplaintDialog = false">Close</button></div></template>
            </Dialog>

            <!-- View: Warning -->
            <Dialog v-model:visible="viewWarningDialog" header="Warning Details" :style="{ width: '420px' }" modal class="dlg">
                <div v-if="selectedItem" class="dlg-body">
                    <div class="view-hero rose-hero">
                        <div class="view-hero-title">{{ selectedItem.title }}</div>
                        <div class="view-hero-sub">Received: {{ selectedItem.warning_date }}</div>
                    </div>
                    <p class="view-desc">{{ selectedItem.description }}</p>
                </div>
                <template #footer><div class="dlg-footer"><button class="dlg-cancel" @click="viewWarningDialog = false">Close</button></div></template>
            </Dialog>

            <!-- View: Loan -->
            <Dialog v-model:visible="viewLoanDialog" header="Loan Details" :style="{ width: '420px' }" modal class="dlg">
                <div v-if="selectedItem" class="dlg-body">
                    <div class="view-hero indigo-hero">
                        <div class="view-hero-amount">{{ formatCurrency(selectedItem.amount) }}</div>
                        <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" />
                    </div>
                    <div class="stat-row">
                        <div class="stat-cell"><span class="stat-lbl">Installments</span><span class="stat-val">{{ selectedItem.installments }} mo.</span></div>
                        <div class="stat-cell"><span class="stat-lbl">Monthly</span><span class="stat-val">{{ formatCurrency(selectedItem.monthly_installment) }}</span></div>
                    </div>
                    <p class="view-desc">{{ selectedItem.reason }}</p>
                </div>
                <template #footer><div class="dlg-footer"><button class="dlg-cancel" @click="viewLoanDialog = false">Close</button></div></template>
            </Dialog>

            <!-- View: Advance -->
            <Dialog v-model:visible="viewAdvanceDialog" header="Advance Details" :style="{ width: '420px' }" modal class="dlg">
                <div v-if="selectedItem" class="dlg-body">
                    <div class="view-hero violet-hero">
                        <div class="view-hero-amount">{{ formatCurrency(selectedItem.amount) }}</div>
                        <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" />
                    </div>
                    <p class="view-desc">{{ selectedItem.reason }}</p>
                    <div v-if="selectedItem.repayment_date" class="stat-row">
                        <div class="stat-cell"><span class="stat-lbl">Expected Repayment</span><span class="stat-val">{{ selectedItem.repayment_date }}</span></div>
                    </div>
                </div>
                <template #footer><div class="dlg-footer"><button class="dlg-cancel" @click="viewAdvanceDialog = false">Close</button></div></template>
            </Dialog>

            <!-- View: Leave -->
            <Dialog v-model:visible="viewLeaveDialog" header="Leave Details" :style="{ width: '420px' }" modal class="dlg">
                <div v-if="selectedItem" class="dlg-body">
                    <div class="view-hero teal-hero">
                        <div class="view-hero-title">{{ selectedItem.leave_type?.name }}</div>
                        <div class="view-hero-sub">{{ selectedItem.start_date }} → {{ selectedItem.end_date }} · {{ calculateDays(selectedItem.start_date, selectedItem.end_date) }} days</div>
                        <Tag :value="selectedItem.status.toUpperCase()" :severity="getStatusSeverity(selectedItem.status)" class="mt-2" />
                    </div>
                    <p class="view-desc">{{ selectedItem.reason }}</p>
                </div>
                <template #footer><div class="dlg-footer"><button class="dlg-cancel" @click="viewLeaveDialog = false">Close</button></div></template>
            </Dialog>

            <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message" :type="alertConfig.type" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ── Base ── */
.dash {
    padding: 1.5rem 1.75rem 3rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    background: #f8f9fb;
    min-height: 100vh;
}

/* ── Top Bar ── */
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.greeting { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin: 0 0 2px; }
.name-highlight { background: linear-gradient(135deg, #4f46e5, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.greeting-sub { font-size: 0.78rem; color: #94a3b8; font-weight: 500; }
.wave { display: inline-block; animation: wave 2.5s infinite; transform-origin: 70% 70%; }

.action-row { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.action-btn {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 0.9rem; border-radius: 8px; font-size: 0.75rem;
    font-weight: 700; border: 1px solid transparent; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.btn-indigo  { background: #eef2ff; color: #4338ca; border-color: #c7d2fe; }
.btn-indigo:hover  { background: #4338ca; color: #fff; }
.btn-violet  { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
.btn-violet:hover  { background: #6d28d9; color: #fff; }
.btn-slate   { background: #f1f5f9; color: #334155; border-color: #e2e8f0; }
.btn-slate:hover   { background: #334155; color: #fff; }
.btn-emerald { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
.btn-emerald:hover { background: #059669; color: #fff; }

/* ── Layout ── */
.row-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.25rem; align-items: start; }
.right-col { display: flex; flex-direction: column; gap: 1.25rem; }
.two-col   { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

/* ── Card ── */
.card {
    background: #fff;
    border: 1px solid #e9ecf0;
    border-radius: 14px;
    overflow: hidden;
}
.card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.85rem 1.1rem 0.6rem;
    border-bottom: 1px solid #f1f5f9;
}
.card-title { font-size: 0.72rem; font-weight: 800; color: #374151; text-transform: uppercase; letter-spacing: 0.08em; display: flex; align-items: center; gap: 0.4rem; }
.card-title .pi { color: #6366f1; font-size: 0.75rem; }

/* ── Accent bars ── */
.accent-bar { display: inline-block; width: 3px; height: 14px; border-radius: 2px; flex-shrink: 0; }
.accent-bar.blue   { background: #3b82f6; }
.accent-bar.amber  { background: #f59e0b; }
.accent-bar.teal   { background: #0d9488; }
.accent-bar.violet { background: #7c3aed; }
.accent-bar.rose   { background: #f43f5e; }

/* ── Badge ── */
.badge-active { font-size: 0.62rem; font-weight: 800; color: #059669; background: #dcfce7; border: 1px solid #bbf7d0; padding: 2px 8px; border-radius: 99px; letter-spacing: 0.05em; }

/* ── Attendance ── */
.attendance-card .card-head { padding: 0.85rem 1.1rem; }
.shift-info { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.1rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.micro-label { font-size: 0.62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; }
.shift-time { font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-top: 1px; }
.shift-icon { font-size: 1rem; color: #f59e0b; }

.clock-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; padding: 0.85rem 1.1rem; }
.clock-btn {
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    padding: 0.6rem; border-radius: 9px; font-size: 0.75rem; font-weight: 700;
    border: none; cursor: pointer; transition: all 0.2s;
}
.clock-in  { background: #dcfce7; color: #15803d; }
.clock-in:not(.clock-disabled):hover  { background: #16a34a; color: #fff; }
.clock-out { background: #fee2e2; color: #dc2626; }
.clock-out:not(.clock-disabled):hover { background: #dc2626; color: #fff; }
.clock-disabled { opacity: 0.35; cursor: not-allowed; filter: grayscale(0.6); }

.time-row { display: flex; align-items: center; padding: 0.7rem 1.1rem; border-top: 1px solid #f1f5f9; gap: 0; }
.time-cell { flex: 1; text-align: center; }
.time-divider { width: 1px; height: 28px; background: #e9ecf0; }
.time-val { font-size: 1rem; font-weight: 900; display: block; margin-top: 2px; }
.time-val.in  { color: #16a34a; }
.time-val.out { color: #dc2626; }

/* ── KPI ── */
.kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.75rem; }
.kpi-card {
    background: #fff; border: 1px solid #e9ecf0; border-radius: 12px;
    padding: 0.9rem 0.75rem; display: flex; flex-direction: column; align-items: center; gap: 0.3rem;
    transition: all 0.2s; cursor: default;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.kpi-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
.kpi-num  { font-size: 1.4rem; font-weight: 900; color: #0f172a; line-height: 1; }
.kpi-lbl  { font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; text-align: center; }

.kpi-indigo .kpi-icon { background: #eef2ff; color: #4338ca; }
.kpi-amber  .kpi-icon { background: #fffbeb; color: #d97706; }
.kpi-rose   .kpi-icon { background: #fff1f2; color: #e11d48; }
.kpi-teal   .kpi-icon { background: #f0fdfa; color: #0d9488; }
.kpi-violet .kpi-icon { background: #f5f3ff; color: #7c3aed; }

/* ── Leave Balances ── */
.leave-bal-card .card-head { padding-bottom: 0.6rem; }
.leave-bal-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.leave-bal-item {
    display: flex; align-items: flex-start; gap: 0.5rem;
    padding: 0.75rem 1rem; border-right: 1px solid #f1f5f9;
}
.leave-bal-item:last-child { border-right: none; }
.leave-bal-bar { width: 3px; height: 36px; border-radius: 2px; flex-shrink: 0; margin-top: 2px; }
.leave-bal-name { font-size: 0.68rem; font-weight: 700; color: #64748b; }
.leave-bal-count { font-size: 1.2rem; font-weight: 900; color: #0f172a; line-height: 1.1; }
.leave-bal-unit  { font-size: 0.62rem; font-weight: 500; color: #94a3b8; }
.leave-bal-used  { font-size: 0.62rem; color: #94a3b8; font-weight: 600; }

/* ── Section Cards (Tables) ── */
.section-card .card-head { padding: 0.85rem 1.1rem 0.65rem; }

.dt :deep(.p-datatable-thead > tr > th) {
    background: #f8fafc !important;
    font-size: 0.63rem; font-weight: 800; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.06em;
    padding: 0.5rem 0.85rem !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
.dt :deep(.p-datatable-tbody > tr > td) {
    padding: 0.55rem 0.85rem !important;
    font-size: 0.8rem; color: #374151; font-weight: 500;
    border-bottom: 1px solid #f8fafc !important;
}
.dt :deep(.p-datatable-tbody > tr:last-child > td) { border-bottom: none !important; }
.dt :deep(.p-datatable-tbody > tr:hover > td) { background: #fafbfd !important; }
.dt-tag { font-size: 0.58rem !important; padding: 2px 7px !important; font-weight: 800 !important; }

.icon-btn {
    width: 26px; height: 26px; border-radius: 6px; border: 1px solid #e9ecf0;
    background: #f8fafc; color: #6b7280; cursor: pointer; display: flex;
    align-items: center; justify-content: center; font-size: 0.75rem;
    transition: all 0.15s;
}
.icon-btn:hover { background: #6366f1; color: #fff; border-color: #6366f1; }
.icon-btn-check:hover { background: #10b981 !important; border-color: #10b981 !important; }

.leave-type-cell { display: flex; align-items: center; gap: 0.5rem; }
.leave-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

.empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 2rem 1rem; gap: 0.4rem; color: #cbd5e1; font-size: 0.8rem; font-weight: 500;
}
.empty .pi { font-size: 1.25rem; }

.view-all {
    font-size: 0.63rem; font-weight: 800; color: #6366f1;
    background: #eef2ff; border: 1px solid #c7d2fe;
    padding: 3px 10px; border-radius: 6px; text-transform: uppercase;
    letter-spacing: 0.05em; transition: all 0.15s;
}
.view-all:hover { background: #6366f1; color: #fff; }

/* ── Dialogs ── */
.dlg :deep(.p-dialog-header) { padding: 1rem 1.25rem 0.75rem; font-size: 0.95rem; font-weight: 800; }
.dlg :deep(.p-dialog-content) { padding: 0 !important; }
.dlg :deep(.p-dialog-footer) { padding: 0 !important; }
.dlg-body { padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; }
.two-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; }
.field :deep(.p-inputtext),
.field :deep(.p-textarea),
.field :deep(.p-dropdown),
.field :deep(.p-datepicker-input) {
    font-size: 0.82rem !important; border-radius: 8px !important;
    border-color: #e2e8f0 !important; padding: 0.45rem 0.7rem !important;
}

.estimate-box {
    display: flex; justify-content: space-between; align-items: center;
    background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px;
    padding: 0.6rem 0.85rem;
}
.estimate-lbl { font-size: 0.68rem; font-weight: 700; color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em; }
.estimate-val { font-size: 1rem; font-weight: 900; color: #3730a3; }

.dlg-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 0.75rem 1.25rem; border-top: 1px solid #f1f5f9; }
.dlg-cancel { padding: 0.45rem 1rem; border-radius: 7px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; cursor: pointer; transition: all 0.15s; }
.dlg-cancel:hover { background: #f1f5f9; }
.dlg-submit { padding: 0.45rem 1.1rem; border-radius: 7px; font-size: 0.75rem; font-weight: 700; border: none; color: #fff; cursor: pointer; transition: all 0.15s; }
.dlg-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.bg-indigo  { background: #4f46e5; } .bg-indigo:not(:disabled):hover  { background: #4338ca; }
.bg-violet  { background: #7c3aed; } .bg-violet:not(:disabled):hover  { background: #6d28d9; }
.bg-emerald { background: #059669; } .bg-emerald:not(:disabled):hover { background: #047857; }

/* ── View dialog hero ── */
.view-hero { padding: 0.9rem 1rem; border-radius: 10px; border: 1px solid; margin-bottom: 0; }
.view-hero-title  { font-size: 1rem; font-weight: 800; }
.view-hero-sub    { font-size: 0.73rem; font-weight: 600; margin-top: 3px; }
.view-hero-amount { font-size: 1.4rem; font-weight: 900; }
.blue-hero   { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
.amber-hero  { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.rose-hero   { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
.indigo-hero { background: #eef2ff; border-color: #c7d2fe; color: #3730a3; }
.violet-hero { background: #f5f3ff; border-color: #ddd6fe; color: #5b21b6; }
.teal-hero   { background: #f0fdfa; border-color: #99f6e4; color: #134e4a; }

.view-desc { font-size: 0.8rem; color: #475569; line-height: 1.6; padding: 0; margin: 0; }
.resolution-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.7rem 0.85rem; font-size: 0.78rem; color: #166534; }
.resolution-lbl { display: block; font-size: 0.63rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #16a34a; margin-bottom: 4px; }

.stat-row { display: flex; gap: 0.75rem; }
.stat-cell { flex: 1; background: #f8fafc; border: 1px solid #e9ecf0; border-radius: 8px; padding: 0.55rem 0.75rem; }
.stat-lbl { font-size: 0.62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; display: block; }
.stat-val { font-size: 0.88rem; font-weight: 800; color: #1e293b; }

/* ── Animations ── */
@keyframes wave {
    0%,60%,100% { transform: rotate(0deg); }
    10% { transform: rotate(14deg); }
    20% { transform: rotate(-8deg); }
    30% { transform: rotate(14deg); }
    40% { transform: rotate(-4deg); }
    50% { transform: rotate(10deg); }
}
</style>