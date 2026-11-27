<script setup>
import {
    ref,
    reactive,
    computed,
    watch,
    toRefs,
    nextTick,
} from 'vue'

import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'

import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'

import TablePagination from '@/Components/Table/TablePagination.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import { currency, formatDate } from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import { LoadingOverlay } from '@/Components/Feedback'

import {
    FunnelIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    stockCard: {
        type: Object,

        default: () => ({
            summary: {
                opening_qty: 0,
                total_qty_in: 0,
                total_qty_out: 0,
                closing_qty: 0,
            },

            data: [],

            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        }),
    },

    branches: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    variants: {
        type: Array,
        default: () => [],
    },

    units: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

})


const {
    stockCard,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Filter Visibility
|--------------------------------------------------------------------------
*/

const showFilters = ref(false)

const suspendFilterWatchers = ref(false)

const skipWarehouseWatch = ref(false)


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    branch_id:
        props.filters?.branch_id ?? '',

    warehouse_id:
        props.filters?.warehouse_id ?? '',

    product_variant_id:
        props.filters?.product_variant_id ?? '',

    unit_id:
        props.filters?.unit_id ?? '',

})


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const dateRange = ref(
    filters.date_from && filters.date_to
        ? `${filters.date_from} to ${filters.date_to}`
        : filters.date_from || filters.date_to || ''
)


/*
|--------------------------------------------------------------------------
| Active Filter
|--------------------------------------------------------------------------
*/

const hasActiveFilter = computed(() => {

    return Boolean(
        filters.date_from ||
        filters.date_to ||
        filters.branch_id ||
        filters.warehouse_id ||
        filters.product_variant_id ||
        filters.unit_id
    )

})


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData() {

    router.get(

        route('stock-card.index'),

        {
            ...filters,
            page: 1,
        },

        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onStart: () => {
                loading.value = true
            },

            onFinish: () => {
                loading.value = false
            },
        }

    )

}


/*
|--------------------------------------------------------------------------
| Branch Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => filters.branch_id,

    (newValue, oldValue) => {

        if (
            suspendFilterWatchers.value ||
            newValue === oldValue
        ) {
            return
        }

        /*
        |--------------------------------------------------------------------------
        | Reset Warehouse When Branch Changes
        |--------------------------------------------------------------------------
        */

        if (filters.warehouse_id) {

            skipWarehouseWatch.value = true

            filters.warehouse_id = ''

        }

        loadData()

    }
)


/*
|--------------------------------------------------------------------------
| Warehouse Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => filters.warehouse_id,

    (newValue, oldValue) => {

        if (
            suspendFilterWatchers.value ||
            newValue === oldValue
        ) {
            return
        }

        if (skipWarehouseWatch.value) {

            skipWarehouseWatch.value = false

            return

        }

        loadData()

    }
)


/*
|--------------------------------------------------------------------------
| Product Variant Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => filters.product_variant_id,

    (newValue, oldValue) => {

        if (
            suspendFilterWatchers.value ||
            newValue === oldValue
        ) {
            return
        }

        loadData()

    }
)


/*
|--------------------------------------------------------------------------
| Unit Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => filters.unit_id,

    (newValue, oldValue) => {

        if (
            suspendFilterWatchers.value ||
            newValue === oldValue
        ) {
            return
        }

        loadData()

    }
)


/*
|--------------------------------------------------------------------------
| Filtered Warehouses
|--------------------------------------------------------------------------
*/

const filteredWarehouses = computed(() => {

    if (!filters.branch_id) {
        return props.warehouses
    }

    return props.warehouses.filter(

        warehouse =>

            Number(warehouse.branch_id)
            ===
            Number(filters.branch_id)

    )

})


/*
|--------------------------------------------------------------------------
| Date Formatting
|--------------------------------------------------------------------------
*/

function formatDateForFilter(date) {

    const year = date.getFullYear()

    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0')

    const day = String(
        date.getDate()
    ).padStart(2, '0')

    return `${year}-${month}-${day}`

}


/*
|--------------------------------------------------------------------------
| Date Range Change
|--------------------------------------------------------------------------
*/

function handleDateRangeChange(selectedDates) {

    if (!selectedDates.length) {

        filters.date_from = ''
        filters.date_to = ''

        loadData()

        return

    }

    filters.date_from = formatDateForFilter(
        selectedDates[0]
    )

    filters.date_to = selectedDates.length > 1

        ? formatDateForFilter(
            selectedDates[1]
        )

        : formatDateForFilter(
            selectedDates[0]
        )

    loadData()

}


