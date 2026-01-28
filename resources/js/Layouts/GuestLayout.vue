<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="min-h-screen w-full flex items-center justify-center bg-[#0F1025] relative overflow-hidden font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Background Ambient Glow -->
        <div
            class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none">
        </div>
        <div
            class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[120px] pointer-events-none">
        </div>

        <!-- Grid Pattern (Optional subtle texture) -->
        <div
            class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-20">
        </div>

        <!-- Main Card Container -->
        <div
            class="w-full max-w-[1100px] h-auto lg:h-[700px] bg-[#1A1B3A]/60 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl flex flex-col lg:flex-row overflow-hidden relative z-10 mx-4">

            <!-- Left Side: Branding -->
            <div
                class="w-full lg:w-1/2 p-12 flex flex-col justify-between relative bg-gradient-to-br from-[#1E2045] to-[#13142D]">
                <!-- Logo -->
                <div>
                    <Link href="/" class="flex items-center gap-3 group">
                        <ApplicationLogo
                            class="h-10 w-10 fill-current text-indigo-500 group-hover:scale-110 transition-transform duration-300" />
                        <span class="text-2xl font-bold tracking-wider text-white">AIMANOVA</span>
                    </Link>
                </div>

                <!-- Hero Text -->
                <div class="mt-12 lg:mt-0">
                    <h1 class="text-4xl lg:text-5xl font-bold text-white leading-[1.15] tracking-tight">
                        Empowering <br>
                        your <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-500">greatest
                            asset.</span>
                    </h1>
                    <p class="mt-6 text-lg text-indigo-200/80 font-light leading-relaxed max-w-sm">
                        Experience the next generation of human resource management. Seamless, intuitive, and driven by
                        intelligence.
                    </p>
                </div>

                <!-- Social Proof -->
                <div class="mt-12 lg:mt-0">
                    <div class="flex items-center gap-4">
                        <div class="flex -space-x-3">
                            <img class="w-10 h-10 rounded-full border-2 border-[#1E2045]"
                                src="https://i.pravatar.cc/100?img=1" alt="User 1">
                            <img class="w-10 h-10 rounded-full border-2 border-[#1E2045]"
                                src="https://i.pravatar.cc/100?img=5" alt="User 2">
                            <img class="w-10 h-10 rounded-full border-2 border-[#1E2045]"
                                src="https://i.pravatar.cc/100?img=8" alt="User 3">
                            <div
                                class="w-10 h-10 rounded-full border-2 border-[#1E2045] bg-indigo-600 flex items-center justify-center text-xs font-bold text-white">
                                +12k
                            </div>
                        </div>
                        <span class="text-sm text-indigo-200/60 font-medium">Joined by 12,000+ companies
                            worldwide</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Content/Form -->
            <div class="w-full lg:w-1/2 p-12 bg-[#1A1B3A]/40 flex flex-col justify-center relative">
                <slot />

                <!-- Footer Links -->
                <div
                    class="mt-auto pt-8 flex items-center justify-between text-[10px] text-zinc-500 uppercase tracking-wider font-semibold">
                    <span>&copy; {{ new Date().getFullYear() }} AIMANOVA. All Rights Reserved.</span>
                    <div class="flex gap-4">
                        <a href="#" class="hover:text-zinc-300 transition-colors">Privacy</a>
                        <a href="#" class="hover:text-zinc-300 transition-colors">Terms</a>
                        <a href="#" class="hover:text-zinc-300 transition-colors">Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes float {

    0%,
    100% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-15px);
    }
}

.animate-float-slow {
    animation: float 6s ease-in-out infinite;
}

.animate-float-medium {
    animation: float 5s ease-in-out infinite;
}

.animate-float-fast {
    animation: float 4s ease-in-out infinite;
}
</style>
