<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import {
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    MinusSmallIcon,
} from '@heroicons/vue/24/outline'
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
})

/*
|--------------------------------------------------------------------------
| Computed Data
|--------------------------------------------------------------------------
*/

const summary = computed(() => props.report.summary ?? {})

const sources = computed(() => props.report.sources ?? {})

const trend = computed(() => props.report.sales_trend ?? [])

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

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${value}T00:00:00`))
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

    return `${props.branches.length} Branches`
})

/*
|--------------------------------------------------------------------------
| Sales Trend Chart
|--------------------------------------------------------------------------
*/

const maxRevenue = computed(() => {
    return Math.max(
        ...trend.value.map(item => Number(item.revenue ?? 0)),
        1
    )
})

const getBarWidth = (value) => {
    return `${(Number(value ?? 0) / maxRevenue.value) * 100}%`
}

/*
|--------------------------------------------------------------------------
| Daily Sales Comparison
|--------------------------------------------------------------------------
*/

const getDailyChange = (index) => {

    if (index === 0) {
        return {
            type: 'first',
            percentage: null,
        }
    }

    const currentRevenue = Number(
        trend.value[index]?.revenue ?? 0
    )

    const previousRevenue = Number(
        trend.value[index - 1]?.revenue ?? 0
    )

    const difference = currentRevenue - previousRevenue

    /*
    |--------------------------------------------------------------------------
    | Previous Day Has No Revenue
    |--------------------------------------------------------------------------
    */

    if (previousRevenue === 0) {

        if (currentRevenue > 0) {
            return {
                type: 'new',
                percentage: null,
            }
        }

        return {
            type: 'equal',
            percentage: 0,
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Percentage
    |--------------------------------------------------------------------------
    */

    const percentage = (
        difference / previousRevenue
    ) * 100

    if (difference > 0) {
        return {
            type: 'up',
            percentage,
        }
    }

    if (difference < 0) {
        return {
            type: 'down',
            percentage,
        }
    }

    return {
        type: 'equal',
        percentage: 0,
    }
}

/*
|--------------------------------------------------------------------------
| Print Report
|--------------------------------------------------------------------------
*/

const printReport = () => {
    window.open(route('reports.sales.summary.print'), '_blank')
}

const exportPdf = () => {
    window.location.href = route('reports.sales.summary.pdf')
}

const exportExcel = () => {
    window.location.href = route('reports.sales.summary.excel')
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
                            @click="router.visit(route('reports.sales.summary.parameters'))"
                            class="text-gray-500 transition hover:text-blue-600"
                        >
                            Sales Summary
                        </button>

                        <span class="text-gray-400">/</span>

                        <span class="font-medium text-gray-900">
                            Results
                        </span>

                    </div>

                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Sales Summary
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Sales performance report
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

                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">

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
                            Branch
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ branchLabel }}
                        </p>

                    </div>

                    <div>

                        <p class="text-gray-500">
                            Generated At
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ new Date().toLocaleString('id-ID') }}
                        </p>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- KPI CARDS -->
            <!-- ===================================================== -->

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <!-- Total Revenue -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Revenue
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.total_revenue) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        POS + Consignment
                    </p>

                </div>


                <!-- Total Transactions -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Transactions
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_transactions) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Posted transactions
                    </p>

                </div>


                <!-- Items Sold -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Items Sold
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_items) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Total quantity sold
                    </p>

                </div>


                <!-- Gross Profit -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Gross Profit
                    </p>

                    <p
                        class="mt-2 text-xl font-bold"
                        :class="Number(summary.gross_profit) >= 0
                            ? 'text-emerald-600'
                            : 'text-red-600'"
                    >
                        {{ formatCurrency(summary.gross_profit) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Margin:
                        {{ formatNumber(summary.gross_profit_margin) }}%
                    </p>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- FINANCIAL SUMMARY & SALES SOURCES -->
            <!-- ===================================================== -->

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                <!-- Financial Summary -->

                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-200 px-5 py-4">

                        <h2 class="font-semibold text-gray-900">
                            Financial Summary
                        </h2>

                    </div>

                    <div class="space-y-4 p-5">

                        <div class="flex items-center justify-between text-sm">

                            <span class="text-gray-600">
                                Total Revenue
                            </span>

                            <span class="font-semibold text-gray-900">
                                {{ formatCurrency(summary.total_revenue) }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between text-sm">

                            <span class="text-gray-600">
                                Total Discount
                            </span>

                            <span class="font-semibold text-red-600">
                                {{ formatCurrency(summary.total_discount) }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between text-sm">

                            <span class="text-gray-600">
                                Total COGS
                            </span>

                            <span class="font-semibold text-gray-900">
                                {{ formatCurrency(summary.total_cogs) }}
                            </span>

                        </div>

                        <div class="border-t border-gray-200 pt-4">

                            <div class="flex items-center justify-between">

                                <span class="font-semibold text-gray-900">
                                    Gross Profit
                                </span>

                                <span class="text-lg font-bold text-emerald-600">
                                    {{ formatCurrency(summary.gross_profit) }}
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Sales Sources -->

                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                    <div class="border-b border-gray-200 px-5 py-4">

                        <h2 class="font-semibold text-gray-900">
                            Sales by Source
                        </h2>

                    </div>

                    <div class="space-y-5 p-5">

                        <!-- POS Sales -->

                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <span class="text-sm font-medium text-gray-700">
                                    POS Sales
                                </span>

                                <span class="text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(sources.pos?.revenue) }}
                                </span>

                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">

                                <div
                                    class="h-full rounded-full bg-blue-600"
                                    :style="{
                                        width: `${summary.total_revenue > 0
                                            ? (Number(sources.pos?.revenue ?? 0) / Number(summary.total_revenue)) * 100
                                            : 0}%`
                                    }"
                                ></div>

                            </div>

                            <p class="mt-2 text-xs text-gray-500">
                                {{ formatNumber(sources.pos?.transactions) }}
                                transactions
                            </p>

                        </div>


                        <!-- Consignment -->

                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <span class="text-sm font-medium text-gray-700">
                                    Consignment Settlement
                                </span>

                                <span class="text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(sources.consignment?.revenue) }}
                                </span>

                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">

                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    :style="{
                                        width: `${summary.total_revenue > 0
                                            ? (Number(sources.consignment?.revenue ?? 0) / Number(summary.total_revenue)) * 100
                                            : 0}%`
                                    }"
                                ></div>

                            </div>

                            <p class="mt-2 text-xs text-gray-500">
                                {{ formatNumber(sources.consignment?.transactions) }}
                                transactions
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- DAILY SALES TREND -->
            <!-- ===================================================== -->

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-5 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Daily Sales Trend
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        Revenue breakdown by transaction date.
                        Daily comparison against the previous day.
                    </p>

                </div>

                <div class="space-y-4 p-5">

                    <div
                        v-for="(item, index) in trend"
                        :key="item.date"
                        class="grid grid-cols-1 items-center gap-3 md:grid-cols-[110px_1fr_180px]"
                    >

                        <!-- Date -->

                        <div class="text-sm font-medium text-gray-600">
                            {{ formatDate(item.date) }}
                        </div>


                        <!-- Revenue Bars & Source Amounts -->

                        <div class="space-y-2">

                            <!-- Bars -->

                            <div class="flex h-2 overflow-hidden rounded-full bg-gray-100">

                                <div
                                    class="h-full bg-blue-600"
                                    :style="{
                                        width: getBarWidth(item.pos)
                                    }"
                                ></div>

                                <div
                                    class="h-full bg-emerald-500"
                                    :style="{
                                        width: getBarWidth(item.consignment)
                                    }"
                                ></div>

                            </div>

                           <!-- Revenue per Source -->

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">

                                    <!-- POS Revenue -->

                                    <span
                                        v-if="Number(item.pos) > 0"
                                        class="text-xs font-semibold text-blue-600"
                                    >
                                        {{ formatCurrency(item.pos) }}
                                    </span>

                                    <!-- Consignment Revenue -->

                                    <span
                                        v-if="Number(item.consignment) > 0"
                                        class="text-xs font-semibold text-emerald-600"
                                    >
                                        {{ formatCurrency(item.consignment) }}
                                    </span>

                                </div>

                        </div>


                        <!-- Revenue & Daily Comparison -->

                        <div class="md:text-right">

                            <p class="text-sm font-semibold text-gray-900">
                                {{ formatCurrency(item.revenue) }}
                            </p>

                            <div
                                v-if="getDailyChange(index).type !== 'first'"
                                class="mt-1 flex items-center gap-1 md:justify-end"
                            >

                                <!-- Increase -->

                                <template
                                    v-if="getDailyChange(index).type === 'up'"
                                >
                                    <ArrowTrendingUpIcon
                                        class="h-3.5 w-3.5 text-emerald-600"
                                    />

                                    <span class="text-xs font-semibold text-emerald-600">
                                        +{{ formatNumber(getDailyChange(index).percentage) }}%
                                    </span>
                                </template>


                                <!-- Decrease -->

                                <template
                                    v-else-if="getDailyChange(index).type === 'down'"
                                >
                                    <ArrowTrendingDownIcon
                                        class="h-3.5 w-3.5 text-red-600"
                                    />

                                    <span class="text-xs font-semibold text-red-600">
                                        {{ formatNumber(getDailyChange(index).percentage) }}%
                                    </span>
                                </template>


                                <!-- No Change -->

                                <template
                                    v-else-if="getDailyChange(index).type === 'equal'"
                                >
                                    <MinusSmallIcon
                                        class="h-3.5 w-3.5 text-slate-400"
                                    />

                                    <span class="text-xs font-medium text-slate-400">
                                        0%
                                    </span>
                                </template>


                                <!-- New Sales -->

                                <template
                                    v-else-if="getDailyChange(index).type === 'new'"
                                >
                                    <ArrowTrendingUpIcon
                                        class="h-3.5 w-3.5 text-emerald-600"
                                    />

                                    <span class="text-xs font-semibold text-emerald-600">
                                        New
                                    </span>
                                </template>

                            </div>

                            <p
                                v-if="getDailyChange(index).type !== 'first'"
                                class="mt-0.5 text-[10px] text-gray-400"
                            >
                                vs previous day
                            </p>

                        </div>

                    </div>


                    <!-- Empty State -->

                    <div
                        v-if="!trend.length"
                        class="py-8 text-center text-sm text-gray-500"
                    >
                        No sales data available for this period.
                    </div>


                    <!-- Legend -->

                    <div class="flex flex-wrap gap-5 border-t border-gray-200 pt-4">

                        <div class="flex items-center gap-2 text-xs text-gray-600">

                            <span class="h-3 w-3 rounded-sm bg-blue-600"></span>

                            POS Sales

                        </div>

                        <div class="flex items-center gap-2 text-xs text-gray-600">

                            <span class="h-3 w-3 rounded-sm bg-emerald-500"></span>

                            Consignment

                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- PRINT FOOTER -->
            <!-- ===================================================== -->

            <div class="hidden border-t border-gray-300 pt-4 text-xs text-gray-500 print:block">
                Nuvora ERP — Sales Summary Report
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