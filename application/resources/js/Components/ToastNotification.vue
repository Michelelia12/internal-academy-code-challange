<script setup>
import { watch } from 'vue';
import { useToast } from '../composables/useToast.js';

const { message, visible, hide } = useToast();

let timer = null;

watch(visible, (val) => {
    if (val) {
        clearTimeout(timer);
        timer = setTimeout(hide, 4000);
    }
});
</script>

<template>
    <div
        v-if="visible"
        role="alert"
        class="fixed bottom-4 right-4 z-50 max-w-sm rounded-lg bg-red-600 px-4 py-3 text-white shadow-lg"
    >
        <div class="flex items-center justify-between gap-4">
            <span>{{ message }}</span>
            <button
                @click="hide"
                class="shrink-0 text-lg font-bold leading-none"
                aria-label="Close"
            >&times;</button>
        </div>
    </div>
</template>
