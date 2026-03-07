<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserAutocomplete from '@/Components/UserAutocomplete.vue';

const props = defineProps({
    user: Object,
    profile: Object,
});

const page = usePage();

const isOwnProfile = computed(() => page.props.auth?.user?.id === props.user?.id);
const peopleDialogOpen = ref(false);
const peopleSearch = ref('');

function openPeopleSearch() {
    peopleDialogOpen.value = true;
}

function closePeopleSearch() {
    peopleDialogOpen.value = false;
    peopleSearch.value = '';
}

function openUserProfile(user) {
    closePeopleSearch();
    window.location.href = route('profile.show', { username: user.username });
}
</script>

<template>
    <AppLayout title="Profile">
        <div class="mx-auto max-w-3xl">
            <!-- Cover Image -->
            <div class="relative h-48 overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 to-primary-800 sm:h-56">
                <img
                    v-if="profile.cover_url"
                    :src="profile.cover_url"
                    :alt="`${user.username}'s cover`"
                    class="h-full w-full object-cover"
                />
            </div>

            <!-- Profile Header -->
            <div class="relative -mt-16 px-4 sm:px-6">
                <div class="flex items-end gap-4">
                    <!-- Avatar -->
                    <div class="h-28 w-28 flex-shrink-0 overflow-hidden rounded-full border-4 border-white bg-gray-200 shadow-lg dark:border-gray-900 dark:bg-gray-700 sm:h-32 sm:w-32">
                        <img
                            v-if="profile.avatar_url"
                            :src="profile.avatar_url"
                            :alt="user.username"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-3xl font-bold text-gray-400">
                            {{ user.username.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <!-- Name & Handle -->
                    <div class="mb-2">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ profile.display_name || user.username }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">@{{ user.username }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <Link
                        v-if="!isOwnProfile"
                        :href="route('messages.index', { target_user_id: user.id })"
                        class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-900 transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-700 dark:text-white dark:hover:border-gray-600 dark:hover:bg-gray-800"
                    >
                        Message
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                        @click="openPeopleSearch"
                    >
                        Find people
                    </button>
                </div>

                <!-- Bio -->
                <p v-if="profile.bio" class="mt-4 text-gray-700 dark:text-gray-300">
                    {{ profile.bio }}
                </p>

                <!-- Meta -->
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span v-if="profile.location" class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        {{ profile.location }}
                    </span>
                    <a v-if="profile.website" :href="profile.website" target="_blank" class="flex items-center gap-1 text-primary-600 hover:underline dark:text-primary-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        {{ profile.website.replace(/^https?:\/\//, '') }}
                    </a>
                    <span class="flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Joined {{ new Date(user.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) }}
                    </span>
                </div>
            </div>

            <!-- Posts placeholder -->
            <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500 dark:text-gray-400">Posts will appear here once the Content domain is built.</p>
            </div>
        </div>

        <div
            v-if="peopleDialogOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 px-4"
            @click.self="closePeopleSearch"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-primary-600 dark:text-primary-400">
                            People
                        </p>
                        <h2 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            Jump to a profile
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Search someone, then open their profile to follow or message them.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border border-gray-300 px-3 py-1 text-sm font-semibold text-gray-700 transition hover:border-gray-400 dark:border-gray-700 dark:text-gray-300"
                        @click="closePeopleSearch"
                    >
                        Close
                    </button>
                </div>

                <div class="px-6 py-5">
                    <UserAutocomplete
                        v-model="peopleSearch"
                        placeholder="Search by username or display name"
                        empty-label="No matching profiles."
                        @select="openUserProfile"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
