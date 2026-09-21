<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

import DatePicker from '@/Components/Form/DatePicker.vue'

//import BaseButton from '@/Components/Form/BaseButton.vue'
//import LoadingOverlay from '@/Components/LoadingOverlay.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    title: {
        type: String,
        default: 'Consignment Report',
    },

    report: {
        type: Object,
        default: () => ({
            type: 'all_activity',
            title: 'All Activity',
            rows: [],
            summary: {},
        }),
    },

    branches: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
})


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Report Type
|--------------------------------------------------------------------------
*/

const reportTypes = [
    {
        id: 'all_activity',
        label: 'All Activity',
    },

    {
        id: 'consignment_out',
        label: 'Consignment Out',
    },

    {
        id: 'sales_settlement',
        label: 'Sales Settlement',
    },

    {
        id: 'receivable_payment',
        label: 'Receivable Payment',
    },

    {
        id: 'consignment_return',
        label: 'Consignment Return',
    },

    {
        id: 'stock_position',
        label: 'Stock Position',
    },

    {
        id: 'receivable',
        label: 'Receivable',
    },

    {
        id: 'sales_analysis',
        label: 'Sales Analysis',
    },

    {
        id: 'profit_analysis',
        label: 'Profit Analysis',
    },
]


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const reportType = ref(
    props.filters.report_type
    ?? 'all_activity'
)

const resellerId = ref(
    props.filters.reseller_id
    ?? null
)

const branchId = ref(
    props.filters.branch_id
    ?? null
)

const dateFrom = ref(
    props.filters.date_from
    ?? ''
)

const dateTo = ref(
    props.filters.date_to
    ?? ''
)


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const activeReportType = computed(() => {

    return reportTypes.find(
        item =>
            item.id === reportType.value
    )
    ?? reportTypes[0]
})


const rows = computed(() => {

    return props.report?.rows
        ?? []
})


const summary = computed(() => {

    return props.report?.summary
        ?? {}
})


/*
|--------------------------------------------------------------------------
| Apply Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {

    loading.value = true

    router.get(
        route('consignment-reports.index'),
        {
            report_type:
                reportType.value,

            reseller_id:
                resellerId.value,

            branch_id:
                branchId.value,

            date_from:
                dateFrom.value,

            date_to:
                dateTo.value,
        },
        {
            preserveState: true,
            preserveScroll: true,

            onFinish: () => {
                loading.value = false
            },
        }
    )
}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

const refresh = () => {

    reportType.value =
        'all_activity'

    resellerId.value =
        null

    branchId.value =
        null

    dateFrom.value =
        ''

    dateTo.value =
        ''

    applyFilter()
}


/*
|--------------------------------------------------------------------------
| Export Query
|--------------------------------------------------------------------------
*/

const exportQuery = computed(() => {

    const params = new URLSearchParams()

    if (reportType.value) {
        params.set(
            'report_type',
            reportType.value
        )
    }

    if (resellerId.value) {
        params.set(
            'reseller_id',
            resellerId.value
        )
    }

    if (branchId.value) {
        params.set(
            'branch_id',
            branchId.value
        )
    }

    if (dateFrom.value) {
        params.set(
            'date_from',
            dateFrom.value
        )
    }

    if (dateTo.value) {
        params.set(
            'date_to',
            dateTo.value
        )
    }

    return params.toString()
})


/*
|--------------------------------------------------------------------------
| Export / Print
|--------------------------------------------------------------------------
*/

const openPrint = () => {

    const url =
        route(
            'consignment-reports.print'
        )
        + (
            exportQuery.value
                ? `?${exportQuery.value}`
                : ''
        )

    window.open(
        url,
        '_blank'
    )
}


const downloadPdf = () => {

    const url =
        route(
            'consignment-reports.pdf'
        )
        + (
            exportQuery.value
                ? `?${exportQuery.value}`
                : ''
        )

    window.location.href = url
}


const downloadExcel = () => {

    const url =
        route(
            'consignment-reports.excel'
        )
        + (
            exportQuery.value
                ? `?${exportQuery.value}`
                : ''
        )

    window.location.href = url
}

/*
|--------------------------------------------------------------------------
| Selected Row
|--------------------------------------------------------------------------
*/

const selectedRow = ref(null)

const selectRow = (row) => {
    if (selectedRow.value === row) {
        selectedRow.value = null
        return
    }

    selectedRow.value = row
}


