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

const paymentRows = computed(
    () => props.report.payment_rows?.data
        ?? props.report.payment_rows
        ?? []
)

const totals = computed(() => props.report.totals ?? {})

const pagination = computed(
    () => props.report.payment_rows?.links
        ? props.report.payment_rows
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
        name: 'POS',
        data: chartData.value.map(
            item => Number(item.pos ?? 0)
        ),
    },
    {
        name: 'Consignment',
        data: chartData.value.map(
            item => Number(item.consignment ?? 0)
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
            text: 'Payment Method',
        },

        labels: {
            rotate: -35,
            trim: false,
            hideOverlappingLabels: false,
        },
    },

    yaxis: {
        title: {
            text: 'Payment Amount (IDR)',
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
| Payment Method Filter Label
|--------------------------------------------------------------------------
*/

const paymentMethodLabel = computed(() => {
    return props.report.filters?.payment_method_label
        ?? 'All Payment Methods'
})

/*
|--------------------------------------------------------------------------
| Source Styling
|--------------------------------------------------------------------------
*/

const sourceClass = (source) => {
    return source === 'POS'
        ? 'bg-blue-50 text-blue-700'
        : 'bg-emerald-50 text-emerald-700'
}

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
        route('reports.sales.by-payment-method.print'),
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
        'reports.sales.by-payment-method.pdf'
    )
}

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.location.href = route(
        'reports.sales.by-payment-method.excel'
    )
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
                            @click="router.visit(route('reports.sales.by-payment-method.parameters'))"
                            class="text-gray-500 transition hover:text-blue-600"
                        >
                            Sales by Payment Method
                        </button>

                        <span class="text-gray-400">/</span>

                        <span class="font-medium text-gray-900">
                            Results
                        </span>

                    </div>

                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Sales by Payment Method
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Analyze payment collections by payment method,
                        source, and outstanding Consignment receivables.
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
                            Payment Method
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ paymentMethodLabel }}
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

                <!-- Total Payment Methods -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Payment Methods
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_payment_methods) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('total_payment_methods')"
                        >
                            {{ comparisonArrow('total_payment_methods') }}
                            {{ comparisonText('total_payment_methods') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Payment Transactions -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Payment Transactions
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.payment_transactions) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('payment_transactions')"
                        >
                            {{ comparisonArrow('payment_transactions') }}
                            {{ comparisonText('payment_transactions') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Sales Transactions -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Sales Transactions
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.sales_transactions) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('sales_transactions')"
                        >
                            {{ comparisonArrow('sales_transactions') }}
                            {{ comparisonText('sales_transactions') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Total Payment Amount -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Payment Amount
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.total_payment_amount) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('total_payment_amount')"
                        >
                            {{ comparisonArrow('total_payment_amount') }}
                            {{ comparisonText('total_payment_amount') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- POS Payment Amount -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        POS Payment Amount
                    </p>

                    <p class="mt-2 text-xl font-bold text-blue-700">
                        {{ formatCurrency(summary.pos_payment_amount) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('pos_payment_amount')"
                        >
                            {{ comparisonArrow('pos_payment_amount') }}
                            {{ comparisonText('pos_payment_amount') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Consignment Payment Amount -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Consignment Payment Amount
                    </p>

                    <p class="mt-2 text-xl font-bold text-emerald-700">
                        {{ formatCurrency(summary.consignment_payment_amount) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('consignment_payment_amount')"
                        >
                            {{ comparisonArrow('consignment_payment_amount') }}
                            {{ comparisonText('consignment_payment_amount') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>


                <!-- Outstanding Receivables -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Outstanding Receivables
                    </p>

                    <p class="mt-2 text-xl font-bold text-amber-600">
                        {{ formatCurrency(summary.outstanding_receivables) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Current outstanding balance
                    </p>

                </div>


                <!-- Average Payment Amount -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Average Payment Amount
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.average_payment_amount) }}
                    </p>

                    <div class="mt-2 flex items-center gap-1.5 text-xs">
                        <span
                            class="font-semibold"
                            :class="comparisonClass('average_payment_amount')"
                        >
                            {{ comparisonArrow('average_payment_amount') }}
                            {{ comparisonText('average_payment_amount') }}
                        </span>

                        <span class="text-gray-400">
                            vs {{ comparisonLabel }}
                        </span>
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- PAYMENT METHOD CHART -->
            <!-- ===================================================== -->

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Payment Amount by Method
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Comparison of POS and Consignment payment collections
                        by payment method.
                    </p>

                </div>

                <div class="p-5">

                    <apexchart
                        v-if="chartData.length"
                        type="bar"
                        :height="380"
                        :options="chartOptions"
                        :series="chartSeries"
                    />

                    <div
                        v-else
                        class="py-12 text-center"
                    >

                        <p class="text-sm font-medium text-gray-700">
                            No payment data available.
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            No posted payments match the selected filters.
                        </p>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- PAYMENT METHOD TABLE -->
            <!-- ===================================================== -->

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-gray-900">
                            Payment Method Details
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Payment collections grouped by payment method
                            and sales source.
                        </p>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ pagination.total ?? paymentRows.length }}
                        rows
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
                                    Payment Method
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Source
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Payment Transactions
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Sales Transactions
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Payment Amount
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Contribution
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            <tr
                                v-for="(item, index) in paymentRows"
                                :key="`${item.payment_method}-${item.source_type}`"
                                class="transition hover:bg-gray-50"
                            >

                                <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-500">
                                    {{
                                        (pagination.from ?? 1) + index
                                    }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4">

                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ item.payment_method_label }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ item.payment_method }}
                                    </p>

                                </td>

                                <td class="whitespace-nowrap px-4 py-4">

                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                        :class="sourceClass(item.source_type)"
                                    >
                                        {{ item.source_type }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatNumber(item.payment_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatNumber(item.sales_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(item.payment_amount) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-700">
                                    {{ formatPercentage(item.percentage) }}
                                </td>

                            </tr>


                            <!-- Empty State -->

                            <tr v-if="!paymentRows.length">

                                <td
                                    colspan="7"
                                    class="px-4 py-12 text-center"
                                >

                                    <p class="text-sm font-medium text-gray-700">
                                        No payment records found.
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        No posted payments match the selected filters.
                                    </p>

                                </td>

                            </tr>

                        </tbody>


                        <!-- GRAND TOTAL -->

                        <tfoot
                            v-if="paymentRows.length"
                            class="border-t-2 border-gray-300 bg-gray-100"
                        >

                            <tr>

                                <td
                                    colspan="3"
                                    class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold uppercase text-gray-900"
                                >
                                    Grand Total
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatNumber(totals.payment_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatNumber(totals.sales_transactions) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatCurrency(totals.payment_amount) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold text-gray-900">
                                    {{ formatPercentage(totals.percentage) }}
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
                        rows
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
                Nuvora ERP — Sales by Payment Method Report
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