<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import OpenSessionModal from './OpenSessionModal.vue'
import CloseSessionModal from './CloseSessionModal.vue'
const page = usePage()

const props = defineProps({
    activeSession: {
        type: Object,
        default: null,
    },

    cashAccounts: {
        type: Array,
        default: () => [],
    },

        warehouses: {
        type: Array,
        default: () => [],
    },
})

/*
|--------------------------------------------------------------------------
| Shared Auth Context
|--------------------------------------------------------------------------
*/


const authUser = computed(() => page.props.auth?.user ?? null)

const currentBranch = computed(() => page.props.auth?.current_branch ?? null)

const cashAccounts = computed(() => props.cashAccounts ?? [])

/*
|--------------------------------------------------------------------------
| Dashboard Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('session')

const tabs = [
    {
        key: 'session',
        label: 'Session',
    },
    {
        key: 'sales',
        label: 'Sales',
    },
    {
        key: 'payments',
        label: 'Payments',
    },
    {
        key: 'cash-movement',
        label: 'Cash Movement',
    },
    {
        key: 'receipts',
        label: 'Receipts',
    },
    {
        key: 'returns',
        label: 'Returns',
    },
    {
        key: 'reports',
        label: 'Reports',
    },
]

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

const showOpenSessionModal = ref(false)
const showCloseSessionModal = ref(false)

const openingForm = ref({
    warehouse_id: '',
    cash_account_id: '',
    opening_balance: 0,
})

const closingForm = ref({
    closing_balance: 0,
    closing_note: '',
})

const openingProcessing = ref(false)
const closingProcessing = ref(false)

/*
|--------------------------------------------------------------------------
| Session State
|--------------------------------------------------------------------------
*/

const hasActiveSession = computed(() => {
    return !!props.activeSession
})

const sessionStatus = computed(() => {
    return hasActiveSession.value
        ? 'Open'
        : 'Not Open'
})

const formattedOpeningBalance = computed(() => {
    return formatCurrency(
        props.activeSession?.opening_balance ?? 0
    )
})

/*
|--------------------------------------------------------------------------
| Today Statistics
|--------------------------------------------------------------------------
|
| Sales module belum dibuat.
| Jangan membuat dummy transaction/statistics.
| Untuk sementara angka dimulai dari 0.
|
*/

const todayStats = computed(() => [
    {
        key: 'sales',
        label: 'Sales',
        value: 0,
        format: 'number',
        icon: 'sales',
        accent: 'blue',
    },
    {
        key: 'cash',
        label: 'Cash',
        value: 0,
        format: 'currency',
        icon: 'cash',
        accent: 'green',
    },
    {
        key: 'payments',
        label: 'Payments',
        value: 0,
        format: 'currency',
        icon: 'payment',
        accent: 'indigo',
    },
    {
        key: 'returns',
        label: 'Returns',
        value: 0,
        format: 'number',
        icon: 'return',
        accent: 'orange',
    },
])

/*
|--------------------------------------------------------------------------
| Recent Transactions
|--------------------------------------------------------------------------
|
| Sengaja kosong sampai modul POS Sales menyediakan data sebenarnya.
|
*/

const recentTransactions = ref([])

/*
|--------------------------------------------------------------------------
| Open Session
|--------------------------------------------------------------------------
*/

const openSession = () => {
    openingForm.value = {
        warehouse_id: props.warehouses?.[0]?.id ?? '',
        cash_account_id: props.cashAccounts?.[0]?.id ?? '',
        opening_balance: 0,
    }

    showOpenSessionModal.value = true
}
/*
|--------------------------------------------------------------------------
| Submit Open Session
|--------------------------------------------------------------------------
*/

