<script setup>
import {
    computed,
    ref,
    nextTick,
} from 'vue'

import { router } from '@inertiajs/vue3'

import SearchableSelect
    from '@/Components/Form/SearchableSelect.vue'

import AppLayout
    from '@/Layouts/AppLayout.vue'


const props = defineProps({

    report: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    suppliers: {
        type: Array,
        default: () => [],
    },

    branches: {
        type: Array,
        default: () => [],
    },

})


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const supplierId = ref(
    props.filters.supplier_id ?? ''
)

const branchId = ref(
    props.filters.branch_id ?? ''
)

const dateFrom = ref(
    props.filters.date_from ?? ''
)

const dateTo = ref(
    props.filters.date_to ?? ''
)


/*
|--------------------------------------------------------------------------
| Report Data
|--------------------------------------------------------------------------
*/

const rows = computed(() =>
    props.report?.rows ?? []
)

const totals = computed(() =>
    props.report?.totals ?? {
        debit: 0,
        credit: 0,
    }
)

const openingBalance = computed(() =>
    Number(
        props.report?.opening_balance ?? 0
    )
)

const closingBalance = computed(() =>
    Number(
        props.report?.totals?.closing_balance ?? 0
    )
)
console.log(
    'SUPPLIER STATEMENT REPORT:',
    props.report
)

/*
|--------------------------------------------------------------------------
| Format
|--------------------------------------------------------------------------
*/

const formatAmount = (amount) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(amount ?? 0)
    )
}


const formatDate = (date) => {

    if (!date) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }
    ).format(
        new Date(date)
    )
}


/*
|--------------------------------------------------------------------------
| Supplier
|--------------------------------------------------------------------------
*/

const supplierName = computed(() => {

    return (
        props.report?.supplier?.name ??
        '-'
    )

})


