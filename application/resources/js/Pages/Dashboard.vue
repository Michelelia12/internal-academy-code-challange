<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    workshops: Array,
});

function registerForm(workshop) {
    return useForm({});
}

function register(workshop) {
    useForm({}).post(`/workshops/${workshop.id}/registrations`);
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
                            <p class="mt-1 text-sm text-gray-600">{{ workshop.description }}</p>

                            <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-500">
                                <span>
                                    <span class="font-medium text-gray-700">Starts:</span>
                                    {{ workshop.starts_at }}
                                </span>
                                <span>
                                    <span class="font-medium text-gray-700">Ends:</span>
                                    {{ workshop.ends_at }}
                                </span>
                                <span>
                                    <span class="font-medium text-gray-700">Available seats:</span>
                                    {{ workshop.available_seats ?? workshop.capacity }} / {{ workshop.capacity }}
                                </span>
                            </div>
                        </div>

                        <div class="shrink-0">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
