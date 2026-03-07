<script setup>
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    results: {
        type: Array,
        required: true,
    },
    users: {
        type: Array,
        required: true,
    },
    trendingHashtags: {
        type: Array,
        required: true,
    },
    query: {
        type: String,
        default: '',
    },
    hashtag: {
        type: String,
        default: '',
    },
});

const searchQuery = ref(props.query ?? '');

const emptyLabel = computed(() => {
    if (props.hashtag) {
        return `No posts found for #${props.hashtag}.`;
    }

    if (props.query) {
        return `No posts found for "${props.query}".`;
    }

    return 'Search for posts, people, or hashtags.';
});

function relativeTime(isoString) {
    if (!isoString) {
        return '';
    }

    const value = new Date(isoString);
    const diff = Math.floor((Date.now() - value.getTime()) / 1000);

    if (diff < 60) {
        return 'just now';
    }

    if (diff < 3600) {
        return `${Math.floor(diff / 60)}m ago`;
    }

    if (diff < 86400) {
        return `${Math.floor(diff / 3600)}h ago`;
    }

    return `${Math.floor(diff / 86400)}d ago`;
}

function submitSearch() {
    const query = searchQuery.value.trim();

    window.location.href = route('content.discover', query === '' ? {} : { query });
}
</script>

<template>
    <AppLayout title="Discover">
        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,2fr)_320px]">
            <section class="space-y-5">
                <div class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_32%),linear-gradient(135deg,_rgba(255,255,255,0.96),_rgba(240,249,255,0.96))] px-6 py-5 dark:border-gray-800 dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_32%),linear-gradient(135deg,_rgba(17,24,39,0.96),_rgba(24,24,27,0.96))]">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600 dark:text-sky-400">
                            Discover
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            Search posts and hashtags
                        </h1>
                    </div>

                    <form class="flex flex-col gap-3 px-6 py-5 sm:flex-row" @submit.prevent="submitSearch">
                        <input
                            v-model="searchQuery"
                            type="search"
                            class="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-2 focus:ring-sky-200 dark:border-gray-700 dark:bg-gray-950 dark:text-white dark:focus:border-sky-500 dark:focus:ring-sky-500/20"
                            placeholder="Search by post text, username, or #hashtag"
                        >
                        <button
                            type="submit"
                            class="rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-700"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <div v-if="results.length === 0" class="rounded-[2rem] border border-dashed border-gray-300 bg-white px-8 py-16 text-center dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ emptyLabel }}
                    </p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Trending hashtags are listed on the right to seed discovery.
                    </p>
                </div>

                <article
                    v-for="post in results"
                    :key="post.id"
                    class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex items-start justify-between gap-4 px-6 py-5">
                        <div>
                            <a
                                :href="route('profile.show', { username: post.author.username })"
                                class="text-base font-semibold text-gray-950 transition hover:text-sky-600 dark:text-white dark:hover:text-sky-400"
                            >
                                @{{ post.author.username }}
                            </a>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                                {{ relativeTime(post.published_at) }}
                            </p>
                        </div>
                        <a
                            :href="route('feed', { focus_post_id: post.id })"
                            class="rounded-full border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 transition hover:border-gray-400 dark:border-gray-700 dark:text-gray-300"
                        >
                            Open in feed
                        </a>
                    </div>

                    <div class="space-y-4 px-6 pb-6">
                        <p class="whitespace-pre-wrap text-[15px] leading-7 text-gray-800 dark:text-gray-200">
                            {{ post.body }}
                        </p>

                        <div v-if="post.media.length > 0" class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="media in post.media"
                                :key="media.id ?? media.url"
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-950"
                            >
                                <img
                                    v-if="media.kind === 'image'"
                                    :src="media.url"
                                    :alt="media.original_name ?? 'Post media'"
                                    class="max-h-[20rem] w-full object-cover"
                                >
                                <video
                                    v-else
                                    :src="media.url"
                                    controls
                                    class="max-h-[20rem] w-full object-cover"
                                />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                v-for="tag in post.hashtags"
                                :key="tag"
                                :href="route('content.discover', { hashtag: tag })"
                                class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800 dark:bg-sky-500/15 dark:text-sky-300"
                            >
                                #{{ tag }}
                            </a>
                        </div>

                        <div class="flex items-center gap-5 border-t border-gray-200 pt-4 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <span>{{ post.counts.comments }} comments</span>
                            <span>{{ post.counts.reactions }} reactions</span>
                        </div>
                    </div>
                </article>
            </section>

            <aside class="space-y-4">
                <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">
                        People
                    </p>
                    <div v-if="users.length === 0" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        No matching profiles.
                    </div>
                    <div v-else class="mt-4 space-y-3">
                        <a
                            v-for="user in users"
                            :key="user.id"
                            :href="route('profile.show', { username: user.username })"
                            class="block rounded-2xl border border-gray-200 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-50 dark:border-gray-800 dark:hover:border-sky-700 dark:hover:bg-sky-950/30"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ user.display_name || user.username }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @{{ user.username }}
                                    </p>
                                </div>
                                <span
                                    v-if="user.is_private"
                                    class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    Private
                                </span>
                            </div>
                            <p v-if="user.bio" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ user.bio }}
                            </p>
                        </a>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">
                        Trending tags
                    </p>
                    <div class="mt-4 space-y-3">
                        <a
                            v-for="trend in trendingHashtags"
                            :key="trend.tag"
                            :href="route('content.discover', { hashtag: trend.tag })"
                            class="flex items-center justify-between rounded-2xl border border-gray-200 px-4 py-3 text-sm transition hover:border-sky-300 hover:bg-sky-50 dark:border-gray-800 dark:hover:border-sky-700 dark:hover:bg-sky-950/30"
                        >
                            <span class="font-semibold text-gray-900 dark:text-white">#{{ trend.tag }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ trend.count }}</span>
                        </a>
                    </div>
                </section>
            </aside>
        </div>
    </AppLayout>
</template>
