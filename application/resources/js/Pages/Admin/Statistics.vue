<script setup>
import { onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    most_popular: Object,
    total_count: Number,
});

onMounted(() => {
    window.Echo.channel('academy').listen('RegistrationUpdated', () => {
        router.reload({ only: ['most_popular', 'total_count'] });
    });
});

onUnmounted(() => {
    window.Echo.leaveChannel('academy');
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="mx-auto max-w-4xl">
            <h1 class="mb-8 text-2xl font-bold text-gray-900">Statistics</h1>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Total registrations -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-500">Total Confirmed Registrations</p>
                    <p class="mt-2 text-4xl font-bold text-blue-600">{{ props.total_count }}</p>
                    <p class="mt-1 text-xs text-gray-400">Updates live via WebSocket</p>
                </div>

                <!-- Most popular workshop -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-500">Most Popular Workshop</p>
                    <template v-if="props.most_popular">
                        <p class="mt-2 text-lg font-semibold text-gray-900">
                            {{ props.most_popular.title }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ props.most_popular.starts_at }}
                        </p>
                    </template>
                    <p v-else class="mt-2 text-sm text-gray-400">No registrations yet.</p>
                </div>
            </div>
        </div>
    </div>
</template>
