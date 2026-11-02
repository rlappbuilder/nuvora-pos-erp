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

const categories = computed(
    () => props.report.categories?.data ?? props.report.categories ?? []
)

const totals = computed(() => props.report.totals ?? {})

const pagination = computed(
    () => props.report.categories?.links
        ? props.report.categories
        : {}
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
| Format Percentage
|--------------------------------------------------------------------------
*/

const formatPercentage = (value) => {
    return `${formatNumber(value)}%`
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
| Category Filter Label
|--------------------------------------------------------------------------
*/

const categoryLabel = computed(() => {
    return props.report.filters?.category_label
        ?? 'All Categories'
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
        route('reports.sales.by-category.print'),
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
        'reports.sales.by-category.pdf'
    )
}

/*
|--------------------------------------------------------------------------
| Export Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.location.href = route(
        'reports.sales.by-category.excel'
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
                            @click="router.visit(route('reports.sales.by-category.parameters'))"
                            class="text-gray-500 transition hover:text-blue-600"
                        >
                            Sales by Category
                        </button>

                        <span class="text-gray-400">/</span>

                        <span class="font-medium text-gray-900">
                            Results
                        </span>

                    </div>

                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        Sales by Category
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Analyze sales performance, cost, and gross profit by product category.
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
                            Product Category
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ categoryLabel }}
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

                <!-- Total Categories -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Categories
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_categories) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Categories with sales
                    </p>

                </div>


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


                <!-- Total Items Sold -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Items Sold
                    </p>

                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ formatNumber(summary.total_items) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Total quantity sold
                    </p>

                </div>


                <!-- Gross Sales -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Gross Sales
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.gross_sales) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Before discounts
                    </p>

                </div>


                <!-- Total Discount -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Total Discount
                    </p>

                    <p class="mt-2 text-xl font-bold text-red-600">
                        {{ formatCurrency(summary.discount) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Item and transaction discounts
                    </p>

                </div>


                <!-- Net Sales -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Net Sales
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.net_sales) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        After discounts
                    </p>

                </div>


                <!-- COGS -->

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-gray-500">
                        Cost of Goods Sold
                    </p>

                    <p class="mt-2 text-xl font-bold text-gray-900">
                        {{ formatCurrency(summary.cogs) }}
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        Inventory cost
                    </p>

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

                    <p class="mt-2 text-xs text-gray-500">
                        {{ formatPercentage(summary.margin) }} gross margin
                    </p>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- SALES BY CATEGORY -->
            <!-- ===================================================== -->

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-gray-900">
                            Category Performance
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Sales, discounts, cost of goods sold, and gross profit by category.
                        </p>
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ totals.total_categories ?? pagination.total ?? categories.length }}
                        categories
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
                                    Category
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

                            <tr
                                v-for="(category, index) in categories"
                                :key="category.category_id"
                                class="transition hover:bg-gray-50"
                            >

                                <!-- No. -->

                                <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-500">
                                    {{ (pagination.from ?? 1) + index }}
                                </td>


                                <!-- Category -->

                                <td class="whitespace-nowrap px-4 py-4">

                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ category.category_name }}
                                    </p>

                                </td>


                                <!-- Total Qty -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatNumber(category.total_qty) }}
                                </td>


                                <!-- Gross Sales -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatCurrency(category.gross_sales) }}
                                </td>


                                <!-- Discount -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-red-600">
                                    {{ formatCurrency(category.discount) }}
                                </td>


                                <!-- Net Sales -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    {{ formatCurrency(category.net_sales) }}
                                </td>


                                <!-- COGS -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700">
                                    {{ formatCurrency(category.cogs) }}
                                </td>


                                <!-- Gross Profit -->

                                <td
                                    class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold"
                                    :class="profitClass(category.gross_profit)"
                                >
                                    {{ formatCurrency(category.gross_profit) }}
                                </td>


                                <!-- Margin -->

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium text-gray-700">
                                    {{ formatPercentage(category.margin) }}
                                </td>

                            </tr>


                            <!-- Empty State -->

                            <tr v-if="!categories.length">

                                <td
                                    colspan="9"
                                    class="px-4 py-12 text-center"
                                >
                                    <p class="text-sm font-medium text-gray-700">
                                        No category sales found.
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        No posted sales match the selected filters.
                                    </p>

                                </td>

                            </tr>

                        </tbody>


                        <!-- TOTAL ROW -->

                        <tfoot v-if="categories.length" class="border-t-2 border-gray-300 bg-gray-50">

                            <tr>

                                <td
                                    colspan="2"
                                    class="whitespace-nowrap px-4 py-4 text-right text-sm font-bold uppercase text-gray-900"
                                >
                                    Total
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
                        categories
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
                Nuvora ERP — Sales by Category Report
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