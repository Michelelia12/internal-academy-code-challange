<script setup>
import AppNav from '../Components/AppNav.vue';
import ToastNotification from '../Components/ToastNotification.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <AppNav />
        <main>
            <slot />
        </main>
        <ToastNotification />
    </div>
</template>
