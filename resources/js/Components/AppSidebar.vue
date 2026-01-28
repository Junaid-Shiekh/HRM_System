<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const isEmployee = computed(() => {
    return page.props.auth?.user?.roles?.some(r => r.name === 'employee') || false;
});

const menuItems = ref([
    {
        label: 'Platform',
        expanded: false,
        items: [
            { label: 'Dashboard', route: 'dashboard', icon: 'pi pi-th-large' }
        ]
    },
    {
        label: 'Staff',
        expanded: false,
        items: [
            { label: 'Users', route: 'users.index', icon: 'pi pi-users' },
            { label: 'Roles', route: 'roles.index', icon: 'pi pi-lock' },
            { label: 'Permissions', route: 'permissions.index', icon: 'pi pi-key' }
        ]
    },
    {
        label: 'Aimanova Management',
        expanded: false,
        items: [
            { label: 'Branches', route: 'branches.index', icon: 'pi pi-building' },
            { label: 'Departments', route: 'departments.index', icon: 'pi pi-sitemap' },
            { label: 'Designations', route: 'designations.index', icon: 'pi pi-id-card' },
            { label: 'Employees', route: 'employees.index', icon: 'pi pi-users' },
            { label: 'Employee Credentials', route: 'employee-credentials.index', icon: 'pi pi-key' },
            { label: 'Documents', route: 'documents.index', icon: 'pi pi-folder' },
            { label: 'Complaints', route: 'complaints.index', icon: 'pi pi-exclamation-circle' },
            { label: 'Employee Warnings', route: 'warnings.index', icon: 'pi pi-exclamation-triangle' }
        ]
    },
    {
        label: 'Meetings',
        expanded: false,
        items: [
            { label: 'Meeting Types', route: 'meeting-types.index', icon: 'pi pi-list' },
            { label: 'Meeting Rooms', route: 'meeting-rooms.index', icon: 'pi pi-building' },
            { label: 'Meetings', route: 'meetings.index', icon: 'pi pi-calendar' }
        ]
    },
    {
        label: 'Contract Management',
        expanded: false,
        items: [
            { label: 'Contract Types', route: 'contract-types.index', icon: 'pi pi-list' },
            { label: 'Contracts', route: 'contracts.index', icon: 'pi pi-file' }
        ]
    },
    {
        label: 'Recruitment',
        expanded: false,
        items: [
            { label: 'Jobs', route: 'job-postings.index', icon: 'pi pi-briefcase' },
            { label: 'Applicants', route: 'job-applications.index', icon: 'pi pi-file-edit' },
            { label: 'Candidates', route: 'candidates.index', icon: 'pi pi-user-plus' },
            { label: 'Categories', route: 'job-categories.index', icon: 'pi pi-list' }
        ]
    },
    {
        label: 'Leave Management',
        expanded: false,
        items: [
            { label: 'Leave Types', route: 'leave-types.index', icon: 'pi pi-list' },
            { label: 'Leave Policies', route: 'leave-policies.index', icon: 'pi pi-shield' },
            { label: 'Leave Applications', route: 'leave-applications.index', icon: 'pi pi-calendar-plus' }
        ]
    },
    {
        label: 'Attendance Management',
        expanded: false,
        items: [
            { label: 'Shifts', route: 'attendance-shifts.index', icon: 'pi pi-clock' },
            { label: 'Attendance Policies', route: 'attendance-policies.index', icon: 'pi pi-shield' },
            { label: 'Attendance Records', route: 'attendance-records.index', icon: 'pi pi-calendar' },
            { label: 'Attendance Regularization', route: 'attendance-regularizations.index', icon: 'pi pi-check-square' }
        ]
    },
    {
        label: 'Payroll & Finance',
        expanded: false,
        items: [
            { label: 'Salary Components', route: 'salary-components.index', icon: 'pi pi-list' },
            { label: 'Payroll Setup', route: 'salary-profiles.index', icon: 'pi pi-cog' },
            { label: 'Process Payroll', route: 'payrolls.index', icon: 'pi pi-money-bill' },
            { label: 'Loans & Advances', route: 'loans.index', icon: 'pi pi-wallet' }
        ]
    }
]);

const filteredMenuItems = computed(() => {
    if (!isEmployee.value) return menuItems.value;

    return [
        {
            label: 'Platform',
            expanded: false,
            items: [
                { label: 'Dashboard', route: 'employee.dashboard', icon: 'pi pi-th-large' }
            ]
        },
        {
            label: 'Office Portal',
            expanded: false,
            items: [
                { label: 'Meetings', route: 'meetings.index', icon: 'pi pi-calendar' },
                { label: 'Complaints', route: 'complaints.index', icon: 'pi pi-exclamation-circle' },
                { label: 'Warnings', route: 'warnings.index', icon: 'pi pi-exclamation-triangle' }
            ]
        }
    ];
});

