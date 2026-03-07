<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserAutocomplete from '@/Components/UserAutocomplete.vue';

const props = defineProps({
    feed: {
        type: Object,
        required: true,
    },
    focusPostId: {
        type: String,
        default: null,
    },
    mode: {
        type: String,
        default: 'top',
    },
});

const page = usePage();

const items = ref(props.feed.items ?? []);
const nextCursor = ref(props.feed.next_cursor ?? null);
const loadingMore = ref(false);
const uploading = ref(false);
const uploadedMedia = ref([]);
const rebuilding = ref(false);
const mentionSearch = ref('');
const composerRef = ref(null);

const form = useForm({
    body: '',
    media: [],
});

const canSubmit = computed(() => {
    return !form.processing && !uploading.value && (form.body.trim() !== '' || uploadedMedia.value.length > 0);
});

watch(() => props.feed, (feed) => {
    items.value = feed.items ?? [];
    nextCursor.value = feed.next_cursor ?? null;
}, { deep: true });

async function scrollToFocusedPost() {
    if (!props.focusPostId) {
        return;
    }

    await nextTick();

    document.querySelector(`[data-post-id="${props.focusPostId}"]`)?.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
    });
}

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

function removeDraftMedia(path) {
    uploadedMedia.value = uploadedMedia.value.filter((media) => media.path !== path);
}

function insertMention(user) {
    const mention = `@${user.username} `;
    const textarea = composerRef.value;

    if (!textarea) {
        form.body = `${form.body}${form.body.endsWith(' ') || form.body === '' ? '' : ' '}${mention}`;
        mentionSearch.value = '';
        return;
    }

    const start = textarea.selectionStart ?? form.body.length;
    const end = textarea.selectionEnd ?? form.body.length;
    const prefix = form.body.slice(0, start);
    const suffix = form.body.slice(end);
    const separator = prefix.length > 0 && !/\s$/.test(prefix) ? ' ' : '';

    form.body = `${prefix}${separator}${mention}${suffix}`;
    mentionSearch.value = '';

    nextTick(() => {
        const cursor = (prefix + separator + mention).length;
        textarea.focus();
        textarea.setSelectionRange(cursor, cursor);
    });
}

function switchMode(mode) {
    window.location.href = route('feed', {
        mode,
    });
}

async function uploadFiles(event) {
    const files = Array.from(event.target.files ?? []);

    if (files.length === 0) {
        return;
    }

    uploading.value = true;

    try {
        for (const file of files) {
            const payload = new FormData();
            payload.append('file', file);

            const response = await window.axios.post(route('posts.media.store'), payload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            uploadedMedia.value.push(response.data.data);
        }
    } finally {
        uploading.value = false;
        event.target.value = '';
    }
}

function submitPost() {
    form.media = uploadedMedia.value;

    form.post(route('posts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            uploadedMedia.value = [];
            items.value = page.props.feed?.items ?? items.value;
            nextCursor.value = page.props.feed?.next_cursor ?? nextCursor.value;
        },
    });
}

async function loadMore() {
    if (loadingMore.value || nextCursor.value === null) {
        return;
    }

    loadingMore.value = true;

    try {
        const response = await window.axios.get(route('feed', {
            before_score: nextCursor.value,
            mode: props.mode,
        }), {
            headers: {
                Accept: 'application/json',
            },
        });

        const incoming = response.data.data ?? [];
        const known = new Set(items.value.map((item) => item.post_id));

        items.value = items.value.concat(
            incoming.filter((item) => !known.has(item.post_id)),
        );
        nextCursor.value = response.data.meta?.next_cursor ?? null;
    } finally {
        loadingMore.value = false;
    }
}

async function rebuildFeed() {
    if (rebuilding.value) {
        return;
    }

    rebuilding.value = true;

    try {
        await window.axios.post(route('feed.rebuild'), {}, {
            headers: {
                Accept: 'application/json',
            },
        });
    } finally {
        rebuilding.value = false;
    }
}

onMounted(() => {
    scrollToFocusedPost();
});

watch(items, () => {
    scrollToFocusedPost();
}, { deep: true });
</script>

