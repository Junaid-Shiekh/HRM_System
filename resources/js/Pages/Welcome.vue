<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const activeFaq = ref(null);

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

const navLinks = [
    { name: 'Home', href: '#' },
    { name: 'About Us', href: '#features' },
    { name: 'Services', href: '#solutions' },
    { name: 'Pricing', href: '#pricing' },
    { name: 'Blog', href: '#blog' },
    { name: 'Contact', href: '#contact' },
];

const stats = [
    { label: 'Success Rate', value: '286%' },
    { label: 'Global Clients', value: '$15' },
    { label: 'Years Exp', value: '10' },
];

const jobCategories = [
    { name: 'Development', count: '120+', icon: 'pi pi-code' },
    { name: 'Marketing', count: '80+', icon: 'pi pi-megaphone' },
    { name: 'Design', count: '95+', icon: 'pi pi-palette' },
    { name: 'Human Resource', count: '45+', icon: 'pi pi-users' },
    { name: 'Management', count: '60+', icon: 'pi pi-sitemap' },
    { name: 'Customer Service', count: '30+', icon: 'pi pi-phone' },
];

onMounted(() => {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal-active');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
});
</script>

<template>
    <Head title="Aimanova HRM - The #1 Workforce Management Platform" />

    <div class="min-h-screen bg-[#FDFDFF] text-slate-900 font-sans selection:bg-[#7C3AED] selection:text-white overflow-x-hidden">
        
        <!-- Header -->
        <nav class="fixed w-full z-50 bg-white/60 backdrop-blur-xl border-b border-white/20 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex items-center gap-3 cursor-pointer group">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform">
                            <ApplicationLogo class="w-6 h-6 fill-current text-white" />
                        </div>
                        <span class="text-2xl font-black tracking-tight text-slate-900 uppercase">Aimanova</span>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden lg:flex items-center space-x-10">
                        <a v-for="link in navLinks" :key="link.name" :href="link.href" 
                           class="text-[15px] font-semibold text-slate-600 hover:text-indigo-600 transition-colors relative group">
                            {{ link.name }}
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-indigo-600 transition-all group-hover:w-full"></span>
                        </a>
                    </div>

                    <!-- CTA -->
                    <div v-if="canLogin" class="flex items-center gap-6">
                        <template v-if="$page.props.auth.user">
                            <Link :href="route('dashboard')" class="btn-primary">
                                Go to Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="text-[15px] font-bold text-slate-700 hover:text-indigo-600 transition-colors">
                                Sign In
                            </Link>
                            <Link :href="route('register')" class="btn-primary">
                                Get App
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 lg:pt-48 pb-20 overflow-hidden">
            <!-- Background Shape -->
            <div class="absolute top-0 right-0 w-1/2 h-full bg-[#7C3AED] -z-10 rounded-bl-[100px] lg:rounded-bl-[300px] transform translate-x-1/4 -translate-y-1/4 opacity-10 blur-[120px]"></div>
            <div class="absolute top-0 right-0 w-1/2 h-[800px] bg-gradient-to-bl from-[#7C3AED] to-[#4F46E5] -z-10 rounded-bl-[200px] lg:rounded-bl-[400px] hidden lg:block"></div>

            <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
                <div class="reveal">
                    <h1 class="text-6xl lg:text-[85px] font-black text-slate-900 leading-[0.9] tracking-tight mb-8">
                        The <span class="text-[#7C3AED]">#1</span> Job Site to Find <br> Remote Jobs.
                    </h1>
                    <p class="text-xl text-slate-600 leading-relaxed mb-10 max-w-xl">
                        Aimanova empowers organizations with world-class HR solutions. Automate HR workflows, enhance employee experience, and make data-driven decisions that drive growth.
                    </p>
                    <div class="flex flex-wrap gap-6 items-center">
                        <Link :href="route('register')" class="px-10 py-5 bg-[#D946EF] text-white font-bold rounded-full hover:bg-fuchsia-600 transition-all shadow-xl shadow-fuchsia-200 hover:-translate-y-1">
                            Get Started Now
                        </Link>
                        <a href="#" class="text-indigo-600 font-bold hover:underline flex items-center gap-2 group">
                            Learn how it works
                            <i class="pi pi-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                    <!-- Floating Badge -->
                    <div class="mt-16 flex items-center gap-4 bg-white p-4 rounded-3xl shadow-2xl border border-slate-100 max-w-xs">
                        <div class="flex -space-x-4">
                            <img v-for="i in 3" :key="i" :src="`https://i.pravatar.cc/100?img=${i+10}`" class="w-10 h-10 rounded-full border-2 border-white" alt="user">
                        </div>
                        <div class="text-sm">
                            <span class="font-black text-slate-900 block">20k+ Users</span>
                            <span class="text-slate-500">Trusted globally</span>
                        </div>
                    </div>
                </div>

                <div class="relative reveal delay-300">
                    <div class="relative z-10 rounded-[4rem] overflow-hidden shadow-[0_50px_100px_-20px_rgba(28,13,130,0.2)]">
                        <!-- User's Image -->
                        <img src="/images/junaid.png" alt="Junaid" class="w-full h-auto object-cover min-h-[500px]">
                    </div>

                    <!-- Floating UI Elements -->
                    <div class="absolute -top-10 -left-10 bg-white p-6 rounded-3xl shadow-2xl animate-float border border-slate-50 z-20 hidden sm:block">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                <i class="pi pi-briefcase"></i>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 font-bold block uppercase tracking-widest">Jobs for you</span>
                                <span class="text-lg font-black text-slate-900">Project Manager</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-400">
                            <span>Google Inc.</span>
                            <span class="text-green-600 bg-green-50 px-2 py-1 rounded-lg">$120k /yr</span>
                        </div>
                    </div>

                    <div class="absolute -bottom-10 -right-10 bg-white p-6 rounded-3xl shadow-2xl animate-float-delayed border border-slate-50 z-20 hidden sm:block">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center">
                                <i class="pi pi-wallet text-xl"></i>
                            </div>
                            <div>
                                <span class="text-xs text-slate-400 font-black uppercase tracking-widest">New Salary</span>
                                <span class="text-2xl font-black text-slate-900">$12,400.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners Section -->
        <section class="py-16 bg-white border-y border-slate-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-wrap justify-between items-center gap-12 opacity-40 grayscale hover:grayscale-0 transition-all">
                    <div class="text-2xl font-black text-slate-400">GOOGLE</div>
                    <div class="text-2xl font-black text-slate-400">UNITECH</div>
                    <div class="text-2xl font-black text-slate-400">DRIBUL</div>
                    <div class="text-2xl font-black text-slate-400">GORITMI</div>
                    <div class="text-2xl font-black text-slate-400">AIRBNB</div>
                </div>
            </div>
        </section>

        <!-- Feature Section 1 -->
        <section class="py-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-24 items-center">
                    <div class="reveal relative">
                        <div class="relative z-10 rounded-[3rem] overflow-hidden shadow-2xl">
                            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop" alt="Team Working" class="w-full h-[600px] object-cover">
                        </div>
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-600 rounded-full mix-blend-multiply opacity-10 filter blur-3xl"></div>
                        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-fuchsia-600 rounded-full mix-blend-multiply opacity-10 filter blur-3xl"></div>
                    </div>
                    <div class="reveal delay-300">
                        <span class="text-indigo-600 font-bold uppercase tracking-[0.2em] text-sm mb-6 block">Our Platform</span>
                        <h2 class="text-5xl font-black text-slate-900 leading-tight mb-8">
                            Create a profile, find your community, build your career.
                        </h2>
                        <ul class="space-y-6 mb-12">
                            <li class="flex items-start gap-4">
                                <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="pi pi-check text-[10px] font-bold"></i>
                                </div>
                                <p class="text-slate-600 font-medium">Personalized career path recommendations based on your skills.</p>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="pi pi-check text-[10px] font-bold"></i>
                                </div>
                                <p class="text-slate-600 font-medium">Access to exclusive networking groups and industry experts.</p>
                            </li>
                        </ul>
                        <button class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all hover:scale-105">
                            Learn More
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Section 2 -->
        <section class="py-32 bg-slate-50 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-24 items-center">
                    <div class="reveal order-2 lg:order-1">
                        <span class="text-fuchsia-600 font-bold uppercase tracking-[0.2em] text-sm mb-6 block">Career Growth</span>
                        <h2 class="text-5xl font-black text-slate-900 leading-tight mb-8">
                            Ready to level up your CV game?
                        </h2>
                        <p class="text-xl text-slate-600 leading-relaxed mb-10">
                            Our AI-driven CV builder helps you stand out from the crowd. We analyze thousands of successful applications to give you the best chance of landing your dream job.
                        </p>
                        <button class="px-8 py-4 border-2 border-slate-200 text-slate-900 font-bold rounded-2xl hover:bg-white hover:border-indigo-600 transition-all">
                            Explore Templates
                        </button>
                    </div>
                    <div class="reveal delay-300 order-1 lg:order-2">
                        <div class="relative">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=1976&auto=format&fit=crop" alt="CV" class="w-full h-[500px] object-cover rounded-[3rem] shadow-2xl">
                            <div class="absolute -right-8 top-1/2 -translate-y-1/2 bg-white p-6 rounded-3xl shadow-2xl border border-slate-100 z-10 max-w-[240px]">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="space-y-3">
                                    <div class="h-2 bg-slate-100 rounded-full w-full"></div>
                                    <div class="h-2 bg-slate-100 rounded-full w-5/6"></div>
                                    <div class="h-2 bg-indigo-100 rounded-full w-4/6"></div>
                                    <div class="pt-2 flex justify-between">
                                        <div class="w-10 h-10 bg-slate-50 rounded-lg"></div>
                                        <div class="w-24 h-10 bg-fuchsia-600 rounded-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-32 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-3 gap-12 text-center">
                    <div v-for="stat in stats" :key="stat.label" class="reveal">
                        <div class="text-[80px] font-black text-slate-900 mb-2 leading-none">{{ stat.value }}</div>
                        <div class="text-xl font-bold text-slate-400 uppercase tracking-widest">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Job Categories -->
        <section class="py-32 bg-[#F9FAFB]">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-20 reveal">
                    <h2 class="text-5xl font-black text-slate-900 mb-6">Popular Categories</h2>
                    <p class="text-xl text-slate-500">Explore jobs across various industries and roles.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 reveal delay-300">
                    <div v-for="cat in jobCategories" :key="cat.name" 
                         class="bg-white p-8 rounded-[2rem] border border-slate-100 hover:border-indigo-600 hover:shadow-2xl hover:shadow-indigo-100 transition-all cursor-pointer group text-center">
                        <div class="w-14 h-14 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i :class="cat.icon"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 mb-2">{{ cat.name }}</h4>
                        <span class="text-sm text-slate-400 font-bold tracking-widest uppercase">{{ cat.count }} Jobs</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-32 px-6">
            <div class="max-w-6xl mx-auto bg-slate-950 rounded-[4rem] p-16 lg:p-24 relative overflow-hidden shadow-[0_50px_100px_-20px_rgba(0,0,0,0.4)]">
                <!-- Background Glows -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-fuchsia-600/20 rounded-full blur-[120px] translate-y-1/2 -translate-x-1/2"></div>

                <div class="relative z-10 text-center max-w-3xl mx-auto">
                    <h2 class="text-5xl lg:text-7xl font-black text-white mb-10 leading-tight">Ready to get started?</h2>
                    <p class="text-xl text-slate-400 mb-12">
                        Join thousands of companies and professionals using Aimanova to streamline their HR and career growth.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <Link :href="route('register')" class="px-12 py-6 bg-[#D946EF] text-white font-bold rounded-3xl hover:bg-fuchsia-600 transition-all hover:-translate-y-1 text-lg shadow-2xl shadow-fuchsia-500/20">
                            Create Account
                        </Link>
                        <a href="#" class="px-12 py-6 bg-white/10 text-white font-bold rounded-3xl hover:bg-white/20 transition-all text-lg backdrop-blur-md">
                            Contact Sales
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white pt-32 pb-16">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-12 mb-20">
                    <div class="col-span-2">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                                <ApplicationLogo class="w-5 h-5 fill-current text-white" />
                            </div>
                            <span class="text-xl font-black uppercase tracking-tight">Aimanova</span>
                        </div>
                        <p class="text-slate-500 leading-relaxed max-w-xs mb-8">
                            Dedicated to connecting talent with opportunity and simplifying HR management for businesses worldwide.
                        </p>
                        <div class="flex gap-4">
                            <a v-for="i in ['twitter', 'linkedin', 'instagram', 'facebook']" :key="i" href="#" class="w-10 h-10 rounded-full border border-slate-100 flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-all">
                                <i :class="`pi pi-${i}`"></i>
                            </a>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-black mb-8 text-slate-900 uppercase tracking-widest text-xs">Product</h5>
                        <ul class="space-y-4 text-slate-500 font-medium">
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Features</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Integrations</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Pricing</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-black mb-8 text-slate-900 uppercase tracking-widest text-xs">Solutions</h5>
                        <ul class="space-y-4 text-slate-500 font-medium">
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">For Enterprise</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Remote Teams</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Startups</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-black mb-8 text-slate-900 uppercase tracking-widest text-xs">Company</h5>
                        <ul class="space-y-4 text-slate-500 font-medium">
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Blog</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-black mb-8 text-slate-900 uppercase tracking-widest text-xs">Support</h5>
                        <ul class="space-y-4 text-slate-500 font-medium">
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Help Center</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Contact Us</a></li>
                            <li><a href="#" class="hover:text-indigo-600 transition-colors">Privacy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                    <p class="text-slate-400 text-sm font-medium">© 2026 Aimanova Inc. All rights reserved.</p>
                    <div class="flex items-center gap-6 text-slate-300">
                        <span class="text-xs font-semibold">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</span>
                        <span class="text-xs uppercase font-black tracking-widest">Goritmi Framework</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

.btn-primary {
    @apply px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 hover:scale-105 active:scale-95 text-sm;
}

/* Scroll Reveal */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 1s cubic-bezier(0.19, 1, 0.22, 1);
}

.reveal-active {
    opacity: 1;
    transform: translateY(0);
}

.delay-100 { transition-delay: 0.1s; }
.delay-200 { transition-delay: 0.2s; }
.delay-300 { transition-delay: 0.3s; }
.delay-400 { transition-delay: 0.4s; }
.delay-500 { transition-delay: 0.5s; }

/* Floating Animation */
@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(1deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes float-delayed {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(20px) rotate(-1deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 8s ease-in-out infinite;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 10px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #7C3AED;
    border-radius: 5px;
}
::-webkit-scrollbar-thumb:hover {
    background: #4F46E5;
}
</style>