/*
|--------------------------------------------------------------------------
| Date Range Configuration
|--------------------------------------------------------------------------
*/

const dateRangeConfig = {

    mode: 'range',

    dateFormat: 'Y-m-d',

    onChange: handleDateRangeChange,

}


/*
|--------------------------------------------------------------------------
| Reset Filters
|--------------------------------------------------------------------------
*/

async function resetFilters() {

    suspendFilterWatchers.value = true

    skipWarehouseWatch.value = false

    Object.assign(filters, {

        date_from: '',
        date_to: '',

        branch_id: '',
        warehouse_id: '',

        product_variant_id: '',
        unit_id: '',

    })

    dateRange.value = ''

    await nextTick()

    suspendFilterWatchers.value = false

    loadData()

}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh() {

    resetFilters()

}


/*
|--------------------------------------------------------------------------
| Number Formatting
|--------------------------------------------------------------------------
*/

function formatNumber(value) {

    return new Intl.NumberFormat('id-ID', {

        minimumFractionDigits: 2,
        maximumFractionDigits: 2,

    }).format(
        Number(value ?? 0)
    )

}


/*
|--------------------------------------------------------------------------
| Currency Formatting
|--------------------------------------------------------------------------
*/

function formatCurrency(value) {

    return currency(
        Number(value ?? 0)
    )

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const summary = computed(

    () => stockCard.value?.summary ?? {

        opening_qty: 0,
        total_qty_in: 0,
        total_qty_out: 0,
        closing_qty: 0,

    }

)


/*
|--------------------------------------------------------------------------
| Stock Card Rows
|--------------------------------------------------------------------------
*/

const rows = computed(

    () => stockCard.value?.data ?? []

)

</script>


<template>
    <AppLayout title="Stock Card">

        <div class="space-y-6">

            <!-- ===================================================== -->
            <!-- Page Header -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-4
                    lg:flex-row
                    lg:items-center
                    lg:justify-between
                "
            >

                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Stock Card
                    </h1>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        View detailed inventory movement history and stock balance.
                    </p>
                </div>


                <!-- Date Range & Refresh -->

                <div
                    class="
                        flex
                        w-full
                        flex-col
                        gap-2
                        sm:flex-row
                        lg:w-auto
                        sm:items-center
                    "
                >

                    <div class="w-full sm:w-64">
                        <FlatPickr
                            v-model="dateRange"
                            :config="dateRangeConfig"
                            placeholder="Select date range"
                            class="
                                w-full
                                rounded-lg
                                border-gray-300
                                text-sm
                                shadow-sm
                                focus:border-blue-500
                                focus:ring-blue-500
                                dark:border-gray-700
                                dark:bg-gray-800
                                dark:text-white
                            "
                        />
                    </div>


                    <BaseButton
                        variant="secondary"
                        class="w-full shrink-0 sm:w-auto"
                        @click="refresh"
                    >
                        <span class="inline-flex items-center justify-center gap-2 whitespace-nowrap">
                            <ArrowPathIcon class="h-4 w-4 shrink-0" />
                            Refresh
                        </span>
                    </BaseButton>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Statistics -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-4
                    sm:grid-cols-2
                    xl:grid-cols-4
                "
            >

                <StatsCard
                    title="Opening Balance"
                    :value="formatNumber(summary.opening_qty)"
                    icon="📦"
                    color="blue"
                />

                <StatsCard
                    title="Total In"
                    :value="formatNumber(summary.total_qty_in)"
                    icon="📥"
                    color="green"
                />

                <StatsCard
                    title="Total Out"
                    :value="formatNumber(summary.total_qty_out)"
                    icon="📤"
                    color="red"
                />

                <StatsCard
                    title="Closing Balance"
                    :value="formatNumber(summary.closing_qty)"
                    icon="📊"
                    color="purple"
                />

            </div>


            <!-- ===================================================== -->
            <!-- Stock Card Table -->
            <!-- ===================================================== -->

            <Card>

                <!-- ================================================= -->
                <!-- Toolbar -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        flex-col
                        gap-3
                        border-b
                        border-gray-200
                        p-4
                        dark:border-gray-700
                        sm:flex-row
                        sm:items-center
                        sm:justify-between
                    "
                >

                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Inventory Movements
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Detailed stock movement transactions.
                        </p>
                    </div>


                    <!-- Filter Actions -->

                    <div
                        class="
                            flex
                            w-full
                            flex-wrap
                            items-center
                            gap-2
                            sm:w-auto
                            sm:justify-end
                        "
                    >

                        <!-- Show / Hide Filter -->

                        <BaseButton
                            variant="secondary"
                            class="w-full sm:w-auto"
                            @click="showFilters = !showFilters"
                        >
                            <span
                                class="
                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-2
                                    whitespace-nowrap
                                "
                            >

                                <FunnelIcon class="h-4 w-4 shrink-0" />

                                <span>
                                    {{ showFilters ? 'Hide Filter' : 'Show Filter' }}
                                </span>

                                <span
                                    v-if="hasActiveFilter"
                                    class="h-2 w-2 shrink-0 rounded-full bg-blue-500"
                                ></span>

                            </span>
                        </BaseButton>


                        <!-- Reset Filter -->
                        <!-- Only visible when filters are expanded -->

                        <BaseButton
                            v-if="showFilters"
                            variant="secondary"
                            class="w-full sm:w-auto"
                            @click="resetFilters"
                        >
                            <span
                                class="
                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-2
                                    whitespace-nowrap
                                "
                            >
                                <ArrowPathIcon class="h-4 w-4 shrink-0" />

                                <span>
                                    Reset Filter
                                </span>
                            </span>
                        </BaseButton>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Filter Panel -->
                <!-- ================================================= -->

                <div
                    v-if="showFilters"
                    class="
                        border-b
                        border-gray-200
                        bg-gray-50
                        p-4
                        dark:border-gray-700
                        dark:bg-gray-800/50
                    "
                >

                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            sm:grid-cols-2
                            xl:grid-cols-4
                        "
                    >

                        <!-- Branch -->

                        <div class="min-w-0">

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    dark:text-gray-300
                                "
                            >
                                Branch
                            </label>

                            <SearchableSelect
                                v-model="filters.branch_id"
                                :options="branches"
                                label="label"
                                value-key="id"
                                placeholder="All Branches"
                            />

                        </div>


                        <!-- Warehouse -->

                        <div class="min-w-0">

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    dark:text-gray-300
                                "
                            >
                                Warehouse
                            </label>

                            <SearchableSelect
                                v-model="filters.warehouse_id"
                                :options="filteredWarehouses"
                                label="label"
                                value-key="id"
                                placeholder="All Warehouses"
                            />

                        </div>


                        <!-- Product / Variant -->

                        <div class="min-w-0">

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    dark:text-gray-300
                                "
                            >
                                Product / Variant
                            </label>

                            <SearchableSelect
                                v-model="filters.product_variant_id"
                                :options="variants"
                                label="label"
                                value-key="id"
                                placeholder="All Products"
                            />

                        </div>


                        <!-- Unit -->

                        <div class="min-w-0">

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    dark:text-gray-300
                                "
                            >
                                Unit
                            </label>

                            <SearchableSelect
                                v-model="filters.unit_id"
                                :options="units"
                                label="label"
                                value-key="id"
                                placeholder="All Units"
                            />

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Table -->
                <!-- ================================================= -->

                <div class="relative mt-6">

                    <!-- Loading -->

                    <LoadingOverlay
                        :show="loading"
                        text="Loading Stock Card..."
                    />


                    <!-- Data -->

                    <DataTable
                        v-if="rows.length"
                        sticky-header
                        max-height="650px"
                    >

                        <DataTableHead sticky>

                            <DataTableHeaderCell width="140px">
                                Date
                            </DataTableHeaderCell>


                            <DataTableHeaderCell width="180px">
                                Reference
                            </DataTableHeaderCell>


                            <DataTableHeaderCell width="260px">
                                Description
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="140px"
                                align="right"
                            >
                                Stock Awal
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="130px"
                                align="right"
                            >
                                Qty In
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="130px"
                                align="right"
                            >
                                Qty Out
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="140px"
                                align="right"
                            >
                                Balance
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="160px"
                                align="right"
                            >
                                Unit Cost
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="180px"
                                align="right"
                            >
                                Total Cost
                            </DataTableHeaderCell>

                        </DataTableHead>


                        <DataTableBody>

                            <DataTableRow
                                v-for="(row, index) in rows"
                                :key="row.id ?? index"
                            >

                                <!-- Date -->

                                <DataTableCell>
                                    <span
                                        class="
                                            whitespace-nowrap
                                            text-sm
                                            text-gray-700
                                            dark:text-gray-300
                                        "
                                    >
                                        {{ formatDate(row.transaction_date ?? row.date) }}
                                    </span>
                                </DataTableCell>


                                <!-- Reference -->

                                <DataTableCell>
                                    <span
                                        class="
                                            font-medium
                                            text-blue-600
                                            dark:text-blue-400
                                        "
                                    >
                                        {{ row.reference_number ?? row.reference ?? '-' }}
                                    </span>
                                </DataTableCell>


                                <!-- Description -->

                                <DataTableCell>
                                    <span
                                        class="
                                            text-sm
                                            text-gray-700
                                            dark:text-gray-300
                                        "
                                    >
                                        {{ row.description ?? '-' }}
                                    </span>
                                </DataTableCell>


                                <!-- Opening Balance -->

                                <DataTableCell align="right">
                                    <span
                                        class="
                                            tabular-nums
                                            text-sm
                                            text-gray-700
                                            dark:text-gray-300
                                        "
                                    >
                                        {{ formatNumber(row.opening_qty ?? row.stock_awal ?? 0) }}
                                    </span>
                                </DataTableCell>


                                <!-- Qty In -->

                                <DataTableCell align="right">

                                    <span
                                        v-if="Number(row.qty_in) > 0"
                                        class="
                                            tabular-nums
                                            text-sm
                                            font-medium
                                            text-green-600
                                            dark:text-green-400
                                        "
                                    >
                                        +{{ formatNumber(row.qty_in) }}
                                    </span>

                                    <span
                                        v-else
                                        class="text-gray-400"
                                    >
                                        -
                                    </span>

                                </DataTableCell>


                                <!-- Qty Out -->

                                <DataTableCell align="right">

                                    <span
                                        v-if="Number(row.qty_out) > 0"
                                        class="
                                            tabular-nums
                                            text-sm
                                            font-medium
                                            text-red-600
                                            dark:text-red-400
                                        "
                                    >
                                        -{{ formatNumber(row.qty_out) }}
                                    </span>

                                    <span
                                        v-else
                                        class="text-gray-400"
                                    >
                                        -
                                    </span>

                                </DataTableCell>


                                <!-- Balance -->

                                <DataTableCell align="right">
                                    <span
                                        class="
                                            tabular-nums
                                            text-sm
                                            font-semibold
                                            text-gray-900
                                            dark:text-white
                                        "
                                    >
                                        {{ formatNumber(row.balance_qty) }}
                                    </span>
                                </DataTableCell>


                                <!-- Unit Cost -->

                                <DataTableCell align="right">
                                    <span
                                        class="
                                            whitespace-nowrap
                                            tabular-nums
                                            text-sm
                                            text-gray-700
                                            dark:text-gray-300
                                        "
                                    >
                                        {{ formatCurrency(row.unit_cost) }}
                                    </span>
                                </DataTableCell>


                                <!-- Total Cost -->

                                <DataTableCell align="right">
                                    <span
                                        class="
                                            whitespace-nowrap
                                            tabular-nums
                                            text-sm
                                            font-medium
                                            text-gray-900
                                            dark:text-white
                                        "
                                    >
                                        {{ formatCurrency(row.total_cost) }}
                                    </span>
                                </DataTableCell>

                            </DataTableRow>

                        </DataTableBody>

                    </DataTable>


                    <!-- Empty State -->

                    <TableEmpty
                        v-else
                        icon="📦"
                        title="No Stock Card Found"
                        description="
                            There are no stock movements matching
                            the selected filters.
                        "
                    />

                </div>


                <!-- ================================================= -->
                <!-- Pagination -->
                <!-- ================================================= -->

                <div
                    v-if="stockCard && stockCard.last_page > 1"
                    class="
                        mt-6
                        border-t
                        border-gray-200
                        px-4
                        py-3
                        dark:border-gray-700
                    "
                >

                    <TablePagination
                        :data="stockCard"
                    />

                </div>

            </Card>

        </div>

    </AppLayout>
</template>