<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const isScrolled = ref(false);
const showJobsDropdown = ref(false);
const isMobileMenuOpen = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.body.style.overflow = 'auto';
});
</script>

<template>
    <div class="website-container">
        <!-- Navbar -->
        <nav :class="['navbar', { 'navbar-scrolled': isScrolled }]">
            <div class="nav-content">
                <Link href="/" class="logo-wrapper">
                    <div class="logo-box">
                        <ApplicationLogo class="w-6 h-6 fill-current text-white" />
                    </div>
                    <span class="logo-text">AIMANOVA</span>
                </Link>

                <!-- Desktop Nav -->
                <div class="nav-links hidden md:flex">
                    <Link :href="route('website.home')" :class="{ 'active': route().current('website.home') }">Home
                    </Link>
                    <a href="/#solutions">Solutions</a>
                    <a href="/#services">Services</a>
                    <Link :href="route('website.about')" :class="{ 'active': route().current('website.about') }">About
                    </Link>

                    <div class="dropdown" @mouseenter="showJobsDropdown = true" @mouseleave="showJobsDropdown = false">
                        <button class="dropdown-trigger" :class="{ 'active': route().current('website.jobs') }">
                            Jobs
                            <i class="pi pi-chevron-down text-[10px] ml-1 transition-transform"
                                :class="{ 'rotate-180': showJobsDropdown }"></i>
                        </button>
                        <transition name="slide-fade">
                            <div v-if="showJobsDropdown" class="dropdown-menu">
                                <Link :href="route('website.jobs')" class="dropdown-item">Recent Post Jobs</Link>
                                <a href="#" class="dropdown-item">Career Guidance</a>
                                <a href="#" class="dropdown-item">HR Consulting</a>
                            </div>
                        </transition>
                    </div>
                </div>

                <div class="nav-actions">
                    <a href="/#faq"
                        class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors mr-8 hidden md:block">FAQ</a>
                    <Link :href="route('login')" class="btn-login shadow-indigo-200 hidden md:block">Login</Link>

                    <!-- Mobile Menu Button -->
                    <button @click="toggleMobileMenu"
                        class="md:hidden w-10 h-10 flex flex-col items-center justify-center gap-1.5 focus:outline-none z-[1100] relative">
                        <span
                            :class="['w-6 h-0.5 bg-slate-900 transition-all duration-300 rounded-full', { 'rotate-45 translate-y-2': isMobileMenuOpen, 'bg-indigo-600': isMobileMenuOpen }]"></span>
                        <span
                            :class="['w-6 h-0.5 bg-slate-900 transition-all duration-300 rounded-full', { 'opacity-0': isMobileMenuOpen }]"></span>
                        <span
                            :class="['w-6 h-0.5 bg-slate-900 transition-all duration-300 rounded-full', { '-rotate-45 -translate-y-2': isMobileMenuOpen, 'bg-indigo-600': isMobileMenuOpen }]"></span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Drawer -->
        <transition name="drawer">
            <div v-if="isMobileMenuOpen" class="fixed inset-0 z-[1050] md:hidden">
                <!-- Overlay -->
                <transition name="fade">
                    <div v-if="isMobileMenuOpen" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                        @click="toggleMobileMenu"></div>
                </transition>

                <!-- Drawer Content -->
                <div
                    class="absolute top-0 right-0 w-[300px] h-full bg-white shadow-2xl p-6 pt-12 flex flex-col gap-2 transform transition-transform duration-500 ease-out">
                    <div class="mb-4">
                        <Link href="/" @click="toggleMobileMenu" class="logo-wrapper !gap-3">
                            <div class="logo-box !w-10 !h-10 !rounded-xl">
                                <ApplicationLogo class="w-6 h-6 fill-current text-white" />
                            </div>
                            <span class="logo-text !text-xl">AIMANOVA</span>
                        </Link>
                    </div>

                    <nav class="flex flex-col gap-2">
                        <Link :href="route('website.home')" @click="toggleMobileMenu" class="mobile-nav-link"
                            :class="{ 'active': route().current('website.home') }">
                            <i class="pi pi-home mr-3 text-indigo-600"></i> Home
                        </Link>
                        <a href="/#solutions" @click="toggleMobileMenu" class="mobile-nav-link">
                            <i class="pi pi-th-large mr-3 text-indigo-600"></i> Solutions
                        </a>
                        <a href="/#services" @click="toggleMobileMenu" class="mobile-nav-link">
                            <i class="pi pi-briefcase mr-3 text-indigo-600"></i> Services
                        </a>
                        <Link :href="route('website.about')" @click="toggleMobileMenu" class="mobile-nav-link"
                            :class="{ 'active': route().current('website.about') }">
                            <i class="pi pi-info-circle mr-3 text-indigo-600"></i> About Us
                        </Link>
                        <Link :href="route('website.jobs')" @click="toggleMobileMenu" class="mobile-nav-link"
                            :class="{ 'active': route().current('website.jobs') }">
                            <i class="pi pi-send mr-3 text-indigo-600"></i> Jobs
                        </Link>
                        <a href="/#faq" @click="toggleMobileMenu" class="mobile-nav-link">
                            <i class="pi pi-question-circle mr-3 text-indigo-600"></i> FAQ
                        </a>
                    </nav>

                    <div class="mt-auto pt-6 border-t border-slate-50 flex flex-col gap-3">
                        <Link :href="route('login')" @click="toggleMobileMenu"
                            class="w-full py-4 bg-indigo-600 text-white font-black rounded-2xl text-center shadow-xl shadow-indigo-100">
                            Log In
                        </Link>
                        <Link :href="route('register')" @click="toggleMobileMenu"
                            class="w-full py-4 bg-slate-100 text-slate-900 font-black rounded-2xl text-center border border-slate-200">
                            Create Account
                        </Link>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-brand">
                    <Link href="/" class="logo-wrapper mb-8">
                        <div class="logo-box !w-8 !h-8">
                            <ApplicationLogo class="w-5 h-5 fill-current text-white" />
                        </div>
                        <span class="logo-text !text-xl">AIMANOVA</span>
                    </Link>
                    <p class="text-slate-500 leading-relaxed max-w-xs mb-8">
                        Elevating businesses with cutting-edge HR solutions. Empowering your workforce through
                        innovation and smart automation.
                    </p>
                    <div class="social-links">
                        <a v-for="i in ['twitter', 'linkedin', 'instagram', 'facebook']" :key="i" href="#"
                            class="social-btn">
                            <i :class="`pi pi-${i}`"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-grid">
                    <div class="footer-column">
                        <h4 class="footer-title">Platform</h4>
                        <Link href="/">Home</Link>
                        <a href="/#solutions">Solutions</a>
                        <a href="/#services">Services</a>
                        <Link :href="route('website.jobs')">Jobs</Link>
                    </div>
                    <div class="footer-column">
                        <h4 class="footer-title">Company</h4>
                        <Link :href="route('website.about')">About Us</Link>
                        <a href="/#faq">FAQ</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                    <div class="footer-column col-span-2 md:col-span-1">
                        <h4 class="footer-title">Contact Us</h4>
                        <div class="contact-info">
                            <p><i class="pi pi-envelope text-indigo-600"></i> contact@aimanova.com</p>
                            <p><i class="pi pi-phone text-indigo-600"></i> +1 (234) 567-890</p>
                            <p><i class="pi pi-map-marker text-indigo-600"></i> Silicon Valley, CA</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-slate-400 text-sm font-medium">© 2026 AIMANOVA Inc. All rights reserved.</p>
                    <div class="flex items-center gap-6">
                        <span class="text-[10px] uppercase font-black tracking-widest text-slate-300">Goritmi
                            Framework</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');