const submitOpenSession = () => {
    if (openingProcessing.value) {
        return
    }

    openingProcessing.value = true

    console.log(
        'OPEN SESSION URL:',
        route('pos.cashier-sessions.store')
    )

      router.post(
        route('pos.cashier-sessions.store'),
        {
            warehouse_id: openingForm.value.warehouse_id,
            cash_account_id: openingForm.value.cash_account_id,
            opening_balance: openingForm.value.opening_balance,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                openingProcessing.value = false
            },

            onSuccess: () => {
                showOpenSessionModal.value = false
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Close Session
|--------------------------------------------------------------------------
*/

const openCloseSession = () => {
    if (!props.activeSession) {
        return
    }

    closingForm.value = {
        closing_balance: 0,
        closing_note: '',
    }

    showCloseSessionModal.value = true
}

const closeCloseSessionModal = () => {
    if (closingProcessing.value) {
        return
    }

    showCloseSessionModal.value = false
}

/*
|--------------------------------------------------------------------------
| Submit Close Session
|--------------------------------------------------------------------------
*/

const submitCloseSession = () => {
    if (closingProcessing.value) {
        return
    }

    closingProcessing.value = true

    router.post(
        route('pos.cashier-sessions.close'),
        {
            closing_balance: closingForm.value.closing_balance,
            closing_note: closingForm.value.closing_note || null,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                closingProcessing.value = false
            },

            onSuccess: () => {
                showCloseSessionModal.value = false
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Tab Navigation
|--------------------------------------------------------------------------
*/

const changeTab = (tab) => {
    activeTab.value = tab
}

/*
|--------------------------------------------------------------------------
| Formatters
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(
        Number(value || 0)
    )
}

const formatStatValue = (stat) => {
    if (stat.format === 'currency') {
        return formatCurrency(stat.value)
    }

    return formatNumber(stat.value)
}

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value))
}

/*
|--------------------------------------------------------------------------
| Session Display
|--------------------------------------------------------------------------
*/

const sessionNumber = computed(() => {
    return props.activeSession?.session_number ?? '-'
})

const sessionOpenedAt = computed(() => {
    return formatDateTime(
        props.activeSession?.opened_at
    )
})

/*
|--------------------------------------------------------------------------
| Auto Open Session
|--------------------------------------------------------------------------
|
| Kalau tidak ada active session, modal akan dibuka oleh template
| setelah page selesai mount.
|
*/

if (!props.activeSession) {
    showOpenSessionModal.value = true
}

</script>

<template>
<AppLayout>
    <div class="min-h-screen bg-slate-50">
        <!-- =========================================================
             PAGE HEADER
        ========================================================== -->
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-[1600px] px-6 py-5">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 10.5 12 3l9 7.5"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5.5 9.5V21h13V9.5"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 21v-6h6v6"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h1
                                    class="text-xl font-semibold tracking-tight text-slate-900"
                                >
                                    Cashier Dashboard
                                </h1>

                                <div
                                    class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500"
                                >
                                    <span>
                                        {{ currentBranch?.code ?? '-' }}
                                    </span>

                                    <span class="text-slate-300">•</span>

                                    <span>
                                        {{
                                            currentBranch?.name ??
                                            'No branch selected'
                                        }}
                                    </span>

                                    <span class="text-slate-300">•</span>

                                    <span>
                                        {{
                                            authUser?.name ??
                                            'Cashier'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Status -->
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium"
                        :class="
                            hasActiveSession
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200'
                                : 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200'
                        "
                    >
                        <span
                            class="h-2 w-2 rounded-full"
                            :class="
                                hasActiveSession
                                    ? 'bg-emerald-500'
                                    : 'bg-amber-500'
                            "
                        />

                        {{
                            hasActiveSession
                                ? 'Session Open'
                                : 'Session Not Open'
                        }}
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             MAIN
        ========================================================== -->
        <main class="mx-auto max-w-[1600px] px-6 py-6">
            <!-- =====================================================
                 TABS
            ====================================================== -->
            <div
                class="mb-7 flex overflow-x-auto border-b border-slate-200"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="relative whitespace-nowrap px-4 pb-3 text-sm font-medium transition"
                    :class="
                        activeTab === tab.key
                            ? 'text-blue-600'
                            : 'text-slate-500 hover:text-slate-800'
                    "
                    @click="changeTab(tab.key)"
                >
                    {{ tab.label }}

                    <span
                        v-if="activeTab === tab.key"
                        class="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-blue-600"
                    />
                </button>
            </div>

            <!-- =====================================================
                 SESSION TAB
            ====================================================== -->
            <template v-if="activeTab === 'session'">
                <!-- =================================================
                     SESSION
                ================================================== -->
                <section>
                    <div
                        class="mb-3 flex items-center justify-between"
                    >
                        <div>
                            <h2
                                class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                            >
                                Session
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Manage your current cashier session.
                            </p>
                        </div>
                    </div>

                    <!-- Active Session -->
                    <div
                        v-if="hasActiveSession"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="p-5 sm:p-6">
                            <div
                                class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="9"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                d="M12 7v5l3 2"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <h3
                                                class="text-base font-semibold text-slate-900"
                                            >
                                                Current Session
                                            </h3>

                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700"
                                            >
                                                <span
                                                    class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                                />
                                                OPEN
                                            </span>
                                        </div>

                                        <div
                                            class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm"
                                        >
                                            <span
                                                class="font-medium text-slate-700"
                                            >
                                                {{ sessionNumber }}
                                            </span>

                                            <span
                                                class="text-slate-300"
                                            >
                                                •
                                            </span>

                                            <span
                                                class="text-slate-500"
                                            >
                                                Opened
                                                {{ sessionOpenedAt }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex flex-col items-start gap-3 sm:flex-row sm:items-center"
                                >
                                    <div
                                        class="rounded-xl bg-slate-50 px-4 py-3"
                                    >
                                        <div
                                            class="text-[11px] font-medium uppercase tracking-wide text-slate-500"
                                        >
                                            Opening Balance
                                        </div>

                                        <div
                                            class="mt-0.5 text-lg font-semibold tabular-nums text-slate-900"
                                        >
                                            {{ formattedOpeningBalance }}
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/20"
                                        @click="openCloseSession"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 7h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7Z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                d="M9 12h6"
                                            />
                                        </svg>

                                        Close Session
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Active Session -->
                    <div
                        v-else
                        class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            d="M12 8v4"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            d="M12 16h.01"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3
                                        class="text-base font-semibold text-slate-900"
                                    >
                                        No Active Session
                                    </h3>

                                    <p
                                        class="mt-1 max-w-xl text-sm leading-6 text-slate-500"
                                    >
                                        Open a cashier session before
                                        processing POS transactions.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
                                @click="openSession"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M12 5v14"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        d="M5 12h14"
                                    />
                                </svg>

                                Open Session
                            </button>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     TODAY
                ================================================== -->
                <section class="mt-8">
                    <div class="mb-3">
                        <h2
                            class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                        >
                            Today
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Overview of today's cashier activity.
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            v-for="stat in todayStats"
                            :key="stat.key"
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium text-slate-500"
                                    >
                                        {{ stat.label }}
                                    </p>

                                    <p
                                        class="mt-2 text-xl font-semibold tabular-nums tracking-tight text-slate-900"
                                    >
                                        {{ formatStatValue(stat) }}
                                    </p>
                                </div>

                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg"
                                    :class="{
                                        'bg-blue-50 text-blue-600':
                                            stat.accent === 'blue',

                                        'bg-emerald-50 text-emerald-600':
                                            stat.accent === 'green',

                                        'bg-indigo-50 text-indigo-600':
                                            stat.accent === 'indigo',

                                        'bg-orange-50 text-orange-600':
                                            stat.accent === 'orange',
                                    }"
                                >
                                    <!-- Sales -->
                                    <svg
                                        v-if="stat.icon === 'sales'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4.5 w-4.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4 19V5"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4 19h16"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m7 15 4-4 3 2 5-6"
                                        />
                                    </svg>

                                    <!-- Cash -->
                                    <svg
                                        v-else-if="stat.icon === 'cash'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4.5 w-4.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect
                                            x="3"
                                            y="6"
                                            width="18"
                                            height="12"
                                            rx="2"
                                        />
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="2.5"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            d="M7 10h.01M17 14h.01"
                                        />
                                    </svg>

                                    <!-- Payment -->
                                    <svg
                                        v-else-if="stat.icon === 'payment'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4.5 w-4.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            d="M3 10h18"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            d="M7 15h3"
                                        />
                                    </svg>

                                    <!-- Return -->
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4.5 w-4.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 7H5v4"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 11a7 7 0 1 0 2-5"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     RECENT TRANSACTIONS
                ================================================== -->
                <section class="mt-8">
                    <div
                        class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                            >
                                Recent Transactions
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Latest posted POS transactions from this
                                cashier session.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="text-xs font-semibold text-blue-600 transition hover:text-blue-700"
                            @click="changeTab('sales')"
                        >
                            View all Sales
                        </button>
                    </div>

                    <!-- Empty -->
                    <div
                        v-if="recentTransactions.length === 0"
                        class="flex min-h-[180px] items-center justify-center rounded-2xl bg-white px-6 py-10 text-center"
                    >
                        <div>
                            <div
                                class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        d="M8 8h8M8 12h8M8 16h5"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-3 text-sm font-medium text-slate-700"
                            >
                                No transactions yet
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                Posted POS sales will appear here.
                            </p>
                        </div>
                    </div>

                    <!-- Borderless transaction list -->
                    <div
                        v-else
                        class="overflow-hidden rounded-2xl bg-white"
                    >
                        <!-- Header -->
                        <div
                            class="grid grid-cols-[minmax(180px,1.5fr)_100px_minmax(130px,1fr)_120px_110px] gap-4 px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                        >
                            <div>Transaction</div>
                            <div>Time</div>
                            <div class="text-right">Amount</div>
                            <div>Payment</div>
                            <div>Status</div>
                        </div>

                        <button
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            type="button"
                            class="group grid w-full grid-cols-[minmax(180px,1.5fr)_100px_minmax(130px,1fr)_120px_110px] gap-4 px-4 py-3.5 text-left transition hover:bg-blue-50/70"
                        >
                            <div
                                class="min-w-0"
                            >
                                <div
                                    class="truncate text-sm font-semibold text-slate-800 group-hover:text-blue-700"
                                >
                                    {{ transaction.number }}
                                </div>

                                <div
                                    class="mt-0.5 truncate text-xs text-slate-400"
                                >
                                    {{ transaction.customer ?? 'Walk-in Customer' }}
                                </div>
                            </div>

                            <div
                                class="self-center text-xs tabular-nums text-slate-500"
                            >
                                {{ transaction.time }}
                            </div>

                            <div
                                class="self-center text-right text-sm font-semibold tabular-nums text-slate-800"
                            >
                                {{ formatCurrency(transaction.amount) }}
                            </div>

                            <div
                                class="self-center text-xs font-medium text-slate-600"
                            >
                                {{ transaction.payment_method }}
                            </div>

                            <div
                                class="self-center"
                            >
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                    />
                                    {{ transaction.status }}
                                </span>
                            </div>
                        </button>
                    </div>
                </section>
            </template>

            <!-- =====================================================
                 OTHER TABS
            ====================================================== -->
            <template v-else>
                <div
                    class="flex min-h-[360px] items-center justify-center rounded-2xl bg-white"
                >
                    <div class="max-w-md px-6 text-center">
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 5h16v14H4z"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M8 9h8M8 13h5"
                                />
                            </svg>
                        </div>

                        <h3
                            class="mt-4 text-sm font-semibold text-slate-800"
                        >
                            {{ tabs.find((tab) => tab.key === activeTab)?.label }}
                        </h3>

                        <p
                            class="mt-1 text-sm leading-6 text-slate-500"
                        >
                            This section will be connected to the
                            corresponding POS module.
                        </p>
                    </div>
                </div>
            </template>
        </main>
        
    </div>
 </AppLayout>
<OpenSessionModal
    :show="showOpenSessionModal"
    :current-branch="currentBranch"
    :auth-user="authUser"
    :warehouses="props.warehouses"
    :cash-accounts="cashAccounts"
    :form="openingForm"
    :processing="openingProcessing"
    @close="closeOpenSessionModal"
    @submit="submitOpenSession"
/>

<CloseSessionModal
    :show="showCloseSessionModal"
    :session-number="sessionNumber"
    :form="closingForm"
    :processing="closingProcessing"
    @close="closeCloseSessionModal"
    @submit="submitCloseSession"
/>
</template>