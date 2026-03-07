<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const form = useForm({
    email: '',
});

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <GuestLayout title="Forgot Password">
        <h2 class="mb-4 text-xl font-bold text-white">Reset your password</h2>
        <p class="mb-6 text-sm leading-6 text-gray-300">
            Enter your account email and we’ll send you a reset link if the account exists.
        </p>

        <p v-if="page.props.flash?.success" class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ page.props.flash.success }}
        </p>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-300">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-500 disabled:opacity-50"
            >
                {{ form.processing ? 'Sending...' : 'Email reset link' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400">
            Remembered your password?
            <a :href="route('login')" class="font-medium text-primary-400 hover:text-primary-300 transition-colors">
                Sign in
            </a>
        </p>
    </GuestLayout>
</template>
