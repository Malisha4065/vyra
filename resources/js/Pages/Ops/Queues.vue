<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    snapshot: {
        type: Object,
        required: true,
    },
    horizonUrl: {
        type: String,
        required: true,
    },
});

const totalPending = computed(() =>
    (props.snapshot.queues ?? []).reduce((total, queue) => total + (queue.pending ?? 0), 0),
);

const totalReserved = computed(() =>
    (props.snapshot.queues ?? []).reduce((total, queue) => total + (queue.reserved ?? 0), 0),
);

const totalDelayed = computed(() =>
    (props.snapshot.queues ?? []).reduce((total, queue) => total + (queue.delayed ?? 0), 0),
);

function refreshSnapshot() {
    router.reload({ only: ['snapshot'] });
}
</script>

<template>
    <AppLayout title="Queue Ops">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-4 border-b border-gray-200 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_32%),linear-gradient(135deg,_rgba(255,255,255,0.98),_rgba(240,249,255,0.96))] px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_32%),linear-gradient(135deg,_rgba(17,24,39,0.96),_rgba(24,24,27,0.96))]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600 dark:text-sky-400">
                            Operations
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            Queue health
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Horizon-backed queue backlog visibility for the active environment.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-2xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-400 dark:border-gray-700 dark:text-gray-200"
                            @click="refreshSnapshot"
                        >
                            Refresh
                        </button>
                        <a
                            :href="horizonUrl"
                            class="rounded-2xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700"
                        >
                            Open Horizon
                        </a>
                    </div>
                </div>

                <div class="grid gap-4 px-6 py-5 sm:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                            Pending
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">
                            {{ totalPending }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                            Reserved
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">
                            {{ totalReserved }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-gray-950">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                            Delayed
                        </p>
                        <p class="mt-2 text-3xl font-semibold text-gray-950 dark:text-white">
                            {{ totalDelayed }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                v-if="snapshot.status !== 'ok'"
                class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-sm text-amber-800 dark:border-amber-900/70 dark:bg-amber-950/50 dark:text-amber-200"
            >
                {{ snapshot.message }}
                <span class="ml-2 font-semibold">Current connection: {{ snapshot.connection }}</span>
            </section>

            <section v-else class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Updated {{ new Date(snapshot.generated_at).toLocaleString() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-950">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                    Queue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                    Pending
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                    Reserved
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                                    Delayed
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr v-for="queue in snapshot.queues" :key="queue.queue">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ queue.queue }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ queue.pending }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ queue.reserved }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ queue.delayed }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