const supplierCode = computed(() => {

    return (
        props.report?.supplier?.code ??
        ''
    )

})


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {

    router.get(
        route(
            'reports.supplier-statement'
        ),
        {
            branch_id:
                branchId.value ||
                undefined,

            supplier_id:
                supplierId.value ||
                undefined,

            date_from:
                dateFrom.value ||
                undefined,

            date_to:
                dateTo.value ||
                undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const activeSummary = ref(
    'closing_balance'
)

const tableSection = ref(null)


const summaryCards = computed(() => [

    {
        key: 'closing_balance',

        label: 'Closing Balance',

        value:
            closingBalance.value,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-gray-500',
    },

    {
        key: 'opening_balance',

        label: 'Opening Balance',

        value:
            openingBalance.value,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-blue-500',
    },

    {
        key: 'debit',

        label: 'Total Debit',

        value:
            totals.value.debit,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-emerald-500',
    },

    {
        key: 'credit',

        label: 'Total Credit',

        value:
            totals.value.credit,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-yellow-500',
    },

])


/*
|--------------------------------------------------------------------------
| Summary Selection
|--------------------------------------------------------------------------
*/

const selectSummary = async (
    key
) => {

    activeSummary.value =
        key

    await nextTick()

    tableSection.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })

}


/*
|--------------------------------------------------------------------------
| Print / Export
|--------------------------------------------------------------------------
*/

const printReport = () => {

    window.print()

}


const exportPdf = () => {

    window.location.href =
        route(
            'reports.supplier-statement.pdf',
            {
                branch_id:
                    branchId.value ||
                    undefined,

                supplier_id:
                    supplierId.value ||
                    undefined,

                date_from:
                    dateFrom.value ||
                    undefined,

                date_to:
                    dateTo.value ||
                    undefined,
            }
        )

}


const exportExcel = () => {

    window.location.href =
        route(
            'reports.supplier-statement.excel',
            {
                branch_id:
                    branchId.value ||
                    undefined,

                supplier_id:
                    supplierId.value ||
                    undefined,

                date_from:
                    dateFrom.value ||
                    undefined,

                date_to:
                    dateTo.value ||
                    undefined,
            }
        )

}

</script>


<template>

<AppLayout>

    <div class="space-y-4">


        <!-- Header -->

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
                    Supplier Statement
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Supplier payable statement
                    from
                    {{ formatDate(report.date_from) }}
                    to
                    {{ formatDate(report.date_to) }}
                </p>

            </div>


            <!-- Actions -->

            <div
                class="
                    flex
                    items-center
                    gap-2
                    print:hidden
                "
            >

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
                    @click="printReport"
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
                    @click="exportPdf"
                >

                    <svg
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                    >

                        <path
                            fill="#E53935"
                            d="
                                M7 2h13l7 7v21H7z
                            "
                        />

                        <path
                            fill="#fff"
                            d="
                                M20 2v7h7
                            "
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
                    @click="exportExcel"
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


        <!-- Supplier Info -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
                shadow-sm
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Supplier
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        <span
                            v-if="supplierCode"
                            class="mr-1"
                        >
                            {{ supplierCode }} -
                        </span>

                        {{ supplierName }}
                    </div>

                </div>


                <div
                    class="
                        text-sm
                        text-gray-500
                    "
                >
                    {{ formatDate(report.date_from) }}
                    –
                    {{ formatDate(report.date_to) }}
                </div>

            </div>

        </div>


        <!-- Filter -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
                print:hidden
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-3
                    md:grid-cols-2
                    lg:grid-cols-5
                "
            >

                <!-- Date From -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Date From
                    </label>

                    <input
                        v-model="dateFrom"
                        type="date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                    />

                </div>


                <!-- Date To -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Date To
                    </label>

                    <input
                        v-model="dateTo"
                        type="date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                    />

                </div>


                <!-- Supplier -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Supplier
                    </label>

                    <SearchableSelect
                        v-model="supplierId"
                        :options="suppliers"
                        label="label"
                        value-key="id"
                        placeholder="Select Supplier"
                    />

                </div>


                <!-- Branch -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Branch
                    </label>

                    <SearchableSelect
                        v-model="branchId"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                    />

                </div>


                <!-- Apply -->

                <div
                    class="
                        flex
                        items-end
                    "
                >

                    <button
                        type="button"
                        class="
                            w-full
                            rounded-lg
                            bg-gray-900
                            px-4
                            py-2
                            text-sm
                            font-medium
                            text-white
                            transition
                            hover:bg-gray-800
                        "
                        @click="applyFilter"
                    >
                        Apply
                    </button>

                </div>

            </div>

        </div>


        <!-- Summary -->

        <div
            class="
                print-summary
                rounded-xl
                border
                border-gray-100
                bg-white
                p-3
                shadow-sm
                print:rounded-none
                print:border-0
                print:p-0
                print:shadow-none
            "
        >

            <div
                class="
                    grid
                    grid-cols-2
                    gap-2
                    sm:grid-cols-4
                "
            >

                <button
                    v-for="card in summaryCards"
                    :key="card.key"
                    type="button"
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        px-4
                        py-3
                        text-left
                        transition
                        hover:-translate-y-px
                        hover:shadow-sm
                    "
                    :class="[
                        card.classes,

                        activeSummary === card.key
                            ? 'ring-2 ring-gray-300 ring-offset-1'
                            : '',
                    ]"
                    @click="selectSummary(card.key)"
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                        "
                        :class="card.accent"
                    ></span>


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
                            text-base
                            font-semibold
                            tabular-nums
                            text-gray-900
                        "
                    >
                        Rp
                        {{ formatAmount(card.value) }}
                    </div>

                </button>

            </div>

        </div>


        <!-- Table -->

        <div
            ref="tableSection"
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
                print:overflow-visible
                print:rounded-none
                print:border-0
                print:shadow-none
            "
        >

            <div class="overflow-x-auto">

                <table
                    class="
                        print-table
                        w-full
                        text-sm
                    "
                >

                    <thead>

                        <tr
                            class="
                                border-b
                                border-gray-200
                            "
                        >

                            <th
                                class="
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Date
                            </th>


                            <th
                                class="
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Document
                            </th>


                            <th
                                class="
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Type
                            </th>


                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Debit
                            </th>


                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Credit
                            </th>


                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Balance
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <!-- Opening -->

                        <tr
                            class="
                                border-b
                                border-gray-100
                            "
                        >

                            <td
                                colspan="3"
                                class="
                                    px-4
                                    py-3
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Opening Balance
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    tabular-nums
                                    text-gray-500
                                "
                            >
                                -
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    tabular-nums
                                    text-gray-500
                                "
                            >
                                -
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(openingBalance) }}
                            </td>

                        </tr>


                        <!-- Transactions -->

                        <tr
                            v-for="row in rows"
                            :key="
                                row.type +
                                '-' +
                                row.document +
                                '-' +
                                row.date
                            "
                            class="
                                border-b
                                border-gray-50
                                last:border-b-0
                            "
                        >

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-gray-700
                                "
                            >
                                {{ formatDate(row.date) }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{ row.document }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-gray-700
                                "
                            >
                                {{ row.type }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    tabular-nums
                                    text-gray-900
                                "
                            >

                                <span
                                    v-if="
                                        Number(row.debit ?? 0) !== 0
                                    "
                                >
                                    Rp
                                    {{ formatAmount(row.debit) }}
                                </span>

                                <span
                                    v-else
                                    class="text-gray-400"
                                >
                                    -
                                </span>

                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    tabular-nums
                                    text-gray-900
                                "
                            >

                                <span
                                    v-if="
                                        Number(row.credit ?? 0) !== 0
                                    "
                                >
                                    Rp
                                    {{ formatAmount(row.credit) }}
                                </span>

                                <span
                                    v-else
                                    class="text-gray-400"
                                >
                                    -
                                </span>

                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-medium
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(row.balance) }}
                            </td>

                        </tr>


                        <!-- Empty -->

                        <tr
                            v-if="rows.length === 0"
                        >

                            <td
                                colspan="6"
                                class="
                                    px-4
                                    py-10
                                    text-center
                                    text-sm
                                    text-gray-500
                                "
                            >
                                No supplier transactions found.
                            </td>

                        </tr>


                        <!-- Period Total -->

                        <tr
                            class="
                                border-t
                                border-gray-300
                            "
                        >

                            <td
                                colspan="3"
                                class="
                                    px-4
                                    py-3
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Period Total
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{
                                    formatAmount(
                                        totals.debit
                                    )
                                }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{
                                    formatAmount(
                                        totals.credit
                                    )
                                }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                -
                            </td>

                        </tr>


                        <!-- Closing -->

                        <tr
                            class="
                                border-t
                                border-gray-300
                            "
                        >

                            <td
                                colspan="5"
                                class="
                                    px-4
                                    py-3
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Closing Balance
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(closingBalance) }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</AppLayout>

</template>


<style>

@media print {

    @page {
        size: A4 landscape;
        margin: 12mm;
    }


    body {
        background: white !important;
    }


    .print-summary {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    .print-summary-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    .print-table {
        width: 100% !important;
    }


    .print-table tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

}

</style>