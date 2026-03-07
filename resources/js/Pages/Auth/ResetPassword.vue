<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        default: '',
    },
});

const page = usePage();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <GuestLayout title="Reset Password">
        <h2 class="mb-4 text-xl font-bold text-white">Choose a new password</h2>

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

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-300">New password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-300">Confirm password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-gray-700 bg-gray-800/50 px-4 py-3 text-sm text-white transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-500 disabled:opacity-50"
            >
                {{ form.processing ? 'Resetting...' : 'Reset password' }}
            </button>
        </form>
    </GuestLayout>
</template>
