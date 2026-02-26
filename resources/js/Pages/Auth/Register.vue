<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout title="Create Account">
        <h2 class="mb-6 text-xl font-bold text-white">Create your account</h2>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Username -->
            <div>
                <label for="username" class="mb-1 block text-sm font-medium text-gray-300">Username</label>
                <input
                    id="username"
                    v-model="form.username"
                    type="text"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="e.g. jane_doe"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white placeholder-gray-500 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                />
                <p v-if="form.errors.username" class="mt-1 text-xs text-red-400">{{ form.errors.username }}</p>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-300">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white placeholder-gray-500 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-300">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Minimum 8 characters"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white placeholder-gray-500 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-300">Confirm Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Re-enter your password"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white placeholder-gray-500 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                />
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50"
            >
                <span v-if="form.processing">Creating account...</span>
                <span v-else>Create Account</span>
            </button>
        </form>

        <!-- Login link -->
        <p class="mt-6 text-center text-sm text-gray-400">
            Already have an account?
            <a :href="route('login')" class="font-medium text-primary-400 hover:text-primary-300 transition-colors">
                Sign in
            </a>
        </p>
    </GuestLayout>
</template>
