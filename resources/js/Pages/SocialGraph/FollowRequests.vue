<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    requests: Object,
});

const accept = (id) => {
    router.post(route('follow-requests.accept', id));
};

const reject = (id) => {
    router.post(route('follow-requests.reject', id));
};
</script>

<template>
    <AppLayout title="Follow Requests">
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Follow Requests</h1>

            <div v-if="requests.data.length === 0" class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500 dark:text-gray-400">No pending follow requests.</p>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="request in requests.data"
                    :key="request.id"
                    class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <img
                                v-if="request.requester?.profile?.avatar_url"
                                :src="request.requester.profile.avatar_url"
                                :alt="request.requester.username"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-lg font-bold text-gray-400">
                                {{ request.requester.username.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ request.requester.profile?.display_name || request.requester.username }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">@{{ request.requester.username }}</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="accept(request.id)"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                        >
                            Accept
                        </button>
                        <button
                            @click="reject(request.id)"
                            class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
