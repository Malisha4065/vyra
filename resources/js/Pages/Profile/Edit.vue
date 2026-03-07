<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    profile: Object,
});

const profileForm = useForm({
    display_name: props.profile.display_name || '',
    bio: props.profile.bio || '',
    website: props.profile.website || '',
    location: props.profile.location || '',
    date_of_birth: props.profile.date_of_birth || '',
});

const privacyForm = useForm({
    is_private: props.profile.is_private,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteAccountForm = useForm({
    current_password: '',
});

const submitProfile = () => {
    profileForm.put(route('profile.update'));
};

const submitPrivacy = () => {
    privacyForm.put(route('profile.privacy'));
};

const submitPassword = () => {
    passwordForm.put(route('profile.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const submitDeleteAccount = () => {
    deleteAccountForm.delete(route('profile.destroy'));
};
</script>

<template>
    <AppLayout title="Edit Profile">
        <div class="mx-auto max-w-2xl space-y-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>

            <!-- Profile Form -->
            <form @submit.prevent="submitProfile" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h2>

                <!-- Display Name -->
                <div>
                    <label for="display_name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Display Name</label>
                    <input
                        id="display_name"
                        v-model="profileForm.display_name"
                        type="text"
                        maxlength="50"
                        placeholder="Your display name"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="profileForm.errors.display_name" class="mt-1 text-xs text-red-500">{{ profileForm.errors.display_name }}</p>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                    <textarea
                        id="bio"
                        v-model="profileForm.bio"
                        rows="3"
                        maxlength="500"
                        placeholder="Tell people about yourself"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    ></textarea>
                    <div class="mt-1 flex justify-between text-xs text-gray-400">
                        <p v-if="profileForm.errors.bio" class="text-red-500">{{ profileForm.errors.bio }}</p>
                        <span>{{ profileForm.bio.length }} / 500</span>
                    </div>
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Website</label>
                    <input
                        id="website"
                        v-model="profileForm.website"
                        type="url"
                        placeholder="https://yoursite.com"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="profileForm.errors.website" class="mt-1 text-xs text-red-500">{{ profileForm.errors.website }}</p>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                    <input
                        id="location"
                        v-model="profileForm.location"
                        type="text"
                        maxlength="100"
                        placeholder="e.g. San Francisco, CA"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="profileForm.errors.location" class="mt-1 text-xs text-red-500">{{ profileForm.errors.location }}</p>
                </div>

                <!-- Date of Birth -->
                <div>
                    <label for="date_of_birth" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                    <input
                        id="date_of_birth"
                        v-model="profileForm.date_of_birth"
                        type="date"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="profileForm.errors.date_of_birth" class="mt-1 text-xs text-red-500">{{ profileForm.errors.date_of_birth }}</p>
                </div>

                <!-- Submit -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="profileForm.processing"
                        class="rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/50 disabled:opacity-50"
                    >
                        {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>

                <!-- Success -->
                <p v-if="$page.props.flash?.success" class="text-sm font-medium text-green-600 dark:text-green-400">
                    {{ $page.props.flash.success }}
                </p>
            </form>

            <!-- Privacy Settings -->
            <form @submit.prevent="submitPrivacy" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Privacy</h2>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Private Account</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Only approved followers can see your posts</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            v-model="privacyForm.is_private"
                            type="checkbox"
                            class="peer sr-only"
                            @change="submitPrivacy"
                        />
                        <div class="peer h-6 w-11 rounded-full bg-gray-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-2 peer-focus:ring-primary-500/50 dark:bg-gray-700"></div>
                    </label>
                </div>
            </form>

            <form @submit.prevent="submitPassword" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Password</h2>

                <div>
                    <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                    <input
                        id="current_password"
                        v-model="passwordForm.current_password"
                        type="password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                    <input
                        id="password"
                        v-model="passwordForm.password"
                        type="password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="passwordForm.processing"
                        class="rounded-xl bg-gray-950 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                    >
                        {{ passwordForm.processing ? 'Updating...' : 'Update password' }}
                    </button>
                </div>
            </form>

            <form @submit.prevent="submitDeleteAccount" class="space-y-4 rounded-2xl border border-red-200 bg-red-50/70 p-6 shadow-sm dark:border-red-900/60 dark:bg-red-950/20">
                <h2 class="text-lg font-semibold text-red-900 dark:text-red-300">Danger Zone</h2>
                <p class="text-sm text-red-700 dark:text-red-400">
                    Deleting your account is permanent and removes your profile, posts, and relationships.
                </p>

                <div>
                    <label for="delete_current_password" class="mb-1 block text-sm font-medium text-red-800 dark:text-red-300">Confirm Password</label>
                    <input
                        id="delete_current_password"
                        v-model="deleteAccountForm.current_password"
                        type="password"
                        class="w-full rounded-xl border border-red-200 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-200 dark:border-red-900/60 dark:bg-gray-900 dark:text-white"
                    />
                    <p v-if="deleteAccountForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ deleteAccountForm.errors.current_password }}</p>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="deleteAccountForm.processing"
                        class="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ deleteAccountForm.processing ? 'Deleting...' : 'Delete account' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
