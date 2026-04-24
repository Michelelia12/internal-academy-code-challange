<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <nav class="bg-white shadow">
        <div class="mx-auto max-w-5xl px-4">
            <div class="flex h-14 items-center justify-between">
                <div class="flex items-center space-x-6">
                    <a
                        href="/dashboard"
                        class="text-sm font-medium text-gray-700 hover:text-blue-600"
                    >
                        Dashboard
                    </a>
                    <template v-if="user.is_admin">
                        <a
                            href="/workshops"
                            class="text-sm font-medium text-gray-700 hover:text-blue-600"
                        >
                            Workshops
                        </a>
                        <a
                            href="/admin/statistics"
                            class="text-sm font-medium text-gray-700 hover:text-blue-600"
                        >
                            Statistics
                        </a>
                    </template>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ user.name }}</span>
                    <button
                        type="button"
                        class="text-sm font-medium text-gray-700 hover:text-red-600"
                        @click="logout"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>
