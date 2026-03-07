<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search users...',
    },
    limit: {
        type: Number,
        default: 8,
    },
    minimumLength: {
        type: Number,
        default: 2,
    },
    emptyLabel: {
        type: String,
        default: 'No matching users.',
    },
});

const emit = defineEmits([
    'update:modelValue',
    'select',
]);

const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const results = ref([]);
const loading = ref(false);
const error = ref(null);
const isOpen = ref(false);

let debounceTimer = null;
let requestId = 0;

watch(() => props.modelValue, (value) => {
    const query = value.trim();

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    if (query.length < props.minimumLength) {
        requestId += 1;
        results.value = [];
        error.value = null;
        loading.value = false;
        isOpen.value = false;
        return;
    }

    debounceTimer = setTimeout(() => {
        fetchResults(query);
    }, 220);
}, { immediate: true });

async function fetchResults(query) {
    const activeRequestId = ++requestId;

    loading.value = true;
    error.value = null;

    try {
        const response = await window.axios.get(route('users.search', {
            query,
            limit: props.limit,
        }));

        if (activeRequestId !== requestId) {
            return;
        }

        results.value = response.data?.data ?? [];
        isOpen.value = true;
    } catch {
        if (activeRequestId !== requestId) {
            return;
        }

        results.value = [];
        error.value = 'User search is temporarily unavailable.';
        isOpen.value = true;
    } finally {
        if (activeRequestId === requestId) {
            loading.value = false;
        }
    }
}

function selectUser(user) {
    emit('select', user);
    isOpen.value = false;
}

function closeResults() {
    setTimeout(() => {
        isOpen.value = false;
    }, 120);
}

function openResults() {
    if (results.value.length > 0 || loading.value || error.value) {
        isOpen.value = true;
    }
}

function initialsFor(user) {
    const seed = (user.display_name || user.username || '?').trim();

    return seed.slice(0, 1).toUpperCase();
}

onBeforeUnmount(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});
</script>

<template>
    <div class="relative">
        <input
            v-model="inputValue"
            type="search"
            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-200 dark:border-gray-700 dark:bg-gray-950 dark:text-white dark:focus:border-sky-500 dark:focus:ring-sky-500/20"
            :placeholder="placeholder"
            autocomplete="off"
            @focus="openResults"
            @blur="closeResults"
        >

        <div
            v-if="isOpen"
            class="absolute left-0 right-0 top-[calc(100%+0.5rem)] z-30 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div v-if="loading" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                Searching...
            </div>

            <div v-else-if="error" class="px-4 py-3 text-sm text-red-600 dark:text-red-300">
                {{ error }}
            </div>

            <div v-else-if="results.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                {{ emptyLabel }}
            </div>

            <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                <button
                    v-for="user in results"
                    :key="user.id"
                    type="button"
                    class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-sky-50 dark:hover:bg-sky-950/30"
                    @mousedown.prevent="selectUser(user)"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-100 text-xs font-semibold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                        {{ initialsFor(user) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                {{ user.display_name || user.username }}
                            </p>
                            <span
                                v-if="user.is_private"
                                class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            >
                                Private
                            </span>
                        </div>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            @{{ user.username }}
                        </p>
                        <p v-if="user.bio" class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">
                            {{ user.bio }}
                        </p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
