<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserAutocomplete from '@/Components/UserAutocomplete.vue';
import { ensureEcho } from '@/lib/echo';

const props = defineProps({
    targetUserId: {
        type: String,
        default: null,
    },
    conversationId: {
        type: String,
        default: null,
    },
});

const page = usePage();

const authUserId = computed(() => page.props.auth?.user?.id ?? null);

const conversations = ref([]);
const messages = ref([]);
const activeConversationId = ref(null);
const composer = ref('');
const conversationSearch = ref('');

const loadingConversations = ref(false);
const loadingMessages = ref(false);
const sendingMessage = ref(false);
const startingConversation = ref(false);

const remoteTypingUserIds = ref([]);
const directConversationError = ref(null);

const messageListRef = ref(null);

let echo = null;
let subscribedConversationId = null;
let typingDebounce = null;
const typingClearTimers = new Map();
let localTyping = false;

const activeConversation = computed(() =>
    conversations.value.find((conversation) => conversation.id === activeConversationId.value) ?? null,
);

function normalizeReadReceipt(receipt) {
    return {
        user_id: receipt?.user_id ?? null,
        read_at: receipt?.read_at ?? null,
    };
}

function normalizeParticipant(participant) {
    return {
        id: participant.id,
        username: participant.username,
        read_state: {
            last_read_message_id: participant.read_state?.last_read_message_id ?? null,
            last_read_at: participant.read_state?.last_read_at ?? null,
        },
    };
}

function normalizeConversation(conversation) {
    return {
        id: conversation.id,
        type: conversation.type,
        updated_at: conversation.updated_at,
        participants: (conversation.participants ?? []).map(normalizeParticipant),
        latest_message: conversation.latest_message
            ? {
                id: conversation.latest_message.id,
                body: conversation.latest_message.body,
                sender_id: conversation.latest_message.sender_id,
                created_at: conversation.latest_message.created_at,
                sender: conversation.latest_message.sender ?? null,
            }
            : null,
    };
}

const typingLabel = computed(() => {
    if (!activeConversation.value || remoteTypingUserIds.value.length === 0) {
        return null;
    }

    const participants = activeConversation.value.participants ?? [];
    const names = remoteTypingUserIds.value
        .map((id) => participants.find((participant) => participant.id === id)?.username)
        .filter(Boolean);

    if (names.length === 0) {
        return 'Someone is typing...';
    }

    return `${names.join(', ')} ${names.length > 1 ? 'are' : 'is'} typing...`;
});

function findParticipant(conversation, userId) {
    return (conversation?.participants ?? []).find((participant) => participant.id === userId) ?? null;
}

function conversationTitle(conversation) {
    const otherParticipant = (conversation?.participants ?? [])
        .find((participant) => participant.id !== authUserId.value);

    return otherParticipant?.username ?? 'Direct conversation';
}

function latestPreview(conversation) {
    return conversation?.latest_message?.body ?? 'No messages yet';
}

function conversationHasUnread(conversation) {
    const latestMessage = conversation?.latest_message;

    if (!latestMessage || latestMessage.sender_id === authUserId.value) {
        return false;
    }

    const authParticipant = findParticipant(conversation, authUserId.value);

    return authParticipant?.read_state?.last_read_message_id !== latestMessage.id;
}

function isOwnMessage(message) {
    return message.sender_id === authUserId.value;
}

function normalizeMessage(message) {
    return {
        id: message.id,
        conversation_id: message.conversation_id,
        sender_id: message.sender_id,
        body: message.body,
        metadata: message.metadata ?? {},
        created_at: message.created_at,
        sender: {
            id: message.sender?.id,
            username: message.sender?.username,
        },
        read_receipts: (message.read_receipts ?? []).map(normalizeReadReceipt),
    };
}

function messageReadLabel(message) {
    if (!isOwnMessage(message)) {
        return null;
    }

    const otherReceipts = (message.read_receipts ?? []).filter((receipt) => receipt.user_id !== authUserId.value);

    if (otherReceipts.length === 0) {
        return null;
    }

    if (otherReceipts.length === 1) {
        return 'Seen';
    }

    return `Seen by ${otherReceipts.length}`;
}

