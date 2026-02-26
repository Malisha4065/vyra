<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-primary-950 via-gray-900 to-gray-950 px-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-white">Vyra</h1>
                <p class="mt-2 text-sm text-gray-400">Connect. Share. Discover.</p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl border border-gray-800 bg-gray-900/60 p-8 shadow-2xl backdrop-blur-xl">
                <slot />
            </div>
        </div>
    </div>
</template>
