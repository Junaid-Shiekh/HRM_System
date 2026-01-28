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
                    <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-6 block">Join Our
                        Team</span>
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-slate-900 mb-8 leading-tight">Current
                        Openings</h1>
                    <p class="text-xl text-slate-500 font-medium font-medium">Explore thousands of job opportunities
                        across specialized industries and leading companies.</p>
                </div>
            </header>

            <div class="max-w-7xl mx-auto px-6 pb-32">
                <div v-if="jobs.length === 0"
                    class="flex flex-col items-center justify-center py-32 text-center reveal">
                    <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center text-4xl mb-8">📂
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 mb-4">No open positions at the moment</h2>
                    <p class="text-slate-500 font-medium">Check back later or follow us for updates.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="job in jobs" :key="job.id"
                        class="reveal p-10 bg-white rounded-[3rem] border border-slate-100 hover:border-indigo-600 hover:shadow-2xl hover:shadow-indigo-50 transition-all group flex flex-col h-full">
                        <div
                            class="inline-flex px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest mb-8 w-fit">
                            {{ job.category?.name || 'General' }}
                        </div>
                        <h3
                            class="text-2xl font-black text-slate-900 mb-6 leading-snug group-hover:text-indigo-600 transition-colors">
                            {{ job.title }}</h3>

                        <div class="space-y-4 mb-10 flex-grow">
                            <div class="flex items-center gap-3 text-slate-500 font-bold text-sm">
                                <i class="pi pi-map-marker text-indigo-400"></i>
                                {{ job.branch?.name || 'Remote' }}
                            </div>
                            <div class="flex items-center gap-3 text-slate-500 font-bold text-sm">
                                <i class="pi pi-clock text-indigo-400"></i>
                                {{ job.job_type?.replace('_', ' ') }}
                            </div>
                            <div class="mt-6 pt-6 border-t border-slate-50">
                                <span
                                    class="text-[10px] text-slate-400 font-black uppercase tracking-widest block mb-1">Salary
                                    Range</span>
                                <span class="text-lg font-black text-slate-900">{{ formatCurrency(job.salary_min) }} -
                                    {{ formatCurrency(job.salary_max) }}</span>
                            </div>
                        </div>

                        <button @click="openJobModal(job)"
                            class="w-full py-5 bg-slate-900 text-white font-black rounded-2xl hover:bg-indigo-600 transition-all hover:-translate-y-1 shadow-xl shadow-slate-100 group-hover:shadow-indigo-100">
                            Apply Now
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details Modal -->
        <transition name="modal-bounce">
            <div v-if="showModal"
                class="fixed inset-0 z-[2000] flex items-center justify-center p-4 md:p-6 bg-slate-900/40 backdrop-blur-xl"
                @click.self="closeModal">
                <div
                    class="bg-white w-full max-w-4xl max-h-[90vh] rounded-[2rem] md:rounded-[4rem] shadow-2xl relative flex flex-col overflow-hidden">
                    <button
                        class="absolute top-6 right-6 md:top-8 md:right-8 w-10 h-10 md:w-12 md:h-12 bg-slate-50 rounded-full flex items-center justify-center text-slate-900 hover:bg-slate-100 transition-all z-50"
                        @click="closeModal">
                        <i class="pi pi-times"></i>
                    </button>

                    <div class="modal-header p-8 md:p-12 lg:p-20 pb-0 overflow-y-auto">
                        <div
                            class="inline-flex px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest mb-6">
                            {{ selectedJob.category?.name || 'General' }}
                        </div>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-black text-slate-900 mb-4">{{ selectedJob.title
                        }}</h2>
                        <div
                            class="flex flex-wrap gap-4 text-slate-400 font-black text-[10px] uppercase tracking-widest">
                            <span><i class="pi pi-map-marker mr-1"></i> {{ selectedJob.branch?.name || 'Remote'
                                }}</span>
                            <span>•</span>
                            <span><i class="pi pi-clock mr-1"></i> {{ selectedJob.job_type?.replace('_', ' ') }}</span>
                        </div>

                        <div class="mt-12 space-y-12 pb-20">
                            <div v-if="!showApplyForm">
                                <div class="mb-12">
                                    <h3
                                        class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 border-b border-indigo-100 pb-2 inline-block">
                                        Job Description</h3>
                                    <div class="text-slate-500 font-medium leading-relaxed prose max-w-none"
                                        v-html="selectedJob.description"></div>
                                </div>

                                <div class="mb-12" v-if="selectedJob.requirements">
                                    <h3
                                        class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 border-b border-fuchsia-100 pb-2 inline-block">
                                        Requirements</h3>
                                    <div class="text-slate-500 font-medium leading-relaxed prose max-w-none"
                                        v-html="selectedJob.requirements"></div>
                                </div>

                                <div class="p-10 bg-indigo-50 rounded-[3rem] border border-indigo-100">
                                    <span
                                        class="text-[10px] text-indigo-400 font-black uppercase tracking-widest block mb-2">Estimated
                                        Salary</span>
                                    <span class="text-3xl font-black text-indigo-700">{{
                                        formatCurrency(selectedJob.salary_min) }} - {{
                                            formatCurrency(selectedJob.salary_max) }} /Year</span>
                                </div>
                            </div>

                            <!-- Apply Form -->
                            <div v-else class="reveal">
                                <h3 class="text-3xl font-black text-slate-900 mb-10">Submit Your Application</h3>
                                <div class="grid md:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">First
                                            Name</label>
                                        <input v-model="form.first_name" type="text" placeholder="John"
                                            class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl focus:ring-2 focus:ring-indigo-600 font-medium">
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Last
                                            Name</label>
                                        <input v-model="form.last_name" type="text" placeholder="Doe"
                                            class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl focus:ring-2 focus:ring-indigo-600 font-medium">
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Email
                                            Address</label>
                                        <input v-model="form.email" type="email" placeholder="john@example.com"
                                            class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl focus:ring-2 focus:ring-indigo-600 font-medium">
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Phone
                                            Number</label>
                                        <input v-model="form.phone" type="tel" placeholder="+1 (234) 567-890"
                                            class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl focus:ring-2 focus:ring-indigo-600 font-medium">
                                    </div>
                                    <div class="md:col-span-2 space-y-2">
                                        <label
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">CV
                                            / Resume (PDF, DOC)</label>
                                        <div class="relative">
                                            <input type="file" @input="form.resume = $event.target.files[0]"
                                                id="resume-upload" hidden accept=".pdf,.doc,.docx">
                                            <label for="resume-upload"
                                                class="flex flex-col items-center justify-center p-12 bg-indigo-50/50 border-2 border-dashed border-indigo-200 rounded-[3rem] cursor-pointer hover:bg-indigo-50 hover:border-indigo-400 transition-all">
                                                <i class="pi pi-upload text-3xl text-indigo-600 mb-4"></i>
                                                <span class="text-indigo-600 font-black text-lg">{{ form.resume ?
                                                    form.resume.name : 'Click to upload your resume' }}</span>
                                                <span class="text-slate-400 text-xs font-bold mt-2">Maximum file size:
                                                    5MB</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 md:p-12 lg:p-20 pt-0 mt-auto bg-white border-t border-slate-50 relative z-20">
                        <button v-if="!showApplyForm" @click="startApplication"
                            class="w-full py-6 bg-indigo-600 text-white font-black rounded-3xl hover:bg-indigo-700 transition-all hover:scale-[1.02] shadow-2xl shadow-indigo-100 text-xl">
                            Apply for this Position
                        </button>
                        <div v-else class="flex gap-6">
                            <button @click="showApplyForm = false"
                                class="px-10 py-6 bg-slate-50 text-slate-900 font-black rounded-3xl hover:bg-slate-100 transition-all">
                                Back
                            </button>
                            <button @click="submitApplication"
                                class="flex-grow py-6 bg-indigo-600 text-white font-black rounded-3xl hover:bg-indigo-700 transition-all hover:scale-[1.02] shadow-2xl shadow-indigo-100 text-xl disabled:opacity-50"
                                :disabled="form.processing">
                                {{ form.processing ? 'Submitting Application...' : 'Submit Application' }}
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
    padding-top: 100px;
}

.page-header {
    padding: 10rem 0 5rem;
    text-align: center;
    background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.05) 0%, transparent 70%);
}

.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 1.2s cubic-bezier(0.19, 1, 0.22, 1);
}

.reveal-active {
    opacity: 1;
    transform: translateY(0);
}

.modal-bounce-enter-active {
    animation: bounce 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal-bounce-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.modal-bounce-enter-from,
.modal-bounce-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(40px);
}

@keyframes bounce {
    0% {
        opacity: 0;
        transform: scale(0.9) translateY(40px);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
</style>
