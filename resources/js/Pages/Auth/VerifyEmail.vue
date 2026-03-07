<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const form = useForm({});
const logoutForm = useForm({});

function resend() {
    form.post(route('verification.send'));
}

function logout() {
    logoutForm.post(route('logout'));
}
</script>

<template>
    <GuestLayout title="Verify Email">
        <h2 class="mb-4 text-xl font-bold text-white">Verify your email</h2>
        <p class="text-sm leading-6 text-gray-300">
            We sent a verification link to <span class="font-semibold text-white">{{ page.props.auth?.user?.email }}</span>.
            Open that email and confirm the address before using the app normally.
        </p>

        <p v-if="page.props.flash?.success" class="mt-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ page.props.flash.success }}
        </p>

        <form class="mt-6 space-y-4" @submit.prevent="resend">
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-500 disabled:opacity-50"
            >
                {{ form.processing ? 'Sending...' : 'Resend verification email' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400">
            Wrong account?
            <button
                type="button"
                class="font-medium text-primary-400 hover:text-primary-300 transition-colors"
                @click="logout"
            >
                Sign out
            </button>
        </p>
    </GuestLayout>
</template>