function clearTypingState() {
    remoteTypingUserIds.value = [];

    for (const timeout of typingClearTimers.values()) {
        clearTimeout(timeout);
    }

    typingClearTimers.clear();
}

function touchConversation(conversationId, message) {
    const index = conversations.value.findIndex((conversation) => conversation.id === conversationId);

    if (index === -1) {
        loadConversations(false);
        return;
    }

    const current = conversations.value[index];
    const updated = {
        ...current,
        latest_message: {
            id: message.id,
            body: message.body,
            sender_id: message.sender_id,
            created_at: message.created_at,
            sender: message.sender,
        },
        updated_at: message.created_at,
    };

    conversations.value.splice(index, 1);
    conversations.value.unshift(updated);
}

function upsertMessage(message) {
    const normalized = normalizeMessage(message);
    const index = messages.value.findIndex((current) => current.id === normalized.id);

    if (index !== -1) {
        messages.value[index] = {
            ...messages.value[index],
            ...normalized,
        };

        return;
    }

    messages.value.push(normalized);
    messages.value.sort((a, b) => {
        if (!a.created_at || !b.created_at) {
            return 0;
        }

        return a.created_at.localeCompare(b.created_at);
    });

    scrollMessagesToBottom();
}

function updateConversationReadState(conversationId, readerId, messageIds, readAt) {
    const conversationIndex = conversations.value.findIndex((conversation) => conversation.id === conversationId);

    if (conversationIndex === -1 || messageIds.length === 0) {
        return;
    }

    const conversation = conversations.value[conversationIndex];

    conversations.value[conversationIndex] = {
        ...conversation,
        participants: (conversation.participants ?? []).map((participant) => {
            if (participant.id !== readerId) {
                return participant;
            }

            return {
                ...participant,
                read_state: {
                    last_read_message_id: messageIds[messageIds.length - 1],
                    last_read_at: readAt,
                },
            };
        }),
    };
}

function applyReadReceipts(payload) {
    updateConversationReadState(payload.conversation_id, payload.reader_id, payload.message_ids ?? [], payload.read_at);

    messages.value = messages.value.map((message) => {
        if (!payload.message_ids?.includes(message.id)) {
            return message;
        }

        const alreadyRead = message.read_receipts.some((receipt) => receipt.user_id === payload.reader_id);

        if (alreadyRead) {
            return message;
        }

        return {
            ...message,
            read_receipts: [
                ...message.read_receipts,
                {
                    user_id: payload.reader_id,
                    read_at: payload.read_at,
                },
            ],
        };
    });
}

function scrollMessagesToBottom() {
    nextTick(() => {
        if (!messageListRef.value) {
            return;
        }

        messageListRef.value.scrollTop = messageListRef.value.scrollHeight;
    });
}

function handleTypingEvent(payload) {
    if (payload.conversation_id !== activeConversationId.value) {
        return;
    }

    if (payload.user_id === authUserId.value) {
        return;
    }

    if (payload.is_typing) {
        if (!remoteTypingUserIds.value.includes(payload.user_id)) {
            remoteTypingUserIds.value.push(payload.user_id);
        }

        if (typingClearTimers.has(payload.user_id)) {
            clearTimeout(typingClearTimers.get(payload.user_id));
        }

        const timeout = setTimeout(() => {
            remoteTypingUserIds.value = remoteTypingUserIds.value.filter((id) => id !== payload.user_id);
            typingClearTimers.delete(payload.user_id);
        }, 3000);

        typingClearTimers.set(payload.user_id, timeout);

        return;
    }

    remoteTypingUserIds.value = remoteTypingUserIds.value.filter((id) => id !== payload.user_id);

    if (typingClearTimers.has(payload.user_id)) {
        clearTimeout(typingClearTimers.get(payload.user_id));
        typingClearTimers.delete(payload.user_id);
    }
}

async function subscribeToConversation(conversationId) {
    echo = await ensureEcho();

    if (!echo) {
        return;
    }

    if (subscribedConversationId) {
        echo.leave(`conversation.${subscribedConversationId}`);
    }

    subscribedConversationId = conversationId;

    echo.private(`conversation.${conversationId}`)
        .listen('.communication.message.sent', (payload) => {
            if (payload.conversation_id !== conversationId) {
                return;
            }

            upsertMessage(payload.message);
            touchConversation(payload.conversation_id, payload.message);

            if (payload.message.sender_id !== authUserId.value) {
                markConversationRead();
            }
        })
        .listen('.communication.typing.updated', (payload) => {
            handleTypingEvent(payload);
        })
        .listen('.communication.read.updated', (payload) => {
            handleTypingEvent({
                ...payload,
                is_typing: false,
                user_id: payload.reader_id,
            });
            applyReadReceipts(payload);
        });
}

