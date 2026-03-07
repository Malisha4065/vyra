<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout title="Sign In">
        <h2 class="mb-6 text-xl font-bold text-white">Welcome back</h2>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-300">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
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
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white placeholder-gray-500 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center gap-2">
                <input
                    id="remember"
                    v-model="form.remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-primary-600 focus:ring-primary-500/50"
                />
                <label for="remember" class="text-sm text-gray-400">Remember me</label>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50"
            >
                <span v-if="form.processing">Signing in...</span>
                <span v-else>Sign In</span>
            </button>

            <p class="text-center text-sm text-gray-400">
                <a :href="route('password.request')" class="font-medium text-primary-400 hover:text-primary-300 transition-colors">
                    Forgot your password?
                </a>
            </p>
        </form>

        <!-- Register link -->
        <p class="mt-6 text-center text-sm text-gray-400">
            Don't have an account?
            <a :href="route('register')" class="font-medium text-primary-400 hover:text-primary-300 transition-colors">
                Create one
            </a>
        </p>
    </GuestLayout>
</template>
