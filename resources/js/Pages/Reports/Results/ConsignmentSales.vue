<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ReportActionButtons from '@/Components/Action/ReportActionButtons.vue'

const props = defineProps({
    report: {
        type: Object,
        required: true,
    },
    branches: {
        type: Array,
        default: () => [],
    },
    company: {
        type: Object,
        default: () => ({}),
    },
    generatedAt: {
        type: String,
        default: '',
    },
    branchLabel: {
        type: String,
        default: 'All Branches',
    },
})

/*
|--------------------------------------------------------------------------
| Report Data
|--------------------------------------------------------------------------
*/

const summary = computed(() => props.report?.summary ?? {})
const totals = computed(() => props.report?.totals ?? {})

const resellers = computed(() => {
    const data = props.report?.resellers

    if (Array.isArray(data)) {
        return data
    }

    return data?.data ?? []
})

const pagination = computed(() => {
    const data = props.report?.resellers

    return data?.links ? data : {}
})

const chartData = computed(() => props.report?.chart ?? [])

const filters = computed(() => props.report?.filters ?? {})

/*
|--------------------------------------------------------------------------
| Comparison
|--------------------------------------------------------------------------
*/

const comparison = computed(() => props.report?.comparison ?? {})

const comparisonMetrics = computed(() => comparison.value.metrics ?? {})

const comparisonPeriod = computed(() => {
    return comparison.value.period_label ?? 'Previous Period'
})

const getComparison = (key) => {
    return comparisonMetrics.value[key] ?? {
        percentage: null,
        direction: 'unchanged',
        status: 'no_data',
    }
}

const comparisonText = (key) => {
    const item = getComparison(key)

    if (item.status === 'no_data') {
        return '—'
    }

    if (item.status === 'new') {
        return 'New'
    }

    if (item.percentage === null || item.percentage === undefined) {
        return '—'
    }

    return `${Math.abs(Number(item.percentage)).toFixed(2)}%`
}

const comparisonArrow = (key) => {
    const item = getComparison(key)

    if (item.status === 'new') {
        return '↑'
    }

    if (item.direction === 'up') return '↑'
    if (item.direction === 'down') return '↓'

    return '→'
}

const comparisonClass = (key) => {
    const item = getComparison(key)

    if (item.status === 'no_data') {
        return 'text-gray-400'
    }

    if (item.status === 'new') {
        return 'text-emerald-600'
    }

    if (item.direction === 'up') {
        return 'text-emerald-600'
    }

    if (item.direction === 'down') {
        return 'text-red-600'
    }

    return 'text-yellow-600'
}

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

const formatPercentage = (value) => {
    return `${Number(value ?? 0).toFixed(2)}%`
}

const formatDate = (value) => {
    if (!value) return '-'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const formatDateTime = (value) => {
    if (!value) return '-'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const profitClass = (value) => {
    return Number(value ?? 0) >= 0
        ? 'text-green-600'
        : 'text-red-600'
}

/*
|--------------------------------------------------------------------------
| Chart
|--------------------------------------------------------------------------
*/

const chartSeries = computed(() => [
    {
        name: 'Gross Sales',
        data: chartData.value.map(item => Number(item.gross_sales ?? 0)),
    },
    {
        name: 'Gross Profit',
        data: chartData.value.map(item => Number(item.gross_profit ?? 0)),
    },
])

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 350,
        toolbar: {
            show: true,
        },
        zoom: {
            enabled: false,
        },
    },

    colors: ['#2563EB', '#10B981'],

    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            borderRadius: 4,
            borderRadiusApplication: 'end',
        },
    },

    dataLabels: {
        enabled: false,
    },

    xaxis: {
        categories: chartData.value.map(item => item.reseller_name),
        labels: {
            rotate: -35,
            trim: true,
            hideOverlappingLabels: true,
        },
    },

    yaxis: {
        labels: {
            formatter: (value) => {
                return new Intl.NumberFormat('id-ID', {
                    notation: 'compact',
                    maximumFractionDigits: 1,
                }).format(value)
            },
        },
    },

    legend: {
        position: 'top',
        horizontalAlign: 'right',
    },

    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: (value) => formatCurrency(value),
        },
    },

    noData: {
        text: 'No consignment sales data available',
        align: 'center',
        verticalAlign: 'middle',
    },

    responsive: [
        {
            breakpoint: 640,
            options: {
                chart: {
                    height: 300,
                },
                plotOptions: {
                    bar: {
                        columnWidth: '60%',
                    },
                },
                xaxis: {
                    labels: {
                        rotate: -45,
                    },
                },
            },
        },
    ],
}))

/*
|--------------------------------------------------------------------------
| Report Information
|--------------------------------------------------------------------------
*/

const companyName = computed(() => {
    return props.company?.company_name ?? '-'
})

