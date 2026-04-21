<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50">
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow">
            <h1 class="mb-6 text-2xl font-bold text-gray-900">Sign in</h1>

            <form @submit.prevent="submit" novalidate>
                <div class="mb-4">
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="mb-6">
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.password }"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                >
                    Sign in
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                No account?
                <a href="/register" class="text-blue-600 hover:underline">Register</a>
            </p>
        </div>
    </div>
</template>
