<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

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

        <Head title="About Our Mission - AIMANOVA" />

        <div class="about-page">
            <header class="about-hero">
                <div class="max-w-4xl mx-auto px-6 reveal">
                    <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-6 block">Our
                        Vision</span>
                    <h1 class="text-4xl md:text-6xl lg:text-8xl font-black text-slate-900 leading-tight mb-8">
                        Redefining the Future of <span class="text-indigo-600">Workforce</span>
                    </h1>
                    <p class="text-xl text-slate-500 leading-relaxed font-medium">
                        At AIMANOVA, we're building the infrastructure for the next generation of global talent
                        management.
                    </p>
                </div>
            </header>

            <section class="py-16 md:py-32">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="grid lg:grid-cols-2 gap-24 items-center">
                        <div class="reveal">
                            <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-8">Who We Are</h2>
                            <p class="text-lg text-slate-500 leading-relaxed mb-6 font-medium">
                                AIMANOVA is a pioneering HR technology company dedicated to helping organizations of all
                                sizes manage their most valuable asset: their people. Founded with a vision to
                                streamline complex workflows, we've been at the forefront of the digital HR revolution.
                            </p>
                            <p class="text-lg text-slate-500 leading-relaxed mb-10 font-medium">
                                Our platform is built on the principles of transparency, efficiency, and human-centric
                                design. We believe that technology should serve people, empowering every member of your
                                organization to reach their full potential.
                            </p>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100">
                                    <div class="text-4xl font-black text-indigo-600 mb-2">10k+</div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active
                                        Users</div>
                                </div>
                                <div class="p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100">
                                    <div class="text-4xl font-black text-fuchsia-600 mb-2">500+</div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        Enterprise Clients</div>
                                </div>
                            </div>
                        </div>
                        <div class="relative reveal delay-300">
                            <div
                                class="rounded-[2.5rem] md:rounded-[4rem] overflow-hidden shadow-2xl border-4 border-white">
                                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=2070"
                                    alt="Team Work" class="w-full h-[400px] md:h-[600px] object-cover">
                            </div>
                            <div
                                class="absolute -bottom-10 -right-10 w-64 h-64 bg-indigo-600/10 rounded-full blur-[80px] -z-10">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-16 md:py-32 bg-slate-50/50">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="text-center mb-24 reveal">
                        <span class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-6 block">Our
                            Core Pillars</span>
                        <h2 class="text-3xl md:text-5xl font-black text-slate-900">What Drives Us</h2>
                    </div>

                    <div class="grid md:grid-cols-3 gap-12">
                        <div
                            class="reveal p-12 bg-white rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                            <div
                                class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center text-4xl mb-10 group-hover:bg-indigo-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                                💡
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 mb-6">Innovation</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">We're constantly pushing the
                                boundaries of what's possible in HR technology, integrating AI and smart automation.</p>
                        </div>
                        <div
                            class="reveal delay-200 p-12 bg-white rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                            <div
                                class="w-20 h-20 bg-fuchsia-50 text-fuchsia-600 rounded-3xl flex items-center justify-center text-4xl mb-10 group-hover:bg-fuchsia-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                                🤝
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 mb-6">Integrity</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">We build trust through radical
                                transparency and ethical business practices, ensuring your data is always protected.</p>
                        </div>
                        <div
                            class="reveal delay-400 p-12 bg-white rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                            <div
                                class="w-20 h-20 bg-green-50 text-green-600 rounded-3xl flex items-center justify-center text-4xl mb-10 group-hover:bg-green-600 group-hover:text-white transition-all transform group-hover:rotate-6">
                                🎨
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 mb-6">Design</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">We believe beautiful tools are better
                                tools. Aesthetics and function go hand in hand to create a premium experience.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </WebsiteLayout>
</template>

<style scoped>
.about-page {
    padding-top: 100px;
}

.about-hero {
    padding: 10rem 0 5rem;
    text-align: center;
    background: radial-gradient(circle at 20% 30%, rgba(79, 70, 229, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(217, 70, 239, 0.05) 0%, transparent 50%);
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

.delay-200 {
    transition-delay: 0.2s;
}

.delay-300 {
    transition-delay: 0.3s;
}

.delay-400 {
    transition-delay: 0.4s;
}
</style>
