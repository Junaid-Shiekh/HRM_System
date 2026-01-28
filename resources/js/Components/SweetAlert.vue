<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Success'
    },
    message: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'success', // success, error, warning, info
        validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
    },
    confirmText: {
        type: String,
        default: 'OK'
    },
    showCancel: {
        type: Boolean,
        default: false
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    }
});

const emit = defineEmits(['update:visible', 'confirm', 'cancel']);

const isVisible = ref(props.visible);

watch(() => props.visible, (newVal) => {
    isVisible.value = newVal;
});

const handleConfirm = () => {
    emit('confirm');
    closeDialog();
};

const handleCancel = () => {
    emit('cancel');
    closeDialog();
};

const closeDialog = () => {
    isVisible.value = false;
    emit('update:visible', false);
};

const getIconComponent = () => {
    switch (props.type) {
        case 'success':
            return 'success-icon';
        case 'error':
            return 'error-icon';
        case 'warning':
            return 'warning-icon';
        case 'info':
            return 'info-icon';
        default:
            return 'success-icon';
    }
};

const getIconBgColor = () => {
    switch (props.type) {
        case 'success':
            return 'bg-green-100';
        case 'error':
            return 'bg-red-100';
        case 'warning':
            return 'bg-orange-100';
        case 'info':
            return 'bg-blue-100';
        default:
            return 'bg-green-100';
    }
};

const getButtonColor = () => {
    switch (props.type) {
        case 'success':
            return '!bg-green-500 hover:!bg-green-600 !border-green-500';
        case 'error':
            return '!bg-red-500 hover:!bg-red-600 !border-red-500';
        case 'warning':
            return '!bg-orange-500 hover:!bg-orange-600 !border-orange-500';
        case 'info':
            return '!bg-blue-500 hover:!bg-blue-600 !border-blue-500';
        default:
            return '!bg-green-500 hover:!bg-green-600 !border-green-500';
    }
};
</script>

<template>
    <Dialog v-model:visible="isVisible" modal :closable="false" :style="{ width: '420px' }" :pt="{
        root: { class: 'rounded-2xl sweet-alert-enter' },
        mask: { class: 'backdrop-blur-sm' },
        header: { class: 'hidden' },
        content: { class: 'p-8' }
    }">
        <div class="text-center relative">
            <!-- Animated Icon Container -->
            <div class="mb-6 flex justify-center">
                <div :class="[getIconBgColor(), 'w-24 h-24 rounded-full flex items-center justify-center icon-bounce']">
                    <!-- Success Icon -->
                    <div v-if="type === 'success'" class="relative w-16 h-16">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                        </svg>
                    </div>

                    <!-- Error Icon -->
                    <div v-else-if="type === 'error'" class="relative w-16 h-16">
                        <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="error-circle" cx="26" cy="26" r="25" fill="none" />
                            <path class="error-line error-line-1" fill="none" d="M16 16 36 36" />
                            <path class="error-line error-line-2" fill="none" d="M36 16 16 36" />
                        </svg>
                    </div>

                    <!-- Warning Icon -->
                    <div v-else-if="type === 'warning'" class="relative w-16 h-16">
                        <svg class="warning-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="warning-circle" cx="26" cy="26" r="25" fill="none" />
                            <path class="warning-line" fill="none" d="M26 18 26 28" />
                            <circle class="warning-dot" cx="26" cy="34" r="2" />
                        </svg>
                    </div>

                    <!-- Info Icon -->
                    <div v-else class="relative w-16 h-16">
                        <svg class="info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="info-circle" cx="26" cy="26" r="25" fill="none" />
                            <circle class="info-dot" cx="26" cy="18" r="2" />
                            <path class="info-line" fill="none" d="M26 24 26 36" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Title with fade in -->
            <h3 class="text-3xl font-bold text-gray-800 mb-3 title-fade-in">{{ title }}</h3>

            <!-- Message with fade in -->
            <p class="text-gray-600 mb-8 text-lg message-fade-in">{{ message }}</p>

            <!-- Actions with slide up -->
            <div class="flex justify-center gap-3 buttons-slide-up">
                <Button v-if="showCancel" :label="cancelText"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-8 !py-3 !rounded-lg !text-base font-semibold !border-gray-200 transition-all duration-200 hover:scale-105"
                    @click="handleCancel" />
                <Button :label="confirmText"
                    :class="[getButtonColor(), 'text-white !px-8 !py-3 !rounded-lg !text-base font-semibold transition-all duration-200 hover:scale-105 hover:shadow-lg']"
                    @click="handleConfirm" />
            </div>

            <!-- Close button (X) -->
            <button @click="closeDialog"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors duration-200 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100"
                aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </Dialog>
</template>

<style scoped>
/* Dialog entrance animation */
@keyframes sweetAlertEnter {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.sweet-alert-enter {
    animation: sweetAlertEnter 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Icon bounce animation */
@keyframes iconBounce {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }
}

.icon-bounce {
    animation: iconBounce 0.6s ease-in-out;
}

/* Success checkmark animation */
.checkmark {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    stroke-width: 3;
    stroke: #10b981;
    stroke-miterlimit: 10;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.4s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

/* Error icon animation */
.error-icon {
    width: 100%;
    height: 100%;
    stroke-width: 3;
    stroke: #ef4444;
}

.error-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.error-line {
    stroke-dasharray: 28;
    stroke-dashoffset: 28;
}

.error-line-1 {
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.4s forwards;
}

.error-line-2 {
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.5s forwards;
}

/* Warning icon animation */
.warning-icon {
    width: 100%;
    height: 100%;
    stroke-width: 3;
    stroke: #f97316;
}

.warning-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.warning-line {
    stroke-dasharray: 20;
    stroke-dashoffset: 20;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.4s forwards;
    stroke-linecap: round;
}

.warning-dot {
    fill: #f97316;
    opacity: 0;
    animation: dotFade 0.3s ease-in 0.6s forwards;
}

@keyframes dotFade {
    to {
        opacity: 1;
    }
}

/* Info icon animation */
.info-icon {
    width: 100%;
    height: 100%;
    stroke-width: 3;
    stroke: #3b82f6;
}

.info-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.info-dot {
    fill: #3b82f6;
    opacity: 0;
    animation: dotFade 0.3s ease-in 0.4s forwards;
}

.info-line {
    stroke-dasharray: 24;
    stroke-dashoffset: 24;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.5s forwards;
    stroke-linecap: round;
}

/* Title fade in animation */
@keyframes titleFadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.title-fade-in {
    animation: titleFadeIn 0.4s ease-out 0.3s both;
}

/* Message fade in animation */
@keyframes messageFadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-fade-in {
    animation: messageFadeIn 0.4s ease-out 0.5s both;
}

/* Buttons slide up animation */
@keyframes buttonsSlideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.buttons-slide-up {
    animation: buttonsSlideUp 0.4s ease-out 0.7s both;
}
</style>