const periodLabel = computed(() => {
    if (!filters.value.date_from || !filters.value.date_to) {
        return '-'
    }

    return `${formatDate(filters.value.date_from)} - ${formatDate(filters.value.date_to)}`
})

const resellerLabel = computed(() => {
    return filters.value.reseller_name || 'All Resellers'
})

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const visitPage = (url) => {
    if (!url) return

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

/*
|--------------------------------------------------------------------------
| Report Actions
|--------------------------------------------------------------------------
*/

const printReport = () => {
    window.open(
        route('reports.sales.consignment-sales.print'),
        '_blank'
    )
}

const exportPdf = () => {
    window.open(
        route('reports.sales.consignment-sales.pdf'),
        '_blank'
    )
}

const exportExcel = () => {
    window.location.href = route(
        'reports.sales.consignment-sales.excel'
    )
}
</script>

<template>
    <AppLayout title="Consignment Sales Report">
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                   <div class="flex flex-wrap items-center gap-2 text-sm">

    <button
        type="button"
        @click="router.visit(route('reports.index'))"
        class="text-gray-500 transition hover:text-blue-600"
    >
        Reports
    </button>

    <span class="text-gray-400">/</span>

    <button
        type="button"
        @click="router.visit(route('reports.index', { category: 'Sales' }))"
        class="text-gray-500 transition hover:text-blue-600"
    >
        Sales
    </button>

    <span class="text-gray-400">/</span>

    <button
        type="button"
        @click="router.visit(route('reports.sales.consignment-sales.parameters'))"
        class="text-gray-500 transition hover:text-blue-600"
    >
        Consignment Sales
    </button>

    <span class="text-gray-400">/</span>

    <span class="font-medium text-gray-900">
        Results
    </span>

</div>

                    <h1 class="text-2xl font-bold text-gray-800">
                        Consignment Sales Report
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Analyze consignment sales performance by reseller.
                    </p>
                </div>

                <ReportActionButtons
                    :show-refresh="false"
                    :show-print="true"
                    :show-pdf="true"
                    :show-excel="true"
                    @print="printReport"
                    @pdf="exportPdf"
                    @excel="exportExcel"
                />
            </div>

            <!-- Report Information -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">
                        Report Information
                    </h2>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                        Consignment Sales
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-xs text-gray-500">Company</p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ companyName }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Branch</p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ branchLabel }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Period</p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ periodLabel }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Reseller</p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ resellerLabel }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Generated At</p>
                        <p class="mt-1 text-sm font-medium text-gray-800">
                            {{ formatDateTime(generatedAt) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                <!-- Total Resellers -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Total Resellers
                        </p>

                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a4 4 0 00-5.5-3.7M9 20H2v-2a4 4 0 017-2.65M16 3.5a4 4 0 010 7.5M8 3.5a4 4 0 000 7.5M12 14a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-2xl font-bold text-gray-800">
                        {{ formatNumber(summary.total_resellers) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold" :class="comparisonClass('total_resellers')">
                            {{ comparisonArrow('total_resellers') }}
                            {{ comparisonText('total_resellers') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonPeriod }}
                        </span>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Total Transactions
                        </p>

                        <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-2xl font-bold text-gray-800">
                        {{ formatNumber(summary.total_transactions) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold" :class="comparisonClass('total_transactions')">
                            {{ comparisonArrow('total_transactions') }}
                            {{ comparisonText('total_transactions') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonPeriod }}
                        </span>
                    </div>
                </div>

                <!-- Total Items Sold -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Total Items Sold
                        </p>

                        <div class="rounded-lg bg-purple-50 p-2 text-purple-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-2xl font-bold text-gray-800">
                        {{ formatNumber(summary.total_items) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold" :class="comparisonClass('total_items')">
                            {{ comparisonArrow('total_items') }}
                            {{ comparisonText('total_items') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonPeriod }}
                        </span>
                    </div>
                </div>

                <!-- Gross Sales -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Gross Sales
                        </p>

                        <div class="rounded-lg bg-green-50 p-2 text-green-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-xl font-bold text-gray-800">
                        {{ formatCurrency(summary.gross_sales) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold" :class="comparisonClass('gross_sales')">
                            {{ comparisonArrow('gross_sales') }}
                            {{ comparisonText('gross_sales') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonPeriod }}
                        </span>
                    </div>
                </div>

                <!-- COGS -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Cost of Goods Sold
                        </p>

                        <div class="rounded-lg bg-orange-50 p-2 text-orange-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-xl font-bold text-gray-800">
                        {{ formatCurrency(summary.cogs) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold" :class="comparisonClass('cogs')">
                            {{ comparisonArrow('cogs') }}
                            {{ comparisonText('cogs') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonPeriod }}
                        </span>
                    </div>
                </div>

                <!-- Gross Profit -->
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-500">
                            Gross Profit
                        </p>

                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 17l6-6 4 4 8-8M15 7h6v6" />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-xl font-bold" :class="profitClass(summary.gross_profit)">
                        {{ formatCurrency(summary.gross_profit) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold" :class="comparisonClass('gross_profit')">
                            {{ comparisonArrow('gross_profit') }}
                            {{ comparisonText('gross_profit') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonPeriod }}
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-gray-400">
                        {{ formatPercentage(summary.margin) }} gross margin
                    </p>
                </div>

            </div>

            <!-- Chart -->
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Reseller Sales Performance
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Gross Sales and Gross Profit by reseller
                    </p>
                </div>

                <apexchart
                    v-if="chartData.length"
                    type="bar"
                    height="350"
                    :options="chartOptions"
                    :series="chartSeries"
                />

                <div
                    v-else
                    class="flex h-[300px] items-center justify-center text-sm text-gray-400"
                >
                    No consignment sales data available.
                </div>
            </div>

            <!-- Reseller Performance Table -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-5">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Reseller Performance
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Sales, cost, and profitability breakdown by reseller.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    No.
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">
                                    Reseller
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Transactions
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Total Qty
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Gross Sales
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    COGS
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Gross Profit
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Margin
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">
                                    Sales Contribution
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr
                                v-for="(reseller, index) in resellers"
                                :key="reseller.reseller_id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                    {{ (pagination.from ?? 1) + index }}
                                </td>

                                <td class="px-4 py-3">
                                    <p class="whitespace-nowrap text-sm font-medium text-gray-800">
                                        {{ reseller.reseller_name }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">
                                    {{ formatNumber(reseller.total_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">
                                    {{ formatNumber(reseller.total_qty) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-800">
                                    {{ formatCurrency(reseller.gross_sales) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">
                                    {{ formatCurrency(reseller.cogs) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold"
                                    :class="profitClass(reseller.gross_profit)"
                                >
                                    {{ formatCurrency(reseller.gross_profit) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">
                                    {{ formatPercentage(reseller.margin) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700">
                                    {{ formatPercentage(reseller.sales_contribution) }}
                                </td>
                            </tr>

                            <tr v-if="!resellers.length">
                                <td
                                    colspan="9"
                                    class="px-4 py-12 text-center text-sm text-gray-500"
                                >
                                    No reseller sales data found for the selected filters.
                                </td>
                            </tr>
                        </tbody>

                        <!-- Grand Total -->
                        <tfoot v-if="resellers.length" class="bg-gray-50">
                            <tr>
                                <td
                                    colspan="2"
                                    class="whitespace-nowrap px-4 py-4 text-sm font-bold text-gray-800"
                                >
                                    GRAND TOTAL
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-800">
                                    {{ formatNumber(totals.total_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-800">
                                    {{ formatNumber(totals.total_qty) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-800">
                                    {{ formatCurrency(totals.gross_sales) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-800">
                                    {{ formatCurrency(totals.cogs) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold"
                                    :class="profitClass(totals.gross_profit)"
                                >
                                    {{ formatCurrency(totals.gross_profit) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-800">
                                    {{ formatPercentage(totals.margin) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-800">
                                    {{ formatPercentage(totals.sales_contribution) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.links?.length"
                    class="flex flex-col gap-4 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-gray-500">
                        Showing
                        <span class="font-medium text-gray-700">
                            {{ pagination.from ?? 0 }}
                        </span>
                        to
                        <span class="font-medium text-gray-700">
                            {{ pagination.to ?? 0 }}
                        </span>
                        of
                        <span class="font-medium text-gray-700">
                            {{ pagination.total ?? 0 }}
                        </span>
                        resellers
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in pagination.links"
                            :key="index"
                            type="button"
                            :disabled="!link.url"
                            @click="visitPage(link.url)"
                            class="rounded-lg border px-3 py-2 text-sm transition"
                            :class="[
                                link.active
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50',
                                !link.url ? 'cursor-not-allowed opacity-40' : 'cursor-pointer'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- Print Footer -->
            <div class="hidden print:block">
                <div class="mt-8 border-t border-gray-300 pt-4 text-center">
                    <p class="text-xs text-gray-500">
                        Nuvora ERP — Consignment Sales Report
                    </p>

                    <p class="mt-1 text-xs text-gray-400">
                        Generated at {{ formatDateTime(generatedAt) }}
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style>
@media print {
    @page {
        size: landscape;
        margin: 12mm;
    }

    body {
        background: white !important;
    }

    nav,
    aside,
    header,
    button {
        display: none !important;
    }

    .shadow-sm {
        box-shadow: none !important;
    }

    .rounded-xl {
        border-radius: 0 !important;
    }

    .overflow-x-auto {
        overflow: visible !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    th,
    td {
        font-size: 9px !important;
        padding: 6px !important;
    }

    tr {
        page-break-inside: avoid;
    }

    .print\:block {
        display: block !important;
    }

    .hidden {
        display: none !important;
    }
}
</style>