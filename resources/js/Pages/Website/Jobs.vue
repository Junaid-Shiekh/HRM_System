<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import SweetAlert from '@/Components/SweetAlert.vue';

const props = defineProps({
    jobs: Array
});

const selectedJob = ref(null);
const showModal = ref(false);
const showApplyForm = ref(false);
const showSuccessAlert = ref(false);

const form = useForm({
    job_id: null,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    resume: null,
});

const openJobModal = (job) => {
    selectedJob.value = job;
    showModal.value = true;
    showApplyForm.value = false;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    showModal.value = false;
    showApplyForm.value = false;
    form.reset();
    document.body.style.overflow = 'auto';
};

const startApplication = () => {
    form.job_id = selectedJob.value.id;
    showApplyForm.value = true;
};

const submitApplication = () => {
    form.post(route('website.jobs.apply'), {
        onSuccess: () => {
            closeModal();
            showSuccessAlert.value = true;
        },
    });
};

const formatCurrency = (amount) => {
    if (!amount) return 'N/A';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0
    }).format(amount);
};

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('reveal-active');
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
});
</script>

<template>
    <WebsiteLayout>

        <Head title="Browse Open Roles - AIMANOVA Careers" />

        <SweetAlert v-model:visible="showSuccessAlert" title="Application Sent!"
            message="Your application has been submitted successfully. We will review it and get back to you soon."
            type="success" />

        <div class="jobs-page">
            <header class="page-header">
                <div class="max-w-4xl mx-auto px-6 reveal">
                    <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-4 block">Join Our
                        Team</span>
                    <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-5 leading-tight">Current Openings</h1>
                    <p class="text-base text-slate-500 font-medium">Explore job opportunities across specialized
                        industries and leading companies.</p>
                </div>
            </header>

            <div class="max-w-7xl mx-auto px-6 pb-24">
                <div v-if="jobs.length === 0"
                    class="flex flex-col items-center justify-center py-24 text-center reveal">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-3xl mb-6">📂
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 mb-3">No open positions at the moment</h2>
                    <p class="text-slate-500 font-medium text-sm">Check back later or follow us for updates.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="job in jobs" :key="job.id"
                        class="reveal p-7 bg-white rounded-2xl border border-slate-100 hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-50/60 transition-all group flex flex-col h-full">
                        <div
                            class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[9px] font-black uppercase tracking-widest mb-5 w-fit">
                            {{ job.category?.name || 'General' }}
                        </div>
                        <h3
                            class="text-lg font-black text-slate-900 mb-4 leading-snug group-hover:text-indigo-600 transition-colors">
                            {{ job.title }}
                        </h3>

                        <div class="space-y-2.5 mb-6 flex-grow">
                            <div class="flex items-center gap-2 text-slate-500 font-semibold text-xs">
                                <i class="pi pi-map-marker text-indigo-400 text-xs"></i>
                                {{ job.branch?.name || 'Remote' }}
                            </div>
                            <div class="flex items-center gap-2 text-slate-500 font-semibold text-xs">
                                <i class="pi pi-clock text-indigo-400 text-xs"></i>
                                {{ job.job_type?.replace('_', ' ') }}
                            </div>
                            <div class="mt-4 pt-4 border-t border-slate-50">
                                <span
                                    class="text-[9px] text-slate-400 font-black uppercase tracking-widest block mb-0.5">Salary
                                    Range</span>
                                <span class="text-base font-black text-slate-900">{{ formatCurrency(job.salary_min) }} –
                                    {{ formatCurrency(job.salary_max) }}</span>
                            </div>
                        </div>

                        <button @click="openJobModal(job)"
                            class="w-full py-3 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-indigo-600 transition-all hover:-translate-y-0.5 shadow-lg shadow-slate-100 group-hover:shadow-indigo-100">
                            Apply Now
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details Modal -->
        <transition name="modal-bounce">
            <div v-if="showModal"
                class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                @click.self="closeModal">
                <div
                    class="bg-white w-full max-w-xl max-h-[85vh] rounded-2xl shadow-2xl relative flex flex-col overflow-hidden">

                    <!-- Close Button -->
                    <button
                        class="absolute top-4 right-4 w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all z-50 text-xs"
                        @click="closeModal">
                        <i class="pi pi-times"></i>
                    </button>

                    <!-- Scrollable Content -->
                    <div class="overflow-y-auto flex-1 p-7">
                        <!-- Header -->
                        <div class="mb-5">
                            <div
                                class="inline-flex px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[9px] font-black uppercase tracking-widest mb-3">
                                {{ selectedJob.category?.name || 'General' }}
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 mb-2 leading-tight pr-8">{{ selectedJob.title
                                }}</h2>
                            <div
                                class="flex flex-wrap gap-3 text-slate-400 font-bold text-[10px] uppercase tracking-wider">
                                <span class="flex items-center gap-1"><i class="pi pi-map-marker"></i> {{
                                    selectedJob.branch?.name || 'Remote' }}</span>
                                <span class="text-slate-200">•</span>
                                <span class="flex items-center gap-1"><i class="pi pi-clock"></i> {{
                                    selectedJob.job_type?.replace('_', ' ') }}</span>
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div v-if="!showApplyForm" class="space-y-5">
                            <div>
                                <h3
                                    class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-3 pb-1.5 border-b border-indigo-100 inline-block">
                                    Job Description
                                </h3>
                                <div class="text-slate-500 text-sm font-medium leading-relaxed prose max-w-none prose-sm"
                                    v-html="selectedJob.description"></div>
                            </div>

                            <div v-if="selectedJob.requirements">
                                <h3
                                    class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-3 pb-1.5 border-b border-fuchsia-100 inline-block">
                                    Requirements
                                </h3>
                                <div class="text-slate-500 text-sm font-medium leading-relaxed prose max-w-none prose-sm"
                                    v-html="selectedJob.requirements"></div>
                            </div>

                            <!-- Salary Box -->
                            <div class="p-5 bg-indigo-50 rounded-xl border border-indigo-100/80">
                                <span
                                    class="text-[9px] text-indigo-400 font-black uppercase tracking-widest block mb-1">Estimated
                                    Salary</span>
                                <span class="text-xl font-black text-indigo-700">
                                    {{ formatCurrency(selectedJob.salary_min) }} – {{
                                    formatCurrency(selectedJob.salary_max) }}
                                    <span class="text-sm font-semibold text-indigo-400">/year</span>
                                </span>
                            </div>
                        </div>

                        <!-- Apply Form -->
                        <div v-else>
                            <h3 class="text-lg font-black text-slate-900 mb-5">Submit Your Application</h3>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-1">First
                                        Name</label>
                                    <input v-model="form.first_name" type="text" placeholder="John"
                                        class="w-full px-4 py-3 bg-slate-50 border border-transparent rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium text-sm transition-all"
                                        :class="{ 'ring-2 ring-red-400 bg-red-50': form.errors.first_name }">
                                    <div v-if="form.errors.first_name" class="text-xs text-red-500 pl-1 font-semibold">
                                        {{ form.errors.first_name }}</div>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-1">Last
                                        Name</label>
                                    <input v-model="form.last_name" type="text" placeholder="Doe"
                                        class="w-full px-4 py-3 bg-slate-50 border border-transparent rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium text-sm transition-all"
                                        :class="{ 'ring-2 ring-red-400 bg-red-50': form.errors.last_name }">
                                    <div v-if="form.errors.last_name" class="text-xs text-red-500 pl-1 font-semibold">{{
                                        form.errors.last_name }}</div>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-1">Email
                                        Address</label>
                                    <input v-model="form.email" type="email" placeholder="john@example.com"
                                        class="w-full px-4 py-3 bg-slate-50 border border-transparent rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium text-sm transition-all"
                                        :class="{ 'ring-2 ring-red-400 bg-red-50': form.errors.email }">
                                    <div v-if="form.errors.email" class="text-xs text-red-500 pl-1 font-semibold">{{
                                        form.errors.email }}</div>
                                </div>
                                <div class="space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-1">Phone
                                        Number</label>
                                    <input v-model="form.phone" type="tel" placeholder="+1 (234) 567-890"
                                        class="w-full px-4 py-3 bg-slate-50 border border-transparent rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white font-medium text-sm transition-all"
                                        :class="{ 'ring-2 ring-red-400 bg-red-50': form.errors.phone }">
                                    <div v-if="form.errors.phone" class="text-xs text-red-500 pl-1 font-semibold">{{
                                        form.errors.phone }}</div>
                                </div>
                                <div class="md:col-span-2 space-y-1">
                                    <label
                                        class="text-[9px] font-black text-slate-400 uppercase tracking-widest pl-1">CV /
                                        Resume (PDF, DOC)</label>
                                    <div class="relative">
                                        <input type="file" @input="form.resume = $event.target.files[0]"
                                            id="resume-upload" hidden accept=".pdf,.doc,.docx">
                                        <label for="resume-upload"
                                            class="flex flex-col items-center justify-center p-6 bg-indigo-50/60 border-2 border-dashed border-indigo-200 rounded-xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-400 transition-all"
                                            :class="{ 'border-red-400 bg-red-50': form.errors.resume }">
                                            <i class="pi pi-upload text-xl mb-2"
                                                :class="form.errors.resume ? 'text-red-400' : 'text-indigo-500'"></i>
                                            <span class="font-bold text-sm text-center"
                                                :class="form.errors.resume ? 'text-red-500' : 'text-indigo-600'">
                                                {{ form.resume ? form.resume.name : 'Click to upload your resume' }}
                                            </span>
                                            <span class="text-slate-400 text-xs font-medium mt-1">PDF, DOC up to
                                                5MB</span>
                                            <div v-if="form.errors.resume"
                                                class="text-xs text-red-500 font-semibold mt-1">{{ form.errors.resume }}
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-7 py-4 bg-white border-t border-slate-100 flex-shrink-0">
                        <button v-if="!showApplyForm" @click="startApplication"
                            class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all text-sm shadow-lg shadow-indigo-100 hover:shadow-indigo-200">
                            Apply for this Position
                        </button>
                        <div v-else class="flex gap-3">
                            <button @click="showApplyForm = false"
                                class="px-6 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-all text-sm">
                                Back
                            </button>
                            <button @click="submitApplication"
                                class="flex-grow py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all text-sm shadow-lg shadow-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="form.processing">
                                {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </WebsiteLayout>
</template>

<style scoped>
.jobs-page {
    padding-top: 80px;
}

.page-header {
    padding: 6rem 0 3.5rem;
    text-align: center;
    background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.04) 0%, transparent 70%);
}

.reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: all 1s cubic-bezier(0.19, 1, 0.22, 1);
}

.reveal-active {
    opacity: 1;
    transform: translateY(0);
}

.modal-bounce-enter-active {
    animation: bounce 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal-bounce-leave-active {
    transition: all 0.2s ease-in;
}

.modal-bounce-enter-from,
.modal-bounce-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
}

@keyframes bounce {
    0% {
        opacity: 0;
        transform: scale(0.95) translateY(20px);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
</style>