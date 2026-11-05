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
| Computed Data
|--------------------------------------------------------------------------
*/

const summary = computed(() => props.report.summary ?? {})

const branchRows = computed(
    () => props.report.branch_rows?.data ?? props.report.branch_rows ?? []
)

const totals = computed(() => props.report.totals ?? {})

const pagination = computed(
    () => props.report.branch_rows?.links
        ? props.report.branch_rows
        : {}
)

/*
|--------------------------------------------------------------------------
| Comparison Data
|--------------------------------------------------------------------------
*/

const comparison = computed(
    () => props.report.comparison ?? {}
)

const comparisonMetrics = computed(
    () => comparison.value.metrics ?? {}
)

const comparisonLabel = computed(
    () => comparison.value.period_label ?? 'Previous Period'
)

const getComparison = (key) => {
    return comparisonMetrics.value[key] ?? {
        current: 0,
        previous: 0,
        percentage: null,
        direction: 'unchanged',
        status: 'no_data',
    }
}

/*
|--------------------------------------------------------------------------
| Format Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

/*
|--------------------------------------------------------------------------
| Format Compact Currency
|--------------------------------------------------------------------------
*/

const formatCompactCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(Number(value ?? 0))
}

/*
|--------------------------------------------------------------------------
| Format Number
|--------------------------------------------------------------------------
*/

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

/*
|--------------------------------------------------------------------------
| Format Percentage
|--------------------------------------------------------------------------
*/

const formatPercentage = (value) => {
    return `${formatNumber(value)}%`
}

/*
|--------------------------------------------------------------------------
| Comparison Display
|--------------------------------------------------------------------------
*/

const comparisonText = (key) => {
    const metric = getComparison(key)

    if (metric.status === 'new') {
        return 'New'
    }

    if (metric.status === 'no_data') {
        return '—'
    }

    return `${formatNumber(Math.abs(metric.percentage ?? 0))}%`
}

const comparisonArrow = (key) => {
    const direction = getComparison(key).direction

    if (direction === 'up') return '↑'
    if (direction === 'down') return '↓'

    return '→'
}

const comparisonClass = (key) => {
    const metric = getComparison(key)

    if (metric.status === 'new') {
        return 'text-emerald-600'
    }

    if (metric.status === 'no_data') {
        return 'text-gray-400'
    }

    if (metric.direction === 'up') {
        return 'text-emerald-600'
    }

    if (metric.direction === 'down') {
        return 'text-red-600'
    }

    return 'text-gray-500'
}

/*
|--------------------------------------------------------------------------
| ApexCharts Data
|--------------------------------------------------------------------------
*/

const chartData = computed(() => {
    const chart = props.report.chart ?? {}

    return Array.isArray(chart.data)
        ? chart.data
        : []
})

const chartSeries = computed(() => [
    {
        name: 'Gross Sales',
        data: chartData.value.map(
            item => Number(item.gross_sales ?? 0)
        ),
    },
    {
        name: 'Gross Profit',
        data: chartData.value.map(
            item => Number(item.gross_profit ?? 0)
        ),
    },
])

/*
|--------------------------------------------------------------------------
| ApexCharts Configuration
|--------------------------------------------------------------------------
*/

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 380,
        toolbar: {
            show: false,
        },
    },

    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 4,
        },
    },

    dataLabels: {
        enabled: false,
    },

    stroke: {
        show: true,
        width: 2,
        colors: ['transparent'],
    },

    xaxis: {
        categories: chartData.value.map(item => item.label),

        title: {
            text: 'User / Cashier',
        },

        labels: {
            rotate: -35,
            trim: false,
            hideOverlappingLabels: false,
        },
    },

    yaxis: {
        title: {
            text: 'Amount (IDR)',
        },

        labels: {
            formatter: value => `Rp ${formatCompactCurrency(value)}`,
        },
    },

    tooltip: {
        y: {
            formatter: value => formatCurrency(value),
        },
    },

    legend: {
        position: 'top',
    },

    grid: {
        borderColor: '#e5e7eb',
    },
}))

