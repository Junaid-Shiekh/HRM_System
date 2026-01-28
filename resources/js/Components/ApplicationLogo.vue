<template>
    <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#1e3a8a;stop-opacity:1" /> <!-- Dark Blue -->
                <stop offset="100%" style="stop-color:#4f46e5;stop-opacity:1" /> <!-- Indigo -->
            </linearGradient>
            <linearGradient id="logoGradientLight" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#e0e7ff;stop-opacity:0.9" />
            </linearGradient>
        </defs>
        <!-- Left Blade -->
        <path d="M 250 50 Q 150 350 50 450 L 150 450 Q 220 350 250 150 Z" class="fill-current" />
        <!-- Right Blade -->
        <path d="M 250 50 Q 350 350 450 450 L 350 450 Q 280 350 250 150 Z" class="fill-current" />
        <!-- Center/Bottom Crescent -->
        <path d="M 120 350 Q 250 420 380 350 L 380 400 Q 250 500 120 400 Z" class="fill-current" />

        <!-- Overlay for depth (Optional, can be removed if flattened style is preferred) -->
        <path d="M 250 50 L 250 150 Q 280 350 350 450 L 450 450 Q 350 350 250 50 Z" fill-opacity="0.2" fill="black" />
    </svg>
</template>