:root {
    --primary: #4F46E5;
    --primary-light: #818CF8;
    --secondary: #D946EF;
    --slate-900: #0F172A;
    --slate-600: #475569;
    --slate-400: #94A3B8;
}

body {
    margin: 0;
    padding: 0;
}
</style>

<style scoped>
.website-container {
    font-family: 'Outfit', sans-serif;
    background-color: #FDFDFF;
    color: var(--slate-900);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

/* Navbar */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    padding: 1.5rem 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.navbar-scrolled {
    padding: 1rem 0;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
}

.nav-content {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.logo-wrapper:hover {
    transform: scale(1.02);
}

.logo-box {
    width: 2.5rem;
    height: 2.5rem;
    background: var(--primary);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 16px -4px rgba(79, 70, 229, 0.4);
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--slate-900);
    letter-spacing: -0.025em;
    text-transform: uppercase;
}

.nav-links {
    display: flex;
    gap: 2.5rem;
    align-items: center;
}

.nav-links a,
.dropdown-trigger {
    color: var(--slate-600);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9375rem;
    transition: all 0.3s ease;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    position: relative;
    padding: 0.5rem 0;
}

.nav-links a:hover,
.nav-links a.active,
.dropdown-trigger:hover,
.dropdown-trigger.active {
    color: var(--primary);
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary);
    transition: width 0.3s ease;
}

.nav-links a:hover::after,
.nav-links a.active::after {
    width: 100%;
}

.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-top: 1rem;
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 1.5rem;
    padding: 0.75rem;
    min-width: 200px;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    display: block;
    padding: 0.875rem 1.25rem;
    border-radius: 1rem;
    text-decoration: none;
    color: var(--slate-600) !important;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background: #F8FAFC;
    color: var(--primary) !important;
    transform: translateX(5px);
}

.nav-actions {
    display: flex;
    align-items: center;
}

.btn-login {
    background: var(--primary);
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 1rem;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.875rem;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px -10px rgba(79, 70, 229, 0.4);
    background: var(--primary-light);
}

/* Transitions */
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(10px) translateX(-50%);
    opacity: 0;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-radius: 1.5rem;
    color: var(--slate-600);
    text-decoration: none;
    font-weight: 700;
    font-size: 1.125rem;
    transition: all 0.3s ease;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    background: #F8FAFC;
    color: var(--primary);
    transform: translateX(5px);
}

/* Animations */
.drawer-enter-active,
.drawer-leave-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

.drawer-enter-from div[class*="bg-white"],
.drawer-leave-to div[class*="bg-white"] {
    transform: translateX(100%);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Footer */
.footer {
    background: white;
    padding-top: 8rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.footer-content {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 1.5fr 2.5fr;
    gap: 6rem;
    margin-bottom: 5rem;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4rem;
}

.footer-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.footer-title {
    font-size: 0.75rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--slate-900);
    margin-bottom: 0.5rem;
}

.footer-column a {
    color: var(--slate-600);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.footer-column a:hover {
    color: var(--primary);
    transform: translateX(5px);
}

.contact-info p {
    color: var(--slate-600);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9375rem;
}

.social-links {
    display: flex;
    gap: 0.75rem;
}

.social-btn {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: #F8FAFC;
    border: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--slate-400);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.social-btn:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
    box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.2);
}

.footer-bottom {
    padding: 2.5rem 0;
    background: #F8FAFC;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

@media (max-width: 1024px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 4rem;
    }
}

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }

    .footer-grid {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
}
</style>
