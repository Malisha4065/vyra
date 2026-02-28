<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Nav -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-900/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a :href="route('feed')" class="text-xl font-bold tracking-tight text-primary-600 dark:text-primary-400">
                    Vyra
                </a>

                <div class="flex items-center gap-4">
                    <a :href="route('messages.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
                        Messages
                    </a>
                    <a :href="route('profile.edit')" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
                        Profile
                    </a>
                    <form method="POST" :action="route('logout')">
                        <input type="hidden" name="_token" :value="$page.props._token ?? ''">
                        <button type="submit" class="text-sm font-medium text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
