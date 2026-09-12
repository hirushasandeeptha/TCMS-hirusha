<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const modules = ref([]);
const loading = ref(true);
const error = ref(null);
const subscribing = ref(null);

const fetchModules = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(route('marketplace.data'));
        if (!response.ok) {
            throw new Error('Failed to load marketplace modules.');
        }
        const data = await response.json();
        modules.value = data.modules || [];
    } catch (e) {
        error.value = e.message || 'An unexpected error occurred.';
    } finally {
        loading.value = false;
    }
};

const toggleSubscription = async (mod) => {
    if (subscribing.value === mod.slug) return;
    subscribing.value = mod.slug;

    try {
        const url = mod.is_subscribed
            ? route('marketplace.unsubscribe', mod.slug)
            : route('marketplace.subscribe', mod.slug);

        const method = mod.is_subscribed ? 'DELETE' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''
                ),
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Subscription action failed.');
        }

        mod.is_subscribed = !mod.is_subscribed;
    } catch (e) {
        error.value = e.message || 'Failed to update subscription.';
    } finally {
        subscribing.value = null;
    }
};

onMounted(fetchModules);
</script>

<template>
    <Head title="Marketplace" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Marketplace
            </h2>
        </template>

        <div class="py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Description -->
                <div class="mb-8">
                    <p class="max-w-2xl text-sm text-gray-500">
                        Browse available teaching modules and subscribe to the ones that fit your curriculum. Manage your subscriptions at any time.
                    </p>
                </div>

                <!-- Loading state -->
                <div v-if="loading" class="flex flex-col items-center justify-center py-16">
                    <svg class="h-10 w-10 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-500">Loading modules...</p>
                </div>

                <!-- Error state -->
                <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-6">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-red-800">Something went wrong</h3>
                            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
                            <button
                                @click="fetchModules"
                                class="mt-3 text-sm font-medium text-red-600 underline hover:text-red-800"
                            >
                                Try again
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-else-if="modules.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 py-16"
                >
                    <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500">No modules available yet.</p>
                </div>

                <!-- Module grid -->
                <div
                    v-else
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="mod in modules"
                        :key="mod.id"
                        class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition-shadow hover:shadow-md"
                    >
                        <!-- Card header accent -->
                        <div class="h-1.5 w-full" :class="mod.is_subscribed ? 'bg-emerald-500' : 'bg-indigo-500'" />

                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-base font-semibold text-gray-900">{{ mod.name }}</h3>
                                <span
                                    v-if="mod.is_subscribed"
                                    class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700"
                                >
                                    Subscribed
                                </span>
                            </div>

                            <p class="mt-2 flex-1 text-sm text-gray-500 leading-relaxed">
                                {{ mod.description || 'No description available.' }}
                            </p>

                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-lg font-bold text-gray-900">
                                    {{ mod.price > 0 ? `$${mod.price.toFixed(2)}` : 'Free' }}
                                </p>

                                <button
                                    @click="toggleSubscription(mod)"
                                    :disabled="subscribing === mod.slug"
                                    :class="[
                                        mod.is_subscribed
                                            ? 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'
                                            : 'bg-indigo-600 text-white hover:bg-indigo-700',
                                    ]"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50"
                                >
                                    <svg
                                        v-if="subscribing === mod.slug"
                                        class="h-4 w-4 animate-spin"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <template v-if="subscribing === mod.slug">
                                        Processing...
                                    </template>
                                    <template v-else-if="mod.is_subscribed">
                                        Unsubscribe
                                    </template>
                                    <template v-else>
                                        Subscribe
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
