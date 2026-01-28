<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'primevue/chart';
import { ref, onMounted } from 'vue';

const props = defineProps({
    stats: Object,
    charts: Object,
    recent: Object
});

// Chart.js options for a premium look
const chartOptions = {
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: { size: 11, weight: '600' }
            }
        },
        tooltip: {
            backgroundColor: '#1C0D82',
            padding: 12,
            cornerRadius: 8,
        }
    },
    responsive: true,
    maintainAspectRatio: false
};

const barOptions = {
    ...chartOptions,
    scales: {
        y: {
            beginAtZero: true,
            grid: { display: false }
        },
        x: { grid: { display: false } }
    }
};

// 1. Department Distribution (Donut)
const deptData = ref({
    labels: props.charts.deptDistribution.map(d => d.name),
    datasets: [{
        data: props.charts.deptDistribution.map(d => d.count),
        backgroundColor: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4'],
        hoverOffset: 4,
        borderWidth: 0
    }]
});

// 2. Hiring Trend (Bar - as per pic)
const trendData = ref({
    labels: props.charts.hiringTrend.map(d => d.month),
    datasets: [{
        label: 'New Employees',
        data: props.charts.hiringTrend.map(d => d.count),
        backgroundColor: '#3B82F6',
        borderRadius: 8,
        barPercentage: 0.6
    }]
});

// 3. Candidate Status (Pie)
const candidateStatusData = ref({
    labels: props.charts.candidateStatus.map(d => d.status),
    datasets: [{
        data: props.charts.candidateStatus.map(d => d.count),
        backgroundColor: ['#10B981', '#3B82F6', '#6366F1', '#F59E0B', '#EF4444', '#06B6D4'],
        borderWidth: 0
    }]
});

