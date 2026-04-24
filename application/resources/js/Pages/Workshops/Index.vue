<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    workshops: Array,
});

// ── Create ────────────────────────────────────────────────────────────────────

const createForm = useForm({
    title: '',
    description: '',
    starts_at: '',
    ends_at: '',
    capacity: '',
});

function store() {
    createForm.post('/workshops', {
        onSuccess: () => createForm.reset(),
    });
}

// ── Edit ──────────────────────────────────────────────────────────────────────

const editingId = ref(null);

const editForm = useForm({
    title: '',
    description: '',
    starts_at: '',
    ends_at: '',
    capacity: '',
});

function toDatetimeLocal(value) {
    if (!value) return '';
    return String(value).replace(' ', 'T').slice(0, 16);
}

function startEdit(workshop) {
    editingId.value = workshop.id;
    editForm.title = workshop.title;
    editForm.description = workshop.description;
    editForm.starts_at = toDatetimeLocal(workshop.starts_at);
    editForm.ends_at = toDatetimeLocal(workshop.ends_at);
    editForm.capacity = workshop.capacity;
}

function update(workshop) {
    editForm.put(`/workshops/${workshop.id}`, {
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
}

function cancelEdit() {
    editingId.value = null;
    editForm.reset();
}

// ── Delete ────────────────────────────────────────────────────────────────────

function destroy(workshop) {
    if (!confirm(`Delete "${workshop.title}"?`)) return;
    router.delete(`/workshops/${workshop.id}`);
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="mx-auto max-w-5xl">
            <h1 class="mb-8 text-2xl font-bold text-gray-900">Workshops</h1>

            <!-- Create form -->
            <div class="mb-8 rounded-lg bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold text-gray-800">New Workshop</h2>

                <form @submit.prevent="store" novalidate>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                            <input
                                v-model="createForm.title"
                                type="text"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': createForm.errors.title }"
                            />
                            <p v-if="createForm.errors.title" class="mt-1 text-sm text-red-600">
                                {{ createForm.errors.title }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Capacity</label>
                            <input
                                v-model="createForm.capacity"
                                type="number"
                                min="1"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': createForm.errors.capacity }"
                            />
                            <p v-if="createForm.errors.capacity" class="mt-1 text-sm text-red-600">
                                {{ createForm.errors.capacity }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                v-model="createForm.description"
                                rows="2"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': createForm.errors.description }"
                            />
                            <p v-if="createForm.errors.description" class="mt-1 text-sm text-red-600">
                                {{ createForm.errors.description }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Starts at</label>
                            <input
                                v-model="createForm.starts_at"
                                type="datetime-local"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': createForm.errors.starts_at }"
                            />
                            <p v-if="createForm.errors.starts_at" class="mt-1 text-sm text-red-600">
                                {{ createForm.errors.starts_at }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ends at</label>
                            <input
                                v-model="createForm.ends_at"
                                type="datetime-local"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': createForm.errors.ends_at }"
                            />
                            <p v-if="createForm.errors.ends_at" class="mt-1 text-sm text-red-600">
                                {{ createForm.errors.ends_at }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="mt-4 rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        Create
                    </button>
                </form>
            </div>

            <!-- Workshop table -->
            <div class="rounded-lg bg-white shadow">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Starts</th>
                            <th class="px-4 py-3">Ends</th>
                            <th class="px-4 py-3">Capacity</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="workshop in props.workshops" :key="workshop.id">
                            <!-- Read row -->
                            <tr v-if="editingId !== workshop.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ workshop.title }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ workshop.starts_at }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ workshop.ends_at }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ workshop.capacity }}</td>
                                <td class="px-4 py-3 space-x-2">
                                    <button
                                        @click="startEdit(workshop)"
                                        class="rounded bg-yellow-400 px-3 py-1 text-xs font-medium text-white hover:bg-yellow-500"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="destroy(workshop)"
                                        class="rounded bg-red-500 px-3 py-1 text-xs font-medium text-white hover:bg-red-600"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit row -->
                            <tr v-else class="border-b border-blue-100 bg-blue-50">
                                <td class="px-4 py-3" colspan="5">
                                    <form @submit.prevent="update(workshop)" novalidate>
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <input
                                                    v-model="editForm.title"
                                                    type="text"
                                                    placeholder="Title"
                                                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': editForm.errors.title }"
                                                />
                                                <p v-if="editForm.errors.title" class="mt-1 text-xs text-red-600">
                                                    {{ editForm.errors.title }}
                                                </p>
                                            </div>

                                            <div>
                                                <input
                                                    v-model="editForm.capacity"
                                                    type="number"
                                                    min="1"
                                                    placeholder="Capacity"
                                                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': editForm.errors.capacity }"
                                                />
                                                <p v-if="editForm.errors.capacity" class="mt-1 text-xs text-red-600">
                                                    {{ editForm.errors.capacity }}
                                                </p>
                                            </div>

                                            <div class="sm:col-span-2">
                                                <textarea
                                                    v-model="editForm.description"
                                                    rows="2"
                                                    placeholder="Description"
                                                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': editForm.errors.description }"
                                                />
                                                <p v-if="editForm.errors.description" class="mt-1 text-xs text-red-600">
                                                    {{ editForm.errors.description }}
                                                </p>
                                            </div>

                                            <div>
                                                <input
                                                    v-model="editForm.starts_at"
                                                    type="datetime-local"
                                                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': editForm.errors.starts_at }"
                                                />
                                                <p v-if="editForm.errors.starts_at" class="mt-1 text-xs text-red-600">
                                                    {{ editForm.errors.starts_at }}
                                                </p>
                                            </div>

                                            <div>
                                                <input
                                                    v-model="editForm.ends_at"
                                                    type="datetime-local"
                                                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': editForm.errors.ends_at }"
                                                />
                                                <p v-if="editForm.errors.ends_at" class="mt-1 text-xs text-red-600">
                                                    {{ editForm.errors.ends_at }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3 space-x-2">
                                            <button
                                                type="submit"
                                                :disabled="editForm.processing"
                                                class="rounded bg-blue-600 px-3 py-1 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                                            >
                                                Save
                                            </button>
                                            <button
                                                type="button"
                                                @click="cancelEdit"
                                                class="rounded bg-gray-200 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-300"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="!props.workshops.length">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                No workshops yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