async function loadConversations(selectFirst = true) {
    loadingConversations.value = true;

    try {
        const response = await window.axios.get(route('messages.conversations.index'));
        conversations.value = (response.data.data ?? []).map(normalizeConversation);

        if (selectFirst && !activeConversationId.value && conversations.value.length > 0) {
            activeConversationId.value = conversations.value[0].id;
        }
    } finally {
        loadingConversations.value = false;
    }
}

async function loadMessages() {
    if (!activeConversationId.value) {
        messages.value = [];
        return;
    }

    loadingMessages.value = true;

    try {
        const response = await window.axios.get(route('messages.conversations.messages.index', {
            conversation: activeConversationId.value,
        }));

        messages.value = (response.data.data ?? []).map(normalizeMessage);
        scrollMessagesToBottom();
    } finally {
        loadingMessages.value = false;
    }
}

async function markConversationRead() {
    if (!activeConversationId.value) {
        return;
    }

    const response = await window.axios.put(route('messages.conversations.read', {
        conversation: activeConversationId.value,
    }));

    if ((response.data?.data?.marked_count ?? 0) === 0 || !activeConversation.value) {
        return;
    }

    const readMessageIds = messages.value
        .filter((message) => !isOwnMessage(message))
        .map((message) => message.id);

    if (readMessageIds.length === 0) {
        return;
    }

    updateConversationReadState(
        activeConversationId.value,
        authUserId.value,
        readMessageIds,
        new Date().toISOString(),
    );
}

async function emitTyping(isTyping) {
    if (!activeConversationId.value) {
        return;
    }

    await window.axios.post(route('messages.conversations.typing', {
        conversation: activeConversationId.value,
    }), {
        is_typing: isTyping,
    });

    localTyping = isTyping;
}

async function sendMessage() {
    const body = composer.value.trim();

    if (!activeConversationId.value || body === '' || sendingMessage.value) {
        return;
    }

    sendingMessage.value = true;

    try {
        await window.axios.post(route('messages.conversations.messages.store', {
            conversation: activeConversationId.value,
        }), {
            body,
        });

        composer.value = '';

        if (typingDebounce) {
            clearTimeout(typingDebounce);
            typingDebounce = null;
        }

        if (localTyping) {
            await emitTyping(false);
        }

        await loadMessages();
        await loadConversations(false);
    } finally {
        sendingMessage.value = false;
    }
}

async function startDirectConversation(targetUserId) {
    if (!targetUserId || targetUserId === authUserId.value || startingConversation.value) {
        return;
    }

    startingConversation.value = true;
    directConversationError.value = null;

    try {
        const response = await window.axios.post(route('messages.conversations.direct.start'), {
            target_user_id: targetUserId,
        });

        await loadConversations(false);

        activeConversationId.value = response.data?.data?.conversation_id ?? activeConversationId.value;
        conversationSearch.value = '';
    } catch (error) {
        directConversationError.value = error?.response?.data?.message ?? 'Unable to start this conversation.';
    } finally {
        startingConversation.value = false;
    }
}

function handleComposerInput() {
    if (!activeConversationId.value) {
        return;
    }

    const hasText = composer.value.trim() !== '';

    if (hasText && !localTyping) {
        emitTyping(true);
    }

    if (typingDebounce) {
        clearTimeout(typingDebounce);
    }

    typingDebounce = setTimeout(() => {
        if (localTyping) {
            emitTyping(false);
        }
    }, 1200);
}

watch(activeConversationId, async (conversationId) => {
    clearTypingState();

    if (!conversationId) {
        return;
    }

    await loadMessages();
    await markConversationRead();
    await subscribeToConversation(conversationId);
});

