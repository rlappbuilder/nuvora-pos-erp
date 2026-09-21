<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
//import LoadingOverlay from '@/Components/LoadingOverlay.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Reseller Mutation',
    },

    report: {
        type: Object,
        default: () => ({
            type: 'reseller_mutation',
            title: 'Reseller Mutation',
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
| Filters
|--------------------------------------------------------------------------
*/

const dateFrom = ref(props.filters?.date_from ?? '')
const dateTo = ref(props.filters?.date_to ?? '')
const resellerId = ref(props.filters?.reseller_id ?? null)
const branchId = ref(props.filters?.branch_id ?? null)

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const selectedRow = ref(null)

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const rows = computed(() => props.report?.rows ?? [])

const selectedReseller = computed(() => {
    return props.resellers.find(
        item => String(item.id) === String(resellerId.value)
    )
})

/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(date)
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

/*
|--------------------------------------------------------------------------
| Row Selection
|--------------------------------------------------------------------------
*/

const selectRow = (row) => {
    if (selectedRow.value === row) {
        selectedRow.value = null
        return
    }

    selectedRow.value = row
}

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {
    if (!resellerId.value) {
        return
    }

    loading.value = true

    router.get(
        route('reseller-mutations.index'),
        {
            reseller_id: resellerId.value,
            branch_id: branchId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

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

const refreshReport = () => {
    dateFrom.value = ''
    dateTo.value = ''
    resellerId.value = null
    branchId.value = null
    selectedRow.value = null

    loading.value = true

    router.get(
        route('reseller-mutations.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                loading.value = false
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

const printReport = () => {
    window.open(
        route('reseller-mutations.print', {
            reseller_id: resellerId.value,
            branch_id: branchId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        }),
        '_blank'
    )
}

/*
|--------------------------------------------------------------------------
| PDF
|--------------------------------------------------------------------------
*/

const exportPdf = () => {
    window.open(
        route('reseller-mutations.pdf', {
            reseller_id: resellerId.value,
            branch_id: branchId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        }),
        '_blank'
    )
}

/*
|--------------------------------------------------------------------------
| Excel
|--------------------------------------------------------------------------
*/

const exportExcel = () => {
    window.open(
        route('reseller-mutations.excel', {
            reseller_id: resellerId.value,
            branch_id: branchId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        }),
        '_blank'
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
                        Reseller Mutation
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Reseller stock mutation activity
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
                        @click="refreshReport"
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


            <!-- Mutation Info -->

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
                            Reseller Mutation
                        </div>

                        <div
                            class="
                                mt-0.5
                                text-sm
                                font-semibold
                                text-gray-900
                            "
                        >
                            {{
                                selectedReseller?.label ??
                                'Select Reseller'
                            }}
                        </div>

                    </div>


                    <div
                        class="
                            text-sm
                            text-gray-500
                        "
                    >

                        <template
                            v-if="dateFrom || dateTo"
                        >

                            {{ formatDate(dateFrom) }}
                            –
                            {{ formatDate(dateTo) }}

                        </template>

                        <template v-else>

                            All Data

                        </template>

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


                    <!-- Reseller -->

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
                            Reseller
                        </label>

                        <SearchableSelect
                            v-model="resellerId"
                            :options="resellers"
                            label="label"
                            value-key="id"
                            placeholder="Select Reseller"
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


            <!-- Mutation Table -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    shadow-sm
                "
            >

                <LoadingOverlay :show="loading" />

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[1100px]">

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
                                    Date
                                </th>

                                <th class="px-4 py-3">
                                    Reference
                                </th>

                                <th class="px-4 py-3">
                                    Description
                                </th>

                                <th class="px-4 py-3 text-right">
                                    In
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Out
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Price
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Debit
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Credit
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Balance Qty
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Balance Value
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-if="rows.length === 0"
                            >

                                <td
                                    colspan="10"
                                    class="
                                        px-4
                                        py-10
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    No mutation data found.
                                </td>

                            </tr>


                            <tr
                                v-for="(row, index) in rows"
                                :key="`${row.reference}-${row.date}-${index}`"
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

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-4
                                        py-3
                                    "
                                >
                                    {{ formatDate(row.date) }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-4
                                        py-3
                                        font-medium
                                    "
                                >
                                    {{ row.reference ?? '-' }}
                                </td>


                                <td class="px-4 py-3">
                                    {{ row.description ?? '-' }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        tabular-nums
                                    "
                                >
                                    {{
                                        Number(row.in ?? 0) === 0
                                            ? '-'
                                            : formatNumber(row.in)
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        tabular-nums
                                    "
                                >
                                    {{
                                        Number(row.out ?? 0) === 0
                                            ? '-'
                                            : formatNumber(row.out)
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        tabular-nums
                                    "
                                >
                                    {{
                                        row.price === null ||
                                        row.price === undefined
                                            ? '-'
                                            : formatNumber(row.price)
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        tabular-nums
                                    "
                                >
                                    {{
                                        Number(row.debit ?? 0) === 0
                                            ? '-'
                                            : formatCurrency(row.debit)
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        tabular-nums
                                    "
                                >
                                    {{
                                        Number(row.credit ?? 0) === 0
                                            ? '-'
                                            : formatCurrency(row.credit)
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                    "
                                >
                                    {{ formatNumber(row.balance_qty) }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                    "
                                >
                                    {{ formatCurrency(row.balance_value) }}
                                </td>

                            </tr>

                        </tbody>


                        <!-- Ending Balance -->

                        <tfoot
                            v-if="rows.length > 0"
                        >

                            <tr class="border-t border-black">

                                <td
                                    colspan="8"
                                    class="
                                        px-4
                                        py-4
                                        text-right
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                    "
                                >
                                    Ending Balance
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-right
                                        text-sm
                                        font-semibold
                                        text-gray-900
                                        tabular-nums
                                    "
                                >
                                    {{
                                        formatNumber(
                                            rows[rows.length - 1]
                                                ?.balance_qty
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-right
                                        text-sm
                                        font-semibold
                                        text-gray-900
                                        tabular-nums
                                    "
                                >
                                    {{
                                        formatCurrency(
                                            rows[rows.length - 1]
                                                ?.balance_value
                                        )
                                    }}
                                </td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>

    </AppLayout>
</template>