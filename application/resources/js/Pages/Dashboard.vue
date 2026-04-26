<script setup>
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import { useToast } from '../composables/useToast.js';

defineOptions({ layout: AppLayout });

const props = defineProps({
    workshops: Array,
});

const { show } = useToast();

function register(workshop) {
    useForm({}).post(`/workshops/${workshop.id}/registrations`, {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.overlap) {
                show(errors.overlap);
            }
        },
    });
}

function unregister(workshop) {
    router.delete(`/workshops/${workshop.id}/registrations`, { preserveScroll: true });
}

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function formatDate(value) {
    const d = new Date(value);
    const hh = String(d.getUTCHours()).padStart(2, '0');
    const mm = String(d.getUTCMinutes()).padStart(2, '0');
    return `${d.getUTCDate()} ${MONTHS[d.getUTCMonth()]} ${d.getUTCFullYear()}, ${hh}:${mm}`;
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="mx-auto max-w-4xl">
            <h1 class="mb-8 text-2xl font-bold text-gray-900">Upcoming Workshops</h1>

            <div v-if="!props.workshops.length" class="rounded-lg bg-white p-8 text-center text-gray-400 shadow">
                No upcoming workshops available.
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="workshop in props.workshops"
                    :key="workshop.id"
                    class="rounded-lg bg-white p-6 shadow"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-semibold text-gray-900">{{ workshop.title }}</h2>
                            <small>ID: {{ workshop.id }}</small>
                            <p class="mt-1 text-sm text-gray-600">{{ workshop.description }}</p>

                            <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-500">
                                <span>
                                    <span class="font-medium text-gray-700">Starts:</span>
                                    {{ formatDate(workshop.starts_at) }}
                                </span>
                                <span>
                                    <span class="font-medium text-gray-700">Ends:</span>
                                    {{ formatDate(workshop.ends_at) }}
                                </span>
                                <span>
                                    <span class="font-medium text-gray-700">Available seats:</span>
                                    {{ workshop.available_seats ?? workshop.capacity }} / {{ workshop.capacity }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <template v-if="workshop.user_registration">
                                <span
                                    v-if="workshop.user_registration.status === 'confirmed'"
                                    class="inline-block rounded bg-green-100 px-3 py-1 text-sm font-medium text-green-800"
                                >
                                    Confirmed
                                </span>
                                <span
                                    v-else
                                    class="inline-block rounded bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800"
                                >
                                    Waiting List
                                </span>
                                <button
                                    @click="unregister(workshop)"
                                    class="rounded bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                                >
                                    Unregister
                                </button>
                            </template>
                            <template v-else>
                                <button
                                    v-if="(workshop.available_seats ?? workshop.capacity) > 0"
                                    @click="register(workshop)"
                                    class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                >
                                    Register
                                </button>
                                <button
                                    v-else
                                    @click="register(workshop)"
                                    class="rounded bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600"
                                >
                                    Join Waiting List
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