onMounted(async () => {
    await loadConversations(true);
    if (props.conversationId && conversations.value.some((conversation) => conversation.id === props.conversationId)) {
        activeConversationId.value = props.conversationId;
    }
    await startDirectConversation(props.targetUserId);
});

onBeforeUnmount(async () => {
    clearTypingState();

    if (typingDebounce) {
        clearTimeout(typingDebounce);
    }

    if (localTyping) {
        try {
            await emitTyping(false);
        } catch {
            // ignore network shutdown during navigation
        }
    }

    if (echo && subscribedConversationId) {
        echo.leave(`conversation.${subscribedConversationId}`);
    }
});
</script>

<template>
    <AppLayout title="Messages">
        <div class="grid gap-4 lg:grid-cols-[320px_1fr]">
            <section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Conversations
                    </h1>
                    <span
                        v-if="startingConversation"
                        class="text-xs font-medium text-primary-600 dark:text-primary-400"
                    >
                        Starting...
                    </span>
                </div>

                <div class="mb-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                        New direct chat
                    </p>
                    <UserAutocomplete
                        v-model="conversationSearch"
                        placeholder="Search someone to message"
                        empty-label="No users available to message."
                        @select="startDirectConversation($event.id)"
                    />
                </div>

                <p v-if="directConversationError" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/70 dark:bg-red-950/60 dark:text-red-300">
                    {{ directConversationError }}
                </p>

                <div v-if="loadingConversations" class="text-sm text-gray-500 dark:text-gray-400">
                    Loading conversations...
                </div>

                <div v-else-if="conversations.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                    No conversations yet.
                </div>

                <div v-else class="space-y-2">
                    <button
                        v-for="conversation in conversations"
                        :key="conversation.id"
                        type="button"
                        class="w-full rounded-xl border p-3 text-left transition-colors"
                        :class="conversation.id === activeConversationId
                            ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-500/10'
                            : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'"
                        @click="activeConversationId = conversation.id"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                {{ conversationTitle(conversation) }}
                            </p>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="conversationHasUnread(conversation)"
                                    class="h-2.5 w-2.5 rounded-full bg-primary-500"
                                />
                                <span class="text-xs text-gray-400">
                                    {{ conversation.latest_message?.created_at?.slice(11, 16) ?? '' }}
                                </span>
                            </div>
                        </div>
                        <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ latestPreview(conversation) }}
                        </p>
                    </button>
                </div>
            </section>

            <section class="flex min-h-[70vh] flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <header class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ activeConversation ? conversationTitle(activeConversation) : 'Select a conversation' }}
                    </h2>
                    <p v-if="typingLabel" class="mt-1 text-xs text-primary-600 dark:text-primary-400">
                        {{ typingLabel }}
                    </p>
                </header>

                <div ref="messageListRef" class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
                    <div v-if="!activeConversation" class="text-sm text-gray-500 dark:text-gray-400">
                        Choose a conversation to start messaging.
                    </div>

                    <div v-else-if="loadingMessages" class="text-sm text-gray-500 dark:text-gray-400">
                        Loading messages...
                    </div>

                    <div v-else-if="messages.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        No messages yet.
                    </div>

                    <div v-else>
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="mb-3 flex"
                            :class="isOwnMessage(message) ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="max-w-[75%] rounded-2xl px-4 py-2 text-sm"
                                :class="isOwnMessage(message)
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100'"
                            >
                                <p v-if="!isOwnMessage(message)" class="mb-1 text-xs opacity-70">
                                    {{ message.sender?.username ?? 'Unknown' }}
                                </p>
                                <p class="whitespace-pre-wrap break-words">{{ message.body }}</p>
                                <p
                                    v-if="messageReadLabel(message)"
                                    class="mt-2 text-[11px] font-medium opacity-80"
                                >
                                    {{ messageReadLabel(message) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="border-t border-gray-200 p-4 dark:border-gray-800" @submit.prevent="sendMessage">
                    <div class="flex gap-2">
                        <input
                            v-model="composer"
                            type="text"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm text-gray-900 outline-none ring-primary-500 focus:ring-2 dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                            placeholder="Type a message..."
                            :disabled="!activeConversation || sendingMessage"
                            @input="handleComposerInput"
                        >
                        <button
                            type="submit"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!activeConversation || sendingMessage || composer.trim() === ''"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AppLayout>
</template>
