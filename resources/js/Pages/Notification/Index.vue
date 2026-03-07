<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ensureEcho } from '@/lib/echo';

const page = usePage();

const notifications = ref([]);
const unreadOnly = ref(false);
const unreadTotal = ref(0);
const loading = ref(false);
const markingAll = ref(false);

let echo = null;
let subscribedChannel = null;

const authUserId = computed(() => page.props.auth?.user?.id ?? null);

const emptyLabel = computed(() => unreadOnly.value
    ? 'No unread notifications.'
    : 'No notifications yet.');

function dispatchUnreadCount() {
    window.dispatchEvent(new CustomEvent('notifications:unread-changed', {
        detail: {
            unread_count: unreadTotal.value,
        },
    }));
}

function normalizeNotification(notification) {
    return {
        id: notification.id,
        type: notification.type,
        title: notification.title,
        body: notification.body,
        data: notification.data ?? {},
        read_at: notification.read_at,
        created_at: notification.created_at,
    };
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

function notificationAccent(notification) {
    if (notification.type.startsWith('social.')) {
        return 'border-sky-200 bg-sky-50/70 dark:border-sky-900/70 dark:bg-sky-950/30';
    }

    if (notification.type.startsWith('content.')) {
        return 'border-amber-200 bg-amber-50/70 dark:border-amber-900/70 dark:bg-amber-950/30';
    }

    return 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900';
}

async function loadNotifications() {
    loading.value = true;

    try {
        const response = await window.axios.get(route('notifications.index', {
            per_page: 30,
            unread_only: unreadOnly.value ? 1 : 0,
        }));

        notifications.value = (response.data.data ?? []).map(normalizeNotification);
        unreadTotal.value = response.data.meta?.unread_total ?? 0;
        dispatchUnreadCount();
    } finally {
        loading.value = false;
    }
}

async function markRead(notificationId) {
    const current = notifications.value.find((notification) => notification.id === notificationId);

    if (!current || current.read_at) {
        return;
    }

    const response = await window.axios.put(route('notifications.read', {
        notification: notificationId,
    }), {}, {
        headers: {
            Accept: 'application/json',
        },
    });

    const updated = normalizeNotification(response.data.data);
    notifications.value = notifications.value
        .map((notification) => notification.id === notificationId ? updated : notification)
        .filter((notification) => unreadOnly.value ? notification.read_at === null : true);

    unreadTotal.value = response.data.meta?.unread_total ?? unreadTotal.value;
    dispatchUnreadCount();
}

async function markAllRead() {
    if (markingAll.value || unreadTotal.value === 0) {
        return;
    }

    markingAll.value = true;

    try {
        const response = await window.axios.put(route('notifications.read-all'), {}, {
            headers: {
                Accept: 'application/json',
            },
        });

        unreadTotal.value = response.data.meta?.unread_total ?? 0;
        notifications.value = unreadOnly.value
            ? []
            : notifications.value.map((notification) => ({
                ...notification,
                read_at: notification.read_at ?? new Date().toISOString(),
            }));
        dispatchUnreadCount();
    } finally {
        markingAll.value = false;
    }
}

async function subscribeRealtime() {
    echo = await ensureEcho();

    if (!echo || !authUserId.value) {
        return;
    }

    subscribedChannel = `users.${authUserId.value}`;

    echo.private(subscribedChannel)
        .listen('.notification.created', (payload) => {
            const notification = normalizeNotification(payload.notification);

            unreadTotal.value += 1;
            dispatchUnreadCount();

            if (unreadOnly.value && notification.read_at) {
                return;
            }

            notifications.value.unshift(notification);
            notifications.value = notifications.value
                .filter((current, index, all) => all.findIndex((item) => item.id === current.id) === index)
                .slice(0, 30);
        });
}

onMounted(async () => {
    await loadNotifications();
    await subscribeRealtime();
});

onBeforeUnmount(() => {
    if (echo && subscribedChannel) {
        echo.leave(subscribedChannel);
    }
});
</script>

<template>
    <AppLayout title="Alerts">
        <div class="mx-auto max-w-4xl">
            <section class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <header class="border-b border-gray-200 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.18),_transparent_34%),linear-gradient(135deg,_rgba(255,255,255,0.9),_rgba(255,247,237,0.9))] px-6 py-6 dark:border-gray-800 dark:bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.12),_transparent_34%),linear-gradient(135deg,_rgba(17,24,39,0.96),_rgba(24,24,27,0.96))]">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600 dark:text-amber-400">
                                Notification Center
                            </p>
                            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                Alerts and activity
                            </h1>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Live updates from follows, reactions, comments, and account events.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                                :class="unreadOnly
                                    ? 'border-amber-300 bg-amber-100 text-amber-900 dark:border-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                                    : 'border-gray-300 text-gray-700 hover:border-gray-400 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600'"
                                @click="unreadOnly = !unreadOnly; loadNotifications()"
                            >
                                {{ unreadOnly ? 'Showing unread' : 'Show unread only' }}
                            </button>

                            <button
                                type="button"
                                class="rounded-full bg-gray-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                                :disabled="markingAll || unreadTotal === 0"
                                @click="markAllRead"
                            >
                                Mark all read
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3 text-sm">
                        <span class="rounded-full bg-rose-500 px-2.5 py-1 font-semibold text-white">
                            {{ unreadTotal }}
                        </span>
                        <span class="text-gray-600 dark:text-gray-400">
                            unread notifications
                        </span>
                    </div>
                </header>

                <div class="p-6">
                    <div v-if="loading" class="rounded-2xl border border-dashed border-gray-300 px-6 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Loading notifications...
                    </div>

                    <div v-else-if="notifications.length === 0" class="rounded-2xl border border-dashed border-gray-300 px-6 py-12 text-center dark:border-gray-700">
                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ emptyLabel }}
                        </p>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            New events will appear here in real time.
                        </p>
                    </div>

                    <div v-else class="space-y-3">
                        <article
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="rounded-2xl border p-4 transition-colors"
                            :class="[notificationAccent(notification), notification.read_at ? 'opacity-75' : 'shadow-sm']"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="!notification.read_at"
                                            class="inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"
                                        />
                                        <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                            {{ notification.title }}
                                        </p>
                                    </div>

                                    <p v-if="notification.body" class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">
                                        {{ notification.body }}
                                    </p>

                                    <div class="mt-3 flex items-center gap-3 text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                                        <span>{{ notification.type }}</span>
                                        <span>{{ relativeTime(notification.created_at) }}</span>
                                    </div>
                                </div>

                                <button
                                    v-if="!notification.read_at"
                                    type="button"
                                    class="shrink-0 rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-gray-400 hover:text-gray-950 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-white"
                                    @click="markRead(notification.id)"
                                >
                                    Mark read
                                </button>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