/*
|--------------------------------------------------------------------------
| Number Formatter
|--------------------------------------------------------------------------
*/

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(
        Number(value ?? 0)
    )
}


const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(
        Number(value ?? 0)
    )
}


/*
|--------------------------------------------------------------------------
| Summary Cards
|--------------------------------------------------------------------------
*/

const summaryCards = computed(() => {

    const type =
        props.report?.type

    const data =
        props.report?.summary ?? {}

    switch (type) {

        case 'consignment_out':

            return [
                {
                    label: 'Documents',
                    value: formatNumber(data.documents),
                },
                {
                    label: 'Qty',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Value',
                    value: formatCurrency(data.total),
                },
            ]


        case 'sales_settlement':

            return [
                {
                    label: 'Documents',
                    value: formatNumber(data.documents),
                },
                {
                    label: 'Sold',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Sales',
                    value: formatCurrency(data.sales),
                },
                {
                    label: 'Payment',
                    value: formatCurrency(data.payment),
                },
                {
                    label: 'Receivable',
                    value: formatCurrency(data.receivable),
                },
            ]


        case 'receivable_payment':

            return [
                {
                    label: 'Documents',
                    value: formatNumber(data.documents),
                },
                {
                    label: 'Payment',
                    value: formatCurrency(data.payment),
                },
            ]


        case 'consignment_return':

            return [
                {
                    label: 'Documents',
                    value: formatNumber(data.documents),
                },
                {
                    label: 'Qty Return',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Total HPP',
                    value: formatCurrency(data.total_cost),
                },
            ]


        case 'stock_position':

            return [
                {
                    label: 'Products',
                    value: formatNumber(data.products),
                },
                {
                    label: 'Stock Qty',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Stock Value',
                    value: formatCurrency(data.stock_value),
                },
            ]


        case 'receivable':

            return [
                {
                    label: 'Debit',
                    value: formatCurrency(data.debit),
                },
                {
                    label: 'Credit',
                    value: formatCurrency(data.credit),
                },
                {
                    label: 'Outstanding',
                    value: formatCurrency(data.outstanding),
                },
            ]


        case 'sales_analysis':

            return [
                {
                    label: 'Resellers',
                    value: formatNumber(data.resellers),
                },
                {
                    label: 'Sold',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Sales',
                    value: formatCurrency(data.sales),
                },
                {
                    label: 'HPP Nuvora',
                    value: formatCurrency(data.hpp),
                },
                {
                    label: 'Gross Profit',
                    value: formatCurrency(data.profit),
                },
                {
                    label: 'Margin',
                    value: `${formatNumber(data.margin)}%`,
                },
            ]


        case 'profit_analysis':

            return [
                {
                    label: 'Sold',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Sales',
                    value: formatCurrency(data.sales),
                },
                {
                    label: 'HPP Nuvora',
                    value: formatCurrency(data.hpp),
                },
                {
                    label: 'Gross Profit',
                    value: formatCurrency(data.profit),
                },
                {
                    label: 'Margin',
                    value: `${formatNumber(data.margin)}%`,
                },
            ]


        default:

            return [
                {
                    label: 'Rows',
                    value: formatNumber(data.rows),
                },
                {
                    label: 'Qty',
                    value: formatNumber(data.qty),
                },
                {
                    label: 'Amount',
                    value: formatCurrency(data.amount),
                },
            ]
    }
})
</script>
<template>

    <AppLayout>

        <div class="space-y-4">

            <!-- ====================================================== -->
            <!-- HEADER -->
            <!-- ====================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Consignment Report
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Reseller consignment activity and analysis
                    </p>

                </div>


                <!-- ================================================== -->
                <!-- ACTIONS -->
                <!-- ================================================== -->

                <div
                    class="
                        flex
                        items-center
                        gap-2
                        print:hidden
                    "
                >

                    <!-- Refresh -->

                    <button
                        type="button"
                        class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-3
                            py-2
                            text-sm
                            font-medium
                            text-gray-700
                            transition
                            hover:bg-gray-50
                        "
                        @click="refresh"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-4 w-4 text-gray-600"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20 11a8.1 8.1 0 0 0-14.9-4M4 5v4h4"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 13a8.1 8.1 0 0 0 14.9 4M20 19v-4h-4"
                            />

                        </svg>

                        Refresh

                    </button>


                    <!-- Print -->

                    <button
                        type="button"
                        class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-3
                            py-2
                            text-sm
                            font-medium
                            text-gray-700
                            transition
                            hover:bg-gray-50
                        "
                        @click="openPrint"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-4 w-4 text-gray-600"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 9V3h12v6"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5h-2"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 14h12v7H6z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M18 12h.01"
                            />

                        </svg>

                        Print

                    </button>


                    <!-- PDF -->

                    <button
                        type="button"
                        class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-3
                            py-2
                            text-sm
                            font-medium
                            text-gray-700
                            transition
                            hover:bg-gray-50
                        "
                        @click="downloadPdf"
                    >

                        <svg
                            viewBox="0 0 32 32"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                        >

                            <path
                                fill="#E53935"
                                d="M7 2h13l7 7v21H7z"
                            />

                            <path
                                fill="#fff"
                                d="M20 2v7h7"
                            />

                            <path
                                fill="#fff"
                                d="
                                    M10 18.5h2.3
                                    c2.1 0 3.3-1.1 3.3-2.8
                                    0-1.7-1.2-2.7-3.3-2.7H10zm2.2-1.8
                                    h-.5v-1.9h.5
                                    c.7 0 1.2.3 1.2.9
                                    0 .7-.5 1-1.2 1z
                                "
                            />

                            <path
                                fill="#fff"
                                d="
                                    M17 18.5h2.1
                                    c2.4 0 3.8-1 3.8-3.7
                                    0-2.5-1.4-3.8-3.8-3.8H17zm2.1-5.7
                                    h.3c1.1 0 1.7.6 1.7 2
                                    0 1.5-.6 2-1.7 2h-.3z
                                "
                            />

                        </svg>

                        PDF

                    </button>


                    <!-- Excel -->

                    <button
                        type="button"
                        class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-3
                            py-2
                            text-sm
                            font-medium
                            text-gray-700
                            transition
                            hover:bg-gray-50
                        "
                        @click="downloadExcel"
                    >

                        <svg
                            viewBox="0 0 32 32"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                        >

                            <path
                                fill="#21A366"
                                d="
                                    M18 3h9a2 2 0 0 1 2 2v22
                                    a2 2 0 0 1-2 2h-9z
                                "
                            />

                            <path
                                fill="#107C41"
                                d="
                                    M3 7a2 2 0 0 1 2-2h13v22H5
                                    a2 2 0 0 1-2-2z
                                "
                            />

                            <path
                                fill="#fff"
                                d="
                                    M8.2 11h2.4l2.1 3.5
                                    2-3.5h2.3l-3.1 5
                                    3.2 5h-2.5l-2-3.6
                                    -2.1 3.6H8.1l3.2-5z
                                "
                            />

                        </svg>

                        Excel

                    </button>

                </div>

            </div>


            <!-- ====================================================== -->
            <!-- FILTER -->
            <!-- ====================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                    shadow-sm
                "
            >

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                        lg:grid-cols-5
                    "
                >

                    <!-- Report Type -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Report Type
                        </label>

                        <SearchableSelect
                            v-model="reportType"
                            :options="reportTypes"
                            label="label"
                            track-by="id"
                            placeholder="Select report type"
                        />

                    </div>


                    <!-- Reseller -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Reseller
                        </label>

                        <SearchableSelect
                            v-model="resellerId"
                            :options="resellers"
                            label="label"
                            track-by="id"
                            placeholder="All Reseller"
                            clearable
                        />

                    </div>


                    <!-- Branch -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Branch
                        </label>

                        <SearchableSelect
                            v-model="branchId"
                            :options="branches"
                            label="label"
                            track-by="id"
                            placeholder="All Branch"
                            clearable
                        />

                    </div>


                    <!-- Date From -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Date From
                        </label>

                        <DatePicker
                            v-model="dateFrom"
                            class="w-full"
                        />

                    </div>


                    <!-- Date To -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Date To
                        </label>

                        <DatePicker
                            v-model="dateTo"
                            class="w-full"
                        />

                    </div>

                </div>


                <!-- Filter Actions -->

                <div
                    class="
                        mt-4
                        flex
                        items-center
                        justify-end
                        gap-2
                    "
                >

                    <button
                        type="button"
                        class="
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-4
                            py-2
                            text-sm
                            font-medium
                            text-gray-700
                            transition
                            hover:bg-gray-50
                        "
                        @click="refresh"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="
                            rounded-lg
                            bg-[#2563EB]
                            px-4
                            py-2
                            text-sm
                            font-medium
                            text-white
                            transition
                            hover:bg-blue-700
                        "
                        @click="applyFilter"
                    >
                        Apply Filter
                    </button>

                </div>

            </div>


            <!-- ====================================================== -->
            <!-- REPORT TITLE -->
            <!-- ====================================================== -->

            <div
                class="
                    flex
                    items-center
                    justify-between
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-semibold
                            text-gray-800
                        "
                    >
                        {{ activeReportType.label }}
                    </h2>

                    <p
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{ rows.length }} result(s)
                    </p>

                </div>

            </div>


            <!-- ====================================================== -->
            <!-- REPORT AREA -->
            <!-- ====================================================== -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    shadow-sm
                "
            >

                <!-- ================================================== -->
                <!-- SUMMARY -->
                <!-- ================================================== -->

                <div
                    v-if="summaryCards.length"
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        p-5
                        sm:grid-cols-2
                        lg:grid-cols-3
                        xl:grid-cols-6
                    "
                >

                    <div
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-4
                            py-3
                        "
                    >

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            {{ card.label }}
                        </div>

                        <div
                            class="
                                mt-1
                                text-lg
                                font-semibold
                                text-gray-800
                            "
                        >
                            {{ card.value }}
                        </div>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- SALES ANALYSIS -->
                <!-- ================================================== -->

                <div
                    v-if="report.type === 'sales_analysis'"
                >

                    <div class="px-5 pb-5">

                        <div
                            class="
                                mb-3
                                text-sm
                                font-semibold
                                text-gray-800
                            "
                        >
                            Sales By Reseller
                        </div>

                        <div class="overflow-x-auto">

                            <table class="w-full">

                                <thead>

                                    <tr
                                        class="
                                            border-b
                                            border-black
                                            text-left
                                            text-xs
                                            font-semibold
                                            uppercase
                                            tracking-wide
                                            text-gray-700
                                        "
                                    >

                                        <th class="px-3 py-3">
                                            No
                                        </th>

                                        <th class="px-3 py-3">
                                            Reseller
                                        </th>

                                        <th class="px-3 py-3 text-right">
                                            Sold
                                        </th>

                                        <th class="px-3 py-3 text-right">
                                            Sales
                                        </th>

                                        <th class="px-3 py-3 text-right">
                                            HPP Nuvora
                                        </th>

                                        <th class="px-3 py-3 text-right">
                                            Gross Profit
                                        </th>

                                        <th class="px-3 py-3 text-right">
                                            Margin
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <template
                                        v-for="(row, index) in rows"
                                        :key="row.reseller_id ?? index"
                                    >

                                        <tr
                                            class="
                                                cursor-pointer
                                                select-none
                                                text-sm
                                                text-gray-700
                                            "
                                            :class="
                                                selectedRow === row
                                                    ? 'bg-[#2563EB] text-white'
                                                    : 'bg-white'
                                            "
                                            @click="selectRow(row)"
                                        >

                                            <td class="px-3 py-3">
                                                {{ index + 1 }}
                                            </td>

                                            <td class="px-3 py-3 font-medium">
                                                {{ row.reseller }}
                                            </td>

                                            <td class="px-3 py-3 text-right">
                                                {{ formatNumber(row.qty) }}
                                            </td>

                                            <td class="px-3 py-3 text-right">
                                                {{ formatCurrency(row.sales) }}
                                            </td>

                                            <td class="px-3 py-3 text-right">
                                                {{ formatCurrency(row.hpp) }}
                                            </td>

                                            <td class="px-3 py-3 text-right">
                                                {{ formatCurrency(row.profit) }}
                                            </td>

                                            <td class="px-3 py-3 text-right">
                                                {{ formatNumber(row.margin) }}%
                                            </td>

                                        </tr>


                                        <!-- Product Detail -->

                                        <tr
                                            v-if="selectedRow === row"
                                            class="bg-white"
                                        >

                                            <td
                                                colspan="7"
                                                class="px-3 py-4"
                                            >

                                                <div
                                                    class="
                                                        rounded-lg
                                                        border
                                                        border-gray-200
                                                        bg-white
                                                        p-4
                                                    "
                                                >

                                                    <div
                                                        class="
                                                            mb-3
                                                            text-sm
                                                            font-semibold
                                                            text-gray-800
                                                        "
                                                    >
                                                        Product Detail
                                                    </div>

                                                    <div class="overflow-x-auto">

                                                        <table class="w-full">

                                                            <thead>

                                                                <tr
                                                                    class="
                                                                        border-b
                                                                        border-black
                                                                        text-left
                                                                        text-xs
                                                                        font-semibold
                                                                        uppercase
                                                                        tracking-wide
                                                                        text-gray-700
                                                                    "
                                                                >

                                                                    <th class="px-3 py-2">
                                                                        Product
                                                                    </th>

                                                                    <th class="px-3 py-2">
                                                                        Unit
                                                                    </th>

                                                                    <th class="px-3 py-2 text-right">
                                                                        Sold
                                                                    </th>

                                                                    <th class="px-3 py-2 text-right">
                                                                        Price
                                                                    </th>

                                                                    <th class="px-3 py-2 text-right">
                                                                        Sales
                                                                    </th>

                                                                    <th class="px-3 py-2 text-right">
                                                                        HPP
                                                                    </th>

                                                                    <th class="px-3 py-2 text-right">
                                                                        Profit
                                                                    </th>

                                                                    <th class="px-3 py-2 text-right">
                                                                        Margin
                                                                    </th>

                                                                </tr>

                                                            </thead>

                                                            <tbody>

                                                                <tr
                                                                    v-for="(
                                                                        detail,
                                                                        detailIndex
                                                                    ) in row.details"
                                                                    :key="
                                                                        `${row.reseller_id}-${detail.product_variant_id}-${detail.unit_id ?? detailIndex}`
                                                                    "
                                                                    class="
                                                                        text-sm
                                                                        text-gray-700
                                                                    "
                                                                >

                                                                    <td class="px-3 py-2">
                                                                        {{ detail.product }}
                                                                    </td>

                                                                    <td class="px-3 py-2">
                                                                        {{ detail.unit }}
                                                                    </td>

                                                                    <td class="px-3 py-2 text-right">
                                                                        {{ formatNumber(detail.qty) }}
                                                                    </td>

                                                                    <td class="px-3 py-2 text-right">
                                                                        {{ formatCurrency(detail.unit_price) }}
                                                                    </td>

                                                                    <td class="px-3 py-2 text-right">
                                                                        {{ formatCurrency(detail.sales) }}
                                                                    </td>

                                                                    <td class="px-3 py-2 text-right">
                                                                        {{ formatCurrency(detail.hpp) }}
                                                                    </td>

                                                                    <td class="px-3 py-2 text-right">
                                                                        {{ formatCurrency(detail.profit) }}
                                                                    </td>

                                                                    <td class="px-3 py-2 text-right">
                                                                        {{ formatNumber(detail.margin) }}%
                                                                    </td>

                                                                </tr>

                                                            </tbody>

                                                        </table>

                                                    </div>

                                                </div>

                                            </td>

                                        </tr>

                                    </template>

                                </tbody>

                                <tfoot>

                                    <tr
                                        class="
                                            border-t
                                            border-black
                                            text-sm
                                            font-semibold
                                            text-gray-800
                                        "
                                    >

                                        <td class="px-3 py-3" colspan="2">
                                            Total
                                        </td>

                                        <td class="px-3 py-3 text-right">
                                            {{ formatNumber(summary.qty) }}
                                        </td>

                                        <td class="px-3 py-3 text-right">
                                            {{ formatCurrency(summary.sales) }}
                                        </td>

                                        <td class="px-3 py-3 text-right">
                                            {{ formatCurrency(summary.hpp) }}
                                        </td>

                                        <td class="px-3 py-3 text-right">
                                            {{ formatCurrency(summary.profit) }}
                                        </td>

                                        <td class="px-3 py-3 text-right">
                                            {{ formatNumber(summary.margin) }}%
                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- STANDARD REPORTS -->
                <!-- ================================================== -->
