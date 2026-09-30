<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'
import { router } from '@inertiajs/vue3'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
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
})

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const showDateRangePicker = ref(false)
const dateRangePickerRef = ref(null)
const isMobile = ref(false)

const updateIsMobile = () => {
    isMobile.value = window.innerWidth < 640
}

const handleClickOutsideDateRange = (event) => {
    if (!showDateRangePicker.value) {
        return
    }

    if (
        dateRangePickerRef.value &&
        !dateRangePickerRef.value.contains(event.target)
    ) {
        showDateRangePicker.value = false
    }
}

onMounted(() => {
    updateIsMobile()

    window.addEventListener(
        'resize',
        updateIsMobile
    )

    document.addEventListener(
        'mousedown',
        handleClickOutsideDateRange
    )
})

onBeforeUnmount(() => {
    window.removeEventListener(
        'resize',
        updateIsMobile
    )

    document.removeEventListener(
        'mousedown',
        handleClickOutsideDateRange
    )
})

const selectedPreset = ref('this-month')

const reportDateFrom = ref(
    props.sessionReport?.filters?.date_from ?? ''
)

const reportDateTo = ref(
    props.sessionReport?.filters?.date_to ?? ''
)

const calendarRange = ref([])

const reportLoading = ref(false)

/*
|--------------------------------------------------------------------------
| Presets
|--------------------------------------------------------------------------
*/

const presets = [
    {
        key: 'today',
        label: 'Today',
    },
    {
        key: 'yesterday',
        label: 'Yesterday',
    },
    {
        key: 'last-7-days',
        label: 'Last 7 Days',
    },
    {
        key: 'last-30-days',
        label: 'Last 30 Days',
    },
    {
        key: 'this-month',
        label: 'This Month',
    },
    {
        key: 'last-month',
        label: 'Last Month',
    },
    {
        key: 'custom',
        label: 'Custom Range',
    },
]

/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

const parseDate = (value) => {
    if (!value) {
        return null
    }

    const [year, month, day] =
        value.split('-').map(Number)

    return new Date(
        year,
        month - 1,
        day
    )
}

const toDateString = (date) => {
    if (!date) {
        return ''
    }

    const year = date.getFullYear()

    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0')

    const day = String(
        date.getDate()
    ).padStart(2, '0')

    return `${year}-${month}-${day}`
}

const startOfDay = (date) => {
    const result = new Date(date)

    result.setHours(0, 0, 0, 0)

    return result
}

/*
|--------------------------------------------------------------------------
| Display
|--------------------------------------------------------------------------
*/

const formatReportDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = parseDate(value)

    if (!date) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date)
}

const reportDateRangeLabel = computed(() => {
    if (
        !reportDateFrom.value ||
        !reportDateTo.value
    ) {
        return 'Select Date Range'
    }

    return `${formatReportDate(reportDateFrom.value)} - ${formatReportDate(reportDateTo.value)}`
})

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

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(new Date(value))
}

/*
|--------------------------------------------------------------------------
| Report Data
|--------------------------------------------------------------------------
*/

const sessionReportSummary = computed(() => {
    return props.sessionReport?.summary ?? {
        sessions: 0,
        open_sessions: 0,
        closed_sessions: 0,
        total_sales: 0,
        cash_sales: 0,
    }
})

const sessionReportRows = computed(() => {
    return props.sessionReport?.rows ?? []
})

/*
|--------------------------------------------------------------------------
| Load Report
|--------------------------------------------------------------------------
*/