/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

const formatDate = (value) => {
    if (!value) return '-'

    const date = String(value).substring(0, 10)

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${date}T00:00:00`))
}

/*
|--------------------------------------------------------------------------
| Branch Information
|--------------------------------------------------------------------------
*/

const displayBranchLabel = computed(() => {
    return props.branchLabel || 'All Branches'
})

/*
|--------------------------------------------------------------------------
| Sales Source Label
|--------------------------------------------------------------------------
*/

const sourceLabel = computed(() => {
    const source = props.report.filters?.source ?? 'all'

    const labels = {
        all: 'All Sources',
        pos: 'POS',
        consignment: 'Consignment',
    }

    return labels[source] ?? 'All Sources'
})

/*
|--------------------------------------------------------------------------
| Cashier Filter Label
|--------------------------------------------------------------------------
*/

const cashierLabel = computed(() => {
    return props.report.filters?.cashier_label
        ?? 'All Cashiers'
})

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const navigatePage = (url) => {
    if (!url) return

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

const paginationLabel = (label) => {
    return String(label)
        .replace('&laquo;', '«')
        .replace('&raquo;', '»')
}

/*
|--------------------------------------------------------------------------
| Print Report
|--------------------------------------------------------------------------
*/

const printReport = () => {
    window.open(
        route('reports.sales.by-cashier.print'),
        '_blank'
    )
}

/*
|--------------------------------------------------------------------------
| Export PDF
|--------------------------------------------------------------------------
*/

const exportPdf = () => {
    window.location.href = route(
        'reports.sales.by-cashier.pdf'
    )
}

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.location.href = route(
        'reports.sales.by-cashier.excel'
    )
}

/*
|--------------------------------------------------------------------------
| Gross Profit Color
|--------------------------------------------------------------------------
*/

const profitClass = (value) => {
    return Number(value ?? 0) >= 0
        ? 'text-emerald-700'
        : 'text-red-600'
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">

            <!-- ===================================================== -->
            <!-- HEADER -->
            <!-- ===================================================== -->

            <div class="flex flex-wrap items-start justify-between gap-4">

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
                            @click="router.visit(route('reports.sales.by-cashier.parameters'))"
                            class="text-gray-500 transition hover:text-blue-600"
                        >
                            Sales by Cashier
                        </button>

                        <span class="text-gray-400">/</span>

                        <span class="font-medium text-gray-900">
                            Results
                        </span>

                    </div>

                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Sales by Cashier
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Analyze cashier sales performance, transactions,
                        costs, and gross profit.
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


            <!-- ===================================================== -->
            <!-- REPORT INFORMATION -->
            <!-- ===================================================== -->

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2 xl:grid-cols-5">

                    <div>
                        <p class="text-gray-500">
                            Company
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ company?.company_name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Branch
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ displayBranchLabel }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Period
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ formatDate(report.filters.date_from) }}
                            -
                            {{ formatDate(report.filters.date_to) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Sales Source
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ sourceLabel }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">
                            Cashier
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ cashierLabel }}
                        </p>
                    </div>

                </div>

                <div class="mt-4 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500">
                        Generated At
                    </p>

                    <p class="mt-1 text-sm font-semibold text-gray-900">
                        {{ generatedAt || '-' }}
                    </p>
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- KPI CARDS -->
            <!-- ===================================================== -->

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <!-- Total Cashiers -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Cashiers
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_cashiers) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('total_cashiers')"
                        >
                            {{ comparisonArrow('total_cashiers') }}
                            {{ comparisonText('total_cashiers') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Total Transactions -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Transactions
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_transactions) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('total_transactions')"
                        >
                            {{ comparisonArrow('total_transactions') }}
                            {{ comparisonText('total_transactions') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Total Items Sold -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Items Sold
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_items) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('total_items')"
                        >
                            {{ comparisonArrow('total_items') }}
                            {{ comparisonText('total_items') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Gross Sales -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Gross Sales
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.gross_sales) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('gross_sales')"
                        >
                            {{ comparisonArrow('gross_sales') }}
                            {{ comparisonText('gross_sales') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Total Discount -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Discount
                    </p>

                    <p class="mt-2 text-xl font-bold text-red-600">
                        {{ formatCurrency(summary.discount) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('discount')"
                        >
                            {{ comparisonArrow('discount') }}
                            {{ comparisonText('discount') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Net Sales -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Net Sales
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.net_sales) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('net_sales')"
                        >
                            {{ comparisonArrow('net_sales') }}
                            {{ comparisonText('net_sales') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- COGS -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Cost of Goods Sold
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.cogs) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('cogs')"
                        >
                            {{ comparisonArrow('cogs') }}
                            {{ comparisonText('cogs') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Gross Profit -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Gross Profit
                    </p>

                    <p
                        class="mt-2 text-xl font-bold"
                        :class="profitClass(summary.gross_profit)"
                    >
                        {{ formatCurrency(summary.gross_profit) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('gross_profit')"
                        >
                            {{ comparisonArrow('gross_profit') }}
                            {{ comparisonText('gross_profit') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-gray-500">
                        {{ formatPercentage(summary.margin) }} gross margin
                    </p>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- CASHIER PERFORMANCE CHART -->
            <!-- ===================================================== -->

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Top 10 Cashier Performance
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Gross sales and gross profit comparison across cashiers.
                        Cashier sales are combined across branches.
                    </p>

                </div>

                <div class="p-5">

                    <apexchart
                        type="bar"
                        :height="Math.max(380, chartData.length * 45)"
                        :options="chartOptions"
                        :series="chartSeries"
                    />

                    <div
                        v-if="!chartData.length"
                        class="py-4 text-center text-sm text-gray-500"
                    >
                        No cashier sales data available for this period.
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- CASHIER PERFORMANCE TABLE -->
            <!-- ===================================================== -->

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-gray-900">
                            Cashier Performance
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Sales performance grouped by branch and cashier.
                        </p>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ pagination.total ?? branchRows.length }}
                        branches
                    </div>

                </div>


                <!-- TABLE -->

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    No.
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Cashier / Sales Source
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Transactions
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Total Qty
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Gross Sales
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Discount
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Net Sales
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    COGS
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Gross Profit
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Margin
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            <template
                                v-for="branch in branchRows"
                                :key="branch.branch_id"
                            >

                                <!-- Branch Header -->

                                <tr class="bg-blue-50">

                                    <td
                                        colspan="10"
                                        class="px-4 py-3"
                                    >

                                        <div class="flex items-center gap-2">

                                            <span class="text-sm font-bold text-blue-900">
                                                {{ branch.branch_name }}
                                            </span>

                                            <span class="rounded-full border border-blue-200 bg-white px-2 py-0.5 text-xs font-medium text-blue-700">
                                                {{ branch.rows?.length ?? 0 }} groups
                                            </span>

                                        </div>

                                    </td>

                                </tr>


                                <!-- Cashier / Consignment Rows -->

                                <tr
                                    v-for="(item, itemIndex) in branch.rows"
                                    :key="`${branch.branch_id}-${item.source_type}-${item.cashier_id ?? 'consignment'}`"
                                    class="transition hover:bg-gray-50"
                                >

                                    <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-500">
                                        {{ itemIndex + 1 }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">

                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ item.cashier_name }}
                                        </p>

                                        <span
                                            class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="item.source_type === 'POS'
                                                ? 'bg-blue-50 text-blue-700'
                                                : 'bg-emerald-50 text-emerald-700'"
                                        >
                                            {{ item.source_type }}
                                        </span>

                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                        {{ formatNumber(item.total_transactions) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                        {{ formatNumber(item.total_qty) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                        {{ formatCurrency(item.gross_sales) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-red-600">
                                        {{ formatCurrency(item.discount) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900">
                                        {{ formatCurrency(item.net_sales) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                        {{ formatCurrency(item.cogs) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold"
                                        :class="profitClass(item.gross_profit)"
                                    >
                                        {{ formatCurrency(item.gross_profit) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-700">
                                        {{ formatPercentage(item.margin) }}
                                    </td>

                                </tr>


                                <!-- Branch Subtotal -->

                                <tr class="border-t border-gray-200 bg-gray-50">

                                    <td
                                        colspan="2"
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900"
                                    >
                                        Subtotal {{ branch.branch_name }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                        {{ formatNumber(branch.subtotal?.total_transactions) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                        {{ formatNumber(branch.subtotal?.total_qty) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                        {{ formatCurrency(branch.subtotal?.gross_sales) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-red-600">
                                        {{ formatCurrency(branch.subtotal?.discount) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                        {{ formatCurrency(branch.subtotal?.net_sales) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                        {{ formatCurrency(branch.subtotal?.cogs) }}
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold"
                                        :class="profitClass(branch.subtotal?.gross_profit)"
                                    >
                                        {{ formatCurrency(branch.subtotal?.gross_profit) }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-gray-900">
                                        {{ formatPercentage(branch.subtotal?.margin) }}
                                    </td>

                                </tr>

                            </template>


                            <!-- Empty State -->

                            <tr v-if="!branchRows.length">

                                <td
                                    colspan="10"
                                    class="px-4 py-12 text-center"
                                >

                                    <p class="text-sm font-medium text-gray-700">
                                        No cashier sales found.
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        No posted sales match the selected filters.
                                    </p>

                                </td>

                            </tr>

                        </tbody>


                        <!-- GRAND TOTAL -->

                        <tfoot
                            v-if="branchRows.length"
                            class="border-t-2 border-gray-300 bg-gray-100"
                        >

                            <tr>

                                <td
                                    colspan="2"
                                    class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold uppercase text-gray-900"
                                >
                                    Grand Total
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatNumber(totals.total_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatNumber(totals.total_qty) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatCurrency(totals.gross_sales) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-red-600">
                                    {{ formatCurrency(totals.discount) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatCurrency(totals.net_sales) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatCurrency(totals.cogs) }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold"
                                    :class="profitClass(totals.gross_profit)"
                                >
                                    {{ formatCurrency(totals.gross_profit) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatPercentage(totals.margin) }}
                                </td>

                            </tr>

                        </tfoot>

                    </table>

                </div>


                <!-- PAGINATION -->

                <div
                    v-if="pagination.links?.length"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-4"
                >

                    <p class="text-xs text-gray-500">
                        Showing
                        {{ pagination.from ?? 0 }}
                        to
                        {{ pagination.to ?? 0 }}
                        of
                        {{ pagination.total ?? 0 }}
                        branches
                    </p>

                    <div class="flex flex-wrap items-center gap-1">

                        <button
                            v-for="(link, index) in pagination.links"
                            :key="index"
                            type="button"
                            @click="navigatePage(link.url)"
                            :disabled="!link.url"
                            class="min-w-9 rounded-lg border px-3 py-2 text-xs font-medium transition"
                            :class="link.active
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40'"
                        >
                            {{ paginationLabel(link.label) }}
                        </button>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- PRINT FOOTER -->
            <!-- ===================================================== -->

            <div class="hidden border-t border-gray-300 pt-4 text-xs text-gray-500 print:block">
                Nuvora ERP — Sales by Cashier Report
            </div>

        </div>
    </AppLayout>
</template>

<style>
@media print {
    body {
        background: white !important;
    }

    nav,
    aside {
        display: none !important;
    }

    .print\:hidden {
        display: none !important;
    }

    @page {
        size: A4 landscape;
        margin: 12mm;
    }
}
</style>