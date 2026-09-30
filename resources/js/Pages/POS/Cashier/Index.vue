<script setup>
import { computed, ref} from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import OpenSessionModal from './OpenSessionModal.vue'
import CloseSessionModal from './CloseSessionModal.vue'
import CloseSessionSuccessModal from './CloseSessionSuccessModal.vue'
import SessionReport from './SessionReport.vue'

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

    closeSummary: {
    type: Object,
    default: null,
    },

    openingContext: {
        type: Object,
        default: () => ({
            previous_session: null,
            previous_closing_balance: 0,
            posted_deposits: 0,
            carry_forward: 0,
        }),
    },

        sessionReport: {
        type: Object,
        default: () => ({
            filters: {
                date_from: '',
                date_to: '',
            },
            summary: {
                sessions: 0,
                open_sessions: 0,
                closed_sessions: 0,
                total_sales: 0,
                cash_sales: 0,
            },
            rows: [],
        }),
    },
    dashboardSummary: {
        type: Object,
        default: () => ({
            today: {
                sales: 0,
                cash: 0,
                payments: 0,
                returns: 0,
            },
            recent_transactions: [],
        }),
    },

    dashboardAnalytics: {
    type: Object,
    default: () => ({
        sales_by_hour: [],
        top_products: [],
    }),
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

const showCloseSuccessModal = ref(false)

//const closedSessionId = ref(null)

const closedSessionNumber = ref('-')
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
const closedSessionId = ref(null)
/*
|--------------------------------------------------------------------------
| Close Open Session Modal
|--------------------------------------------------------------------------
*/

const closeOpenSessionModal = () => {
    if (openingProcessing.value) {
        return
    }

    showOpenSessionModal.value = false
}

const submitOpenSession = () => {
    if (openingProcessing.value) {
        return
    }

    if (!openingForm.value.warehouse_id) {
        return
    }

    openingProcessing.value = true

    router.post(
        route('pos.cashier-sessions.store'),
        {
            warehouse_id:
                openingForm.value.warehouse_id,

            opening_balance:
                openingForm.value.opening_balance,
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
| Session State
|--------------------------------------------------------------------------
*/

const hasActiveSession = computed(() => {
    return !!props.activeSession
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
        value:
            props.dashboardSummary?.today?.sales ?? 0,
        format: 'number',
        icon: 'sales',
        accent: 'blue',
    },

    {
        key: 'cash',
        label: 'Cash',
        value:
            props.dashboardSummary?.today?.cash ?? 0,
        format: 'currency',
        icon: 'cash',
        accent: 'green',
    },

    {
        key: 'payments',
        label: 'Payments',
        value:
            props.dashboardSummary?.today?.payments ?? 0,
        format: 'currency',
        icon: 'payment',
        accent: 'indigo',
    },

    {
        key: 'returns',
        label: 'Returns',
        value:
            props.dashboardSummary?.today?.returns ?? 0,
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

const recentTransactions = computed(() =>
    props.dashboardSummary?.recent_transactions ?? []
)

const salesByHour = computed(() =>
    props.dashboardAnalytics?.sales_by_hour ?? []
)

const topProducts = computed(() =>
    props.dashboardAnalytics?.top_products ?? []
)
const formatQuantity = (value) =>
    new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(Number(value || 0))
/*
|--------------------------------------------------------------------------
| Open Session
|--------------------------------------------------------------------------
*/

const openSession = () => {
    if (openingProcessing.value) {
        return
    }

    openingForm.value = {
        warehouse_id:
            props.warehouses?.[0]?.id ?? '',

        cash_account_id:
            props.cashAccounts?.[0]?.id ?? '',

        opening_balance:
            props.openingContext?.carry_forward ?? 0,
    }

    showOpenSessionModal.value = true
}

const submitCloseSession = () => {
    if (closingProcessing.value) {
        return
    }

    if (!props.activeSession?.id) {
        return
    }

    /*
    |--------------------------------------------------------------------------
    | Keep Closed Session
    |--------------------------------------------------------------------------
    */

    closedSessionId.value =
        props.activeSession.id

    closedSessionNumber.value =
        props.activeSession.session_number

    closingProcessing.value = true

    router.post(
        route('pos.cashier-sessions.close'),
        {
            closing_balance:
                closingForm.value.closing_balance,

            closing_note:
                closingForm.value.closing_note || null,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                closingProcessing.value = false
            },

            onSuccess: () => {
                showCloseSessionModal.value = false

                showCloseSuccessModal.value = true
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
const formatChartCurrency = (value) => {
    const amount = Number(value || 0)

    if (amount >= 1_000_000) {
        return `Rp ${(amount / 1_000_000).toFixed(1)}jt`
    }

    if (amount >= 1_000) {
        return `Rp ${(amount / 1_000).toFixed(0)}rb`
    }

    return `Rp ${amount.toLocaleString('id-ID')}`
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

const printClosedSession = () => {
    if (!closedSessionId.value){
    return
    }
    window.open(
    route(
        'pos.cashier-sessions.print',
        closedSessionId.value
    ),
    '_blank'
    )
}

const salesChartPointData = computed(() => {
    const data = salesByHour.value

    if (!data.length) {
        return []
    }

    const values = data.map(
        item => Number(item.sales || 0)
    )

    const maxValue = Math.max(
        ...values,
        1
    )

    const chartWidth = 930
    const chartHeight = 220
    const startX = 50
    const startY = 30

    const step =
        data.length > 1
            ? chartWidth / (data.length - 1)
            : 0

    return data.map((item, index) => {
        const x =
            startX + (step * index)

        const y =
            startY +
            chartHeight -
            (
                Number(item.sales || 0)
                / maxValue
            ) * chartHeight

        return {
            hour: item.hour,
            sales: Number(item.sales || 0),
            x,
            y,
        }
    })
})

const salesChartPoints = computed(() =>
    salesChartPointData.value
        .map(point =>
            `${point.x},${point.y}`
        )
        .join(' ')
)
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
                        <div
                            class="mb-4 flex items-end justify-between gap-4"
                        >
                            <div>
                                <h2
                                    class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                                >
                                    Today
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    Overview of today's cashier activity.
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                        >
                            <div
                                v-for="stat in todayStats"
                                :key="stat.key"
                                class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="text-xs font-medium text-slate-500"
                                        >
                                            {{ stat.label }}
                                        </p>

                                        <p
                                            class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900"
                                        >
                                            {{ formatStatValue(stat) }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition"
                                        :class="{
                                            'bg-blue-50 text-blue-600 group-hover:bg-blue-100':
                                                stat.accent === 'blue',

                                            'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100':
                                                stat.accent === 'green',

                                            'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100':
                                                stat.accent === 'indigo',

                                            'bg-orange-50 text-orange-600 group-hover:bg-orange-100':
                                                stat.accent === 'orange',
                                        }"
                                    >
                                        <!-- Sales -->
                                        <svg
                                            v-if="stat.icon === 'sales'"
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
                                            class="h-5 w-5"
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

                                        <!-- Payments -->
                                        <svg
                                            v-else-if="stat.icon === 'payment'"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
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

                                        <!-- Returns -->
                                        <svg
                                            v-else
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
                     SALES CHART
                ================================================== -->
                <section class="mt-8">
                    <div class="mb-4">
                        <h2
                            class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                        >
                            Sales Today
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Hourly sales performance for the current cashier session.
                        </p>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="p-5 sm:p-6">
                            <div
                                v-if="salesByHour.length === 0"
                                class="flex min-h-[280px] items-center justify-center"
                            >
                                <div class="text-center">
                                    <div
                                        class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400"
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
                                    </div>

                                    <p
                                        class="mt-3 text-sm font-semibold text-slate-700"
                                    >
                                        No sales yet
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        Posted sales will appear on the chart.
                                    </p>
                                </div>
                            </div>

                            <div
                                v-else
                                class="h-[300px] w-full"
                            >
                                <svg
                                    viewBox="0 0 1000 300"
                                    preserveAspectRatio="none"
                                    class="h-full w-full"
                                >
                                    <!-- Grid -->
                                    <line
                                        v-for="line in 5"
                                        :key="`grid-${line}`"
                                        :x1="50"
                                        :x2="980"
                                        :y1="30 + ((line - 1) * 55)"
                                        :y2="30 + ((line - 1) * 55)"
                                        stroke="currentColor"
                                        class="text-slate-100"
                                        stroke-width="1"
                                    />

                                    <!-- Sales Line -->
                                    <polyline
                                        v-if="salesChartPoints.length > 1"
                                        :points="salesChartPoints"
                                        fill="none"
                                        stroke="currentColor"
                                        class="text-blue-600"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                    <!-- Points -->
                                   <!-- Points + Sales Value -->
                                    <g
                                        v-for="point in salesChartPointData"
                                        :key="point.hour"
                                    >
                                        <circle
                                            :cx="point.x"
                                            :cy="point.y"
                                            r="4"
                                            class="fill-white stroke-blue-600"
                                            stroke-width="2.5"
                                        />

                                        <text
                                            v-if="point.sales > 0"
                                            :x="point.x"
                                            :y="point.y - 12"
                                            text-anchor="middle"
                                            class="fill-slate-600 text-[10px] font-semibold"
                                        >
                                            {{ formatChartCurrency(point.sales) }}
                                        </text>
                                    </g>
                                    <!-- X Axis -->
                                    <line
                                        x1="50"
                                        x2="980"
                                        y1="250"
                                        y2="250"
                                        stroke="currentColor"
                                        class="text-slate-200"
                                        stroke-width="1"
                                    />

                                    <!-- X Labels -->
                                    <text
                                        v-for="point in salesChartPointData.filter(
                                            (_, index) =>
                                                index % 2 === 0
                                        )"
                                        :key="`label-${point.hour}`"
                                        :x="point.x"
                                        y="275"
                                        text-anchor="middle"
                                        class="fill-slate-400 text-[11px]"
                                    >
                                        {{ point.hour }}
                                    </text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </section>
               <!-- =================================================
                     RECENT TRANSACTIONS
                ================================================= -->
                <section class="mt-8">
                    <div
                        class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                            >
                                Recent Transactions
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Latest posted POS transactions from this cashier session.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 transition hover:text-blue-700"
                            @click="changeTab('sales')"
                        >
                            View all Sales

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Empty -->
                    <div
                        v-if="recentTransactions.length === 0"
                        class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm"
                    >
                        <div
                            class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400"
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
                            class="mt-3 text-sm font-semibold text-slate-700"
                        >
                            No transactions yet
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Posted POS sales will appear here.
                        </p>
                    </div>

                    <!-- Transactions -->
                    <div
                        v-else
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <!-- Desktop Header -->
                        <div
                            class="hidden grid-cols-[minmax(200px,1.5fr)_110px_minmax(130px,1fr)_120px_110px] gap-4 border-b border-slate-100 px-5 py-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400 md:grid"
                        >
                            <div>Transaction</div>

                            <div>Time</div>

                            <div class="text-right">
                                Amount
                            </div>

                            <div>Payment</div>

                            <div>Status</div>
                        </div>

                        <div>
                            <button
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                type="button"
                                class="group w-full border-b border-slate-100 px-5 py-4 text-left transition last:border-b-0 hover:bg-blue-50/70"
                            >
                                <!-- Desktop -->
                                <div
                                    class="hidden grid-cols-[minmax(200px,1.5fr)_110px_minmax(130px,1fr)_120px_110px] items-center gap-4 md:grid"
                                >
                                    <!-- Transaction -->
                                    <div class="min-w-0">
                                        <div
                                            class="truncate text-sm font-semibold text-slate-800 transition group-hover:text-blue-700"
                                        >
                                            {{ transaction.number }}
                                        </div>

                                        <div
                                            class="mt-0.5 truncate text-xs text-slate-400"
                                        >
                                            {{
                                                transaction.customer
                                                    ?? 'Walk-in Customer'
                                            }}
                                        </div>
                                    </div>

                                    <!-- Time -->
                                    <div
                                        class="text-xs tabular-nums text-slate-500"
                                    >
                                        {{ transaction.time }}
                                    </div>

                                    <!-- Amount -->
                                    <div
                                        class="text-right text-sm font-semibold tabular-nums text-slate-800"
                                    >
                                        {{ formatCurrency(transaction.amount) }}
                                    </div>

                                    <!-- Payment -->
                                    <div
                                        class="text-xs font-medium text-slate-600"
                                    >
                                        {{ transaction.payment_method }}
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                            />

                                            {{ transaction.status }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Mobile -->
                                <div class="md:hidden">
                                    <div
                                        class="flex items-start justify-between gap-4"
                                    >
                                        <div class="min-w-0">
                                            <div
                                                class="truncate text-sm font-semibold text-slate-800 group-hover:text-blue-700"
                                            >
                                                {{ transaction.number }}
                                            </div>

                                            <div
                                                class="mt-0.5 truncate text-xs text-slate-400"
                                            >
                                                {{
                                                    transaction.customer
                                                        ?? 'Walk-in Customer'
                                                }}
                                            </div>
                                        </div>

                                        <div
                                            class="shrink-0 text-right"
                                        >
                                            <div
                                                class="text-sm font-semibold tabular-nums text-slate-800"
                                            >
                                                {{
                                                    formatCurrency(
                                                        transaction.amount
                                                    )
                                                }}
                                            </div>

                                            <div
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                {{ transaction.time }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-3 flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="text-xs font-medium text-slate-600"
                                        >
                                            {{ transaction.payment_method }}
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                            />

                                            {{ transaction.status }}
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- =================================================
     TOP PRODUCTS
================================================== -->
<section class="mt-8">
    <div class="mb-4">
        <h2
            class="text-sm font-semibold uppercase tracking-wide text-slate-700"
        >
            Top Products
        </h2>

        <p class="mt-0.5 text-xs text-slate-500">
            Best-selling products from today's cashier session.
        </p>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <!-- Empty -->
        <div
            v-if="topProducts.length === 0"
            class="flex min-h-[180px] items-center justify-center px-6 py-10 text-center"
        >
            <div>
                <div
                    class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400"
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
                            d="M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"
                        />

                        <path
                            stroke-linecap="round"
                            d="M8 8h8M8 12h5"
                        />
                    </svg>
                </div>

                <p
                    class="mt-3 text-sm font-semibold text-slate-700"
                >
                    No product sales yet
                </p>

                <p
                    class="mt-1 text-xs text-slate-500"
                >
                    Products will appear after posted sales.
                </p>
            </div>
        </div>

        <!-- Products -->
        <div v-else>
            <!-- Header -->
            <div
                class="grid grid-cols-[minmax(0,1fr)_100px_140px] gap-4 border-b border-slate-100 px-5 py-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400 sm:px-6"
            >
                <div>Product</div>

                <div class="text-right">
                    Qty
                </div>

                <div class="text-right">
                    Sales
                </div>
            </div>

            <!-- Rows -->
            <div>
                <div
                    v-for="(product, index) in topProducts"
                    :key="product.variant_id"
                    class="grid grid-cols-[minmax(0,1fr)_100px_140px] items-center gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0 sm:px-6"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500"
                        >
                            {{ index + 1 }}
                        </div>

                        <div class="min-w-0">
                            <div
                                class="truncate text-sm font-semibold text-slate-800"
                            >
                                {{ product.product_name }}
                            </div>

                            <div
                                class="mt-0.5 truncate text-xs text-slate-400"
                            >
                                {{ product.variant_name }}

                                <span
                                    v-if="product.sku"
                                >
                                    · {{ product.sku }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="text-right text-sm font-medium tabular-nums text-slate-600"
                    >
                        {{ formatQuantity(product.qty) }}
                    </div>

                    <div
                        class="text-right text-sm font-semibold tabular-nums text-slate-900"
                    >
                        {{ formatCurrency(product.sales) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
            </template>

            <!-- =====================================================
                 REPORTS TAB
            ====================================================== -->
            <template v-else-if="activeTab === 'reports'">
                <SessionReport
                    :session-report="props.sessionReport"
                />
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
                            {{
                                tabs.find(
                                    (tab) =>
                                        tab.key === activeTab
                                )?.label
                            }}
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
        :opening-balance="props.activeSession?.opening_balance ?? 0"
        :summary="props.closeSummary"
        :form="closingForm"
        :active-session="props.activeSession"
        :processing="closingProcessing"
        @close="closeCloseSessionModal"
        @submit="submitCloseSession"
    />

    <CloseSessionSuccessModal
        :show="showCloseSuccessModal"
        :session-number="closedSessionNumber"
        :processing="false"
        @close="showCloseSuccessModal = false"
        @print="printClosedSession"
    />
</template>