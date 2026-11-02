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

const sources = computed(() => props.report.sources ?? {})

const trend = computed(() => props.report.sales_trend ?? [])

const transactions = computed(
    () => props.report.transactions?.data ?? []
)

const pagination = computed(
    () => props.report.transactions ?? {}
)

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

const formatDateTime = (value) => {
    if (!value) return '-'

    const date = new Date(value)

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

/*
|--------------------------------------------------------------------------
| Branch Information
|--------------------------------------------------------------------------
*/

const branchLabel = computed(() => {
    if (!props.branches.length) return 'All Branches'

    if (props.branches.length === 1) {
        return props.branches[0].name
    }

    return 'All Branches'
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
| ApexCharts Configuration
|--------------------------------------------------------------------------
*/

const chartSeries = computed(() => [
    {
        name: 'POS',
        data: trend.value.map(item => Number(item.pos ?? 0)),
    },
    {
        name: 'Consignment',
        data: trend.value.map(item => Number(item.consignment ?? 0)),
    },
])

const chartOptions = computed(() => ({
    chart: {
        type: 'line',
        height: 350,
        toolbar: {
            show: false,
        },
        zoom: {
            enabled: false,
        },
        fontFamily: 'inherit',
        animations: {
            enabled: true,
            speed: 500,
        },
    },

    colors: ['#2563EB', '#10B981'],

    stroke: {
        curve: 'smooth',
        width: 3,
    },

    markers: {
        size: 4,
        strokeWidth: 2,
        hover: {
            size: 6,
        },
    },

    dataLabels: {
        enabled: false,
    },

    grid: {
        borderColor: '#E5E7EB',
        strokeDashArray: 4,
        padding: {
            left: 10,
            right: 10,
        },
    },

    xaxis: {
        categories: trend.value.map(item => formatDate(item.date)),

        labels: {
            style: {
                colors: '#6B7280',
                fontSize: '11px',
            },
        },

        axisBorder: {
            show: false,
        },

        axisTicks: {
            show: false,
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

            style: {
                colors: '#6B7280',
                fontSize: '11px',
            },
        },
    },

    legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '12px',
        markers: {
            radius: 12,
        },
    },

    tooltip: {
        shared: true,
        intersect: false,

        custom: ({ dataPointIndex }) => {
            const item = trend.value[dataPointIndex]

            if (!item) return ''

            return `
                <div style="
                    padding: 12px 14px;
                    min-width: 190px;
                    font-family: inherit;
                ">
                    <div style="
                        font-size: 12px;
                        font-weight: 700;
                        color: #111827;
                        margin-bottom: 10px;
                    ">
                        ${formatDate(item.date)}
                    </div>

                    <div style="
                        display: flex;
                        justify-content: space-between;
                        gap: 18px;
                        font-size: 12px;
                        margin-bottom: 6px;
                    ">
                        <span style="color: #2563EB;">POS</span>
                        <strong style="color: #111827;">
                            ${formatCurrency(item.pos)}
                        </strong>
                    </div>

                    <div style="
                        display: flex;
                        justify-content: space-between;
                        gap: 18px;
                        font-size: 12px;
                        margin-bottom: 8px;
                    ">
                        <span style="color: #10B981;">Consignment</span>
                        <strong style="color: #111827;">
                            ${formatCurrency(item.consignment)}
                        </strong>
                    </div>

                    <div style="
                        border-top: 1px solid #E5E7EB;
                        padding-top: 8px;
                        display: flex;
                        justify-content: space-between;
                        gap: 18px;
                        font-size: 12px;
                    ">
                        <strong style="color: #111827;">Total</strong>
                        <strong style="color: #111827;">
                            ${formatCurrency(item.total)}
                        </strong>
                    </div>
                </div>
            `
        },
    },

    noData: {
        text: 'No sales data available for this period.',
        align: 'center',
        verticalAlign: 'middle',
        style: {
            color: '#6B7280',
            fontSize: '13px',
        },
    },

    responsive: [
        {
            breakpoint: 640,
            options: {
                chart: {
                    height: 300,
                },

                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                },

                markers: {
                    size: 3,
                },
            },
        },
    ],
}))

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
        route('reports.sales.all.print'),
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
        'reports.sales.all.pdf'
    )
}

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.location.href = route(
        'reports.sales.all.excel'
    )
}