const loadSessionReport = (
    dateFrom,
    dateTo
) => {
    reportLoading.value = true

    router.get(
        window.location.pathname,
        {
            date_from: dateFrom,
            date_to: dateTo,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            only: [
                'sessionReport',
            ],

            onFinish: () => {
                reportLoading.value = false
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Apply Range
|--------------------------------------------------------------------------
*/

const applyReportRange = (
    dateFrom,
    dateTo,
    preset = null
) => {
    reportDateFrom.value = dateFrom
    reportDateTo.value = dateTo

    calendarRange.value = [
        parseDate(dateFrom),
        parseDate(dateTo),
    ]

    if (preset) {
        selectedPreset.value = preset
    }

    showDateRangePicker.value = false

    loadSessionReport(
        dateFrom,
        dateTo
    )
}

/*
|--------------------------------------------------------------------------
| Apply Preset
|--------------------------------------------------------------------------
*/

const applyReportPreset = (preset) => {
    const today = startOfDay(
        new Date()
    )

    let dateFrom
    let dateTo

    switch (preset) {
        case 'today':
            dateFrom = today
            dateTo = today
            break

        case 'yesterday': {
            const yesterday =
                new Date(today)

            yesterday.setDate(
                yesterday.getDate() - 1
            )

            dateFrom = yesterday
            dateTo = yesterday

            break
        }

        case 'last-7-days': {
            const from =
                new Date(today)

            from.setDate(
                from.getDate() - 6
            )

            dateFrom = from
            dateTo = today

            break
        }

        case 'last-30-days': {
            const from =
                new Date(today)

            from.setDate(
                from.getDate() - 29
            )

            dateFrom = from
            dateTo = today

            break
        }

        case 'this-month':
            dateFrom = new Date(
                today.getFullYear(),
                today.getMonth(),
                1
            )

            dateTo = today

            break

        case 'last-month':
            dateFrom = new Date(
                today.getFullYear(),
                today.getMonth() - 1,
                1
            )

            dateTo = new Date(
                today.getFullYear(),
                today.getMonth(),
                0
            )

            break

        case 'custom':
            selectedPreset.value = 'custom'
            return

        default:
            return
    }

    applyReportRange(
        toDateString(dateFrom),
        toDateString(dateTo),
        preset
    )
}

/*
|--------------------------------------------------------------------------
| Calendar
|--------------------------------------------------------------------------
*/

const handleCalendarChange = (
    selectedDates
) => {
    if (selectedDates.length !== 2) {
        return
    }

    const dateFrom =
        toDateString(selectedDates[0])

    const dateTo =
        toDateString(selectedDates[1])

    applyReportRange(
        dateFrom,
        dateTo,
        'custom'
    )
}

/*
|--------------------------------------------------------------------------
| Sync Inertia Props
|--------------------------------------------------------------------------
*/

watch(
    () => props.sessionReport?.filters,
    (filters) => {
        if (!filters) {
            return
        }

        reportDateFrom.value =
            filters.date_from ?? ''

        reportDateTo.value =
            filters.date_to ?? ''

        calendarRange.value = [
            parseDate(
                filters.date_from
            ),
            parseDate(
                filters.date_to
            ),
        ]
    },
    {
        immediate: true,
    }
)
</script>

<template>
    <section>

        <!-- =====================================================
             REPORT HEADER
        ====================================================== -->
        <div
            class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <h2
                    class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                >
                    Session Report
                </h2>

                <p class="mt-0.5 text-xs text-slate-500">
                    Review your cashier sessions and sales activity.
                </p>
            </div>

            <!-- DATE RANGE -->
            <div
                ref="dateRangePickerRef"
                class="relative"
            >
                <button
                    type="button"
                    class="inline-flex min-w-[270px] items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600/20"
                    @click="
                        showDateRangePicker =
                            !showDateRangePicker
                    "
                >
                    <div
                        class="flex items-center gap-2.5"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-slate-500"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="17"
                                rx="2"
                            />

                            <path
                                stroke-linecap="round"
                                d="M16 2v4M8 2v4M3 10h18"
                            />
                        </svg>

                        <span
                            class="text-sm font-medium text-slate-700"
                        >
                            {{ reportDateRangeLabel }}
                        </span>
                    </div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m6 9 6 6 6-6"
                        />
                    </svg>
                </button>

                <!-- =================================================
                     DATE RANGE POPUP
                ================================================== -->
                <div
                    v-if="showDateRangePicker"
                    class="absolute right-0 z-50 mt-2 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                >
                    <div
                        class="flex flex-col sm:flex-row"
                    >

                        <!-- PRESETS -->
                        <div
                            class="w-full shrink-0 border-b border-slate-200 py-2 sm:w-[170px] sm:border-b-0 sm:border-r"
                        >
                            <button
                                v-for="preset in presets"
                                :key="preset.key"
                                type="button"
                                class="block w-full px-4 py-2.5 text-left text-sm transition"
                                :class="
                                    selectedPreset === preset.key
                                        ? 'bg-blue-600 font-medium text-white'
                                        : 'text-slate-600 hover:bg-slate-50'
                                "
                                @click="
                                    applyReportPreset(
                                        preset.key
                                    )
                                "
                            >
                                {{ preset.label }}
                            </button>
                        </div>

                        <!-- CALENDAR -->
                        <div
                            class="max-w-full overflow-x-auto p-4"
                        >
                            <FlatPickr
                                v-model="calendarRange"
                                :config="{
                                    mode: 'range',
                                    inline: true,
                                    showMonths: isMobile ? 1 : 2,
                                    dateFormat: 'Y-m-d',
                                    allowInput: false,
                                    clickOpens: true,
                                    onChange: handleCalendarChange,
                                }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =====================================================
             SUMMARY
        ====================================================== -->
        <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5"
        >

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-medium text-slate-500"
                >
                    Sessions
                </p>

                <p
                    class="mt-2 text-xl font-semibold tabular-nums text-slate-900"
                >
                    {{
                        formatNumber(
                            sessionReportSummary.sessions
                        )
                    }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-medium text-slate-500"
                >
                    Open Sessions
                </p>

                <p
                    class="mt-2 text-xl font-semibold tabular-nums text-emerald-600"
                >
                    {{
                        formatNumber(
                            sessionReportSummary.open_sessions
                        )
                    }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-medium text-slate-500"
                >
                    Closed Sessions
                </p>

                <p
                    class="mt-2 text-xl font-semibold tabular-nums text-slate-900"
                >
                    {{
                        formatNumber(
                            sessionReportSummary.closed_sessions
                        )
                    }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-medium text-slate-500"
                >
                    Total Sales
                </p>

                <p
                    class="mt-2 text-xl font-semibold tabular-nums text-blue-600"
                >
                    {{
                        formatCurrency(
                            sessionReportSummary.total_sales
                        )
                    }}
                </p>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <p
                    class="text-xs font-medium text-slate-500"
                >
                    Cash Sales
                </p>

                <p
                    class="mt-2 text-xl font-semibold tabular-nums text-emerald-600"
                >
                    {{
                        formatCurrency(
                            sessionReportSummary.cash_sales
                        )
                    }}
                </p>
            </div>

        </div>

        <!-- =====================================================
             SESSION TABLE
        ====================================================== -->
        <div
            class="mt-6 overflow-hidden rounded-2xl bg-white"
        >

            <div
                class="border-b border-slate-100 px-5 py-4"
            >
                <h3
                    class="text-sm font-semibold text-slate-800"
                >
                    Sessions
                </h3>

                <p
                    class="mt-0.5 text-xs text-slate-500"
                >
                    Sessions opened within the selected date range.
                </p>
            </div>

            <!-- EMPTY -->
            <div
                v-if="sessionReportRows.length === 0"
                class="flex min-h-[260px] items-center justify-center px-6 py-10 text-center"
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
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="16"
                                rx="2"
                            />

                            <path
                                stroke-linecap="round"
                                d="M7 9h10M7 13h6"
                            />
                        </svg>
                    </div>

                    <p
                        class="mt-3 text-sm font-medium text-slate-700"
                    >
                        No sessions found
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-500"
                    >
                        No cashier sessions were found for this date range.
                    </p>
                </div>
            </div>

            <!-- TABLE -->
            <div
                v-else
                class="overflow-x-auto"
            >
                <table class="min-w-[1100px] w-full">

                    <thead>
                        <tr
                            class="border-b border-slate-100 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                        >
                            <th class="px-5 py-3">
                                Session
                            </th>

                            <th class="px-5 py-3">
                                Opened
                            </th>

                            <th class="px-5 py-3">
                                Closed
                            </th>

                            <th
                                class="px-5 py-3 text-right"
                            >
                                Opening
                            </th>

                            <th
                                class="px-5 py-3 text-right"
                            >
                                Total Sales
                            </th>

                            <th
                                class="px-5 py-3 text-right"
                            >
                                Cash Sales
                            </th>

                            <th
                                class="px-5 py-3 text-right"
                            >
                                Closing
                            </th>

                            <th class="px-5 py-3">
                                Status
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="session in sessionReportRows"
                            :key="session.id"
                            class="border-b border-slate-50 transition hover:bg-blue-50/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="text-sm font-semibold text-slate-800"
                                >
                                    {{ session.session_number }}
                                </div>

                                <div
                                    class="mt-0.5 text-xs text-slate-400"
                                >
                                    {{
                                        session.warehouse?.name ??
                                        '-'
                                    }}
                                </div>
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-xs text-slate-600"
                            >
                                {{
                                    formatDateTime(
                                        session.opened_at
                                    )
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-xs text-slate-600"
                            >
                                {{
                                    session.closed_at
                                        ? formatDateTime(
                                            session.closed_at
                                        )
                                        : '-'
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium tabular-nums text-slate-700"
                            >
                                {{
                                    formatCurrency(
                                        session.opening_balance
                                    )
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold tabular-nums text-slate-800"
                            >
                                {{
                                    formatCurrency(
                                        session.total_sales
                                    )
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold tabular-nums text-emerald-600"
                            >
                                {{
                                    formatCurrency(
                                        session.cash_sales
                                    )
                                }}
                            </td>

                            <td
                                class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium tabular-nums text-slate-700"
                            >
                                {{
                                    session.closing_balance !==
                                    null
                                        ? formatCurrency(
                                            session.closing_balance
                                        )
                                        : '-'
                                }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="
                                        session.status === 'open'
                                            ? 'text-emerald-600'
                                            : 'text-slate-500'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="
                                            session.status === 'open'
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-400'
                                        "
                                    />

                                    {{
                                        session.status === 'open'
                                            ? 'Open'
                                            : 'Closed'
                                    }}
                                </span>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

        </div>

    </section>
</template>