// 4. Leave Types Distribution (Doughnut)
const leaveTypesData = ref({
    labels: props.charts.leaveTypes.map(d => d.name),
    datasets: [{
        data: props.charts.leaveTypes.map(d => d.count),
        backgroundColor: ['#10B981', '#F43F5E', '#0EA5E9', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4'],
        borderWidth: 0
    }]
});

// 5. Employee Growth (Line)
const growthData = ref({
    labels: props.charts.employeeGrowth.map(d => d.month),
    datasets: [{
        label: 'Employees',
        data: props.charts.employeeGrowth.map(d => d.count),
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 0
    }]
});

const lineOptions = {
    ...chartOptions,
    scales: {
        y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
        x: { grid: { display: false } }
    },
    plugins: {
        ...chartOptions.plugins,
        legend: { display: false }
    }
};

</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-8 pb-10">
            <!-- Header section -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Dashboard</h1>
                </div>
                <button @click="$inertia.reload()"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-600 shadow-sm hover:bg-gray-50 transition-all">
                    <i class="pi pi-refresh"></i>
                    Refresh
                </button>
            </div>

            <!-- Stats Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Employees -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Employees
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.totalEmployees }}</div>
                        <div class="text-green-500 text-[10px] mt-2 font-bold">+{{ stats.employeesThisMonth }} this
                            month</div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm">
                        <i class="pi pi-users text-xl"></i>
                    </div>
                </div>

                <!-- Branches -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Branches</div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.totalBranches }}</div>
                        <div class="text-gray-400 text-[10px] mt-2 font-medium">{{ stats.totalDepartments }} departments
                        </div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-sm">
                        <i class="pi pi-building text-xl"></i>
                    </div>
                </div>

                <!-- Attendance placeholder (matching pic) -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Attendance Rate
                        </div>
                        <div class="text-3xl font-black text-gray-800">20%</div>
                        <div class="text-gray-400 text-[10px] mt-2 font-medium">2 present today</div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 shadow-sm">
                        <i class="pi pi-clock text-xl"></i>
                    </div>
                </div>

                <!-- Pending Leaves -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Pending Leaves
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.pendingLeaves }}</div>
                        <div class="text-gray-400 text-[10px] mt-2 font-medium">{{ stats.onLeaveToday }} on leave today
                        </div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-500 shadow-sm">
                        <i class="pi pi-calendar-plus text-xl"></i>
                    </div>
                </div>

                <!-- Active Jobs -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Active Jobs
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.activeJobs }}</div>
                        <div class="text-orange-500 text-[10px] mt-2 font-bold">+{{ stats.jobsThisMonth }} this month
                        </div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 shadow-sm">
                        <i class="pi pi-briefcase text-xl"></i>
                    </div>
                </div>

                <!-- Total Candidates -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Candidates
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.totalCandidates }}</div>
                        <div class="text-indigo-500 text-[10px] mt-2 font-bold">+{{ stats.candidatesThisMonth }} this
                            month</div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 shadow-sm">
                        <i class="pi pi-user-plus text-xl"></i>
                    </div>
                </div>

                <!-- Pending Loans -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Pending Loans
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.pendingLoans }}</div>
                        <div class="text-blue-500 text-[10px] mt-2 font-bold">Awaiting Approval</div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm">
                        <i class="pi pi-money-bill text-xl"></i>
                    </div>
                </div>

                <!-- Pending Advances -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Pending Advances
                        </div>
                        <div class="text-3xl font-black text-gray-800">{{ stats.pendingAdvances }}</div>
                        <div class="text-fuchsia-500 text-[10px] mt-2 font-bold">Salary Advances</div>
                    </div>
                    <div
                        class="h-12 w-12 rounded-full bg-fuchsia-50 flex items-center justify-center text-fuchsia-500 shadow-sm">
                        <i class="pi pi-wallet text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Top Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dept Distribution -->
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                        <i class="pi pi-chart-bar text-gray-400"></i>
                        Department Distribution
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <Chart type="doughnut" :data="deptData" :options="chartOptions"
                            class="h-full w-full max-w-[280px]" />
                    </div>
                </div>

                <!-- Hiring Trend -->
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                        <i class="pi pi-trending-up text-gray-400"></i>
                        Hiring Trend (6 Months)
                    </h3>
                    <div class="h-64">
                        <Chart type="bar" :data="trendData" :options="barOptions" class="h-full w-full" />
                    </div>
                </div>
            </div>

            <!-- Middle Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Candidate Status -->
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                        <i class="pi pi-users text-gray-400"></i>
                        Candidate Status
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <Chart type="pie" :data="candidateStatusData" :options="chartOptions"
                            class="h-full w-full max-w-[280px]" />
                    </div>
                </div>

                <!-- Leave Types Distribution -->
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                        <i class="pi pi-calendar text-gray-400"></i>
                        Leave Types
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <Chart type="doughnut" :data="leaveTypesData" :options="chartOptions"
                            class="h-full w-full max-w-[280px]" />
                    </div>
                </div>
            </div>

            <!-- Activity Feeds Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Leave Applications -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-6 pb-0 flex justify-between items-center">
                        <h3 class="text-sm font-black text-gray-800 flex items-center gap-2">
                            <i class="pi pi-calendar-minus text-gray-400"></i>
                            Recent Leave Applications
                        </h3>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-bold">{{
                            props.stats.pendingLeaves }}</span>
                        <a href="/leave-applications" class="text-[10px] text-blue-600 font-bold hover:underline">View
                            All</a>
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="app in recent.leaveApplications" :key="app.id"
                            class="flex items-center justify-between p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ app.employee.first_name }} {{
                                    app.employee.last_name }} <span
                                        class="bg-green-100 text-green-700 text-[10px] px-1.5 py-0.5 rounded ml-2">{{
                                            app.status }}</span></p>
                                <p class="text-[10px] text-gray-500 mt-1">{{ app.leave_type.name }} • {{ app.start_date
                                    }} - {{ app.end_date }}</p>
                            </div>
                        </div>
                        <div v-if="!recent.leaveApplications.length" class="text-center py-6 text-gray-400 text-xs">No
                            recent applications</div>
                    </div>
                </div>

                <!-- Recent Candidates -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-6 pb-0 flex justify-between items-center">
                        <h3 class="text-sm font-black text-gray-800 flex items-center gap-2">
                            <i class="pi pi-user-plus text-gray-400"></i>
                            Recent Candidates
                        </h3>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-bold">{{
                            props.stats.totalCandidates }}</span>
                        <a href="/candidates" class="text-[10px] text-blue-600 font-bold hover:underline">View All</a>
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="cand in recent.candidates" :key="cand.id"
                            class="flex items-center justify-between p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ cand.name }} <span
                                        class="bg-blue-100 text-blue-700 text-[10px] px-1.5 py-0.5 rounded ml-2">{{
                                            cand.status }}</span></p>
                                <p class="text-[10px] text-gray-500 mt-1">{{ cand.email }} • Applied {{
                                    cand.created_at_fmt }}</p>
                            </div>
                        </div>
                        <div v-if="!recent.candidates.length" class="text-center py-6 text-gray-400 text-xs">No recent
                            candidates</div>
                    </div>
                </div>
            </div>

            <!-- Row 4: Announcements & Meetings -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Announcements Placeholder -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-6 pb-0 flex justify-between items-center">
                        <h3 class="text-sm font-black text-gray-800 flex items-center gap-2">
                            <i class="pi pi-megaphone text-gray-400"></i>
                            Recent Announcements
                        </h3>
                        <a href="#" class="text-[10px] text-blue-600 font-bold hover:underline">View All</a>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                            <p class="text-xs font-bold text-gray-800">Welcome to New Financial Year 2025 <span
                                    class="bg-red-100 text-red-700 text-[10px] px-1.5 py-0.5 rounded ml-2">High
                                    Priority</span></p>
                            <p class="text-[10px] text-gray-500 mt-1">Company News • Sep 18, 2025</p>
                        </div>
                        <div class="p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                            <p class="text-xs font-bold text-gray-800">Updated Employee Handbook and Policies <span
                                    class="bg-red-100 text-red-700 text-[10px] px-1.5 py-0.5 rounded ml-2">High
                                    Priority</span></p>
                            <p class="text-[10px] text-gray-500 mt-1">Policy Updates • Sep 18, 2025</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Meetings -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="p-6 pb-0 flex justify-between items-center">
                        <h3 class="text-sm font-black text-gray-800 flex items-center gap-2">
                            <i class="pi pi-users text-gray-400"></i>
                            Recent Meetings
                        </h3>
                        <a href="/meetings" class="text-[10px] text-blue-600 font-bold hover:underline">View All</a>
                    </div>
                    <div class="p-4 space-y-3">
                        <div v-for="meeting in recent.meetings" :key="meeting.id"
                            class="flex items-center justify-between p-3 border border-gray-100 rounded-xl bg-gray-50/50">
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ meeting.title }} <span
                                        class="bg-gray-100 text-gray-700 text-[10px] px-1.5 py-0.5 rounded ml-2">Scheduled</span>
                                </p>
                                <p class="text-[10px] text-gray-500 mt-1">{{ meeting.date }} • {{ meeting.start_time_fmt
                                }} - {{ meeting.end_time_fmt }}</p>
                            </div>
                        </div>
                        <div v-if="!recent.meetings.length" class="text-center py-6 text-gray-400 text-xs">No recent
                            meetings</div>
                    </div>
                </div>
            </div>

            <!-- Full Width Growth Chart -->
            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2 uppercase tracking-widest">
                    <i class="pi pi-chart-line text-gray-400"></i>
                    Employee Growth (2025)
                </h3>
                <div class="h-80">
                    <Chart type="line" :data="growthData" :options="lineOptions" class="h-full w-full" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
:deep(.p-chart) {
    height: 100% !important;
    width: 100% !important;
}
</style>