// Initialize expanded state from localStorage or current route
onMounted(() => {
    // const savedStates = JSON.parse(localStorage.getItem('sidebar_expanded_states') || '{}');

    filteredMenuItems.value.forEach((section, index) => {
        // First check if it was manually expanded/collapsed
        // if (savedStates[section.label] !== undefined) {
        //     section.expanded = savedStates[section.label];
        // }

        // Then override if it contains the active route (to ensure visibility)
        const hasActiveItem = section.items.some(item => route().current(item.route));
        if (hasActiveItem) {
            section.expanded = true;
        }
    });
});

const toggleSection = (index) => {
    filteredMenuItems.value[index].expanded = !filteredMenuItems.value[index].expanded;

    // Save to localStorage
    const savedStates = JSON.parse(localStorage.getItem('sidebar_expanded_states') || '{}');
    savedStates[filteredMenuItems.value[index].label] = filteredMenuItems.value[index].expanded;
    localStorage.setItem('sidebar_expanded_states', JSON.stringify(savedStates));
};
</script>

<template>
    <div class="h-screen w-64 bg-white border-r border-gray-200 flex flex-col fixed left-0 top-0 overflow-y-auto">
        <!-- Logo -->
        <div class="h-24 flex items-center px-6 mb-2 border-b border-gray-50 bg-gray-50/10 pt-4">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="w-10 h-10 mb-3 fill-current text-[#1C0D82]" />
                <div class="flex flex-col">
                    <span
                        class="text-2xl font-black tracking-tighter mt-2 text-[#1C0D82] leading-none uppercase">Aimanova</span>
                    <span
                        class="text-[10px] uppercase tracking-[0.3em] font-bold text-black leading-none mt-2 ml-2 opacity-90">HRM
                        System</span>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <div class="flex-1 py-4">
            <template v-for="(section, index) in filteredMenuItems" :key="index">
                <button @click="toggleSection(index)"
                    class="w-full px-6 py-2 flex items-center justify-between text-xs font-medium text-black uppercase tracking-wider hover:text-gray-700 focus:outline-none">
                    <span>{{ section.label }}</span>
                    <i class="pi pi-chevron-down text-[10px] transition-transform duration-200"
                        :class="{ 'rotate-180': section.expanded }"></i>
                </button>

                <div v-show="section.expanded" class="space-y-1 px-3 mb-4 transition-all duration-300 ease-in-out">
                    <Link v-for="item in section.items" :key="item.label"
                        :href="route().has(item.route) ? route(item.route) : '#'"
                        class="flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors duration-150 group"
                        :class="[
                            route().current(item.route)
                                ? 'bg-indigo-50 text-indigo-700'
                                : 'text-black hover:bg-[#1C0D82] hover:text-white'
                        ]">
                        <i
                            :class="[item.icon, 'mr-3 text-lg', route().current(item.route) ? 'text-indigo-700' : 'text-black group-hover:text-white']"></i>
                        <span class="flex-1">{{ item.label }}</span>
                        <span v-if="item.label === 'Applicants' && $page.props.applicantCount > 0"
                            class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full min-w-[18px] text-center">
                            {{ $page.props.applicantCount }}
                        </span>
                        <span v-if="item.label === 'Complaints' && !isEmployee && $page.props.pendingComplaintCount > 0"
                            class="ml-auto bg-[#1C0D82] text-white text-[10px] font-bold px-2 py-0.5 rounded-full min-w-[18px] text-center border border-white/20">
                            {{ $page.props.pendingComplaintCount }}
                        </span>
                        <span
                            v-if="item.label === 'Employee Warnings' && !isEmployee && $page.props.pendingWarningCount > 0"
                            class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full min-w-[18px] text-center border border-white/20">
                            {{ $page.props.pendingWarningCount }}
                        </span>
                        <span v-if="item.label === 'Warnings' && isEmployee && $page.props.pendingWarningCount > 0"
                            class="ml-auto bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full min-w-[18px] text-center border border-white/20">
                            {{ $page.props.pendingWarningCount }}
                        </span>
                        <span
                            v-if="item.label === 'Loans & Advances' && !isEmployee && ($page.props.pendingLoanCount + $page.props.pendingAdvanceCount) > 0"
                            class="ml-auto bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full min-w-[18px] text-center border border-white/20">
                            {{ $page.props.pendingLoanCount + $page.props.pendingAdvanceCount }}
                        </span>
                    </Link>

                </div>
            </template>
        </div>

        <!-- Logout Section -->
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <Link :href="route('logout')" method="post" as="button"
                class="w-full flex items-center px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-100 rounded-md transition-colors duration-150 group">
                <i class="pi pi-sign-out mr-3 text-lg group-hover:text-red-700"></i>
                <span class="group-hover:text-red-700">Logout</span>
            </Link>
        </div>
    </div>
</template>