<!-- ============================================================
     ALL ACTIVITY
     ============================================================ -->
<div v-else-if="report.type === 'all_activity'">

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr
                    class="
                        border-b
                        border-black
                        text-left
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wide
                        text-gray-700
                    "
                >

                    <th class="px-4 py-3">
                        No
                    </th>

                    <th class="px-4 py-3">
                        Date
                    </th>

                    <th class="px-4 py-3">
                        Activity
                    </th>

                    <th class="px-4 py-3">
                        Number
                    </th>

                    <th class="px-4 py-3">
                        Reseller
                    </th>

                    <th class="px-4 py-3">
                        Product
                    </th>

                    <th class="px-4 py-3 text-right">
                        Qty
                    </th>

                    <th class="px-4 py-3 text-right">
                        Amount
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="(row, index) in rows"
                    :key="`${row.number}-${index}`"
                    class="
                        cursor-pointer
                        select-none
                        text-sm
                    "
                    :class="
                        selectedRow === row
                            ? 'bg-[#2563EB] text-white'
                            : 'bg-white text-gray-700'
                    "
                    @click="selectRow(row)"
                >

                    <td class="px-4 py-3">
                        {{ index + 1 }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ row.date }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap font-medium">
                        {{ row.activity }}
                    </td>

                    <td class="px-4 py-3 whitespace-nowrap font-medium">
                        {{ row.number }}
                    </td>

                    <td class="px-4 py-3">
                        {{ row.reseller }}
                    </td>

                    <td class="px-4 py-3">
                        {{ row.product ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        {{ row.qty_label ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        {{ formatCurrency(row.amount) }}
                    </td>

                </tr>

            </tbody>

            <tfoot>

                <tr
                    class="
                        border-t
                        border-black
                        text-sm
                        font-semibold
                        text-gray-800
                    "
                >

                    <td
                        colspan="6"
                        class="px-4 py-3 text-right"
                    >
                        Summary
                    </td>

                    <td class="px-4 py-3 text-right">

                        <div>
                            Out:
                            {{ formatNumber(summary.consignment_out_qty) }}
                        </div>

                        <div>
                            Sold:
                            {{ formatNumber(summary.sold_qty) }}
                        </div>

                        <div>
                            Return:
                            {{ formatNumber(summary.return_qty) }}
                        </div>

                    </td>

                    <td class="px-4 py-3 text-right">

                        {{ formatCurrency(summary.payment_amount) }}

                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</div>


<!-- ============================================================
     CONSIGNMENT OUT
     ============================================================ -->
<div v-else-if="report.type === 'consignment_out'">

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr
                    class="
                        border-b
                        border-black
                        text-left
                        text-xs
                        font-semibold
                        uppercase
                        tracking-wide
                        text-gray-700
                    "
                >

                    <th class="px-4 py-3">
                        No
                    </th>

                    <th class="px-4 py-3">
                        Date
                    </th>

                    <th class="px-4 py-3">
                        Number
                    </th>

                    <th class="px-4 py-3">
                        Reseller
                    </th>

                    <th class="px-4 py-3">
                        Product
                    </th>

                    <th class="px-4 py-3 text-right">
                        Qty
                    </th>

                    <th class="px-4 py-3 text-right">
                        Price
                    </th>

                    <th class="px-4 py-3 text-right">
                        Total
                    </th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="(row, index) in rows"
                    :key="index"
                    class="
                        cursor-pointer
                        select-none
                        text-sm
                        text-gray-700
                    "
                    :class="
                        selectedRow === row
                            ? 'bg-[#2563EB] text-white'
                            : 'bg-white'
                    "
                    @click="selectRow(row)"
                >

                    <td class="px-4 py-3">
                        {{ index + 1 }}
                    </td>

                    <td class="px-4 py-3">
                        {{ row.date }}
                    </td>

                    <td class="px-4 py-3 font-medium">
                        {{ row.number }}
                    </td>

                    <td class="px-4 py-3">
                        {{ row.reseller }}
                    </td>

                    <td class="px-4 py-3">
                        {{ row.product }}
                    </td>

                    <td class="px-4 py-3 text-right">
                        {{ formatNumber(row.qty) }}
                    </td>

                    <td class="px-4 py-3 text-right">
                        {{ formatCurrency(row.unit_price) }}
                    </td>

                    <td class="px-4 py-3 text-right">
                        {{ formatCurrency(row.total) }}
                    </td>

                </tr>

            </tbody>

            <tfoot>

                <tr
                    class="
                        border-t
                        border-black
                        text-sm
                        font-semibold
                        text-gray-800
                    "
                >

                    <td
                        colspan="5"
                        class="px-4 py-3"
                    >
                        Total
                    </td>

                    <td class="px-4 py-3 text-right">
                        {{ formatNumber(summary.qty) }}
                    </td>

                    <td></td>

                    <td class="px-4 py-3 text-right">
                        {{ formatCurrency(summary.amount ?? summary.total) }}
                    </td>

                </tr>

            </tfoot>

        </table>

    </div>

</div>
                


                <!-- ================================================== -->
                <!-- SALES SETTLEMENT -->
                <!-- ================================================== -->

                <div
                    v-else-if="report.type === 'sales_settlement'"
                >

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-black
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-700
                                    "
                                >

                                    <th class="px-4 py-3">
                                        No
                                    </th>

                                    <th class="px-4 py-3">
                                        Date
                                    </th>

                                    <th class="px-4 py-3">
                                        Number
                                    </th>

                                    <th class="px-4 py-3">
                                        Reseller
                                    </th>

                                    <th class="px-4 py-3">
                                        Product
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Sold
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Price
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Sales
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    class="
                                        cursor-pointer
                                        select-none
                                        text-sm
                                        text-gray-700
                                    "
                                    :class="
                                        selectedRow === row
                                            ? 'bg-[#2563EB] text-white'
                                            : 'bg-white'
                                    "
                                    @click="selectRow(row)"
                                >

                                    <td class="px-4 py-3">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.date }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ row.number }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.reseller }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.product }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(row.qty) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.unit_price) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.sales) }}
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr
                                    class="
                                        border-t
                                        border-black
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >

                                    <td
                                        colspan="5"
                                        class="px-4 py-3"
                                    >
                                        Total
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(summary.qty) }}
                                    </td>

                                    <td></td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.sales) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- RECEIVABLE PAYMENT -->
                <!-- ================================================== -->

                <div
                    v-else-if="report.type === 'receivable_payment'"
                >

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-black
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-700
                                    "
                                >

                                    <th class="px-4 py-3">
                                        No
                                    </th>

                                    <th class="px-4 py-3">
                                        Date
                                    </th>

                                    <th class="px-4 py-3">
                                        Number
                                    </th>

                                    <th class="px-4 py-3">
                                        Reseller
                                    </th>

                                    <th class="px-4 py-3">
                                        Settlement
                                    </th>

                                    <th class="px-4 py-3">
                                        Payment Method
                                    </th>

                                    <th class="px-4 py-3">
                                        Account
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Payment
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    class="
                                        cursor-pointer
                                        select-none
                                        text-sm
                                        text-gray-700
                                    "
                                    :class="
                                        selectedRow === row
                                            ? 'bg-[#2563EB] text-white'
                                            : 'bg-white'
                                    "
                                    @click="selectRow(row)"
                                >

                                    <td class="px-4 py-3">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.date }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ row.number }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.reseller }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.settlement_number }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.payment_method }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.payment_account }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.payment) }}
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr
                                    class="
                                        border-t
                                        border-black
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >

                                    <td
                                        colspan="7"
                                        class="px-4 py-3"
                                    >
                                        Total
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.payment) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- CONSIGNMENT RETURN -->
                <!-- ================================================== -->

                <div
                    v-else-if="report.type === 'consignment_return'"
                >

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-black
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-700
                                    "
                                >

                                    <th class="px-4 py-3">
                                        No
                                    </th>

                                    <th class="px-4 py-3">
                                        Date
                                    </th>

                                    <th class="px-4 py-3">
                                        Number
                                    </th>

                                    <th class="px-4 py-3">
                                        Reseller
                                    </th>

                                    <th class="px-4 py-3">
                                        Product
                                    </th>

                                    <th class="px-4 py-3">
                                        Unit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Qty
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        HPP
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    class="
                                        cursor-pointer
                                        select-none
                                        text-sm
                                        text-gray-700
                                    "
                                    :class="
                                        selectedRow === row
                                            ? 'bg-[#2563EB] text-white'
                                            : 'bg-white'
                                    "
                                    @click="selectRow(row)"
                                >

                                    <td class="px-4 py-3">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.date }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ row.number }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.reseller }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.product }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.unit }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(row.qty) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.total_cost) }}
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr
                                    class="
                                        border-t
                                        border-black
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >

                                    <td
                                        colspan="6"
                                        class="px-4 py-3"
                                    >
                                        Total
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(summary.qty) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.total_cost) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- STOCK POSITION -->
                <!-- ================================================== -->

                <div
                    v-else-if="report.type === 'stock_position'"
                >

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-black
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-700
                                    "
                                >

                                    <th class="px-4 py-3">
                                        No
                                    </th>

                                    <th class="px-4 py-3">
                                        Reseller
                                    </th>

                                    <th class="px-4 py-3">
                                        Branch
                                    </th>

                                    <th class="px-4 py-3">
                                        Product
                                    </th>

                                    <th class="px-4 py-3">
                                        Unit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        On Hand
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Available
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Price
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Stock Value
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    class="
                                        cursor-pointer
                                        select-none
                                        text-sm
                                        text-gray-700
                                    "
                                    :class="
                                        selectedRow === row
                                            ? 'bg-[#2563EB] text-white'
                                            : 'bg-white'
                                    "
                                    @click="selectRow(row)"
                                >

                                    <td class="px-4 py-3">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ row.reseller }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.branch }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.product }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.unit }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(row.on_hand) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(row.available) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.consignment_price) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.stock_value) }}
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr
                                    class="
                                        border-t
                                        border-black
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >

                                    <td
                                        colspan="5"
                                        class="px-4 py-3"
                                    >
                                        Total
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(summary.qty) }}
                                    </td>

                                    <td></td>

                                    <td></td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.stock_value) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>
                <!-- ================================================== -->
                <!-- RECEIVABLE -->
                <!-- ================================================== -->

                <div
                    v-else-if="report.type === 'receivable'"
                >

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-black
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-700
                                    "
                                >

                                    <th class="px-4 py-3">
                                        No
                                    </th>

                                    <th class="px-4 py-3">
                                        Date
                                    </th>

                                    <th class="px-4 py-3">
                                        Type
                                    </th>

                                    <th class="px-4 py-3">
                                        Number
                                    </th>

                                    <th class="px-4 py-3">
                                        Reseller
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Debit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Credit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Balance
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    class="
                                        cursor-pointer
                                        select-none
                                        text-sm
                                        text-gray-700
                                    "
                                    :class="
                                        selectedRow === row
                                            ? 'bg-[#2563EB] text-white'
                                            : 'bg-white'
                                    "
                                    @click="selectRow(row)"
                                >

                                    <td class="px-4 py-3">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.date }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.type }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ row.number }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.reseller }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.debit) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.credit) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.balance) }}
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr
                                    class="
                                        border-t
                                        border-black
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >

                                    <td
                                        colspan="5"
                                        class="px-4 py-3"
                                    >
                                        Total
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.debit) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.credit) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.outstanding) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- PROFIT ANALYSIS -->
                <!-- ================================================== -->

                <div
                    v-else-if="report.type === 'profit_analysis'"
                >

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-black
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-700
                                    "
                                >

                                    <th class="px-4 py-3">
                                        No
                                    </th>

                                    <th class="px-4 py-3">
                                        Reseller
                                    </th>

                                    <th class="px-4 py-3">
                                        Product
                                    </th>

                                    <th class="px-4 py-3">
                                        Unit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Sold
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Price
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Sales
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        HPP
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Profit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Margin
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr
                                    v-for="(row, index) in rows"
                                    :key="index"
                                    class="
                                        cursor-pointer
                                        select-none
                                        text-sm
                                        text-gray-700
                                    "
                                    :class="
                                        selectedRow === row
                                            ? 'bg-[#2563EB] text-white'
                                            : 'bg-white'
                                    "
                                    @click="selectRow(row)"
                                >

                                    <td class="px-4 py-3">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        {{ row.reseller }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.product }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ row.unit }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(row.qty) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.unit_price) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.sales) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.hpp) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(row.profit) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(row.margin) }}%
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr
                                    class="
                                        border-t
                                        border-black
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >

                                    <td
                                        colspan="4"
                                        class="px-4 py-3"
                                    >
                                        Total
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(summary.qty) }}
                                    </td>

                                    <td></td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.sales) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.hpp) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatCurrency(summary.profit) }}
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        {{ formatNumber(summary.margin) }}%
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>


                <!-- ================================================== -->
                <!-- EMPTY -->
                <!-- ================================================== -->

                <div
                    v-if="rows.length === 0"
                    class="
                        px-6
                        py-12
                        text-center
                        text-sm
                        text-gray-500
                    "
                >
                    No data found.
                </div>

            </div>

        </div>

    </AppLayout>

</template>

                