/*
|--------------------------------------------------------------------------
| Transaction Source Badge
|--------------------------------------------------------------------------
*/

const sourceBadgeClass = (source) => {
    if (source === 'POS') {
        return 'bg-blue-50 text-blue-700 ring-blue-200'
    }

    return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
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
                            @click="router.visit(route('reports.sales.all.parameters'))"
                            class="text-gray-500 transition hover:text-blue-600"
                        >
                            All Sales
                        </button>

                        <span class="text-gray-400">/</span>

                        <span class="font-medium text-gray-900">
                            Results
                        </span>

                    </div>

                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        All Sales
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Combined POS and consignment transaction listing.
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
                            {{ branchLabel }}
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
                            Generated At
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ generatedAt || '-' }}
                        </p>
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- KPI CARDS -->
            <!-- ===================================================== -->

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                <!-- Total Transactions -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Transactions
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_transactions) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Posted transactions
                    </p>

                </div>


                <!-- Total Items -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Items
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_items) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Total quantity sold
                    </p>

                </div>


                <!-- Total Sales -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Sales
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatCurrency(summary.total_sales) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        POS + Consignment
                    </p>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- SALES TREND -->
            <!-- ===================================================== -->

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Sales Trend
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Daily revenue comparison between POS and Consignment.
                    </p>

                </div>

                <div class="p-5">

                    <apexchart
                        type="line"
                        height="350"
                        :options="chartOptions"
                        :series="chartSeries"
                    />

                    <div
                        v-if="!trend.length"
                        class="py-4 text-center text-sm text-gray-500"
                    >
                        No sales data available for this period.
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- SALES TRANSACTIONS -->
            <!-- ===================================================== -->

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-gray-900">
                            Sales Transactions
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Posted POS sales and consignment settlements.
                        </p>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ pagination.total ?? 0 }} transactions
                    </div>

                </div>


                <!-- Table -->

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    No.
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Transaction Information
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Source / Cashier
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Branch / Customer
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Items
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Total Qty
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Discount
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Grand Total
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-100 bg-white">

                            <tr
                                v-for="(transaction, index) in transactions"
                                :key="`${transaction.source}-${transaction.transaction_id}`"
                                class="transition hover:bg-gray-50"
                            >

                                <!-- No. -->

                                <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-500">
                                    {{
                                        (pagination.from ?? 1) + index
                                    }}
                                </td>


                                <!-- Transaction Information -->

                                <td class="whitespace-nowrap px-4 py-4">

                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ formatDateTime(transaction.transaction_date) }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-blue-600">
                                        {{ transaction.transaction_number }}
                                    </p>

                                </td>


                                <!-- Source / Cashier -->

                                <td class="whitespace-nowrap px-4 py-4">

                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="sourceBadgeClass(transaction.source)"
                                    >
                                        {{ transaction.source }}
                                    </span>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{
                                            transaction.source === 'POS'
                                                ? transaction.cashier_name
                                                : '-'
                                        }}
                                    </p>

                                </td>


                                <!-- Branch / Customer -->

                                <td class="px-4 py-4">

                                    <p class="text-sm font-medium text-gray-900">
                                        {{ transaction.branch_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ transaction.partner_type }}:
                                        {{ transaction.customer_name }}
                                    </p>

                                </td>


                                <!-- Items -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatNumber(transaction.total_items) }}
                                </td>


                                <!-- Total Qty -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatNumber(transaction.total_qty) }}
                                </td>


                                <!-- Discount -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-red-600">
                                    {{ formatCurrency(transaction.discount) }}
                                </td>


                                <!-- Grand Total -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(transaction.grand_total) }}
                                </td>

                            </tr>


                            <!-- Empty State -->

                            <tr v-if="!transactions.length">

                                <td
                                    colspan="8"
                                    class="px-4 py-12 text-center"
                                >
                                    <p class="text-sm font-medium text-gray-700">
                                        No transactions found.
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        No posted sales transactions match the selected filters.
                                    </p>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- Pagination -->

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
                        transactions
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
                Nuvora ERP — All Sales Report
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