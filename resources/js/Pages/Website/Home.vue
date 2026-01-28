<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

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
    <WebsiteLayout>

        <Head title="Aimanova HRM - The #1 Workforce Management Platform" />

        <!-- Hero Section -->
        <section class="relative pt-32 lg:pt-48 pb-20 overflow-hidden">
            <!-- Background Decoration -->
            <div
                class="absolute top-0 right-0 w-1/2 h-full bg-indigo-600/5 -z-10 rounded-bl-[200px] lg:rounded-bl-[400px] blur-[120px] hidden md:block">
            </div>
            <div
                class="absolute top-0 right-0 w-1/2 h-[800px] bg-gradient-to-bl from-indigo-600 to-indigo-800 -z-10 rounded-bl-[200px] lg:rounded-bl-[400px] hidden lg:block opacity-[0.03]">
            </div>
            <div
                class="absolute top-0 right-0 w-1/2 h-[700px] bg-indigo-600 -z-20 rounded-bl-[200px] lg:rounded-bl-[400px] hidden lg:block opacity-10">
            </div>

            <div
                class="max-w-7xl mx-auto px-6 flex flex-col lg:grid lg:grid-cols-2 gap-12 lg:gap-16 items-center text-center lg:text-left">
                <div class="reveal flex flex-col items-center lg:items-start">
                    <div
                        class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest mb-6 md:mb-8 border border-indigo-100">
                        <span class="flex h-2 w-2 mr-2 relative">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                        </span>
                        Next-Generation HRM
                    </div>
                    <h1
                        class="text-3xl md:text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] md:leading-[0.9] tracking-tight mb-6 md:mb-8">
                        The <span class="text-indigo-600">#1</span> Job Site to Find <br class="hidden sm:block"> Remote
                        Jobs.
                    </h1>
                    <p class="text-base md:text-xl text-slate-500 leading-relaxed mb-10 max-w-xl">
                        Aimanova empowers organizations with world-class HR solutions. Automate workflows, enhance
                        experience, and grow your career.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 md:gap-6 items-center lg:items-center">
                        <Link :href="route('register')"
                            class="w-full sm:w-auto px-10 py-5 bg-[#D946EF] text-white font-bold rounded-2xl hover:bg-fuchsia-600 transition-all shadow-xl shadow-fuchsia-200 hover:-translate-y-1 text-center">
                            Get Started Now
                        </Link>
                        <a href="#how-it-works"
                            class="text-indigo-600 font-bold hover:underline flex items-center gap-2 group">
                            Learn how it works
                            <i class="pi pi-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>

                    <!-- Trust Badge -->
                    <div
                        class="mt-12 md:mt-16 flex items-center gap-4 bg-white p-4 rounded-3xl shadow-2xl shadow-slate-100 border border-slate-50 max-w-xs mx-auto lg:mx-0">
                        <div class="flex -space-x-4">
                            <img v-for="i in 3" :key="i" :src="`https://i.pravatar.cc/100?img=${i + 14}`"
                                class="w-10 h-10 rounded-full border-2 border-white" alt="user">
                        </div>
                        <div class="text-sm">
                            <span class="font-black text-slate-900 block">20k+ Users</span>
                            <span class="text-slate-400 font-medium">Trusted global talent</span>
                        </div>
                    </div>
                </div>

                <div class="relative reveal delay-300 mt-12 lg:mt-0">
                    <div
                        class="relative z-10 rounded-[2.5rem] md:rounded-[4rem] overflow-hidden shadow-[0_50px_100px_-20px_rgba(79,70,229,0.2)] border-4 md:border-8 border-white">
                        <img src="/images/junaid.png" alt="Hero Image"
                            class="w-full h-auto object-cover min-h-[350px] md:min-h-[500px]">
                    </div>

                    <!-- Floating Elements -->
                    <div
                        class="absolute -top-10 -left-10 bg-white p-6 rounded-3xl shadow-2xl animate-float border border-slate-50 z-20 hidden sm:block">
                        <div class="flex items-center gap-4 mb-3">
                            <div
                                class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                <i class="pi pi-briefcase"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-black block uppercase tracking-widest">New
                                    Opportunity</span>
                                <span class="text-lg font-black text-slate-900">Project Manager</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-400">
                            <span>Google Inc.</span>
                            <span class="text-green-600 bg-green-50 px-2 py-1 rounded-lg">$120k /yr</span>
                        </div>
                    </div>

                    <div
                        class="absolute -bottom-10 -right-10 bg-white p-6 rounded-3xl shadow-2xl animate-float-delayed border border-slate-50 z-20 hidden sm:block">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center">
                                <i class="pi pi-wallet text-xl"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Est.
                                    Salary</span>
                                <span class="text-2xl font-black text-slate-900">$12,400.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div
                    class="flex flex-row flex-nowrap justify-between items-center gap-2 md:gap-12 lg:gap-24 opacity-50 grayscale hover:grayscale-0 transition-all duration-500 px-1">
                    <div class="text-[8px] sm:text-lg md:text-2xl font-black text-slate-900 tracking-tighter">GOOGLE
                    </div>
                    <div class="text-[8px] sm:text-lg md:text-2xl font-black text-slate-900 tracking-tighter">UNITECH
                    </div>
                    <div class="text-[8px] sm:text-lg md:text-2xl font-black text-slate-900 tracking-tighter">DRIBUL
                    </div>
                    <div class="text-[8px] sm:text-lg md:text-2xl font-black text-slate-900 tracking-tighter">GORITMI
                    </div>
                    <div class="text-[8px] sm:text-lg md:text-2xl font-black text-slate-900 tracking-tighter">AIRBNB
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature 1 -->
        <section id="solutions" class="py-16 md:py-32 overflow-hidden bg-slate-50/50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-16 lg:gap-24 items-center text-center lg:text-left">
                    <div class="reveal relative">
                        <div class="rounded-[2rem] md:rounded-[4rem] overflow-hidden shadow-2xl border-4 border-white">
                            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070"
                                alt="Team Meeting" class="w-full h-[300px] md:h-[600px] object-cover">
                        </div>
                        <div class="absolute -top-10 -right-10 w-64 h-64 bg-indigo-600/10 rounded-full blur-[80px]">
                        </div>
                        <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-fuchsia-600/10 rounded-full blur-[80px]">
                        </div>
                    </div>
                    <div class="reveal flex flex-col items-center lg:items-start mt-12 lg:mt-0">
                        <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-6 block">Our
                            Strategy</span>
                        <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-8 leading-tight">Empowering Your
                            Global Team
                        </h2>
                        <div class="space-y-8 mb-12">
                            <div
                                class="flex gap-4 p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                                <div
                                    class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                    <i class="pi pi-check font-bold"></i>
                                </div>
                                <p class="text-slate-500 font-medium leading-relaxed">Personalized career path
                                    recommendations based on your unique skills and interests.</p>
                            </div>
                            <div
                                class="flex gap-4 p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                                <div
                                    class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                    <i class="pi pi-users font-bold"></i>
                                </div>
                                <p class="text-slate-500 font-medium leading-relaxed">Join exclusive industry groups and
                                    connect with top-tier professionals worldwide.</p>
                            </div>
                        </div>
                        <button
                            class="px-10 py-5 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all hover:scale-105 shadow-xl shadow-indigo-100">
                            Explore Community
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature 2 -->
        <section id="services" class="py-16 md:py-32 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid lg:grid-cols-2 gap-16 lg:gap-24 items-center text-center lg:text-left">
                    <div class="reveal order-2 lg:order-1 flex flex-col items-center lg:items-start">
                        <span
                            class="text-fuchsia-600 font-black uppercase tracking-[0.3em] text-[10px] mb-6 block">Career
                            Growth</span>
                        <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-8 leading-tight">Build Your
                            Personal Brand
                        </h2>
                        <p class="text-xl text-slate-500 leading-relaxed mb-10">
                            Our AI-driven CV builder analyzes thousands of successful applications to help you craft a
                            resume that gets noticed by top recruiters.
                        </p>
                        <div class="grid grid-cols-2 gap-6 mb-12">
                            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <div class="text-3xl font-black text-slate-900 mb-2">90%</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">More Interviews
                                </div>
                            </div>
                            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <div class="text-3xl font-black text-slate-900 mb-2">15+</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Smart Templates
                                </div>
                            </div>
                        </div>
                        <button
                            class="px-10 py-5 border-2 border-slate-200 text-slate-900 font-bold rounded-2xl hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all">
                            Build Your CV
                        </button>
                    </div>
                    <div class="reveal delay-300 order-1 lg:order-2 mb-12 lg:mb-0">
                        <div class="relative">
                            <div
                                class="rounded-[2rem] md:rounded-[4rem] overflow-hidden shadow-2xl border-4 border-slate-50">
                                <img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?q=80&w=2070"
                                    alt="CV Builder" class="w-full h-[300px] md:h-[600px] object-cover">
                            </div>
                            <!-- CV Preview Card -->
                            <div
                                class="absolute -right-12 top-1/2 -translate-y-1/2 bg-white p-8 rounded-[2.5rem] shadow-2xl border border-slate-100 z-10 max-w-[280px] hidden md:block">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="space-y-4">
                                    <div class="h-3 bg-slate-100 rounded-full w-full"></div>
                                    <div class="h-3 bg-slate-100 rounded-full w-5/6"></div>
                                    <div class="h-3 bg-indigo-100 rounded-full w-4/6"></div>
                                    <div class="h-3 bg-slate-100 rounded-full w-full opacity-50"></div>
                                    <div class="pt-4 flex justify-between items-center">
                                        <div class="w-12 h-12 bg-slate-50 rounded-xl"></div>
                                        <div class="w-24 h-10 bg-indigo-600 rounded-xl"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="py-16 md:py-32 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-3 gap-16 text-center">
                    <div v-for="stat in stats" :key="stat.label" class="reveal">
                        <div
                            class="text-6xl md:text-[90px] font-black text-slate-900 mb-2 leading-none tracking-tighter">
                            {{
                                stat.value }}</div>
                        <div class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="py-16 md:py-32 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-24 reveal">
                    <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-6 block">Browse
                        Roles</span>
                    <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-8">Popular Job Categories</h2>
                    <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto font-medium">Explore thousands of job
                        opportunities across specialized industries and leading companies.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 reveal delay-300">
                    <div v-for="cat in jobCategories" :key="cat.name"
                        class="bg-white p-10 rounded-[2.5rem] border border-slate-100 hover:border-indigo-600 hover:shadow-2xl hover:shadow-indigo-50 transition-all cursor-pointer group text-center">
                        <div
                            class="w-16 h-16 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-8 group-hover:bg-indigo-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                            <i :class="cat.icon"></i>
                        </div>
                        <h4 class="font-black text-slate-900 mb-2 text-lg">{{ cat.name }}</h4>
                        <span class="text-[10px] text-slate-400 font-black tracking-widest uppercase">{{ cat.count }}
                            Positions</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-16 md:py-32 bg-slate-950 text-white overflow-hidden relative">
            <div class="absolute inset-0 bg-indigo-600/10 blur-[120px] rounded-full -translate-x-1/2 -translate-y-1/2">
            </div>
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="text-center mb-24 reveal">
                    <h2 class="text-5xl font-black mb-6">Loved by Global Talent.</h2>
                    <p class="text-indigo-300 text-lg font-medium opacity-80">Join 10,000+ professionals who leveled up
                        their career with Aimanova.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-10">
                    <div v-for="i in 3" :key="i"
                        class="p-10 bg-white/5 border border-white/10 rounded-[3rem] backdrop-blur-xl reveal"
                        :class="'delay-' + (i * 100)">
                        <div class="flex text-fuchsia-500 mb-8 gap-1">
                            <i v-for="star in 5" :key="star" class="pi pi-star-fill text-xs"></i>
                        </div>
                        <p class="text-indigo-50 text-xl font-medium leading-relaxed mb-10 italic">"The most intuitive
                            job search platform I've ever used. The match system is eerily accurate!"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full border-2 border-indigo-500/30 overflow-hidden">
                                <img :src="`https://i.pravatar.cc/100?img=${i + 20}`" alt="user">
                            </div>
                            <div>
                                <h5 class="font-black text-white">Alex Morgan</h5>
                                <p class="text-indigo-400 text-xs font-bold uppercase tracking-widest">SR. Designer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-16 md:py-32 px-6">
            <div
                class="max-w-6xl mx-auto bg-indigo-600 rounded-[4rem] p-16 lg:p-28 relative overflow-hidden shadow-2xl shadow-indigo-200">
                <div
                    class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-[100px] translate-x-1/2 -translate-y-1/2">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-96 h-96 bg-fuchsia-600/20 rounded-full blur-[100px] -translate-x-1/2 translate-y-1/2">
                </div>

                <div class="relative z-10 text-center max-w-3xl mx-auto">
                    <h2 class="text-5xl lg:text-7xl font-black text-white mb-10 leading-tight">Ready to get started?
                    </h2>
                    <p class="text-xl text-indigo-100 mb-12 font-medium opacity-90">
                        Join thousands of companies and professionals using Aimanova to streamline their career growth.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <Link :href="route('register')"
                            class="px-12 py-6 bg-white text-indigo-600 font-black rounded-3xl hover:bg-slate-50 transition-all hover:-translate-y-1 text-lg shadow-xl">
                            Create Free Account
                        </Link>
                        <a href="#"
                            class="px-12 py-6 bg-indigo-700 text-white font-black rounded-3xl hover:bg-indigo-800 transition-all text-lg border border-indigo-400/30">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </WebsiteLayout>
</template>

<style scoped>
/* Scroll Reveal Core */
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 1.2s cubic-bezier(0.19, 1, 0.22, 1);
}

.reveal-active {
    opacity: 1;
    transform: translateY(0);
}

.delay-100 {
    transition-delay: 0.1s;
}

.delay-200 {
    transition-delay: 0.2s;
}

.delay-300 {
    transition-delay: 0.3s;
}

.delay-400 {
    transition-delay: 0.4s;
}

.delay-500 {
    transition-delay: 0.5s;
}

/* Floating Animation */
@keyframes float {
    0% {
        transform: translateY(0px) rotate(0.5deg);
    }

    50% {
        transform: translateY(-20px) rotate(-0.5deg);
    }

    100% {
        transform: translateY(0px) rotate(0.5deg);
    }
}

@keyframes float-delayed {
    0% {
        transform: translateY(0px) rotate(-0.5deg);
    }

    50% {
        transform: translateY(20px) rotate(0.5deg);
    }

    100% {
        transform: translateY(0px) rotate(-0.5deg);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 8s ease-in-out infinite;
}

/* Custom Selection */
::selection {
    background: #4F46E5;
    color: white;
}
</style>
