<script setup>
import AppNav from '../Components/AppNav.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <AppNav />
        <main>
            <slot />
        </main>
    </div>
</template>
