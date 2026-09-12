<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    subscribedModules: {
        type: Array,
        default: () => [],
    },
    allModules: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const user = usePage().props.auth.user;
const toggling = ref(null);

const moduleCards = [
    {
        title: 'Manage Students',
        description: 'View and manage your student roster, QR codes, and details.',
        href: '/students',
        icon: 'students',
        slug: 'student',
        color: 'bg-blue-50 text-blue-700 group-hover:bg-blue-100',
    },
    {
        title: 'Class Schedules',
        description: 'Set up and manage your class timetables, rooms, and subjects.',
        href: '/class-schedules',
        icon: 'schedule',
        slug: 'classschedule',
        color: 'bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100',
    },
    {
        title: 'Mark Attendance',
        description: 'Scan QR codes or manually record student attendance.',
        href: '/attendances',
        icon: 'attendance',
        slug: 'attendance',
        color: 'bg-emerald-50 text-emerald-700 group-hover:bg-emerald-100',
    },
    {
        title: 'Track Payments',
        description: 'Manage monthly fees, discounts, and payment receipts.',
        href: '/payments',
        icon: 'payment',
        slug: 'payment',
        color: 'bg-amber-50 text-amber-700 group-hover:bg-amber-100',
    },
    {
        title: 'Send Notifications',
        description: 'Message parents and students via SMS, WhatsApp, or email.',
        href: '/notifications',
        icon: 'notification',
        slug: 'notification',
        color: 'bg-rose-50 text-rose-700 group-hover:bg-rose-100',
    },
    {
        title: 'Marketplace',
        description: 'Browse and subscribe to teaching modules and resources.',
        href: '/marketplace',
        icon: 'marketplace',
        slug: null,
        color: 'bg-purple-50 text-purple-700 group-hover:bg-purple-100',
    },
];

const quickActions = computed(() =>
    moduleCards.filter((m) => !m.slug || props.subscribedModules.includes(m.slug)),
);

const toggleModule = async (mod) => {
    if (toggling.value === mod.slug) return;
    toggling.value = mod.slug;

    try {
        const url = mod.is_subscribed
            ? route('modules.unsubscribe', mod.slug)
            : route('modules.subscribe', mod.slug);

        const method = mod.is_subscribed ? 'DELETE' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''
                ),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.message || 'Action failed');
        }

        // Flip local state + reload dashboard data live
        mod.is_subscribed = !mod.is_subscribed;
        router.reload({ only: ['subscribedModules', 'stats'] });
    } catch (e) {
        alert(e.message || 'Failed to update subscription.');
    } finally {
        toggling.value = null;
    }
};

const formatCurrency = (val) =>
    new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR', maximumFractionDigits: 0 }).format(val || 0);

const attendanceRate = computed(() => {
    const total = props.stats.attendance_today || 0;
    if (total === 0) return 0;
    return Math.round(((props.stats.attendance_present || 0) / total) * 100);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-8 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Welcome banner -->
                <div class="mb-8 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 shadow-lg">
                    <div class="px-6 py-8 sm:px-8">
                        <h1 class="text-2xl font-bold text-white">
                            Welcome back, {{ user.name }}!
                        </h1>
                        <p class="mt-2 text-indigo-100">
                            Here's an overview of your tuition class management system.
                        </p>
                    </div>
                </div>

                <!-- ==================== LIVE STATS CARDS ==================== -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Today's Overview</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                        <!-- Students -->
                        <div v-if="stats.students_total !== undefined" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.students_total }}</p>
                                    <p class="text-xs text-gray-500">Total Students</p>
                                </div>
                            </div>
                            <p v-if="stats.students_this_week" class="mt-3 text-xs text-blue-600 font-medium">
                                +{{ stats.students_this_week }} this week
                            </p>
                        </div>

                        <!-- Schedules today -->
                        <div v-if="stats.schedules_today !== undefined" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.schedules_today }}</p>
                                    <p class="text-xs text-gray-500">Classes Today</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-indigo-600 font-medium">
                                {{ stats.schedules_total }} total active schedules
                            </p>
                        </div>

                        <!-- Attendance rate -->
                        <div v-if="stats.attendance_today !== undefined" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.attendance_today }}</p>
                                    <p class="text-xs text-gray-500">Attendance Today</p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-xs">
                                <span class="font-medium text-emerald-600">{{ attendanceRate }}% present</span>
                                <span class="text-gray-400">|</span>
                                <span class="text-amber-600">{{ stats.attendance_late || 0 }} late</span>
                                <span class="text-gray-400">|</span>
                                <span class="text-red-600">{{ stats.attendance_absent || 0 }} absent</span>
                            </div>
                        </div>

                        <!-- Revenue -->
                        <div v-if="stats.revenue_this_month !== undefined" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.revenue_this_month) }}</p>
                                    <p class="text-xs text-gray-500">Revenue This Month</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-amber-600 font-medium">
                                {{ stats.payments_this_month }} payments collected
                            </p>
                        </div>

                        <!-- Notifications -->
                        <div v-if="stats.notifications_total !== undefined" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50">
                                    <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ stats.notifications_total }}</p>
                                    <p class="text-xs text-gray-500">Notifications Sent</p>
                                </div>
                            </div>
                            <p v-if="stats.notifications_unsent" class="mt-3 text-xs text-rose-600 font-medium">
                                {{ stats.notifications_unsent }} drafts pending
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ==================== MODULE TOGGLE CARDS ==================== -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Your Modules</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="mod in allModules"
                            :key="mod.id"
                            class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div class="h-1.5 w-full" :class="mod.is_subscribed ? 'bg-emerald-500' : 'bg-gray-200'" />
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="text-sm font-semibold text-gray-900">{{ mod.name }}</h4>
                                    <span
                                        v-if="mod.is_subscribed"
                                        class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700"
                                    >
                                        Active
                                    </span>
                                </div>
                                <p class="mt-1 flex-1 text-xs text-gray-500 leading-relaxed">
                                    {{ mod.description }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ mod.price > 0 ? formatCurrency(mod.price) : 'Free' }}
                                    </p>
                                    <button
                                        @click="toggleModule(mod)"
                                        :disabled="toggling === mod.slug"
                                        :class="[
                                            mod.is_subscribed
                                                ? 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'
                                                : 'bg-indigo-600 text-white hover:bg-indigo-700',
                                        ]"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors disabled:opacity-50"
                                    >
                                        <svg v-if="toggling === mod.slug" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        {{ toggling === mod.slug ? '...' : (mod.is_subscribed ? 'Remove' : 'Add') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== QUICK ACTIONS ==================== -->
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Quick Actions</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="action in quickActions"
                        :key="action.title"
                        :href="action.href"
                        class="group flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md"
                    >
                        <div
                            :class="action.color"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg transition-colors"
                        >
                            <!-- Students icon -->
                            <svg v-if="action.icon === 'students'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                            </svg>
                            <!-- Schedule icon -->
                            <svg v-else-if="action.icon === 'schedule'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <!-- Attendance icon -->
                            <svg v-else-if="action.icon === 'attendance'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Payment icon -->
                            <svg v-else-if="action.icon === 'payment'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            <!-- Notification icon -->
                            <svg v-else-if="action.icon === 'notification'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            <!-- Marketplace icon -->
                            <svg v-else-if="action.icon === 'marketplace'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016A3.001 3.001 0 0021 9.35m-18 0V6.375a3 3 0 013-3h.375a3 3 0 013 3v.375" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600">
                                {{ action.title }}
                            </h4>
                            <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                                {{ action.description }}
                            </p>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-gray-300 group-hover:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