<template>
    <AppLayout title="Feed">
        <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,2fr)_320px]">
            <section class="space-y-5">
                <form
                    class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    @submit.prevent="submitPost"
                >
                    <div class="border-b border-gray-200 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.12),_transparent_34%),linear-gradient(135deg,_rgba(255,255,255,0.96),_rgba(240,253,244,0.96))] px-6 py-5 dark:border-gray-800 dark:bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.08),_transparent_34%),linear-gradient(135deg,_rgba(17,24,39,0.96),_rgba(24,24,27,0.96))]">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-600 dark:text-emerald-400">
                                    Publish
                                </p>
                                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                    Share what matters now
                                </h1>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                                    :class="mode === 'top'
                                        ? 'bg-gray-950 text-white dark:bg-white dark:text-gray-950'
                                        : 'border border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300'"
                                    @click="switchMode('top')"
                                >
                                    Top
                                </button>
                                <button
                                    type="button"
                                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                                    :class="mode === 'latest'
                                        ? 'bg-gray-950 text-white dark:bg-white dark:text-gray-950'
                                        : 'border border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300'"
                                    @click="switchMode('latest')"
                                >
                                    Latest
                                </button>
                                <button
                                    type="button"
                                    class="rounded-full border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:border-emerald-400 dark:border-emerald-700 dark:text-emerald-300"
                                    :disabled="rebuilding"
                                    @click="rebuildFeed"
                                >
                                    {{ rebuilding ? 'Queueing...' : 'Rebuild feed' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <textarea
                            ref="composerRef"
                            v-model="form.body"
                            rows="4"
                            class="w-full resize-none rounded-2xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-950 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-500/20"
                            placeholder="What are you building, thinking, or shipping?"
                        />

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/30">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">
                                    Mention someone
                                </p>
                                <span class="text-xs text-emerald-700/80 dark:text-emerald-300/80">
                                    Inserts `@username` into the composer
                                </span>
                            </div>
                            <UserAutocomplete
                                v-model="mentionSearch"
                                placeholder="Search users to mention"
                                empty-label="No matching users to mention."
                                @select="insertMention"
                            />
                        </div>

                        <div v-if="uploadedMedia.length > 0" class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="media in uploadedMedia"
                                :key="media.path"
                                class="relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-950"
                            >
                                <img
                                    v-if="media.kind === 'image'"
                                    :src="media.url"
                                    :alt="media.original_name"
                                    class="h-48 w-full object-cover"
                                >
                                <video
                                    v-else
                                    :src="media.url"
                                    controls
                                    class="h-48 w-full object-cover"
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-3 rounded-full bg-black/70 px-2.5 py-1 text-xs font-semibold text-white"
                                    @click="removeDraftMedia(media.path)"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-400 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600">
                                <input
                                    type="file"
                                    class="hidden"
                                    multiple
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm"
                                    @change="uploadFiles"
                                >
                                <span>{{ uploading ? 'Uploading...' : 'Add media' }}</span>
                            </label>

                            <button
                                type="submit"
                                class="rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="!canSubmit"
                            >
                                Publish post
                            </button>
                        </div>

                        <p v-if="form.errors.post" class="text-sm text-rose-600 dark:text-rose-400">
                            {{ form.errors.post }}
                        </p>
                    </div>
                </form>

                <div v-if="items.length === 0" class="rounded-[2rem] border border-dashed border-gray-300 bg-white px-8 py-16 text-center dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">
                        Your timeline is quiet.
                    </p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Publish the first post or follow more people to populate the feed.
                    </p>
                </div>

                <article
                    v-for="item in items"
                    :key="item.post_id"
                    :data-post-id="item.post_id"
                    class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    :class="item.post_id === focusPostId ? 'ring-2 ring-emerald-400 ring-offset-2 dark:ring-emerald-500 dark:ring-offset-gray-950' : ''"
                >
                    <div class="flex items-start justify-between gap-4 px-6 py-5">
                        <div>
                            <a
                                :href="route('profile.show', { username: item.post.author.username })"
                                class="text-base font-semibold text-gray-950 transition hover:text-emerald-600 dark:text-white dark:hover:text-emerald-400"
                            >
                                @{{ item.post.author.username }}
                            </a>
                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                                {{ relativeTime(item.post.published_at) }}
                            </p>
                        </div>

                        <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ item.post.counts.reactions }} reactions
                        </div>
                    </div>

                    <div class="space-y-4 px-6 pb-6">
                        <p v-if="item.post.body" class="whitespace-pre-wrap text-[15px] leading-7 text-gray-800 dark:text-gray-200">
                            {{ item.post.body }}
                        </p>

                        <div v-if="item.post.media.length > 0" class="grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="media in item.post.media"
                                :key="media.id ?? media.url"
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-950"
                            >
                                <img
                                    v-if="media.kind === 'image'"
                                    :src="media.url"
                                    :alt="media.original_name ?? 'Post media'"
                                    class="max-h-[28rem] w-full object-cover"
                                >
                                <video
                                    v-else
                                    :src="media.url"
                                    controls
                                    class="max-h-[28rem] w-full object-cover"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-5 border-t border-gray-200 pt-4 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <span>{{ item.post.counts.comments }} comments</span>
                            <span>{{ item.post.counts.reactions }} reactions</span>
                        </div>
                    </div>
                </article>

                <div v-if="nextCursor !== null" class="flex justify-center">
                    <button
                        type="button"
                        class="rounded-full border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-400 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600"
                        :disabled="loadingMore"
                        @click="loadMore"
                    >
                        {{ loadingMore ? 'Loading...' : 'Load more' }}
                    </button>
                </div>
            </section>

            <aside class="space-y-4">
                <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">
                        Feed Engine
                    </p>
                    <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">
                        {{ mode === 'top' ? 'Algorithmic ranking is active' : 'Chronological ranking is active' }}
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Posts from regular accounts are cached into follower timelines, while high-follower accounts are merged in with the hybrid strategy.
                        {{ mode === 'top'
                            ? ' Ranking now boosts engaged posts with comments, reactions, and media.'
                            : ' The latest mode reads directly against the fan-out timeline order.' }}
                    </p>
                </section>

                <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-500 dark:text-gray-400">
                        Draft State
                    </p>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <p>{{ uploadedMedia.length }} media attachment{{ uploadedMedia.length === 1 ? '' : 's' }} staged</p>
                        <p>{{ form.body.trim().length }} characters in composer</p>
                    </div>
                </section>
            </aside>
        </div>
    </AppLayout>
</template>
